<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CommonHelper;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\ExternalApiReceive;
use App\Models\Order;
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
        // The external client may send the payload either as a `payload` form field
        // (often a JSON-encoded string), as the raw JSON body, or already decoded.
        // normalizeToArray() collapses all of those into a clean associative array
        // so it is never stored double-encoded again.
        $payload = $this->normalizeToArray($request->input('payload', $request->all()));
        if ($payload === [] && trim((string) $request->getContent()) !== '') {
            $payload = $this->normalizeToArray($request->getContent());
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
            'order_created' => false,
            'tour_id' => null,
            'tour_display_id' => null,
            'order_id' => null,
            'orders_count' => 0,
            'agent_id' => null,
            'agency_id' => null,
            'email_sent' => false,
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
        // This payload is DMC-centric: identity comes from destinations[].DMC[].DMC_id
        // (a User with role DMC), NOT from an agent_id. The agent is optional and is
        // matched by the originating sender_email when an Agent record exists.
        $primaryDmc = $this->resolvePrimaryDmc($payload);
        $dmcUser = $this->resolveDmcUser($payload, $primaryDmc);

        if (!$dmcUser) {
            throw new \RuntimeException('Unable to resolve the DMC from the payload (DMC_id, DMC_email or Master_DMC_id is required).');
        }

        // Mirror store(): the tour is owned by the DMC account ($dmcId), while
        // created_by records the acting DMC user. For a DMC sub-user, created_by
        // points to the master DMC; fall back to the user itself for a master.
        $createdBy = (int) $dmcUser->userId;
        $dmcId = (int) ($dmcUser->created_by ?: $dmcUser->userId);

        // Resolve agent for this DMC: use an existing agency/agent linked to the DMC,
        // or create demo agency + agent when none exist (tours.agent_id is required for edit).
        $agent = $this->resolveOrCreateAgentForDmc($dmcUser, $dmcId, $payload, $primaryDmc);

        // Travel dates: start_date + requested_days (fall back to total_days / 1).
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

        // DMC taxes snapshot (same convention as store()): stored as an array
        // because the Tour model casts `taxes` to array.
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

        // Finalize the human-readable display id (mirrors store()).
        $tour->display_id = 'DMC-ORD' . $tour->tour_id;
        $tour->save();

        // Track the initial status transition (reuse existing helper).
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

                        $serviceGroups = [
                            'hotel' => $day['hotels'] ?? [],
                            'attraction' => $day['attractions'] ?? [],
                            'restaurant' => $day['restaurants'] ?? [],
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

                        // `services` carries its own service_type per item.
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
     * Persist a single Order linked to the tour. `data` is cast to JSON by the model.
     */
    protected function makeOrder(Tour $tour, string $type, array $item, array $meta = []): Order
    {
        $data = array_merge($item, $meta, ['source' => 'external_api']);
        $maxBookingId = (int) (Order::max('booking_id') ?? 0);
        $bookingId = (int) CommonHelper::createId($maxBookingId);

        return Order::create([
            'agent_id' => $tour->agent_id,
            'tour_id' => $tour->tour_id,
            'booking_id' => $bookingId,
            'data' => [$data],
            'type' => $type,
            'status' => 1,
            'bookingType' => 'enquiry',
        ]);
    }

    /**
     * Send the tour proposal email to the agent. Non-fatal by design.
     * Temporarily authenticates the agent so CommonHelper::getDmcId() (which
     * reads Auth::user() internally) works on this unauthenticated endpoint.
     */
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

        $email = $primaryDmc['DMC_email'] ?? ($payload['sender_email'] ?? null);
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
                return ['raw_body' => $value];
            }
            $value = $decoded;
            $loops++;
        }

        return is_array($value) ? $value : [];
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

