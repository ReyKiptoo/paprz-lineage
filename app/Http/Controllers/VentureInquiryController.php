<?php

namespace App\Http\Controllers;

use App\Services\PaprzKnowledgeService;
use App\Services\VentureGeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentureInquiryController extends Controller
{
    public function __construct(
        protected VentureGeminiService $geminiService,
        protected PaprzKnowledgeService $knowledgeService
    ) {}

    /**
     * Render the Paprz Lineage Startup Inquiry Canvas.
     */
    public function index(): View
    {
        $foundations = $this->knowledgeService->getFoundations();
        $starters = $this->knowledgeService->getStarters();

        return view('inquiry.index', [
            'foundations' => $foundations,
            'starters' => $starters,
            'hasServerKey' => ! empty(config('services.gemini.key')),
            'serverModel' => config('services.gemini.model', 'gemini-2.5-flash'),
        ]);
    }

    /**
     * Forge a new 5-facet startup building block using Gemini AI or heuristic fallback.
     */
    public function forge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
            'priors' => 'nullable|array',
            'parent_id' => 'nullable|string|max:100',
        ]);

        $userApiKey = $request->header('X-Gemini-Key') ?: $request->input('api_key');

        $block = $this->geminiService->forgeInquiry(
            prompt: $validated['prompt'],
            priors: $validated['priors'] ?? [],
            parentBlockId: $validated['parent_id'] ?? null,
            userApiKey: $userApiKey
        );

        return response()->json([
            'status' => 'success',
            'block' => $block,
        ]);
    }

    /**
     * Challenge an established business or technical foundation.
     */
    public function challenge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'block' => 'required|array',
            'counter_evidence' => 'required|string|max:2000',
        ]);

        $userApiKey = $request->header('X-Gemini-Key') ?: $request->input('api_key');

        $evaluation = $this->geminiService->challengeBlock(
            blockData: $validated['block'],
            counterEvidence: $validated['counter_evidence'],
            userApiKey: $userApiKey
        );

        return response()->json([
            'status' => 'success',
            'evaluation' => $evaluation,
        ]);
    }
}
