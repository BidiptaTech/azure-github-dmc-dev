<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\SupplierMaster;
use App\Services\SupplierEnvService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

class SupplierMasterController extends Controller
{
    public function __construct(
        private SupplierEnvService $supplierEnv
    ) {}

    public function index()
    {
        if (! $this->isTravClicksAdmin()) {
            abort(403, 'Only TravClicks administrators can access this page.');
        }

        $suppliers = SupplierMaster::query()
            ->with('country')
            ->orderBy('name')
            ->get();

        $countries = Country::query()->orderBy('name')->get();
        $supplierCodes = SupplierMaster::codeOptions();
        $supplierDefinitions = $this->supplierEnv->allDefinitions();

        if ($supplierDefinitions === [] && is_file(config_path('suppliers.php'))) {
            config(['suppliers' => require config_path('suppliers.php')]);
            $supplierDefinitions = config('suppliers', []);
        }

        $credentialValues = [];
        foreach (array_keys($supplierDefinitions) as $code) {
            $credentialValues[$code] = $this->supplierEnv->valuesFor($code);
        }

        return view('suppliers.index', compact(
            'suppliers',
            'countries',
            'supplierCodes',
            'supplierDefinitions',
            'credentialValues'
        ));
    }

    public function store(Request $request)
    {
        if (! $this->isTravClicksAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id', 'unique:suppliers_master,country_id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', Rule::in(array_keys(config('suppliers', [])))],
            'status' => ['sometimes', 'boolean'],
        ]);

        SupplierMaster::query()->create([
            'country_id' => $validated['country_id'],
            'name' => $validated['name'],
            'code' => $validated['code'],
            'status' => $request->boolean('status', true),
        ]);

        return back()->with('success', 'Country supplier mapping saved.');
    }

    public function update(Request $request, SupplierMaster $supplier)
    {
        if (! $this->isTravClicksAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id', 'unique:suppliers_master,country_id,'.$supplier->id],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', Rule::in(array_keys(config('suppliers', [])))],
            'status' => ['sometimes', 'boolean'],
        ]);

        $supplier->update([
            'country_id' => $validated['country_id'],
            'name' => $validated['name'],
            'code' => $validated['code'],
            'status' => $request->boolean('status'),
        ]);

        return back()->with('success', 'Country supplier mapping updated.');
    }

    public function updateCredentials(Request $request, string $code)
    {
        if (! $this->isTravClicksAdmin()) {
            abort(403);
        }

        if (! array_key_exists($code, config('suppliers', []))) {
            abort(404);
        }

        $request->validate($this->supplierEnv->validationRules($code));

        try {
            $this->supplierEnv->updateFromRequest($code, $request);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $label = config("suppliers.{$code}.label", $code);

        return back()->with('success', "{$label} credentials saved to .env file.");
    }

    public function destroy(SupplierMaster $supplier)
    {
        if (! $this->isTravClicksAdmin()) {
            abort(403);
        }

        $supplier->delete();

        return back()->with('success', 'Supplier mapping removed.');
    }

    private function isTravClicksAdmin(): bool
    {
        return auth()->check()
            && (int) auth()->user()->role_id === 1
            && (hasPermission('settings') || hasPermission('edit settings'));
    }
}
