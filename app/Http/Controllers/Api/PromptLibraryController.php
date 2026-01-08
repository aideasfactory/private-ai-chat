<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovedPrompt;
use App\Models\PromptCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromptLibraryController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = PromptCategory::orderBy('sort_order')
            ->orderBy('name')
            ->with(['activePrompts' => function ($query) {
                $query->orderBy('title');
            }])
            ->get();

        return response()->json($categories);
    }

    public function prompts(Request $request): JsonResponse
    {
        $query = ApprovedPrompt::with('category')->active();

        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $prompts = $query->orderBy('title')->get();

        return response()->json($prompts);
    }

    public function usePrompt(ApprovedPrompt $prompt): JsonResponse
    {
        if (!$prompt->is_active) {
            return response()->json(['error' => 'Prompt is not active'], 422);
        }

        $prompt->incrementUsage();

        return response()->json([
            'message' => 'Prompt usage recorded',
            'prompt' => $prompt->load('category')
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:prompt_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true; // User prompts are automatically active

        $prompt = ApprovedPrompt::create($validated);

        return response()->json([
            'message' => 'Prompt created successfully',
            'prompt' => $prompt->load('category')
        ], 201);
    }

    public function update(Request $request, ApprovedPrompt $prompt): JsonResponse
    {
        // Only allow users to edit their own prompts
        if ($prompt->created_by !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:prompt_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $prompt->update($validated);

        return response()->json([
            'message' => 'Prompt updated successfully',
            'prompt' => $prompt->load('category')
        ]);
    }

    public function destroy(ApprovedPrompt $prompt): JsonResponse
    {
        // Only allow users to delete their own prompts
        if ($prompt->created_by !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $prompt->delete();

        return response()->json([
            'message' => 'Prompt deleted successfully'
        ]);
    }

    public function myPrompts(Request $request): JsonResponse
    {
        $query = ApprovedPrompt::with('category')
            ->where('created_by', auth()->id());

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $prompts = $query->orderBy('title')->get();

        return response()->json($prompts);
    }
}
