@php
    /** @var \App\Models\Invoice|null $invoice */
    $logoType = $logoType ?? 'dmc';
    $showBlueTitle = $showBlueTitle ?? true;
    $hasInvoice = isset($invoice) && $invoice;

    $displayLogoSrc = null;
    $displayCompanyName = 'DMC Name';
    $displayCompanyRegNo = null;
    $displayLicenceNo = null;
    $displayAddress = null;
    $displayPhone = null;
    $displayEmail = null;

    $docTitle = $docTitle ?? 'INVOICE';
    $docNumber = $docNumber ?? 'DRAFT';
    if ($hasInvoice) {
        $docTitle = 'INVOICE';
        $docNumber = $invoice->invoice_number ?? 'DRAFT';
        if (($invoice->invoice_type ?? '') === 'proforma') {
            $docTitle = 'PROFORMA INVOICE';
            $docNumber = $invoice->proforma_number ?? 'DRAFT';
        }
    }

    // Agency logo option (if used by caller)
    if ($hasInvoice && $logoType === 'agency' && $invoice->agent && $invoice->agent->agency) {
        $agency = $invoice->agent->agency;
        $displayCompanyName = $agency->agency_name ?? ($invoice->travel_company_details['company_name'] ?? 'Agency Name');
        $agencyLogo = $agency->logo ?? null;
        if ($agencyLogo) {
            try {
                if (preg_match('/^data:image\//i', $agencyLogo)) {
                    $displayLogoSrc = $agencyLogo;
                } else {
                    $logoContent = preg_match('/^https?:\/\//i', $agencyLogo)
                        ? @file_get_contents($agencyLogo)
                        : @file_get_contents(public_path(ltrim($agencyLogo, '/')));
                    if ($logoContent) {
                        $displayLogoSrc = 'data:image/png;base64,' . base64_encode($logoContent);
                    }
                }
            } catch (\Exception $e) {
                $displayLogoSrc = null;
            }
        }
    }

    // DMC logo + contact details
    if ($hasInvoice && $logoType === 'dmc') {
        $dmcUser = $invoice->dmc;
        $rootDmc = $dmcUser;
        $visited = [];
        while ($rootDmc && (int) $rootDmc->role_id !== 11 && $rootDmc->created_by && !in_array($rootDmc->created_by, $visited, true)) {
            $visited[] = $rootDmc->created_by;
            $rootDmc = \App\Models\User::where('userId', $rootDmc->created_by)->first();
        }
        if (!$rootDmc) {
            $rootDmc = $dmcUser;
        }

        $dmcLogo = $rootDmc->logo ?? $dmcUser->logo ?? null;
        $displayCompanyName = $rootDmc->company_name ?? $dmcUser->company_name ?? 'DMC Name';

        $displayAddress = $rootDmc->company_address
            ?? $rootDmc->address
            ?? $dmcUser->company_address
            ?? $dmcUser->address
            ?? null;
        $displayPhone = $rootDmc->company_phone
            ?? $rootDmc->phone
            ?? $rootDmc->mobile
            ?? $dmcUser->company_phone
            ?? $dmcUser->phone
            ?? $dmcUser->mobile
            ?? null;
        $displayEmail = $rootDmc->company_email
            ?? $rootDmc->email
            ?? $dmcUser->company_email
            ?? $dmcUser->email
            ?? null;

        $reg = trim((string) ($rootDmc->company_reg_no ?? ''));
        if ($reg === '') {
            $reg = trim((string) ($dmcUser->company_reg_no ?? ''));
        }
        $displayCompanyRegNo = $reg !== '' ? $reg : null;

        $lic = $rootDmc->ta_licence_no ?? $rootDmc->licence_no ?? $dmcUser->ta_licence_no ?? $dmcUser->licence_no ?? null;
        $displayLicenceNo = ($lic !== null && trim((string) $lic) !== '') ? trim((string) $lic) : null;

        if ($dmcLogo) {
            try {
                if (preg_match('/^data:image\//i', $dmcLogo)) {
                    $displayLogoSrc = $dmcLogo;
                } else {
                    $logoContent = preg_match('/^https?:\/\//i', $dmcLogo)
                        ? @file_get_contents($dmcLogo)
                        : @file_get_contents(public_path(ltrim($dmcLogo, '/')));
                    if ($logoContent) {
                        $displayLogoSrc = 'data:image/png;base64,' . base64_encode($logoContent);
                    }
                }
            } catch (\Exception $e) {
                $displayLogoSrc = null;
            }
        }
    }

    // Non-invoice fallback (e.g. itinerary PDF)
    if (!$hasInvoice && isset($user_dmc) && $user_dmc) {
        $displayCompanyName = $user_dmc->company_name ?? $user_dmc->name ?? config('app.name');
        $displayAddress = $user_dmc->company_address ?? $user_dmc->address ?? null;
        $displayPhone = $user_dmc->company_phone ?? $user_dmc->phone ?? $user_dmc->tel ?? null;
        $displayEmail = $user_dmc->company_email ?? $user_dmc->email ?? null;
        $displayCompanyRegNo = $user_dmc->company_reg_no ?? null;
        $displayLicenceNo = $user_dmc->ta_licence_no ?? $user_dmc->licence_no ?? null;

        $dmcLogo = $user_dmc->logo ?? null;
        if ($dmcLogo) {
            try {
                if (preg_match('/^data:image\//i', $dmcLogo)) {
                    $displayLogoSrc = $dmcLogo;
                } else {
                    $logoContent = preg_match('/^https?:\/\//i', $dmcLogo)
                        ? @file_get_contents($dmcLogo)
                        : @file_get_contents(public_path(ltrim($dmcLogo, '/')));
                    if ($logoContent) {
                        $displayLogoSrc = 'data:image/png;base64,' . base64_encode($logoContent);
                    } else {
                        // fallback for already-resolvable URL/path
                        $displayLogoSrc = $dmcLogo;
                    }
                }
            } catch (\Exception $e) {
                $displayLogoSrc = $dmcLogo;
            }
        }
    }
@endphp

<div class="header">
    <table class="header-table">
        <tr>
            <td class="header-left">
                @if($displayLogoSrc)
                    <div class="dmc-logo-wrapper">
                        <img src="{{ $displayLogoSrc }}" class="dmc-logo" alt="">
                    </div>
                @endif
            </td>
            <td class="header-center">
                <div class="dmc-name">{{ $displayCompanyName }}</div>
                @if(!empty($displayAddress))
                    <div class="dmc-address">Address: {{ $displayAddress }}</div>
                @endif
                @if(!empty($displayPhone) || !empty($displayEmail))
                    <div class="dmc-contact">
                        @if(!empty($displayPhone)) Tel: {{ $displayPhone }} @endif
                        @if(!empty($displayPhone) && !empty($displayEmail)) | @endif
                        @if(!empty($displayEmail)) Email: {{ $displayEmail }} @endif
                    </div>
                @endif
                @if(!empty($displayCompanyRegNo) || !empty($displayLicenceNo))
                    <div class="dmc-meta">
                        @if(!empty($displayCompanyRegNo))
                            <div>UEN/Co. Reg No.: {{ $displayCompanyRegNo }}</div>
                        @endif
                        @if(!empty($displayLicenceNo))
                            <div>TA Licence No.: {{ $displayLicenceNo }}</div>
                        @endif
                    </div>
                @endif
            </td>
            <td class="header-right">
                <div class="doc-number">{{ $docNumber }}</div>
            </td>
        </tr>
    </table>

    @if($showBlueTitle)
        <div class="header-doc-title">{{ $docTitle }} {{ $docNumber }}</div>
    @endif
</div>
