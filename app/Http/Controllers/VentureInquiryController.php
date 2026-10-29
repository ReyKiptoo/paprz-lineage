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
            'branch_type' => 'nullable|string|max:50',
            'connection_reason' => 'nullable|string|max:255',
        ]);

        $userApiKey = $request->header('X-Gemini-Key') ?: $request->input('api_key');

        $block = $this->geminiService->forgeInquiry(
            prompt: $validated['prompt'],
            priors: $validated['priors'] ?? [],
            parentBlockId: $validated['parent_id'] ?? null,
            userApiKey: $userApiKey,
            branchType: $validated['branch_type'] ?? null,
            connectionReason: $validated['connection_reason'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'block' => $block,
        ]);
    }

    /**
     * Consolidate a cluster of proven blocks into one overarching Macro Truth.
     */
    public function synthesizeMacro(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'blocks' => 'required|array|min:2',
        ]);

        $userApiKey = $request->header('X-Gemini-Key') ?: $request->input('api_key');

        $macro = $this->geminiService->synthesizeMacroCluster(
            blocks: $validated['blocks'],
            userApiKey: $userApiKey
        );

        return response()->json([
            'status' => 'success',
            'macro' => $macro,
        ]);
    }

    /**
     * Permanently save the verified lineage tree to server storage (paprz_foundations.json).
     */
    public function saveBedrock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'blocks' => 'required|array|min:1',
        ]);

        $path = database_path('data/paprz_foundations.json');
        file_put_contents($path, json_encode($validated['blocks'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'status' => 'success',
            'message' => 'Lineage tree saved permanently to bedrock foundations.',
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
