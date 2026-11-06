<?php

namespace App\Http\Controllers;

use App\Models\AiKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiConfigController extends Controller
{
    private const ALLOWED_ROLE_IDS = [1];

    private function authorizeAccess(): void
    {
        if (!in_array((int) Auth::user()->role_id, self::ALLOWED_ROLE_IDS, true)) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    private function categoryRule(): string
    {
        return 'in:' . implode(',', array_keys(AiKeyword::CATEGORIES));
    }

    private function parseKeywords(string $input): array
    {
        return AiKeyword::parseKeywordLine($input);
    }

    private function keywordsToLine(string $input): string
    {
        return AiKeyword::keywordsToLine($input);
    }

    private function lineToTextarea(string $line): string
    {
        return implode("\n", $this->parseKeywords($line));
    }

    public function index()
    {
        $this->authorizeAccess();

        $aiKeywords = AiKeyword::query()
            ->orderBy('category')
            ->get();

        $categories = AiKeyword::CATEGORIES;

        return view('ai-keywords.index', compact('aiKeywords', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'keywords' => ['required', 'string'],
            'category' => ['required', 'string', $this->categoryRule()],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $keywordsLine = $this->keywordsToLine($validated['keywords']);
        $category = $validated['category'];

        if ($keywordsLine === '') {
            return back()
                ->withErrors(['keywords' => 'Please enter at least one keyword.'])
                ->withInput();
        }

        $existing = AiKeyword::withTrashed()
            ->where('category', $category)
            ->first();

        if ($existing && !$existing->trashed()) {
            return back()
                ->withErrors(['category' => 'Keywords for this category already exist. Please edit the existing entry.'])
                ->withInput();
        }

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update([
                'keyword' => $keywordsLine,
                'status' => $validated['status'],
            ]);

            return redirect()->route('ai-key-words.index')->with('success', 'AI keywords restored successfully.');
        }

        AiKeyword::create([
            'keyword' => $keywordsLine,
            'category' => $category,
            'status' => $validated['status'],
        ]);

        return redirect()->route('ai-key-words.index')->with('success', 'AI keywords added successfully.');
    }

    public function edit(AiKeyword $ai_keyword)
    {
        $this->authorizeAccess();

        $categories = AiKeyword::CATEGORIES;

        return view('ai-keywords.edit', [
            'aiKeyword' => $ai_keyword,
            'categories' => $categories,
            'keywordsText' => $this->lineToTextarea($ai_keyword->keyword),
        ]);
    }

    public function update(Request $request, AiKeyword $ai_keyword)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'keywords' => ['required', 'string'],
            'category' => ['required', 'string', $this->categoryRule()],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $keywordsLine = $this->keywordsToLine($validated['keywords']);
        $category = $validated['category'];

        if ($keywordsLine === '') {
            return back()
                ->withErrors(['keywords' => 'Please enter at least one keyword.'])
                ->withInput();
        }

        $duplicate = AiKeyword::query()
            ->whereNull('deleted_at')
            ->where('id', '!=', $ai_keyword->id)
            ->where('category', $category)
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['category' => 'Keywords for this category already exist.'])
                ->withInput();
        }

        $ai_keyword->update([
            'keyword' => $keywordsLine,
            'category' => $category,
            'status' => $validated['status'],
        ]);

        return redirect()->route('ai-key-words.index')->with('success', 'AI keywords updated successfully.');
    }

    public function destroy(AiKeyword $ai_keyword)
    {
        $this->authorizeAccess();

        $ai_keyword->delete();

        return redirect()->route('ai-key-words.index')->with('success', 'AI keywords deleted successfully.');
    }
}
