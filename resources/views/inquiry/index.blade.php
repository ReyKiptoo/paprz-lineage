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

    <!-- Toast Notification Banner -->
    <div x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-120"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-[var(--radius-md)] bg-[var(--bg-raised)] border border-[var(--border-strong)] shadow-[var(--shadow-2)] text-xs font-medium text-[var(--text)] flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)]"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-40 border-b border-[var(--border)] bg-[var(--bg)]/95 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16 gap-4">
                
                <!-- Wordmark & Subtitle -->
                <div class="flex items-center gap-3">
                    <span class="font-serif text-lg font-normal tracking-tight text-[var(--text)]">
                        Paprz Lineage
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-[var(--radius-sm)] bg-[var(--bg-sunken)] text-[var(--text-muted)] border border-[var(--border)] font-sans hidden sm:inline-block">
                        Startup Thought Engine
                    </span>
                </div>

                <!-- Navigation Tools -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Proven Counter -->
                    <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-[var(--radius-sm)] bg-[var(--bg-sunken)] border border-[var(--border)] text-xs font-mono text-[var(--text-muted)]">
                        <span class="text-[var(--text-muted)]" x-text="draftCount + ' drafts'"></span>
                        <span class="text-[var(--text-faint)]">/</span>
                        <span class="text-[var(--success-text)] font-medium" x-text="sealedCount + ' proven'"></span>
                    </div>

                    <!-- Macro Synthesis Button -->
                    <button @click="synthesizeMacro()"
                            x-show="sealedCount >= 2"
                            type="button"
                            class="btn-secondary !h-8 !px-2.5 !text-xs text-[var(--purple-text)] border-[var(--purple-text)]/40 hover:bg-[var(--purple-bg)]"
                            title="Synthesize proven cluster into overarching Master Truth">
                        <span>Consolidate master truth</span>
                    </button>

                    <!-- Interrogate Me Dialogue Co-Pilot -->
                    <button @click="openDialogue()"
                            type="button"
                            class="btn-primary !h-8 !px-3 !text-xs flex items-center gap-1.5"
                            title="Open Socratic founder interrogation co-pilot">
                        <span>Interrogate me</span>
                        <span class="text-[10px]">💬</span>
                    </button>

                    <!-- Save to Bedrock -->
                    <button @click="saveToBedrock()"
                            type="button"
                            class="btn-secondary !h-8 !px-2.5 !text-xs hidden md:inline-flex"
                            title="Save current lineage tree permanently to server bedrock">
                        <span>Save to bedrock</span>
                    </button>

                    <!-- Gemini API Status Button -->
                    <button @click="showKeyModal = true"
                            type="button"
                            class="btn-secondary !h-8 !px-2.5 !text-xs flex items-center gap-1.5"
                            :title="apiStatusMessage || 'Configure Gemini API key'">
                        <span class="w-1.5 h-1.5 rounded-full" 
                              :class="{
                                  'bg-[var(--success-text)]': apiStatus === 'connected',
                                  'bg-[var(--error-text)] animate-pulse': apiStatus === 'error',
                                  'bg-[var(--warning-text)]': apiStatus === 'untested',
                                  'bg-[var(--accent)] animate-ping': apiStatus === 'testing'
                              }"></span>
                        <span x-text="apiStatus === 'connected' ? 'Gemini live' : (apiStatus === 'error' ? 'Gemini error' : (apiStatus === 'testing' ? 'Testing...' : 'Connect Gemini'))"></span>
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
                            class="btn-ghost !h-8 !px-2.5 !text-xs hidden lg:inline-flex text-[var(--text-faint)]"
                            title="Reset to genesis bedrock">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Viewport -->
    <main class="flex-1 flex flex-col py-6 sm:py-10 px-4 sm:px-6 max-w-7xl mx-auto w-full">

        <!-- Hero Section & Hypothesis Composer -->
        <div class="mb-10 max-w-[720px] mx-auto w-full">
            <h1 class="font-serif text-3xl sm:text-4xl text-[var(--text)] font-normal tracking-tight mb-2 text-left sm:text-center">
                What Startup Hypothesis Shall We Test?
            </h1>
            <p class="text-[var(--text-muted)] text-sm sm:text-base mb-6 text-left sm:text-center max-w-[64ch] mx-auto leading-relaxed">
                Start with open questions. Every hypothesis forms a compact story block connected causally to what came before.
            </p>

            <!-- Composer (Hero input) -->
            <form @submit.prevent="forgeNewInquiry(null, null, 'genesis', 'Initial inquiry question')" 
                  class="rounded-[var(--radius-xl)] bg-[var(--bg-raised)] border border-[var(--border-strong)] shadow-[var(--shadow-1)] p-3 sm:p-4 mb-5 transition-all focus-within:border-[var(--accent)]">
                <textarea x-model="inputPrompt"
                          rows="3"
                          placeholder="State any business assumption, pricing intuition, or product experiment to test..."
                          class="w-full bg-transparent text-[var(--text)] placeholder-[var(--text-faint)] focus:outline-none focus:ring-0 focus:border-0 border-0 ring-0 resize-none text-sm sm:text-base font-sans leading-relaxed p-0"
                          style="outline: none !important; box-shadow: none !important;"></textarea>
                
                <div class="flex items-center justify-between pt-3 border-t border-[var(--border)] mt-2">
                    <span class="text-xs text-[var(--text-faint)] hidden sm:inline">
                        Forges a draft 5-facet story block ready for validation
                    </span>
                    <button type="submit"
                            :disabled="!inputPrompt.trim() || isLoading"
                            class="btn-primary !h-9 !px-4 text-xs sm:text-sm ml-auto">
                        <span x-text="isLoading ? 'Synthesizing...' : 'Forge hypothesis'"></span>
                    </button>
                </div>
            </form>

            <!-- Starter Prompts (Quiet list) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-left">
                @foreach(array_slice($starters, 0, 3) as $starter)
                <button @click="startWithStarter({{ json_encode($starter) }})"
                        type="button"
                        class="p-3 rounded-[var(--radius-md)] bg-[var(--bg-raised)] hover:bg-[var(--bg-sunken)] border border-[var(--border)] hover:border-[var(--border-strong)] transition-colors text-left group">
                    <span class="text-[10px] text-[var(--text-muted)] font-sans block mb-0.5">{{ $starter['category'] }}</span>
                    <h3 class="text-xs font-medium text-[var(--text)] group-hover:text-[var(--accent)] transition-colors">
                        {{ $starter['title'] }}
                    </h3>
                    <p class="text-xs text-[var(--text-muted)] mt-1 line-clamp-1 leading-relaxed">
                        “{{ $starter['question'] }}”
                    </p>
                </button>
                @endforeach
            </div>
        </div>

        <!-- ========================================== -->
        <!-- CANVAS CONTROLS & FILTER BAR               -->
        <!-- ========================================== -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-3 border-b border-[var(--border)]">
            <div class="flex items-center gap-2">
                <span class="text-xs font-serif text-[var(--text)] font-medium">Hypothesis Story Blocks</span>
                <span class="text-xs text-[var(--text-faint)] font-mono" x-text="'(' + totalCount + ' blocks)'"></span>
            </div>

            <!-- Filter tabs -->
            <div class="flex items-center gap-1 text-xs">
                <button @click="filterStatus = 'all'"
                        class="px-2.5 py-1 rounded-[var(--radius-sm)] transition-colors"
                        :class="filterStatus === 'all' ? 'bg-[var(--bg-tint)] text-[var(--text)] font-medium' : 'text-[var(--text-muted)] hover:text-[var(--text)]'">
                    All (<span x-text="totalCount"></span>)
                </button>
                <button @click="filterStatus = 'draft'"
                        class="px-2.5 py-1 rounded-[var(--radius-sm)] transition-colors"
                        :class="filterStatus === 'draft' ? 'bg-[var(--bg-tint)] text-[var(--text)] font-medium' : 'text-[var(--text-muted)] hover:text-[var(--text)]'">
                    Drafts (<span x-text="draftCount"></span>)
                </button>
                <button @click="filterStatus = 'sealed'"
                        class="px-2.5 py-1 rounded-[var(--radius-sm)] transition-colors"
                        :class="filterStatus === 'sealed' ? 'bg-[var(--bg-tint)] text-[var(--text)] font-medium' : 'text-[var(--text-muted)] hover:text-[var(--text)]'">
                    Proven (<span x-text="sealedCount"></span>)
                </button>
                <button @click="filterStatus = 'challenged'"
                        class="px-2.5 py-1 rounded-[var(--radius-sm)] transition-colors"
                        :class="filterStatus === 'challenged' ? 'bg-[var(--bg-tint)] text-[var(--text)] font-medium' : 'text-[var(--text-muted)] hover:text-[var(--text)]'">
                    Questioned
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MASONRY CANVAS OF TINY STORY BLOCKS        -->
        <!-- ========================================== -->
        <div id="canvas-container" class="relative min-h-[400px]">

            <!-- Dynamic SVG Bezier Connector Layer (Colored lines by connection reason) -->
            <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" style="min-height: 100%;">
                <template x-for="conn in connectors" :key="conn.id">
                    <g>
                        <!-- Main Bezier Curve -->
                        <path :d="conn.d" 
                              fill="none" 
                              :stroke="conn.isChallenged ? 'var(--error-text)' : (
                                  conn.branch_type === 'validate' ? 'var(--success-text)' : (
                                      conn.branch_type === 'horizon' ? 'var(--info-text)' : (
                                          conn.branch_type === 'paradox' ? 'var(--warning-text)' : 'var(--accent)'
                                      )
                                  )
                              )" 
                              stroke-width="1.5" 
                              :stroke-dasharray="conn.isChallenged ? '4,4' : 'none'" />

                        <!-- Midpoint connection reason label circle marker -->
                        <circle :cx="conn.mx" :cy="conn.my" r="3" 
                                :fill="conn.isChallenged ? 'var(--error-text)' : (
                                    conn.branch_type === 'validate' ? 'var(--success-text)' : (
                                        conn.branch_type === 'horizon' ? 'var(--info-text)' : (
                                            conn.branch_type === 'paradox' ? 'var(--warning-text)' : 'var(--accent)'
                                        )
                                    )
                                )" />
                    </g>
                </template>
            </svg>

            <!-- Cards Grid: Compact Tiny Story Cards -->
            <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="(block, index) in filteredBlocks" :key="block.id">
                    <div :id="'card-' + block.id"
                         @click="selectBlock(block.id)"
                         class="cursor-pointer rounded-[var(--radius-lg)] bg-[var(--bg-raised)] hover:bg-[var(--bg-sunken)] p-4 sm:p-5 border transition-all relative flex flex-col justify-between"
                         :class="{
                             'ring-2 ring-[var(--accent)] border-transparent shadow-[var(--shadow-2)]': selectedBlockId === block.id,
                             'border-[var(--border)]': selectedBlockId !== block.id,
                             'border-l-4 !border-l-[var(--success-text)]': block.branch_type === 'validate',
                             'border-l-4 !border-l-[var(--info-text)]': block.branch_type === 'horizon',
                             'border-l-4 !border-l-[var(--warning-text)]': block.branch_type === 'paradox',
                             'border-l-4 !border-l-[var(--purple-text)]': block.is_macro,
                             'border-l-4 !border-l-[var(--accent)]': !block.branch_type || block.branch_type === 'genesis'
                         }">

                        <!-- Card Top Bar: Reason Tag & Status -->
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <!-- Connection Origin Badge -->
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-sans"
                                      :class="{
                                          'text-[var(--success-text)]': block.branch_type === 'validate',
                                          'text-[var(--info-text)]': block.branch_type === 'horizon',
                                          'text-[var(--warning-text)]': block.branch_type === 'paradox',
                                          'text-[var(--purple-text)]': block.is_macro,
                                          'text-[var(--accent)]': !block.branch_type || block.branch_type === 'genesis'
                                      }">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                          :class="{
                                              'bg-[var(--success-text)]': block.branch_type === 'validate',
                                              'bg-[var(--info-text)]': block.branch_type === 'horizon',
                                              'bg-[var(--warning-text)]': block.branch_type === 'paradox',
                                              'bg-[var(--purple-text)]': block.is_macro,
                                              'bg-[var(--accent)]': !block.branch_type || block.branch_type === 'genesis'
                                          }"></span>
                                    <span class="truncate max-w-[140px]" 
                                          x-text="block.is_macro ? 'Master truth' : (block.branch_type ? block.branch_type : 'Genesis')"></span>
                                </span>

                                <!-- Status Badge -->
                                <div>
                                    <template x-if="block.challenged">
                                        <span class="px-1.5 py-0.5 rounded-[var(--radius-sm)] bg-[var(--error-bg)] text-[var(--error-text)] text-[10px] font-medium">
                                            Questioned ⚡
                                        </span>
                                    </template>
                                    <template x-if="block.sealed && !block.challenged">
                                        <span class="px-1.5 py-0.5 rounded-[var(--radius-sm)] bg-[var(--success-bg)] text-[var(--success-text)] text-[10px] font-medium">
                                            Proven 🧱
                                        </span>
                                    </template>
                                    <template x-if="!block.sealed && !block.challenged">
                                        <span class="px-1.5 py-0.5 rounded-[var(--radius-sm)] bg-[var(--bg-sunken)] border border-[var(--border)] text-[var(--text-muted)] text-[10px]">
                                            Draft
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Title -->
                            <h3 class="font-serif text-base sm:text-lg font-normal text-[var(--text)] leading-snug line-clamp-2 mb-2"
                                x-text="block.title">
                            </h3>

                            <!-- Hypothesis Snippet -->
                            <p class="font-serif italic text-xs text-[var(--text-muted)] line-clamp-2 leading-relaxed mb-3"
                               x-text="block.hypothesis">
                            </p>
                        </div>

                        <!-- Card Bottom Bar: Reason & Tap Hint -->
                        <div class="pt-3 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-faint)]">
                            <span class="truncate max-w-[160px] text-[11px]" 
                                  x-text="block.connection_reason || block.category || 'Independent'"></span>
                            <span class="text-[var(--accent)] hover:underline text-[11px] font-medium flex items-center gap-1">
                                <span>Inspect</span>
                                <span>→</span>
                            </span>
                        </div>

                    </div>
                </template>
            </div>

        </div>

    </main>

    <!-- ========================================== -->
    <!-- 5-FACET DETAIL INSPECTOR DRAWER            -->
    <!-- ========================================== -->
    <div x-show="isInspectorOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden flex justify-end"
         @keydown.escape.window="closeInspector()">
        
        <!-- Scrim backdrop -->
        <div class="fixed inset-0 bg-[rgba(31,30,29,0.3)] transition-opacity"
             @click="closeInspector()"></div>

        <!-- Drawer Panel -->
        <div class="relative w-full max-w-xl bg-[var(--bg-raised)] border-l border-[var(--border)] shadow-[var(--shadow-3)] h-full flex flex-col z-10 overflow-y-auto">
            
            <template x-if="selectedBlock">
                <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-6">
                    
                    <!-- Header -->
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs px-2 py-0.5 rounded-[var(--radius-sm)] bg-[var(--bg-sunken)] border border-[var(--border)] text-[var(--text-muted)]"
                                      x-text="selectedBlock.id"></span>
                                <span class="text-xs text-[var(--text-muted)]" x-text="selectedBlock.category"></span>
                            </div>
                            <button @click="closeInspector()" class="btn-ghost !h-7 !px-2 text-xs">✕</button>
                        </div>

                        <!-- Title -->
                        <h2 class="font-serif text-2xl font-normal text-[var(--text)] tracking-tight mb-2" 
                            x-text="selectedBlock.title"></h2>

                        <!-- Status & Origin Banner -->
                        <div class="flex flex-wrap items-center gap-2 text-xs mb-4">
                            <!-- Status -->
                            <template x-if="selectedBlock.sealed">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[var(--radius-sm)] bg-[var(--success-bg)] text-[var(--success-text)] font-medium">
                                    <span>Proven truth</span>
                                    <span class="text-[10px] text-[var(--text-faint)]" x-text="selectedBlock.sealed_at"></span>
                                </span>
                            </template>
                            <template x-if="!selectedBlock.sealed">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[var(--radius-sm)] bg-[var(--bg-sunken)] border border-[var(--border)] text-[var(--text-muted)]">
                                    <span>Draft hypothesis (Testing in progress)</span>
                                </span>
                            </template>
                            <template x-if="selectedBlock.connection_reason">
                                <span class="text-[var(--text-faint)] italic" x-text="'Reason: ' + selectedBlock.connection_reason"></span>
                            </template>
                        </div>

                        <!-- Action Toolbar -->
                        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-[var(--border)] mb-6">
                            <!-- Toggle Seal / Proven -->
                            <template x-if="!selectedBlock.sealed">
                                <button @click="sealBlock(selectedBlock)" 
                                        type="button" 
                                        class="btn-primary !h-8 !px-3 !text-xs">
                                    <span>Seal as proven truth 🧱</span>
                                </button>
                            </template>
                            <template x-if="selectedBlock.sealed">
                                <button @click="unsealBlock(selectedBlock)" 
                                        type="button" 
                                        class="btn-secondary !h-8 !px-3 !text-xs">
                                    <span>Revert to draft</span>
                                </button>
                            </template>

                            <!-- Branch New Child -->
                            <button @click="openBranchModal(selectedBlock)" 
                                    type="button" 
                                    class="btn-secondary !h-8 !px-3 !text-xs">
                                <span>Branch next hypothesis</span>
                            </button>

                            <!-- Challenge Foundation -->
                            <template x-if="selectedBlock.sealed">
                                <button @click="openChallenge(selectedBlock)" 
                                        type="button" 
                                        class="btn-ghost !h-8 !px-2.5 !text-xs text-[var(--error-text)] hover:bg-[var(--error-bg)]">
                                    <span>Challenge ⚡</span>
                                </button>
                            </template>

                            <!-- Delete -->
                            <button @click="deleteBlock(selectedBlock.id)" 
                                    type="button" 
                                    class="btn-ghost !h-8 !px-2 !text-xs ml-auto text-[var(--text-faint)] hover:text-[var(--error-text)]">
                                <span>Delete</span>
                            </button>
                        </div>

                        <!-- 5 CORE FACETS CONTAINER -->
                        <div class="space-y-6">

                            <!-- FACET 1: HYPOTHESIS -->
                            <div class="space-y-1.5 p-4 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)]">
                                <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)]"></span>
                                    <span>Facet 1: Hypothesis under test</span>
                                </div>
                                <p class="font-serif italic text-base text-[var(--text)] leading-relaxed prose" 
                                   x-text="selectedBlock.hypothesis"></p>
                            </div>

                            <!-- FACET 2: THINGS WE KNOW -->
                            <div class="space-y-2">
                                <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--info-text)]"></span>
                                    <span>Facet 2: Things we know (Accepted priors)</span>
                                </div>
                                <ul class="space-y-1.5 text-sm text-[var(--text)]">
                                    <template x-for="(prior, pIndex) in selectedBlock.things_we_know" :key="pIndex">
                                        <li class="flex items-start gap-2">
                                            <span class="text-[var(--text-muted)] select-none">—</span>
                                            <span x-text="prior"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <!-- FACET 3: THE EXPERIMENT -->
                            <div class="space-y-2 pt-3 border-t border-[var(--border)]">
                                <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--purple-text)]"></span>
                                    <span>Facet 3: The experiment</span>
                                </div>
                                <div class="grid grid-cols-1 gap-2.5 text-xs sm:text-sm">
                                    <div class="p-3 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)]">
                                        <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-1">Apparatus and tools</span>
                                        <p class="text-[var(--text)]" x-text="selectedBlock.experiment?.apparatus"></p>
                                    </div>
                                    <div class="p-3 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)]">
                                        <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-1">Method and procedure</span>
                                        <p class="text-[var(--text)] leading-relaxed" x-text="selectedBlock.experiment?.method"></p>
                                    </div>
                                    <template x-if="selectedBlock.experiment?.variable_tested">
                                        <div class="p-2.5 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border)]">
                                            <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-0.5">Variable tested</span>
                                            <p class="text-[var(--text)]" x-text="selectedBlock.experiment?.variable_tested"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- FACET 4: RESULTS & PRINCIPLES -->
                            <div class="space-y-3 pt-3 border-t border-[var(--border)]">
                                <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--success-text)]"></span>
                                    <span>Facet 4: Results and deduced principle</span>
                                </div>
                                
                                <div class="text-xs sm:text-sm">
                                    <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-0.5">Observed behavior</span>
                                    <p class="text-[var(--text)]" x-text="selectedBlock.results?.observation"></p>
                                </div>

                                <div class="p-3.5 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border-l-2 border-[var(--accent)] text-xs sm:text-sm">
                                    <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-0.5 font-medium text-[var(--accent)]">Deduced principle</span>
                                    <p class="font-serif text-sm sm:text-base font-normal text-[var(--text)] leading-relaxed" 
                                       x-text="selectedBlock.results?.lesson"></p>
                                </div>

                                <div class="text-xs">
                                    <span class="text-[11px] text-[var(--text-muted)] font-sans block mb-0.5">Venture significance</span>
                                    <p class="text-[var(--text-muted)]" x-text="selectedBlock.results?.significance"></p>
                                </div>
                            </div>

                            <!-- CHALLENGE REVIEW (IF CHALLENGED) -->
                            <template x-if="selectedBlock.challenged && selectedBlock.challenge_notes">
                                <div class="p-4 rounded-[var(--radius-md)] bg-[var(--error-bg)] border border-[var(--error-text)] text-xs sm:text-sm space-y-2">
                                    <div class="flex items-center justify-between text-[var(--error-text)] font-sans text-xs">
                                        <span class="font-medium">Market challenge evaluation</span>
                                        <span class="font-mono text-[11px]" x-text="selectedBlock.challenge_notes.verdict"></span>
                                    </div>
                                    <p class="text-[var(--text)] leading-relaxed" x-text="selectedBlock.challenge_notes.analysis"></p>
                                    
                                    <template x-if="selectedBlock.challenge_notes.fractured_assumptions?.length">
                                        <div class="pt-1">
                                            <span class="text-[11px] text-[var(--error-text)] font-sans block mb-1">Fractured assumptions:</span>
                                            <ul class="list-disc list-inside space-y-0.5 text-[var(--text)]">
                                                <template x-for="item in selectedBlock.challenge_notes.fractured_assumptions" :key="item">
                                                    <li x-text="item"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- FACET 5: NEXT STRATEGIC PATHS -->
                            <div class="pt-3 border-t border-[var(--border)]">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] font-sans">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--text-faint)]"></span>
                                        <span>Facet 5: The 3 divergent branching paths</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2.5">
                                    <template x-for="(path, pathIndex) in selectedBlock.next_paths" :key="pathIndex">
                                        <button @click="branchFromNextPath(selectedBlock, path)"
                                                :disabled="isLoading"
                                                type="button"
                                                class="p-3 rounded-[var(--radius-md)] text-left transition-colors flex flex-col justify-between group bg-[var(--bg-sunken)] hover:bg-[var(--bg-tint)] border border-[var(--border)]">
                                            <div>
                                                <div class="flex items-center gap-1.5 mb-1">
                                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                                          :class="{
                                                              'bg-[var(--success-text)]': path.type === 'validate',
                                                              'bg-[var(--info-text)]': path.type === 'horizon',
                                                              'bg-[var(--warning-text)]': path.type === 'paradox'
                                                          }"></span>
                                                    <span class="text-[11px] text-[var(--text-muted)] font-sans font-medium" 
                                                          x-text="path.label"></span>
                                                    <span class="text-[11px] text-[var(--accent)] ml-auto opacity-0 group-hover:opacity-100 transition-opacity">Branch this hypothesis →</span>
                                                </div>
                                                <p class="font-serif text-sm font-normal text-[var(--text)] group-hover:text-[var(--accent)] transition-colors leading-snug mb-1"
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

                </div>
            </template>

        </div>
    </div>

    <!-- ========================================== -->
    <!-- CUSTOM BRANCH MODAL                        -->
    <!-- ========================================== -->
    <div x-show="showBranchModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(31,30,29,0.4)] p-4"
         @keydown.escape.window="showBranchModal = false">
        <div class="p-6 rounded-[var(--radius-xl)] bg-[var(--bg-raised)] border border-[var(--border)] max-w-lg w-full shadow-[var(--shadow-3)] relative"
             @click.away="showBranchModal = false">
            
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-serif text-xl font-normal text-[var(--text)]">Branch new hypothesis</h3>
                    <p class="text-xs text-[var(--text-muted)] mt-0.5" x-text="'Building from: ' + branchParentBlock?.title"></p>
                </div>
                <button @click="showBranchModal = false" class="btn-ghost !h-7 !px-2 text-xs">✕</button>
            </div>

            <!-- Connection reason type selector -->
            <div class="mb-4">
                <label class="block text-xs text-[var(--text-muted)] mb-1.5">Connection type / intent</label>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <button type="button" 
                            @click="branchType = 'validate'"
                            class="p-2 rounded-[var(--radius-sm)] border text-center transition-colors"
                            :class="branchType === 'validate' ? 'border-[var(--success-text)] bg-[var(--success-bg)] text-[var(--success-text)] font-medium' : 'border-[var(--border)] text-[var(--text-muted)]'">
                        🟢 Validate &amp; deepen
                    </button>
                    <button type="button" 
                            @click="branchType = 'horizon'"
                            class="p-2 rounded-[var(--radius-sm)] border text-center transition-colors"
                            :class="branchType === 'horizon' ? 'border-[var(--info-text)] bg-[var(--info-bg)] text-[var(--info-text)] font-medium' : 'border-[var(--border)] text-[var(--text-muted)]'">
                        🔵 New horizon
                    </button>
                    <button type="button" 
                            @click="branchType = 'paradox'"
                            class="p-2 rounded-[var(--radius-sm)] border text-center transition-colors"
                            :class="branchType === 'paradox' ? 'border-[var(--warning-text)] bg-[var(--warning-bg)] text-[var(--warning-text)] font-medium' : 'border-[var(--border)] text-[var(--text-muted)]'">
                        ⚡ Anomaly / risk
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs text-[var(--text-muted)] mb-1.5">What new hypothesis or question are we asking?</label>
                <textarea x-model="branchCustomPrompt"
                          rows="3"
                          placeholder="e.g. Will providing offline PDF caching on Flutter reduce hostel Wi-Fi dropouts by 40%?"
                          class="w-full px-3.5 py-2 rounded-[var(--radius-md)] bg-[var(--bg-raised)] border border-[var(--border-strong)] text-[var(--text)] placeholder-[var(--text-faint)] focus:outline-none focus:border-[var(--accent)] text-xs sm:text-sm leading-relaxed"
                          style="outline: none !important;"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--border)]">
                <button @click="showBranchModal = false" 
                        type="button" 
                        class="btn-secondary !h-8 !px-3 !text-xs">
                    Cancel
                </button>
                <button @click="submitCustomBranch()" 
                        :disabled="!branchCustomPrompt.trim() || isLoading"
                        type="button" 
                        class="btn-primary !h-8 !px-3 !text-xs">
                    <span>Forge child block</span>
                </button>
            </div>

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

            <div class="mb-4">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs text-[var(--text-muted)]">API key</label>
                    <button @click="testApiKey(tempApiKey, false)"
                            :disabled="!tempApiKey.trim() || isTestingKey"
                            type="button"
                            class="text-xs text-[var(--accent)] hover:underline flex items-center gap-1">
                        <span x-show="isTestingKey" class="w-1.5 h-1.5 rounded-full bg-[var(--accent)] animate-ping"></span>
                        <span x-text="isTestingKey ? 'Testing with Google...' : 'Test connection'"></span>
                    </button>
                </div>
                <input type="password" 
                       x-model="tempApiKey"
                       placeholder="AIzaSy..."
                       class="w-full px-3.5 py-2 rounded-[var(--radius-md)] bg-[var(--bg-raised)] border border-[var(--border-strong)] text-[var(--text)] placeholder-[var(--text-faint)] focus:outline-none focus:border-[var(--accent)] focus:ring-1 focus:ring-[var(--accent)] text-xs font-mono"
                       style="outline: none !important;" />
            </div>

            <!-- Test Connection Result Banner -->
            <template x-if="testResult">
                <div class="mb-4 p-3 rounded-[var(--radius-md)] text-xs border"
                     :class="testResult.status === 'success' ? 'bg-[var(--success-bg)] text-[var(--success-text)] border-[var(--success-border)]' : 'bg-[var(--error-bg)] text-[var(--error-text)] border-[var(--error-border)]'">
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-sm" x-text="testResult.status === 'success' ? '✓' : '⚠️'"></span>
                        <div class="flex-1 space-y-1">
                            <div class="font-medium" x-text="testResult.status === 'success' ? 'Connection Verified' : 'Google API Rejection (HTTP ' + (testResult.code || 'Error') + ')'"></div>
                            <p class="font-mono text-[11px] leading-relaxed break-words" x-text="testResult.message"></p>
                            <template x-if="testResult.code === 402">
                                <div class="pt-1.5 mt-1 border-t border-[var(--error-border)]/50">
                                    <a href="https://ai.studio/projects" target="_blank" rel="noopener" class="underline font-sans text-xs font-medium">Manage project billing on Google AI Studio →</a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

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
                        Close
                    </button>
                    <button @click="saveApiKey()" 
                            :disabled="isTestingKey"
                            type="button" 
                            class="btn-primary !h-8 !px-3 !text-xs">
                        <span x-text="isTestingKey ? 'Verifying...' : 'Save & verify key'"></span>
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
                          class="w-full px-3.5 py-2 rounded-[var(--radius-md)] bg-[var(--bg-raised)] border border-[var(--border-strong)] text-[var(--text)] placeholder-[var(--text-faint)] focus:outline-none focus:border-[var(--accent)] text-xs sm:text-sm leading-relaxed"
                          style="outline: none !important;"></textarea>
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

    <!-- ========================================== -->
    <!-- INTERROGATION CO-PILOT CHAT DRAWER         -->
    <!-- ========================================== -->
    <div x-show="isDialogueOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden flex justify-end"
         @keydown.escape.window="closeDialogue()">
        
        <!-- Scrim -->
        <div class="fixed inset-0 bg-[rgba(31,30,29,0.3)] transition-opacity"
             @click="closeDialogue()"></div>

        <!-- Dialogue Panel -->
        <div class="relative w-full max-w-lg bg-[var(--bg-raised)] border-l border-[var(--border)] shadow-[var(--shadow-3)] h-full flex flex-col z-10">
            
            <!-- Drawer Header -->
            <div class="p-4 sm:p-5 border-b border-[var(--border)] flex items-center justify-between gap-3 bg-[var(--bg-sunken)]">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full"
                              :class="{
                                  'bg-[var(--success-text)]': apiStatus === 'connected',
                                  'bg-[var(--error-text)] animate-pulse': apiStatus === 'error',
                                  'bg-[var(--warning-text)]': apiStatus === 'untested' || apiStatus === 'testing'
                              }"></span>
                        <h3 class="font-serif text-lg font-normal text-[var(--text)]">Founder Interrogation Co-Pilot</h3>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-[var(--text-muted)] font-mono" x-text="'Context: ' + totalCount + ' blocks injected'"></span>
                        <span class="text-[var(--text-faint)]">·</span>
                        <button @click="showKeyModal = true" 
                                type="button" 
                                class="text-[11px] font-mono hover:underline"
                                :class="apiStatus === 'connected' ? 'text-[var(--success-text)]' : (apiStatus === 'error' ? 'text-[var(--error-text)] font-semibold' : 'text-[var(--warning-text)]')"
                                x-text="apiStatus === 'connected' ? '● Live Gemini (3.8-flash)' : (apiStatus === 'error' ? '● Gemini Error (Fix key)' : '● Offline Simulator')"></button>
                    </div>
                </div>
                <button @click="closeDialogue()" class="btn-ghost !h-7 !px-2 text-xs">✕</button>
            </div>

            <!-- Messages Stream -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4">
                <template x-for="(msg, mIndex) in dialogueMessages" :key="mIndex">
                    <div class="space-y-2">
                        <!-- Message Bubble -->
                        <div :class="msg.is_error ? 'mr-4 bg-[var(--error-bg)] border border-[var(--error-border)] p-4 rounded-[var(--radius-md)] text-xs sm:text-sm text-[var(--error-text)] font-sans' : (msg.role === 'user' ? 'ml-8 bg-[var(--bg-sunken)] p-3.5 rounded-[var(--radius-md)] text-xs sm:text-sm text-[var(--text)]' : 'mr-4 bg-[var(--bg-raised)] border border-[var(--border)] p-4 rounded-[var(--radius-md)] text-xs sm:text-sm font-serif leading-relaxed text-[var(--text)]')">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-[10px] uppercase font-mono tracking-wider text-[var(--text-faint)]" 
                                     x-text="msg.is_error ? 'System Alert' : (msg.role === 'user' ? 'You (Founder)' : 'Epistemological Co-Founder')"></div>
                                <template x-if="msg.is_error">
                                    <button @click="showKeyModal = true" type="button" class="text-[11px] underline font-sans text-[var(--error-text)] hover:opacity-80">Fix API Key →</button>
                                </template>
                            </div>
                            <p class="whitespace-pre-line" x-text="msg.content"></p>
                        </div>

                        <!-- Proposed Block Card (If Model generated one) -->
                        <template x-if="msg.proposed_block">
                            <div class="p-4 rounded-[var(--radius-md)] bg-[var(--bg-sunken)] border border-[var(--border-strong)] space-y-2 text-xs sm:text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-sans text-[11px] font-medium text-[var(--accent)] flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)]"></span>
                                        <span>Proposed Story Block</span>
                                    </span>
                                    <span class="text-[10px] font-mono text-[var(--text-faint)]" 
                                          x-text="msg.proposed_block.branch_type || 'hypothesis'"></span>
                                </div>

                                <h4 class="font-serif text-base font-normal text-[var(--text)]" 
                                    x-text="msg.proposed_block.title"></h4>
                                <p class="font-serif italic text-xs text-[var(--text-muted)]" 
                                   x-text="msg.proposed_block.hypothesis"></p>

                                <div class="pt-2 flex items-center justify-between border-t border-[var(--border)]">
                                    <span class="text-[11px] text-[var(--text-faint)]" 
                                          x-text="msg.proposed_block.connection_reason || 'Connected hypothesis'"></span>
                                    <button @click="acceptProposedBlock(msg.proposed_block)"
                                            type="button"
                                            class="btn-primary !h-7 !px-3 !text-xs">
                                        <span>Add to canvas 🧱</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div x-show="isDialogueSending" class="flex items-center gap-2 text-xs text-[var(--text-muted)] font-serif italic p-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)] animate-ping"></span>
                    <span>Interrogating assumptions...</span>
                </div>
            </div>

            <!-- Dialogue Input Bar -->
            <div class="p-3 sm:p-4 border-t border-[var(--border)] bg-[var(--bg-sunken)]">
                <form @submit.prevent="sendDialogueMessage()" class="space-y-2">
                    <textarea x-model="dialogueInput"
                              @keydown.enter.prevent="if(!$event.shiftKey) sendDialogueMessage()"
                              rows="2"
                              placeholder="Answer questions, debate assumptions, or explore new ideas..."
                              class="w-full px-3 py-2 rounded-[var(--radius-md)] bg-[var(--bg-raised)] border border-[var(--border-strong)] text-[var(--text)] placeholder-[var(--text-faint)] focus:outline-none focus:border-[var(--accent)] text-xs sm:text-sm resize-none"
                              style="outline: none !important;"></textarea>
                    
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[10px] text-[var(--text-faint)]">Press Enter to send</span>
                        <button type="submit"
                                :disabled="!dialogueInput.trim() || isDialogueSending"
                                class="btn-primary !h-7 !px-3 !text-xs">
                            <span>Send response</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-[var(--border)] bg-[var(--bg)] py-8 text-center text-xs text-[var(--text-muted)] mt-12">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
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
