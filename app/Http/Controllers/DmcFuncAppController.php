<?php

namespace App\Http\Controllers;

use App\Models\DmcFuncApp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DmcFuncAppController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user || (int) $user->role_id !== 1) {
            abort(403, 'You do not have permission to access DMC Func App settings.');
        }
    }

    private function dmcList()
    {
        return User::where('role_id', 11)
            ->select('userId', 'name', 'company_name', 'email', 'country')
            ->orderByRaw("COALESCE(NULLIF(company_name, ''), name)")
            ->get();
    }

    private function normalizeFunctionName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '', trim($name)) ?? '';
    }

    private function normalizeDmcIds($dmcIds): array
    {
        return array_values(array_unique(array_filter(array_map('intval', (array) $dmcIds))));
    }

    private function validateAssignments(array $dmcIds, int $maximumLimit, ?int $exceptId = null): array
    {
        $errors = [];

        if (count($dmcIds) > $maximumLimit) {
            $errors['dmc_id'] = "This method can have a maximum of {$maximumLimit} DMC(s).";
        }

        $validDmcIds = User::where('role_id', 11)
            ->whereIn('userId', $dmcIds)
            ->pluck('userId')
            ->map(fn ($id) => (int) $id)
            ->all();

        $invalid = array_diff($dmcIds, $validDmcIds);
        if (!empty($invalid)) {
            $errors['dmc_id'] = 'One or more selected DMCs are invalid.';
        }

        $alreadyAssigned = DmcFuncApp::assignedDmcIds($exceptId);
        $conflicts = array_values(array_intersect($dmcIds, $alreadyAssigned));
        if (!empty($conflicts)) {
            $names = User::whereIn('userId', $conflicts)
                ->get(['userId', 'name', 'company_name'])
                ->map(fn ($dmc) => $dmc->company_name ?: $dmc->name)
                ->filter()
                ->implode(', ');

            $errors['dmc_id'] = 'These DMC(s) are already assigned to another method: ' . ($names ?: implode(', ', $conflicts));
        }

        return $errors;
    }

    public function index()
    {
        $this->authorizeAccess();

        $methods = DmcFuncApp::query()->orderBy('function_name')->get();
        $assignedDmcIds = DmcFuncApp::assignedDmcIds();
        $dmcs = $this->dmcList();
        $availableDmcs = $dmcs->whereNotIn('userId', $assignedDmcIds)->values();

        $dmcNames = $dmcs->keyBy('userId')->map(function ($dmc) {
            $label = $dmc->company_name ?: $dmc->name;
            if (!empty($dmc->country)) {
                $label .= ' (' . $dmc->country . ')';
            }

            return $label;
        });

        return view('dmc-func-app.index', compact('methods', 'availableDmcs', 'dmcNames'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $functionName = $this->normalizeFunctionName((string) $request->input('function_name', ''));
        $request->merge(['function_name' => $functionName]);

        if ($functionName !== '' && DmcFuncApp::whereRaw('LOWER(function_name) = ?', [strtolower($functionName)])->exists()) {
            return back()->withErrors(['function_name' => 'This function name already exists.'])->withInput();
        }

        $validated = $request->validate([
            'function_name' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z_][A-Za-z0-9_]*$/',
                Rule::unique('dmc_func_apps', 'function_name'),
            ],
            'maximum_limit' => ['required', 'integer', 'min:1', 'max:1000'],
            'dmc_id' => ['nullable', 'array'],
            'dmc_id.*' => ['integer'],
        ], [
            'function_name.regex' => 'Function name must start with a letter or underscore and contain only letters, numbers, or underscores (no spaces or special characters).',
        ]);

        $dmcIds = $this->normalizeDmcIds($validated['dmc_id'] ?? []);
        $assignmentErrors = $this->validateAssignments($dmcIds, (int) $validated['maximum_limit']);
        if (!empty($assignmentErrors)) {
            return back()->withErrors($assignmentErrors)->withInput();
        }

        DmcFuncApp::create([
            'function_name' => $validated['function_name'],
            'maximum_limit' => (int) $validated['maximum_limit'],
            'dmc_id' => $dmcIds,
        ]);

        return redirect()->route('dmc-func-app.index')->with('success', 'DMC Func App method created successfully.');
    }

    public function edit(DmcFuncApp $dmc_func_app)
    {
        $this->authorizeAccess();

        $assignedElsewhere = DmcFuncApp::assignedDmcIds((int) $dmc_func_app->id);
        $currentIds = $dmc_func_app->dmc_ids;
        $dmcs = $this->dmcList()->filter(function ($dmc) use ($assignedElsewhere, $currentIds) {
            return in_array((int) $dmc->userId, $currentIds, true)
                || !in_array((int) $dmc->userId, $assignedElsewhere, true);
        })->values();

        return view('dmc-func-app.edit', [
            'method' => $dmc_func_app,
            'dmcs' => $dmcs,
            'selectedDmcIds' => $currentIds,
        ]);
    }

    public function update(Request $request, DmcFuncApp $dmc_func_app)
    {
        $this->authorizeAccess();

        $functionName = $this->normalizeFunctionName((string) $request->input('function_name', ''));
        $request->merge(['function_name' => $functionName]);

        if (
            $functionName !== ''
            && DmcFuncApp::whereRaw('LOWER(function_name) = ?', [strtolower($functionName)])
                ->where('id', '!=', $dmc_func_app->id)
                ->exists()
        ) {
            return back()->withErrors(['function_name' => 'This function name already exists.'])->withInput();
        }

        $validated = $request->validate([
            'function_name' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z_][A-Za-z0-9_]*$/',
                Rule::unique('dmc_func_apps', 'function_name')->ignore($dmc_func_app->id),
            ],
            'maximum_limit' => ['required', 'integer', 'min:1', 'max:1000'],
            'dmc_id' => ['nullable', 'array'],
            'dmc_id.*' => ['integer'],
        ], [
            'function_name.regex' => 'Function name must start with a letter or underscore and contain only letters, numbers, or underscores (no spaces or special characters).',
        ]);

        $dmcIds = $this->normalizeDmcIds($validated['dmc_id'] ?? []);
        $assignmentErrors = $this->validateAssignments(
            $dmcIds,
            (int) $validated['maximum_limit'],
            (int) $dmc_func_app->id
        );
        if (!empty($assignmentErrors)) {
            return back()->withErrors($assignmentErrors)->withInput();
        }

        $dmc_func_app->update([
            'function_name' => $validated['function_name'],
            'maximum_limit' => (int) $validated['maximum_limit'],
            'dmc_id' => $dmcIds,
        ]);

        return redirect()->route('dmc-func-app.index')->with('success', 'DMC Func App method updated successfully.');
    }

    public function destroy(DmcFuncApp $dmc_func_app)
    {
        $this->authorizeAccess();

        $dmc_func_app->delete();

        return redirect()->route('dmc-func-app.index')->with('success', 'DMC Func App method deleted successfully.');
    }
}
