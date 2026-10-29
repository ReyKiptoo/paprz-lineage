<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VentureGeminiService
{
    protected ?string $defaultApiKey;
    protected string $defaultModel;

    public function __construct()
    {
        $this->defaultApiKey = config('services.gemini.key');
        $this->defaultModel = config('services.gemini.model', 'gemini-3.8-flash');
    }

    /**
     * Forge a Startup / Venture Inquiry Building Block from a hypothesis and prior facts.
     */
    public function forgeInquiry(
        string $prompt,
        array $priors = [],
        ?string $parentBlockId = null,
        ?string $userApiKey = null,
        ?string $branchType = null,
        ?string $connectionReason = null
    ): array
    {
        $apiKey = $userApiKey ?: $this->defaultApiKey;

        if ($apiKey) {
            try {
                $block = $this->callGeminiForVentureBlock($prompt, $priors, $apiKey);
                if ($block) {
                    $block['id'] = 'blk_v_' . Str::random(10);
                    $block['parent_id'] = $parentBlockId;
                    $block['branch_type'] = $branchType;
                    $block['connection_reason'] = $connectionReason;
                    $block['created_at'] = now()->toIso8601String();
                    $block['status'] = 'draft';
                    $block['sealed'] = false;
                    $block['challenged'] = false;
                    $block['challenge_notes'] = null;
                    $block['source'] = 'gemini';
                    return $block;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini venture synthesis failed, falling back: ' . $e->getMessage());
            }
        }

        $block = $this->heuristicFallback($prompt, $priors, $parentBlockId);
        $block['branch_type'] = $branchType;
        $block['connection_reason'] = $connectionReason;
        $block['source'] = $apiKey ? 'heuristic_fallback' : 'heuristic';
        return $block;
    }

    /**
     * Test connection to Gemini API with a fast ping request.
     */
    public function testConnection(?string $userApiKey = null): array
    {
        $defaultKey = config('services.gemini.key');
        $apiKey = $userApiKey !== null ? trim($userApiKey) : ($this->defaultApiKey ?: $defaultKey);

        if (empty($apiKey)) {
            return [
                'status' => 'error',
                'code' => 401,
                'message' => 'No API key provided. Please paste your Google AI Studio API key.',
            ];
        }

        $startTime = microtime(true);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->defaultModel}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout(10)->post($url, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => 'Ping. Respond with exactly the word "PONG".']
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.0,
                    'maxOutputTokens' => 10,
                ],
            ]);

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $replyText = trim($response->json('candidates.0.content.parts.0.text') ?? '');
                return [
                    'status' => 'success',
                    'model' => $this->defaultModel,
                    'latency_ms' => $latencyMs,
                    'reply' => $replyText,
                    'message' => "Connected successfully to Google Gemini ({$this->defaultModel}) in {$latencyMs}ms.",
                ];
            }

            $errBody = $response->json();
            $code = $response->status();
            $msg = $errBody['error']['message'] ?? 'API request was rejected by Google Gemini.';

            return [
                'status' => 'error',
                'code' => $code,
                'message' => "Google API Error (HTTP {$code}): {$msg}",
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'code' => 500,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Interrogate the founder in a multi-turn Socratic dialogue with full canvas context.
     */
    public function interrogateDialogue(array $messages, array $canvasBlocks, ?string $userApiKey = null): array
    {
        $apiKey = $userApiKey ?: $this->defaultApiKey;

        if ($apiKey) {
            try {
                $result = $this->callGeminiForDialogue($messages, $canvasBlocks, $apiKey);
                if ($result) {
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini interrogation dialogue failed: ' . $e->getMessage());
                return [
                    'error' => true,
                    'status_code' => 500,
                    'reply' => "⚠️ **Gemini Connection Error**\n\n" . $e->getMessage() . "\n\nPlease check your internet connection or verify your API key in 'Connect Gemini'.",
                    'proposed_block' => null,
                ];
            }
        }

        return $this->heuristicDialogueFallback($messages, $canvasBlocks);
    }

    /**
     * Call Gemini with canvas context and venture blueprint for interactive interrogation.
     */
    protected function callGeminiForDialogue(array $messages, array $canvasBlocks, string $apiKey): ?array
    {
        $manifestoPath = base_path('VENTURE_MANIFESTO.md');
        $manifesto = file_exists($manifestoPath) ? file_get_contents($manifestoPath) : 'Academic past exam paper archive for university students.';

        $blockSummaries = array_map(function ($b) {
            $parent = $b['parent_id'] ? "(Child of {$b['parent_id']})" : "(Root)";
            $status = $b['sealed'] ? "Proven" : "Draft";
            return "Block [{$b['id']}] {$parent} - {$b['title']} [{$status}]\n"
                . "  Hypothesis: {$b['hypothesis']}\n"
                . "  Lesson: " . ($b['results']['lesson'] ?? 'Under test') . "\n";
        }, $canvasBlocks);

        $canvasContext = count($blockSummaries) > 0 
            ? "CURRENT CANVAS HYPOTHESIS TREE:\n" . implode("\n", $blockSummaries)
            : "Canvas is currently empty.";

        $systemInstruction = <<<EOT
You are the First-Principles Startup Epistemology Co-Founder for 'Paprz'.
You are sitting across the table from the founder in an intense, Socratic venture interrogation dialogue.

VENTURE MASTER MANIFESTO:
{$manifesto}

{$canvasContext}

Your Responsibilities:
1. Speak as a thoughtful, sharp, and supportive technical co-founder. Use calm, specific, concise language. No hype.
2. Continually reference their actual canvas blocks and the Paprz blueprint (JKUAT engineering focus, KES 3 CATs / KES 5 Exams, web discovery vs native mobile reader with FLAG_SECURE).
3. Point out friction: Safaricom M-Pesa Daraja STK push fee overhead vs in-app prepaid tokens, class rep incentive alignment, hostel Wi-Fi drops, student panic timelines.
4. When the founder clarifies a specific decision or asks to build the next card, synthesize and PROPOSE a structured 5-facet building block! The block can either connect to an existing parent or be an independent root.

Always return strictly valid JSON matching this schema:
{
  "reply": "Your conversational dialogue, interrogation feedback, and next probing question.",
  "proposed_block": null or {
    "title": "Short evocative title (3-6 words)",
    "category": "e.g. Unit Economics, Architecture, Distribution, Defensibility",
    "hypothesis": "1-2 sentences stating the falsifiable assumption under test",
    "things_we_know": [
      "Prior fact 1",
      "Prior fact 2"
    ],
    "experiment": {
      "apparatus": "Software tools, prototype, payment gateway, cohort test",
      "method": "Step-by-step test procedure executed",
      "variable_tested": "The specific metric isolated"
    },
    "results": {
      "observation": "What happens or is observed in testing",
      "lesson": "Universal product law or business rule deduced",
      "significance": "Why this creates a competitive moat"
    },
    "next_paths": [
      { "type": "validate", "label": "Validate & Deepen", "question": "...", "teaser": "..." },
      { "type": "horizon", "label": "New Horizon", "question": "...", "teaser": "..." },
      { "type": "paradox", "label": "Wait, how is this possible?!", "question": "...", "teaser": "..." }
    ],
    "parent_id": "blk_id_to_connect_to or null if independent",
    "branch_type": "validate" | "horizon" | "paradox" | "genesis",
    "connection_reason": "Short reason explaining why this block connects to the parent"
  }
}
EOT;

        $contents = [];
        foreach ($messages as $msg) {
            $role = in_array($msg['role'] ?? '', ['founder', 'user']) ? 'user' : 'model';
            $text = trim($msg['content'] ?? '');
            if ($text !== '') {
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }

        if (empty($contents)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => 'Hello. Let us review our current venture blocks.']],
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->defaultModel}:generateContent?key={$apiKey}";

        $response = Http::timeout(25)->post($url, [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if (! $response->successful()) {
            $status = $response->status();
            $errBody = $response->json();
            $msg = $errBody['error']['message'] ?? 'Gemini API call failed with status ' . $status;
            Log::warning("Gemini API call failed with status: {$status} body: " . $response->body());

            return [
                'error' => true,
                'status_code' => $status,
                'reply' => "⚠️ **Google Gemini API Error (HTTP {$status})**\n\n{$msg}\n\n*Please verify your API key and billing in 'Connect Gemini' or visit https://aistudio.google.com.*",
                'proposed_block' => null,
            ];
        }

        $parsed = $this->extractJsonFromResponse($response->json());
        if ($parsed && isset($parsed['proposed_block']) && is_array($parsed['proposed_block'])) {
            $parsed['proposed_block']['id'] = 'blk_v_' . Str::random(10);
            $parsed['proposed_block']['status'] = 'draft';
            $parsed['proposed_block']['sealed'] = false;
            $parsed['proposed_block']['challenged'] = false;
            $parsed['proposed_block']['source'] = 'gemini';
            $parsed['proposed_block']['created_at'] = now()->toIso8601String();
        }

        if (! $parsed) {
            return [
                'error' => true,
                'reply' => "⚠️ Gemini responded, but the response could not be parsed as JSON. Please try again.",
                'proposed_block' => null,
            ];
        }

        $parsed['is_live_gemini'] = true;
        $parsed['model'] = $this->defaultModel;
        return $parsed;
    }

    /**
     * Offline heuristic dialogue fallback that actively guides the founder through next blocks.
     */
    protected function heuristicDialogueFallback(array $messages, array $canvasBlocks): array
    {
        $last = strtolower(end($messages)['content'] ?? '');

        // Founder asks if they are talking to Gemini or an LLM
        if (str_contains($last, 'talking to gemini') || str_contains($last, 'are you gemini') || str_contains($last, 'real llm') || str_contains($last, 'am i talking to') || str_contains($last, 'is this an ai')) {
            return [
                'reply' => "No, you are currently in **Offline Simulation Mode**.\n\nYour requests are being handled locally by the heuristic engine because no valid Gemini API key is verified.\n\nTo connect to live Google Gemini 1.5 Flash:\n1. Click **Connect Gemini** in the top navigation bar.\n2. Paste your Google AI Studio API key.\n3. Click **Test API Connection** to verify your key and account quota.\n4. Click **Save key**.",
                'proposed_block' => null,
                'is_offline' => true
            ];
        }

        // 1. Founder asks what is needed to make the next block or asks what to do
        if (str_contains($last, 'what do you need') || str_contains($last, 'next card') || str_contains($last, 'next block') || str_contains($last, 'how do we make')) {
            return [
                'reply' => "To construct our next building block (Block 04), we need to isolate the single most fragile assumption in our current chain:\n\n"
                    . "• In Block 02, we assumed students will pay KES 3 for CATs and KES 5 for Exams.\n"
                    . "• In Block 03, we assumed a native mobile app container prevents piracy via Android FLAG_SECURE.\n\n"
                    . "The immediate friction point is **Payment Friction vs PIN Fatigue**: If a student wants to unlock a KES 3 CAT, triggering a Safaricom M-Pesa Daraja STK push prompt for 3 shillings introduces carrier minimum fees and 15 seconds of friction.\n\n"
                    . "I have drafted a candidate hypothesis block for an **In-App Prepaid Micro-Wallet (e.g. deposit KES 20 once, tap once to unlock CATs)**. Review the proposal below and click 'Add to canvas 🧱' if you agree, or tell me if you'd rather test acquisition via class reps instead.",
                'proposed_block' => [
                    'id' => 'blk_v_' . Str::random(10),
                    'parent_id' => 'blk_paprz_02',
                    'branch_type' => 'validate',
                    'connection_reason' => 'Validate: Testing prepaid micro-token wallet to eliminate STK push fees on KES 3 CATs',
                    'title' => 'The Prepaid Token Micro-Wallet',
                    'category' => 'Unit Economics & UX',
                    'hypothesis' => 'Allowing students to pre-fund an in-app coin wallet (e.g. KES 20 for 20 tokens) via a single M-Pesa push eliminates multi-transaction fee overhead and accelerates impulse CAT unlocks to under 1 second.',
                    'things_we_know' => [
                        'Safaricom STK push prompts require network roundtrips and user PIN entry each time.',
                        'Students studying in groups want to unlock multiple CATs in quick succession.',
                        'Transaction fees on multiple KES 3 charges erode developer revenue.'
                    ],
                    'experiment' => [
                        'apparatus' => 'Local coin ledger SQLite database + single KES 20 M-Pesa Daraja top-up trigger.',
                        'method' => 'A/B test direct KES 5 STK push per paper vs 1-tap token deduction from prepaid wallet.',
                        'variable_tested' => 'Repeat unlock velocity and payment completion rate.'
                    ],
                    'results' => [
                        'observation' => 'Students who top up once unlock an average of 4.2 papers in a single study session.',
                        'lesson' => 'Prepaid digital credits reduce the psychological friction of multiple micro-purchases to zero.',
                        'significance' => 'Protects gross margins while creating in-app balance lock-in.'
                    ],
                    'next_paths' => [
                        ['type' => 'validate', 'label' => 'Validate & Deepen', 'question' => 'What is the optimal minimum top-up bundle (KES 20 vs KES 50)?', 'teaser' => 'Test top-up amount sensitivity.'],
                        ['type' => 'horizon', 'label' => 'New Horizon', 'question' => 'Can students earn free tokens by reporting typos or uploading missing CATs?', 'teaser' => 'Explore token rewards for community contribution.'],
                        ['type' => 'paradox', 'label' => 'Wait, how is this possible?!', 'question' => 'Will students hesitate to deposit KES 20 upfront if they only need 1 specific paper right now?', 'teaser' => 'Analyze upfront deposit hesitation.']
                    ],
                    'status' => 'draft',
                    'sealed' => false,
                    'challenged' => false,
                    'source' => 'interrogation',
                    'created_at' => now()->toIso8601String()
                ]
            ];
        }

        // 2. Founder talks about payments, M-Pesa, coins, pricing, wallet
        if (str_contains($last, 'mpesa') || str_contains($last, 'm-pesa') || str_contains($last, 'coin') || str_contains($last, 'wallet') || str_contains($last, 'pricing') || str_contains($last, 'pay')) {
            return [
                'reply' => "If we charge KES 3 for CATs and KES 5 for Exams, single M-Pesa STK push calls will face minimum fee friction and student PIN fatigue. An in-app prepaid coin wallet (e.g. deposit KES 20 once for 20 Paprz Coins, spend 3 coins per CAT with instant 1-tap unlock) would decouple transaction fees from single-paper unlocks.\n\nI have generated this candidate block below. Tap 'Add to canvas 🧱' to attach it to Block 02.",
                'proposed_block' => [
                    'id' => 'blk_v_' . Str::random(10),
                    'parent_id' => 'blk_paprz_02',
                    'branch_type' => 'validate',
                    'connection_reason' => 'Validate: Prepaid micro-coin wallet to eliminate M-Pesa STK push PIN fatigue',
                    'title' => 'The Prepaid Token Micro-Wallet',
                    'category' => 'Payment Infrastructure',
                    'hypothesis' => 'Allowing students to pre-fund an in-app coin wallet (e.g. KES 20 for 20 tokens) via a single M-Pesa push eliminates multi-transaction fee overhead and accelerates impulse CAT unlocks to under 1 second.',
                    'things_we_know' => [
                        'Safaricom STK push prompts require network roundtrips and user PIN entry each time.',
                        'Students studying in groups want to unlock multiple CATs in quick succession.',
                        'Transaction fees on multiple KES 3 charges erode developer revenue.'
                    ],
                    'experiment' => [
                        'apparatus' => 'Local coin ledger SQLite database + single KES 20 M-Pesa Daraja top-up trigger.',
                        'method' => 'A/B test direct KES 5 STK push per paper vs 1-tap token deduction from prepaid wallet.',
                        'variable_tested' => 'Repeat unlock velocity and payment completion rate.'
                    ],
                    'results' => [
                        'observation' => 'Students who top up once unlock an average of 4.2 papers in a single study session.',
                        'lesson' => 'Prepaid digital credits reduce the psychological friction of multiple micro-purchases to zero.',
                        'significance' => 'Protects gross margins while creating in-app balance lock-in.'
                    ],
                    'next_paths' => [
                        ['type' => 'validate', 'label' => 'Validate & Deepen', 'question' => 'What is the optimal minimum top-up bundle (KES 20 vs KES 50)?', 'teaser' => 'Test top-up amount sensitivity.'],
                        ['type' => 'horizon', 'label' => 'New Horizon', 'question' => 'Can students earn free tokens by reporting typos or uploading missing CATs?', 'teaser' => 'Explore token rewards for community contribution.'],
                        ['type' => 'paradox', 'label' => 'Wait, how is this possible?!', 'question' => 'Will students hesitate to deposit KES 20 upfront if they only need 1 specific paper right now?', 'teaser' => 'Analyze upfront deposit hesitation.']
                    ],
                    'status' => 'draft',
                    'sealed' => false,
                    'challenged' => false,
                    'source' => 'interrogation',
                    'created_at' => now()->toIso8601String()
                ]
            ];
        }

        // 3. Founder discusses paper acquisition, class reps, or supply
        if (str_contains($last, 'rep') || str_contains($last, 'lecturer') || str_contains($last, 'supply') || str_contains($last, 'acquire') || str_contains($last, 'collect') || str_contains($last, 'upload')) {
            return [
                'reply' => "Acquisition of pristine exam papers is our supply-side bottleneck. If we rely on random student uploads, 80% will be blurry camera shots with poor lighting.\n\nShould we incentivize Department Class Representatives with a KES 50 bounty or revenue share on every paper they scan with our mobile document scanner tool?",
                'proposed_block' => [
                    'id' => 'blk_v_' . Str::random(10),
                    'parent_id' => 'blk_paprz_01',
                    'branch_type' => 'horizon',
                    'connection_reason' => 'Horizon: Testing class representative supply bounty for verified exam papers',
                    'title' => 'Class Representative Supply Bounty',
                    'category' => 'Supply Acquisition',
                    'hypothesis' => 'Contracting academic class representatives with a KES 50 bounty per verified, unblurred exam paper yields a 90% complete 5-year department catalog within 7 days.',
                    'things_we_know' => [
                        'Class reps are the gatekeepers of lecturer announcements and past exam collections.',
                        'Class reps are trusted peer figures with access to department printing offices.',
                        'Students refuse to pay for incomplete catalogs missing their specific year or semester.'
                    ],
                    'experiment' => [
                        'apparatus' => 'WhatsApp direct outreach script + KES 50 M-Pesa airtime/cash bounty per cataloged paper.',
                        'method' => 'Recruit 5 JKUAT engineering class reps to source 2018–2024 past papers for 10 core units.',
                        'variable_tested' => 'Paper catalog completeness percentage and ingestion cost per unit.'
                    ],
                    'results' => [
                        'observation' => '4 out of 5 class reps delivered complete collections within 48 hours for immediate M-Pesa payment.',
                        'lesson' => 'Direct financial micro-incentives to trusted student nodes solve the cold-start catalog problem at under KES 500 per department.',
                        'significance' => 'Establishes a defacto proprietary catalog moat before competitors can aggregate papers.'
                    ],
                    'next_paths' => [
                        ['type' => 'validate', 'label' => 'Validate & Deepen', 'question' => 'Can we turn the bounty into a 20% passive revenue share on unlocks?', 'teaser' => 'Align class rep incentives with student adoption.'],
                        ['type' => 'horizon', 'label' => 'New Horizon', 'question' => 'Can we give reps automated document scanning & perspective correction inside the app?', 'teaser' => 'In-app OCR and scanner tooling.'],
                        ['type' => 'paradox', 'label' => 'Wait, how is this possible?!', 'question' => 'Will lecturers retaliate against class reps who digitize their department tests?', 'teaser' => 'Legal and institutional risk review.']
                    ],
                    'status' => 'draft',
                    'sealed' => false,
                    'challenged' => false,
                    'source' => 'interrogation',
                    'created_at' => now()->toIso8601String()
                ]
            ];
        }

        // 4. Default conversational probe (dynamic, references context)
        $blockCount = count($canvasBlocks);
        return [
            'reply' => "Our canvas currently tracks {$blockCount} foundational hypotheses: distribution liquidity (Block 01), KES 3–5 pricing (Block 02), and anti-piracy mobile protection (Block 03).\n\nTo build the next card, tell me: Which area would you like to formulate next?\n1. **Prepaid Wallet Architecture**: Decoupling M-Pesa fees from KES 3 CAT unlocks.\n2. **Paper Supply Pipeline**: How we get clean 2018–2024 exam papers from JKUAT class reps.\n3. **Offline Caching**: Allowing hostel study when campus Wi-Fi drops.",
            'proposed_block' => null
        ];
    }

    /**
     * Synthesize multiple connected proven blocks into one overarching Macro Truth.
     */
    public function synthesizeMacroCluster(array $blocks, ?string $userApiKey = null): array
    {
        $apiKey = $userApiKey ?: $this->defaultApiKey;

        $blockSummaries = array_map(function ($b) {
            return "- [{$b['title']}]: Hypothesis: {$b['hypothesis']} | Deduced Principle: {$b['results']['lesson']}";
        }, $blocks);

        $prompt = "We have proven the following cluster of connected venture hypotheses:\n"
            . implode("\n", $blockSummaries) . "\n\n"
            . "Synthesize these into a single unified 'Macro Truth Milestone' representing the overarching business or technical law that has now been answered beyond doubt.";

        $macro = $this->forgeInquiry($prompt, [], null, $apiKey, 'macro', 'Consolidation of proven cluster');
        $macro['title'] = 'Macro Truth: ' . Str::headline(Str::limit($macro['title'], 32, ''));
        $macro['category'] = 'Master Truth Milestone';
        $macro['sealed'] = true;
        $macro['status'] = 'sealed';
        $macro['is_macro'] = true;
        $macro['consolidated_block_ids'] = array_column($blocks, 'id');

        return $macro;
    }

    /**
     * Evaluate market friction or counter-evidence against a sealed business foundation.
     */
    public function challengeBlock(array $blockData, string $counterEvidence, ?string $userApiKey = null): array
    {
        $apiKey = $userApiKey ?: $this->defaultApiKey;

        if ($apiKey) {
            try {
                $evaluation = $this->callGeminiForVentureChallenge($blockData, $counterEvidence, $apiKey);
                if ($evaluation) {
                    return $evaluation;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini venture challenge call failed: ' . $e->getMessage());
            }
        }

        return $this->heuristicChallengeFallback($blockData, $counterEvidence);
    }

    /**
     * Prompt Gemini to generate a 5-facet startup building block.
     */
    protected function callGeminiForVentureBlock(string $prompt, array $priors, string $apiKey): ?array
    {
        $systemInstruction = <<<EOT
You are an epistemological thought engine for 'Paprz Lineage', an interactive system where startup founders reconstruct business, technical, and product assumptions from first principles in modular building blocks.
The business being built is 'Paprz' (paprz-jkuat): an academic exam paper archive, revision, and solution marketplace for university students (e.g. at JKUAT in Kenya), featuring free question papers, paid step-by-step marking schemes via M-Pesa STK Push, phone-scan normalization (deskewing, bilevel CCITT compression), Google Drive storage tiering, and canvas anti-piracy watermarking.

Every startup building block must contain exactly 5 core facets:
1. Hypothesis: The core product, market, unit economics, or engineering assumption under test.
2. Things We Know (Priors): Validated facts accepted up to this point (market reality, student behavior, technical constraints).
3. The Experiment: The physical code prototype, A/B test, micro-survey, data pipeline run, or beta deployment used to test the hypothesis.
4. The Results: Empirical observations, deduced business/engineering principles, and venture significance.
5. Next Possible Paths: Exactly 3 divergent choices:
   - "validate": A question testing limits, edge cases, or unit economic boundaries.
   - "horizon": A new frontier question branching into adjacent product features or market expansion.
   - "paradox": A provocative anomaly question ("Wait, how is this possible?!") exposing hidden risks or flaws.

Return strictly valid JSON with no markdown wrapping.
JSON Schema:
{
  "title": "Short evocative title (3-6 words)",
  "category": "e.g. Product Discovery, Engineering Pipeline, Monetization, Anti-Piracy, Growth",
  "hypothesis": "1-2 sentences stating the clear business/technical hypothesis under test",
  "things_we_know": [
    "Prior fact 1",
    "Prior fact 2",
    "Prior fact 3"
  ],
  "experiment": {
    "apparatus": "Software tools, prototype, payment gateway, survey, or metric instrumentation",
    "method": "Step-by-step test procedure executed",
    "variable_tested": "The specific metric or behavior measured"
  },
  "results": {
    "observation": "What actually happened when tested with real users or code",
    "lesson": "The core product law or business principle deduced",
    "significance": "Why this secures our competitive moat or product-market fit"
  },
  "next_paths": [
    {
      "type": "validate",
      "label": "Validate & Deepen",
      "question": "Question testing scale, edge cases, or pricing limits",
      "teaser": "One short sentence previewing this path"
    },
    {
      "type": "horizon",
      "label": "New Horizon",
      "question": "New product capability or adjacent market opportunity",
      "teaser": "One short sentence previewing this path"
    },
    {
      "type": "paradox",
      "label": "Wait, how is this possible?!",
      "question": "Provocative paradox or risk question exposing an anomaly",
      "teaser": "One short sentence previewing this risk"
    }
  ]
}
EOT;

        $priorsContext = count($priors) > 0
            ? "Accepted Foundational Bedrock:\n- " . implode("\n- ", $priors)
            : "Genesis state (First foundational assumption). Establish baseline market priors.";

        $userContent = <<<EOT
Founder Hypothesis / Question: "{$prompt}"

Context of Established Foundations:
{$priorsContext}

Synthesize a complete 5-facet startup building block according to the JSON schema.
EOT;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->defaultModel}:generateContent?key={$apiKey}";

        $response = Http::timeout(25)->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemInstruction . "\n\n" . $userContent]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $this->extractJsonFromResponse($response->json());
    }

    /**
     * Call Gemini to evaluate a market challenge or friction against a sealed startup assumption.
     */
    protected function callGeminiForVentureChallenge(array $blockData, string $counterEvidence, string $apiKey): ?array
    {
        $prompt = <<<EOT
You are an epistemological startup arbiter for 'Paprz Lineage'.
A founder is challenging a previously sealed product or business premise with new market counter-evidence, metric failures, or user resistance.

Sealed Block:
Title: {$blockData['title']}
Hypothesis: {$blockData['hypothesis']}
Deduced Principle: {$blockData['results']['lesson']}

New Counter-Evidence / Market Friction:
"{$counterEvidence}"

Evaluate whether this challenge:
1. "fractured" (the business/tech premise fails or is invalid under these conditions; requires a strategic pivot)
2. "refined" (the premise holds with narrow boundary constraints or adjusted execution)
3. "upheld" (the objection is an outlier or does not invalidate the core unit economics/retention)

Return strictly valid JSON:
{
  "verdict": "fractured" | "refined" | "upheld",
  "verdict_title": "Short impact summary (e.g., 'Monetization Premise Fractured' or 'Boundary Constraint Added')",
  "analysis": "Critical venture analysis explaining the strategic implications",
  "fractured_assumptions": [
    "Specific premise that no longer holds true"
  ],
  "new_synthesis": "The refined, deeper truth or strategic pivot needed",
  "divergent_question": "The bold new hypothesis we must now test"
}
EOT;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->defaultModel}:generateContent?key={$apiKey}";
        $response = Http::timeout(25)->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $prompt]]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->successful()) {
            return $this->extractJsonFromResponse($response->json());
        }

        return null;
    }

    protected function extractJsonFromResponse(array $payload): ?array
    {
        $text = $payload['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (! $text) {
            return null;
        }

        $cleaned = trim($text);
        if (str_starts_with($cleaned, '```json')) {
            $cleaned = substr($cleaned, 7);
        } elseif (str_starts_with($cleaned, '```')) {
            $cleaned = substr($cleaned, 3);
        }
        if (str_ends_with($cleaned, '```')) {
            $cleaned = substr($cleaned, 0, -3);
        }

        $decoded = json_decode(trim($cleaned), true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Heuristic synthesis when offline or running without an API key.
     */
    public function heuristicFallback(string $prompt, array $priors = [], ?string $parentBlockId = null): array
    {
        $title = Str::headline(Str::limit($prompt, 38, ''));
        if (empty($title)) {
            $title = 'Venture First Principles';
        }

        return [
            'id' => 'blk_v_' . Str::random(10),
            'parent_id' => $parentBlockId,
            'created_at' => now()->toIso8601String(),
            'title' => $title,
            'category' => 'Product Hypothesis',
            'hypothesis' => "Hypothesis under test: " . rtrim($prompt, '?') . ", driving sustainable student retention and conversion.",
            'things_we_know' => count($priors) > 0 ? $priors : [
                'Kenyan university students revise under intense pre-exam time pressure.',
                'Mobile-first accessibility and zero data waste are non-negotiable on campus networks.',
                'Friction in payment or document readability destroys adoption instantly.'
            ],
            'experiment' => [
                'apparatus' => 'Targeted feature prototype, telemetry event tracking, and student cohort sample',
                'method' => 'Deploy scoped test to one active student course cohort and measure conversion vs baseline.',
                'variable_tested' => 'User activation, completion rate, or willingness-to-pay metric'
            ],
            'results' => [
                'observation' => 'Statistically significant improvement in student task completion observed.',
                'lesson' => 'Removing cognitive and financial friction accelerates spontaneous word-of-mouth adoption.',
                'significance' => 'Validates the assumption and unlocks the next strategic growth tier.'
            ],
            'next_paths' => [
                [
                    'type' => 'validate',
                    'label' => 'Validate & Deepen',
                    'question' => "What are the unit economics when this is scaled across all 5 JKUAT colleges?",
                    'teaser' => 'Test financial and operational scale limits.'
                ],
                [
                    'type' => 'horizon',
                    'label' => 'New Horizon',
                    'question' => "Can we expand this mechanism to other public and private universities in Kenya?",
                    'teaser' => 'Explore geographic and curriculum expansion.'
                ],
                [
                    'type' => 'paradox',
                    'label' => 'Wait, how is this possible?!',
                    'question' => "What happens if a major university implements an official free repository?",
                    'teaser' => 'Stress-test institutional competition risks.'
                ]
            ],
            'status' => 'draft',
            'sealed' => false,
            'challenged' => false,
            'challenge_notes' => null,
        ];
    }

    protected function heuristicChallengeFallback(array $blockData, string $counterEvidence): array
    {
        return [
            'verdict' => 'refined',
            'verdict_title' => 'Operational Boundary Exposed',
            'analysis' => "The counter-evidence reveals that the premise holds within typical exam season spikes, but encounters behavioral friction under this condition: \"{$counterEvidence}\".",
            'fractured_assumptions' => [
                'Assumption that student behavior is uniform across all academic semesters and departments.'
            ],
            'new_synthesis' => 'The core model remains viable, but requires differentiated product tiers to address this market friction.',
            'divergent_question' => 'How can we adapt our product workflow to convert this friction into an organic advantage?'
        ];
    }
}
