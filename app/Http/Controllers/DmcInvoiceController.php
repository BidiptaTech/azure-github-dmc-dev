<?php

namespace App\Http\Controllers;

use App\Models\DmcInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DmcInvoiceController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user || (int) $user->role_id !== 1) {
            abort(403, 'You do not have permission to access DMC Invoice settings.');
        }
    }

    public function index()
    {
        $this->authorizeAccess();

        // Travclicks-level: only one invoice price row is allowed.
        $invoice = DmcInvoice::query()->orderByDesc('id')->first();

        return view('dmc-invoice.index', compact('invoice'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        if (DmcInvoice::query()->exists()) {
            return redirect()
                ->route('dmc-invoice.index')
                ->withErrors(['adult_price' => 'DMC Invoice prices already exist. Edit the existing row instead of adding another.']);
        }

        $validated = $request->validate([
            'adult_price' => ['required', 'numeric', 'min:0'],
            'child_price' => ['required', 'numeric', 'min:0'],
            'infant_price' => ['required', 'numeric', 'min:0'],
        ]);

        DmcInvoice::create([
            'adult_price' => $validated['adult_price'],
            'child_price' => $validated['child_price'],
            'infant_price' => $validated['infant_price'],
            'currency' => DmcInvoice::CURRENCY,
            'created_by' => Auth::user()->userId,
        ]);

        return redirect()->route('dmc-invoice.index')->with('success', 'DMC Invoice prices saved successfully.');
    }

    public function edit(DmcInvoice $dmc_invoice)
    {
        $this->authorizeAccess();

        return view('dmc-invoice.edit', ['invoice' => $dmc_invoice]);
    }

    public function update(Request $request, DmcInvoice $dmc_invoice)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'adult_price' => ['required', 'numeric', 'min:0'],
            'child_price' => ['required', 'numeric', 'min:0'],
            'infant_price' => ['required', 'numeric', 'min:0'],
        ]);

        $dmc_invoice->update([
            'adult_price' => $validated['adult_price'],
            'child_price' => $validated['child_price'],
            'infant_price' => $validated['infant_price'],
            'currency' => DmcInvoice::CURRENCY,
        ]);

        return redirect()->route('dmc-invoice.index')->with('success', 'DMC Invoice prices updated successfully.');
    }

    public function destroy(DmcInvoice $dmc_invoice)
    {
        $this->authorizeAccess();

        $dmc_invoice->delete();

        return redirect()->route('dmc-invoice.index')->with('success', 'DMC Invoice prices deleted successfully.');
    }
}
