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
        $this->defaultModel = config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Forge a Startup / Venture Inquiry Building Block from a hypothesis and prior facts.
     */
    public function forgeInquiry(string $prompt, array $priors = [], ?string $parentBlockId = null, ?string $userApiKey = null): array
    {
        $apiKey = $userApiKey ?: $this->defaultApiKey;

        if ($apiKey) {
            try {
                $block = $this->callGeminiForVentureBlock($prompt, $priors, $apiKey);
                if ($block) {
                    $block['id'] = 'blk_v_' . Str::random(10);
                    $block['parent_id'] = $parentBlockId;
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
        $block['source'] = $apiKey ? 'heuristic_fallback' : 'heuristic';
        return $block;
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
