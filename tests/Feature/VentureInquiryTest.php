<?php

namespace Tests\Feature;

use Tests\TestCase;

class VentureInquiryTest extends TestCase
{
    /**
     * Test the main Paprz Lineage canvas loads with pre-seeded bedrock blocks.
     */
    public function test_inquiry_canvas_page_loads_with_foundations(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Paprz Lineage');
        $response->assertSee('Startup Thought Engine');
        $response->assertSee('What Startup Hypothesis Shall We Test?');
        $response->assertSee('The Liquidity \u0026 Distribution Bottleneck', false);
    }

    /**
     * Test forging a startup hypothesis block returns all 5 facets.
     */
    public function test_forge_startup_hypothesis_returns_all_five_facets(): void
    {
        $response = $this->postJson('/api/inquiry/forge', [
            'prompt' => 'Will offering a KES 200 semester pass increase total revenue compared to only selling KES 20 single solutions?',
            'priors' => [
                'Kenyan university students are price-sensitive but experience frequent revision panic.',
                'M-Pesa transaction fees make multiple micro-purchases less attractive than one bundle.'
            ],
            'parent_id' => 'blk_paprz_05',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $block = $response->json('block');
        $this->assertNotNull($block);
        $this->assertArrayHasKey('id', $block);
        $this->assertArrayHasKey('title', $block);
        $this->assertArrayHasKey('hypothesis', $block);
        $this->assertArrayHasKey('things_we_know', $block);
        $this->assertArrayHasKey('experiment', $block);
        $this->assertArrayHasKey('results', $block);
        $this->assertArrayHasKey('next_paths', $block);

        // Verify the 5 facets
        $this->assertNotEmpty($block['hypothesis']);
        $this->assertIsArray($block['things_we_know']);
        $this->assertArrayHasKey('apparatus', $block['experiment']);
        $this->assertArrayHasKey('method', $block['experiment']);
        $this->assertArrayHasKey('observation', $block['results']);
        $this->assertArrayHasKey('lesson', $block['results']);
        $this->assertArrayHasKey('significance', $block['results']);

        // Verify 3 next paths
        $this->assertCount(3, $block['next_paths']);
        $types = array_column($block['next_paths'], 'type');
        $this->assertContains('validate', $types);
        $this->assertContains('horizon', $types);
        $this->assertContains('paradox', $types);
    }

    /**
     * Test challenging a startup premise.
     */
    public function test_challenge_startup_premise_evaluates_friction(): void
    {
        $block = [
            'title' => 'Dual-Access Monetization via M-Pesa Micropayments',
            'hypothesis' => 'Students will pay KES 20 via M-Pesa for worked solutions.',
            'things_we_know' => ['Students carry liquid M-Pesa balances.'],
            'results' => [
                'lesson' => 'Impulse pricing converts student revision anxiety into sustainable revenue.'
            ]
        ];

        $response = $this->postJson('/api/inquiry/challenge', [
            'block' => $block,
            'counter_evidence' => 'Airtel Money and Telkom users cannot trigger Safaricom STK push prompts, losing 15% of the student market.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $evaluation = $response->json('evaluation');
        $this->assertNotNull($evaluation);
        $this->assertArrayHasKey('verdict', $evaluation);
        $this->assertArrayHasKey('analysis', $evaluation);
    }
}
