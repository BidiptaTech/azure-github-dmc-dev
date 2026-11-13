<?php

namespace App\Http\Controllers;

use App\Models\DmcOffer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DmcOfferController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user || (int) $user->role_id !== 1) {
            abort(403, 'You do not have permission to access DMC Offer settings.');
        }
    }

    private function dmcList(?int $includeDmcId = null)
    {
        // One offer per DMC: only show DMCs that do not already have an offer
        // (plus the current offer's DMC when editing).
        $takenDmcIds = DmcOffer::query()->pluck('dmc_id')->map(fn ($id) => (int) $id)->all();
        if ($includeDmcId) {
            $takenDmcIds = array_values(array_filter(
                $takenDmcIds,
                fn ($id) => (int) $id !== (int) $includeDmcId
            ));
        }

        return User::whereIn('role_id', DmcOffer::DMC_ROLE_IDS)
            ->when(!empty($takenDmcIds), fn ($q) => $q->whereNotIn('userId', $takenDmcIds))
            ->select('userId', 'name', 'company_name', 'email', 'country')
            ->orderByRaw("COALESCE(NULLIF(company_name, ''), name)")
            ->get();
    }

    public function index()
    {
        $this->authorizeAccess();

        $offers = DmcOffer::query()
            ->with('dmc:userId,name,company_name,country')
            ->orderByDesc('id')
            ->get();

        $dmcs = $this->dmcList();

        return view('dmc-offer.index', compact('offers', 'dmcs'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'dmc_id' => [
                'required',
                'integer',
                Rule::exists('users', 'userId')->where(function ($q) {
                    $q->whereIn('role_id', DmcOffer::DMC_ROLE_IDS);
                }),
                Rule::unique('dmc_offer', 'dmc_id'),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'adult_price' => ['required', 'numeric', 'min:0'],
            'child_price' => ['required', 'numeric', 'min:0'],
            'infant_price' => ['required', 'numeric', 'min:0'],
        ], [
            'dmc_id.unique' => 'This DMC already has an offer. Edit the existing offer instead of adding another.',
        ]);

        DmcOffer::create([
            'dmc_id' => $validated['dmc_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'adult_price' => $validated['adult_price'],
            'child_price' => $validated['child_price'],
            'infant_price' => $validated['infant_price'],
            'currency' => DmcOffer::CURRENCY,
            'created_by' => Auth::user()->userId,
        ]);

        return redirect()->route('dmc-offer.index')->with('success', 'DMC Offer saved successfully.');
    }

    public function edit(DmcOffer $dmc_offer)
    {
        $this->authorizeAccess();

        $dmcs = $this->dmcList((int) $dmc_offer->dmc_id);

        return view('dmc-offer.edit', [
            'offer' => $dmc_offer,
            'dmcs' => $dmcs,
        ]);
    }

    public function update(Request $request, DmcOffer $dmc_offer)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'dmc_id' => [
                'required',
                'integer',
                Rule::exists('users', 'userId')->where(function ($q) {
                    $q->whereIn('role_id', DmcOffer::DMC_ROLE_IDS);
                }),
                Rule::unique('dmc_offer', 'dmc_id')->ignore($dmc_offer->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'adult_price' => ['required', 'numeric', 'min:0'],
            'child_price' => ['required', 'numeric', 'min:0'],
            'infant_price' => ['required', 'numeric', 'min:0'],
        ], [
            'dmc_id.unique' => 'This DMC already has an offer. Only one offer is allowed per DMC.',
        ]);

        $dmc_offer->update([
            'dmc_id' => $validated['dmc_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'adult_price' => $validated['adult_price'],
            'child_price' => $validated['child_price'],
            'infant_price' => $validated['infant_price'],
            'currency' => DmcOffer::CURRENCY,
        ]);

        return redirect()->route('dmc-offer.index')->with('success', 'DMC Offer updated successfully.');
    }

    public function destroy(DmcOffer $dmc_offer)
    {
        $this->authorizeAccess();

        $dmc_offer->delete();

        return redirect()->route('dmc-offer.index')->with('success', 'DMC Offer deleted successfully.');
    }
}
