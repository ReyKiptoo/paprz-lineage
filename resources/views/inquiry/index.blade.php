<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Paprz Lineage | Startup Epistemology &amp; Hypothesis Thought Engine</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=newsreader:400,400i,500,600|inter:400,500,600|jetbrains-mono:400,500" rel="stylesheet" />

    <script>
        window.__PAPRZ_FOUNDATIONS__ = {!! json_encode($foundations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-[var(--text)] bg-[var(--bg)]"
      x-data="ventureCanvasApp(window.__PAPRZ_FOUNDATIONS__)">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 border-b border-[var(--border)] bg-[var(--bg)]/95 backdrop-blur-sm">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16 gap-4">
                
                <!-- Wordmark & Subtitle -->
                <div class="flex items-center gap-3">
                    <span class="font-serif text-lg font-normal tracking-tight text-[var(--text)]">
                        Paprz Lineage
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-[var(--radius-sm)] bg-[var(--bg-sunken)] text-[var(--text-muted)] border border-[var(--border)] font-sans">
                        Startup Thought Engine
                    </span>
                </div>

                <!-- Navigation Tools -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Proven Counter -->
                    <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-[var(--radius-sm)] bg-[var(--bg-sunken)] border border-[var(--border)] text-xs font-mono text-[var(--text-muted)]">
                        <span class="text-[var(--text)] font-medium" x-text="sealedCount"></span>
                        <span>proven</span>
                        <span class="text-[var(--text-faint)]">/</span>
                        <span x-text="totalCount + ' hypotheses'"></span>
                    </div>

                    <!-- Gemini API Status Button -->
                    <button @click="showKeyModal = true"
                            type="button"
                            class="btn-secondary !h-8 !px-2.5 !text-xs"
                            :title="apiKey ? 'Gemini API active' : 'Configure Gemini API key'">
                        <span class="w-1.5 h-1.5 rounded-full" 
                              :class="apiKey ? 'bg-[var(--success-text)]' : 'bg-[var(--warning-text)]'"></span>
                        <span x-text="apiKey ? 'Gemini connected' : 'Connect Gemini'"></span>
                    </button>

                    <!-- Theme Toggle -->
                    <button @click="toggleTheme()"
                            type="button"
                            class="btn-ghost !h-8 !px-2 !text-xs"
                            :title="theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'">
                        <template x-if="theme === 'dark'">
                            <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                        </template>
                        <template x-if="theme !== 'dark'">
                            <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                        </template>
                    </button>

                    <!-- Restore Bedrock -->
                    <button @click="resetToFoundations(window.__PAPRZ_FOUNDATIONS__)"
                            type="button"
                            class="btn-ghost !h-8 !px-2.5 !text-xs hidden md:inline-flex"
                            title="Reset to 6 bedrock foundations">
                        Restore bedrock
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Viewport -->
    <main class="flex-1 flex flex-col py-8 sm:py-12 px-4 sm:px-6 max-w-4xl mx-auto w-full">

        <!-- Hero Section & Hypothesis Composer -->
        <div class="mb-12 max-w-[720px] mx-auto w-full">
            <h1 class="font-serif text-3xl sm:text-4xl text-[var(--text)] font-normal tracking-tight mb-2 text-left sm:text-center">
                What Startup Hypothesis Shall We Test?
            </h1>
            <p class="text-[var(--text-muted)] text-sm sm:text-base mb-6 text-left sm:text-center max-w-[64ch] mx-auto leading-relaxed">
                State any business model assumption, technical architecture challenge, or student behavior intuition to forge your next 5-facet building block.
            </p>

            <!-- Composer (Hero input) -->
            <form @submit.prevent="forgeNewInquiry(null)" 
                  class="rounded-[var(--radius-xl)] bg-[var(--bg-raised)] border border-[var(--border-strong)] shadow-[var(--shadow-1)] p-3 sm:p-4 mb-6 transition-all focus-within:border-[var(--accent)]">
                <textarea x-model="inputPrompt"
                          rows="3"
                          placeholder="e.g. Will students pay KES 20 via M-Pesa STK push for step-by-step solutions if question papers are 100% free?"
                          class="w-full bg-transparent text-[var(--text)] placeholder-[var(--text-faint)] focus:outline-none resize-none text-sm sm:text-base font-sans leading-relaxed border-0 p-0"
                          autofocus></textarea>
                
                <div class="flex items-center justify-between pt-3 border-t border-[var(--border)] mt-2">
                    <span class="text-xs text-[var(--text-faint)] hidden sm:inline">
                        Synthesizes priors, experiment, results, and 3 strategic paths
                    </span>
                    <button type="submit"
                            :disabled="!inputPrompt.trim() || isLoading"
                            class="btn-primary !h-9 !px-4 text-xs sm:text-sm ml-auto">
                        <span x-text="isLoading ? 'Synthesizing...' : 'Forge hypothesis'"></span>
                    </button>
                </div>
            </form>

            <!-- Starter Prompts (Quiet list) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 text-left">
                @foreach($starters as $starter)
                <button @click="startWithStarter({{ json_encode($starter) }})"
                        type="button"
                        class="p-3.5 rounded-[var(--radius-md)] bg-[var(--bg-raised)] hover:bg-[var(--bg-sunken)] border border-[var(--border)] hover:border-[var(--border-strong)] transition-colors text-left group">
                    <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-1">{{ $starter['category'] }}</span>
                    <h3 class="text-xs font-medium text-[var(--text)] group-hover:text-[var(--accent)] transition-colors">
                        {{ $starter['title'] }}
                    </h3>
                    <p class="text-xs text-[var(--text-muted)] mt-1 line-clamp-2 leading-relaxed">
                        “{{ $starter['question'] }}”
                    </p>
                </button>
                @endforeach
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MASONRY WALL & CONNECTED BUILDING BLOCKS   -->
        <!-- ========================================== -->
        <div id="canvas-container" class="relative">

            <!-- Dynamic SVG Bezier Connector Layer (Calm 1.5px hairline diagram) -->
            <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" style="min-height: 100%;">
                <template x-for="conn in connectors" :key="conn.id">
                    <g>
                        <path :d="conn.d" 
                              fill="none" 
                              :stroke="conn.isChallenged ? 'var(--error-text)' : 'var(--border-strong)'" 
                              stroke-width="1.5" 
                              :stroke-dasharray="conn.isChallenged ? '4,4' : 'none'" />
                    </g>
                </template>
            </svg>

            <!-- Building Blocks List -->
            <div class="relative z-10 space-y-10">
                <template x-for="(block, index) in blocks" :key="block.id">
                    <div :id="block.id"
                         class="rounded-[var(--radius-lg)] bg-[var(--bg-raised)] transition-colors border overflow-hidden"
                         :class="{
                             'border-[var(--border-strong)]': block.sealed && !block.challenged,
                             'border-[var(--error-text)]': block.challenged,
                             'border-[var(--border)]': !block.sealed && !block.challenged
                         }">
                        
                        <!-- Upstream Alert -->
                        <div x-show="block.upstreamAlert" 
                             class="px-4 py-2 bg-[var(--error-bg)] border-b border-[var(--border)] text-[var(--error-text)] text-xs flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[var(--error-text)]"></span>
                            <span x-text="block.upstreamAlert"></span>
                        </div>

                        <!-- Block Header Bar -->
                        <div class="p-4 sm:p-5 border-b border-[var(--border)] flex flex-wrap items-center justify-between gap-3 bg-[var(--bg-sunken)]">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-[var(--radius-sm)] bg-[var(--bg-raised)] border border-[var(--border)] flex items-center justify-center font-mono text-xs text-[var(--text-muted)] font-medium"
                                     x-text="index + 1">
                                </div>

                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-[var(--text-muted)] font-sans" x-text="block.category || 'Venture foundation'"></span>
                                        <span x-show="block.source === 'heuristic' || block.source === 'heuristic_fallback'" 
                                              class="text-[11px] px-1.5 py-0.2 rounded-[var(--radius-sm)] bg-[var(--bg-tint)] text-[var(--text-faint)] font-mono">Offline</span>
                                    </div>
                                    <h2 class="font-serif text-lg sm:text-xl font-normal text-[var(--text)] tracking-tight" x-text="block.title"></h2>
                                </div>
                            </div>

                            <!-- State Badges & Actions -->
                            <div class="flex items-center gap-2">
                                <template x-if="block.sealed && !block.challenged">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[var(--radius-sm)] bg-[var(--bg-tint)] text-[var(--text-muted)] text-xs font-sans">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--success-text)]"></span>
                                        <span>Proven foundation</span>
                                    </span>
                                </template>

                                <template x-if="block.challenged">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[var(--radius-sm)] bg-[var(--error-bg)] text-[var(--error-text)] text-xs font-sans">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--error-text)]"></span>
                                        <span x-text="block.challenge_notes?.verdict_title || 'Premise questioned'"></span>
                                    </span>
                                </template>

                                <template x-if="!block.sealed">
                                    <button @click="sealBlock(block)"
                                            type="button"
                                            class="btn-primary !h-8 !px-3 !text-xs">
                                        Seal foundation
                                    </button>
                                </template>

                                <template x-if="block.sealed && !block.challenged">
                                    <button @click="openChallenge(block)"
                                            type="button"
                                            class="btn-ghost !h-8 !px-2.5 !text-xs text-[var(--text-muted)] hover:text-[var(--text)]">
                                        Challenge
                                    </button>
                                </template>

                                <button @click="deleteBlock(block.id)"
                                        type="button"
                                        class="p-1.5 text-[var(--text-faint)] hover:text-[var(--text)] rounded-[var(--radius-sm)] hover:bg-[var(--bg-sunken)] transition-colors"
                                        title="Remove block">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- 5 CORE FACETS -->
                        <div class="p-5 sm:p-6 space-y-6">

                            <!-- FACET 1: HYPOTHESIS -->
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)]"></span>
                                    <span>Facet 1: Hypothesis under test</span>
                                </div>
                                <p class="font-serif italic text-base sm:text-lg text-[var(--text)] leading-relaxed prose" x-text="block.hypothesis"></p>
                            </div>

                            <!-- FACET 2: THINGS WE KNOW -->
                            <div class="space-y-2 pt-2 border-t border-[var(--border)]">
                                <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--info-text)]"></span>
                                    <span>Facet 2: Things we know</span>
                                </div>
                                <ul class="space-y-1.5 text-sm text-[var(--text)]">
                                    <template x-for="(prior, pIndex) in block.things_we_know" :key="pIndex">
                                        <li class="flex items-start gap-2">
                                            <span class="text-[var(--text-muted)] select-none">—</span>
                                            <span x-text="prior"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <!-- FACET 3: THE EXPERIMENT -->
                            <div class="space-y-2 pt-2 border-t border-[var(--border)]">
                                <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--purple-text)]"></span>
                                    <span>Facet 3: The experiment</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs sm:text-sm">
                                    <div class="p-3 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)]">
                                        <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-1">Apparatus and tools</span>
                                        <p class="text-[var(--text)]" x-text="block.experiment?.apparatus"></p>
                                    </div>
                                    <div class="p-3 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)] md:col-span-2">
                                        <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-1">Method and procedure</span>
                                        <p class="text-[var(--text)] leading-relaxed" x-text="block.experiment?.method"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- FACET 4: RESULTS & PRINCIPLES -->
                            <div class="space-y-3 pt-2 border-t border-[var(--border)]">
                                <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--success-text)]"></span>
                                    <span>Facet 4: Results and deduced principle</span>
                                </div>
                                
                                <div class="text-xs sm:text-sm">
                                    <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-0.5">Observed behavior</span>
                                    <p class="text-[var(--text)]" x-text="block.results?.observation"></p>
                                </div>

                                <div class="p-3.5 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border-l-2 border-[var(--accent)] text-xs sm:text-sm">
                                    <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-0.5">Deduced principle</span>
                                    <p class="font-serif text-sm sm:text-base font-normal text-[var(--text)] leading-relaxed" x-text="block.results?.lesson"></p>
                                </div>

                                <div class="text-xs">
                                    <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-0.5">Venture significance</span>
                                    <p class="text-[var(--text-muted)]" x-text="block.results?.significance"></p>
                                </div>
                            </div>

                            <!-- CHALLENGE REVIEW (IF CHALLENGED) -->
                            <template x-if="block.challenged && block.challenge_notes">
                                <div class="p-4 rounded-[var(--radius-md)] bg-[var(--error-bg)] border border-[var(--error-text)] text-xs sm:text-sm space-y-2">
                                    <div class="flex items-center justify-between text-[var(--error-text)] font-sans text-xs">
                                        <span class="font-medium">Market challenge evaluation</span>
                                        <span class="font-mono text-[11px]" x-text="block.challenge_notes.verdict"></span>
                                    </div>
                                    <p class="text-[var(--text)] leading-relaxed" x-text="block.challenge_notes.analysis"></p>
                                    
                                    <template x-if="block.challenge_notes.fractured_assumptions?.length">
                                        <div class="pt-1">
                                            <span class="text-[11px] text-[var(--error-text)] font-sans block mb-1">Fractured assumptions:</span>
                                            <ul class="list-disc list-inside space-y-0.5 text-[var(--text)]">
                                                <template x-for="item in block.challenge_notes.fractured_assumptions" :key="item">
                                                    <li x-text="item"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </template>

                                    <template x-if="block.challenge_notes.new_synthesis">
                                        <div class="pt-2 p-2.5 rounded-[var(--radius-sm)] bg-[var(--bg-raised)] border border-[var(--border)]">
                                            <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-1">Refined synthesis:</span>
                                            <p class="text-[var(--text)] font-serif" x-text="block.challenge_notes.new_synthesis"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- FACET 5: NEXT STRATEGIC PATHS (BRANCHING) -->
                            <div class="pt-2 border-t border-[var(--border)]">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--text-faint)]"></span>
                                        <span>Facet 5: Next possible paths</span>
                                    </div>
                                    <span class="text-xs text-[var(--text-faint)]" x-show="!block.sealed">Seal block to branch</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <template x-for="(path, pathIndex) in block.next_paths" :key="pathIndex">
                                        <button @click="branchFromNextPath(block, path)"
                                                :disabled="!block.sealed || isLoading"
                                                type="button"
                                                class="p-3.5 rounded-[var(--radius-md)] text-left transition-colors flex flex-col justify-between group bg-[var(--bg-sunken)] hover:bg-[var(--bg-tint)] border border-[var(--border)]"
                                                :class="{
                                                    'opacity-40 cursor-not-allowed': !block.sealed || isLoading
                                                }">
                                            <div>
                                                <div class="flex items-center gap-1.5 mb-1.5">
                                                    <!-- Tiny distinct dot -->
                                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                                          :class="{
                                                              'bg-[var(--success-text)]': path.type === 'validate',
                                                              'bg-[var(--info-text)]': path.type === 'horizon',
                                                              'bg-[var(--warning-text)]': path.type === 'paradox'
                                                          }"></span>
                                                    <span class="text-[11px] text-[var(--text-muted)] font-sans" x-text="path.label"></span>
                                                </div>
                                                <p class="font-serif text-sm font-normal text-[var(--text)] group-hover:text-[var(--accent)] transition-colors leading-snug mb-2"
                                                   x-text="path.question">
                                                </p>
                                            </div>
                                            <p class="text-xs text-[var(--text-muted)]" x-text="path.teaser"></p>
                                        </button>
                                    </template>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- LOADING OVERLAY                            -->
        <!-- ========================================== -->
        <div x-show="isLoading" 
             x-transition:enter="transition ease-out duration-120"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(31,30,29,0.4)] p-4">
            <div class="p-6 rounded-[var(--radius-xl)] bg-[var(--bg-raised)] border border-[var(--border)] max-w-sm w-full text-center shadow-[var(--shadow-3)]">
                <div class="w-6 h-6 border-2 border-[var(--border-strong)] border-t-[var(--accent)] rounded-full animate-spin mx-auto mb-3"></div>
                <h3 class="font-serif text-base font-normal text-[var(--text)] mb-1">Synthesizing building block</h3>
                <p class="text-xs text-[var(--text-muted)]" x-text="loadingMessage"></p>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- GEMINI API KEY MODAL                       -->
        <!-- ========================================== -->
        <div x-show="showKeyModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(31,30,29,0.4)] p-4"
             @keydown.escape.window="showKeyModal = false">
            <div class="p-7 rounded-[var(--radius-xl)] bg-[var(--bg-raised)] border border-[var(--border)] max-w-lg w-full shadow-[var(--shadow-3)] relative"
                 @click.away="showKeyModal = false">
                
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-serif text-xl font-normal text-[var(--text)]">Gemini API setup</h3>
                    <button @click="showKeyModal = false" class="btn-ghost !h-7 !px-2 text-xs">✕</button>
                </div>

                <p class="text-xs text-[var(--text-muted)] mb-4 leading-relaxed">
                    Connect your Gemini API key to unlock dynamic hypothesis synthesis. The key is stored locally in your browser and never shared.
                </p>

                <div class="p-3 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)] mb-4 flex items-center justify-between text-xs">
                    <span class="text-[var(--text-muted)]">Need an API key?</span>
                    <a href="https://aistudio.google.com/app/apikey" 
                       target="_blank" 
                       rel="noopener"
                       class="text-[var(--accent)] hover:underline">
                        Get free key at Google AI Studio
                    </a>
                </div>

                <div class="mb-5">
                    <label class="block text-xs text-[var(--text-muted)] mb-1.5">API key</label>
                    <input type="password" 
                           x-model="tempApiKey"
                           placeholder="AIzaSy..."
                           class="w-full px-3.5 py-2 rounded-[var(--radius-md)] bg-[var(--bg-raised)] border border-[var(--border-strong)] text-[var(--text)] placeholder-[var(--text-faint)] focus:outline-none focus:border-[var(--accent)] text-xs font-mono" />
                </div>

                <div x-show="keyNotice" class="mb-4 text-xs text-[var(--success-text)]" x-text="keyNotice"></div>

                <div class="flex items-center justify-between gap-3 pt-2 border-t border-[var(--border)]">
                    <button @click="clearApiKey()" 
                            type="button" 
                            class="btn-ghost !h-8 !px-2 !text-xs text-[var(--error-text)]">
                        Clear stored key
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <button @click="showKeyModal = false" 
                                type="button" 
                                class="btn-secondary !h-8 !px-3 !text-xs">
                            Cancel
                        </button>
                        <button @click="saveApiKey()" 
                                type="button" 
                                class="btn-primary !h-8 !px-3 !text-xs">
                            Save key
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- CHALLENGE FOUNDATION MODAL                 -->
        <!-- ========================================== -->
        <div x-show="showChallengeModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(31,30,29,0.4)] p-4"
             @keydown.escape.window="showChallengeModal = false">
            <div class="p-7 rounded-[var(--radius-xl)] bg-[var(--bg-raised)] border border-[var(--border)] max-w-lg w-full shadow-[var(--shadow-3)] relative"
                 @click.away="showChallengeModal = false">
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-serif text-xl font-normal text-[var(--text)]">Challenge foundation</h3>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5" x-text="'Testing: ' + challengingBlock?.title"></p>
                    </div>
                    <button @click="showChallengeModal = false" class="btn-ghost !h-7 !px-2 text-xs">✕</button>
                </div>

                <div class="p-3 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)] mb-4 text-xs text-[var(--text-muted)] space-y-1">
                    <span class="text-[11px] text-[var(--text-faint)] block">Accepted principle</span>
                    <p class="text-[var(--text)] font-serif" x-text="challengingBlock?.results?.lesson"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-[var(--text-muted)] mb-1.5">
                        What counter-evidence, metric failure, or user friction challenges this premise?
                    </label>
                    <textarea x-model="challengeInput"
                              rows="3"
                              placeholder="e.g. Students share downloaded solutions on WhatsApp despite watermarks; or Safaricom STK push failure rate exceeds 15% on weak campus cellular coverage."
                              class="w-full px-3.5 py-2 rounded-[var(--radius-md)] bg-[var(--bg-raised)] border border-[var(--border-strong)] text-[var(--text)] placeholder-[var(--text-faint)] focus:outline-none focus:border-[var(--accent)] text-xs sm:text-sm leading-relaxed"></textarea>
                </div>

                <template x-if="challengeResult">
                    <div class="mb-4 p-3.5 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)] text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-[var(--text)]" x-text="challengeResult.verdict_title"></span>
                            <span class="font-mono text-[11px] text-[var(--text-muted)]" x-text="challengeResult.verdict"></span>
                        </div>
                        <p class="text-[var(--text-muted)]" x-text="challengeResult.analysis"></p>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--border)]">
                    <button @click="showChallengeModal = false" 
                            type="button" 
                            class="btn-secondary !h-8 !px-3 !text-xs">
                        Close
                    </button>
                    <button @click="submitChallenge()" 
                            :disabled="!challengeInput.trim() || isChallenging"
                            type="button" 
                            class="btn-primary !h-8 !px-3 !text-xs">
                        <span x-text="isChallenging ? 'Evaluating...' : 'Evaluate challenge'"></span>
                    </button>
                </div>

            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-[var(--border)] bg-[var(--bg)] py-8 text-center text-xs text-[var(--text-muted)]">
        <div class="max-w-4xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p>Paprz Lineage · Reconstructing startup epistemology from first principles</p>
            <div class="flex items-center gap-3 text-[var(--text-faint)]">
                <span>Venture: Paprz</span>
                <span>·</span>
                <span>Academic Solutions Engine</span>
            </div>
        </div>
    </footer>

</body>
</html>
