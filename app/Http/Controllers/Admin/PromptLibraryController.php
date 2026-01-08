<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovedPrompt;
use App\Models\PromptCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromptLibraryController extends Controller
{
    public function index(): Response
    {
        $prompts = ApprovedPrompt::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = PromptCategory::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/PromptLibrary/Index', [
            'prompts' => $prompts,
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        $categories = PromptCategory::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/PromptLibrary/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:prompt_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        ApprovedPrompt::create($validated);

        return redirect()->route('admin.prompt-library.index')
            ->with('success', 'Prompt created successfully.');
    }

    public function edit(ApprovedPrompt $prompt): Response
    {
        $categories = PromptCategory::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/PromptLibrary/Edit', [
            'prompt' => $prompt->load('category'),
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, ApprovedPrompt $prompt): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:prompt_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $prompt->update($validated);

        return redirect()->route('admin.prompt-library.index')
            ->with('success', 'Prompt updated successfully.');
    }

    public function destroy(ApprovedPrompt $prompt): RedirectResponse
    {
        $prompt->delete();

        return redirect()->route('admin.prompt-library.index')
            ->with('success', 'Prompt deleted successfully.');
    }

    public function categories(): Response
    {
        $categories = PromptCategory::withCount('approvedPrompts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/PromptLibrary/Categories', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'integer',
        ]);

        PromptCategory::create($validated);

        return redirect()->route('admin.prompt-library.categories')
            ->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, PromptCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'integer',
        ]);

        $category->update($validated);

        return redirect()->route('admin.prompt-library.categories')
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(PromptCategory $category): RedirectResponse
    {
        if ($category->approvedPrompts()->count() > 0) {
            return redirect()->route('admin.prompt-library.categories')
                ->with('error', 'Cannot delete category that contains prompts.');
        }

        $category->delete();

        return redirect()->route('admin.prompt-library.categories')
            ->with('success', 'Category deleted successfully.');
    }
}
