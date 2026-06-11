<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CommonHelper;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Attraction;
use App\Models\Bed;
use App\Models\ExternalApiReceive;
use App\Models\Hotel;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Room;
use App\Models\Tax;
use App\Models\Tour;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalApiReceiveController extends Controller
{
    public function receive(Request $request): JsonResponse
    {
        $payload = $this->normalizeToArray($request->input('payload', $request->all()));
        if ($payload === [] && trim((string) $request->getContent()) !== '') {
            $payload = $this->normalizeToArray($request->getContent());
        }
        $payload = $this->unwrapPayload($payload);

        if (isset($payload['raw_body']) && empty($payload['destinations'])) {
            $reparsed = $this->unwrapPayload($this->normalizeToArray($payload['raw_body']));
            if (! empty($reparsed['destinations'])) {
                $payload = $reparsed;
            }
        }

        if (empty($payload['destinations'])) {
            $record = ExternalApiReceive::create([
                'source_ip' => $request->ip(),
                'source_server' => (string) ($request->header('X-Source-Server') ?? ''),
                'headers' => $request->headers->all(),
                'payload' => $payload,
                'status' => false,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid payload: destinations missing. Send valid JSON (double quotes) or a Python dict with a response.destinations block.',
                'received_id' => $record->id,
                'hint' => 'Use Content-Type: application/json and json.dumps(data) in Python, not str(dict).',
            ], 422);
        }
        // Always persist the received payload first (audit trail). The index()
        // endpoint reads status=false rows as "pending", so keeping the record
        // even when conversion fails lets the payload be retried/inspected later.
        $record = ExternalApiReceive::create([
            'source_ip' => $request->ip(),
            'source_server' => (string) ($request->header('X-Source-Server') ?? ''),
            'headers' => $request->headers->all(),
            'payload' => $payload,
            'status' => false,
        ]);

        $result = [
            'external_data_stored' => true,
            'tour_created' => false,
            'tour_already_exists' => false,
            'order_created' => false,
            'tour_id' => null,
            'tour_display_id' => null,
            'order_id' => null,
            'orders_count' => 0,
            'agent_id' => null,
            'agency_id' => null,
            'email_sent' => false,
            'sender_email_sent' => false,
            'sender_email' => null,
        ];

        try {
            // Atomic: Tour creation + Order creation + status flip succeed or fail together.
            [$tour, $orders] = DB::transaction(function () use ($payload, $record) {
                $tour = $this->createTourFromPayload($payload);
                $orders = $this->createOrdersFromPayload($tour, $payload);

                // Mark the received payload as processed only after both succeed.
                $record->status = true;
                $record->save();

                return [$tour, $orders];
            });

            $result['tour_created'] = true;
            $result['order_created'] = $orders->isNotEmpty();
            $result['tour_id'] = $tour->tour_id;
            $result['tour_display_id'] = $tour->display_id;
            $result['order_id'] = optional($orders->first())->getKey();
            $result['orders_count'] = $orders->count();
            $result['agent_id'] = $tour->agent_id;
            $result['agency_id'] = Agent::where('agent_id', $tour->agent_id)->value('agency_id');

            // Notify the agent (non-fatal: never roll back a committed tour for an email).
            $result['email_sent'] = $this->notifyAgent($tour);

            // Notify sender_email from payload (non-fatal).
            $senderNotify = $this->notifySender($tour, $payload, $orders);
            $result['sender_email_sent'] = $senderNotify['sent'];
            $result['sender_email'] = $senderNotify['email'];

            return response()->json([
                'success' => true,
                'message' => 'Payload received and tour/order generated successfully.',
                'received_id' => $record->id,
                'result' => $result,
            ], 201);
        } catch (Throwable $e) {
            // Transaction already rolled back the Tour/Order; the audit record remains.
            Log::error('External API tour/order generation failed', [
                'received_id' => $record->id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payload stored, but tour/order generation failed: ' . $e->getMessage(),
                'received_id' => $record->id,
                'result' => $result,
            ], 422);
        }
    }

    /**
     * Build and persist a Tour from the received payload, mirroring the business
     * logic in SingleTourPackageController::store() (adapted for an unauthenticated
     * external request, where the owning DMC is derived from the payload's DMC_id
     * instead of the authenticated user).
     */
    protected function createTourFromPayload(array $payload): Tour
    {
        $primaryDmc = $this->resolvePrimaryDmc($payload);
        $dmcUser = $this->resolveDmcUser($payload, $primaryDmc);
        if (!$dmcUser) {
            throw new \RuntimeException('Unable to resolve the DMC from the payload (DMC_id, DMC_email or Master_DMC_id is required).');
        }
        $createdBy = (int) $dmcUser->userId;
        // $dmcId = (int) ($dmcUser->created_by ?: $dmcUser->userId);
        $dmcId = (int) $dmcUser->userId;
        $agent = $this->resolveOrCreateAgentForDmc($dmcUser, $dmcId, $payload, $primaryDmc);
        $checkInTime = $this->parseDate(
            $this->payloadValue($payload, ['start_date', 'check_in', 'check_in_date']),
            Carbon::today()
        );
        $nights = (int) ($this->payloadValue($payload, ['requested_days', 'total_days', 'nights'], 0) ?: 0);
        $checkOutTime = $nights > 0
            ? (clone $checkInTime)->addDays($nights)
            : (clone $checkInTime)->addDay();
        if ($checkOutTime->lte($checkInTime)) {
            $checkOutTime = (clone $checkInTime)->addDay();
        }
        $autoCancelDay = (int) ($dmcUser->auto_cancel_date ?? 0);
        $autoCancelDate = (clone $checkInTime)->subDays($autoCancelDay)->toDateString();
        $taxArray = [];
        $taxes = Tax::where('dmc_id', $dmcId)
            ->where('is_active', 1)
            ->orderBy('created_at', 'asc')
            ->get();
        foreach ($taxes as $tax) {
            $taxArray[] = [
                'tax_id' => $tax->tax_id,
                'tax_name' => $tax->tax_name,
                'tax_type' => $tax->tax_type,
                'tax_value' => $tax->tax_value,
                'calculate_on' => $tax->calculate_on,
                'description' => $tax->description ?? '',
                'if_fixed' => $tax->if_fixed ?? null,
            ];
        }
        $destination = $this->resolveDestination($payload, $primaryDmc);
        $city = $this->resolveFirstCity($payload) ?: $destination;
        $tour = new Tour();
        $tour->destination = $destination;
        $tour->adult = (int) ($this->payloadValue($payload, ['adults', 'adult'], 1) ?: 1);
        $tour->child = (int) ($this->payloadValue($payload, ['children', 'child'], 0) ?: 0);
        $tour->infant = (int) ($this->payloadValue($payload, ['infants', 'infant'], 0) ?: 0);
        $tour->agent_id = $agent->agent_id;
        $tour->tour_type = 'FIT';
        $tour->discount = 0;
        $tour->discount_amount = 0;
        $tour->city_type = $this->payloadValue($payload, ['city_type'], 'single');
        $tour->male_count = 0;
        $tour->female_count = 0;
        $tour->check_in_time = $checkInTime;
        $tour->check_out_time = $checkOutTime;
        $tour->display_id = 'DMC-ORD';
        $tour->tour_status = 'New Enquiry';
        $tour->city = $city;
        $tour->dmc_id = $dmcId;
        $tour->auto_cancel_date = $autoCancelDate;
        $tour->taxes = !empty($taxArray) ? $taxArray : null;
        $tour->reference_id = $this->payloadValue($payload, ['reference_number', 'reference_id', 'Master_DMC_id'], null);
        $tour->created_by = $createdBy;
        $tour->mainguest = $this->extractMainGuest($payload);
        $tour->additionalguest = $this->extractAdditionalGuests($payload);
        $tour->save();
        $tour->refresh();
        $tour->display_id = 'DMC-ORD' . $tour->tour_id;
        $tour->save();
        CommonHelper::appendTourStatusTrackById(
            (int) $tour->tour_id,
            null,
            'New Enquiry',
            null,
            null,
            null,
            null,
            $dmcUser->name ?? null,
            $dmcId
        );
        return $tour;
    }

    /**
     * Create the Orders related to the freshly created Tour. The payload nests
     * services per day (destinations[].DMC[].packages[].days[].{hotels,attractions,
     * restaurants,services}); each service item becomes one typed Order linked to
     * the tour. When no services are present, a single fallback order preserves the
     * raw package data so nothing is lost.
     */
    protected function createOrdersFromPayload(Tour $tour, array $payload): Collection
    {
        $orders = collect();
        $startDate = $this->parseDate(
            $this->payloadValue($payload, ['start_date', 'check_in', 'check_in_date']),
            Carbon::today()
        );

        foreach ($payload['destinations'] ?? [] as $destination) {
            foreach ($destination['DMC'] ?? [] as $dmc) {
                foreach ($dmc['packages'] ?? [] as $package) {
                    $packageId = $package['package_id'] ?? null;

                    foreach ($package['days'] ?? [] as $day) {
                        $dayNumber = (int) ($day['day'] ?? 0);
                        $bookingDate = $dayNumber > 0
                            ? (clone $startDate)->addDays($dayNumber - 1)->toDateString()
                            : $startDate->toDateString();

                        $hasRestaurantServices = collect($this->itemsFrom($day['services'] ?? []))
                            ->contains(fn ($row) => strtolower((string) ($row['service_type'] ?? '')) === 'restaurant');

                        $serviceGroups = [
                            'hotel' => $day['hotels'] ?? [],
                            'attraction' => $day['attractions'] ?? [],
                            'restaurant' => $hasRestaurantServices ? [] : ($day['restaurants'] ?? []),
                        ];

                        foreach ($serviceGroups as $type => $node) {
                            foreach ($this->itemsFrom($node) as $item) {
                                $orders->push($this->makeOrder($tour, $type, $item, [
                                    'day' => $dayNumber,
                                    'bookingDate' => $bookingDate,
                                    'package_id' => $packageId,
                                ]));
                            }
                        }

                        foreach ($this->itemsFrom($day['services'] ?? []) as $item) {
                            $type = (string) ($item['service_type'] ?? 'service') ?: 'service';
                            $orders->push($this->makeOrder($tour, $type, $item, [
                                'day' => $dayNumber,
                                'bookingDate' => $bookingDate,
                                'package_id' => $packageId,
                            ]));
                        }
                    }
                }
            }
        }

        if ($orders->isEmpty()) {
            // Fallback: still create one order so the tour has related order data.
            $orders->push($this->makeOrder($tour, 'enquiry', $payload, [
                'bookingDate' => $startDate->toDateString(),
            ]));
        }

        return $orders;
    }

    /**
     * Persist a single Order linked to the tour. Data is normalized to the same shape
     * used by SingleTourPackageController::storeServiceOrders() so editform can read it.
     */
    protected function makeOrder(Tour $tour, string $type, array $item, array $meta = []): Order
    {
        $data = $this->buildOrderData($tour, $type, $item, $meta);
        $normalizedType = $this->normalizeOrderType($type);

        if ($normalizedType === 'hotel') {
            $data = $this->normalizeHotelOrderPayload($data, $tour);
        }

        $bookingId = CommonHelper::nextOrderBookingId();

        return Order::create([
            'agent_id' => $tour->agent_id,
            'tour_id' => $tour->tour_id,
            'booking_id' => $bookingId,
            'data' => [$data],
            'type' => $normalizedType,
            'status' => 1,
            'bookingType' => 'enquiry',
            'remarks' => $data['remarks'] ?? null,
        ]);
    }

    /**
     * Map external payload items into the order `data` structure expected by edit/create views.
     */
    protected function buildOrderData(Tour $tour, string $type, array $item, array $meta): array
    {
        $customer = $this->customerContextFromTour($tour);

        $normalized = match ($this->normalizeOrderType($type)) {
            'hotel' => $this->transformHotelItem($tour, $item, $meta, $customer),
            'attraction' => $this->transformAttractionItem($tour, $item, $meta, $customer),
            'restaurant' => $this->transformRestaurantItem($tour, $item, $meta, $customer),
            default => array_merge($customer, $item, $meta, [
                'bookingDate' => $meta['bookingDate'] ?? null,
                'totalPrice' => (float) ($item['price'] ?? $item['totalPrice'] ?? 0),
                'price' => (float) ($item['price'] ?? $item['totalPrice'] ?? 0),
                'source' => 'external_api',
            ]),
        };

        // Always keep audit metadata without overwriting mapped keys.
        return array_merge($normalized, array_filter([
            'external_day' => $meta['day'] ?? null,
            'external_package_id' => $meta['package_id'] ?? null,
            'source' => 'external_api',
        ], fn ($v) => $v !== null && $v !== ''));
    }

    protected function normalizeOrderType(string $type): string
    {
        $type = strtolower(trim($type));
        return match ($type) {
            'hotels', 'hotel_booking' => 'hotel',
            'attractions', 'attraction_booking' => 'attraction',
            'restaurants', 'restaurant_booking' => 'restaurant',
            default => $type,
        };
    }

    protected function customerContextFromTour(Tour $tour): array
    {
        $guest = is_array($tour->mainguest) ? $tour->mainguest : [];

        return [
            'fullName' => $guest['full_name'] ?? $guest['fullName'] ?? 'Guest User',
            'email' => $guest['email'] ?? 'guest@example.com',
            'phone' => $guest['phone'] ?? '0000000000',
            'countryCode' => $guest['country_code'] ?? $guest['countryCode'] ?? '65',
            'address1' => $guest['address1'] ?? 'Address not provided',
            'address2' => $guest['address2'] ?? null,
            'state' => $guest['state'] ?? null,
            'zip' => $guest['zip'] ?? null,
            'specialRequests' => $guest['special_requests'] ?? $guest['specialRequests'] ?? null,
            'bookingType' => 'enquiry',
        ];
    }

    protected function transformHotelItem(Tour $tour, array $item, array $meta, array $customer): array
    {
        $hotelId = $item['hotel_id'] ?? $item['hotelId'] ?? null;
        $hotel = $this->resolveHotelRecord($hotelId, $item);
        if ($hotel) {
            $hotelId = $hotel->hotel_unique_id;
        }

        $dmcId = (int) ($tour->dmc_id ?? 0);
        $createdBy = (int) ($tour->created_by ?? 0);
        $resolvedRoom = $this->resolveRoomFromItem($hotelId, $item, $dmcId, $createdBy);
        $resolvedBed = $resolvedRoom ? $this->resolveBedFromItem($resolvedRoom, $item) : null;

        $hotelName = $item['hotel_name'] ?? $item['name'] ?? ($hotel->name ?? 'Hotel Booking');
        $city = $item['city'] ?? ($hotel->city ?? 'Location not specified');
        $checkIn = $this->parseDate($meta['bookingDate'] ?? $tour->check_in_time, Carbon::today())->toDateString();
        $nights = max(1, (int) ($item['night'] ?? $item['nights'] ?? 1));
        $checkOut = Carbon::parse($checkIn)->addDays($nights)->toDateString();
        $mealPlan = $this->resolveHotelMealPlan($item, $resolvedRoom);
        $mealPlanLabel = strtolower($this->mealPlanToLabel(
            trim((string) ($item['meal_plan'] ?? $item['meal_type'] ?? '')) ?: $mealPlan
        ));
        $numberOfRooms = max(1, (int) ($item['number_of_rooms'] ?? 1));
        $adults = max(1, (int) $tour->adult);
        $occupancy = $adults <= 1 ? 'single' : 'double';

        $payloadPrice = (float) ($item['price'] ?? $item['totalPrice'] ?? 0);
        $price = $payloadPrice;
        if ($price <= 0 && $resolvedRoom) {
            $price = $this->calculateHotelTotalPrice(
                $resolvedRoom,
                $nights,
                $numberOfRooms,
                $adults,
                $occupancy,
                $mealPlan
            );
        }

        $headCount = max(1, (int) ($resolvedBed?->adult_count ?? $resolvedBed?->max_occupancy ?? $adults));
        $bedType = trim((string) (
            $resolvedBed?->room_type
            ?? $item['bed_type']
            ?? $item['bedType']
            ?? ''
        ));
        $roomType = trim((string) (
            $resolvedRoom?->room_type
            ?? $item['room_type']
            ?? $item['roomType']
            ?? ''
        ));

        $bedPrice = $resolvedRoom
            ? ($occupancy === 'single'
                ? (float) ($resolvedRoom->weekday_price ?? 0)
                : (float) ($resolvedRoom->double_weekday_price ?? $resolvedRoom->weekday_price ?? 0))
            : 0;
        $mealPrice = max(0, $price - ($bedPrice * $nights * $numberOfRooms));
        $roomId = $resolvedRoom?->room_id ?? ($item['room_id'] ?? $item['roomId'] ?? null);
        $bedId = $resolvedBed?->bed_id ?? ($item['bed_id'] ?? $item['bedId'] ?? null);

        $transfer = $this->mapTransferOptions(
            is_array($item['transfer'] ?? null) ? $item['transfer'] : [],
            [
                'required' => ($item['arrival_departure'] ?? 'No') === 'Yes',
                'type' => $item['arrival_departure_type'] ?? 'private',
                'pickup_label' => $item['transfer_pickup_label'] ?? null,
                'drop_label' => $item['transfer_drop_label'] ?? null,
                'pickup_location' => $item['transfer_pickup'] ?? null,
                'drop_location' => $item['transfer_drop'] ?? null,
                'city' => $item['transfer_city'] ?? $city,
            ]
        );

        return array_merge($customer, [
            'id' => null,
            'bookingDate' => [$checkIn, $checkOut],
            'hotelDetails' => [
                'hotel_id' => $hotel?->hotel_unique_id ?? $hotelId,
                'hotel_name' => $hotelName,
                'image' => $hotel?->main_image ?? '',
                'location' => $city,
                'checkInTime' => $hotel?->check_in_time ?? '15:00:00',
                'checkOutTime' => $hotel?->check_out_time ?? '12:00:00',
                'cancellation_charge' => null,
            ],
            'priceMode' => 'dmc',
            'priceModeId' => $dmcId ?: $createdBy,
            'rooms' => [[
                'room_id' => is_numeric($roomId) ? (int) $roomId : $roomId,
                'room_type' => $roomType,
                'number_of_rooms' => $numberOfRooms,
                'occupancy' => $occupancy,
                'selected_persons' => $adults,
                'beds' => [[
                    'bed_id' => $bedId !== null ? (string) $bedId : '',
                    'bed_type' => $bedType,
                    'room_type' => $roomType,
                    'baby_cot' => (int) ($resolvedBed?->baby_cot ?? 0),
                    'head_count' => $headCount,
                    'max_occupancy' => max(1, (int) ($resolvedBed?->max_occupancy ?? $headCount)),
                    'price' => $bedPrice > 0 ? ($bedPrice * $nights * $numberOfRooms) : $price,
                    'mealTypes' => [$mealPlanLabel],
                    'selectedMeals' => [
                        'meal_1' => [
                            'type' => $mealPlanLabel,
                            'price' => $mealPrice,
                        ],
                    ],
                ]],
            ]],
            'totalPrice' => $price,
            'price' => $price,
            'transfer_options' => $transfer,
            'child_with_bed' => null,
            'child_without_bed' => null,
            'guide_options' => ($item['guide_required'] ?? 'No') === 'Yes'
                ? ['guide_required' => true]
                : null,
            'remarks' => $item['remarks'] ?? '',
            'supplement' => filter_var($item['supplement'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'tour_id' => (string) $tour->tour_id,
        ]);
    }

    /**
     * Align external API hotel order JSON with SingleTourPackageController::storeServiceOrders().
     *
     * @param  array<string, mixed>  $hotelData
     * @return array<string, mixed>
     */
    protected function normalizeHotelOrderPayload(array $hotelData, Tour $tour): array
    {
        $hotelDetails = is_array($hotelData['hotelDetails'] ?? null) ? $hotelData['hotelDetails'] : [];
        $hotelId = $hotelDetails['hotel_id'] ?? $hotelData['hotel_id'] ?? null;
        $dmcId = (int) ($tour->dmc_id ?? 0);
        $createdBy = (int) ($tour->created_by ?? 0);
        $rooms = is_array($hotelData['rooms'] ?? null) ? $hotelData['rooms'] : [];

        if ($hotelId && $rooms !== []) {
            $rooms = CommonHelper::fixHotelOrderRoomIds($rooms, $hotelId);
            $rooms = $this->syncHotelRoomsFromDatabase($rooms, $dmcId, $createdBy);
        }

        foreach ($rooms as $roomIndex => $room) {
            if (! is_array($room['beds'] ?? null)) {
                continue;
            }

            $roomType = (string) ($room['room_type'] ?? '');
            foreach ($room['beds'] as $bedIndex => $bed) {
                if (! is_array($bed)) {
                    continue;
                }

                $mealLabel = $bed['selectedMeals']['meal_1']['type'] ?? ($bed['mealTypes'][0] ?? 'room only');
                $mealLower = strtolower(str_replace('_', ' ', trim((string) $mealLabel)));

                $rooms[$roomIndex]['beds'][$bedIndex]['mealTypes'] = [$mealLower];
                $rooms[$roomIndex]['beds'][$bedIndex]['room_type'] = $roomType;
                if (isset($rooms[$roomIndex]['beds'][$bedIndex]['selectedMeals']['meal_1'])) {
                    $rooms[$roomIndex]['beds'][$bedIndex]['selectedMeals']['meal_1']['type'] = $mealLower;
                }
            }
        }

        return [
            'fullName' => $hotelData['fullName'] ?? 'Guest User',
            'email' => $hotelData['email'] ?? 'guest@example.com',
            'phone' => $hotelData['phone'] ?? '0000000000',
            'countryCode' => $hotelData['countryCode'] ?? '65',
            'address1' => $hotelData['address1'] ?? 'Address not provided',
            'address2' => $hotelData['address2'] ?? null,
            'state' => $hotelData['state'] ?? 'State not provided',
            'zip' => $hotelData['zip'] ?? '000000',
            'specialRequests' => $hotelData['specialRequests'] ?? null,
            'id' => $hotelData['id'] ?? null,
            'bookingType' => $hotelData['bookingType'] ?? 'enquiry',
            'bookingDate' => $hotelData['bookingDate'] ?? [],
            'hotelDetails' => [
                'hotel_id' => $hotelId ?? ('hotel_' . time()),
                'hotel_name' => $hotelDetails['hotel_name'] ?? $hotelData['hotel_name'] ?? 'Hotel Booking',
                'image' => $hotelDetails['image'] ?? $hotelData['hotel_image'] ?? '',
                'location' => $hotelDetails['location'] ?? $hotelData['hotel_location'] ?? 'Location not specified',
                'checkInTime' => $hotelDetails['checkInTime'] ?? $hotelData['check_in_time'] ?? '15:00:00',
                'checkOutTime' => $hotelDetails['checkOutTime'] ?? $hotelData['check_out_time'] ?? '12:00:00',
                'cancellation_charge' => $hotelDetails['cancellation_charge'] ?? null,
            ],
            'priceMode' => $hotelData['priceMode'] ?? 'dmc',
            'priceModeId' => (int) ($hotelData['priceModeId'] ?? ($dmcId ?: $createdBy)),
            'rooms' => $rooms,
            'totalPrice' => (float) ($hotelData['totalPrice'] ?? 0),
            'price' => (float) ($hotelData['totalPrice'] ?? $hotelData['price'] ?? 0),
            'transfer_options' => $hotelData['transfer_options'] ?? null,
            'child_with_bed' => $hotelData['child_with_bed'] ?? null,
            'child_without_bed' => $hotelData['child_without_bed'] ?? null,
            'extra_bed' => $hotelData['extra_bed'] ?? null,
            'guide_options' => $hotelData['guide_options'] ?? null,
            'tour_id' => (string) $tour->tour_id,
            'remarks' => $hotelData['remarks'] ?? null,
            'supplement' => filter_var($hotelData['supplement'] ?? $hotelData['is_supplement'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rooms
     * @return list<array<string, mixed>>
     */
    protected function syncHotelRoomsFromDatabase(array $rooms, int $dmcId, int $createdBy): array
    {
        foreach ($rooms as $roomIndex => $room) {
            $roomId = $room['room_id'] ?? null;
            $roomRecord = null;

            if ($roomId !== null && $roomId !== '' && is_numeric($roomId)) {
                $roomRecord = Room::where('room_id', (int) $roomId)->first();
            }

            if ($roomRecord) {
                $rooms[$roomIndex]['room_id'] = (int) $roomRecord->room_id;
                $rooms[$roomIndex]['room_type'] = (string) $roomRecord->room_type;
            }

            $roomType = (string) ($rooms[$roomIndex]['room_type'] ?? '');
            $beds = is_array($room['beds'] ?? null) ? $room['beds'] : [];

            foreach ($beds as $bedIndex => $bed) {
                if (! is_array($bed)) {
                    continue;
                }

                $bedId = $bed['bed_id'] ?? null;
                if (is_string($bedId) && is_numeric($bedId)) {
                    $bedId = (int) $bedId;
                }

                $bedRecord = null;
                if ($bedId !== null && $bedId !== '' && is_numeric($bedId)) {
                    $bedRecord = Bed::where('bed_id', (int) $bedId)->first();
                }

                if (! $bedRecord && $roomRecord) {
                    $bedType = trim((string) ($bed['bed_type'] ?? ''));
                    $bedQuery = Bed::query()
                        ->where('room_id', $roomRecord->room_id)
                        ->where(function ($q) {
                            $q->where('is_active', 1)->orWhereNull('is_active');
                        });

                    if ($bedType !== '') {
                        $bedRecord = (clone $bedQuery)
                            ->where('room_type', $bedType)
                            ->orderBy('bed_id')
                            ->first();
                    }

                    if (! $bedRecord) {
                        $bedRecord = $bedQuery->orderBy('bed_id')->first();
                    }
                }

                if ($bedRecord) {
                    $bedType = (string) ($bedRecord->room_type ?: ($bed['bed_type'] ?? ''));
                    $headCount = max(1, (int) ($bed['head_count'] ?? $bedRecord->adult_count ?? $bedRecord->max_occupancy ?? 2));

                    $rooms[$roomIndex]['beds'][$bedIndex]['bed_id'] = (string) $bedRecord->bed_id;
                    $rooms[$roomIndex]['beds'][$bedIndex]['bed_type'] = $bedType;
                    $rooms[$roomIndex]['beds'][$bedIndex]['room_type'] = $roomType;
                    $rooms[$roomIndex]['beds'][$bedIndex]['head_count'] = $headCount;
                    $rooms[$roomIndex]['beds'][$bedIndex]['max_occupancy'] = max(
                        1,
                        (int) ($bed['max_occupancy'] ?? $bedRecord->max_occupancy ?? $headCount)
                    );
                    $rooms[$roomIndex]['beds'][$bedIndex]['baby_cot'] = (int) ($bed['baby_cot'] ?? $bedRecord->baby_cot ?? 0);
                }
            }
        }

        return $rooms;
    }

    /**
     * Resolve hotel row; accepts hotel_unique_id or numeric id from external payload.
     */
    protected function resolveHotelRecord($hotelId, array $item): ?Hotel
    {
        if (empty($hotelId)) {
            return null;
        }

        foreach ($this->normalizeHotelIds($hotelId) as $id) {
            $hotel = Hotel::where('hotel_unique_id', $id)->first();
            if ($hotel) {
                return $hotel;
            }
        }

        $name = trim((string) ($item['hotel_name'] ?? $item['name'] ?? ''));
        if ($name !== '') {
            return Hotel::where('name', $name)->orWhere('name', 'like', '%' . explode(' - ', $name)[0] . '%')->first();
        }

        return null;
    }

    /**
     * Resolve room from external payload room_id / room_type, then fall back to base room.
     */
    protected function resolveRoomFromItem($hotelId, array $item, ?int $dmcId, ?int $createdBy = null): ?Room
    {
        $roomId = $item['room_id'] ?? $item['roomId'] ?? null;
        if ($roomId !== null && $roomId !== '' && is_numeric($roomId)) {
            $query = Room::query()
                ->where('room_id', (int) $roomId)
                ->where(function ($q) {
                    $q->where('status', 1)->orWhereNull('status');
                });

            if (!empty($hotelId)) {
                $query->whereIn('hotel_id', $this->normalizeHotelIds($hotelId));
            }

            $room = $query->first();
            if ($room) {
                return $room;
            }
        }

        $roomType = trim((string) ($item['room_type'] ?? $item['roomType'] ?? ''));
        if ($roomType !== '' && !empty($hotelId)) {
            $typeQuery = Room::query()
                ->whereIn('hotel_id', $this->normalizeHotelIds($hotelId))
                ->where('room_type', $roomType)
                ->where(function ($q) {
                    $q->where('status', 1)->orWhereNull('status');
                });

            foreach (array_filter(array_unique([$createdBy, $dmcId])) as $ownerId) {
                $room = (clone $typeQuery)
                    ->where('created_by', $ownerId)
                    ->orderBy('room_id')
                    ->first();
                if ($room) {
                    return $room;
                }
            }

            $room = $typeQuery->orderBy('room_id')->first();
            if ($room) {
                return $room;
            }
        }

        return $this->resolveBaseRoom($hotelId, $dmcId, $createdBy);
    }

    /**
     * Resolve bed from external payload bed_id / bed_type, then fall back to first bed.
     */
    protected function resolveBedFromItem(Room $room, array $item): ?Bed
    {
        $bedId = $item['bed_id'] ?? $item['bedId'] ?? null;
        if ($bedId !== null && $bedId !== '' && is_numeric($bedId)) {
            $bed = Bed::where('bed_id', (int) $bedId)
                ->where('room_id', $room->room_id)
                ->where(function ($q) {
                    $q->where('is_active', 1)->orWhereNull('is_active');
                })
                ->first();
            if ($bed) {
                return $bed;
            }

            $bed = Bed::where('bed_id', (int) $bedId)
                ->where(function ($q) {
                    $q->where('is_active', 1)->orWhereNull('is_active');
                })
                ->first();
            if ($bed) {
                return $bed;
            }
        }

        $bedType = trim((string) ($item['bed_type'] ?? $item['bedType'] ?? ''));
        if ($bedType !== '') {
            $bed = Bed::where('room_id', $room->room_id)
                ->where('room_type', $bedType)
                ->where(function ($q) {
                    $q->where('is_active', 1)->orWhereNull('is_active');
                })
                ->orderBy('bed_id')
                ->first();
            if ($bed) {
                return $bed;
            }
        }

        return $this->resolveFirstBed($room);
    }

    /**
     * Convert internal meal plan keys to the label format stored in order data.
     */
    protected function mealPlanToLabel(string $mealPlan): string
    {
        $trimmed = trim($mealPlan);
        if ($trimmed === '') {
            return 'room only';
        }

        if (!str_contains($trimmed, '_')) {
            return $trimmed;
        }

        $map = [
            'room_only' => 'room only',
            'bed_&_breakfast' => 'bed & breakfast',
            'half_board_breakfast_lunch' => 'room with breakfast + lunch',
            'half_board_breakfast_dinner' => 'room with breakfast + dinner',
            'half_board_lunch_dinner' => 'room with lunch + dinner',
            'all_inclusive' => 'room with all meals (breakfast + lunch + dinner)',
            'lunch_only' => 'room with lunch',
            'dinner_only' => 'room with dinner',
        ];

        $lower = strtolower($trimmed);

        return $map[$lower] ?? str_replace('_', ' ', $lower);
    }

    /**
     * Each hotel has exactly one base room: rooms.base_room = 1.
     * Prefer the copy owned by the tour DMC (matches fetch-rooms on edit).
     */
    protected function resolveBaseRoom($hotelId, ?int $dmcId, ?int $createdBy = null): ?Room
    {
        if (empty($hotelId)) {
            return null;
        }

        $baseQuery = Room::query()
            ->whereIn('hotel_id', $this->normalizeHotelIds($hotelId))
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->where(function ($q) {
                $q->where('base_room', 1)
                    ->orWhere('base_room', true)
                    ->orWhere('base_room', '1');
            });

        foreach (array_filter(array_unique([$createdBy, $dmcId])) as $ownerId) {
            $room = (clone $baseQuery)
                ->where('created_by', $ownerId)
                ->orderBy('room_id')
                ->first();
            if ($room) {
                return $room;
            }
        }

        return $baseQuery->orderBy('room_id')->first();
    }

    /**
     * Accept hotel_id as string or int (PostgreSQL/MySQL safe).
     */
    protected function normalizeHotelIds($hotelId): array
    {
        $ids = [(string) $hotelId];
        if (is_numeric($hotelId)) {
            $ids[] = (int) $hotelId;
        }

        return array_values(array_unique($ids));
    }

    /**
     * Auto price: base room rate + meal add-ons for the resolved meal plan.
     */
    protected function calculateHotelTotalPrice(
        Room $room,
        int $nights,
        int $numberOfRooms,
        int $adults,
        string $occupancy,
        string $mealPlan
    ): float {
        $roomRate = $occupancy === 'single'
            ? (float) ($room->weekday_price ?? 0)
            : (float) ($room->double_weekday_price ?? $room->weekday_price ?? 0);

        $total = $roomRate * $nights * $numberOfRooms;
        $plan = strtolower($mealPlan);

        $includesBreakfast = str_contains($plan, 'breakfast')
            || str_contains($plan, 'bed_&_')
            || str_contains($plan, 'half_board')
            || str_contains($plan, 'all_inclusive');
        $includesLunch = str_contains($plan, 'lunch') || str_contains($plan, 'all_inclusive');
        $includesDinner = str_contains($plan, 'dinner') || str_contains($plan, 'all_inclusive');

        if ($includesBreakfast) {
            $total += (float) ($room->breakfast_price ?? 0) * $adults * $nights * $numberOfRooms;
        }
        if ($includesLunch) {
            $total += (float) ($room->lunch_price ?? 0) * $adults * $nights * $numberOfRooms;
        }
        if ($includesDinner) {
            $total += (float) ($room->dinner_price ?? 0) * $adults * $nights * $numberOfRooms;
        }

        return round($total, 2);
    }

    /**
     * First active bed for the resolved base room.
     */
    protected function resolveFirstBed(Room $room): ?Bed
    {
        return Bed::where('room_id', $room->room_id)
            ->where(function ($q) {
                $q->where('is_active', 1)->orWhereNull('is_active');
            })
            ->orderBy('bed_id')
            ->first();
    }

    /**
     * Pick meal plan from payload first, then fall back to base room meal flags.
     */
    protected function resolveHotelMealPlan(array $item, ?Room $room): string
    {
        $mealPlan = trim((string) ($item['meal_plan'] ?? ''));
        if ($mealPlan !== '') {
            return $this->normalizeMealPlanValue($mealPlan);
        }

        $mealTypes = $item['meal_types'] ?? null;
        if (is_string($mealTypes) && $mealTypes !== '') {
            $mealTypes = array_map('trim', explode(',', $mealTypes));
        }
        if (is_array($mealTypes) && $mealTypes !== []) {
            return $this->mealPlanFromMealLabels($mealTypes);
        }

        $mealType = trim((string) ($item['meal_type'] ?? ''));
        if ($mealType !== '') {
            return $this->mealPlanFromMealLabels(array_map('trim', explode(',', $mealType)));
        }

        if ($room) {
            return $this->mealPlanFromRoom($room);
        }

        return 'room_only';
    }

    protected function mealPlanFromMealLabels(array $labels): string
    {
        $normalized = array_map(static fn ($label) => strtolower(trim((string) $label)), array_filter($labels));
        $hasBreakfast = $this->labelsIncludeMeal($normalized, 'breakfast');
        $hasLunch = $this->labelsIncludeMeal($normalized, 'lunch');
        $hasDinner = $this->labelsIncludeMeal($normalized, 'dinner');

        if (!$hasBreakfast && !$hasLunch && !$hasDinner) {
            return 'room_only';
        }
        if ($hasBreakfast && $hasLunch && $hasDinner) {
            return 'all_inclusive';
        }
        if ($hasBreakfast && $hasLunch) {
            return 'half_board_breakfast_lunch';
        }
        if ($hasBreakfast && $hasDinner) {
            return 'half_board_breakfast_dinner';
        }
        if ($hasLunch && $hasDinner) {
            return 'half_board_lunch_dinner';
        }
        if ($hasBreakfast) {
            return 'bed_&_breakfast';
        }
        if ($hasLunch) {
            return 'lunch_only';
        }

        return 'dinner_only';
    }

    protected function mealPlanFromRoom(Room $room): string
    {
        $labels = [];
        if ((int) ($room->breakfast ?? 0) === 1 || filter_var($room->breakfast_included ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $labels[] = 'breakfast';
        }
        if ((int) ($room->lunch ?? 0) === 1) {
            $labels[] = 'lunch';
        }
        if ((int) ($room->dinner ?? 0) === 1) {
            $labels[] = 'dinner';
        }

        if ($labels === [] && (int) ($room->rooms_only ?? 0) === 1) {
            return 'room_only';
        }

        return $this->mealPlanFromMealLabels($labels ?: ['room only']);
    }

    protected function labelsIncludeMeal(array $labels, string $meal): bool
    {
        foreach ($labels as $label) {
            if ($label === $meal || str_contains($label, $meal)) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeMealPlanValue(string $mealPlan): string
    {
        $lower = strtolower(trim($mealPlan));
        $directMap = [
            'room only' => 'room_only',
            'room_only' => 'room_only',
            'bed & breakfast' => 'bed_&_breakfast',
            'bed and breakfast' => 'bed_&_breakfast',
            'bed_&_breakfast' => 'bed_&_breakfast',
            'room with breakfast' => 'bed_&_breakfast',
            'room with lunch' => 'lunch_only',
            'room with dinner' => 'dinner_only',
            'room with breakfast + lunch' => 'half_board_breakfast_lunch',
            'room with breakfast + dinner' => 'half_board_breakfast_dinner',
            'room with lunch + dinner' => 'half_board_lunch_dinner',
            'room with all meals (breakfast + lunch + dinner)' => 'all_inclusive',
            'all inclusive' => 'all_inclusive',
        ];

        if (isset($directMap[$lower])) {
            return $directMap[$lower];
        }

        return $this->normalizeMealPlan($mealPlan);
    }

    protected function transformAttractionItem(Tour $tour, array $item, array $meta, array $customer): array
    {
        $attractionId = $item['attraction_id'] ?? $item['AttractionId'] ?? null;
        $attraction = $attractionId ? Attraction::where('attraction_id', $attractionId)->first() : null;

        $name = $item['name'] ?? $item['AttractionName'] ?? ($attraction->name ?? 'Attraction');
        $tickets = $item['ticket_mapping'] ?? [];
        $firstTicket = is_array($tickets) && isset($tickets[0]) ? $tickets[0] : [];
        $ticketId = $firstTicket['ticket_id'] ?? $item['ticketId'] ?? null;
        $ticketName = $firstTicket['ticket_name'] ?? $item['ticketName'] ?? 'General Ticket';
        $bookingDate = $this->parseDate($meta['bookingDate'] ?? $tour->check_in_time, Carbon::today())->toDateString();
        $price = (float) ($item['price'] ?? $item['totalPrice'] ?? 0);
        $transfer = $this->mapTransferOptions(is_array($item['transfer'] ?? null) ? $item['transfer'] : []);

        return array_merge($customer, [
            'bookingDate' => $bookingDate,
            'visitTime' => $item['visitTime'] ?? $item['time_slot'] ?? '10:00 AM',
            'adultCount' => max(1, (int) ($item['adultCount'] ?? $tour->adult ?: 1)),
            'childCount' => max(0, (int) ($item['childCount'] ?? $tour->child)),
            'seniorCount' => max(0, (int) ($item['seniorCount'] ?? 0)),
            'AttractionId' => $attraction?->attraction_id ?? $attractionId,
            'AttractionName' => $name,
            'ticketId' => $ticketId,
            'ticketName' => $ticketName,
            'ticket_details' => [
                'adult_price' => $price,
                'child_price' => 0,
                'senior_price' => 0,
                'description' => '',
                'nri' => 'residential',
            ],
            'Selection' => 'withoutTransport',
            'mode' => 'dmc',
            'totalPrice' => $price,
            'price' => $price,
            'prices' => ['price' => $price],
            'dmc_id' => $tour->dmc_id,
            'created_by_dmc' => $tour->dmc_id,
            'transfer_options' => $transfer,
            'guide_options' => ($item['guide_required'] ?? 'No') === 'Yes'
                ? ['guide_required' => true]
                : null,
            'remarks' => $item['remarks'] ?? null,
            'supplement' => filter_var($item['supplement'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    protected function transformRestaurantItem(Tour $tour, array $item, array $meta, array $customer): array
    {
        $restaurantId = $item['restaurant_id'] ?? $item['restaurantId'] ?? null;
        $restaurant = $restaurantId ? Restaurant::where('restaurant_id', $restaurantId)->first() : null;

        $name = $item['restaurant_name'] ?? $item['name'] ?? ($restaurant->name ?? 'Restaurant');
        $mealConfig = is_array($item['meal_configuration'] ?? null) ? $item['meal_configuration'] : [];
        $mealType = $mealConfig['meal_type'] ?? $item['meal_type'] ?? $item['mealType'] ?? '';
        $dish = $mealConfig['dish'] ?? $item['dish'] ?? $item['mealSpecificType'] ?? '';
        $timeSlot = $mealConfig['time_slot'] ?? $item['time_slot'] ?? $item['visitTime'] ?? '12:00 PM';
        $bookingDate = $this->parseDate($meta['bookingDate'] ?? $tour->check_in_time, Carbon::today())->toDateString();
        $price = (float) ($item['price'] ?? $item['totalPrice'] ?? $item['mealPrice'] ?? 0);
        $transfer = $this->mapTransferOptions(is_array($item['transfer'] ?? null) ? $item['transfer'] : []);

        return array_merge($customer, [
            'bookingDate' => $bookingDate,
            'visitTime' => $timeSlot,
            'adultCount' => max(1, (int) ($item['adultCount'] ?? $tour->adult ?: 1)),
            'childCount' => max(0, (int) ($item['childCount'] ?? $tour->child)),
            'restaurantId' => $restaurant?->restaurant_id ?? $restaurantId,
            'restaurantName' => $name,
            'mealType' => $this->normalizeMealTypeLabel($mealType),
            'mealSpecificType' => $dish !== '' ? $dish : null,
            'MealDescription' => [[
                'item_name' => $dish !== '' ? $dish : 'Menu Item',
                'name' => $dish !== '' ? $dish : 'Menu Item',
                'price' => $price,
                'meal_id' => $restaurant?->restaurant_id ?? $restaurantId,
                'quantity' => 1,
            ]],
            'totalPrice' => $price,
            'mealPrice' => $price,
            'priceTypes' => ['dmc'],
            'dmc_id' => (string) ($tour->dmc_id ?? ''),
            'transfer_options' => $transfer,
            'remarks' => $item['remarks'] ?? null,
            'supplement' => filter_var($item['supplement'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Convert external transfer blocks into editform-compatible transfer_options.
     */
    protected function mapTransferOptions(array $transfer, array $fallback = []): ?array
    {
        $requiredRaw = $transfer['required'] ?? $fallback['required'] ?? false;
        $required = filter_var($requiredRaw, FILTER_VALIDATE_BOOLEAN)
            || (is_string($requiredRaw) && strtolower($requiredRaw) === 'yes');

        if (!$required && empty($transfer) && empty($fallback['required'])) {
            return null;
        }

        $typeRaw = $transfer['type'] ?? $transfer['transfer_type'] ?? $fallback['type'] ?? 'private';
        $type = ucfirst(strtolower((string) $typeRaw));
        if (!in_array($type, ['Private', 'Shared', 'Sic'], true)) {
            $type = 'Private';
        }

        $wayRaw = $transfer['way'] ?? 'One Way';
        $way = in_array($wayRaw, ['Two Way', 'Return'], true) ? 'Two Way' : 'One Way';

        return [
            'transfer_required' => $required,
            'type' => $type,
            'way' => $way,
            'vehicle_id' => $transfer['vehicle_id'] ?? '',
            'vehicle_name' => $transfer['vehicle_name'] ?? '',
            'pickup_location_name' => $transfer['pickup_location_label']
                ?? $fallback['pickup_label']
                ?? $transfer['pickup_location']
                ?? $fallback['pickup_location']
                ?? '',
            'destination' => $transfer['drop_location_label']
                ?? $fallback['drop_label']
                ?? $transfer['drop_location']
                ?? $fallback['drop_location']
                ?? '',
            'pickup_location_id' => $transfer['pickup_location_id'] ?? '',
            'cost' => (float) ($transfer['cost'] ?? $transfer['price'] ?? 0),
            'price' => (float) ($transfer['cost'] ?? $transfer['price'] ?? 0),
            'passengers' => $transfer['passengers'] ?? null,
            'pickup_time' => $transfer['pickup_time'] ?? '',
            'city' => $transfer['city'] ?? $fallback['city'] ?? '',
        ];
    }

    protected function normalizeMealPlan(string $mealPlan): string
    {
        $value = strtolower(trim(str_replace(['&', '-'], ['_', '_'], $mealPlan)));
        $map = [
            'room only' => 'room_only',
            'room_only' => 'room_only',
            'bed & breakfast' => 'bed_&_breakfast',
            'bed and breakfast' => 'bed_&_breakfast',
            'bed_&_breakfast' => 'bed_&_breakfast',
        ];

        if (isset($map[$value])) {
            return $map[$value];
        }

        if ($value !== '' && strpos($value, ' ') !== false) {
            return strtolower(preg_replace('/\s+/', '_', $value));
        }

        return $value;
    }

    protected function normalizeMealTypeLabel(string $mealType): string
    {
        $lower = strtolower(trim($mealType));
        return match ($lower) {
            'breakfast' => 'Breakfast',
            'lunch' => 'Lunch',
            'dinner' => 'Dinner',
            '' => 'Lunch',
            default => ucfirst($lower),
        };
    }
    /**
     * Send the tour proposal email to the agent. Non-fatal by design.
     * Temporarily authenticates the agent so CommonHelper::getDmcId() (which
     * reads Auth::user() internally) works on this unauthenticated endpoint.
     */
    /**
     * Email the sender using sender_email from the external payload.
     *
     * @return array{sent: bool, email: ?string}
     */
    protected function notifySender(Tour $tour, array $payload, Collection $orders): array
    {
        $senderEmail = $this->resolveSenderNotificationEmail($payload);

        if ($senderEmail === null) {
            Log::info('External API: skipping sender auto-book email, no valid sender_email', [
                'tour_id' => $tour->tour_id,
            ]);

            return ['sent' => false, 'email' => null];
        }

        $primaryDmc = $this->resolvePrimaryDmc($payload);
        $dmcUser = $this->resolveDmcUser($payload, $primaryDmc);
        $agent = $tour->agent_id ? Agent::where('agent_id', $tour->agent_id)->first() : null;
        $agency = $agent && $agent->agency_id
            ? Agency::where('agency_id', $agent->agency_id)->first()
            : null;

        $senderName = ucfirst(explode('@', $senderEmail)[0]);
        $dmcName = $dmcUser
            ? trim((string) ($dmcUser->company_name ?: $dmcUser->name ?: 'DMC'))
            : 'DMC';

        try {
            $availability = $this->resolvePackageAvailability($payload);
            $bookedServices = $this->buildBookedServicesForEmail($orders);
            $totalEstimation = round(array_sum(array_map(
                static fn (array $service): float => (float) ($service['price_value'] ?? 0),
                $bookedServices
            )), 2);

            $sent = CommonHelper::sendTourAutoBookedDmcEmail($senderEmail, [
                'dmc_name' => $senderName,
                'dmc_logo' => $this->resolveDmcLogoForEmail($dmcUser, $payload),
                'tour_display_id' => $tour->display_id,
                'country' => $this->resolveDayLevelCountry($payload, $primaryDmc),
                'diff' => $availability['diff'],
                'requested_days' => $availability['requested_days'],
                'available_days' => $availability['available_days'],
                'is_partial_package' => $availability['is_partial'],
                'partial_package_message' => $availability['partial_message'],
                'cities' => $this->resolveDayLevelCities($payload),
                'destination' => $tour->destination,
                'city' => $tour->city,
                'check_in_time' => $tour->check_in_time,
                'check_out_time' => $tour->check_out_time,
                'adult' => $tour->adult,
                'child' => $tour->child,
                'infant' => $tour->infant,
                'agent_name' => $agent->name ?? '',
                'agency_name' => $agency->agency_name ?? '',
                'dmc_label' => $dmcName,
                'dmc_contact_email' => $this->resolveDmcContactEmail($payload, $primaryDmc, $dmcUser),
                'booked_at' => now()->format('M d, Y H:i'),
                'booked_services' => $bookedServices,
                'total_estimation' => $totalEstimation,
                'currency_code' => $this->resolveItineraryCurrency($payload, $primaryDmc),
            ]);

            if ($sent !== true) {
                Log::warning('External API sender auto-book email not sent', [
                    'tour_id' => $tour->tour_id,
                    'sender_email' => $senderEmail,
                    'reason' => $sent,
                ]);

                return ['sent' => false, 'email' => $senderEmail];
            }

            return ['sent' => true, 'email' => $senderEmail];
        } catch (Throwable $e) {
            Log::error('External API sender auto-book email failed', [
                'tour_id' => $tour->tour_id,
                'sender_email' => $senderEmail,
                'error' => $e->getMessage(),
            ]);

            return ['sent' => false, 'email' => $senderEmail];
        }
    }

    protected function resolveSenderNotificationEmail(array $payload): ?string
    {
        $email = trim((string) $this->payloadValue($payload, ['sender_email', 'senderEmail'], ''));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        return null;
    }

    protected function resolveDmcContactEmail(array $payload, array $primaryDmc, ?User $dmcUser): ?string
    {
        $candidates = [
            $payload['DMC_email'] ?? null,
            $payload['dmc_email'] ?? null,
            $primaryDmc['DMC_email'] ?? null,
        ];

        foreach ($payload['destinations'] ?? [] as $destination) {
            if (! is_array($destination)) {
                continue;
            }
            foreach ($destination['DMC'] ?? [] as $dmc) {
                if (is_array($dmc) && ! empty($dmc['DMC_email'])) {
                    $candidates[] = $dmc['DMC_email'];
                }
            }
        }

        if ($dmcUser) {
            $candidates[] = $dmcUser->email;
        }

        foreach ($candidates as $email) {
            $email = trim((string) $email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return null;
    }

    protected function resolveDmcLogoForEmail(?User $dmcUser, array $payload): ?string
    {
        $candidates = [];

        if ($dmcUser && ! empty($dmcUser->logo)) {
            $candidates[] = (string) $dmcUser->logo;
        }

        $masterId = $this->payloadValue($payload, ['Master_DMC_id', 'master_dmc_id']);
        if (! empty($masterId)) {
            $masterUser = User::where('userId', $masterId)->first();
            if ($masterUser && ! empty($masterUser->logo)) {
                $candidates[] = (string) $masterUser->logo;
            }
        }

        if ($dmcUser && ! empty($dmcUser->created_by)) {
            $parentUser = User::where('userId', $dmcUser->created_by)->first();
            if ($parentUser && ! empty($parentUser->logo)) {
                $candidates[] = (string) $parentUser->logo;
            }
        }

        foreach ($candidates as $logo) {
            $absolute = CommonHelper::resolveEmailLogoUrl($logo);
            if ($absolute !== null) {
                return $absolute;
            }
        }

        return null;
    }

    /**
     * @return array{diff: int, requested_days: int, available_days: int, is_partial: bool, partial_message: ?string}
     */
    protected function resolvePackageAvailability(array $payload): array
    {
        $requestedDays = (int) ($this->payloadValue($payload, ['requested_days', 'requestedDays'], 0) ?: 0);
        $availableDays = $this->extractAvailablePackageDays($payload);
        $diff = (int) ($this->payloadValue($payload, ['diff'], null) ?? ($availableDays - $requestedDays));
        $isPartial = $diff < 0;

        $partialMessage = null;
        if ($isPartial && $availableDays > 0) {
            $dayLabel = $availableDays === 1 ? '1 day' : $availableDays . ' days';
            $partialMessage = 'You requested a ' . $requestedDays . '-day tour. We have a ' . $dayLabel
                . ' package available. Our team will connect with you.';
        }

        return [
            'diff' => $diff,
            'requested_days' => $requestedDays,
            'available_days' => $availableDays,
            'is_partial' => $isPartial,
            'partial_message' => $partialMessage,
        ];
    }

    protected function extractAvailablePackageDays(array $payload): int
    {
        $maxDays = 0;

        foreach ($payload['destinations'] ?? [] as $destination) {
            if (! is_array($destination)) {
                continue;
            }
            foreach ($destination['DMC'] ?? [] as $dmc) {
                if (! is_array($dmc)) {
                    continue;
                }
                foreach ($dmc['packages'] ?? [] as $package) {
                    if (! is_array($package)) {
                        continue;
                    }
                    $totalDays = (int) ($package['total_days'] ?? 0);
                    $dayCount = count($package['days'] ?? []);
                    $maxDays = max($maxDays, $totalDays > 0 ? $totalDays : $dayCount);
                }
            }
        }

        return $maxDays;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildBookedServicesForEmail(Collection $orders): array
    {
        $services = [];

        foreach ($orders as $order) {
            $data = $this->orderDataRow($order);
            $typeKey = strtolower((string) ($order->type ?? 'service'));
            $dayNum = $data['external_day'] ?? $data['day'] ?? null;

            if (! in_array($typeKey, ['entry_port', 'exit_port'], true)) {
                $transferCard = $this->buildTransferItineraryFromOptions($typeKey, $data, $dayNum);
                if ($transferCard !== null) {
                    $services[] = $transferCard;
                }
            }

            $services[] = $this->buildItineraryCardForOrder($typeKey, $data, $dayNum);
        }

        usort($services, static function (array $a, array $b): int {
            $dayCmp = ($a['_sort_day'] ?? 999) <=> ($b['_sort_day'] ?? 999);
            if ($dayCmp !== 0) {
                return $dayCmp;
            }

            $dateCmp = strcmp((string) ($a['_sort_date'] ?? ''), (string) ($b['_sort_date'] ?? ''));
            if ($dateCmp !== 0) {
                return $dateCmp;
            }

            return ($a['_sort_order'] ?? 0) <=> ($b['_sort_order'] ?? 0);
        });

        return array_map(static function (array $service): array {
            unset($service['_sort_day'], $service['_sort_date'], $service['_sort_order']);

            return $service;
        }, $services);
    }

    protected function resolveItineraryCurrency(array $payload, array $primaryDmc): string
    {
        $candidates = [
            $payload['currency'] ?? null,
            $payload['currency_code'] ?? null,
            $primaryDmc['currency'] ?? null,
        ];

        foreach ($candidates as $code) {
            $code = strtoupper(trim((string) $code));
            if ($code !== '') {
                return $code;
            }
        }

        $country = strtolower($this->resolveDayLevelCountry($payload, $primaryDmc));

        return match (true) {
            str_contains($country, 'singapore') => 'SGD',
            str_contains($country, 'india') => 'INR',
            str_contains($country, 'thailand') => 'THB',
            str_contains($country, 'malaysia') => 'MYR',
            str_contains($country, 'indonesia') => 'IDR',
            str_contains($country, 'united arab') || str_contains($country, 'dubai') => 'AED',
            default => 'USD',
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildItineraryCardForOrder(string $typeKey, array $data, $dayNum): array
    {
        $badge = $this->formatItineraryBadge($typeKey);
        $name = $this->resolveOrderServiceName($typeKey, $data);
        $date = $this->formatServiceBookingDateForEmail($typeKey, $data);
        $time = $this->resolveServiceTimeForEmail($typeKey, $data);
        $pax = $this->resolveServicePaxLabel($typeKey, $data);
        $priceValue = $this->resolveOrderPrice($data);
        $lines = $this->buildItineraryLinesForOrder($typeKey, $data);

        return [
            'badge' => $badge,
            'accent' => $this->itineraryAccentColor($typeKey),
            'type' => $this->formatOrderTypeLabel($typeKey),
            'title' => $name,
            'subtitle' => $this->resolveItinerarySubtitle($typeKey, $data),
            'name' => $name,
            'day' => $dayNum !== null && $dayNum !== '' ? 'Day ' . (int) $dayNum : null,
            'date' => $date,
            'time' => $time,
            'pax' => $pax,
            'details' => $this->buildServiceDetailsForEmail($typeKey, $data),
            'lines' => $lines,
            'price_value' => $priceValue,
            'price' => $priceValue > 0 ? number_format($priceValue, 2) : null,
            '_sort_day' => is_numeric($dayNum) ? (int) $dayNum : 999,
            '_sort_date' => $this->resolveServiceSortDate($typeKey, $data),
            '_sort_order' => 2,
        ];
    }

    /**
     * @return ?array<string, mixed>
     */
    protected function buildTransferItineraryFromOptions(string $parentType, array $data, $dayNum): ?array
    {
        $transfer = $data['transfer_options'] ?? null;
        if (! is_array($transfer) || empty($transfer['transfer_required'])) {
            return null;
        }

        $pickup = trim((string) (
            $transfer['pickup_location_name']
            ?? $transfer['pickup_location']
            ?? $transfer['pickup']
            ?? ''
        ));
        $dropoff = trim((string) (
            $transfer['destination']
            ?? $transfer['drop_location_name']
            ?? $transfer['dropoff']
            ?? ''
        ));

        if ($pickup === '' && $dropoff === '') {
            return null;
        }

        $transferType = trim((string) ($transfer['type'] ?? 'Private'));
        $vehicle = trim((string) ($transfer['vehicle_name'] ?? ''));
        $priceValue = (float) ($transfer['price'] ?? $transfer['cost'] ?? 0);
        $time = trim((string) ($transfer['pickup_time'] ?? ''));
        $passengers = $transfer['passengers'] ?? null;

        $lines = array_values(array_filter([
            $pickup !== '' ? ['label' => 'From', 'value' => $pickup] : null,
            $dropoff !== '' ? ['label' => 'To', 'value' => $dropoff] : null,
            $transferType !== '' ? ['label' => 'Transfer type', 'value' => $transferType . ' Transfer'] : null,
            $vehicle !== '' ? ['label' => 'Vehicle', 'value' => $vehicle] : null,
            ! empty($transfer['way']) ? ['label' => 'Way', 'value' => (string) $transfer['way']] : null,
            $passengers ? ['label' => 'Passengers', 'value' => (string) $passengers] : null,
            $parentType === 'hotel' ? ['label' => 'Linked to', 'value' => 'Hotel arrival / departure'] : null,
        ]));

        return [
            'badge' => 'ARRIVAL',
            'accent' => '#3b82f6',
            'type' => 'Transfer',
            'title' => 'Transfer Service',
            'subtitle' => 'Shared / private transfer for your package',
            'name' => 'Transfer Service',
            'day' => $dayNum !== null && $dayNum !== '' ? 'Day ' . (int) $dayNum : null,
            'date' => $this->formatServiceBookingDateForEmail($parentType, $data),
            'time' => $time !== '' ? $time : null,
            'pax' => $this->formatPaxLabel($passengers, $data),
            'details' => null,
            'lines' => $lines,
            'price_value' => $priceValue,
            'price' => $priceValue > 0 ? number_format($priceValue, 2) : null,
            '_sort_day' => is_numeric($dayNum) ? (int) $dayNum : 999,
            '_sort_date' => $this->resolveServiceSortDate($parentType, $data),
            '_sort_order' => 1,
        ];
    }

    protected function formatItineraryBadge(string $type): string
    {
        return match ($type) {
            'entry_port' => 'ARRIVAL',
            'exit_port' => 'DEPARTURE',
            'hotel' => 'HOTEL',
            'guide' => 'GUIDE',
            'restaurant' => 'RESTAURANT',
            'attraction' => 'ATTRACTION',
            'vehicle', 'transfer', 'travel_point', 'travel_hourly', 'local_transport' => 'TRANSFER',
            default => strtoupper(str_replace('_', ' ', $type)),
        };
    }

    protected function itineraryAccentColor(string $type): string
    {
        return match ($type) {
            'entry_port' => '#3b82f6',
            'exit_port' => '#6366f1',
            'hotel' => '#f59e0b',
            'guide' => '#06b6d4',
            'restaurant' => '#10b981',
            'attraction' => '#ec4899',
            default => '#8b5cf6',
        };
    }

    protected function resolveItinerarySubtitle(string $type, array $data): ?string
    {
        return match ($type) {
            'hotel' => 'Accommodation',
            'guide' => 'Professional tour guide service',
            'restaurant' => 'Dining experience',
            'attraction' => 'Sightseeing & tickets',
            'entry_port' => 'Airport / port arrival transfer',
            'exit_port' => 'Departure transfer',
            'vehicle', 'transfer', 'travel_point', 'travel_hourly', 'local_transport' => 'Transfer service',
            default => null,
        };
    }

    protected function resolveOrderPrice(array $data): float
    {
        foreach ([
            $data['totalPrice'] ?? null,
            $data['price'] ?? null,
            $data['mealPrice'] ?? null,
            $data['prices']['price'] ?? null,
            $data['total_price'] ?? null,
        ] as $candidate) {
            if (is_numeric($candidate) && (float) $candidate > 0) {
                return round((float) $candidate, 2);
            }
        }

        return 0.0;
    }

    protected function resolveServiceTimeForEmail(string $type, array $data): ?string
    {
        $candidates = match ($type) {
            'hotel' => [
                $data['hotelDetails']['checkInTime'] ?? null,
                $data['check_in_time'] ?? null,
            ],
            'restaurant', 'attraction' => [
                $data['visitTime'] ?? null,
                $data['time_slot'] ?? null,
            ],
            'guide' => [
                $data['pickup_time'] ?? null,
                $data['visitTime'] ?? null,
                $data['time'] ?? null,
            ],
            'entry_port', 'exit_port', 'vehicle', 'transfer', 'travel_point', 'travel_hourly', 'local_transport' => [
                $data['entrytime'] ?? null,
                $data['time'] ?? null,
                $data['pickup_time'] ?? null,
                $data['transfer_options']['pickup_time'] ?? null,
            ],
            default => [$data['visitTime'] ?? null, $data['time'] ?? null],
        };

        foreach ($candidates as $time) {
            $time = trim((string) $time);
            if ($time !== '') {
                return $time;
            }
        }

        return null;
    }

    protected function resolveServicePaxLabel(string $type, array $data): ?string
    {
        if (in_array($type, ['entry_port', 'exit_port', 'vehicle', 'transfer', 'travel_point', 'travel_hourly', 'local_transport'], true)) {
            return $this->formatPaxLabel($data['passengers'] ?? null, $data);
        }

        $adults = (int) ($data['adultCount'] ?? $data['adults'] ?? $data['selected_persons'] ?? 0);
        $children = (int) ($data['childCount'] ?? $data['children'] ?? 0);

        if ($type === 'hotel') {
            $room = is_array($data['rooms'][0] ?? null) ? $data['rooms'][0] : [];
            $bed = is_array($room['beds'][0] ?? null) ? $room['beds'][0] : [];
            $headCount = (int) ($bed['head_count'] ?? $room['selected_persons'] ?? 0);
            if ($headCount > 0) {
                return $headCount . ' pax';
            }
        }

        if ($adults > 0 || $children > 0) {
            $parts = [];
            if ($adults > 0) {
                $parts[] = $adults . ' adult' . ($adults > 1 ? 's' : '');
            }
            if ($children > 0) {
                $parts[] = $children . ' child' . ($children > 1 ? 'ren' : '');
            }

            return implode(', ', $parts);
        }

        return null;
    }

    protected function formatPaxLabel($passengers, array $data): ?string
    {
        if (is_numeric($passengers) && (int) $passengers > 0) {
            return (int) $passengers . ' pax';
        }

        $adults = (int) ($data['adultCount'] ?? $data['adults'] ?? 0);
        if ($adults > 0) {
            return $adults . ' pax';
        }

        return null;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    protected function buildItineraryLinesForOrder(string $type, array $data): array
    {
        $lines = [];

        switch ($type) {
            case 'hotel':
                $hotelDetails = is_array($data['hotelDetails'] ?? null) ? $data['hotelDetails'] : [];
                $room = is_array($data['rooms'][0] ?? null) ? $data['rooms'][0] : [];
                $bed = is_array($room['beds'][0] ?? null) ? $room['beds'][0] : [];

                if (! empty($hotelDetails['location'])) {
                    $lines[] = ['label' => 'Location', 'value' => (string) $hotelDetails['location']];
                }
                if (is_array($data['bookingDate'] ?? null) && count($data['bookingDate']) >= 2) {
                    $checkIn = $this->parseDate($data['bookingDate'][0], Carbon::today())->format('M d, Y');
                    $checkOut = $this->parseDate($data['bookingDate'][1], Carbon::today())->format('M d, Y');
                    $lines[] = ['label' => 'Check-in', 'value' => $checkIn];
                    $lines[] = ['label' => 'Check-out', 'value' => $checkOut];
                    $nights = max(1, $this->parseDate($data['bookingDate'][0], Carbon::today())
                        ->diffInDays($this->parseDate($data['bookingDate'][1], Carbon::today())));
                    $lines[] = ['label' => 'Nights', 'value' => (string) $nights];
                }
                if (! empty($hotelDetails['checkInTime'])) {
                    $lines[] = ['label' => 'Hotel check-in time', 'value' => (string) $hotelDetails['checkInTime']];
                }
                if (! empty($hotelDetails['checkOutTime'])) {
                    $lines[] = ['label' => 'Hotel check-out time', 'value' => (string) $hotelDetails['checkOutTime']];
                }
                if (! empty($room['room_type'])) {
                    $roomLabel = (string) $room['room_type'];
                    if (! empty($room['number_of_rooms'])) {
                        $roomLabel = 'Room 1: ' . $roomLabel;
                    }
                    $lines[] = ['label' => 'Room', 'value' => $roomLabel];
                }
                if (! empty($room['number_of_rooms'])) {
                    $count = (int) $room['number_of_rooms'];
                    $lines[] = ['label' => 'Rooms', 'value' => $count . ' room' . ($count > 1 ? 's' : '')];
                }
                if (! empty($bed['bed_type'])) {
                    $bedLine = (string) $bed['bed_type'];
                    if (! empty($bed['head_count'])) {
                        $bedLine .= ' (' . (int) $bed['head_count'] . ' pax)';
                    }
                    $lines[] = ['label' => 'Bed', 'value' => $bedLine];
                }
                $meal = $bed['selectedMeals']['meal_1']['type'] ?? ($bed['mealTypes'][0] ?? null);
                if (is_string($meal) && trim($meal) !== '') {
                    $lines[] = ['label' => 'Meal plan', 'value' => ucwords(str_replace('_', ' ', trim($meal)))];
                }
                break;

            case 'attraction':
                if (! empty($data['ticketName'])) {
                    $lines[] = ['label' => 'Ticket', 'value' => (string) $data['ticketName']];
                }
                if (! empty($data['visitTime'])) {
                    $lines[] = ['label' => 'Visit time', 'value' => (string) $data['visitTime']];
                }
                if (! empty($data['Selection'])) {
                    $lines[] = ['label' => 'Transport', 'value' => (string) $data['Selection']];
                }
                break;

            case 'restaurant':
                if (! empty($data['mealType'])) {
                    $lines[] = ['label' => 'Meal', 'value' => (string) $data['mealType']];
                }
                if (! empty($data['mealSpecificType'])) {
                    $lines[] = ['label' => 'Dish / menu', 'value' => (string) $data['mealSpecificType']];
                }
                if (! empty($data['visitTime'])) {
                    $lines[] = ['label' => 'Time', 'value' => (string) $data['visitTime']];
                }
                break;

            case 'guide':
                $languages = $data['languages'] ?? $data['guide_languages'] ?? $data['language'] ?? null;
                if (is_array($languages)) {
                    $languages = implode(', ', array_filter(array_map('strval', $languages)));
                }
                if (is_string($languages) && trim($languages) !== '') {
                    $lines[] = ['label' => 'Languages', 'value' => trim($languages)];
                }
                $duration = $data['duration'] ?? $data['hours'] ?? $data['custom_hours'] ?? null;
                if ($duration !== null && $duration !== '') {
                    $lines[] = ['label' => 'Duration', 'value' => is_numeric($duration) ? $duration . ' hour(s)' : (string) $duration];
                }
                if (! empty($data['pickup_time'])) {
                    $lines[] = ['label' => 'Pickup time', 'value' => (string) $data['pickup_time']];
                }
                break;

            case 'entry_port':
            case 'exit_port':
            case 'vehicle':
            case 'transfer':
            case 'travel_point':
            case 'travel_hourly':
            case 'local_transport':
                $pickup = trim((string) (
                    $data['entrypickup'] ?? $data['pickup'] ?? $data['pickup_location_name']
                    ?? ($data['transfer_options']['pickup_location_name'] ?? '')
                ));
                $dropoff = trim((string) (
                    $data['entrydropoff'] ?? $data['dropoff'] ?? $data['drop_location_name']
                    ?? ($data['transfer_options']['destination'] ?? '')
                ));
                if ($pickup !== '') {
                    $lines[] = ['label' => 'From', 'value' => $pickup];
                }
                if ($dropoff !== '') {
                    $lines[] = ['label' => 'To', 'value' => $dropoff];
                }
                $flightNo = $data['arrival_flight_no']
                    ?? ($data['entry_port_flight']['flight_no'] ?? null)
                    ?? ($data['flight_number'] ?? null);
                if ($flightNo) {
                    $lines[] = ['label' => 'Flight / transport no.', 'value' => (string) $flightNo];
                }
                $transferType = $data['type'] ?? $data['vehicles_name'] ?? ($data['transfer_options']['type'] ?? null);
                if ($transferType) {
                    $lines[] = ['label' => 'Transfer type', 'value' => (string) $transferType];
                }
                if (! empty($data['vehicles_name']) || ! empty($data['vehicle_name'])) {
                    $lines[] = ['label' => 'Vehicle', 'value' => (string) ($data['vehicles_name'] ?? $data['vehicle_name'])];
                }
                break;
        }

        if (! empty($data['remarks'])) {
            $lines[] = ['label' => 'Remarks', 'value' => (string) $data['remarks']];
        }

        return $lines;
    }

    protected function formatServiceBookingDateForEmail(string $type, array $data): ?string
    {
        $bookingDate = $data['bookingDate'] ?? null;

        if (is_array($bookingDate)) {
            $dates = array_values(array_filter($bookingDate, static fn ($d) => $d !== null && $d !== ''));
            if ($dates === []) {
                return null;
            }

            $checkIn = $this->parseDate($dates[0], Carbon::today());
            if (count($dates) >= 2 && $type === 'hotel') {
                $checkOut = $this->parseDate($dates[1], $checkIn->copy()->addDay());

                return $checkIn->format('M d, Y') . ' – ' . $checkOut->format('M d, Y');
            }

            return $checkIn->format('M d, Y');
        }

        if (is_string($bookingDate) && trim($bookingDate) !== '') {
            return $this->parseDate($bookingDate, Carbon::today())->format('M d, Y');
        }

        return null;
    }

    protected function resolveServiceSortDate(string $type, array $data): string
    {
        $bookingDate = $data['bookingDate'] ?? null;

        if (is_array($bookingDate)) {
            $dates = array_values(array_filter($bookingDate, static fn ($d) => $d !== null && $d !== ''));
            if ($dates !== []) {
                return $this->parseDate($dates[0], Carbon::today())->toDateString();
            }
        }

        if (is_string($bookingDate) && trim($bookingDate) !== '') {
            return $this->parseDate($bookingDate, Carbon::today())->toDateString();
        }

        return '';
    }

    protected function buildServiceDetailsForEmail(string $type, array $data): ?string
    {
        $parts = [];

        switch ($type) {
            case 'hotel':
                $hotelDetails = is_array($data['hotelDetails'] ?? null) ? $data['hotelDetails'] : [];
                $room = is_array($data['rooms'][0] ?? null) ? $data['rooms'][0] : [];
                $bed = is_array($room['beds'][0] ?? null) ? $room['beds'][0] : [];

                if (! empty($room['room_type'])) {
                    $parts[] = (string) $room['room_type'];
                }
                if (! empty($bed['bed_type'])) {
                    $parts[] = (string) $bed['bed_type'];
                }

                $meal = $bed['selectedMeals']['meal_1']['type']
                    ?? ($bed['mealTypes'][0] ?? null);
                if (is_string($meal) && trim($meal) !== '') {
                    $parts[] = ucwords(str_replace('_', ' ', trim($meal)));
                }

                if (! empty($room['number_of_rooms'])) {
                    $roomCount = (int) $room['number_of_rooms'];
                    $parts[] = $roomCount . ' room' . ($roomCount > 1 ? 's' : '');
                }

                if (is_array($data['bookingDate'] ?? null) && count($data['bookingDate']) >= 2) {
                    $checkIn = $this->parseDate($data['bookingDate'][0], Carbon::today());
                    $checkOut = $this->parseDate($data['bookingDate'][1], $checkIn->copy()->addDay());
                    $nights = max(1, $checkIn->diffInDays($checkOut));
                    $parts[] = $nights . ' night' . ($nights > 1 ? 's' : '');
                }

                if (! empty($hotelDetails['location'])) {
                    $parts[] = (string) $hotelDetails['location'];
                }
                break;

            case 'attraction':
                if (! empty($data['ticketName'])) {
                    $parts[] = (string) $data['ticketName'];
                }
                if (! empty($data['visitTime'])) {
                    $parts[] = (string) $data['visitTime'];
                }
                break;

            case 'restaurant':
                if (! empty($data['mealType'])) {
                    $parts[] = (string) $data['mealType'];
                }
                if (! empty($data['mealSpecificType'])) {
                    $parts[] = (string) $data['mealSpecificType'];
                }
                if (! empty($data['visitTime'])) {
                    $parts[] = (string) $data['visitTime'];
                }
                break;

            case 'vehicle':
            case 'transfer':
                if (! empty($data['pickup_location_name'])) {
                    $parts[] = 'From: ' . $data['pickup_location_name'];
                }
                if (! empty($data['drop_location_name'])) {
                    $parts[] = 'To: ' . $data['drop_location_name'];
                }
                if (! empty($data['vehicle_name'])) {
                    $parts[] = (string) $data['vehicle_name'];
                }
                break;
        }

        $parts = array_values(array_filter(array_map(
            static fn ($part) => trim((string) $part),
            $parts
        )));

        return $parts !== [] ? implode(' · ', $parts) : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function orderDataRow(Order $order): array
    {
        $raw = $order->data;
        if (! is_array($raw)) {
            return [];
        }
        $row = $raw[0] ?? $raw;

        return is_array($row) ? $row : [];
    }

    protected function formatOrderTypeLabel(string $type): string
    {
        return match ($type) {
            'hotel' => 'Hotel',
            'attraction' => 'Attraction',
            'restaurant' => 'Restaurant',
            'guide' => 'Guide',
            'vehicle', 'transfer' => 'Transfer',
            'port' => 'Port',
            'enquiry' => 'Enquiry',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    protected function resolveOrderServiceName(string $type, array $data): string
    {
        $hotelDetails = is_array($data['hotelDetails'] ?? null) ? $data['hotelDetails'] : [];

        $name = match ($type) {
            'hotel' => $hotelDetails['hotel_name']
                ?? $data['hotel_name']
                ?? $data['hotelName']
                ?? null,
            'attraction' => $data['AttractionName'] ?? $data['attraction_name'] ?? $data['name'] ?? null,
            'restaurant' => $data['restaurantName'] ?? $data['restaurant_name'] ?? $data['name'] ?? null,
            'guide' => $data['guide_name'] ?? $data['GuideName'] ?? $data['name'] ?? null,
            'vehicle', 'transfer' => $data['vehicle_name']
                ?? $data['pickup_location_name']
                ?? $data['transfer_type']
                ?? null,
            default => $data['service_name']
                ?? $data['name']
                ?? $data['AttractionName']
                ?? $data['restaurantName']
                ?? null,
        };

        if (is_string($name) && trim($name) !== '') {
            return trim($name);
        }

        if ($type === 'restaurant' && ! empty($data['items'][0])) {
            $item = $data['items'][0];
            $dish = $item['item_name'] ?? $item['name'] ?? null;
            if (is_string($dish) && trim($dish) !== '') {
                return trim($dish);
            }
        }

        return $this->formatOrderTypeLabel($type) . ' booking';
    }

    protected function resolveDayLevelCountry(array $payload, array $primaryDmc): string
    {
        $country = trim((string) ($payload['country'] ?? ''));
        if ($country !== '') {
            return $country;
        }

        $fromDmc = trim((string) ($primaryDmc['country'] ?? ''));
        if ($fromDmc !== '') {
            return $fromDmc;
        }

        return $this->resolveDestination($payload, $primaryDmc);
    }

    /**
     * @return list<string>
     */
    protected function resolveDayLevelCities(array $payload): array
    {
        if (isset($payload['city']) && is_array($payload['city'])) {
            return array_values(array_filter(array_map(
                static fn ($c) => trim((string) $c),
                $payload['city']
            )));
        }

        if (isset($payload['city']) && is_string($payload['city'])) {
            $parts = array_map('trim', explode(',', $payload['city']));

            return array_values(array_filter($parts));
        }

        $cities = [];
        foreach ($payload['destinations'] ?? [] as $destination) {
            if (! is_array($destination)) {
                continue;
            }
            foreach ($destination['DMC'] ?? [] as $dmc) {
                if (! is_array($dmc)) {
                    continue;
                }
                foreach ($dmc['cities'] ?? [] as $cityRow) {
                    $name = $this->extractCityName($cityRow);
                    if ($name !== '' && ! in_array($name, $cities, true)) {
                        $cities[] = $name;
                    }
                }
                foreach ($dmc['packages'] ?? [] as $package) {
                    if (! is_array($package)) {
                        continue;
                    }
                    foreach ($package['days'] ?? [] as $day) {
                        if (! is_array($day)) {
                            continue;
                        }
                        foreach ($this->itemsFrom($day['cities'] ?? []) as $cityRow) {
                            $name = $this->extractCityName($cityRow);
                            if ($name !== '' && ! in_array($name, $cities, true)) {
                                $cities[] = $name;
                            }
                        }
                        foreach ($this->itemsFrom($day['hotels'] ?? []) as $hotel) {
                            $name = trim((string) ($hotel['city'] ?? ''));
                            if ($name !== '' && ! in_array($name, $cities, true)) {
                                $cities[] = $name;
                            }
                        }
                    }
                }
            }
        }

        return $cities;
    }

    protected function extractCityName(mixed $cityRow): string
    {
        if (is_string($cityRow)) {
            return trim($cityRow);
        }
        if (is_array($cityRow)) {
            return trim((string) ($cityRow['city'] ?? $cityRow['name'] ?? ''));
        }

        return '';
    }

    protected function notifyAgent(Tour $tour): bool
    {
        if (empty($tour->agent_id)) {
            Log::info('External API: skipping proposal email, no agent linked to tour', [
                'tour_id' => $tour->tour_id,
            ]);
            return false;
        }

        try {
            $agent = Agent::where('agent_id', $tour->agent_id)->first();
            if (!$agent) {
                return false;
            }

            $previousUser = Auth::user();
            Auth::setUser($agent);

            try {
                $emailResult = CommonHelper::sendTourProposalEmail(
                    $tour->agent_id,
                    $tour->tour_id,
                    $tour->display_id,
                    [
                        'destination' => $tour->destination,
                        'city' => $tour->city,
                        'check_in_time' => $tour->check_in_time,
                        'check_out_time' => $tour->check_out_time,
                        'adult' => $tour->adult,
                        'child' => $tour->child,
                        'infant' => $tour->infant,
                    ]
                );
            } finally {
                // Restore prior auth state (request ends right after, but stay clean).
                if ($previousUser) {
                    Auth::setUser($previousUser);
                }
            }

            if ($emailResult !== true) {
                Log::warning('External API tour proposal email not sent', [
                    'tour_id' => $tour->tour_id,
                    'agent_id' => $tour->agent_id,
                    'reason' => $emailResult,
                ]);
                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::error('External API tour proposal email failed', [
                'tour_id' => $tour->tour_id,
                'agent_id' => $tour->agent_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Find the first DMC node in destinations[].DMC[] that carries an identifier.
     */
    protected function resolvePrimaryDmc(array $payload): array
    {
        foreach ($payload['destinations'] ?? [] as $destination) {
            foreach ($destination['DMC'] ?? [] as $dmc) {
                if (is_array($dmc) && (!empty($dmc['DMC_id']) || !empty($dmc['DMC_email']))) {
                    return $dmc;
                }
            }
        }

        return [];
    }

    /**
     * Resolve the DMC User from the payload: by DMC_id, then DMC_email/sender_email,
     * then Master_DMC_id as a last resort.
     */
    protected function resolveDmcUser(array $payload, array $primaryDmc): ?User
    {
        $dmcId = $primaryDmc['DMC_id'] ?? null;
        if (!empty($dmcId)) {
            $user = User::where('userId', $dmcId)->first();
            if ($user) {
                return $user;
            }
        }

        $email = $primaryDmc['DMC_email']
            ?? ($payload['DMC_email'] ?? null)
            ?? ($payload['dmc_email'] ?? null)
            ?? ($payload['sender_email'] ?? null);
        if (!empty($email)) {
            $user = User::where('email', $email)->first();
            if ($user) {
                return $user;
            }
        }

        $masterId = $payload['Master_DMC_id'] ?? null;
        if (!empty($masterId)) {
            return User::where('userId', $masterId)->first();
        }

        return null;
    }

    /**
     * Pick an agent for the tour: prefer one already linked to this DMC's agencies,
     * otherwise create a demo agency + demo agent (idempotent per master DMC).
     */
    protected function resolveOrCreateAgentForDmc(User $dmcUser, int $masterDmcId, array $payload, array $primaryDmc): Agent
    {
        $agents = $this->findAgentsForDmc($masterDmcId, $dmcUser);

        if ($agents->isNotEmpty()) {
            $payloadAgent = $this->resolveAgent($payload);
            if ($payloadAgent && $agents->contains('agent_id', $payloadAgent->agent_id)) {
                return $payloadAgent;
            }

            $senderEmail = trim((string) ($payload['sender_email'] ?? ''));
            if ($senderEmail !== '') {
                $byEmail = $agents->firstWhere('email', $senderEmail);
                if ($byEmail) {
                    return $byEmail;
                }
            }

            return $agents->first();
        }

        return $this->createDemoAgencyAndAgent($dmcUser, $masterDmcId, $payload, $primaryDmc);
    }

    /**
     * Agents belonging to this DMC via agency selection or sales_manager_dmc.
     */
    protected function findAgentsForDmc(int $masterDmcId, User $dmcUser): Collection
    {
        $agencyIds = Agency::whereJsonContains('dmc_id', $masterDmcId)->pluck('agency_id');

        if ($agencyIds->isNotEmpty()) {
            $fromAgencies = Agent::whereIn('agency_id', $agencyIds)
                ->where(function ($q) {
                    $q->where('status', 1)->orWhereNull('status');
                })
                ->orderBy('agent_id')
                ->get();

            if ($fromAgencies->isNotEmpty()) {
                return $fromAgencies;
            }
        }

        return Agent::whereIn('sales_manager_dmc', [
            (string) $dmcUser->userId,
            (string) $masterDmcId,
        ])
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('agent_id')
            ->get();
    }

    /**
     * Create (or reuse) a demo agency and agent for external API tours when the DMC
     * has no agency/agent configured yet.
     */
    protected function createDemoAgencyAndAgent(User $dmcUser, int $masterDmcId, array $payload, array $primaryDmc): Agent
    {
        $destination = $this->resolveDestination($payload, $primaryDmc);
        $city = $this->resolveFirstCity($payload) ?: ($dmcUser->city ?? 'N/A');
        $dmcLabel = trim((string) ($dmcUser->name ?? $dmcUser->company_name ?? ('DMC ' . $dmcUser->userId)));

        $demoAgencyEmail = 'demo-agency-dmc-' . $masterDmcId . '@external-api.local';
        $agency = Agency::where('email', $demoAgencyEmail)->first();

        if (!$agency) {
            $agency = new Agency();
            $agency->agency_name = 'External API Demo Agency - ' . $dmcLabel;
            $agency->email = $demoAgencyEmail;
            $agency->phone = $dmcUser->phone ?? '0000000000';
            $agency->country = $destination !== 'N/A' ? explode(',', $destination)[0] : ($dmcUser->country ?? 'N/A');
            $agency->city = $city;
            $agency->address = 'Auto-created for external API enquiries';
            $agency->contact_person = 'Demo Contact';
            $agency->status = 1;
            $agency->created_by = (int) $dmcUser->userId;
            $agency->dmc_id = [$masterDmcId];
            $agency->save();
            $agency->refresh();

            Log::info('External API: created demo agency for DMC', [
                'master_dmc_id' => $masterDmcId,
                'agency_id' => $agency->agency_id,
            ]);
        } elseif (!$agency->hasSelectedByDmc($masterDmcId)) {
            $agency->addDmcId($masterDmcId);
        }

        $demoAgentEmail = 'demo-agent-dmc-' . $masterDmcId . '@external-api.local';
        $agent = Agent::where('agency_id', $agency->agency_id)
            ->where('email', $demoAgentEmail)
            ->first();

        if ($agent) {
            return $agent;
        }

        $senderEmail = trim((string) ($payload['sender_email'] ?? ''));
        $agentName = 'Demo Agent';
        if ($senderEmail !== '' && !Agent::where('email', $senderEmail)->exists()) {
            $agentName = ucfirst(explode('@', $senderEmail)[0]);
        }

        $agent = new Agent();
        $agent->salutation = 'Mr';
        $agent->name = $agentName;
        $agent->company_name = $agency->agency_name;
        $agent->agency_id = $agency->agency_id;
        $agent->email = $demoAgentEmail;
        $agent->phone = $agency->phone;
        $agent->designation = 'Travel Agent';
        $agent->sales_manager_dmc = (string) $dmcUser->userId;
        $agent->role_id = $dmcUser->role_id ?? null;
        $agent->user_country = $agency->country;
        $agent->city = $agency->city;
        $agent->created_by = (int) $dmcUser->userId;
        $agent->dmc_id = json_encode([$masterDmcId]);
        $agent->password = bcrypt('Demo@' . $masterDmcId);
        $agent->status = 1;
        $agent->save();
        $agent->refresh();

        Log::info('External API: created demo agent for DMC', [
            'master_dmc_id' => $masterDmcId,
            'agency_id' => $agency->agency_id,
            'agent_id' => $agent->agent_id,
        ]);

        return $agent;
    }

    /**
     * Resolve the (optional) originating Agent by sender_email or explicit agent keys.
     */
    protected function resolveAgent(array $payload): ?Agent
    {
        $agentId = $this->payloadValue($payload, ['agent_id', 'agentId']);
        if (!empty($agentId)) {
            $agent = Agent::where('agent_id', $agentId)->first();
            if ($agent) {
                return $agent;
            }
        }

        $email = $this->payloadValue($payload, ['agent_email', 'agentEmail', 'sender_email']);
        if (!empty($email)) {
            return Agent::where('email', $email)->first();
        }

        return null;
    }

    /**
     * Build a destination string from all DMC countries in the payload.
     */
    protected function resolveDestination(array $payload, array $primaryDmc): string
    {
        $countries = [];
        foreach ($payload['destinations'] ?? [] as $destination) {
            foreach ($destination['DMC'] ?? [] as $dmc) {
                $country = trim((string) ($dmc['country'] ?? ''));
                if ($country !== '') {
                    $countries[$country] = $country;
                }
            }
        }

        if ($countries !== []) {
            return implode(', ', array_values($countries));
        }

        $fallback = trim((string) ($primaryDmc['country'] ?? ''));
        return $fallback !== '' ? $fallback : 'N/A';
    }

    /**
     * Find the first city referenced in the package days (cities[].city or hotel city).
     */
    protected function resolveFirstCity(array $payload): ?string
    {
        foreach ($payload['destinations'] ?? [] as $destination) {
            foreach ($destination['DMC'] ?? [] as $dmc) {
                foreach ($dmc['packages'] ?? [] as $package) {
                    foreach ($package['days'] ?? [] as $day) {
                        foreach ($this->itemsFrom($day['cities'] ?? []) as $city) {
                            $name = trim((string) ($city['city'] ?? ''));
                            if ($name !== '') {
                                return $name;
                            }
                        }
                        foreach ($this->itemsFrom($day['hotels'] ?? []) as $hotel) {
                            $name = trim((string) ($hotel['city'] ?? ''));
                            if ($name !== '') {
                                return $name;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Normalize lead/main guest details from the payload. The DMC payload has no
     * explicit guest, so the originating sender is used as the lead contact.
     */
    protected function extractMainGuest(array $payload): ?array
    {
        $guest = $this->payloadValue($payload, ['mainguest', 'lead_guest', 'leadGuest', 'customer']);
        if (is_string($guest)) {
            $decoded = json_decode($guest, true);
            $guest = is_array($decoded) ? $decoded : null;
        }

        if (is_array($guest) && $guest !== []) {
            $fullName = trim((string) ($guest['full_name'] ?? $guest['fullName'] ?? $guest['name'] ?? ''));
            $email = trim((string) ($guest['email'] ?? ''));
            $phone = trim((string) ($guest['phone'] ?? $guest['contact'] ?? ''));

            if ($fullName !== '' || $email !== '' || $phone !== '') {
                return [
                    'salutation' => is_string($guest['salutation'] ?? null) ? rtrim($guest['salutation'], '.') : ($guest['salutation'] ?? null),
                    'full_name' => $fullName,
                    'email' => $email ?: null,
                    'country_code' => $guest['country_code'] ?? $guest['countryCode'] ?? null,
                    'phone' => $phone ?: null,
                    'special_requests' => $guest['special_requests'] ?? $guest['specialRequests'] ?? null,
                ];
            }
        }

        // Fallback to the sender's email as the lead contact.
        $senderEmail = trim((string) ($payload['sender_email'] ?? ''));
        if ($senderEmail !== '') {
            $name = ucfirst(explode('@', $senderEmail)[0]);
            return [
                'salutation' => null,
                'full_name' => $name,
                'email' => $senderEmail,
                'country_code' => null,
                'phone' => null,
                'special_requests' => null,
            ];
        }

        return null;
    }

    /**
     * Normalize additional guests from the payload (none for the DMC payload).
     */
    protected function extractAdditionalGuests(array $payload): ?array
    {
        $guests = $this->payloadValue($payload, ['additionalguest', 'additional_guests', 'additionalGuests']);
        if (is_string($guests)) {
            $decoded = json_decode($guests, true);
            $guests = is_array($decoded) ? $decoded : null;
        }
        if (!is_array($guests) || $guests === []) {
            return null;
        }

        $normalized = array_values(array_filter($guests, function ($row) {
            if (!is_array($row)) {
                return false;
            }
            $name = trim((string) ($row['name'] ?? $row['guest_name'] ?? ''));
            $contact = trim((string) ($row['contact_no'] ?? $row['contact'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            return $name !== '' || $contact !== '' || $email !== '';
        }));

        return $normalized !== [] ? $normalized : null;
    }

    /**
     * Unwrap external payloads that nest booking data under response/data/body keys,
     * then normalize common field aliases (dmc_email, package_id, etc.).
     */
    protected function unwrapPayload(array $payload): array
    {
        foreach (['response', 'data', 'body', 'booking', 'result'] as $wrapper) {
            if (! isset($payload[$wrapper]) || ! is_array($payload[$wrapper])) {
                continue;
            }

            $inner = $payload[$wrapper];
            $outer = array_diff_key($payload, [$wrapper => true]);

            $payload = $outer !== [] ? array_merge($inner, $outer) : $inner;
            break;
        }

        if (empty($payload['DMC_email']) && ! empty($payload['dmc_email'])) {
            $payload['DMC_email'] = $payload['dmc_email'];
        }

        if (empty($payload['package_id']) && empty($payload['id'])) {
            $packageId = $this->extractFirstPackageId($payload);
            if ($packageId !== null) {
                $payload['package_id'] = $packageId;
            }
        }

        if (empty($payload['country'])) {
            $primaryDmc = $this->resolvePrimaryDmc($payload);
            $country = trim((string) ($primaryDmc['country'] ?? ''));
            if ($country !== '') {
                $payload['country'] = $country;
            }
        }

        return $payload;
    }

    protected function extractFirstPackageId(array $payload): ?string
    {
        foreach ($payload['destinations'] ?? [] as $destination) {
            if (! is_array($destination)) {
                continue;
            }
            foreach ($destination['DMC'] ?? [] as $dmc) {
                if (! is_array($dmc)) {
                    continue;
                }
                foreach ($dmc['packages'] ?? [] as $package) {
                    if (! is_array($package)) {
                        continue;
                    }
                    $id = trim((string) ($package['package_id'] ?? $package['packageId'] ?? $package['id'] ?? ''));
                    if ($id !== '') {
                        return $id;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Decode a value into an associative array. Handles raw arrays, JSON strings,
     * and double-encoded JSON strings (the legacy `payload=<json string>` form field
     * which previously caused the stored payload to be a string).
     */
    protected function normalizeToArray($value): array
    {
        $loops = 0;
        while (is_string($value) && $loops < 5) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }
            $decoded = json_decode($trimmed, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                $pythonParsed = $this->parsePythonLikePayload($trimmed);
                if ($pythonParsed !== null) {
                    $value = $pythonParsed;
                    $loops++;

                    continue;
                }

                return ['raw_body' => $value];
            }
            $value = $decoded;
            $loops++;
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Parse Python dict/list literals (single quotes) sent by legacy external clients.
     */
    protected function parsePythonLikePayload(string $value): ?array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $first = $trimmed[0];
        if ($first !== '{' && $first !== '[') {
            return null;
        }

        $converted = preg_replace(
            ['/\bNone\b/', '/\bTrue\b/', '/\bFalse\b/'],
            ['null', 'true', 'false'],
            $trimmed
        );
        $converted = str_replace("'", '"', $converted);

        $decoded = json_decode($converted, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Turn an associative map of named items ("Hotel 1" => {...}) or a list into a
     * flat list of item arrays. Empty arrays/objects are skipped.
     */
    protected function itemsFrom($node): array
    {
        if (!is_array($node) || $node === []) {
            return [];
        }

        $items = [];
        foreach ($node as $value) {
            if (is_array($value) && $value !== []) {
                $items[] = $value;
            }
        }

        return $items;
    }

    /**
     * Read the first present, non-empty value among the given payload keys.
     */
    protected function payloadValue(array $payload, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
                return $payload[$key];
            }
        }

        return $default;
    }

    /**
     * Parse a date value with a fallback when missing/invalid.
     */
    protected function parseDate($value, Carbon $fallback): Carbon
    {
        if (empty($value)) {
            return $fallback->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable $e) {
            return $fallback->copy();
        }
    }

    public function index(Request $request): JsonResponse
    {
        $rows = ExternalApiReceive::query()
            ->latest('id')->where('status', false)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Received payload list fetched.',
            'data' => $rows,
        ]);
    }
}

