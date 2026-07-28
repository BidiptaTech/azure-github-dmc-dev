<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Helpers\CountryHelper;
use App\Helpers\CurrencyHelper;
use App\Models\Agent;
use App\Models\Country;
use App\Models\Enquiry;
use App\Models\EnquiryForm;
use App\Models\Order;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Crypt;

class BookingsController extends Controller
{
    /**
     * Get filtered agents based on logged-in DMC user
     */
    private function getFilteredAgents()
    {
        $user = Auth::user();
        
        // If no user or not a DMC role, return all agents
        if (!$user || !in_array($user->role_id, [11, 33, 37, 38])) {
            return Agent::where('status', 1)->get();
        }
        
        $agents = collect();
        $dmc_id = null;
        
        switch ($user->role_id) {
            case 11: // DMC
                $dmc_id = $user->userId;
                break;
                
            case 33: // Sales Head
                $dmc_id = $user->created_by;
                break;
                
            case 37: // Sales Manager
                // Get parent DMC ID by traversing up the hierarchy
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }
                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                }
                break;
                
            case 38: // Assistant Sales Manager
                // Get parent DMC ID by traversing up the hierarchy
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }
                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                }
                break;
        }
        
        if ($dmc_id) {
            // Get agents that have this DMC ID in their dmc_id field
            $agents = Agent::where('status', 1)
                ->whereRaw("CASE 
                    WHEN dmc_id IS NOT NULL 
                    THEN (
                        CASE 
                            WHEN dmc_id::text ~ '^\\[.*\\]$' 
                            THEN dmc_id::jsonb @> ?::jsonb
                            WHEN dmc_id::text ~ '^\\{.*\\}$'
                            THEN dmc_id::jsonb @> ?::jsonb
                            ELSE dmc_id::text LIKE ?
                        END
                    )
                    ELSE false
                END", [
                    json_encode([$dmc_id]),
                    json_encode([$dmc_id]),
                    "%{$dmc_id}%"
                ])
                ->get();
        }
        
        return $agents;
    }

    /**
     * Format display_id as company_code/user_code/ENQxxxx (strip DMC- prefix).
     * Expects tour items to have created_by_company_code and created_by_user_code, or dmc_company_code and created_by_user_code.
     */
    private function formatToursDisplayId($tours)
    {
        $items = $tours instanceof \Illuminate\Pagination\LengthAwarePaginator ? $tours->getCollection() : $tours;
        foreach ($items as $t) {
            $rest = preg_replace('/^DMC\-/i', '', $t->display_id ?? '');
            $companyCode = $t->dmc_company_code ?? $t->created_by_company_code ?? '';
            $userCode = $t->created_by_user_code ?? '';
            $prefixParts = array_filter([$companyCode, $userCode], 'strlen');
            $t->display_id = $prefixParts ? implode('/', $prefixParts) . '/' . $rest : $rest;
        }
        return $tours;
    }

    /**
     * Add each tour's UTC creation time converted to its destination country's timezone.
     */
    private function hydrateDestinationCreatedAt($tours): void
    {
        $items = $tours instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $tours->getCollection()
            : $tours;

        $destinations = collect($items)
            ->pluck('destination')
            ->filter(fn ($destination) => is_string($destination) && trim($destination) !== '')
            ->map(fn ($destination) => mb_strtolower(trim($destination)))
            ->unique()
            ->values()
            ->all();

        $countriesByName = empty($destinations)
            ? collect()
            : Country::query()
                ->whereIn(DB::raw('LOWER(name)'), $destinations)
                ->get(['id', 'name', 'timezone'])
                ->keyBy(fn (Country $country) => mb_strtolower(trim($country->name)));

        foreach ($items as $tour) {
            $destination = is_string($tour->destination ?? null)
                ? mb_strtolower(trim($tour->destination))
                : '';
            $country = $countriesByName->get($destination);

            $tour->destination_created_at = CountryHelper::convertUtcToDestinationDateTime(
                $tour->created_at,
                $country
            );
            $tour->destination_timezone = $country?->timezone ?: 'UTC';
        }
    }

    /**
     * Ensure the tour markup / discount columns used by the negotiation modals are available on
     * list rows. Join selects often omit them, which makes the modal show 0 for markup / discount.
     * Missing columns are back-filled from the tours table in a single batched query.
     */
    private function hydrateTourNegotiationDiscounts($tours): void
    {
        $items = $tours instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $tours->getCollection()
            : $tours;

        // Columns required for the negotiation markup/discount business calculation.
        $negotiationColumns = ['discount', 'discount_type', 'discount_amount', 'markup', 'markup_type', 'markup_amount'];

        // Collect tour ids that are missing any of these attributes on the loaded row.
        $missingIds = [];
        foreach ($items as $tour) {
            if (empty($tour->tour_id)) {
                continue;
            }
            $attrs = $tour->getAttributes();
            foreach ($negotiationColumns as $col) {
                if (!array_key_exists($col, $attrs)) {
                    $missingIds[] = $tour->tour_id;
                    break;
                }
            }
        }

        $fetched = collect();
        if (!empty($missingIds)) {
            $fetched = Tour::whereIn('tour_id', array_unique($missingIds))
                ->get(array_merge(['tour_id'], $negotiationColumns))
                ->keyBy('tour_id');
        }

        foreach ($items as $tour) {
            $source = !empty($tour->tour_id) ? $fetched->get($tour->tour_id) : null;
            if ($source) {
                $sourceAttrs = $source->getAttributes();
                foreach ($negotiationColumns as $col) {
                    $tour->{$col} = $sourceAttrs[$col] ?? null;
                }
            }
            // Normalise discount_amount for downstream display.
            $tour->discount_amount = max(0, (float) ($tour->getAttributes()['discount_amount'] ?? $tour->discount_amount ?? 0));
        }
    }

    /**
     * Attach orders and per-country negotiation totals (native currency, no conversion).
     * Groups order amounts by country/currency so the modal can show one offer field per country.
     */
    private function hydrateTourNegotiationCurrencyData($tours): void
    {
        $items = $tours instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $tours->getCollection()
            : $tours;

        if ($items->isEmpty()) {
            return;
        }

        $tourIds = $items->pluck('tour_id')->filter()->unique()->values()->all();
        $ordersByTour = Order::query()
            ->whereIn('tour_id', $tourIds)
            ->get(['booking_id', 'tour_id', 'type', 'status', 'data', 'country', 'currency'])
            ->groupBy('tour_id');

        $destinationNames = [];
        foreach ($items as $tour) {
            foreach ($this->parseDestinationCountryNames($tour->destination ?? null) as $name) {
                $destinationNames[mb_strtolower($name)] = $name;
            }
        }

        foreach ($ordersByTour->flatten(1) as $order) {
            if (is_string($order->country ?? null) && trim($order->country) !== '') {
                $name = trim($order->country);
                $destinationNames[mb_strtolower($name)] = $name;
            }
        }

        $allowedCodes = CommonHelper::getPaymentAvailableCurrencies();
        $countriesByName = empty($destinationNames)
            ? collect()
            : Country::query()
                ->whereIn(DB::raw('LOWER(name)'), array_keys($destinationNames))
                ->get(['id', 'name', 'currency'])
                ->keyBy(fn (Country $country) => mb_strtolower(trim($country->name)));

        foreach ($items as $tour) {
            $tourOrders = $ordersByTour->get($tour->tour_id, collect());
            $tour->setRelation('booking', $tourOrders);

            $groups = [];
            foreach ($tourOrders as $order) {
                if (! in_array((int) ($order->status ?? 0), [1, 3], true)) {
                    continue;
                }

                $amount = $this->extractOrderNegotiationAmount($order, (int) ($tour->is_pro ?? 0));
                if ($amount <= 0) {
                    continue;
                }

                $countryName = is_string($order->country ?? null) && trim($order->country) !== ''
                    ? trim($order->country)
                    : null;

                $currency = CurrencyHelper::normalizeCurrencyToCode(
                    is_string($order->currency ?? null) ? $order->currency : null,
                    $allowedCodes,
                    ''
                );
                if ($currency === '' && $countryName) {
                    $country = $countriesByName->get(mb_strtolower($countryName));
                    $currency = CurrencyHelper::normalizeCurrencyToCode(
                        $country?->currency ?? null,
                        $allowedCodes,
                        ''
                    );
                    if (! $countryName && $country) {
                        $countryName = $country->name;
                    }
                }
                if ($currency === '') {
                    $currency = 'SGD';
                }
                if (! $countryName) {
                    $countryName = $currency;
                }

                $key = mb_strtolower($countryName) . '|' . $currency;
                if (! isset($groups[$key])) {
                    $groups[$key] = [
                        'key' => $key,
                        'country' => $countryName,
                        'currency' => $currency,
                        'gross' => 0.0,
                        'order_count' => 0,
                    ];
                }
                $groups[$key]['gross'] += $amount;
                $groups[$key]['order_count']++;
            }

            // Apply tour markup/discount per country bucket (percentage on each; flat on first only).
            $markupType = $tour->markup_type ?? null;
            $markupRaw = (float) ($tour->getAttributes()['markup_amount'] ?? $tour->markup_amount ?? 0);
            $markupOn = ((int) ($tour->markup ?? 0) === 1)
                && $markupRaw > 0
                && in_array($markupType, ['percentage', 'flat'], true);

            $discountType = $tour->discount_type ?? null;
            $discountRaw = (float) ($tour->getAttributes()['discount_amount'] ?? $tour->discount_amount ?? 0);

            $index = 0;
            $countryGroups = [];
            foreach ($groups as $group) {
                $gross = (float) ceil($group['gross']);
                $markupMoney = 0.0;
                if ($markupOn) {
                    if ($markupType === 'percentage') {
                        $markupMoney = $gross * $markupRaw / 100;
                    } elseif ($index === 0) {
                        $markupMoney = $markupRaw;
                    }
                }

                $discountMoney = 0.0;
                $discountBase = $gross + $markupMoney;
                if ($discountType === 'percentage' && $discountRaw > 0) {
                    $discountMoney = $discountBase * $discountRaw / 100;
                } elseif (in_array($discountType, ['flat', 'foc'], true) && $discountRaw > 0 && $index === 0) {
                    $discountMoney = $discountRaw;
                }

                $payable = max(0, ceil($gross + $markupMoney - $discountMoney));
                $countryGroups[] = [
                    'key' => $group['key'],
                    'country' => $group['country'],
                    'currency' => $group['currency'],
                    'gross' => $gross,
                    'markup' => round($markupMoney, 2),
                    'discount' => round($discountMoney, 2),
                    'payable' => $payable,
                    'order_count' => $group['order_count'],
                    'markup_type' => $markupType,
                    'markup_raw' => $markupRaw,
                    'discount_type' => $discountType,
                    'discount_raw' => $discountRaw,
                ];
                $index++;
            }

            $tour->negotiation_country_groups = $countryGroups;
        }
    }

    /**
     * @return array<int, string>
     */
    private function parseDestinationCountryNames(?string $destination): array
    {
        if (! is_string($destination) || trim($destination) === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/', $destination) ?: [];
        $names = [];
        foreach ($parts as $part) {
            $name = trim((string) $part);
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Extract a single order's contribution to tour gross (mirrors blade / CommonHelper logic).
     */
    private function extractOrderNegotiationAmount(Order $order, int $isPro = 0): float
    {
        $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
        if (! is_array($data)) {
            return 0.0;
        }

        $orderType = $order->type ?? '';
        $total = 0.0;

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            $itemPrice = (float) ($item['totalPrice'] ?? $item['price'] ?? 0);
            $transferPrice = 0.0;
            if ($orderType !== 'hotel' && isset($item['transfer_options']['cost']) && $item['transfer_options']['cost'] > 0) {
                if ($isPro === 1 && isset($item['transfer_options']['totalPrice'])) {
                    $transferPrice = (float) $item['transfer_options']['totalPrice'];
                } else {
                    $transferPrice = (float) $item['transfer_options']['cost'];
                }
            }

            $guidePrice = 0.0;
            if (isset($item['guide_options']) && is_array($item['guide_options'])) {
                $gv = $item['guide_options']['total_price']
                    ?? $item['guide_options']['cost']
                    ?? $item['guide_options']['Cost']
                    ?? $item['guide_options']['sell']
                    ?? $item['guide_options']['Sell']
                    ?? 0;
                if ($gv > 0) {
                    $guidePrice = (float) $gv;
                }
            }

            $total += $itemPrice + $transferPrice + $guidePrice;
        }

        return $total;
    }

    /**
     * Display New Enquiries (tour_status = 'New Enquiry')
     */
    public function newEnquiries()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);
        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::where('tour_status', 'New Enquiry')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                            ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
                    'tours.hotel',
                    'tours.attraction',
                    'tours.travel',
                    'tours.restaurent',
                    'tours.guide',
                    'tours.port',
                    'tours.destination',
                    'tours.city',
                    'tours.is_pro',
                    'tours.check_in_time',
                    'tours.check_out_time',
                    'tours.tour_status',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'tours.created_by',
                    'tours.mainguest',
                    'tours.discount_amount',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'dmc_user.company_code as dmc_company_code',
                    'created_by_user.user_code as created_by_user_code'
                    ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->hydrateTourNegotiationCurrencyData($tours);

            foreach ($tours as $t) {
                $rest = preg_replace('/^DMC\-/i', '', $t->display_id ?? '');
                $prefixParts = array_filter([
                    $t->dmc_company_code ?? '',
                    $t->created_by_user_code ?? ''
                ], 'strlen');
                $t->display_id = $prefixParts ? implode('/', $prefixParts) . '/' . $rest : $rest;
            }

                foreach ($tours as $t) {
                    \Log::info('New Enquiry guest debug', [
                        'tour_id'        => $t->tour_id,
                        'display_id'     => $t->display_id,
                        'mainguest_raw'  => $t->mainguest,
                        'customer_name'  => $t->customer_name ?? null,
                    ]);
                }
        }

        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::where('tour_status', 'New Enquiry')
                ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmc_id, Auth::user()))
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
                    'tours.hotel',
                    'tours.attraction',
                    'tours.travel',
                    'tours.restaurent',
                    'tours.guide',
                    'tours.port',
                    'tours.destination',
                    'tours.city',
                    'tours.is_pro',
                    'tours.check_in_time',
                    'tours.check_out_time',
                    'tours.tour_status',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'tours.created_by',
                    'tours.mainguest',
                    'tours.discount_amount',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'dmc_user.company_code as dmc_company_code',
                    'created_by_user.user_code as created_by_user_code'
                    ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->hydrateTourNegotiationCurrencyData($tours);

            foreach ($tours as $t) {
                $rest = preg_replace('/^DMC\-/i', '', $t->display_id ?? '');
                $prefixParts = array_filter([
                    $t->dmc_company_code ?? '',
                    $t->created_by_user_code ?? ''
                ], 'strlen');
                $t->display_id = $prefixParts ? implode('/', $prefixParts) . '/' . $rest : $rest;
            }

            foreach ($tours as $t) {
                \Log::info('New Enquiry guest debug (DMC scope)', [
                    'tour_id'        => $t->tour_id,
                    'display_id'     => $t->display_id,
                    'mainguest_raw'  => $t->mainguest,
                    'customer_name'  => $t->customer_name ?? null,
                ]);
            }
        }

        $enquary_comments = Enquiry::where('dmcId', $dmc_id)->get();
 
        // Get filtered agents based on logged-in DMC user
        $filteredAgents = $this->getFilteredAgents();
        $currency = Country::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) ($user->country ?? ''))])
            ->value('currency');
        $currency = is_string($currency) && trim($currency) !== '' ? trim($currency) : (CommonHelper::getDmcCurrencyByCountry() ?: 'SGD');
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        $this->hydrateDestinationCreatedAt($tours);
        return view('bookings.new-enquiries', compact('tours', 'filteredAgents', 'enquary_comments', 'country_tax', 'currency'));
    }

    public function agentNegotiation(Request $request)
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer|exists:tours,tour_id',
            'action' => 'required|in:negotiate,cancel,confirm',
            'comment' => 'nullable|string|max:1000',
            'offers' => 'required_if:action,negotiate|array|min:1',
            'offers.*.country' => 'required_with:offers|string|max:255',
            'offers.*.currency' => 'required_with:offers|string|max:10',
            'offers.*.amount' => 'required_with:offers|numeric|min:0.01',
            'offers.*.actual_amount' => 'required_with:offers|numeric|min:0',
            'offers.*.gross' => 'nullable|numeric|min:0',
            // Legacy single-amount fields kept optional for older clients.
            'amount' => 'nullable|numeric|min:0.01',
            'currency' => 'required_if:action,confirm|nullable|string|max:10',
        ], [
            'offers.required_if' => 'Please enter a negotiation amount for each country.',
            'offers.*.amount.required_with' => 'Please enter a negotiation amount for each country.',
            'currency.required_if' => 'Please select a currency before confirming the tour.',
        ]);

        $tour = Tour::where('tour_id', $validated['tour_id'])->firstOrFail();
        $action = $validated['action'];

        $currentUser = auth()->user();
        $changedByName = $currentUser ? ($currentUser->name ?? '') : null;
        $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;

        $latestEnquiry = Enquiry::where('tour_id', $tour->tour_id)->orderByDesc('created_at')->first();
        $activeEnquiry = Enquiry::where('tour_id', $tour->tour_id)
            ->where('status', 1)
            ->orderByDesc('created_at')
            ->first();

        if ($action === 'negotiate') {
            $offers = array_values(array_map(function ($offer) {
                return [
                    'country' => trim((string) ($offer['country'] ?? '')),
                    'currency' => strtoupper(trim((string) ($offer['currency'] ?? ''))),
                    'amount' => round((float) ($offer['amount'] ?? 0), 2),
                    'actual_amount' => round((float) ($offer['actual_amount'] ?? 0), 2),
                    'gross' => round((float) ($offer['gross'] ?? 0), 2),
                ];
            }, $validated['offers'] ?? []));

            $primary = $offers[0] ?? null;
            $amountOffered = (float) ($primary['amount'] ?? 0);
            $actualAmount = (float) ($primary['actual_amount'] ?? 0);
            $grossAtNegotiation = (float) ($primary['gross'] ?? 0);
            if ($grossAtNegotiation <= 0) {
                $grossAtNegotiation = \App\Helpers\CommonHelper::calculateTourGrossAmount($tour);
            }

            $enquiry = Enquiry::create([
                'tour_id' => $tour->tour_id,
                'status' => 1,
                'dmcId' => $tour->dmc_id,
                'sender_id' => $tour->agent_id ?? 0,
                'sender_type' => 'agent',
                'receiver_id' => $latestEnquiry->sender_id ?? 0,
                'receiver_type' => 'OM',
                'current_position' => 'OM',
                'amount' => $amountOffered,
                'actual_amount' => $actualAmount ?: ($latestEnquiry->actual_amount ?? 0),
                'gross_amount' => $grossAtNegotiation,
                'comment' => $validated['comment'] ?? '',
                'negotiation_details' => $offers,
            ]);
            $enquiry->refresh();
            if ($enquiry && $activeEnquiry && $activeEnquiry->id !== $enquiry->id) {
                $activeEnquiry->update(['status' => 0]);
            }

            if ($enquiry) {
                return back()->with('success', 'Agent negotiation submitted successfully!');
            }

            return back()->with('error', 'Unable to submit negotiation. Please try again.');
        }

        if ($action === 'cancel') {
            $oldStatus = $tour->tour_status;

            if ($activeEnquiry) {
                $activeEnquiry->update(['status' => 3]);
            }

            $newStatus = $tour->tour_status === 'Definite'
                ? 'Refund - Pending'
                : 'Cancel - ' . $tour->tour_status;

            // Track status change (e.g. New Enquiry -> Cancel - New Enquiry, Definite -> Refund - Pending)
            \App\Helpers\CommonHelper::appendTourStatusTrackById(
                (int) $tour->tour_id,
                $oldStatus,
                $newStatus,
                null,
                null,
                null,
                null,
                $changedByName,
                $changedByUserId
            );

            $tour->update(['tour_status' => $newStatus]);

            return back()->with('success', 'Tour #' . $tour->tour_id . ' cancelled successfully! Status has been updated to ' . $newStatus . '.');
        }

        if ($action === 'confirm') {
            $confirmCurrency = strtoupper(trim((string) ($validated['currency'] ?? '')));
            if ($confirmCurrency === '') {
                return back()
                    ->withErrors(['currency' => 'Please select a currency before confirming the tour.'])
                    ->withInput();
            }

            $offerRows = [];
            if (! empty($validated['offers']) && is_array($validated['offers'])) {
                $offerRows = array_values(array_map(function ($offer) {
                    return [
                        'country' => trim((string) ($offer['country'] ?? '')),
                        'currency' => strtoupper(trim((string) ($offer['currency'] ?? ''))),
                        'amount' => round((float) ($offer['amount'] ?? 0), 2),
                        'actual_amount' => round((float) ($offer['actual_amount'] ?? 0), 2),
                        'gross' => round((float) ($offer['gross'] ?? 0), 2),
                    ];
                }, $validated['offers']));
            } elseif (is_array($activeEnquiry?->negotiation_details) && ! empty($activeEnquiry->negotiation_details)) {
                $offerRows = $activeEnquiry->negotiation_details;
            } elseif (is_array($latestEnquiry?->negotiation_details) && ! empty($latestEnquiry->negotiation_details)) {
                $offerRows = $latestEnquiry->negotiation_details;
            }

            $converted = $this->convertNegotiationOffersToCurrency($offerRows, $confirmCurrency);
            if ($converted['error'] !== null) {
                return back()
                    ->withErrors(['currency' => $converted['error']])
                    ->withInput();
            }

            $convertedNegotiated = (float) ($converted['negotiated_amount'] ?? 0);
            $convertedActual = (float) ($converted['actual_amount'] ?? 0);
            $convertedGross = (float) ($converted['gross_amount'] ?? 0);
            $convertedDetails = $converted['negotiation_details'];

            // Fallback if payable/gross were missing on older offer rows.
            if ($convertedActual <= 0) {
                $convertedActual = $convertedNegotiated;
            }
            if ($convertedGross <= 0) {
                $convertedGross = $convertedActual > 0 ? $convertedActual : $convertedNegotiated;
            }

            $enquiryPayload = [
                'status' => 2,
                'currency' => $confirmCurrency,
                'comment' => $validated['comment'] ?? null,
                'gross_amount' => $convertedGross,
                'negotiation_details' => $convertedDetails,
                'amount' => $convertedNegotiated,
                'actual_amount' => $convertedActual,
            ];

            if ($activeEnquiry) {
                $enquiryPayload['comment'] = $validated['comment'] ?? $activeEnquiry->comment;
                $activeEnquiry->update($enquiryPayload);
                $confirmedEnquiry = $activeEnquiry->fresh();
            } elseif ($latestEnquiry) {
                $enquiryPayload['comment'] = $validated['comment'] ?? $latestEnquiry->comment;
                $latestEnquiry->update($enquiryPayload);
                $confirmedEnquiry = $latestEnquiry->fresh();
            } else {
                $confirmedEnquiry = Enquiry::create([
                    'tour_id' => $tour->tour_id,
                    'status' => 2,
                    'dmcId' => $tour->dmc_id,
                    'sender_id' => $tour->agent_id ?? ($currentUser->userId ?? 0),
                    'sender_type' => 'agent',
                    'receiver_id' => 0,
                    'receiver_type' => 'OM',
                    'current_position' => 'OM',
                    'amount' => $convertedNegotiated,
                    'actual_amount' => $convertedActual,
                    'gross_amount' => $convertedGross,
                    'comment' => $validated['comment'] ?? '',
                    'currency' => $confirmCurrency,
                    'negotiation_details' => $convertedDetails,
                ]);
            }

            Order::where('tour_id', $tour->tour_id)->update(['bookingType' => 'booking']);

            if ($tour->tour_status !== 'Confirmed') {
                $oldStatus = $tour->tour_status;

                $actualAmount = (float) ($confirmedEnquiry?->actual_amount ?? $convertedActual);
                $amount = (float) ($confirmedEnquiry?->amount ?? $convertedNegotiated);
                if ($actualAmount <= 0) {
                    $actualAmount = $convertedActual > 0
                        ? $convertedActual
                        : ($convertedNegotiated > 0 ? $convertedNegotiated : $this->calculateOrdersTotalAmount($tour->tour_id));
                }
                if ($amount <= 0) {
                    $amount = $convertedNegotiated > 0 ? $convertedNegotiated : $actualAmount;
                }

                \App\Helpers\CommonHelper::appendTourStatusTrackById(
                    (int) $tour->tour_id,
                    $oldStatus,
                    'Confirmed',
                    null,
                    $amount,
                    $validated['comment'] ?? ($confirmedEnquiry?->comment ?? null),
                    $actualAmount,
                    $changedByName,
                    $changedByUserId
                );

                $tour->update(['tour_status' => 'Confirmed']);
            }

            if (!empty($tour->multi_enq_id)) {
                $formEnquiry = EnquiryForm::where('multi_enq_id', (string) $tour->multi_enq_id)->first();

                if ($formEnquiry && $formEnquiry->multi_enq_id) {
                    EnquiryForm::where('multi_enq_id', (string) $formEnquiry->multi_enq_id)
                        ->where('enquiry_id', '!=', $formEnquiry->enquiry_id)
                        ->update(['status' => 'cancelled']);
                }

                Tour::where('multi_enq_id', (string) $tour->multi_enq_id)
                    ->where('tour_id', '!=', $tour->tour_id)
                    ->update(['deleted_at' => now()]);
            }

            return back()->with('success', 'Tour #' . $tour->tour_id . ' confirmed successfully! Status has been updated to Confirmed and booking has been finalized.');
        }

        return back()->with('error', 'Unsupported action requested.');
    }

    /**
     * Convert each country's negotiated / payable / gross amounts into the selected currency,
     * attach conversion_rate + date_of_conversion on every row, and return the summed totals.
     *
     * @param  array<int, array<string, mixed>>  $offers
     * @return array{
     *     negotiated_amount: float,
     *     actual_amount: float,
     *     gross_amount: float,
     *     negotiation_details: array<int, array<string, mixed>>,
     *     error: string|null
     * }
     */
    private function convertNegotiationOffersToCurrency(array $offers, string $targetCurrency): array
    {
        $targetCurrency = strtoupper(trim($targetCurrency));
        $conversionDate = now()->toDateTimeString();
        $enriched = [];
        $convertedNegotiatedTotal = 0.0;
        $convertedActualTotal = 0.0;
        $convertedGrossTotal = 0.0;
        $rateCache = [];

        $emptyError = static function (string $error) {
            return [
                'negotiated_amount' => 0.0,
                'actual_amount' => 0.0,
                'gross_amount' => 0.0,
                'negotiation_details' => [],
                'error' => $error,
            ];
        };

        if ($targetCurrency === '') {
            return $emptyError('Please select a currency before confirming the tour.');
        }

        if (empty($offers)) {
            return $emptyError('No negotiated country amounts were found to convert.');
        }

        foreach ($offers as $offer) {
            if (! is_array($offer)) {
                continue;
            }

            $fromCurrency = strtoupper(trim((string) ($offer['currency'] ?? '')));
            $country = trim((string) ($offer['country'] ?? ''));
            $amount = round((float) ($offer['amount'] ?? 0), 2);
            $actualAmount = round((float) ($offer['actual_amount'] ?? 0), 2);
            $gross = round((float) ($offer['gross'] ?? 0), 2);

            if ($fromCurrency === '' || $amount <= 0) {
                return $emptyError('Each country must have a valid negotiated amount and currency before confirmation.');
            }

            if (! array_key_exists($fromCurrency, $rateCache)) {
                $rate = CurrencyHelper::getExchangeRate($fromCurrency, $targetCurrency);
                if ($rate === null || $rate <= 0) {
                    return $emptyError('Unable to fetch exchange rate from ' . $fromCurrency . ' to ' . $targetCurrency . '.');
                }
                $rateCache[$fromCurrency] = (float) $rate;
            }

            $conversionRate = $rateCache[$fromCurrency];
            $convertedAmount = round($amount * $conversionRate, 2);
            $convertedActual = round(max(0, $actualAmount) * $conversionRate, 2);
            $convertedGross = round(max(0, $gross) * $conversionRate, 2);

            $convertedNegotiatedTotal += $convertedAmount;
            $convertedActualTotal += $convertedActual;
            $convertedGrossTotal += $convertedGross;

            $enriched[] = [
                'country' => $country !== '' ? $country : $fromCurrency,
                'currency' => $fromCurrency,
                'amount' => $amount,
                'actual_amount' => $actualAmount,
                'gross' => $gross,
                'conversion_rate' => round($conversionRate, 8),
                'date_of_conversion' => $conversionDate,
                'converted_amount' => $convertedAmount,
                'converted_actual_amount' => $convertedActual,
                'converted_gross' => $convertedGross,
                'target_currency' => $targetCurrency,
            ];
        }

        if (empty($enriched)) {
            return $emptyError('No negotiated country amounts were found to convert.');
        }

        return [
            'negotiated_amount' => round($convertedNegotiatedTotal, 2),
            'actual_amount' => round($convertedActualTotal, 2),
            'gross_amount' => round($convertedGrossTotal, 2),
            'negotiation_details' => $enriched,
            'error' => null,
        ];
    }

    private function calculateOrdersTotalAmount(int $tourId): float
    {
        $orders = Order::where('tour_id', $tourId)->get();
        $sum = 0;

        foreach ($orders as $order) {
            $payload = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            $sum += $this->extractOrderPayloadTotal($payload);
        }

        return round($sum, 2);
    }

    private function extractOrderPayloadTotal($payload): float
    {
        if (is_object($payload)) {
            $payload = (array) $payload;
        }

        if (!is_array($payload)) {
            return 0;
        }

        $priorityKeys = ['totalPrice', 'total_price', 'price', 'amount'];
        foreach ($priorityKeys as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return (float) $payload[$key];
            }
        }

        $sum = 0;
        foreach ($payload as $value) {
            if (is_array($value) || is_object($value)) {
                $sum += $this->extractOrderPayloadTotal($value);
            }
        }

        return $sum;
    }

    /**
     * Display Follow Ups (tour_status = 'Prospect' and 'Tentative')
     */
    // public function followUps()
    // {
    //     $user = Auth::user();
    //     $dmc_id = null;
    //     $tours = collect([]);

    //     if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
    //         $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
    //         ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
    //         ->leftJoin('enquiry_comments', 'tours.tour_id', '=', 'enquiry_comments.tour_id')
    //         ->select([
    //             'tours.tour_id',
    //             'tours.display_id',
    //             'tours.multi_enq_id',
    //             'tours.adult',
    //             'tours.child',
    //             'tours.hotel',
    //             'tours.attraction',
    //             'tours.travel',
    //             'tours.restaurent',
    //             'tours.guide',
    //             'tours.port',
    //             'tours.destination',
    //             'tours.city',
    //             'tours.check_in_time',
    //             'tours.check_out_time',
    //             'tours.tour_status',
    //             'tours.created_at',
    //             'tours.updated_at',
    //             'tours.agent_id',
    //             'agents.name as agent_name',
    //             'enquiry_comments.enquiry_id as enquiry_id',
    //             'enquiry_comments.comment as enquiry_comment',
    //             'enquiry_comments.amount as enquiry_comment_amount',
    //             'enquiry_comments.actual_amount as actual_amount',
    //             'enquiry_comments.created_at as enquiry_comment_created_at',
    //             'enquiry_comments.updated_at as enquiry_comment_updated_at',
    //         ])
    //         ->orderBy('tours.created_at', 'desc')
    //         ->paginate(105);

    //     }
        
    //     if($user->role_id == 11){
    //         $dmc_id = $user->userId;
    //     }else if($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
    //         $dmc_id = $user->created_by;
    //     }else if($user->role_id == 37){
    //         $sales_head = User::where('userId', $user->created_by)->first();
    //         $dmc_id = $sales_head->created_by;
    //     }else if($user->role_id == 38){
    //         $sales_manager = User::where('userId', $user->created_by)->first();
    //         $sales_head = User::where('userId', $sales_manager->created_by)->first();
    //         $dmc_id = $sales_head->created_by;
    //     }

    //     if($dmc_id){
    //         $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
    //             ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
    //             ->leftJoin('enquiry_comments', 'tours.tour_id', '=', 'enquiry_comments.tour_id')
    //             ->select([
    //                 'tours.tour_id',
    //                 'tours.display_id',
    //                 'tours.multi_enq_id',
    //                 'tours.adult',
    //                 'tours.child',
    //                 'tours.hotel',
    //                 'tours.attraction',
    //                 'tours.travel',
    //                 'tours.restaurent',
    //                 'tours.guide',
    //                 'tours.port',
    //                 'tours.destination',
    //                 'tours.city',
    //                 'tours.check_in_time',
    //                 'tours.check_out_time',
    //                 'tours.tour_status',
    //                 'tours.created_at',
    //                 'tours.updated_at',
    //                 'tours.agent_id',
    //                 'agents.name as agent_name',
    //                 'enquiry_comments.enquiry_id as enquiry_id',
    //                 'enquiry_comments.comment as enquiry_comment',
    //                 'enquiry_comments.amount as enquiry_comment_amount',
    //                 'enquiry_comments.actual_amount as actual_amount',
    //                 'enquiry_comments.created_at as enquiry_comment_created_at',
    //                 'enquiry_comments.updated_at as enquiry_comment_updated_at',
    //             ])
    //             ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmc_id, Auth::user()))
    //             ->orderBy('tours.created_at', 'desc')
    //             ->paginate(15);

    //     }
    //     return view('bookings.follow-ups', compact('tours'));
    // }

    public function followUps()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->leftJoin('enquiry_comments', function($join) {
                $join->on('tours.tour_id', '=', 'enquiry_comments.tour_id')
                     ->whereRaw('enquiry_comments.enquiry_id = (
                         SELECT MAX(ec2.enquiry_id) 
                         FROM enquiry_comments ec2 
                         WHERE ec2.tour_id = tours.tour_id
                     )');
            })
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.mainguest',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.is_pro',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.created_by',
                'tours.discount_amount',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'enquiry_comments.enquiry_id as enquiry_id',
                'enquiry_comments.comment as enquiry_comment',
                'enquiry_comments.amount as enquiry_comment_amount',
                'enquiry_comments.actual_amount as actual_amount',
                'enquiry_comments.sender_type as enquiry_comment_sender_type',
                'enquiry_comments.created_at as enquiry_comment_created_at',
                'enquiry_comments.updated_at as enquiry_comment_updated_at',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code',
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->hydrateTourNegotiationCurrencyData($tours);
            $this->formatToursDisplayId($tours);
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::whereIn('tour_status', ['Prospect', 'Tentative'])
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                ->leftJoin('enquiry_comments', function($join) {
                    $join->on('tours.tour_id', '=', 'enquiry_comments.tour_id')
                         ->whereRaw('enquiry_comments.enquiry_id = (
                             SELECT MAX(ec2.enquiry_id) 
                             FROM enquiry_comments ec2 
                             WHERE ec2.tour_id = tours.tour_id
                         )');
                })
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
                    'tours.hotel',
                    'tours.attraction',
                    'tours.travel',
                    'tours.restaurent',
                    'tours.guide',
                    'tours.port',
                    'tours.destination',
                    'tours.city',
                    'tours.mainguest',
                    'tours.check_in_time',
                    'tours.check_out_time',
                    'tours.tour_status',
                    'tours.is_pro',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'tours.created_by',
                    'tours.discount_amount',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'enquiry_comments.enquiry_id as enquiry_id',
                    'enquiry_comments.comment as enquiry_comment',
                    'enquiry_comments.amount as enquiry_comment_amount',
                    'enquiry_comments.actual_amount as actual_amount',
                    'enquiry_comments.sender_type as enquiry_comment_sender_type',
                    'enquiry_comments.created_at as enquiry_comment_created_at',
                    'enquiry_comments.updated_at as enquiry_comment_updated_at',
                    'dmc_user.company_code as dmc_company_code',
                    'created_by_user.user_code as created_by_user_code',
                ])
                ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmc_id, Auth::user()))
                ->orderBy('tours.created_at', 'desc')
                ->get();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->hydrateTourNegotiationCurrencyData($tours);
            $this->formatToursDisplayId($tours);
        }
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        $currency = CommonHelper::getDmcCurrencyByCountry();
        $this->hydrateDestinationCreatedAt($tours);
        return view('bookings.follow-ups', compact('tours', 'country_tax', 'currency'));
    }

    /**
     * Display Tentative Bookings (tour_status = 'Tentative')
     */
    public function tentative()
    {
        $user = Auth::user();
        $tours = Tour::where('tour_status', 'Tentative')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code',
                'created_by_user.company_code as created_by_company_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->paginate(15);
        $this->formatToursDisplayId($tours);
        $currency = CommonHelper::getDmcCurrencyByCountry();
        $country_tax = Country::where('name', optional($user)->country)->value('tax_percentage');
        $this->hydrateDestinationCreatedAt($tours);
        return view('bookings.tentative', compact('tours', 'country_tax', 'currency'));
    }

    /**
     * Display Confirmed Bookings (tour_status = 'Confirmed')
     */
    public function confirmedBookings()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
            ->where('tour_status', 'Confirmed')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.dmc_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.taxes',
                'tours.is_pro',
                'tours.user_currency',
                'tours.mainguest',
                'tours.discount_amount',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->formatToursDisplayId($tours);
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
            ->where('tour_status', 'Confirmed')
            ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmc_id, Auth::user()))
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.dmc_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.taxes',
                'tours.is_pro',
                'tours.user_currency',
                'tours.mainguest',
                'tours.discount_amount',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->formatToursDisplayId($tours);
        }
        $currency = CommonHelper::getDmcCurrencyByCountry();

        // Prepare exchange-rate sources for the "Add Payment" modal:
        // - DMC rate: countries.exchange_rate JSON keyed by tour.dmc_id
        // - Previous rate: last entry in tours.payment_details JSON array
        if ($tours && $tours->count() > 0) {
            // Build a lightweight lookup of countries by normalized name for best-effort matching.
            $destinations = $tours->pluck('destination')
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->map(fn ($v) => mb_strtolower(trim($v)))
                ->unique()
                ->values()
                ->all();

            $countriesByName = [];
            if (!empty($destinations)) {
                Country::query()
                    ->whereIn(DB::raw('LOWER(name)'), $destinations)
                    ->get()
                    ->each(function ($c) use (&$countriesByName) {
                        $key = is_string($c->name) ? mb_strtolower(trim($c->name)) : '';
                        if ($key !== '') {
                            $countriesByName[$key] = $c;
                        }
                    });
            }
            

            foreach ($tours as $tour) {
                // Previous rate/currency from last payment_details entry (if any).
                $prevRate = null;
                $prevCurrency = null;
                $isDefiniteTour = is_string($tour->tour_status) && strcasecmp($tour->tour_status, 'Definite') === 0;
                if ($isDefiniteTour) {
                    $paymentDetails = $tour->payment_details;
                    if (is_string($paymentDetails) && trim($paymentDetails) !== '') {
                        $decoded = json_decode($paymentDetails, true);
                        $paymentDetails = is_array($decoded) ? $decoded : [];
                    }
                    if (is_array($paymentDetails) && !empty($paymentDetails)) {
                        $last = end($paymentDetails);
                        if (is_array($last)) {
                            $prevRate = $last['exchange_rate'] ?? null;
                            $prevCurrency = $last['currency'] ?? null;
                        }
                    }
                }
                $tour->previous_exchange_rate = is_scalar($prevRate) ? (string) $prevRate : null;
                $tour->previous_payment_currency = is_scalar($prevCurrency) ? (string) $prevCurrency : null;

                // DMC rate lookup: countries.exchange_rate[dmc_id].value
                $dmcRate = null;
                $destKey = is_string($tour->destination) ? mb_strtolower(trim($tour->destination)) : '';
                $country = $destKey !== '' ? ($countriesByName[$destKey] ?? null) : null;
                
                if ($country && !empty($dmc_id)) {
                    
                    // countries.exchange_rate format is an array of objects, e.g.
                    // [
                    //   { "dmc_id": 4, "exchange_rate": 5 }
                    // ]
                    $exchangeRateRaw = $country->exchange_rate;
                    
                    if (is_string($exchangeRateRaw) && trim($exchangeRateRaw) !== '') {
                        $decoded = json_decode($exchangeRateRaw, true);
                        $exchangeRateRaw = is_array($decoded) ? $decoded : [];
                    }
                    $exchangeRateArr = is_array($exchangeRateRaw) ? $exchangeRateRaw : [];
                    
                    $match = collect($exchangeRateArr)->firstWhere('dmc_id', (int) $dmc_id);
                    if (is_array($match)) {
                        $dmcRate = $match['exchange_rate'] ?? null;
                    }
                }
                
                $tour->dmc_exchange_rate_value = is_scalar($dmcRate) ? (string) $dmcRate : null;
            }
        }

        $tourIds = $tours->pluck('tour_id')->toArray();
        $tourNegotiationHistory = [];
        if (!empty($tourIds)) {
            $withComments = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('tour_id')
                ->flip()
                ->toArray();
            foreach ($tourIds as $tid) {
                $tourNegotiationHistory[$tid] = isset($withComments[$tid]);
            }

            // Latest non-empty negotiation currency per tour (for Add Payment modal).
            $enquiryCurrencies = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->whereNotNull('currency')
                ->where('currency', '!=', '')
                ->orderByDesc('id')
                ->get(['tour_id', 'currency'])
                ->unique('tour_id')
                ->pluck('currency', 'tour_id');

            foreach ($tours as $tour) {
                $enquiryCurrency = $enquiryCurrencies->get($tour->tour_id);
                $tour->enquiry_currency = is_string($enquiryCurrency) && trim($enquiryCurrency) !== ''
                    ? trim($enquiryCurrency)
                    : null;
            }
        }

        $this->hydrateDestinationCreatedAt($tours);
        return view('bookings.confirmed', compact('tours', 'currency', 'tourNegotiationHistory'));
    }

    /**
     * Fetch DMC exchange rate for selected currency (AJAX).
     */
    public function getDmcExchangeRate(Request $request)
    {
        $tourId = $request->query('tour_id');
        $currency = trim((string) $request->query('currency', ''));
        $defaultRate = '1';

        if (empty($tourId) || $currency === '') {
            return response()->json([
                'success' => false,
                'message' => 'tour_id and currency are required.',
            ], 422);
        }

        $tour = Tour::query()
            ->select(['tour_id', 'dmc_id', 'destination'])
            ->where('tour_id', $tourId)
            ->first();

        if (!$tour || empty($tour->dmc_id)) {
            return response()->json([
                'success' => true,
                'dmc_rate' => $defaultRate,
            ]);
        }

        // Primary lookup by currency selected in modal.
        $country = Country::query()
            ->whereRaw('LOWER(currency) = ?', [mb_strtolower($currency)])
            ->first();

        // Fallback to destination-country mapping if no direct currency country found.
        if (!$country && is_string($tour->destination) && trim($tour->destination) !== '') {
            $country = Country::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($tour->destination))])
                ->first();
        }

        if (!$country) {
            return response()->json([
                'success' => true,
                'dmc_rate' => $defaultRate,
            ]);
        }

        $exchangeRateRaw = $country->exchange_rate;
        if (is_string($exchangeRateRaw) && trim($exchangeRateRaw) !== '') {
            $decoded = json_decode($exchangeRateRaw, true);
            $exchangeRateRaw = is_array($decoded) ? $decoded : [];
        }
        $exchangeRateArr = is_array($exchangeRateRaw) ? $exchangeRateRaw : [];

        $match = collect($exchangeRateArr)->firstWhere('dmc_id', (int) $tour->dmc_id);
        $dmcRate = is_array($match) ? ($match['exchange_rate'] ?? null) : null;
        
        return response()->json([
            'success' => true,
            'dmc_rate' => is_scalar($dmcRate) ? (string) $dmcRate : $defaultRate,
        ]);
    }

    /**
     * Display Definite Bookings (tour_status = 'Definite')
     */
    public function definiteBookings()
    {
        $today = Carbon::today();
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){

            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                },
                'agent'
            ])
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.taxes',
                'tours.is_pro',
                'tours.user_currency',
                'tours.mainguest',
                'tours.discount_amount',
                'tours.created_at',
                'tours.created_by',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->where(function ($query) use ($today) {
                $query->where('tours.tour_status', 'Definite');
                    // ->orWhereDate('tours.updated_at', $today);
            })
            ->orderBy('tours.created_at', 'desc')
            ->get()
            ->unique('tour_id')
            ->values();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->formatToursDisplayId($tours);
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }
        if($dmc_id){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                },
                'agent'
            ])
            ->where(function ($query) use ($today) {
                $query->where('tours.tour_status', 'Definite');
                    // ->orWhereDate('tours.updated_at', $today);
            })
            ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmc_id, Auth::user()))
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.taxes',
                'tours.is_pro',
                'tours.user_currency',
                'tours.mainguest',
                'tours.discount_amount',
                'tours.created_at',
                'tours.created_by',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get()
            ->unique('tour_id')
            ->values();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->formatToursDisplayId($tours);
        }

        $currency = Country::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) ($user->country ?? ''))])
            ->value('currency');
        $currency = is_string($currency) && trim($currency) !== '' ? trim($currency) : (CommonHelper::getDmcCurrencyByCountry() ?: 'SGD');
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');

        // Prepare exchange-rate sources for the "Add Payment" modal:
        // - DMC rate: countries.exchange_rate JSON array of objects keyed by dmc_id
        // - Previous rate: last entry in tours.payment_details JSON array (Definite tours only)
        if ($tours && $tours->count() > 0) {
            $destinations = $tours->pluck('destination')
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->map(fn ($v) => mb_strtolower(trim($v)))
                ->unique()
                ->values()
                ->all();

            $countriesByName = [];
            if (!empty($destinations)) {
                Country::query()
                    ->whereIn(DB::raw('LOWER(name)'), $destinations)
                    ->get()
                    ->each(function ($c) use (&$countriesByName) {
                        $key = is_string($c->name) ? mb_strtolower(trim($c->name)) : '';
                        if ($key !== '') {
                            $countriesByName[$key] = $c;
                        }
                    });
            }

            foreach ($tours as $tour) {
                // Previous rate/currency from last payment_details entry (if any).
                $prevRate = null;
                $prevCurrency = null;
                $isDefiniteTour = is_string($tour->tour_status) && strcasecmp($tour->tour_status, 'Definite') === 0;
                if ($isDefiniteTour) {
                    $paymentDetails = $tour->payment_details;
                    if (is_string($paymentDetails) && trim($paymentDetails) !== '') {
                        $decoded = json_decode($paymentDetails, true);
                        $paymentDetails = is_array($decoded) ? $decoded : [];
                    }
                    if (is_array($paymentDetails) && !empty($paymentDetails)) {
                        $last = end($paymentDetails);
                        if (is_array($last)) {
                            $prevRate = $last['exchange_rate'] ?? null;
                            $prevCurrency = $last['currency'] ?? null;
                        }
                    }
                }
                $tour->previous_exchange_rate = is_scalar($prevRate) ? (string) $prevRate : null;
                $tour->previous_payment_currency = is_scalar($prevCurrency) ? (string) $prevCurrency : null;

                // DMC rate lookup: countries.exchange_rate is an array of objects.
                $dmcRate = null;
                $destKey = is_string($tour->destination) ? mb_strtolower(trim($tour->destination)) : '';
                $country = $destKey !== '' ? ($countriesByName[$destKey] ?? null) : null;
                if ($country && $dmc_id) {
                    $exchangeRateRaw = $country->exchange_rate;
                    if (is_string($exchangeRateRaw) && trim($exchangeRateRaw) !== '') {
                        $decoded = json_decode($exchangeRateRaw, true);
                        $exchangeRateRaw = is_array($decoded) ? $decoded : [];
                    }
                    $exchangeRateArr = is_array($exchangeRateRaw) ? $exchangeRateRaw : [];

                    $match = collect($exchangeRateArr)->firstWhere('dmc_id', (int) $dmc_id);
                    if (is_array($match)) {
                        $dmcRate = $match['exchange_rate'] ?? null;
                    }
                }
                $tour->dmc_exchange_rate_value = is_scalar($dmcRate) ? (string) $dmcRate : null;
            
            }
        }

        $tourIds = $tours->pluck('tour_id')->toArray();
        $tourNegotiationHistory = [];
        if (!empty($tourIds)) {
            $withComments = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('tour_id')
                ->flip()
                ->toArray();
            foreach ($tourIds as $tid) {
                $tourNegotiationHistory[$tid] = isset($withComments[$tid]);
            }

            // Latest non-empty negotiation currency per tour (for Add Payment modal).
            $enquiryCurrencies = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->whereNotNull('currency')
                ->where('currency', '!=', '')
                ->orderByDesc('id')
                ->get(['tour_id', 'currency'])
                ->unique('tour_id')
                ->pluck('currency', 'tour_id');

            foreach ($tours as $tour) {
                $enquiryCurrency = $enquiryCurrencies->get($tour->tour_id);
                $tour->enquiry_currency = is_string($enquiryCurrency) && trim($enquiryCurrency) !== ''
                    ? trim($enquiryCurrency)
                    : null;
            }
        }

        return view('bookings.definite', compact('tours', 'country_tax', 'currency', 'tourNegotiationHistory'));
    }

    /**
     * Display Actual Bookings (tour_status = 'Actual')
     */
    public function actualBookings()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
                ->whereIn('tour_status', ['Actual', 'Complete'])
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
                    'tours.hotel',
                    'tours.attraction',
                    'tours.travel',
                    'tours.restaurent',
                    'tours.guide',
                    'tours.port',
                    'tours.destination',
                    'tours.city',
                    'tours.check_in_time',
                    'tours.check_out_time',
                    'tours.tour_status',
                    'tours.payment_details',
                    'tours.taxes',
                    'tours.is_pro',
                    'tours.user_currency',
                    'tours.mainguest',
                    'tours.discount_amount',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'dmc_user.company_code as dmc_company_code',
                    'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                    'created_by_user.user_code as created_by_user_code'
                ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->formatToursDisplayId($tours);
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->where('bookingType', 'booking');
                }
            ])
                ->whereIn('tour_status', ['Actual', 'Complete'])
                ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmc_id, Auth::user()))
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
                ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
                ->select([
                    'tours.tour_id',
                    'tours.display_id',
                    'tours.reference_id',
                    'tours.multi_enq_id',
                    'tours.tour_type',
                    'tours.adult',
                    'tours.child',
                    'tours.infant',
                    'tours.hotel',
                    'tours.attraction',
                    'tours.travel',
                    'tours.restaurent',
                    'tours.guide',
                    'tours.port',
                    'tours.destination',
                    'tours.city',
                    'tours.check_in_time',
                    'tours.check_out_time',
                    'tours.tour_status',
                    'tours.payment_details',
                    'tours.taxes',
                    'tours.is_pro',
                    'tours.user_currency',
                    'tours.mainguest',
                    'tours.discount_amount',
                    'tours.created_at',
                    'tours.updated_at',
                    'tours.auto_cancel_date',
                    'tours.agent_id',
                    'agents.name as agent_name',
                    'agents.company_name as agent_company_name',
                    'created_by_user.name as created_by_name',
                    'dmc_user.company_code as dmc_company_code',
                    'dmc_user.auto_cancel_date as dmc_auto_cancel_day',
                    'created_by_user.user_code as created_by_user_code'
                ])
                ->orderBy('tours.created_at', 'desc')
                ->get();
            $this->hydrateTourNegotiationDiscounts($tours);
            $this->formatToursDisplayId($tours);
        }

        // Parse payment details for each tour
        $tours->transform(function ($tour) {
            if ($tour->payment_details) {
                try {
                    $tour->parsed_payment_details = json_decode($tour->payment_details, true);
                } catch (\Exception $e) {
                    $tour->parsed_payment_details = [];
                }
            } else {
                $tour->parsed_payment_details = [];
            }
            return $tour;
        });

        // Prepare exchange-rate sources for the "Add Payment" modal:
        // - DMC rate: countries.exchange_rate JSON array of objects keyed by dmc_id
        // - Previous rate: last entry in tours.payment_details JSON array
        if ($tours && $tours->count() > 0) {
            $destinations = $tours->pluck('destination')
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->map(fn ($v) => mb_strtolower(trim($v)))
                ->unique()
                ->values()
                ->all();

            $countriesByName = [];
            if (!empty($destinations)) {
                Country::query()
                    ->whereIn(DB::raw('LOWER(name)'), $destinations)
                    ->get()
                    ->each(function ($c) use (&$countriesByName) {
                        $key = is_string($c->name) ? mb_strtolower(trim($c->name)) : '';
                        if ($key !== '') {
                            $countriesByName[$key] = $c;
                        }
                    });
            }

            foreach ($tours as $tour) {
                $prevRate = null;
                $prevCurrency = null;
                $paymentDetails = $tour->payment_details;
                if (is_string($paymentDetails) && trim($paymentDetails) !== '') {
                    $decoded = json_decode($paymentDetails, true);
                    $paymentDetails = is_array($decoded) ? $decoded : [];
                }
                if (is_array($paymentDetails) && !empty($paymentDetails)) {
                    $last = end($paymentDetails);
                    if (is_array($last)) {
                        $prevRate = $last['exchange_rate'] ?? null;
                        $prevCurrency = $last['currency'] ?? null;
                    }
                }
                $tour->previous_exchange_rate = is_scalar($prevRate) ? (string) $prevRate : null;
                $tour->previous_payment_currency = is_scalar($prevCurrency) ? (string) $prevCurrency : null;

                $dmcRate = null;
                $destKey = is_string($tour->destination) ? mb_strtolower(trim($tour->destination)) : '';
                $country = $destKey !== '' ? ($countriesByName[$destKey] ?? null) : null;
                if ($country && $dmc_id) {
                    $exchangeRateRaw = $country->exchange_rate;
                    if (is_string($exchangeRateRaw) && trim($exchangeRateRaw) !== '') {
                        $decoded = json_decode($exchangeRateRaw, true);
                        $exchangeRateRaw = is_array($decoded) ? $decoded : [];
                    }
                    $exchangeRateArr = is_array($exchangeRateRaw) ? $exchangeRateRaw : [];
                    $match = collect($exchangeRateArr)->firstWhere('dmc_id', $dmc_id);
                    if (is_array($match)) {
                        $dmcRate = $match['exchange_rate'] ?? null;
                    }
                }
                $tour->dmc_exchange_rate_value = is_scalar($dmcRate) ? (string) $dmcRate : null;
            }
        }

        $currency = Country::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) ($user->country ?? ''))])
            ->value('currency');
        $currency = is_string($currency) && trim($currency) !== '' ? trim($currency) : (CommonHelper::getDmcCurrencyByCountry() ?: 'SGD');
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');

        $tourIds = $tours->pluck('tour_id')->toArray();
        $tourNegotiationHistory = [];
        if (!empty($tourIds)) {
            $withComments = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('tour_id')
                ->flip()
                ->toArray();
            foreach ($tourIds as $tid) {
                $tourNegotiationHistory[$tid] = isset($withComments[$tid]);
            }

            // Latest non-empty negotiation currency per tour (for Add Payment modal).
            $enquiryCurrencies = DB::table('enquiry_comments')
                ->whereIn('tour_id', $tourIds)
                ->whereNull('deleted_at')
                ->whereNotNull('currency')
                ->where('currency', '!=', '')
                ->orderByDesc('id')
                ->get(['tour_id', 'currency'])
                ->unique('tour_id')
                ->pluck('currency', 'tour_id');

            foreach ($tours as $tour) {
                $enquiryCurrency = $enquiryCurrencies->get($tour->tour_id);
                $tour->enquiry_currency = is_string($enquiryCurrency) && trim($enquiryCurrency) !== ''
                    ? trim($enquiryCurrency)
                    : null;
            }
        }

        $this->hydrateDestinationCreatedAt($tours);
        return view('bookings.actual', compact('tours', 'country_tax', 'currency', 'tourNegotiationHistory'));
    }

    /**
     * Display Cancelled Bookings (tour_status contains 'Cancel')
     */
    public function cancelledBookings()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
        $tours = Tour::where(function($query) {
                $query->where('tour_status', 'LIKE', 'Cancel%')
                      ->orWhere('tour_status', 'LIKE', '%Cancel%');
            })
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.is_pro',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.mainguest',
                'tours.hotel',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
        }

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $tours = Tour::where(function($query) {
                $query->where('tour_status', 'LIKE', 'Cancel%')
                      ->orWhere('tour_status', 'LIKE', '%Cancel%');
            })
            ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmc_id, Auth::user()))
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.is_pro',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.mainguest',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
        }

        $currency = CommonHelper::getDmcCurrencyByCountry();
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        $this->hydrateDestinationCreatedAt($tours);
        return view('bookings.cancelled', compact('tours', 'country_tax', 'currency'));
    }

    /**
     * Display Refunds (tour_status = 'Refund - Pending')
     */
    public function refunds()
    {
        $user = Auth::user();
        $dmc_id = null;
        $tours = collect([]);

        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 3 || $user->role_id == 4){
            $refundTourIds = Order::withTrashed()
                ->where('bookingType', 'booking')
                ->where('is_refund', 1)
                ->pluck('tour_id')
                ->unique()
                ->toArray();

            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->withTrashed()
                          ->where('bookingType', 'booking')
                          ->where('is_refund', 1);
                }
            ])
            ->where(function ($query) use ($refundTourIds) {
                $query->whereIn('tour_status', ['Refund - Pending', 'Refunded']);
                if (!empty($refundTourIds)) {
                    $query->orWhereIn('tours.tour_id', $refundTourIds);
                }
            })
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.is_pro',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.mainguest',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.dmc_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
        }
        
        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        if($dmc_id){
            $refundTourIds = Order::withTrashed()
                ->where('bookingType', 'booking')
                ->where('is_refund', 1)
                ->where('tour_id', '>', 0)
                ->pluck('tour_id')
                ->unique()
                ->toArray();

            $tours = Tour::with([
                'booking' => function ($query) {
                    $query->withTrashed()
                          ->where('bookingType', 'booking')
                          ->where('is_refund', 1);
                }
            ])
            ->where(function ($query) use ($refundTourIds) {
                $query->whereIn('tour_status', ['Refund - Pending', 'Refunded']);
                if (!empty($refundTourIds)) {
                    $query->orWhereIn('tours.tour_id', $refundTourIds);
                }
            })
            ->tap(fn ($q) => CommonHelper::applyTourDmcCountryAccess($q, $dmc_id, Auth::user()))
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.unique_tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.tour_type',
                'tours.is_pro',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.hotel',
                'tours.mainguest',
                'tours.attraction',
                'tours.travel',
                'tours.restaurent',
                'tours.guide',
                'tours.port',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'tours.dmc_id',
                'tours.created_by',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'created_by_user.name as created_by_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
            $this->formatToursDisplayId($tours);
        }
        
        $currency = CommonHelper::getDmcCurrencyByCountry();
        $country_tax = Country::where('name', $user->country)->value('tax_percentage');
        $this->hydrateDestinationCreatedAt($tours);
        return view('bookings.refunds', compact('tours', 'country_tax', 'currency'));
    }

    /**
     * Process refund for a tour (update tour_status from 'Refund - Pending' to 'Refunded')
     */
    public function processRefund(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer|exists:tours,tour_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid tour ID provided'
                ], 422);
            }

            $tourId = $request->tour_id;
            
            // Find the tour
            $tour = Tour::where('tour_id', $tourId)
                       ->where('tour_status', 'Refund - Pending')
                       ->first();

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found or not eligible for refund processing'
                ], 404);
            }

            // Update tour status to Refunded and track transition
            $oldStatus = $tour->tour_status; // Should be 'Refund - Pending'
            $tour->tour_status = 'Refunded';
            $tour->updated_at = now();

            // Append to track_details history: Refund - Pending -> Refunded
            \App\Helpers\CommonHelper::appendTourStatusTrack(
                $tour,
                $oldStatus,
                $tour->tour_status,
                $tour->updated_at
            );

            $tour->save();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'tour_id' => $tourId,
                'new_status' => 'Refunded'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark refund-eligible orders as refunded for a specific tour
     */
    public function processOrderRefund(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer|exists:tours,tour_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid tour ID provided'
                ], 422);
            }

            $tourId = (int) $request->tour_id;
            $roleId = (int) (Auth::user()->role_id ?? 0);
            $holdRoles = [33, 12, 37, 38];
            $financeRoles = [36, 126, 127];

            if (in_array($roleId, $holdRoles, true)) {
                $updated = Order::withTrashed()
                    ->where('tour_id', $tourId)
                    ->where('bookingType', 'booking')
                    ->where('is_refund', 1)
                    ->update([
                        'is_verify' => 2, // hold
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No refund-eligible services found for this tour.'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Services moved to hold for finance verification.',
                    'tour_id' => $tourId,
                    'updated_orders' => $updated,
                    'is_verify' => 2
                ]);
            }

            if (in_array($roleId, $financeRoles, true)) {
                $updated = Order::withTrashed()
                    ->where('tour_id', $tourId)
                    ->where('bookingType', 'booking')
                    ->where('is_refund', 1)
                    ->update([
                        'is_verify' => 1, // accepted
                        'refunded' => true,
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No refund services found for finance verification.'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Finance verification completed and services marked refunded.',
                    'tour_id' => $tourId,
                    'updated_orders' => $updated,
                    'is_verify' => 1
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating refunded status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a single refund-eligible order as refunded (by booking_id/id)
     */
    public function processOrderRefundByOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer|exists:tours,tour_id',
                'order_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input provided'
                ], 422);
            }

            $tourId = (int) $request->tour_id;
            $orderId = (int) $request->order_id;
            $roleId = (int) (Auth::user()->role_id ?? 0);
            $holdRoles = [33, 12, 37, 38];
            $financeRoles = [36, 126, 127];

            $order = Order::withTrashed()
                ->where('tour_id', $tourId)
                ->where('bookingType', 'booking')
                ->where('is_refund', 1)
                ->where(function ($q) use ($orderId) {
                    $q->where('booking_id', $orderId)
                      ->orWhere('id', $orderId);
                })
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund order not found for this tour.'
                ], 404);
            }

            if (in_array($roleId, $holdRoles, true)) {
                $order->is_verify = 2; // hold
                $order->updated_at = now();
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Selected service moved to hold for finance verification.',
                    'tour_id' => $tourId,
                    'order_id' => $orderId,
                    'is_verify' => 2
                ]);
            }

            if (in_array($roleId, $financeRoles, true)) {
                $order->is_verify = 1; // accepted
                $order->refunded = true;
                $order->updated_at = now();
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Finance verification completed and selected service marked refunded.',
                    'tour_id' => $tourId,
                    'order_id' => $orderId,
                    'is_verify' => 1
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action.'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating refunded status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display Cancellations & Refunds (tour_status = 'Cancelled')
     */
    public function cancellationsRefunds()
    {
        $user = Auth::user();
        $tours = Tour::where('tour_status', 'Cancelled')
            ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
            ->leftJoin('users as created_by_user', 'tours.created_by', '=', 'created_by_user.userId')
            ->leftJoin('users as dmc_user', 'tours.dmc_id', '=', 'dmc_user.userId')
            ->select([
                'tours.tour_id',
                'tours.display_id',
                'tours.reference_id',
                'tours.multi_enq_id',
                'tours.adult',
                'tours.child',
                'tours.infant',
                'tours.destination',
                'tours.city',
                'tours.check_in_time',
                'tours.check_out_time',
                'tours.tour_status',
                'tours.payment_details',
                'tours.created_at',
                'tours.updated_at',
                'tours.auto_cancel_date',
                'tours.agent_id',
                'agents.name as agent_name',
                'agents.company_name as agent_company_name',
                'dmc_user.company_code as dmc_company_code',
                'created_by_user.user_code as created_by_user_code'
            ])
            ->orderBy('tours.created_at', 'desc')
            ->get();
        $this->formatToursDisplayId($tours);
        $currency = CommonHelper::getDmcCurrencyByCountry();
        $country_tax = Country::where('name', optional($user)->country)->value('tax_percentage');
        return view('bookings.cancellations-refunds', compact('tours', 'country_tax', 'currency'));
    }

    /**
     * Get booking statistics for dashboard
     */
    public function getBookingStats()
    {
        $user = Auth::user();
        $dmc_id = CommonHelper::getDmcId($user);
        if (!$dmc_id && $user && (int) ($user->role_id ?? 0) === 11) {
            $dmc_id = $user->userId;
        }

        $countStatus = function ($status) use ($dmc_id, $user) {
            $query = Tour::query();
            if (is_array($status)) {
                $query->whereIn('tour_status', $status);
            } else {
                $query->where('tour_status', $status);
            }
            if ($dmc_id) {
                CommonHelper::applyTourDmcCountryAccess($query, $dmc_id, $user, 'dmc_id', 'destination');
            }
            return $query->count();
        };

        $stats = [
            'new_enquiries' => $countStatus('New Enquiry'),
            'follow_ups' => $countStatus('Prospect'),
            'tentative' => $countStatus('Tentative'),
            'confirmed' => $countStatus('Confirmed'),
            'definite' => $countStatus('Definite'),
            'actual' => $countStatus('Actual'),
            'cancelled' => $countStatus('Cancelled'),
        ];

        return response()->json($stats);
    }

    /**
     * View specific tour details
     */
    public function viewTour($encryptedId)
    {
        $tourId = Crypt::decrypt($encryptedId);
        $tour = Tour::where('tour_id', $tourId)->firstOrFail();
        
        // Parse payment details if exists
        if ($tour->payment_details) {
            try {
                $tour->parsed_payment_details = json_decode($tour->payment_details, true);
            } catch (\Exception $e) {
                $tour->parsed_payment_details = [];
            }
        } else {
            $tour->parsed_payment_details = [];
        }

        return view('bookings.view-tour', compact('tour'));
    }

    /**
     * Export tour details as PDF
     */
    public function exportTourPDF(Request $request, $tourId)
    {
        try {
            $tour = Tour::where('tour_id', $tourId)
                ->leftJoin('agents', 'tours.agent_id', '=', 'agents.agent_id')
                ->select([
                    'tours.*',
                    'agents.name as agent_name'
                ])
                ->firstOrFail();
            
            // Parse payment details if exists
            if ($tour->payment_details) {
                try {
                    $tour->parsed_payment_details = json_decode($tour->payment_details, true);
                } catch (\Exception $e) {
                    $tour->parsed_payment_details = [];
                }
            } else {
                $tour->parsed_payment_details = [];
            }

            // Check if this is a POST request with HTML content (from JavaScript)
            if ($request->isMethod('post') && $request->has('html_content')) {
                // Use the HTML content sent from JavaScript
                $html = $request->input('html_content');
                $tourTitle = $request->input('tour_title', $tour->display_id);
                
                // Try to generate PDF using dompdf (if available)
                if (class_exists('Dompdf\Dompdf')) {
                    $dompdf = new Dompdf([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                        'chroot' => public_path(),
                        'enable_php' => false
                    ]);
                    
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    
                    $filename = 'Tour_Details_' . preg_replace('/[^a-zA-Z0-9]/', '_', $tourTitle) . '.pdf';
                    
                    return response($dompdf->output())
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                        ->header('Cache-Control', 'no-store, no-cache');
                }
                
                // Fallback: return HTML with PDF-optimized styling
                $filename = 'Tour_Details_' . preg_replace('/[^a-zA-Z0-9]/', '_', $tourTitle) . '.html';
                
                return response($html)
                    ->header('Content-Type', 'text/html; charset=utf-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->header('Cache-Control', 'no-store, no-cache');
            }

            // Default behavior: Generate PDF view
            $html = view('bookings.tour-pdf', compact('tour'))->render();
            
            // Try to generate PDF using dompdf (if available)
            if (class_exists('Dompdf\Dompdf')) {
                $dompdf = new Dompdf([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                    'enable_php' => false
                ]);
                
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                
                $filename = 'Tour_Details_' . $tour->display_id . '.pdf';
                
                return response($dompdf->output())
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->header('Cache-Control', 'no-store, no-cache');
            }
            
            // Fallback: return HTML file
            $filename = 'Tour_Details_' . $tour->display_id . '.html';
            
            return response($html)
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-store, no-cache');
                
        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a tour by updating tour_status to 'Cancel'
     */
    public function cancelTour(Request $request, $encryptedId)
    {
        try {
            // Decrypt the tour ID
            $tourId = Crypt::decrypt($encryptedId);
            
            // Find the tour
            $tour = Tour::where('tour_id', $tourId)->first();
            
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }
            
            // Check if tour is already cancelled
            if ($tour->tour_status === 'Cancel') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour is already cancelled'
                ], 400);
            }
            
            // Update tour status to Cancel (and track history)
            $oldStatus = $tour->tour_status;

            if ($tour->tour_status == 'Definite') {
                $tour->tour_status = 'Refund - Pending';
            } else {
                $tour->tour_status = 'Cancel-' . $tour->tour_status;
            }

            // Track status change, e.g. Definite -> Refund - Pending, or X -> Cancel-X
            \App\Helpers\CommonHelper::appendTourStatusTrack(
                $tour,
                $oldStatus,
                $tour->tour_status
            );

            $tour->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Tour cancelled successfully',
                'tour_id' => $tour->display_id,
                'new_status' => $tour->tour_status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel tour: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveQrCode(Request $request, $encryptedId)
    {
        try {
            $qrCode = '';
            if ($request->hasFile('qr_code')) {
                $pathData = CommonHelper::image_path('file_storage', $request->file('qr_code'));
                if (!empty($pathData['master_value'])) {
                    $qrCode = $pathData['master_value'];
                }
            }
            // Allow either an encrypted id or a plain numeric booking_id
            $orderId = is_numeric($encryptedId) ? $encryptedId : Crypt::decrypt($encryptedId);
            $order = Order::where('booking_id', $orderId)->first();
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order->qr_code = $qrCode;
            $order->save();
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save QR code: ' . $e->getMessage()
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'QR code saved successfully',
            'order_id' => $order->booking_id,
            'qr_code' => $qrCode
        ]);
    }

    public function confirmationVoucherPreview(Request $request, $tourId)
    {
        try {
            $tourIdPlain = Crypt::decrypt($tourId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid tour ID');
        }

        $tour = Tour::where('tour_id', $tourIdPlain)->first();
        if (!$tour) {
            return redirect()->back()->with('error', 'Tour not found');
        }

        $logoType = strtolower((string) $request->query('logo_type', 'dmc'));
        if (!in_array($logoType, ['dmc', 'agency'], true)) {
            $logoType = 'dmc';
        }

        $hasAgency = false;
        if (!empty($tour->agent_id)) {
            $agentForPreview = Agent::with('agency')->where('agent_id', $tour->agent_id)->first();
            $hasAgency = (bool) ($agentForPreview && $agentForPreview->agency);
        }
        if ($logoType === 'agency' && !$hasAgency) {
            $logoType = 'dmc';
        }

        return view('bookings.voucher-preview', [
            'tour' => $tour,
            'logoType' => $logoType,
            'hasAgency' => $hasAgency,
            'encryptedTourId' => $tourId,
        ]);
    }

    public function confirmationVoucher(Request $request, $tourId)
    {
        try {
            $tourIdPlain = Crypt::decrypt($tourId);
        } catch (\Exception $e) {
            if ($request->boolean('preview', false)) {
                return $this->confirmationVoucherPreviewErrorResponse('Invalid tour ID');
            }
            abort(404, 'Invalid tour ID');
        }

        $tour = Tour::where('tour_id', $tourIdPlain)->first();
        if (!$tour) {
            if ($request->boolean('preview', false)) {
                return $this->confirmationVoucherPreviewErrorResponse('Tour not found');
            }
            abort(404, 'Tour not found');
        }

        $payload = $this->computeConfirmationVoucherPayload($tour);
        if ($payload === null) {
            if ($request->boolean('preview', false)) {
                return $this->confirmationVoucherPreviewErrorResponse('No approved services found for this tour.');
            }
            return back()->with('error', 'No approved services found for this tour.');
        }

        $preview = $request->boolean('preview', false);
        $requestedLogo = strtolower(trim((string) $request->query('logo_type', 'dmc')));
        if (!in_array($requestedLogo, ['dmc', 'agency'], true)) {
            $requestedLogo = 'dmc';
        }
        $logoAttempts = $requestedLogo === 'agency' ? ['agency', 'dmc'] : ['dmc'];

        foreach ($logoAttempts as $attemptLogo) {
            try {
                $voucherData = $this->applyConfirmationVoucherBranding($payload, $tour, $attemptLogo);
                $dompdf = new Dompdf();
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->set_option('isHtml5ParserEnabled', true);
                $html = view('bookings.voucher-pdf', $voucherData)->render();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $filename = 'Confirmation_Voucher_' . ($tour->display_id ?? $tourIdPlain) . '.pdf';
                return $dompdf->stream($filename, ['Attachment' => !$preview]);
            } catch (\Exception $e) {
                Log::warning('Confirmation voucher PDF attempt failed', [
                    'tour_id' => $tourIdPlain,
                    'logo_type' => $attemptLogo,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($preview) {
            return $this->confirmationVoucherPreviewErrorResponse('Unable to generate confirmation voucher PDF.');
        }

        return back()->with('error', 'Failed to generate PDF.');
    }

    private function confirmationVoucherPreviewErrorResponse(string $message)
    {
        $safe = e($message);
        return response(
            '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Preview unavailable</title></head>'
            . '<body style="margin:0;font-family:system-ui,sans-serif;padding:1.25rem;background:#f8f9fa;color:#333;">'
            . '<p style="margin:0 0 0.5rem;font-weight:600;">Confirmation voucher preview could not be loaded</p>'
            . '<p style="margin:0;font-size:0.9rem;">' . $safe . '</p>'
            . '<p style="margin:1rem 0 0;font-size:0.8rem;color:#6c757d;">Use <strong>Download PDF</strong> on the outer page, or try the other company branding option.</p>'
            . '</body></html>',
            503
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function resolveVoucherRootDmcUser(?User $dmcUser): ?User
    {
        if (!$dmcUser) {
            return null;
        }
        $rootDmc = $dmcUser;
        $visited = [];
        while ($rootDmc
            && (int) $rootDmc->role_id !== 11
            && $rootDmc->created_by
            && !in_array($rootDmc->created_by, $visited, true)) {
            $visited[] = $rootDmc->created_by;
            $rootDmc = User::where('userId', $rootDmc->created_by)->first();
        }
        return $rootDmc ?: $dmcUser;
    }

    private function applyConfirmationVoucherBranding(array $payload, Tour $tour, string $logoType): array
    {
        $user_dmc = null;
        if (!empty($tour->dmc_id)) {
            $user_dmc = User::where('userId', $tour->dmc_id)->first();
            if ($user_dmc && $user_dmc->logo && !str_starts_with((string) $user_dmc->logo, 'data:image')) {
                try {
                    $context = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 10, 'ignore_errors' => true]]);
                    $imageContent = @file_get_contents($user_dmc->logo, false, $context);
                    if ($imageContent !== false) {
                        $imageInfo = @getimagesizefromstring($imageContent);
                        if ($imageInfo !== false) {
                            $user_dmc->logo = 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imageContent);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Voucher header DMC logo base64 failed: ' . $e->getMessage());
                }
            }
        }

        $user_agency = null;
        if (!empty($tour->agent_id)) {
            $agentHeader = Agent::with('agency')->where('agent_id', $tour->agent_id)->first();
            if ($agentHeader && $agentHeader->agency) {
                $user_agency = $agentHeader->agency;
            }
        }

        $normalizedLogo = strtolower((string) $logoType) === 'agency' ? 'agency' : 'dmc';
        if ($normalizedLogo === 'agency' && !$user_agency) {
            $normalizedLogo = 'dmc';
        }

        $voucherRootDmc = $this->resolveVoucherRootDmcUser($user_dmc);

        return array_merge($payload, [
            'user_dmc' => $user_dmc,
            'user_agency' => $user_agency,
            'logoType' => $normalizedLogo,
            'voucherRootDmc' => $voucherRootDmc,
        ]);
    }

    private function computeConfirmationVoucherPayload(Tour $tour): ?array
    {
        $orders = Order::where('tour_id', $tour->tour_id)
            ->where('bookingType', 'booking')
            ->whereNull('deleted_at')
            ->where('is_approve', 1)
            ->get();

        if ($orders->isEmpty()) {
            return null;
        }

        $dmcUser = User::where('userId', $tour->dmc_id)->first();

        $hotels = [];
        $inclusions = [];
        $lowestDueDate = null;
        $totalRooms = 0;
        $confirmationNos = [];
        $allMealPlans = [];
        $childWithBedTotal = 0;
        $childWithoutBedTotal = 0;

        $str = function ($val, $default = '') {
            if (is_array($val) || is_object($val)) return $default;
            return (string) ($val ?? $default);
        };

        foreach ($orders as $order) {
            $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (!is_array($data)) continue;

            foreach ($data as $booking) {
                if (!is_array($booking)) continue;

                switch ($order->type) {
                    case 'hotel':
                        $hotelName = $str($booking['hotelDetails']['hotel_name'] ?? ($booking['hotel_name'] ?? null), 'Hotel');

                        $bookingDate = $booking['bookingDate'] ?? [];
                        $checkIn = '';
                        $checkOut = '';
                        if (is_array($bookingDate) && count($bookingDate) >= 2) {
                            $checkIn = $str($bookingDate[0]);
                            $checkOut = $str($bookingDate[1]);
                        } else {
                            $checkIn = $str($booking['checkIn'] ?? ($booking['check_in_date'] ?? null));
                            $checkOut = $str($booking['checkOut'] ?? ($booking['check_out_date'] ?? null));
                        }

                        $roomTypes = [];
                        $hotelRooms = 0;
                        $hotelMeals = [];
                        $roomsArr = $booking['rooms'] ?? [];
                        if (is_array($roomsArr)) {
                            foreach ($roomsArr as $room) {
                                if (!is_array($room)) continue;
                                $rt = $str($room['room_type'] ?? null);
                                $nr = (int) ($room['number_of_rooms'] ?? 1);
                                $hotelRooms += $nr;
                                if ($rt) $roomTypes[] = $rt;

                                $beds = $room['beds'] ?? [];
                                if (is_array($beds)) {
                                    foreach ($beds as $bed) {
                                        if (!is_array($bed)) continue;
                                        if (isset($bed['selectedMeals']) && is_array($bed['selectedMeals'])) {
                                            foreach ($bed['selectedMeals'] as $meal) {
                                                if (is_array($meal) && isset($meal['type']) && is_string($meal['type'])) {
                                                    $hotelMeals[] = $meal['type'];
                                                }
                                            }
                                        } elseif (isset($bed['mealTypes']) && is_array($bed['mealTypes'])) {
                                            foreach ($bed['mealTypes'] as $mt) {
                                                if (is_string($mt)) $hotelMeals[] = $mt;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if (empty($roomTypes)) {
                            $rt = $str($booking['room_type'] ?? null);
                            if ($rt) $roomTypes[] = $rt;
                        }
                        if ($hotelRooms === 0) {
                            $hotelRooms = (int) ($booking['number_of_rooms'] ?? 1);
                        }

                        $cwb = $booking['child_with_bed'] ?? null;
                        $cnb = $booking['child_without_bed'] ?? null;
                        if (is_array($cwb) && isset($cwb['children'])) {
                            $n = (int) $cwb['children'];
                            if ($n > $childWithBedTotal) $childWithBedTotal = $n;
                        }
                        if (is_array($cnb) && isset($cnb['children'])) {
                            $n = (int) $cnb['children'];
                            if ($n > $childWithoutBedTotal) $childWithoutBedTotal = $n;
                        }

                        $totalRooms += $hotelRooms;
                        $allMealPlans = array_merge($allMealPlans, $hotelMeals);

                        $hotelDueDate = null;
                        if ($order->display_due_date) {
                            try {
                                $hotelDueDate = Carbon::parse($order->display_due_date)->format('d/m/Y');
                            } catch (\Exception $e) {}
                        }

                        $hotelMealFormatted = '';
                        if (!empty($hotelMeals)) {
                            $mealNames = [];
                            foreach (array_unique($hotelMeals) as $mp) {
                                $mp = preg_replace('/^room\s+with\s+/i', '', (string) $mp);
                                $mp = preg_replace('/^room\s+only\s*/i', '', $mp);
                                $mp = trim($mp);
                                if (empty($mp)) continue;
                                $parts = preg_split('/\s*\+\s*|\s+and\s+/i', $mp);
                                foreach ($parts as $p) {
                                    $p = trim($p);
                                    if (!empty($p)) $mealNames[] = ucfirst(strtolower($p));
                                }
                            }
                            $hotelMealFormatted = implode(', ', array_unique($mealNames));
                        }

                        $hotels[] = [
                            'name' => $hotelName,
                            'room_type' => implode(', ', array_filter($roomTypes)),
                            'check_in' => $checkIn,
                            'check_out' => $checkOut,
                            'meal_plan' => $hotelMealFormatted,
                            'rooms' => $hotelRooms,
                            'due_date' => $hotelDueDate,
                            'confirmation_no' => $order->reference_id ? $str($order->reference_id) : '',
                        ];

                        if ($order->reference_id) {
                            $cn = $str($order->reference_id);
                            if ($cn) $confirmationNos[] = $cn;
                        }

                        if ($order->display_due_date) {
                            try {
                                $dueDate = Carbon::parse($order->display_due_date);
                                if (!$lowestDueDate || $dueDate->lt($lowestDueDate)) {
                                    $lowestDueDate = $dueDate;
                                }
                            } catch (\Exception $e) {}
                        }
                        break;

                    case 'attraction':
                        $name = $str($booking['AttractionName'] ?? ($booking['attraction_name'] ?? null), 'Attraction');
                        $ticketName = $str($booking['ticketName'] ?? ($booking['ticket_name'] ?? null));
                        $inclusions[] = $ticketName ? $name . ' (' . $ticketName . ')' : $name;

                        $tf = $booking['transfer_options'] ?? null;
                        if (is_array($tf) && !empty($tf['transfer_required'])) {
                            $tvName = $str($tf['vehicle_name'] ?? ($tf['vehicle_details']['vehicle_name'] ?? null));
                            $tvType = $str($tf['type'] ?? null, 'Private');
                            $tvWay = $str($tf['way'] ?? null, 'One Way');
                            if ($tvName) {
                                $inclusions[] = $name . ' Transfer (' . $tvName . ' - ' . $tvType . ' - ' . $tvWay . ')';
                            }
                        }
                        break;

                    case 'restaurant':
                        $name = $str($booking['restaurantName'] ?? ($booking['restaurant_name'] ?? null), 'Restaurant');
                        $mealType = $str($booking['mealType'] ?? ($booking['meal_type'] ?? null));
                        $inclusions[] = $mealType ? $name . ' (' . $mealType . ')' : $name;

                        $tf = $booking['transfer_options'] ?? null;
                        if (is_array($tf) && !empty($tf['transfer_required'])) {
                            $tvName = $str($tf['vehicle_name'] ?? ($tf['vehicle_details']['vehicle_name'] ?? null));
                            $tvType = $str($tf['type'] ?? null, 'Private');
                            $tvWay = $str($tf['way'] ?? null, 'One Way');
                            if ($tvName) {
                                $inclusions[] = $name . ' Transfer (' . $tvName . ' - ' . $tvType . ' - ' . $tvWay . ')';
                            }
                        }
                        break;

                    case 'guide':
                        $name = $str($booking['guide_name'] ?? null, 'Guide');
                        $hours = $str($booking['hours'] ?? ($booking['service_hours'] ?? null));
                        $inclusions[] = $hours ? $name . ' - ' . $hours . 'H' : $name;
                        break;

                    case 'entry_port':
                        $vehicle = $str($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null), 'Transfer');
                        $bType = $str($booking['type'] ?? null, 'Private');
                        $pickup = $str($booking['entrypickup'] ?? null);
                        $dropoff = $str($booking['entrydropoff'] ?? null);
                        $label = 'Arrival Transfer (' . $vehicle . ' - ' . $bType . ' - One Way)';
                        if ($pickup && $dropoff) $label .= "\n" . $pickup . ' to ' . $dropoff;
                        $inclusions[] = $label;
                        break;

                    case 'exit_port':
                        $vehicle = $str($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null), 'Transfer');
                        $bType = $str($booking['type'] ?? null, 'Private');
                        $pickup = $str($booking['exitpickup'] ?? null);
                        $dropoff = $str($booking['exitdropoff'] ?? null);
                        $label = 'Departure Transfer (' . $vehicle . ' - ' . $bType . ' - One Way)';
                        if ($pickup && $dropoff) $label .= "\n" . $pickup . ' to ' . $dropoff;
                        $inclusions[] = $label;
                        break;

                    case 'local_transport':
                    case 'travel_hourly':
                    case 'travel_point':
                        $vehicle = $str($booking['vehicles_name'] ?? ($booking['vehicle_name'] ?? null), 'Transport');
                        $bType = $str($booking['type'] ?? null);
                        $pickup = $str($booking['entrypickup'] ?? null);
                        $dropoff = $str($booking['entrydropoff'] ?? ($booking['dropoffLocation'] ?? null));
                        $label = $bType ? $vehicle . ' - ' . $bType : $vehicle;
                        if ($pickup && $dropoff) $label .= "\n" . $pickup . ' to ' . $dropoff;
                        $inclusions[] = $label;
                        break;
                }
            }
        }

        $paxName = '';
        if ($tour->mainguest) {
            $guest = is_string($tour->mainguest) ? json_decode($tour->mainguest, true) : $tour->mainguest;
            if (is_array($guest)) {
                $paxName = $str($guest['salutation'] ?? null) . ' ' . $str($guest['first_name'] ?? null) . ' ' . $str($guest['last_name'] ?? null);
                $paxName = trim($paxName);
            }
        }
        if (empty($paxName)) {
            $paxName = $str($tour->customer_name ?? null, 'Guest');
        }

        $travelDates = '';
        if ($tour->check_in_time && $tour->check_out_time) {
            $travelDates = Carbon::parse($tour->check_in_time)->format('d/m/Y') . ' - ' . Carbon::parse($tour->check_out_time)->format('d/m/Y');
        }

        $adultCount = (int) ($tour->adult ?? 0);
        $childCount = (int) ($tour->child ?? 0);
        $infantCount = (int) ($tour->infant ?? 0);
        $noOfPax = sprintf('%02d', $adultCount) . ' Adults';
        if ($childWithBedTotal > 0 || $childWithoutBedTotal > 0) {
            if ($childWithBedTotal > 0) $noOfPax .= '+' . sprintf('%02d', $childWithBedTotal) . ' cwb';
            if ($childWithoutBedTotal > 0) $noOfPax .= '+' . sprintf('%02d', $childWithoutBedTotal) . ' cnb';
        } elseif ($childCount > 0) {
            $noOfPax .= ', ' . $childCount . ' Children';
        }
        if ($infantCount > 0) $noOfPax .= ', ' . $infantCount . ' Infants';

        $refId = $tour->reference_id ?? $tour->display_id ?? '';
        $referenceId = is_array($refId) ? (string) ($refId[0] ?? '') : (string) $refId;

        $mealPlanSummary = '';
        if (!empty($allMealPlans)) {
            $mealNames = [];
            foreach (array_unique($allMealPlans) as $mp) {
                $mp = preg_replace('/^room\s+with\s+/i', '', (string) $mp);
                $mp = preg_replace('/^room\s+only\s*/i', '', $mp);
                $mp = trim($mp);
                if (empty($mp)) continue;
                $parts = preg_split('/\s*\+\s*|\s+and\s+/i', $mp);
                foreach ($parts as $p) {
                    $p = trim($p);
                    if (!empty($p)) {
                        $mealNames[] = ucfirst(strtolower($p));
                    }
                }
            }
            $mealPlanSummary = implode(', ', array_unique($mealNames));
        }
        $confirmationNo = !empty($confirmationNos) ? implode(', ', $confirmationNos) : 'na';

        return [
            'tour' => $tour,
            'dmcUser' => $dmcUser,
            'hotels' => $hotels,
            'inclusions' => $inclusions,
            'lowestDueDate' => $lowestDueDate,
            'paxName' => (string) $paxName,
            'travelDates' => (string) $travelDates,
            'noOfPax' => (string) $noOfPax,
            'referenceId' => $referenceId,
            'totalRooms' => $totalRooms > 0 ? (string) $totalRooms : 'na',
            'confirmationNo' => (string) $confirmationNo,
            'mealPlanSummary' => (string) $mealPlanSummary,
        ];
    }
}
