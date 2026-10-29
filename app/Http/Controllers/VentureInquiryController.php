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
     * Socratic venture interrogation dialogue with full canvas context.
     */
    public function dialogue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => 'required|array',
            'canvas_blocks' => 'required|array',
        ]);

        $userApiKey = $request->header('X-Gemini-Key') ?: $request->input('api_key');

        $result = $this->geminiService->interrogateDialogue(
            messages: $validated['messages'],
            canvasBlocks: $validated['canvas_blocks'],
            userApiKey: $userApiKey
        );

        return response()->json([
            'status' => 'success',
            'reply' => $result['reply'] ?? '',
            'proposed_block' => $result['proposed_block'] ?? null,
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
     * Permanently save the verified lineage tree to server storage (paprz_foundations.json)
     * and automatically commit to Git.
     */
    public function saveBedrock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'blocks' => 'required|array|min:1',
        ]);

        $path = database_path('data/paprz_foundations.json');
        file_put_contents($path, json_encode($validated['blocks'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Automatically create a Git commit and push to remote
        $baseDir = escapeshellarg(base_path());
        $dateStr = now()->format('Y-m-d H:i:s');
        $commitMsg = escapeshellarg("feat(bedrock): save lineage foundations [{$dateStr}]");
        
        $gitOutput = shell_exec("cd {$baseDir} && git add database/data/paprz_foundations.json && git commit -m {$commitMsg} 2>&1");
        $pushOutput = shell_exec("cd {$baseDir} && git push origin main 2>&1");

        return response()->json([
            'status' => 'success',
            'message' => 'Lineage tree saved and committed to Git (main).',
            'git_output' => $gitOutput,
            'push_output' => $pushOutput,
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
