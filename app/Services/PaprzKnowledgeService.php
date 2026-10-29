<?php

namespace App\Services;

class PaprzKnowledgeService
{
    /**
     * Get the foundational building blocks for Paprz.
     */
    public function getFoundations(): array
    {
        $path = database_path('data/paprz_foundations.json');
        if (file_exists($path)) {
            $data = json_decode((string) file_get_contents($path), true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * Quick starter prompts for founder hypothesis testing.
     */
    public function getStarters(): array
    {
        return [
            [
                'id' => 'starter_pmf',
                'title' => 'Liquidity vs Paywall',
                'category' => 'Product-Market Fit',
                'question' => 'Will keeping question papers 100% free while charging KES 20-50 for step-by-step marking schemes maximize word-of-mouth growth?',
                'hint' => 'Test the dual-access engine.',
                'color' => '#f59e0b',
            ],
            [
                'id' => 'starter_scan',
                'title' => 'Phone Photo Normalization',
                'category' => 'Image Engineering',
                'question' => 'Can an automated bilevel CCITT compression pipeline turn crooked 15MB phone photos into 300KB clean A4 documents?',
                'hint' => 'Eliminate the photocopy shop barrier.',
                'color' => '#38bdf8',
            ],
            [
                'id' => 'starter_piracy',
                'title' => 'Anti-Piracy Psychological Gate',
                'category' => 'Security & Defensibility',
                'question' => 'Does dynamic student email and IP watermarking on an HTML5 canvas deter WhatsApp leaks more effectively than DRM?',
                'hint' => 'Protect solution creator revenue.',
                'color' => '#ec4899',
            ],
            [
                'id' => 'starter_cloud',
                'title' => 'Zero-Cost Storage Scaling',
                'category' => 'Infrastructure',
                'question' => 'Can archiving raw originals to Google Drive while serving compressed derivatives from local disk keep VPS costs flat as catalog 10× grows?',
                'hint' => 'Decouple server costs from content volume.',
                'color' => '#10b981',
            ],
            [
                'id' => 'starter_creators',
                'title' => 'Student Creator Marketplace',
                'category' => 'Network Effects',
                'question' => 'Can verified top-scoring students be incentivized to author accurate step-by-step solutions for a 60% revenue share via M-Pesa?',
                'hint' => 'Crowdsource verified solution authoring.',
                'color' => '#8b5cf6',
            ],
        ];
    }
}
