@php
    use App\Models\Setting;

    /*
    |--------------------------------------------------------------------------
    | Quotation email — same data contract as email/booking-confirmation.blade.php
    | Populated via CommonHelper::normalizeQuotationEmailData()
    | Sent when master DMC creates/sends a quotation via sendTourProposalEmail()
    |--------------------------------------------------------------------------
    */

    // Guest summary (same logic as tour_auto_booked_dmc)
    $guestParts = [];
    if (($adults ?? 0) > 0) {
        $guestParts[] = $adults . ' adult' . ($adults > 1 ? 's' : '');
    }
    if (($children ?? 0) > 0) {
        $guestParts[] = $children . ' child' . ($children > 1 ? 'ren' : '');
    }
    if (($infants ?? 0) > 0) {
        $guestParts[] = $infants . ' infant' . ($infants > 1 ? 's' : '');
    }
    $guestsText = count($guestParts)
        ? implode(', ', $guestParts)
        : (($total_guests ?? 0) . ' guest(s)');

    // Branding
    $masterLogo = Setting::where('name', 'logo')->first();
    $masterName = Setting::where('name', 'name')->first();
    $settingLogo = $masterLogo ? $masterLogo->value : '';
    $settingName = $masterName ? $masterName->value : (config('app.name') ?: 'travclicks');

    $logo        = !empty($dmc_logo) ? $dmc_logo : $settingLogo;
    $companyName = !empty($dmc_label) ? $dmc_label : (!empty($dmc_name) ? $dmc_name : $settingName);
    $tagline     = $tagline ?? 'Travel Designed Around You';

    $supportEmail = !empty($dmc_contact_email) ? $dmc_contact_email : ($supportEmail ?? 'reservations.travclicks@gmail.com');
    $supportPhone = $supportPhone ?? '+65 6201 2366';

    // Quotation summary
    $statusLabel   = $statusLabel ?? 'TRAVEL QUOTATION';
    $quotationNumber = $tour_display_id ?? ($quotationNumber ?? ($bookingNumber ?? 'N/A'));

    $destinationDisplay = trim(
        ($country ?? '') .
        (!empty($cities_label) ? ' — ' . $cities_label : (!empty($city ?? null) ? ' — ' . $city : ''))
    );
    if ($destinationDisplay === '') {
        $destinationDisplay = $destination ?? 'N/A';
    }

    $packageName = $packageName ?? (
        ($destination ?? 'Travel') . ' Experience Package'
    );

    $heroText = $heroText ?? "We've prepared a personalized travel quotation based on your request.";
    $quotedAt = $quoted_at ?? ($booked_at ?? null);

    $heroImage = $heroImage ?? 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1200&q=80';

    $tripDates = trim(($check_in_date ?? 'N/A') . ' – ' . ($check_out_date ?? 'N/A'), ' –');

    $bookedVia = !empty($dmc_label)
        ? $dmc_label
        : (
            !empty($agent_name)
                ? $agent_name . (!empty($agency_name) ? ' (' . $agency_name . ')' : '')
                : ($bookedVia ?? 'Travclicks')
        );

    $currencyCode = strtoupper(trim((string) ($currency_code ?? 'SGD'))) ?: 'SGD';
    $totalEstimation = round((float) ($total_estimation ?? 0), 2);

    if (!empty($total_estimation_formatted)) {
        $packageValue = $total_estimation_formatted;
        $totalPrice   = $total_estimation_formatted;
    } elseif ($totalEstimation > 0) {
        $formatted = $currencyCode . ' ' . number_format($totalEstimation, 2);
        $packageValue = $formatted;
        $totalPrice   = $formatted;
    } else {
        $packageValue = $packageValue ?? '—';
        $totalPrice   = $totalPrice ?? '—';
    }

    $detailsUrl   = $dashboard_link ?? ($detailsUrl ?? '#');
    $downloadUrl  = $downloadUrl ?? ($dashboard_link ?? '#');
    $itineraryUrl = $dashboard_link ?? ($itineraryUrl ?? '#');
    $chatUrl      = $chatUrl ?? ('mailto:' . $supportEmail);

    $bookedServices = is_array($booked_services ?? null) ? $booked_services : [];

    // PDF-style quotation layout (same data as single-tour-package/quotation.blade.php)
    $hasPdfQuotationLayout = isset($tour) && is_object($tour)
        && isset($bookingDetails) && is_array($bookingDetails)
        && isset($tourPrices) && is_array($tourPrices);

    if ($hasPdfQuotationLayout) {
        $pdfAdults = (int)($bookingDetails['no_of_adults'] ?? 0);
        $pdfChildren = (int)($bookingDetails['no_of_children'] ?? 0);
        $pdfInfants = (int)($bookingDetails['no_of_infants'] ?? 0);
        $leadGuestName = $bookingDetails['lead_guest_name'] ?? '';

        $tourTypeForPax = strtoupper((string)($tour->tour_type ?? 'FIT'));
        $focForPax = max(0, (int)($tour->foc_size ?? 0));
        $displayAdults = ($tourTypeForPax === 'GROUP' && $focForPax > 0) ? max(0, $pdfAdults - $focForPax) : $pdfAdults;

        $paxParts = [];
        $paxParts[] = str_pad((string) $displayAdults, 2, '0', STR_PAD_LEFT) . 'A';
        if ($pdfChildren > 0) {
            $paxParts[] = str_pad((string) $pdfChildren, 2, '0', STR_PAD_LEFT) . 'C';
        }
        if ($pdfInfants > 0) {
            $paxParts[] = str_pad((string) $pdfInfants, 2, '0', STR_PAD_LEFT) . 'I';
        }
        $paxText = implode(' ', $paxParts);

        $travelFrom = null;
        $travelTo = null;
        try {
            $travelFrom = (!empty($tour->check_in_time)) ? \Carbon\Carbon::parse($tour->check_in_time) : null;
            $travelTo = (!empty($tour->check_out_time)) ? \Carbon\Carbon::parse($tour->check_out_time) : null;
        } catch (\Throwable $e) {
            $travelFrom = null;
            $travelTo = null;
        }

        $travellingDate = 'N/A';
        if ($travelFrom && $travelTo) {
            $travellingDate = $travelFrom->format('d M Y') . ' to ' . $travelTo->format('d M Y');
        } elseif ($travelFrom) {
            $travellingDate = $travelFrom->format('d M Y');
        }
        $inclusionDateRange = ($travelFrom && $travelTo)
            ? $travelFrom->format('d M Y') . ' to ' . $travelTo->format('d M Y')
            : 'N/A';

        $resolveVehicleDisplayName = static function ($rawName, $rawId = null) {
            $name = trim((string) $rawName);
            $id = trim((string) ($rawId ?? ''));
            $token = $id !== '' ? $id : $name;
            $needsLookup = $token !== '' && (
                $name === ''
                || $name === $token
                || (ctype_digit($name) && (string) ((int) $name) === $name)
            );
            if (! $needsLookup) {
                return $name;
            }
            if ($token === '') {
                return $name;
            }
            try {
                $vehicle = \App\Models\Vehicle::withTrashed()
                    ->where(function ($q) use ($token) {
                        $q->where('vehicle_id', $token);
                        if (ctype_digit($token)) {
                            $q->orWhere('id', (int) $token);
                        }
                    })
                    ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END')
                    ->first(['vehicle_name', 'vehicle_type', 'vehicle_id', 'id', 'dmc_id', 'deleted_at']);
                if ($vehicle && $vehicle->deleted_at) {
                    $dmcId = (int) ($vehicle->dmc_id ?? 0);
                    $active = \App\Models\Vehicle::query()
                        ->whereNull('deleted_at')
                        ->when($dmcId > 0, function ($q) use ($dmcId) {
                            $q->where(function ($qq) use ($dmcId) {
                                $qq->where('dmc_id', $dmcId)
                                    ->orWhere('dmc_id', (string) $dmcId)
                                    ->orWhereRaw('CAST(dmc_id AS TEXT) = ?', [(string) $dmcId]);
                            });
                        })
                        ->orderBy('id')
                        ->first(['vehicle_name', 'vehicle_type']);
                    if ($active) {
                        $vehicle = $active;
                    }
                }
                if ($vehicle) {
                    $resolved = trim((string) ($vehicle->vehicle_name ?? ''));
                    if ($resolved === '') {
                        $resolved = trim((string) ($vehicle->vehicle_type ?? ''));
                    }
                    if ($resolved !== '' && !(ctype_digit($resolved) && $resolved === $token)) {
                        return $resolved;
                    }
                }
            } catch (\Throwable $e) {
                // keep original
            }
            return $name !== '' ? $name : $token;
        };

        $isProTour = (int)($tour->is_pro ?? 0) === 1;
        $occupancyKey = $pdfAdults >= 2 ? 'double' : 'single';
        $roomingText = $pdfAdults >= 2 ? '01 DBL TWIN' : '01 SGL';

        $pdfBaseCurrency = strtoupper($baseCurrency ?? ($tour->currency ?? 'SGD'));
        $pdfSelectedCurrency = strtoupper($selectedCurrency ?? $pdfBaseCurrency);
        $pdfExchangeRate = isset($exchangeRate) && is_numeric($exchangeRate) && (float)$exchangeRate > 0 ? (float)$exchangeRate : 1.0;
        $currencyLabel = $pdfSelectedCurrency === 'INR' ? 'INR' : $pdfSelectedCurrency;

        $formatAmount = function ($amount) use ($pdfExchangeRate) {
            if (!is_numeric($amount)) return '0';
            return (string)(int)ceil(((float)$amount) * $pdfExchangeRate);
        };

        $formatMoney = function ($amount) use ($currencyLabel, $formatAmount) {
            return $currencyLabel . ' ' . $formatAmount($amount);
        };

        $supplements = $tourPrices['supplyments'] ?? ($tourPrices['supplements'] ?? []);
        $otherSingleTotal = (float)($tourPrices['other_services_single'] ?? 0);
        $otherDoubleTotal = (float)($tourPrices['other_services_double'] ?? 0);

        $tourType = strtoupper((string)($tour->tour_type ?? 'FIT'));
        $adultTotal = max(0, (int)($tour->adult ?? 0));
        $focSize = max(0, (int)($tour->foc_size ?? 0));
        $totalPax = $adultTotal;
        $hasFoc = $focSize > 0;
        $hasDiscount = !empty($tour->discount) && (int)$tour->discount === 1;
        $showGroupDiscountAmount = ($tourType === 'GROUP' && $hasDiscount);
        $groupDiscountAmount = (float)($tour->discount_amount ?? 0);
        $otherTotalForOccupancy = $occupancyKey === 'double' ? $otherDoubleTotal : $otherSingleTotal;

        $hotelOnlySingleTotal = max(0, (float)($tourPrices['single_sharing'] ?? 0) - $otherSingleTotal);
        $hotelOnlyDoubleTotal = max(0, (float)($tourPrices['double_sharing'] ?? 0) - $otherDoubleTotal);
        if ($isProTour) {
            $hotelOnlySingleTotal = $hotelOnlyDoubleTotal;
        }
        $tripleSharingTotal = (float)($tourPrices['triple_sharing'] ?? 0);
        $hotelOnlyTripleTotal = $tripleSharingTotal > 0
            ? max(0, $tripleSharingTotal - $otherSingleTotal)
            : 0;

        $priceBreakdown = $priceBreakdown ?? \App\Helpers\CommonHelper::buildQuotationPriceBreakdown(
            $tour,
            $tourPrices,
            $pdfAdults,
            $pdfChildren,
            $pdfInfants
        );

        $breakdownHotelTotal = (float) ($priceBreakdown['hotel_total'] ?? 0);
        $breakdownOtherTotal = (float) ($priceBreakdown['other_total'] ?? 0);
        if (! array_key_exists('hotel_total', $priceBreakdown) || ! array_key_exists('other_total', $priceBreakdown)) {
            $breakdownHotelTotal = 0.0;
            $breakdownOtherTotal = 0.0;
            foreach ($priceBreakdown['lines'] ?? [] as $breakdownSumLine) {
                if (($breakdownSumLine['category'] ?? '') === 'hotel') {
                    $breakdownHotelTotal += (float) ($breakdownSumLine['line_total'] ?? 0);
                } else {
                    $breakdownOtherTotal += (float) ($breakdownSumLine['line_total'] ?? 0);
                }
            }
        }

        $formatBreakdownLine = function (array $line) use ($formatMoney) {
            return \App\Helpers\CommonHelper::formatQuotationBreakdownCalculation($line, $formatMoney);
        };

        $bookedAttractionCards = [];
        $bookedRestaurantCards = [];
        $bookedArrivals = [];
        $bookedDepartures = [];
        $bookedLocalTransfers = [];

        if (!empty($servicesByType) && is_array($servicesByType)) {
            foreach ($servicesByType as $type => $cards) {
                if (!is_array($cards) || empty($cards)) continue;
                $normalizedType = str_replace(' ', '_', strtolower($type));

                if ($normalizedType === 'attraction' || $normalizedType === 'attraction_package') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $title = $card['title'] ?? ($card['attraction']['title'] ?? null);
                        if (!empty($title)) $bookedAttractionCards[] = $card;
                    }
                }

                if ($normalizedType === 'restaurant') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $name = $card['title'] ?? ($card['restaurant']['name'] ?? null);
                        if (!empty($name)) $bookedRestaurantCards[] = $card;
                    }
                }

                if ($normalizedType === 'entry_port') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $pickup = '';
                        $entryTime = '';
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (!is_array($chip)) continue;
                            $label = strtolower((string)($chip['label'] ?? ''));
                            $value = (string)($chip['value'] ?? '');
                            if ($label === 'pickup') $pickup = $value;
                            if ($label === 'time') $entryTime = $value;
                        }
                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? '';
                        $transferType = $transferTypeRaw;
                        if (!empty($transferTypeRaw) && $transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        }
                        $portName = !empty($pickup) ? $pickup : '';
                        if (!empty($portName)) {
                            $text = 'Arrival: ' . $portName;
                            if (!empty($entryTime)) $text .= ' (' . $entryTime . ')';
                            if (!empty($transferType)) $text .= ' - ' . $transferType;
                            $bookedArrivals[$text] = true;
                        }
                    }
                }

                if ($normalizedType === 'exit_port') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $dropoff = '';
                        $exitTime = '';
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (!is_array($chip)) continue;
                            $label = strtolower((string)($chip['label'] ?? ''));
                            $value = (string)($chip['value'] ?? '');
                            if ($label === 'dropoff') $dropoff = $value;
                            if ($label === 'time') $exitTime = $value;
                        }
                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? '';
                        $transferType = $transferTypeRaw;
                        if (!empty($transferTypeRaw) && $transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        }
                        $portName = !empty($dropoff) ? $dropoff : '';
                        if (!empty($portName)) {
                            $text = 'Departure: ' . $portName;
                            if (!empty($exitTime)) $text .= ' (' . $exitTime . ')';
                            if (!empty($transferType)) $text .= ' - ' . $transferType;
                            $bookedDepartures[$text] = true;
                        }
                    }
                }

                if ($normalizedType === 'local_transport' || $normalizedType === 'local_transfer') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? '';
                        $transferType = $transferTypeRaw;
                        if (!empty($transferTypeRaw) && $transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        }
                        $vehicleTypeSeater = (string)($vehicleData['vehicle_type_seater'] ?? '');
                        if (!empty($transferType)) {
                            $text = 'Local Transfer: ' . $transferType;
                            if ($vehicleTypeSeater !== '') $text .= ' - ' . $vehicleTypeSeater;
                            $bookedLocalTransfers[$text] = true;
                        }
                    }
                }
            }
        }

        $suppAttractionCounts = [];
        $suppRestaurantCounts = [];
        if (!empty($supplements) && is_array($supplements)) {
            foreach ($supplements as $s) {
                $suppType = strtolower((string)($s['type'] ?? ''));
                $suppName = (string)($s['name'] ?? '');
                if ($suppType === 'attraction' && $suppName !== '') {
                    $suppAttractionCounts[$suppName] = ($suppAttractionCounts[$suppName] ?? 0) + 1;
                }
                if ($suppType === 'restaurant' && $suppName !== '') {
                    $mealType = $s['mealType'] ?? null;
                    $key = $suppName;
                    if (!empty($mealType)) $key .= ' - ' . $mealType;
                    $suppRestaurantCounts[$key] = ($suppRestaurantCounts[$key] ?? 0) + 1;
                }
            }
        }

        if (!empty($suppAttractionCounts)) {
            $filtered = [];
            foreach ($bookedAttractionCards as $c) {
                $a = $c['title'] ?? '';
                if ($a !== '' && isset($suppAttractionCounts[$a]) && $suppAttractionCounts[$a] > 0) {
                    $suppAttractionCounts[$a]--;
                    continue;
                }
                $filtered[] = $c;
            }
            $bookedAttractionCards = $filtered;
        }

        if (!empty($suppRestaurantCounts)) {
            $filtered = [];
            foreach ($bookedRestaurantCards as $rc) {
                $restPart = $rc['restaurant'] ?? [];
                $nm = $rc['title'] ?? '';
                $mealPlan = $restPart['meal_plan'] ?? null;
                $key = $nm;
                if (!empty($mealPlan)) $key .= ' - ' . $mealPlan;
                if ($key !== '' && isset($suppRestaurantCounts[$key]) && $suppRestaurantCounts[$key] > 0) {
                    $suppRestaurantCounts[$key]--;
                    continue;
                }
                $filtered[] = $rc;
            }
            $bookedRestaurantCards = $filtered;
        }

        $tourRawDisplayId = (string)($tour->display_id ?? $tour->tour_id ?? '');
        $ordPart = trim((string) preg_replace('/^DMC-/', '', $tourRawDisplayId));
        if ($ordPart === '') $ordPart = trim($tourRawDisplayId);

        $tourDmcUser = null;
        if (!empty($tour->dmc_id)) {
            $tourDmcUser = \App\Models\User::where('userId', $tour->dmc_id)->first();
        }
        $tourDmcCompanyCode = is_string($tourDmcUser?->company_code ?? null) ? trim($tourDmcUser->company_code) : '';
        $tourDmcCompanyCode = $tourDmcCompanyCode !== '' ? $tourDmcCompanyCode : null;

        $createByUser = null;
        if (!empty($tour->created_by)) {
            $createByUser = \App\Models\User::where('userId', $tour->created_by)->first();
        }
        $createByUserCode = is_string($createByUser?->user_code ?? null) ? trim($createByUser->user_code) : '';
        $createByUserCode = $createByUserCode !== '' ? $createByUserCode : null;

        $formattedDisplayId = $ordPart !== '' ? $ordPart : '—';
        if ($tourDmcCompanyCode && $createByUserCode) {
            $formattedDisplayId = $tourDmcCompanyCode . '/' . $createByUserCode . '/' . $ordPart;
        } elseif ($tourDmcCompanyCode) {
            $formattedDisplayId = $tourDmcCompanyCode . '/' . $ordPart;
        } elseif ($createByUserCode) {
            $formattedDisplayId = $createByUserCode . '/' . $ordPart;
        }

        $formatDateRange = function ($raw) {
            if (empty($raw)) return '';
            $parts = array_map('trim', explode(' to ', (string)$raw));
            if (count($parts) === 2) {
                try {
                    $from = \Carbon\Carbon::parse($parts[0])->format('d M Y');
                    $to = \Carbon\Carbon::parse($parts[1])->format('d M Y');
                    return $from . ' to ' . $to;
                } catch (\Throwable $e) {}
            }
            return $raw;
        };

        $suppHotels = [];
        $suppServices = [];
        foreach ($supplements as $s) {
            $t = strtolower((string)($s['type'] ?? ''));
            if ($t === 'hotel') {
                $suppHotels[] = $s;
            } else {
                $suppServices[] = $s;
            }
        }

        $cellBorder = 'border:1px solid #000;';
        $panelTitle = 'font-weight:bold; text-align:center; background:#f3f3f3; padding:8px 6px; text-transform:uppercase; font-size:12px; ' . $cellBorder;
        $thStyle = $cellBorder . ' padding:6px; background:#f3f3f3; text-align:center; font-size:12px; font-weight:bold;';
        $tdStyle = $cellBorder . ' padding:6px; font-size:12px; vertical-align:top;';
    }

    if (! isset($priceBreakdown) || ! is_array($priceBreakdown)) {
        $priceBreakdown = ['lines' => [], 'grand_total' => (float) ($total_estimation ?? 0)];
    }

    $formatBreakdownLineEmail = function (array $line) use ($currencyCode) {
        $formatMoney = static function ($amount) use ($currencyCode) {
            if (! is_numeric($amount)) {
                return $currencyCode . ' 0';
            }

            return $currencyCode . ' ' . number_format((float) $amount, 0, '.', ',');
        };

        return \App\Helpers\CommonHelper::formatQuotationBreakdownCalculation($line, $formatMoney);
    };

    // Build "What's included" from booked service types
    $includedCounts = [
        'hotel'      => 0,
        'attraction' => 0,
        'restaurant' => 0,
        'transfer'   => 0,
        'guide'      => 0,
    ];
    foreach ($bookedServices as $svc) {
        $typeKey = strtolower((string) ($svc['order_type'] ?? $svc['badge'] ?? ''));
        if ($typeKey === 'hotel') {
            $includedCounts['hotel']++;
        } elseif ($typeKey === 'attraction') {
            $includedCounts['attraction']++;
        } elseif ($typeKey === 'restaurant') {
            $includedCounts['restaurant']++;
        } elseif ($typeKey === 'guide') {
            $includedCounts['guide']++;
        } elseif (in_array($typeKey, ['entry_port', 'exit_port', 'vehicle', 'transfer', 'travel_point', 'travel_hourly', 'local_transport', 'transfer'], true)) {
            $includedCounts['transfer']++;
        }
    }

    $included = $included ?? [];
    if (empty($included)) {
        if ($includedCounts['hotel'] > 0) {
            $included[] = 'Accommodation (' . $includedCounts['hotel'] . ')';
        }
        if ($includedCounts['attraction'] > 0) {
            $included[] = 'Attractions (' . $includedCounts['attraction'] . ')';
        }
        if ($includedCounts['restaurant'] > 0) {
            $included[] = 'Dining Experience (' . $includedCounts['restaurant'] . ')';
        }
        if ($includedCounts['guide'] > 0) {
            $included[] = 'Guide Services (' . $includedCounts['guide'] . ')';
        }
        if ($includedCounts['transfer'] > 0) {
            $included[] = 'Private Transfers (' . $includedCounts['transfer'] . ')';
        }
    }

    $features = $features ?? [
        ['icon' => '📋', 'title' => 'Detailed', 'subtitle' => 'day-by-day breakdown'],
        ['icon' => '💬', 'title' => 'Negotiate', 'subtitle' => 'in real time'],
        ['icon' => '⚡', 'title' => 'Fast', 'subtitle' => 'quotation updates'],
        ['icon' => '🔒', 'title' => 'Secure', 'subtitle' => 'proposal management'],
    ];

    $brandBlue = '#2563eb';
    $textDark  = '#1f2a44';
    $textMuted = '#6b7280';
    $border    = '#e9edf5';
    $bgSoft    = '#f5f7fb';

    // Helper: extract hotel meta row from service lines
    $extractHotelMeta = function (array $service) use ($guestsText) {
        $meta = [];
        $map = [
            'Room'      => 'Room',
            'Bed'       => 'Bed',
            'Meal plan' => 'Meal Plan',
        ];
        foreach ($service['lines'] ?? [] as $line) {
            if (!is_array($line)) {
                continue;
            }
            $label = $line['label'] ?? '';
            if (isset($map[$label])) {
                $meta[] = ['label' => $map[$label], 'value' => $line['value'] ?? ''];
            }
        }
        if (!empty($service['pax'])) {
            $meta[] = ['label' => 'Guests', 'value' => $service['pax']];
        } elseif ($guestsText) {
            $meta[] = ['label' => 'Guests', 'value' => $guestsText];
        }
        return $meta;
    };

    // Helper: find nights badge for hotel services
    $extractNightBadge = function (array $service) {
        foreach ($service['lines'] ?? [] as $line) {
            if (is_array($line) && ($line['label'] ?? '') === 'Nights') {
                $n = (int) ($line['value'] ?? 0);
                return $n . ' Night' . ($n > 1 ? 's' : '');
            }
        }
        return null;
    };

    // Helper: format service price
    $formatServicePrice = function (array $service) use ($currencyCode) {
        $value = (float) ($service['price_value'] ?? 0);
        if ($value > 0) {
            return $currencyCode . ' ' . number_format($value, 2);
        }
        if (!empty($service['price'])) {
            return $currencyCode . ' ' . $service['price'];
        }
        return null;
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Quotation #{{ $quotationNumber }}</title>
</head>
<body style="margin:0; padding:0; background-color:{{ $bgSoft }}; font-family:'Segoe UI', Arial, Helvetica, sans-serif; color:{{ $textDark }}; -webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:{{ $bgSoft }}; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="width:680px; max-width:680px; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 8px 30px rgba(31,42,68,0.08);">

                    <!-- HEADER -->
                    <tr>
                        <td style="padding:22px 28px; border-bottom:1px solid {{ $border }};">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;" width="55%">
                                        @if($logo)
                                            <img src="{{ $logo }}" alt="{{ $companyName }}" style="max-height:34px; display:block;">
                                        @else
                                            <span style="font-size:22px; font-weight:700; color:{{ $brandBlue }};">{{ $companyName }}</span>
                                        @endif
                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-top:4px;">{{ $tagline }}</div>
                                    </td>
                                    <td style="vertical-align:middle; text-align:right;" width="45%">
                                        <div style="font-size:11px; color:{{ $textMuted }};">Need help?</div>
                                        <a href="mailto:{{ $supportEmail }}" style="font-size:13px; color:{{ $brandBlue }}; text-decoration:none; font-weight:600;">{{ $supportEmail }}</a>
                                        @if(!empty($supportPhone))
                                            <div style="font-size:12px; color:{{ $textDark }}; margin-top:2px;">{{ $supportPhone }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- HERO -->
                    <tr>
                        <td style="padding:18px 28px 0 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-radius:12px; overflow:hidden; background-color:#0f2a4a; background-image:linear-gradient(120deg, rgba(15,42,74,0.92) 0%, rgba(15,42,74,0.45) 60%, rgba(15,42,74,0.15) 100%), url('{{ $heroImage }}'); background-size:cover; background-position:center;">
                                <tr>
                                    <td style="padding:30px 28px;">
                                        <span style="display:inline-block; background-color:rgba(255,255,255,0.16); color:#dbe7ff; font-size:11px; font-weight:700; letter-spacing:.5px; padding:6px 12px; border-radius:20px;">✔ {{ $statusLabel }}</span>
                                        <div style="font-size:28px; font-weight:700; color:#ffffff; margin-top:16px;">Quotation #{{ $quotationNumber }}</div>
                                        <div style="font-size:16px; color:#eaf1ff; margin-top:6px;">{{ $packageName }}</div>
                                        @if(!empty($agent_name))
                                            <div style="font-size:13px; color:#dbe7ff; margin-top:10px;">Prepared for {{ $agent_name }}@if(!empty($agency_name)) ({{ $agency_name }})@endif</div>
                                        @endif
                                        <div style="font-size:13px; color:#cdddf7; margin-top:14px; max-width:420px; line-height:1.5;">{{ $heroText }}</div>
                                        @if(!empty($quotedAt))
                                            <div style="font-size:12px; color:#b8cff5; margin-top:10px;">Quoted: {{ $quotedAt }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- TRIP SUMMARY -->
                    <tr>
                        <td style="padding:22px 28px 4px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid {{ $border }}; border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 18px 4px 18px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:14px; font-weight:700; color:{{ $textDark }};">📋 Trip summary</td>
                                                <td style="text-align:right;"><a href="{{ $detailsUrl }}" style="font-size:12px; color:{{ $brandBlue }}; text-decoration:none; font-weight:600;">View details ›</a></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 18px 16px 18px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                @php
                                                    $summaryCells = [
                                                        ['Destination', $destinationDisplay],
                                                        ['Dates', $tripDates],
                                                        ['Guests', $guestsText],
                                                        ['Quoted by', $bookedVia],
                                                    ];
                                                @endphp
                                                @foreach($summaryCells as $cell)
                                                    <td style="vertical-align:top; padding-right:10px; width:25%;">
                                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-bottom:4px;">{{ $cell[0] }}</div>
                                                        <div style="font-size:13px; font-weight:700; color:{{ $textDark }};">{{ $cell[1] }}</div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                            @if(!empty($requested_days) || !empty($available_days))
                                                <tr>
                                                    <td colspan="4" style="padding-top:10px;">
                                                        @if(!empty($requested_days))
                                                            <span style="font-size:11px; color:{{ $textMuted }};">Requested: </span>
                                                            <span style="font-size:12px; font-weight:600; color:{{ $textDark }};">{{ $requested_nights ?? max(0, (int) $requested_days - 1) }} night{{ (($requested_nights ?? max(0, (int) $requested_days - 1)) !== 1) ? 's' : '' }}</span>
                                                        @endif
                                                        @if(!empty($available_days))
                                                            <span style="font-size:11px; color:{{ $textMuted }}; margin-left:12px;">Package available: </span>
                                                            <span style="font-size:12px; font-weight:600; color:{{ $textDark }};">{{ $available_nights ?? max(0, (int) $available_days - 1) }} night{{ (($available_nights ?? max(0, (int) $available_days - 1)) !== 1) ? 's' : '' }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- QUOTATION BODY (PDF-style when full tour data is available) -->
                    @if($hasPdfQuotationLayout)
                        <tr>
                            <td style="padding:18px 28px 4px 28px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:12px; color:#000; margin-bottom:14px;">
                                    <tr>
                                        <td style="padding:4px 0;"><strong>Reference No:</strong> {{ $formattedDisplayId }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 0;"><strong>LEAD GUEST NAME:</strong> {{ $leadGuestName }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 0;"><strong>No. of Pax:</strong> {{ $paxText }}</td>
                                    </tr>
                                    @if($hasFoc)
                                        <tr><td style="padding:4px 0;"><strong>FOC Pax:</strong> {{ $focSize }}</td></tr>
                                        <tr><td style="padding:4px 0;"><strong>Total Pax:</strong> {{ $totalPax }}</td></tr>
                                    @endif
                                    <tr>
                                        <td style="padding:4px 0;"><strong>Travelling Date:</strong> {{ $travellingDate }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 0;"><strong>Rooming:</strong> {{ $roomingText }}</td>
                                    </tr>
                                    @if($showGroupDiscountAmount)
                                        <tr>
                                            <td style="padding:4px 0;"><strong>Discount amount:</strong> {{ $formatMoney($groupDiscountAmount) }}</td>
                                        </tr>
                                    @endif
                                </table>

                                @if(!empty($priceBreakdown['grand_total']))
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:2px solid #000; margin-bottom:14px;">
                                        <tr>
                                            <td style="{{ $panelTitle }}">Total Quotation Price ({{ $currencyLabel ?? $currencyCode }})</td>
                                            <td style="{{ $panelTitle }} text-align:center; width:30%;">{{ $currencyCode }} {{ number_format((float)($priceBreakdown['grand_total'] ?? 0), 0, '.', ',') }}</td>
                                        </tr>
                                    </table>
                                @endif

                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:2px solid #000; table-layout:fixed;">
                                    <tr>
                                        <td width="50%" style="{{ $cellBorder }} padding:0; vertical-align:top;">
                                            <div style="{{ $panelTitle }}">Hotel cost for entire package</div>
                                        </td>
                                        <td width="50%" style="{{ $cellBorder }} padding:0; vertical-align:top;">
                                            <div style="{{ $panelTitle }}">Other services cost for entire package</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%" style="{{ $cellBorder }} padding:8px 6px; vertical-align:top;">
                                            <div style="font-weight:bold; margin-bottom:6px;">Inclusions:</div>
                                            @php
                                                $hotelPriceLookup = [];
                                                foreach ($tourPrices['hotel_price_options'] ?? [] as $hp) {
                                                    $k = strtolower(trim((string)($hp['hotel_name'] ?? '')));
                                                    if ($k !== '') $hotelPriceLookup[$k] = $hp;
                                                }
                                            @endphp
                                            @if(!empty($hotelOptions) && is_array($hotelOptions))
                                                @php $seenHotelKeys = []; @endphp
                                                <ul style="margin:0; padding-left:18px;">
                                                    @foreach($hotelOptions as $h)
                                                        @php
                                                            $hotelName = $h['hotel_name'] ?? 'Hotel';
                                                            $hotelNameLower = strtolower(trim((string)$hotelName));
                                                            $roomCategoryName = $h['room_categories'][0]['name'] ?? ($h['hotel_category'] ?? 'Room');
                                                            $roomCatLower = strtolower(trim((string)$roomCategoryName));
                                                            $dedupKey = $hotelNameLower . '||' . $roomCatLower;
                                                            if (isset($seenHotelKeys[$dedupKey])) continue;
                                                            $seenHotelKeys[$dedupKey] = true;
                                                        @endphp
                                                        <li style="margin:2px 0; line-height:1.35;">{{ strtoupper($hotelName) }}-{{ strtoupper($roomCategoryName) }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <div>No hotel options available</div>
                                            @endif
                                        </td>
                                        <td width="50%" style="{{ $cellBorder }} padding:8px 6px; vertical-align:top;">
                                            <div style="font-weight:bold; margin-bottom:6px;">Inclusions:</div>
                                            @php $hasAnyOtherInclusions = (!empty($bookedAttractionCards) || !empty($bookedRestaurantCards) || !empty($bookedArrivals) || !empty($bookedDepartures) || !empty($bookedLocalTransfers)); @endphp
                                            @if($hasAnyOtherInclusions)
                                                <ul style="margin:0; padding-left:18px;">
                                                    @foreach($bookedAttractionCards as $attrCard)
                                                        @php
                                                            $attrTitle = $attrCard['title'] ?? '';
                                                            $ad = $attrCard['attraction'] ?? null;
                                                            $tr = is_array($ad) ? ($ad['transfer'] ?? null) : null;
                                                            $gd = is_array($ad) ? ($ad['guide'] ?? null) : null;
                                                        @endphp
                                                        <li style="margin:2px 0; line-height:1.35;">
                                                            <strong>Attraction:</strong> {{ $attrTitle }}
                                                            @if(is_array($ad) && is_array($tr) && (!empty($tr['vehicle_name']) || !empty($tr['type']) || !empty($tr['pickup_location_name'])))
                                                                @php
                                                                    $attrVehicleLabel = $resolveVehicleDisplayName(
                                                                        $tr['vehicle_name'] ?? '',
                                                                        $tr['vehicle_id'] ?? ($tr['vehicle_details']['vehicle_id'] ?? null)
                                                                    );
                                                                @endphp
                                                                <div style="margin:2px 0 0 14px;">
                                                                    <strong>Transfer / vehicle:</strong>
                                                                    {{ implode(' · ', array_filter([$tr['type'] ?? null, $tr['way'] ?? null])) }}
                                                                    @if($attrVehicleLabel !== '') — {{ $attrVehicleLabel }} @endif
                                                                    @if(!empty($tr['pickup_location_name']) || !empty($tr['pickup_time']))
                                                                        <br>
                                                                        @if(!empty($tr['pickup_location_name']))<strong>Pickup:</strong> {{ $tr['pickup_location_name'] }}@endif
                                                                        @if(!empty($tr['pickup_time'])) @if(!empty($tr['pickup_location_name'])) — @endif <strong>Time:</strong> {{ $tr['pickup_time'] }}@endif
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            @if(is_array($gd) && (!empty($gd['guide_name']) || !empty($gd['language']) || !empty($gd['pickup_time'])))
                                                                <div style="margin:2px 0 0 14px;">
                                                                    <strong>Guide:</strong> {{ $gd['guide_name'] ?? '' }}
                                                                    @if(!empty($gd['language'])) · {{ $gd['language'] }} @endif
                                                                    @if(!empty($gd['pickup_time']))<br><strong>Pickup time:</strong> {{ $gd['pickup_time'] }}@endif
                                                                </div>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                    @foreach($bookedRestaurantCards as $restCard)
                                                        @php
                                                            $restTitle = $restCard['title'] ?? '';
                                                            $rs = $restCard['restaurant'] ?? null;
                                                            $mealPlan = is_array($rs) ? ($rs['meal_plan'] ?? null) : null;
                                                            $tr = is_array($rs) ? ($rs['transfer'] ?? null) : null;
                                                        @endphp
                                                        <li style="margin:2px 0; line-height:1.35;">
                                                            <strong>Restaurant:</strong> {{ $restTitle }}@if(!empty($mealPlan)) — {{ $mealPlan }}@endif
                                                            @if(is_array($rs) && is_array($tr) && (!empty($tr['vehicle_name']) || !empty($tr['pickup_location_name']) || !empty($tr['pickup_time'])))
                                                                @php
                                                                    $restVehicleLabel = $resolveVehicleDisplayName(
                                                                        $tr['vehicle_name'] ?? '',
                                                                        $tr['vehicle_id'] ?? ($tr['vehicle_details']['vehicle_id'] ?? null)
                                                                    );
                                                                @endphp
                                                                <div style="margin:2px 0 0 14px;">
                                                                    <strong>Transfer / vehicle:</strong>
                                                                    {{ implode(' · ', array_filter([$tr['type'] ?? null, $tr['way'] ?? null])) }}
                                                                    @if($restVehicleLabel !== '') — {{ $restVehicleLabel }} @endif
                                                                </div>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                    @foreach(array_keys($bookedArrivals) as $ar)
                                                        <li style="margin:2px 0; line-height:1.35;">{{ $ar }}</li>
                                                    @endforeach
                                                    @foreach(array_keys($bookedDepartures) as $dp)
                                                        <li style="margin:2px 0; line-height:1.35;">{{ $dp }}</li>
                                                    @endforeach
                                                    @foreach(array_keys($bookedLocalTransfers) as $lt)
                                                        <li style="margin:2px 0; line-height:1.35;">{{ $lt }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <div>No other services booked</div>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50%" style="{{ $cellBorder }} padding:8px 6px; vertical-align:top;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:1px solid #000;">
                                                <tr>
                                                    <th style="{{ $thStyle }} width:33%;">Single</th>
                                                    <th style="{{ $thStyle }} width:33%;">Double</th>
                                                    <th style="{{ $thStyle }} width:33%;">Triple</th>
                                                </tr>
                                                <tr>
                                                    @if($breakdownHotelTotal > 0)
                                                        <td style="{{ $tdStyle }} text-align:center; font-weight:bold;">{{ $occupancyKey === 'single' ? $formatMoney($breakdownHotelTotal) : '—' }}</td>
                                                        <td style="{{ $tdStyle }} text-align:center; font-weight:bold;">{{ $occupancyKey === 'double' ? $formatMoney($breakdownHotelTotal) : '—' }}</td>
                                                        <td style="{{ $tdStyle }} text-align:center; font-weight:bold;">{{ $occupancyKey === 'triple' ? $formatMoney($breakdownHotelTotal) : '—' }}</td>
                                                    @else
                                                        <td style="{{ $tdStyle }} text-align:center; font-weight:bold;">{{ $formatMoney($hotelOnlySingleTotal) }}</td>
                                                        <td style="{{ $tdStyle }} text-align:center; font-weight:bold;">{{ $formatMoney($hotelOnlyDoubleTotal) }}</td>
                                                        <td style="{{ $tdStyle }} text-align:center; font-weight:bold;">{{ $formatMoney($hotelOnlyTripleTotal) }}</td>
                                                    @endif
                                                </tr>
                                            </table>
                                        </td>
                                        <td width="50%" style="{{ $cellBorder }} padding:8px 6px; vertical-align:top;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:1px solid #000;">
                                                <tr>
                                                    <th style="{{ $thStyle }}">{{ $breakdownOtherTotal > 0 ? 'Total price' : 'Price (per pax)' }}</th>
                                                </tr>
                                                <tr>
                                                    <td style="{{ $tdStyle }} text-align:center; font-weight:bold;">{{ $formatMoney($breakdownOtherTotal > 0 ? $breakdownOtherTotal : $otherTotalForOccupancy) }}</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                @if(!empty($suppHotels))
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin-top:14px;">
                                        <tr>
                                            <td style="{{ $panelTitle }}">Supplements – Hotels</td>
                                        </tr>
                                    </table>
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:2px solid #000; margin-bottom:14px;">
                                        <tr>
                                            <th style="{{ $thStyle }} text-align:left; width:52%;">Hotel</th>
                                            <th style="{{ $thStyle }} width:16%;">Single</th>
                                            <th style="{{ $thStyle }} width:16%;">Double</th>
                                            <th style="{{ $thStyle }} width:16%;">Triple</th>
                                        </tr>
                                        @foreach($suppHotels as $s)
                                            @php
                                                $hotelLabel = $s['hotel_name'] ?? ($s['display_name'] ?? ($s['name'] ?? 'Hotel'));
                                                $niceDate = !empty($s['date_range']) ? $formatDateRange($s['date_range']) : '';
                                                $suppSingle = (float)($s['single'] ?? 0);
                                                $suppDouble = (float)($s['double'] ?? 0);
                                                $suppTriple = (float)($s['triple'] ?? 0);
                                                if ($isProTour) {
                                                    $suppSingle = $suppDouble > 0 ? $suppDouble : $suppSingle;
                                                }
                                            @endphp
                                            <tr>
                                                <td style="{{ $tdStyle }}">
                                                    {{ $hotelLabel }}
                                                    @if($niceDate)<span style="color:#444;"> ({{ $niceDate }})</span>@endif
                                                </td>
                                                <td style="{{ $tdStyle }} text-align:center;">{{ $formatMoney($suppSingle) }}</td>
                                                <td style="{{ $tdStyle }} text-align:center;">{{ $formatMoney($suppDouble) }}</td>
                                                <td style="{{ $tdStyle }} text-align:center;">{{ $suppTriple > 0 ? $formatMoney($suppTriple) : '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif

                                @if(!empty($suppServices))
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin-top:4px;">
                                        <tr>
                                            <td style="{{ $panelTitle }}">Supplements – Other Services</td>
                                        </tr>
                                    </table>
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:2px solid #000; margin-bottom:14px;">
                                        <tr>
                                            <th style="{{ $thStyle }} text-align:left; width:70%;">Service</th>
                                            <th style="{{ $thStyle }} width:30%;">Price</th>
                                        </tr>
                                        @foreach($suppServices as $s)
                                            @php
                                                $suppTypeRaw = trim((string)($s['type'] ?? ''));
                                                $svcName = trim((string)($s['name'] ?? ($s['AttractionName'] ?? ($s['restaurantName'] ?? ''))));
                                                $tSlug = strtolower(str_replace([' ', '-'], '_', $suppTypeRaw));
                                                $nSlug = strtolower(str_replace([' ', '-'], '_', $svcName));
                                                $typePretty = $tSlug !== '' ? \Illuminate\Support\Str::headline($tSlug) : '';
                                                $namePretty = $nSlug !== '' ? \Illuminate\Support\Str::headline($nSlug) : '';
                                                $typeNorm = strtolower(preg_replace('/[^a-z0-9]+/', '', $suppTypeRaw));
                                                $nameNorm = strtolower(preg_replace('/[^a-z0-9]+/', '', $svcName));
                                                if ($svcName !== '' && $typeNorm !== '' && $typeNorm === $nameNorm) {
                                                    $svcLabel = $typePretty;
                                                } elseif ($svcName !== '' && $typePretty !== '' && $typeNorm !== $nameNorm) {
                                                    $svcLabel = $typePretty . ': ' . $namePretty;
                                                } elseif ($svcName !== '') {
                                                    $svcLabel = $namePretty;
                                                } else {
                                                    $svcLabel = $typePretty !== '' ? $typePretty : 'Supplement';
                                                }
                                                $suppPrice = $occupancyKey === 'double'
                                                    ? (float)($s['double'] ?? 0)
                                                    : (float)($s['single'] ?? 0);
                                            @endphp
                                            <tr>
                                                <td style="{{ $tdStyle }}">{{ $svcLabel }}</td>
                                                <td style="{{ $tdStyle }} text-align:center;">{{ $formatMoney($suppPrice) }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif

                                @if(!empty($quotationInformationHtml))
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:1px solid #000; margin-top:10px;">
                                        <tr>
                                            <td style="padding:8px 6px; font-size:12px; line-height:1.35;">
                                                <div style="font-weight:bold; margin-bottom:6px;">Quotation Information</div>
                                                {!! $quotationInformationHtml !!}
                                            </td>
                                        </tr>
                                    </table>
                                @endif
                            </td>
                        </tr>
                    @elseif(count($bookedServices) > 0)
                        <tr>
                            <td style="padding:18px 28px 4px 28px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:15px; font-weight:700; color:{{ $textDark }};">📖 Proposed itinerary</td>
                                        <td style="text-align:right;">
                                            <a href="{{ $downloadUrl }}" style="display:inline-block; font-size:12px; color:{{ $brandBlue }}; text-decoration:none; font-weight:600; border:1px solid {{ $border }}; border-radius:8px; padding:8px 14px;">⬇ Download quotation</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        @php $lastDayLabel = null; @endphp
                        @foreach($bookedServices as $service)
                            @php
                                $dayLabel = $service['day'] ?? null;
                                $accent   = $service['accent'] ?? $brandBlue;
                                $typeLabel = strtoupper($service['badge'] ?? ($service['type'] ?? 'SERVICE'));
                                $priceDisplay = $formatServicePrice($service);
                                $nightBadge = $extractNightBadge($service);
                                $hotelMeta = (strtolower($service['order_type'] ?? '') === 'hotel') ? $extractHotelMeta($service) : [];
                            @endphp

                            @if($dayLabel && $dayLabel !== $lastDayLabel)
                                @php $lastDayLabel = $dayLabel; @endphp
                                <tr>
                                    <td style="padding:10px 28px 0 28px;">
                                        <span style="display:inline-block; background-color:{{ $brandBlue }}; color:#ffffff; font-size:11px; font-weight:700; padding:5px 12px; border-radius:6px;">{{ strtoupper($dayLabel) }}</span>
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td style="padding:10px 28px 0 28px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid {{ $border }}; border-left:4px solid {{ $accent }}; border-radius:12px;">
                                        <tr>
                                            @if(!empty($service['time']))
                                                <td style="vertical-align:top; padding:16px 6px 16px 16px; width:62px;">
                                                    <div style="font-size:11px; font-weight:700; color:{{ $textMuted }};">{{ $service['time'] }}</div>
                                                </td>
                                            @endif
                                            <td style="vertical-align:top; padding:14px 16px 14px {{ !empty($service['time']) ? '0' : '16px' }};">
                                                <div style="font-size:10px; font-weight:700; letter-spacing:.6px; color:{{ $accent }};">{{ $typeLabel }}</div>
                                                <div style="font-size:15px; font-weight:700; color:{{ $textDark }}; margin-top:3px;">{{ $service['title'] ?? ($service['name'] ?? '—') }}</div>
                                                @if(!empty($service['subtitle']))
                                                    <div style="font-size:12px; color:{{ $textMuted }}; margin-top:2px;">{{ $service['subtitle'] }}</div>
                                                @endif
                                                @if(!empty($service['date']))
                                                    <div style="font-size:12px; color:{{ $textMuted }}; margin-top:4px;">{{ $service['date'] }}</div>
                                                @endif
                                                @if(!empty($service['pax']))
                                                    <div style="font-size:12px; color:{{ $textMuted }}; margin-top:2px;">{{ $service['pax'] }}</div>
                                                @endif

                                                @if(!empty($service['lines']) && is_array($service['lines']))
                                                    @foreach($service['lines'] as $line)
                                                        @if(is_array($line))
                                                            <div style="font-size:12px; color:{{ $textDark }}; margin-top:4px;">
                                                                <span style="color:{{ $textMuted }};">{{ $line['label'] ?? '' }}:</span>
                                                                {{ $line['value'] ?? '' }}
                                                            </div>
                                                        @else
                                                            <div style="font-size:12px; color:{{ $textDark }}; margin-top:4px;">{{ $line }}</div>
                                                        @endif
                                                    @endforeach
                                                @elseif(!empty($service['details']))
                                                    <div style="font-size:12px; color:{{ $textDark }}; margin-top:4px;">{{ $service['details'] }}</div>
                                                @endif
                                            </td>
                                            <td style="vertical-align:top; text-align:right; padding:14px 16px 14px 0; width:110px;">
                                                @if($nightBadge)
                                                    <span style="display:inline-block; background-color:#e7f7ee; color:#16a34a; font-size:11px; font-weight:700; padding:5px 10px; border-radius:14px;">{{ $nightBadge }}</span>
                                                @elseif($priceDisplay)
                                                    <span style="font-size:13px; font-weight:700; color:{{ $accent }};">{{ $priceDisplay }}</span>
                                                @elseif(!empty($service['time']))
                                                    <span style="font-size:12px; font-weight:600; color:{{ $accent }};">{{ $service['time'] }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if(!empty($hotelMeta))
                                            <tr>
                                                <td colspan="3" style="padding:0 16px 16px 16px;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid {{ $border }};">
                                                        <tr>
                                                            @foreach($hotelMeta as $m)
                                                                <td style="vertical-align:top; padding:12px 8px 0 0; width:25%;">
                                                                    <div style="font-size:10px; color:{{ $textMuted }};">{{ $m['label'] }}</div>
                                                                    <div style="font-size:12px; font-weight:700; color:{{ $textDark }}; margin-top:3px;">{{ $m['value'] }}</div>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <!-- TOTAL / WHAT'S INCLUDED -->
                    @if(!$hasPdfQuotationLayout)
                    <tr>
                        <td style="padding:18px 28px 4px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:{{ $bgSoft }}; border:1px solid {{ $border }}; border-radius:12px;">
                                <tr>
                                    <td style="vertical-align:top; padding:20px; width:50%;">
                                        <div style="font-size:13px; font-weight:700; color:{{ $textDark }};">Total estimated price</div>
                                        @if($totalEstimation > 0)
                                            <div style="font-size:28px; font-weight:800; color:{{ $brandBlue }}; margin-top:6px;">{{ $totalPrice }}</div>
                                        @else
                                            <div style="font-size:16px; font-weight:600; color:{{ $textMuted }}; margin-top:6px;">Price on request</div>
                                        @endif
                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-top:6px; line-height:1.5;">Quotation total for all proposed services above. Final amount subject to availability and confirmation.</div>
                                    </td>
                                    <td style="vertical-align:top; padding:20px; width:50%;">
                                        <div style="font-size:13px; font-weight:700; color:{{ $textDark }}; margin-bottom:10px;">What's included</div>
                                        @if(count($included) > 0)
                                            @foreach($included as $inc)
                                                <div style="font-size:12px; color:{{ $textDark }}; margin-bottom:7px;">
                                                    <span style="color:{{ $brandBlue }}; font-weight:700;">✓</span>&nbsp; {{ $inc }}
                                                </div>
                                            @endforeach
                                        @else
                                            <div style="font-size:12px; color:{{ $textMuted }};">Services as listed in your proposed itinerary.</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    <!-- CTA BUTTONS -->
                    <tr>
                        <td style="padding:16px 28px 4px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:50%; padding-right:7px;">
                                        <a href="{{ $itineraryUrl }}" style="display:block; text-align:center; background-color:{{ $brandBlue }}; color:#ffffff; font-size:14px; font-weight:700; text-decoration:none; padding:14px 0; border-radius:10px;">View full quotation →</a>
                                    </td>
                                    <td style="width:50%; padding-left:7px;">
                                        <a href="{{ $chatUrl }}" style="display:block; text-align:center; background-color:#ffffff; color:{{ $textDark }}; font-size:14px; font-weight:700; text-decoration:none; padding:13px 0; border-radius:10px; border:1px solid {{ $border }};">💬 Chat with travel specialist</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FEATURES -->
                    <tr>
                        <td style="padding:18px 28px 6px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid {{ $border }};">
                                <tr>
                                    @foreach($features as $feature)
                                        <td style="vertical-align:top; text-align:center; padding:16px 6px 4px 6px; width:25%;">
                                            <div style="font-size:18px;">{{ $feature['icon'] }}</div>
                                            <div style="font-size:12px; font-weight:700; color:{{ $textDark }}; margin-top:6px;">{{ $feature['title'] }}</div>
                                            <div style="font-size:11px; color:{{ $textMuted }}; margin-top:2px;">{{ $feature['subtitle'] }}</div>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="padding:22px 28px 28px 28px; border-top:1px solid {{ $border }};">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle; width:33%;">
                                        @if($logo)
                                            <img src="{{ $logo }}" alt="{{ $companyName }}" style="max-height:26px; display:block;">
                                        @else
                                            <span style="font-size:17px; font-weight:700; color:{{ $brandBlue }};">{{ $companyName }}</span>
                                        @endif
                                        <div style="font-size:10px; color:{{ $textMuted }}; margin-top:4px;">{{ $tagline }}</div>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center; width:33%;">
                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-bottom:6px;">Connect with us</div>
                                        <a href="#" style="text-decoration:none; color:{{ $brandBlue }}; font-size:13px; font-weight:700; padding:0 5px;">f</a>
                                        <a href="#" style="text-decoration:none; color:{{ $brandBlue }}; font-size:13px; font-weight:700; padding:0 5px;">◎</a>
                                        <a href="#" style="text-decoration:none; color:{{ $brandBlue }}; font-size:13px; font-weight:700; padding:0 5px;">in</a>
                                    </td>
                                    <td style="vertical-align:middle; text-align:right; width:34%;">
                                        <div style="font-size:11px; color:{{ $textMuted }}; line-height:1.5;">Thank you for partnering with {{ $companyName }}.<br>We look forward to confirming this trip with you.</div>
                                    </td>
                                </tr>
                            </table>
                            <div style="font-size:10px; color:{{ $textMuted }}; text-align:center; margin-top:18px;">&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
