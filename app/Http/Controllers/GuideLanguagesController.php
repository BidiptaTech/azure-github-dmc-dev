<?php

namespace App\Http\Controllers;

use App\Models\Languages;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuideLanguagesController extends Controller
{
    public function index()
    {
        if (!hasPermission('settings') && !hasPermission('edit settings')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $languages = Languages::query()
            ->orderBy('name')
            ->get();

        return view('guide-languages.index', compact('languages'));
    }

    public function store(Request $request)
    {
        if (!hasPermission('settings') && !hasPermission('edit settings')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);
        $model = $validated['model'] !== null ? trim($validated['model']) : null;

        // Case-insensitive duplicate check + restore if it was soft-deleted.
        $existing = Languages::withTrashed()
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->first();

        if ($existing && !$existing->trashed()) {
            return back()
                ->withErrors(['name' => 'This language already exists.'])
                ->withInput();
        }

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update([
                'name' => $name,
                'model' => $model,
            ]);

            return redirect()->route('guide-languages.index')->with('success', 'Language restored successfully.');
        }

        Languages::create([
            'name' => $name,
            'model' => $model,
        ]);

        return redirect()->route('guide-languages.index')->with('success', 'Language added successfully.');
    }

    public function edit(Languages $guide_language)
    {
        if (!hasPermission('settings') && !hasPermission('edit settings')) {
            abort(403, 'You do not have permission to access this page.');
        }

        return view('guide-languages.edit', ['language' => $guide_language]);
    }

    public function update(Request $request, Languages $guide_language)
    {
        if (!hasPermission('settings') && !hasPermission('edit settings')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);
        $model = $validated['model'] !== null ? trim($validated['model']) : null;

        $duplicate = Languages::query()
            ->whereNull('deleted_at')
            ->where('id', '!=', $guide_language->id)
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['name' => 'This language already exists.'])
                ->withInput();
        }

        $guide_language->update([
            'name' => $name,
            'model' => $model,
        ]);

        return redirect()->route('guide-languages.index')->with('success', 'Language updated successfully.');
    }

    public function destroy(Languages $guide_language)
    {
        if (!hasPermission('settings') && !hasPermission('edit settings')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $guide_language->delete();

        return redirect()->route('guide-languages.index')->with('success', 'Language deleted successfully.');
    }
}

