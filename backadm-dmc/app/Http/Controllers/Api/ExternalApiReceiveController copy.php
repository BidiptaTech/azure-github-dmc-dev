<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CommonHelper;
use App\Helpers\HotelPriceHelper;
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
use App\Models\Vehicle;
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
        // print_r($payload);
        // die();
        if ($payload === [] && trim((string) $request->getContent()) !== '') {
            $payload = $this->normalizeToArray($request->getContent());
        }
        $payload = $this->unwrapPayload($payload);

        if (isset($payload['raw_body']) && empty($payload['destinations'])) {
            $reparsed = $this->unwrapPayload($this->normalizeToArray($payload['raw_body']));
            if (! empty($reparsed['destinations'])) {
                $payload = $reparsed;
            } elseif ($reparsed !== []) {
                $payload = array_merge($payload, $reparsed);
            }
        }

        if ($this->payloadMatchingIsZero($payload)) {
            $record = ExternalApiReceive::create([
                'source_ip' => $request->ip(),
                'source_server' => (string) ($request->header('X-Source-Server') ?? ''),
                'headers' => $request->headers->all(),
                'payload' => $payload,
                'status' => false,
            ]);

            $matchingZeroNotify = $this->handleMatchingZeroNotification($payload, $record->id);

            return response()->json([
                'success' => false,
                'message' => $matchingZeroNotify['message'],
                'received_id' => $record->id,
                'result' => array_merge([
                    'matching' => 0,
                ], $matchingZeroNotify['result']),
            ], 422);
        }

        if (empty($payload['destinations'])) {
            $record = ExternalApiReceive::create([
                'source_ip' => $request->ip(),
                'source_server' => (string) ($request->header('X-Source-Server') ?? ''),
                'headers' => $request->headers->all(),
                'payload' => $payload,
                'status' => false,
            ]);

            $senderNotify = ['sent' => false, 'email' => null];
            $matchingZeroResult = [];
            $matchingZeroNotify = null;
            if ($this->payloadMatchingIsZero($payload)) {
                $matchingZeroNotify = $this->handleMatchingZeroNotification($payload, $record->id);
                $senderNotify = [
                    'sent' => (bool) ($matchingZeroNotify['result']['notification_email_sent'] ?? false),
                    'email' => $matchingZeroNotify['result']['notification_email'] ?? null,
                ];
                $matchingZeroResult = $matchingZeroNotify['result'];
            }

            return response()->json([
                'success' => false,
                'message' => $this->payloadMatchingIsZero($payload)
                    ? ($matchingZeroNotify['message'] ?? 'Incomplete travel details. A notification email has been sent to the sender.')
                    : 'Invalid payload: destinations missing. Send valid JSON (double quotes) or a Python dict with a response.destinations block.',
                'received_id' => $record->id,
                'result' => array_merge([
                    'matching' => $this->payloadMatchingValue($payload),
                    'sender_email_sent' => $senderNotify['sent'],
                    'sender_email' => $senderNotify['email'],
                ], $matchingZeroResult),
                'hint' => $this->payloadMatchingIsZero($payload)
                    ? null
                    : 'Use Content-Type: application/json and json.dumps(data) in Python, not str(dict).',
            ], 422);
        }

        $primaryDmc = $this->resolvePrimaryDmc($payload);
        $dmcUser = $this->resolveDmcUser($payload, $primaryDmc);
        $requestedCountry = $this->resolveRequestedDestinationCountry($payload);

        if ($dmcUser && $requestedCountry !== '') {
            $countrySupport = CommonHelper::validateDmcDestinationCountrySupport($dmcUser, $requestedCountry);

            if (! $countrySupport['supported']) {
                $record = ExternalApiReceive::create([
                    'source_ip' => $request->ip(),
                    'source_server' => (string) ($request->header('X-Source-Server') ?? ''),
                    'headers' => $request->headers->all(),
                    'payload' => $payload,
                    'status' => false,
                ]);

                $agentNotify = $this->notifyUnsupportedDestinationCountry(
                    $payload,
                    $dmcUser,
                    $primaryDmc,
                    $countrySupport
                );

                Log::warning('External API: destination country not supported by selected DMC', [
                    'received_id' => $record->id,
                    'matching' => $this->payloadMatchingValue($payload),
                    'dmc_id' => $dmcUser->userId,
                    'dmc_name' => trim((string) ($dmcUser->company_name ?: $dmcUser->name ?: '')),
                    'requested_country' => $countrySupport['requested_country'],
                    'supported_countries' => $countrySupport['supported_countries'],
                    'alternate_dmcs' => $countrySupport['alternate_dmcs'],
                    'agent_email' => $agentNotify['email'],
                    'agent_email_sent' => $agentNotify['sent'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'The selected DMC does not provide services for the requested destination country. An informational email has been sent to the agent.',
                    'received_id' => $record->id,
                    'result' => [
                        'destination_country_supported' => false,
                        'requested_country' => $countrySupport['requested_country'],
                        'supported_countries' => $countrySupport['supported_countries'],
                        'selected_dmc_id' => $dmcUser->userId,
                        'selected_dmc_name' => trim((string) ($dmcUser->company_name ?: $dmcUser->name ?: 'DMC')),
                        'alternate_dmcs' => $countrySupport['alternate_dmcs'],
                        'agent_email_sent' => $agentNotify['sent'],
                        'agent_email' => $agentNotify['email'],
                    ],
                ], 422);
            }
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
            $result['email_sent'] = $this->notifyAgent($tour, $payload);

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
        $requestedDays = (int) ($this->payloadValue($payload, ['requested_days', 'total_days', 'nights'], 0) ?: 0);
        $checkOutTime = $requestedDays > 0
            ? (clone $checkInTime)->addDays($requestedDays - 1)
            : (clone $checkInTime);
        if ($checkOutTime->lt($checkInTime)) {
            $checkOutTime = clone $checkInTime;
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
                            'entry_port' => $day['arrivals'] ?? [],
                            'exit_port' => $day['departures'] ?? [],
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

        // $bookingId = CommonHelper::nextOrderBookingId();

        return Order::create([
            'agent_id' => $tour->agent_id,
            'tour_id' => $tour->tour_id,
            // 'booking_id' => $bookingId,
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
            'entry_port' => $this->transformPortTransportItem($tour, $item, $meta, $customer, 'entry_port'),
            'exit_port' => $this->transformPortTransportItem($tour, $item, $meta, $customer, 'exit_port'),
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
            'arrival', 'arrivals', 'entry', 'entry port', 'entry_port_transfer' => 'entry_port',
            'departure', 'departures', 'exit', 'exit port', 'exit_port_transfer' => 'exit_port',
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
        $mealPlanFields = $this->resolveMealPlanFieldsForOrder($item, $resolvedRoom);
        $mealPlan = $mealPlanFields['key'];
        $mealPlanLabel = $mealPlanFields['label'];
        $requestedPax = $this->resolveHotelRequestedPax($tour, $item);
        $bedCapacity = $this->resolveBedCapacity($resolvedBed);
        $allocation = $this->allocateHotelRoomsByBedCapacity($requestedPax, $bedCapacity);
        $numberOfRooms = $allocation['number_of_rooms'];
        $headCount = $allocation['head_count'];
        $occupancy = $headCount <= 1 ? 'single' : 'double';

        $payloadPrice = (float) ($item['price'] ?? $item['totalPrice'] ?? $item['total_price'] ?? 0);
        $packageRoomPrice = (float) ($item['room_price'] ?? 0);
        $packageBreakfast = (float) ($item['breakfast_price'] ?? 0);
        $packageLunch = (float) ($item['lunch_price'] ?? 0);
        $packageDinner = (float) ($item['dinner_price'] ?? 0);
        $packagePerNight = (float) ($item['price_per_night'] ?? 0);
        if ($packagePerNight <= 0) {
            $packagePerNight = $packageRoomPrice + $packageBreakfast + $packageLunch + $packageDinner;
        }

        $extraBed = max(0, (int) ($item['extra_bed'] ?? $item['extraBed'] ?? 0));
        $helperPricing = ($hotelId && $resolvedRoom)
            ? $this->calculateHotelPriceUsingHelper(
                (string) $hotelId,
                $resolvedRoom,
                $resolvedBed,
                $checkIn,
                $nights,
                $numberOfRooms,
                $headCount,
                $mealPlan,
                $extraBed,
                $dmcId > 0 ? $dmcId : null
            )
            : null;

        $price = $payloadPrice;
        if ($helperPricing) {
            $price = $helperPricing['grand_total'];
        } elseif ($price <= 0 && $packagePerNight > 0) {
            $price = $packagePerNight * $nights * $numberOfRooms;
        } elseif ($price <= 0 && $resolvedRoom) {
            $price = $this->calculateHotelTotalPrice(
                $resolvedRoom,
                $nights,
                $numberOfRooms,
                $requestedPax,
                $occupancy,
                $mealPlan
            );
        }

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

        $bedPrice = $packageRoomPrice > 0
            ? $packageRoomPrice
            : ($helperPricing
                ? ($nights > 0 && $numberOfRooms > 0
                    ? $helperPricing['room_total'] / ($nights * $numberOfRooms)
                    : 0)
                : ($resolvedRoom
                    ? ($occupancy === 'single'
                        ? (float) ($resolvedRoom->weekday_price ?? 0)
                        : (float) ($resolvedRoom->double_weekday_price ?? $resolvedRoom->weekday_price ?? 0))
                    : 0));
        $mealUnitPrice = $packageBreakfast + $packageLunch + $packageDinner;
        if ($helperPricing) {
            $mealPrice = $helperPricing['meal_total'];
        } elseif ($mealUnitPrice <= 0) {
            $mealPrice = max(0, $price - ($bedPrice * $nights * $numberOfRooms));
        } else {
            $mealPrice = $mealUnitPrice * $nights * $numberOfRooms;
        }
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
                'selected_persons' => $requestedPax,
                'beds' => [[
                    'bed_id' => $bedId !== null ? (string) $bedId : '',
                    'bed_type' => $bedType,
                    'room_type' => $roomType,
                    'baby_cot' => (int) ($resolvedBed?->baby_cot ?? 0),
                    'head_count' => $headCount,
                    'max_occupancy' => $bedCapacity,
                    'price' => $bedPrice > 0 ? ($bedPrice * $nights * $numberOfRooms) : $price,
                    'meal_plan' => $mealPlan,
                    'mealTypes' => [$mealPlan],
                    'selectedMeals' => [
                        'meal_1' => [
                            'type' => $mealPlanLabel,
                            'price' => $mealPrice,
                        ],
                    ],
                ]],
            ]],
            'meal_plan' => $mealPlan,
            'mealPlan' => $mealPlanLabel,
            'meal_type' => $mealPlanFields['meal_type'],
            'room_price' => $bedPrice,
            'breakfast_price' => $packageBreakfast,
            'lunch_price' => $packageLunch,
            'dinner_price' => $packageDinner,
            'price_per_night' => $packagePerNight > 0 ? $packagePerNight : ($bedPrice + $packageBreakfast + $packageLunch + $packageDinner),
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

        $resolvedTopMealPlan = '';
        $topMealRaw = trim((string) ($hotelData['meal_plan'] ?? $hotelData['mealPlan'] ?? ''));
        if ($topMealRaw !== '') {
            $resolvedTopMealPlan = $this->normalizeMealPlanValue($topMealRaw);
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

                $rawMealPlan = trim((string) ($bed['meal_plan'] ?? ''));
                if ($rawMealPlan === '') {
                    $rawMealPlan = trim((string) ($bed['selectedMeals']['meal_1']['type'] ?? ''));
                }
                if ($rawMealPlan === '') {
                    $rawMealPlan = trim((string) ($bed['mealTypes'][0] ?? ''));
                }
                if ($rawMealPlan === '') {
                    $rawMealPlan = $resolvedTopMealPlan !== '' ? $resolvedTopMealPlan : 'room_only';
                }

                $mealPlanKey = $this->normalizeMealPlanValue($rawMealPlan);
                $mealPlanLabel = strtolower($this->mealPlanToLabel($mealPlanKey));
                $mealPrice = (float) ($bed['selectedMeals']['meal_1']['price'] ?? 0);

                $rooms[$roomIndex]['beds'][$bedIndex]['meal_plan'] = $mealPlanKey;
                $rooms[$roomIndex]['beds'][$bedIndex]['mealTypes'] = [$mealPlanKey];
                $rooms[$roomIndex]['beds'][$bedIndex]['room_type'] = $roomType;
                $rooms[$roomIndex]['beds'][$bedIndex]['selectedMeals'] = [
                    'meal_1' => [
                        'type' => $mealPlanLabel,
                        'price' => $mealPrice,
                    ],
                ];

                if ($resolvedTopMealPlan === '') {
                    $resolvedTopMealPlan = $mealPlanKey;
                }
            }
        }

        if ($resolvedTopMealPlan === '') {
            $resolvedTopMealPlan = 'room_only';
        }
        $resolvedTopMealLabel = strtolower($this->mealPlanToLabel($resolvedTopMealPlan));

        $pricing = $this->reconcileHotelOrderPricing($rooms, $hotelData, $tour, $hotelId);
        $rooms = $pricing['rooms'];
        $totalPrice = $pricing['totalPrice'];

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
            'meal_plan' => $resolvedTopMealPlan,
            'mealPlan' => $resolvedTopMealLabel,
            'meal_type' => (string) ($hotelData['meal_type'] ?? ''),
            'rooms' => $rooms,
            'totalPrice' => $totalPrice,
            'price' => $totalPrice,
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
     * Build one date string per night (check-in night through last night, excluding checkout day).
     *
     * @return list<string>
     */
    protected function buildHotelStayDates(string $checkIn, int $nights): array
    {
        try {
            $start = Carbon::parse($checkIn);
        } catch (\Throwable $e) {
            return [];
        }

        $dates = [];
        for ($i = 0; $i < max(1, $nights); $i++) {
            $dates[] = $start->copy()->addDays($i)->toDateString();
        }

        return $dates;
    }

    /**
     * Hotel price via HotelPriceHelper (weekend, season, blackout, fair rates).
     *
     * @return array{grand_total: float, room_total: float, meal_total: float, nights: int}|null
     */
    protected function calculateHotelPriceUsingHelper(
        string $hotelUniqueId,
        Room $room,
        ?Bed $bed,
        string $checkIn,
        int $nights,
        int $numberOfRooms,
        int $paxPerRoom,
        string $mealPlanKey,
        int $extraBed = 0,
        ?int $dmcId = null
    ): ?array {
        if ($hotelUniqueId === '' || empty($room->room_id)) {
            return null;
        }

        $dates = $this->buildHotelStayDates($checkIn, $nights);
        if ($dates === []) {
            return null;
        }

        $result = HotelPriceHelper::calculatePrice(
            $hotelUniqueId,
            $room->room_id,
            $bed?->bed_id ?? '',
            $dates,
            $this->mealPlanToLabel($mealPlanKey),
            max(1, $paxPerRoom),
            max(0, $extraBed),
            $dmcId
        );

        if (! ($result['success'] ?? false)) {
            Log::warning('External API: HotelPriceHelper pricing failed', [
                'hotel_unique_id' => $hotelUniqueId,
                'room_id' => $room->room_id,
                'message' => $result['message'] ?? '',
            ]);

            return null;
        }

        $rooms = max(1, $numberOfRooms);

        return [
            'grand_total' => round((float) ($result['grand_total'] ?? 0) * $rooms, 2),
            'room_total' => round((float) ($result['room_total'] ?? 0) * $rooms, 2),
            'meal_total' => round((float) ($result['meal_total'] ?? 0) * $rooms, 2),
            'nights' => (int) ($result['nights'] ?? count($dates)),
        ];
    }

    /**
     * Legacy weekday-only fallback when HotelPriceHelper cannot price the stay.
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
        $mealGuests = max(1, $adults);

        $includesBreakfast = str_contains($plan, 'breakfast')
            || str_contains($plan, 'bed_&_')
            || str_contains($plan, 'half_board')
            || str_contains($plan, 'all_inclusive');
        $includesLunch = str_contains($plan, 'lunch') || str_contains($plan, 'all_inclusive');
        $includesDinner = str_contains($plan, 'dinner') || str_contains($plan, 'all_inclusive');

        if ($includesBreakfast) {
            $total += (float) ($room->breakfast_price ?? 0) * $mealGuests * $nights;
        }
        if ($includesLunch) {
            $total += (float) ($room->lunch_price ?? 0) * $mealGuests * $nights;
        }
        if ($includesDinner) {
            $total += (float) ($room->dinner_price ?? 0) * $mealGuests * $nights;
        }

        return round($total, 2);
    }

    /**
     * Demanded hotel pax for AI booking (tour adults + children, or payload override).
     */
    protected function resolveHotelRequestedPax(Tour $tour, array $item): int
    {
        $fromItem = (int) ($item['adultCount'] ?? $item['adults'] ?? $item['pax'] ?? $item['selected_persons'] ?? 0);
        $childrenFromItem = (int) ($item['childCount'] ?? $item['children'] ?? 0);
        if ($fromItem > 0) {
            return max(1, $fromItem + max(0, $childrenFromItem));
        }

        foreach ($item['rooms'] ?? [] as $room) {
            if (! is_array($room)) {
                continue;
            }
            $roomPax = (int) ($room['selected_persons'] ?? $room['selectedPersons'] ?? 0);
            if ($roomPax > 0) {
                return max(1, $roomPax);
            }
        }

        return max(1, (int) ($tour->adult ?? 0) + (int) ($tour->child ?? 0));
    }

    /**
     * Bed max occupancy used as room capacity for AI hotel allocation.
     */
    protected function resolveBedCapacity(?Bed $bed): int
    {
        if (! $bed) {
            return 1;
        }

        return max(1, (int) ($bed->max_occupancy ?: $bed->adult_count ?: 1));
    }

    /**
     * Allocate rooms from demanded pax and bed capacity.
     *
     * @return array{number_of_rooms: int, head_count: int}
     */
    protected function allocateHotelRoomsByBedCapacity(int $requestedPax, int $bedCapacity): array
    {
        $requestedPax = max(1, $requestedPax);
        $bedCapacity = max(1, $bedCapacity);
        $numberOfRooms = (int) ceil($requestedPax / $bedCapacity);
        $headCount = min($requestedPax, $bedCapacity);

        return [
            'number_of_rooms' => max(1, $numberOfRooms),
            'head_count' => max(1, $headCount),
        ];
    }

    /**
     * Allocate arrival/departure vehicles from pax and seating capacity.
     * Example: 14 pax / capacity 5 => 3 vehicles.
     *
     * @return array{booked_vehicles: int, passengers: int, seating_capacity: int}
     */
    protected function allocateVehiclesBySeatingCapacity(int $requestedPax, int $seatCapacity): array
    {
        $requestedPax = max(1, $requestedPax);

        if ($seatCapacity <= 0) {
            return [
                'booked_vehicles' => 1,
                'passengers' => $requestedPax,
                'seating_capacity' => $requestedPax,
            ];
        }

        $seatCapacity = max(1, $seatCapacity);

        return [
            'booked_vehicles' => max(1, (int) ceil($requestedPax / $seatCapacity)),
            'passengers' => $requestedPax,
            'seating_capacity' => $seatCapacity,
        ];
    }

    protected function resolveVehicleSeatingCapacity(array $item, ?array $vehicleDetails = null): int
    {
        $capacity = (int) (
            $item['seating_capacity']
            ?? $item['seats']
            ?? ($vehicleDetails['seating_capacity'] ?? 0)
        );

        if ($capacity > 0) {
            return $capacity;
        }

        $name = (string) ($item['vehicles_name'] ?? $item['vehicle_name'] ?? $vehicleDetails['vehicle_name'] ?? '');
        if ($name !== '' && preg_match('/(\d+)\s*seat/i', $name, $matches)) {
            return max(1, (int) $matches[1]);
        }

        return 0;
    }

    /**
     * @return array{
     *     booked_vehicles: int,
     *     vehicle_mismatch_corrected: bool,
     *     seating_capacity: int,
     *     passengers: int,
     *     payload_booked_vehicles: int|null
     * }
     */
    protected function reconcilePortVehicleAllocation(array $item, int $pax, int $seatCapacity): array
    {
        $allocation = $this->allocateVehiclesBySeatingCapacity($pax, $seatCapacity);
        $required = $allocation['booked_vehicles'];

        $payloadBooked = max(0, (int) (
            $item['booked_vehicles']
            ?? $item['vehicle_count']
            ?? $item['vehicles_count']
            ?? $item['number_of_vehicles']
            ?? $item['no_of_vehicles']
            ?? 0
        ));

        return [
            'booked_vehicles' => $required,
            'vehicle_mismatch_corrected' => $payloadBooked > 0 && $payloadBooked !== $required,
            'seating_capacity' => $allocation['seating_capacity'],
            'passengers' => $allocation['passengers'],
            'payload_booked_vehicles' => $payloadBooked > 0 ? $payloadBooked : null,
        ];
    }

    /**
     * @return array{unit_price: float, line_total: float}
     */
    protected function resolvePortTransportLineTotal(
        array $item,
        Tour $tour,
        int $pax,
        int $bookedVehicles,
        ?int $payloadBookedVehicles,
        string $serviceType,
        float $unitPrice,
        ?array $vehicleDetails = null
    ): array {
        $explicitTotal = (float) ($item['totalPrice'] ?? $item['total_price'] ?? $item['price'] ?? 0);

        if ($unitPrice <= 0 && $vehicleDetails) {
            $unitPrice = in_array(strtolower($serviceType), ['shared', 'sic'], true)
                ? (float) ($vehicleDetails['shared_price'] ?? 0)
                : (float) ($vehicleDetails['private_price'] ?? 0);
        }

        if (in_array(strtolower($serviceType), ['shared', 'sic'], true)) {
            $childCount = max(0, (int) ($item['childCount'] ?? $item['children'] ?? $item['childQty'] ?? $tour->child ?? 0));
            $adultCount = max(0, $pax - $childCount);
            $adultUnit = $unitPrice > 0 ? $unitPrice : (
                $explicitTotal > 0 && $pax > 0 ? round($explicitTotal / $pax, 2) : 0.0
            );
            $childUnit = (float) ($item['childSell'] ?? $item['child_sell'] ?? $adultUnit);
            $lineTotal = round(($adultUnit * $adultCount) + ($childUnit * $childCount), 2);

            if ($lineTotal <= 0 && $explicitTotal > 0) {
                $lineTotal = round($explicitTotal, 2);
                if ($adultUnit <= 0 && $pax > 0) {
                    $adultUnit = round($explicitTotal / $pax, 2);
                }
            }

            return ['unit_price' => $adultUnit, 'line_total' => $lineTotal];
        }

        if ($unitPrice <= 0 && $explicitTotal > 0) {
            $divisor = ($payloadBookedVehicles ?? 0) > 0 ? $payloadBookedVehicles : max(1, $bookedVehicles);
            $unitPrice = round($explicitTotal / max(1, $divisor), 2);
        }

        $bookedVehicles = max(1, $bookedVehicles);
        $lineTotal = round($unitPrice * $bookedVehicles, 2);

        if ($explicitTotal > 0 && abs($explicitTotal - $lineTotal) > 0.01) {
            if ($explicitTotal >= ($unitPrice * $bookedVehicles * 0.9)) {
                $lineTotal = round($explicitTotal, 2);
            } elseif (($payloadBookedVehicles ?? 1) === 1 && $bookedVehicles > 1) {
                $lineTotal = round($unitPrice * $bookedVehicles, 2);
            }
        }

        return ['unit_price' => $unitPrice, 'line_total' => $lineTotal];
    }

    protected function transformPortTransportItem(Tour $tour, array $item, array $meta, array $customer, string $portType): array
    {
        $item = $this->flattenPortTransportPayload($item, $portType);
        $pax = $this->resolveBillablePax($item, $tour);
        $vehicleRawId = trim((string) ($item['vehicle_id'] ?? $item['vehicles_id'] ?? $item['vehicleId'] ?? ''));
        $vehicleName = trim((string) ($item['vehicle_name'] ?? $item['vehicles_name'] ?? $item['vehicleName'] ?? ''));
        $vehicleDetails = $this->resolveVehicleForTransfer($vehicleRawId, $vehicleName);

        if ($vehicleRawId === '' && is_array($vehicleDetails) && ! empty($vehicleDetails['vehicle_id'])) {
            $vehicleRawId = (string) $vehicleDetails['vehicle_id'];
        }
        if ($vehicleName === '' && is_array($vehicleDetails) && ! empty($vehicleDetails['vehicle_name'])) {
            $vehicleName = (string) $vehicleDetails['vehicle_name'];
        }

        $seatCapacity = $this->resolveVehicleSeatingCapacity($item, $vehicleDetails);
        $vehicleAllocation = $this->reconcilePortVehicleAllocation($item, $pax, $seatCapacity);
        $bookedVehicles = $vehicleAllocation['booked_vehicles'];

        $typeRaw = $item['type'] ?? $item['transferType'] ?? $item['transfer_type'] ?? 'Private';
        $serviceType = ucfirst(strtolower((string) $typeRaw));
        if ($serviceType === 'Sic') {
            $serviceType = 'Shared';
        }
        if (! in_array($serviceType, ['Private', 'Shared'], true)) {
            $serviceType = 'Private';
        }

        $unitPrice = (float) ($item['adultSell'] ?? $item['adult_sell'] ?? $item['base_price'] ?? $item['basePrice'] ?? $item['cost'] ?? 0);
        $pricing = $this->resolvePortTransportLineTotal(
            $item,
            $tour,
            $pax,
            $bookedVehicles,
            $vehicleAllocation['payload_booked_vehicles'],
            $serviceType,
            $unitPrice,
            $vehicleDetails
        );

        $bookingDate = $this->parseDate($meta['bookingDate'] ?? $tour->check_in_time, Carbon::today())->toDateString();
        $pickupTime = trim((string) ($item['entrytime'] ?? $item['exitpickupdate'] ?? $item['time'] ?? $item['pickup_time'] ?? ''));

        $payload = array_merge($customer, [
            'bookingDate' => $bookingDate,
            'pickupdate' => $bookingDate,
            'vehicles_id' => $vehicleRawId,
            'vehicle_id' => $vehicleRawId,
            'vehicles_name' => $vehicleName !== '' ? $vehicleName : $vehicleRawId,
            'vehicle_name' => $vehicleName !== '' ? $vehicleName : $vehicleRawId,
            'Mode' => 'dmc',
            'type' => $serviceType,
            'dmc_id' => (string) ($tour->dmc_id ?? ''),
            'passengers' => $vehicleAllocation['passengers'],
            'adults' => max(1, (int) ($item['adults'] ?? $item['adultCount'] ?? $pax)),
            'children' => max(0, (int) ($item['children'] ?? $item['childCount'] ?? $tour->child ?? 0)),
            'seating_capacity' => $vehicleAllocation['seating_capacity'],
            'booked_vehicles' => $bookedVehicles,
            'vehicle_count' => $bookedVehicles,
            'vehicle_mismatch_corrected' => $vehicleAllocation['vehicle_mismatch_corrected'],
            'vehicle_unit_price' => $pricing['unit_price'],
            'vehicle_type' => is_array($vehicleDetails) ? ($vehicleDetails['vehicle_type'] ?? ($item['vehicle_type'] ?? '')) : ($item['vehicle_type'] ?? ''),
            'totalPrice' => $pricing['line_total'],
            'price' => $pricing['line_total'],
            'travel_type' => $portType,
            'city' => $item['city'] ?? '',
            'remarks' => $item['remarks'] ?? null,
            'supplement' => filter_var($item['supplement'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'source' => 'external_api',
        ]);

        if ($portType === 'entry_port') {
            $payload['entrypickup'] = $item['entrypickup'] ?? $item['pickup'] ?? $item['pickup_location'] ?? $item['port_name'] ?? $item['portName'] ?? '';
            $payload['entrydropoff'] = $item['entrydropoff'] ?? $item['dropoff'] ?? $item['drop_location'] ?? $item['transfer_destination_name'] ?? $item['transferDestinationName'] ?? '';
            $payload['entrytime'] = $pickupTime;
            $payload['arrival_flight_no'] = $item['arrival_flight_no'] ?? $item['flight_no'] ?? $item['flightNo'] ?? $item['flight_number'] ?? '';
        } else {
            $payload['exitpickup'] = $item['exitpickup'] ?? $item['pickup'] ?? $item['pickup_location'] ?? $item['transfer_pickup'] ?? '';
            $payload['exitdropoff'] = $item['exitdropoff'] ?? $item['dropoff'] ?? $item['drop_location'] ?? $item['port_name'] ?? $item['portName'] ?? '';
            $payload['exitpickupdate'] = $pickupTime;
            $payload['departure_flight_no'] = $item['departure_flight_no'] ?? $item['flight_no'] ?? $item['flightNo'] ?? $item['flight_number'] ?? '';
        }

        return $payload;
    }

    /**
     * Blob/day-level arrival & departure items nest vehicle + route under transfer{}.
     *
     * @return array<string, mixed>
     */
    protected function flattenPortTransportPayload(array $item, string $portType): array
    {
        $transfer = is_array($item['transfer'] ?? null) ? $item['transfer'] : [];
        if ($transfer === []) {
            return $item;
        }

        $lineTotal = (float) (
            $item['total_price']
            ?? $item['totalPrice']
            ?? $transfer['transfer_price']
            ?? $transfer['cost']
            ?? 0
        );

        $flattened = array_merge($item, array_filter([
            'vehicle_id' => $transfer['vehicle_id'] ?? null,
            'vehicles_id' => $transfer['vehicle_id'] ?? null,
            'vehicle_name' => $transfer['vehicle_name'] ?? null,
            'vehicles_name' => $transfer['vehicle_name'] ?? null,
            'type' => $transfer['type'] ?? null,
            'transfer_type' => $transfer['transfer_type'] ?? null,
            'transferType' => $transfer['type'] ?? null,
            'cost' => $transfer['cost'] ?? $transfer['transfer_price'] ?? null,
            'price' => $lineTotal > 0 ? $lineTotal : ($transfer['cost'] ?? $transfer['transfer_price'] ?? null),
            'totalPrice' => $lineTotal > 0 ? $lineTotal : null,
            'total_price' => $lineTotal > 0 ? $lineTotal : null,
            'pickup_time' => $transfer['pickup_time'] ?? null,
            'city' => $item['city'] ?? $transfer['city'] ?? null,
            'pickup_location' => $transfer['pickup_location'] ?? null,
            'drop_location' => $transfer['drop_location'] ?? null,
        ], static fn ($value) => $value !== null && $value !== ''));

        if ($portType === 'entry_port') {
            $flattened['entrypickup'] = $item['entrypickup']
                ?? $transfer['pickup_location']
                ?? $item['pickup']
                ?? $item['port_name']
                ?? '';
            $flattened['entrydropoff'] = $item['entrydropoff']
                ?? $transfer['drop_location']
                ?? $item['dropoff']
                ?? $item['transfer_destination_name']
                ?? '';
        } else {
            $flattened['exitpickup'] = $item['exitpickup']
                ?? $transfer['pickup_location']
                ?? $item['pickup']
                ?? '';
            $flattened['exitdropoff'] = $item['exitdropoff']
                ?? $transfer['drop_location']
                ?? $item['dropoff']
                ?? $item['port_name']
                ?? '';
        }

        return $flattened;
    }

    protected function resolveHotelStayNights(array $bookingDate, Tour $tour): int
    {
        if (is_array($bookingDate) && count($bookingDate) === 2) {
            try {
                $start = Carbon::parse($bookingDate[0]);
                $end = Carbon::parse($bookingDate[1]);

                return max(1, $start->diffInDays($end));
            } catch (\Throwable $e) {
                // fall through
            }
        }

        if (! empty($tour->check_in_time) && ! empty($tour->check_out_time)) {
            try {
                $start = Carbon::parse($tour->check_in_time);
                $end = Carbon::parse($tour->check_out_time);

                return max(1, $start->diffInDays($end));
            } catch (\Throwable $e) {
                // fall through
            }
        }

        return 1;
    }

    /**
     * Recalculate hotel order line total and sync room/bed price breakdown before persist.
     *
     * @param  list<array<string, mixed>>  $rooms
     * @return array{rooms: list<array<string, mixed>>, totalPrice: float}
     */
    protected function reconcileHotelOrderPricing(array $rooms, array $hotelData, Tour $tour, $hotelId = null): array
    {
        $bookingDate = is_array($hotelData['bookingDate'] ?? null) ? $hotelData['bookingDate'] : [];
        $nights = $this->resolveHotelStayNights($bookingDate, $tour);
        $checkIn = is_array($bookingDate) && ! empty($bookingDate[0])
            ? (string) $bookingDate[0]
            : $this->parseDate($tour->check_in_time ?? null, Carbon::today())->toDateString();
        $mealPlan = (string) ($hotelData['meal_plan'] ?? 'room_only');
        $dmcId = (int) ($tour->dmc_id ?? 0);
        $createdBy = (int) ($tour->created_by ?? 0);
        $requestedPax = $this->resolveHotelRequestedPax($tour, $hotelData);
        $extraBed = max(0, (int) ($hotelData['extra_bed'] ?? $hotelData['extraBed'] ?? 0));
        $grandTotal = 0.0;
        $hasCalculatedLine = false;

        foreach ($rooms as $roomIndex => $roomPayload) {
            if (! is_array($roomPayload)) {
                continue;
            }

            $roomRecord = null;
            $roomId = $roomPayload['room_id'] ?? null;
            if ($roomId !== null && $roomId !== '' && is_numeric($roomId)) {
                $roomRecord = Room::where('room_id', (int) $roomId)->first();
            }
            if (! $roomRecord && $hotelId) {
                $roomRecord = $this->resolveRoomFromItem($hotelId, $roomPayload, $dmcId, $createdBy);
            }

            $beds = is_array($roomPayload['beds'] ?? null) ? $roomPayload['beds'] : [];
            $bedRecord = null;
            if ($roomRecord) {
                $bedId = $beds[0]['bed_id'] ?? null;
                if ($bedId !== null && $bedId !== '' && is_numeric($bedId)) {
                    $bedRecord = Bed::where('bed_id', (int) $bedId)->first();
                }
                if (! $bedRecord) {
                    $bedRecord = $this->resolveFirstBed($roomRecord);
                }
            }

            $bedCapacity = $this->resolveBedCapacity($bedRecord);
            $selectedPersons = max(1, (int) ($roomPayload['selected_persons'] ?? $roomPayload['selectedPersons'] ?? $requestedPax));
            $allocation = $this->allocateHotelRoomsByBedCapacity($selectedPersons, $bedCapacity);
            $numberOfRooms = max(1, (int) ($roomPayload['number_of_rooms'] ?? $roomPayload['no_of_room'] ?? $allocation['number_of_rooms']));
            if ($numberOfRooms < $allocation['number_of_rooms']) {
                $numberOfRooms = $allocation['number_of_rooms'];
            }
            $headCount = max(1, (int) ($beds[0]['head_count'] ?? $allocation['head_count']));
            $occupancy = (string) ($roomPayload['occupancy'] ?? ($headCount <= 1 ? 'single' : 'double'));
            if (! in_array($occupancy, ['single', 'double', 'triple'], true)) {
                $occupancy = $headCount <= 1 ? 'single' : 'double';
            }
            $rateOccupancy = $occupancy === 'triple' ? 'double' : $occupancy;

            $rooms[$roomIndex]['number_of_rooms'] = $numberOfRooms;
            $rooms[$roomIndex]['selected_persons'] = $selectedPersons;
            $rooms[$roomIndex]['occupancy'] = $occupancy;

            if ($roomRecord) {
                $helperPricing = ($hotelId && $checkIn !== '')
                    ? $this->calculateHotelPriceUsingHelper(
                        (string) $hotelId,
                        $roomRecord,
                        $bedRecord,
                        $checkIn,
                        $nights,
                        $numberOfRooms,
                        $headCount,
                        $mealPlan,
                        $extraBed,
                        $dmcId > 0 ? $dmcId : null
                    )
                    : null;

                if ($helperPricing) {
                    $lineTotal = $helperPricing['grand_total'];
                    $bedComponent = $helperPricing['room_total'];
                    $mealComponent = $helperPricing['meal_total'];
                } else {
                    $lineTotal = $this->calculateHotelTotalPrice(
                        $roomRecord,
                        $nights,
                        $numberOfRooms,
                        $selectedPersons,
                        $rateOccupancy,
                        $mealPlan
                    );
                    $roomRate = $rateOccupancy === 'single'
                        ? (float) ($roomRecord->weekday_price ?? 0)
                        : (float) ($roomRecord->double_weekday_price ?? $roomRecord->weekday_price ?? 0);
                    $bedComponent = $roomRate * $nights * $numberOfRooms;
                    $mealComponent = max(0, $lineTotal - $bedComponent);
                }

                if ($beds !== []) {
                    $rooms[$roomIndex]['beds'][0]['head_count'] = $headCount;
                    $rooms[$roomIndex]['beds'][0]['max_occupancy'] = $bedCapacity;
                    $rooms[$roomIndex]['beds'][0]['price'] = round($bedComponent, 2);
                    if (isset($rooms[$roomIndex]['beds'][0]['selectedMeals']['meal_1'])) {
                        $rooms[$roomIndex]['beds'][0]['selectedMeals']['meal_1']['price'] = round($mealComponent, 2);
                    }
                }

                $grandTotal += $lineTotal;
                $hasCalculatedLine = true;
            } else {
                foreach ($beds as $bed) {
                    if (! is_array($bed)) {
                        continue;
                    }
                    $grandTotal += (float) ($bed['price'] ?? 0);
                    $grandTotal += (float) ($bed['selectedMeals']['meal_1']['price'] ?? 0);
                }
            }
        }

        if (! $hasCalculatedLine) {
            $breakdownTotal = 0.0;
            foreach ($rooms as $roomPayload) {
                if (! is_array($roomPayload)) {
                    continue;
                }
                foreach ($roomPayload['beds'] ?? [] as $bed) {
                    if (! is_array($bed)) {
                        continue;
                    }
                    $breakdownTotal += (float) ($bed['price'] ?? 0);
                    $breakdownTotal += (float) ($bed['selectedMeals']['meal_1']['price'] ?? 0);
                }
            }
            if ($breakdownTotal > 0) {
                $grandTotal = $breakdownTotal;
            }
        }

        if ($grandTotal <= 0) {
            $grandTotal = (float) ($hotelData['totalPrice'] ?? $hotelData['price'] ?? 0);
        }

        return [
            'rooms' => $rooms,
            'totalPrice' => round($grandTotal, 2),
        ];
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
     * Canonical meal plan key + display label for hotel order JSON.
     *
     * @return array{key: string, label: string, meal_type: string}
     */
    protected function resolveMealPlanFieldsForOrder(array $item, ?Room $room): array
    {
        $rawMealPlan = trim((string) ($item['meal_plan'] ?? ''));
        $mealPlanKey = $rawMealPlan !== ''
            ? $this->normalizeMealPlanValue($rawMealPlan)
            : $this->resolveHotelMealPlan($item, $room);

        return [
            'key' => $mealPlanKey,
            'label' => strtolower($this->mealPlanToLabel($mealPlanKey)),
            'meal_type' => trim((string) ($item['meal_type'] ?? '')),
        ];
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
        $unitPrice = (float) ($item['price'] ?? $item['totalPrice'] ?? 0);
        $transfer = $this->mapTransferOptions(
            is_array($item['transfer'] ?? null) ? $item['transfer'] : [],
            [],
            $this->resolveBillablePax($item, $tour)
        );
        $pricing = $this->resolvePerPaxLineTotal($item, $tour, $unitPrice);
        $unitPrice = $pricing['unit_price'];
        $lineTotal = $pricing['line_total'];
        $pax = $pricing['pax'];

        return array_merge($customer, [
            'bookingDate' => $bookingDate,
            'visitTime' => $item['visitTime'] ?? $item['time_slot'] ?? '10:00 AM',
            'adultCount' => $pax,
            'childCount' => max(0, (int) ($item['childCount'] ?? $tour->child)),
            'seniorCount' => max(0, (int) ($item['seniorCount'] ?? 0)),
            'AttractionId' => $attraction?->attraction_id ?? $attractionId,
            'AttractionName' => $name,
            'ticketId' => $ticketId,
            'ticketName' => $ticketName,
            'ticket_details' => [
                'adult_price' => $unitPrice,
                'child_price' => 0,
                'senior_price' => 0,
                'description' => '',
                'nri' => 'residential',
            ],
            'Selection' => 'withoutTransport',
            'mode' => 'dmc',
            'totalPrice' => $lineTotal,
            'price' => $lineTotal,
            'prices' => ['price' => $lineTotal],
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
        $unitPrice = (float) ($item['price'] ?? $item['totalPrice'] ?? $item['mealPrice'] ?? 0);
        $pax = $this->resolveBillablePax($item, $tour);
        $transfer = $this->mapTransferOptions(
            is_array($item['transfer'] ?? null) ? $item['transfer'] : [],
            [],
            $pax
        );
        $pricing = $this->resolvePerPaxLineTotal($item, $tour, $unitPrice);
        $unitPrice = $pricing['unit_price'];
        $lineTotal = $pricing['line_total'];
        $pax = $pricing['pax'];

        return array_merge($customer, [
            'bookingDate' => $bookingDate,
            'visitTime' => $timeSlot,
            'adultCount' => $pax,
            'childCount' => max(0, (int) ($item['childCount'] ?? $tour->child)),
            'restaurantId' => $restaurant?->restaurant_id ?? $restaurantId,
            'restaurantName' => $name,
            'mealType' => $this->normalizeMealTypeLabel($mealType),
            'mealSpecificType' => $dish !== '' ? $dish : null,
            'MealDescription' => [[
                'item_name' => $dish !== '' ? $dish : 'Menu Item',
                'name' => $dish !== '' ? $dish : 'Menu Item',
                'price' => $unitPrice,
                'meal_id' => $restaurant?->restaurant_id ?? $restaurantId,
                'quantity' => $pax,
            ]],
            'totalPrice' => $lineTotal,
            'mealPrice' => $lineTotal,
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
    protected function mapTransferOptions(array $transfer, array $fallback = [], ?int $billablePax = null): ?array
    {
        $requiredRaw = $transfer['required'] ?? $fallback['required'] ?? false;
        $required = filter_var($requiredRaw, FILTER_VALIDATE_BOOLEAN)
            || (is_string($requiredRaw) && strtolower($requiredRaw) === 'yes');

        if (! $required && empty($fallback['required'])) {
            return null;
        }

        $typeRaw = $transfer['type'] ?? $transfer['transfer_type'] ?? $fallback['type'] ?? 'private';
        $type = ucfirst(strtolower((string) $typeRaw));
        if (!in_array($type, ['Private', 'Shared', 'Sic'], true)) {
            $type = 'Private';
        }

        $wayRaw = $transfer['way'] ?? 'One Way';
        $way = in_array($wayRaw, ['Two Way', 'Return'], true) ? 'Two Way' : 'One Way';
        $pax = max(1, (int) ($transfer['passengers'] ?? $billablePax ?? 0));
        $lineCost = $this->resolveTransferLineCost($transfer, $pax);

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
            'cost' => $lineCost,
            'price' => $lineCost,
            'passengers' => $pax,
            'pickup_time' => $transfer['pickup_time'] ?? '',
            'city' => $transfer['city'] ?? $fallback['city'] ?? '',
        ];
    }

    protected function resolveBillablePax(array $item, Tour $tour): int
    {
        $adults = (int) ($item['adultCount'] ?? $item['adults'] ?? $item['pax'] ?? 0);
        $children = (int) ($item['childCount'] ?? $item['children'] ?? 0);
        if ($adults > 0) {
            return max(1, $adults + max(0, $children));
        }

        return max(1, (int) ($tour->adult ?? 0) + (int) ($tour->child ?? 0));
    }

    /**
     * @return array{unit_price: float, line_total: float, pax: int}
     */
    protected function resolvePerPaxLineTotal(array $item, Tour $tour, float $unitPrice): array
    {
        $pax = $this->resolveBillablePax($item, $tour);
        $explicitTotal = (float) ($item['totalPrice'] ?? $item['total_price'] ?? 0);

        if ($unitPrice <= 0 && $explicitTotal > 0) {
            return [
                'unit_price' => $pax > 0 ? round($explicitTotal / $pax, 2) : $explicitTotal,
                'line_total' => round($explicitTotal, 2),
                'pax' => $pax,
            ];
        }

        if ($unitPrice <= 0) {
            return ['unit_price' => 0.0, 'line_total' => 0.0, 'pax' => $pax];
        }

        if ($explicitTotal <= 0 || abs($explicitTotal - $unitPrice) < 0.01) {
            return [
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $pax, 2),
                'pax' => $pax,
            ];
        }

        if ($explicitTotal >= ($unitPrice * max(2, $pax) * 0.9)) {
            return [
                'unit_price' => $unitPrice,
                'line_total' => round($explicitTotal, 2),
                'pax' => $pax,
            ];
        }

        return [
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $pax, 2),
            'pax' => $pax,
        ];
    }

    protected function resolveTransferLineCost(array $transfer, int $billablePax): float
    {
        unset($billablePax);

        $explicitTotal = (float) ($transfer['totalPrice'] ?? $transfer['total_cost'] ?? 0);
        if ($explicitTotal > 0) {
            return round($explicitTotal, 2);
        }

        return round((float) ($transfer['cost'] ?? $transfer['price'] ?? 0), 2);
    }

    /**
     * @return array{vehicle_id: string, vehicle_name: string, vehicle_type?: string, seating_capacity?: int|string}|null
     */
    protected function resolveVehicleForTransfer(string $vehicleRawId, string $vehicleName = ''): ?array
    {
        $vehicleRawId = trim($vehicleRawId);
        $vehicleName = trim($vehicleName);
        $query = Vehicle::withTrashed();

        $vehicle = null;
        if ($vehicleRawId !== '') {
            $vehicle = (clone $query)
                ->where('vehicle_id', $vehicleRawId)
                ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END')
                ->first();
            if (! $vehicle && ctype_digit($vehicleRawId)) {
                $vehicle = (clone $query)
                    ->where('id', (int) $vehicleRawId)
                    ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END')
                    ->first();
            }
        }

        $nameLooksLikeId = $vehicleName !== '' && (
            $vehicleName === $vehicleRawId
            || (ctype_digit($vehicleName) && $vehicleName === (string) ((int) $vehicleName))
        );
        if (! $vehicle && $vehicleName !== '' && ! $nameLooksLikeId) {
            $vehicle = (clone $query)
                ->where(function ($q) use ($vehicleName) {
                    $q->whereRaw('LOWER(TRIM(vehicle_name)) = ?', [strtolower($vehicleName)])
                        ->orWhereRaw('LOWER(TRIM(vehicle_type)) = ?', [strtolower($vehicleName)]);
                })
                ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END')
                ->first();
        }

        if ($vehicle && $vehicle->deleted_at) {
            $dmcId = (int) ($vehicle->dmc_id ?? 0);
            $active = null;
            if ($dmcId > 0) {
                $active = Vehicle::query()
                    ->whereNull('deleted_at')
                    ->where(function ($q) use ($dmcId) {
                        $q->where('dmc_id', $dmcId)
                            ->orWhere('dmc_id', (string) $dmcId)
                            ->orWhereRaw('CAST(dmc_id AS TEXT) = ?', [(string) $dmcId]);
                    })
                    ->orderBy('id')
                    ->first();
            }
            if ($active) {
                $vehicle = $active;
            }
        }

        if (! $vehicle) {
            return null;
        }

        $name = trim((string) ($vehicle->vehicle_name ?? ''));
        if ($name === '') {
            $name = trim((string) ($vehicle->vehicle_type ?? ''));
        }

        $canonicalId = (string) ($vehicle->vehicle_id ?? $vehicle->id ?? $vehicleRawId);
        if ($name === '' || $name === $canonicalId || (ctype_digit($name) && (string) ((int) $name) === $name)) {
            $name = trim((string) ($vehicle->vehicle_type ?? '')) ?: $canonicalId;
        }

        return [
            'vehicle_id' => $canonicalId,
            'vehicle_name' => $name,
            'vehicle_type' => (string) ($vehicle->vehicle_type ?? ''),
            'seating_capacity' => $vehicle->seating_capacity ?? '',
            'private_price' => (string) ($vehicle->base_price ?? $vehicle->private_price ?? '0.00'),
            'shared_price' => (string) ($vehicle->sharable_base_price ?? $vehicle->shared_price ?? '0.00'),
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
     * Email the API sender_email using the DMC ai_response setting (QTN / ITN).
     * To = sender_email only. ai_email is for IMAP fetch, never the recipient.
     * Non-fatal by design.
     *
     * @return array{sent: bool, email: ?string}
     */
    protected function notifySender(Tour $tour, array $payload, Collection $orders): array
    {
        // Recipient is always the enquiry sender — never ai_email / SMTP mailbox.
        $senderEmail = $this->resolveSenderNotificationEmail($payload);

        if ($senderEmail === null) {
            Log::info('External API: skipping sender itinerary email, no valid sender_email', [
                'tour_id' => $tour->tour_id,
            ]);

            return ['sent' => false, 'email' => null];
        }

        $primaryDmc = $this->resolvePrimaryDmc($payload);
        $dmcUser = $this->resolveDmcUser($payload, $primaryDmc);

        if (CommonHelper::resolveDmcAiResponse($dmcUser) === null) {
            Log::info('External API: skipping sender email, DMC ai_response not set to QTN or ITN', [
                'tour_id' => $tour->tour_id,
                'dmc_id' => $dmcUser?->userId,
            ]);

            return ['sent' => false, 'email' => $senderEmail];
        }

        $agent = $tour->agent_id ? Agent::where('agent_id', $tour->agent_id)->first() : null;
        $agency = $agent && $agent->agency_id
            ? Agency::where('agency_id', $agent->agency_id)->first()
            : null;

        $dmcName = $dmcUser
            ? trim((string) ($dmcUser->company_name ?: $dmcUser->name ?: 'DMC'))
            : 'DMC';

        try {
            $availability = $this->resolvePackageAvailability($payload);
            $aiResponse = CommonHelper::resolveDmcAiResponse($dmcUser);
            $emailUuid = $this->resolveEmailUuidFromPayload($payload);
            $emailSubject = $this->resolveEmailSubjectFromPayload($payload);
            $threadFields = $this->resolveEmailThreadPayloadFields($payload);
            // API SMTP values win; this DMC's stored /mail/settings fill any gap.
            // The API does not send smtp_pass, so the stored password is what
            // makes authenticated sending from the DMC mailbox possible.
            $storedMailSettings = $dmcUser
                ? CommonHelper::getDmcMailSettings((int) $dmcUser->userId)
                : [];
            $runtimeMailConfig = CommonHelper::resolveApiRuntimeMailConfig(
                $primaryDmc,
                $payload,
                $storedMailSettings
            );

            if ($runtimeMailConfig === null) {
                Log::warning('External API: DMC SMTP incomplete, falling back to default mailer', [
                    'tour_id' => $tour->tour_id,
                    'dmc_id' => $dmcUser?->userId,
                    'smtp_user' => $this->payloadValue($payload, ['smtp_user'], ''),
                    'has_stored_password' => trim((string) ($storedMailSettings['smtp_pass'] ?? '')) !== '',
                ]);
            }

            $mailContext = $runtimeMailConfig !== null
                ? ['_mail_config' => $runtimeMailConfig]
                : [];

            if ($aiResponse === 'QTN') {
                $emailData = null;
                try {
                    $emailData = CommonHelper::buildQuotationConfirmationEmailDataFromTour($tour);
                } catch (Throwable $buildEx) {
                    Log::warning('External API: quotation email data build failed, using fallback', [
                        'tour_id' => $tour->tour_id,
                        'error' => $buildEx->getMessage(),
                    ]);
                }

                if (!$emailData) {
                    $bookedServices = $this->buildBookedServicesForEmail($orders);
                    $totalEstimation = round(array_sum(array_map(
                        static fn (array $service): float => (float) ($service['price_value'] ?? 0),
                        $bookedServices
                    )), 2);
                    $timestamp = now()->format('M d, Y H:i');
                    $emailData = CommonHelper::normalizeQuotationEmailData([
                        'dmc_name' => $dmcName,
                        'dmc_logo' => $this->resolveDmcLogoForEmail($dmcUser, $payload),
                        'tour_display_id' => $tour->display_id,
                        'country' => $this->resolveDayLevelCountry($payload, $primaryDmc),
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
                        'booked_at' => $timestamp,
                        'quoted_at' => $timestamp,
                        'booked_services' => $bookedServices,
                        'total_estimation' => $totalEstimation,
                        'currency_code' => $this->resolveItineraryCurrency($payload, $primaryDmc),
                    ]);
                }

                if ($emailData) {
                    $emailData['email_uuid'] = $emailUuid;
                    $emailData['subject'] = $emailSubject;
                    $emailData = array_merge($emailData, $threadFields, $mailContext);
                }

                $sent = CommonHelper::sendTourQuotationEmail($senderEmail, $emailData ?: array_merge([
                    'email_uuid' => $emailUuid,
                    'subject' => $emailSubject,
                ], $threadFields, $mailContext));
            } else {
                $bookedServices = $this->buildBookedServicesForEmail($orders);
                $totalEstimation = round(array_sum(array_map(
                    static fn (array $service): float => (float) ($service['price_value'] ?? 0),
                    $bookedServices
                )), 2);

                $timestamp = now()->format('M d, Y H:i');
                $sent = CommonHelper::sendTourItineraryEmailByAiResponse($senderEmail, array_merge([
                    'email_uuid' => $emailUuid,
                    'subject' => $emailSubject,
                    'dmc_name' => $dmcName,
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
                    'booked_at' => $timestamp,
                    'quoted_at' => $timestamp,
                    'booked_services' => $bookedServices,
                    'total_estimation' => $totalEstimation,
                    'currency_code' => $this->resolveItineraryCurrency($payload, $primaryDmc),
                ], $threadFields, $mailContext), $dmcUser);
            }

            if ($sent !== true) {
                Log::warning('External API sender itinerary email not sent', [
                    'tour_id' => $tour->tour_id,
                    'sender_email' => $senderEmail,
                    'ai_response' => CommonHelper::resolveDmcAiResponse($dmcUser),
                    'reason' => $sent,
                ]);

                return ['sent' => false, 'email' => $senderEmail];
            }

            return ['sent' => true, 'email' => $senderEmail];
        } catch (Throwable $e) {
            Log::error('External API sender itinerary email failed', [
                'tour_id' => $tour->tour_id,
                'sender_email' => $senderEmail,
                'error' => $e->getMessage(),
            ]);

            return ['sent' => false, 'email' => $senderEmail];
        }
    }

    protected function resolveSenderNotificationEmail(array $payload): ?string
    {
        // Do not use generic "email" / "ai_email" — those can be DMC mailbox fields.
        $email = trim((string) $this->payloadValue($payload, ['sender_email', 'senderEmail'], ''));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        $mainGuest = $this->extractMainGuest($payload);
        if (is_array($mainGuest) && ! empty($mainGuest['email'])) {
            $guestEmail = trim((string) $mainGuest['email']);
            if ($guestEmail !== '' && filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
                return $guestEmail;
            }
        }

        return null;
    }

    /**
     * Parent Message-ID for In-Reply-To / References. Older payloads send it as
     * `email_uuid`; newer ones send the RFC id as `message_id` plus a bare
     * `uuid`, so `message_id` is preferred when both are present.
     */
    protected function resolveEmailUuidFromPayload(array $payload): ?string
    {
        return CommonHelper::resolveEmailUuidFromContext([
            'email_uuid' => $this->payloadValue($payload, [
                'email_uuid',
                'emailUuid',
                'message_id',
                'messageId',
                'uuid',
            ], ''),
        ]);
    }

    protected function resolveEmailSubjectFromPayload(array $payload): ?string
    {
        return CommonHelper::resolveEmailSubjectFromContext([
            'subject' => $this->payloadValue($payload, ['subject'], ''),
            'mail_received' => $this->payloadValue($payload, ['mail_received'], ''),
        ]);
    }

    /**
     * @return array{references: mixed, cc: list<string>, bcc: list<string>}
     */
    protected function resolveEmailThreadPayloadFields(array $payload): array
    {
        return [
            'references' => $this->payloadValue($payload, [
                'references',
                'email_references',
                'References',
            ], ''),
            'cc' => $this->resolvePayloadEmailList($payload, [
                'cc',
                'cc_list',
                'cc_emails',
                'cc_email',
                'CC',
            ]),
            'bcc' => $this->resolvePayloadEmailList($payload, [
                'bcc',
                'bcc_list',
                'bcc_emails',
                'bcc_email',
                'BCC',
            ]),
        ];
    }

    /**
     * @param  list<string>  $keys
     * @return list<string>
     */
    protected function resolvePayloadEmailList(array $payload, array $keys): array
    {
        $layers = [$payload];
        foreach (['response', 'data', 'body', 'booking', 'result'] as $wrapper) {
            if (isset($payload[$wrapper]) && is_array($payload[$wrapper])) {
                $layers[] = $payload[$wrapper];
            }
        }

        $emails = [];
        foreach ($layers as $layer) {
            $emails = array_merge($emails, CommonHelper::resolveEmailListFromContext($layer, $keys));
        }

        return array_values(array_unique($emails));
    }

    /**
     * @return list<string>
     */
    protected function resolveEmailReferencesFromPayload(array $payload): array
    {
        return CommonHelper::resolveEmailReferencesFromContext([
            'references' => $this->payloadValue($payload, [
                'references',
                'email_references',
                'References',
            ], ''),
        ]);
    }

    protected function payloadMatchingValue(array $payload): ?int
    {
        $value = $this->payloadValue($payload, ['matching', 'Matching']);

        if ($value === null || $value === '') {
            foreach (['response', 'data', 'body'] as $wrapper) {
                if (! isset($payload[$wrapper]) || ! is_array($payload[$wrapper])) {
                    continue;
                }
                if (array_key_exists('matching', $payload[$wrapper])) {
                    $value = $payload[$wrapper]['matching'];
                    break;
                }
            }
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function payloadMatchingIsZero(array $payload): bool
    {
        return $this->payloadMatchingValue($payload) === 0;
    }

    /**
     * matching == 0: validate secondary_country against Master DMC countries.
     * Supported → incomplete-travel-details email. Not supported → mismatch email.
     *
     * @return array{message: string, result: array<string, mixed>}
     */
    protected function handleMatchingZeroNotification(array $payload, ?int $receivedId = null): array
    {
        $requestedCountry = $this->resolveRequestedDestinationCountry($payload, true);
        $primaryDmc = $this->resolvePrimaryDmc($payload);
        $dmcUser = $this->resolveDmcUser($payload, $primaryDmc);
        $masterDmc = $this->resolveMasterDmcUser($dmcUser, $payload);

        if ($requestedCountry !== '' && $masterDmc) {
            $masterSupported = CommonHelper::dmcSupportsDestinationCountry($masterDmc, $requestedCountry);

            if (! $masterSupported) {
                $countrySupport = [
                    'supported' => false,
                    'requested_country' => $requestedCountry,
                    'supported_countries' => CommonHelper::resolveSupportedCountriesForDmc($masterDmc),
                    'alternate_dmcs' => CommonHelper::findAlternateDmcsForCountry(
                        $dmcUser ?? $masterDmc,
                        $requestedCountry
                    ),
                ];

                $agentNotify = $this->notifyUnsupportedDestinationCountry(
                    $payload,
                    $dmcUser ?? $masterDmc,
                    $primaryDmc,
                    $countrySupport
                );

                Log::warning('External API: matching=0 destination country not supported by Master DMC', [
                    'received_id' => $receivedId,
                    'matching' => 0,
                    'requested_country' => $requestedCountry,
                    'secondary_country' => $this->payloadValue($payload, ['secondary_country', 'secondaryCountry'], ''),
                    'master_dmc_id' => $masterDmc->userId,
                    'master_dmc_name' => trim((string) ($masterDmc->company_name ?: $masterDmc->name ?: '')),
                    'supported_countries' => $countrySupport['supported_countries'],
                    'alternate_dmcs' => $countrySupport['alternate_dmcs'],
                    'agent_email' => $agentNotify['email'],
                    'agent_email_sent' => $agentNotify['sent'],
                ]);

                return [
                    'message' => 'The requested destination country is not supported by this Master DMC. An informational email has been sent to the agent.',
                    'result' => [
                        'notification_type' => 'destination_country_mismatch',
                        'destination_country_supported' => false,
                        'validated_against' => 'master_dmc',
                        'requested_country' => $requestedCountry,
                        'supported_countries' => $countrySupport['supported_countries'],
                        'master_dmc_id' => $masterDmc->userId,
                        'master_dmc_name' => trim((string) ($masterDmc->company_name ?: $masterDmc->name ?: 'Master DMC')),
                        'alternate_dmcs' => $countrySupport['alternate_dmcs'],
                        'notification_email_sent' => $agentNotify['sent'],
                        'notification_email' => $agentNotify['email'],
                        'sender_email_sent' => $agentNotify['sent'],
                        'sender_email' => $agentNotify['email'],
                    ],
                ];
            }

            Log::info('External API: matching=0 destination country supported by Master DMC', [
                'received_id' => $receivedId,
                'matching' => 0,
                'requested_country' => $requestedCountry,
                'master_dmc_id' => $masterDmc->userId,
                'supported_countries' => CommonHelper::resolveSupportedCountriesForDmc($masterDmc),
            ]);
        }

        $senderNotify = $this->notifyIncompleteTravelDetails($payload);

        return [
            'message' => 'Incomplete travel details. A notification email has been sent to the sender.',
            'result' => [
                'notification_type' => 'incomplete_travel_details',
                'destination_country_supported' => $requestedCountry === '' || ! $masterDmc
                    ? null
                    : true,
                'requested_country' => $requestedCountry !== '' ? $requestedCountry : null,
                'validated_against' => $masterDmc ? 'master_dmc' : null,
                'notification_email_sent' => $senderNotify['sent'],
                'notification_email' => $senderNotify['email'],
                'sender_email_sent' => $senderNotify['sent'],
                'sender_email' => $senderNotify['email'],
            ],
        ];
    }

    /**
     * Travel destination from payload. When $preferSecondaryCountry is true (matching == 0),
     * secondary_country is checked before country.
     */
    protected function resolveRequestedDestinationCountry(array $payload, bool $preferSecondaryCountry = false): string
    {
        $keys = $preferSecondaryCountry
            ? ['secondary_country', 'secondaryCountry', 'country', 'destination_country', 'destinationCountry', 'requested_country', 'requestedCountry']
            : ['country', 'destination_country', 'destinationCountry', 'secondary_country', 'secondaryCountry', 'requested_country', 'requestedCountry'];

        $explicit = trim((string) $this->payloadValue($payload, $keys, ''));
        if ($explicit !== '') {
            return CommonHelper::normalizeCountryName($explicit);
        }

        foreach ($payload['destinations'] ?? [] as $destination) {
            if (! is_array($destination)) {
                continue;
            }

            $destinationCountry = trim((string) (
                $destination['country']
                ?? $destination['destination_country']
                ?? $destination['destinationCountry']
                ?? ''
            ));

            if ($destinationCountry !== '') {
                return CommonHelper::normalizeCountryName($destinationCountry);
            }
        }

        return '';
    }

    protected function resolveMasterDmcUser(?User $dmcUser, array $payload): ?User
    {
        $masterId = (int) $this->payloadValue($payload, ['Master_DMC_id', 'master_dmc_id', 'masterDmcId'], 0);

        if ($dmcUser) {
            if (CommonHelper::isMasterDmcUser($dmcUser)) {
                return $dmcUser;
            }

            if ((int) ($dmcUser->master_dmc_id ?? 0) > 0) {
                $masterId = (int) $dmcUser->master_dmc_id;
            }
        }

        if ($masterId > 0) {
            $master = User::where('userId', $masterId)->first();
            if ($master) {
                return $master;
            }
        }

        if ($dmcUser && ! empty($dmcUser->created_by)) {
            $parent = User::where('userId', $dmcUser->created_by)->first();
            if ($parent && CommonHelper::isMasterDmcUser($parent)) {
                return $parent;
            }
        }

        return null;
    }

    /**
     * @param  array{
     *   supported: bool,
     *   requested_country: string,
     *   supported_countries: list<string>,
     *   alternate_dmcs: list<array{name: string, email: string, country: string}>
     * }  $countrySupport
     * @return array{sent: bool, email: ?string}
     */
    protected function notifyUnsupportedDestinationCountry(
        array $payload,
        User $dmcUser,
        array $primaryDmc,
        array $countrySupport
    ): array {
        $agentEmail = $this->resolveAgentNotificationEmail($payload, $dmcUser);

        if ($agentEmail === null) {
            Log::info('External API: skipping unsupported destination email, no valid agent email', [
                'dmc_id' => $dmcUser->userId,
                'requested_country' => $countrySupport['requested_country'],
            ]);

            return ['sent' => false, 'email' => null];
        }

        $agent = $this->resolveAgent($payload);
        $agentName = $agent?->name;
        if ($agentName === null || trim((string) $agentName) === '') {
            $agentName = ucfirst(explode('@', $agentEmail)[0]);
        }

        $selectedDmcName = trim((string) (
            $primaryDmc['DMC_name']
            ?? $primaryDmc['dmc_name']
            ?? $dmcUser->company_name
            ?? $dmcUser->name
            ?? 'DMC'
        ));

        $dmcLabel = trim((string) ($dmcUser->company_name ?: $dmcUser->name ?: $selectedDmcName));

        try {
            $sent = CommonHelper::sendUnsupportedDestinationCountryEmail($agentEmail, array_merge([
                'recipient_name' => $agentName,
                'selected_dmc_name' => $selectedDmcName,
                'requested_country' => $countrySupport['requested_country'],
                'alternate_dmcs' => $countrySupport['alternate_dmcs'],
                'dmc_name' => $dmcLabel,
                'dmc_label' => $dmcLabel,
                'dmc_logo' => $this->resolveDmcLogoForEmail($dmcUser, $payload),
                'dmc_contact_email' => $this->resolveDmcContactEmail($payload, $primaryDmc, $dmcUser),
                'email_uuid' => $this->resolveEmailUuidFromPayload($payload),
                'subject' => $this->resolveEmailSubjectFromPayload($payload),
            ], $this->resolveEmailThreadPayloadFields($payload)), $dmcUser);

            if ($sent !== true) {
                Log::warning('External API unsupported destination email not sent', [
                    'agent_email' => $agentEmail,
                    'reason' => $sent,
                ]);

                return ['sent' => false, 'email' => $agentEmail];
            }

            return ['sent' => true, 'email' => $agentEmail];
        } catch (Throwable $e) {
            Log::error('External API unsupported destination email failed', [
                'agent_email' => $agentEmail,
                'error' => $e->getMessage(),
            ]);

            return ['sent' => false, 'email' => $agentEmail];
        }
    }

    /**
     * Email sender when payload matching is 0 (incomplete travel details).
     *
     * @return array{sent: bool, email: ?string}
     */
    protected function notifyIncompleteTravelDetails(array $payload): array
    {
        $senderEmail = $this->resolveSenderNotificationEmail($payload);

        if ($senderEmail === null) {
            Log::info('External API: skipping incomplete travel details email, no valid sender_email', [
                'matching' => 0,
            ]);

            return ['sent' => false, 'email' => null];
        }

        $primaryDmc = $this->resolvePrimaryDmc($payload);
        $dmcUser = $this->resolveDmcUser($payload, $primaryDmc);
        $dmcName = $dmcUser
            ? trim((string) ($dmcUser->company_name ?: $dmcUser->name ?: 'DMC'))
            : 'DMC';
        $senderName = ucfirst(explode('@', $senderEmail)[0]);
        $missingItems = $this->resolveMissingTravelDetailItems($payload);

        try {
            $sent = CommonHelper::sendIncompleteTravelDetailsEmail($senderEmail, array_merge([
                'email_uuid' => $this->resolveEmailUuidFromPayload($payload),
                'subject' => $this->resolveEmailSubjectFromPayload($payload),
                'recipient_name' => $senderName,
                'dmc_name' => $dmcName,
                'dmc_label' => $dmcName,
                'dmc_logo' => $this->resolveDmcLogoForEmail($dmcUser, $payload),
                'dmc_contact_email' => $this->resolveDmcContactEmail($payload, $primaryDmc, $dmcUser),
                'missing_items' => $missingItems,
            ], $this->resolveEmailThreadPayloadFields($payload)), $dmcUser);

            if ($sent !== true) {
                Log::warning('External API incomplete travel details email not sent', [
                    'sender_email' => $senderEmail,
                    'reason' => $sent,
                ]);

                return ['sent' => false, 'email' => $senderEmail];
            }

            return ['sent' => true, 'email' => $senderEmail];
        } catch (Throwable $e) {
            Log::error('External API incomplete travel details email failed', [
                'sender_email' => $senderEmail,
                'error' => $e->getMessage(),
            ]);

            return ['sent' => false, 'email' => $senderEmail];
        }
    }

    /**
     * @return list<string>
     */
    protected function resolveMissingTravelDetailItems(array $payload): array
    {
        $fromPayload = $this->payloadValue($payload, ['missing_fields', 'missingFields', 'missing_items', 'missingItems']);
        if (is_string($fromPayload) && $fromPayload !== '') {
            $fromPayload = array_map('trim', explode(',', $fromPayload));
        }
        if (is_array($fromPayload) && $fromPayload !== []) {
            return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $fromPayload)));
        }

        return [
            'Destination country',
            'Number of nights/days of stay',
            'Travel dates',
        ];
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
        return $this->buildBookedServicesForEmailInternal($orders);
    }

    /**
     * Public entry point for building itinerary cards from persisted orders
     * (used by email preview and other callers outside this controller).
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Order>  $orders
     * @return array<int, array<string, mixed>>
     */
    public function buildBookedServicesForEmailPublic(Collection $orders): array
    {
        return $this->buildBookedServicesForEmailInternal($orders);
    }

    protected function buildBookedServicesForEmailInternal(Collection $orders): array
    {
        $services = [];

        foreach ($orders as $order) {
            $typeKey = strtolower(trim((string) ($order->type ?? '')));
            if ($typeKey === '' || $typeKey === 'enquiry') {
                continue;
            }

            $services[] = $this->buildItineraryCardForOrder($order);
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
     * Build one itinerary card from a persisted orders row (type + data JSON).
     *
     * @return array<string, mixed>
     */
    protected function buildItineraryCardForOrder(Order $order): array
    {
        $data = $this->orderDataRow($order);
        $typeKey = strtolower(trim((string) ($order->type ?? 'service')));
        $dayNum = $data['external_day'] ?? $data['day'] ?? null;

        $badge = $this->formatItineraryBadge($typeKey);
        $name = $this->resolveOrderServiceName($typeKey, $data);
        $date = $this->formatServiceBookingDateForEmail($typeKey, $data);
        $time = $this->resolveServiceTimeForEmail($typeKey, $data);
        $pax = $this->resolveServicePaxLabel($typeKey, $data);
        $priceValue = $this->resolveOrderPrice($data);
        $lines = $this->buildItineraryLinesForOrder($typeKey, $data);

        return [
            'order_id' => $order->booking_id ?? $order->getKey(),
            'order_type' => $typeKey,
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
            '_sort_order' => (int) ($order->booking_id ?? $order->getKey() ?? 0),
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
                $lines = $this->appendEmbeddedTransferLines($lines, $data);
                break;

            case 'attraction':
                if (! empty($data['ticketName'])) {
                    $lines[] = ['label' => 'Ticket', 'value' => (string) $data['ticketName']];
                }
                if (! empty($data['visitTime'])) {
                    $lines[] = ['label' => 'Visit time', 'value' => (string) $data['visitTime']];
                }
                if (! empty($data['Selection'])) {
                    $selection = (string) $data['Selection'];
                    $lines[] = ['label' => 'Transport', 'value' => ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $selection) ?? $selection)];
                }
                $lines = $this->appendEmbeddedTransferLines($lines, $data);
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
                $lines = $this->appendEmbeddedTransferLines($lines, $data);
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

    /**
     * Show transfer details stored on the same order row (not a separate itinerary card).
     *
     * @param  list<array{label: string, value: string}>  $lines
     * @return list<array{label: string, value: string}>
     */
    protected function appendEmbeddedTransferLines(array $lines, array $data): array
    {
        $transfer = $data['transfer_options'] ?? null;
        if (! is_array($transfer) || empty($transfer['transfer_required'])) {
            return $lines;
        }

        $pickup = trim((string) (
            $transfer['pickup_location_name']
            ?? $transfer['pickup_location']
            ?? ''
        ));
        $dropoff = trim((string) (
            $transfer['destination']
            ?? $transfer['drop_location_name']
            ?? ''
        ));

        if ($pickup === '' && $dropoff === '') {
            return $lines;
        }

        if ($pickup !== '') {
            $lines[] = ['label' => 'Transfer from', 'value' => $pickup];
        }
        if ($dropoff !== '') {
            $lines[] = ['label' => 'Transfer to', 'value' => $dropoff];
        }
        if (! empty($transfer['type'])) {
            $lines[] = ['label' => 'Transfer type', 'value' => (string) $transfer['type'] . ' Transfer'];
        }
        if (! empty($transfer['way'])) {
            $lines[] = ['label' => 'Transfer way', 'value' => (string) $transfer['way']];
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

    /**
     * Send tour proposal email to the agent using DMC ai_response (QTN / ITN).
     * Non-fatal by design.
     */
    protected function notifyAgent(Tour $tour, array $payload = []): bool
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

            $dmcUser = !empty($tour->dmc_id)
                ? User::where('userId', $tour->dmc_id)->first()
                : null;

            if (CommonHelper::resolveDmcAiResponse($dmcUser) === null) {
                Log::info('External API: skipping agent email, DMC ai_response not set to QTN or ITN', [
                    'tour_id' => $tour->tour_id,
                    'dmc_id' => $dmcUser?->userId,
                ]);

                return false;
            }

            $previousUser = Auth::user();
            Auth::setUser($agent);

            try {
                $emailResult = CommonHelper::sendTourProposalEmail(
                    $tour->agent_id,
                    $tour->tour_id,
                    $tour->display_id,
                    array_merge([
                        'destination' => $tour->destination,
                        'city' => $tour->city,
                        'check_in_time' => $tour->check_in_time,
                        'check_out_time' => $tour->check_out_time,
                        'adult' => $tour->adult,
                        'child' => $tour->child,
                        'infant' => $tour->infant,
                        'email_uuid' => $this->resolveEmailUuidFromPayload($payload),
                        'subject' => $this->resolveEmailSubjectFromPayload($payload),
                    ], $this->resolveEmailThreadPayloadFields($payload)),
                    $dmcUser
                );
            } finally {
                if ($previousUser) {
                    Auth::setUser($previousUser);
                }
            }

            if ($emailResult !== true) {
                Log::warning('External API tour itinerary email not sent to agent', [
                    'tour_id' => $tour->tour_id,
                    'agent_id' => $tour->agent_id,
                    'ai_response' => CommonHelper::resolveDmcAiResponse($dmcUser),
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

    protected function resolveAgentNotificationEmail(array $payload, User $dmcUser): ?string
    {
        $agent = $this->resolveAgent($payload);
        if ($agent && ! empty($agent->email) && filter_var($agent->email, FILTER_VALIDATE_EMAIL)) {
            return trim((string) $agent->email);
        }

        $senderEmail = trim((string) $this->payloadValue($payload, ['sender_email', 'senderEmail', 'agent_email', 'agentEmail'], ''));
        if ($senderEmail !== '' && filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            return $senderEmail;
        }

        $masterId = (int) ($dmcUser->master_dmc_id ?: (CommonHelper::isMasterDmcUser($dmcUser) ? $dmcUser->userId : 0));
        $agents = $this->findAgentsForDmc($masterId > 0 ? $masterId : (int) $dmcUser->userId, $dmcUser);
        $firstAgent = $agents->first();
        if ($firstAgent && ! empty($firstAgent->email) && filter_var($firstAgent->email, FILTER_VALIDATE_EMAIL)) {
            return trim((string) $firstAgent->email);
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

        foreach (['response', 'data', 'body', 'booking', 'result'] as $wrapper) {
            if (! isset($payload[$wrapper]) || ! is_array($payload[$wrapper])) {
                continue;
            }
            foreach ($keys as $key) {
                if (
                    array_key_exists($key, $payload[$wrapper])
                    && $payload[$wrapper][$key] !== null
                    && $payload[$wrapper][$key] !== ''
                ) {
                    return $payload[$wrapper][$key];
                }
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

