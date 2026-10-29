/**
 * Paprz Lineage - Startup Epistemology & Hypothesis Thought Engine
 * First-Principles Business & Technical Building Blocks powered by Gemini API
 */

export function ventureCanvasApp(initialBlocks = []) {
    return {
        // State
        blocks: [],
        activeBlockId: null,
        inputPrompt: '',
        isLoading: false,
        loadingMessage: 'Synthesizing venture building block...',

        // Gemini API Configuration
        apiKey: localStorage.getItem('paprz_gemini_api_key') || '',
        showKeyModal: false,
        tempApiKey: '',
        keyNotice: '',

        // Challenge Modal
        showChallengeModal: false,
        challengingBlock: null,
        challengeInput: '',
        isChallenging: false,
        challengeResult: null,

        // Filters & Views
        activeCategory: 'all',
        connectors: [],

        init() {
            this.tempApiKey = this.apiKey;
            
            // Try loading from localStorage first, else use pre-seeded foundations
            const saved = localStorage.getItem('paprz_inquiry_blocks');
            if (saved) {
                try {
                    const parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        this.blocks = parsed;
                    } else {
                        this.blocks = initialBlocks;
                    }
                } catch (e) {
                    this.blocks = initialBlocks;
                }
            } else {
                this.blocks = initialBlocks;
            }

            // Bind window resize for SVG connector paths
            window.addEventListener('resize', () => {
                this.$nextTick(() => this.updateConnectors());
            });

            this.$watch('blocks', () => {
                this.$nextTick(() => this.updateConnectors());
            });

            this.$nextTick(() => {
                this.updateConnectors();
            });
        },

        // API Key Management
        saveApiKey() {
            const cleanKey = this.tempApiKey.trim();
            this.apiKey = cleanKey;
            if (cleanKey) {
                localStorage.setItem('paprz_gemini_api_key', cleanKey);
                this.keyNotice = 'API key saved to browser memory!';
            } else {
                localStorage.removeItem('paprz_gemini_api_key');
                this.keyNotice = 'API key removed. Running in heuristic mode.';
            }
            setTimeout(() => {
                this.showKeyModal = false;
                this.keyNotice = '';
            }, 1200);
        },

        clearApiKey() {
            this.apiKey = '';
            this.tempApiKey = '';
            localStorage.removeItem('paprz_gemini_api_key');
            this.keyNotice = 'Key removed from browser memory.';
            setTimeout(() => {
                this.showKeyModal = false;
                this.keyNotice = '';
            }, 1200);
        },

        // Quick starter prompts
        startWithStarter(starter) {
            this.inputPrompt = starter.question;
            this.forgeNewInquiry(null);
        },

        // Forge a new startup inquiry block
        async forgeNewInquiry(parentBlock = null, overridePrompt = null) {
            const prompt = (overridePrompt || this.inputPrompt).trim();
            if (!prompt) return;

            this.isLoading = true;
            this.loadingMessage = parentBlock
                ? `Branching from validated premise: "${parentBlock.title}"...`
                : 'Interrogating business model: synthesizing hypothesis & experiment...';

            let priors = [];
            let parentId = null;
            if (parentBlock) {
                parentId = parentBlock.id;
                priors = [
                    ...(parentBlock.things_we_know || []),
                    `Established Truth: ${parentBlock.results.lesson}`
                ];
            }

            try {
                const response = await fetch('/api/inquiry/forge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Gemini-Key': this.apiKey || ''
                    },
                    body: JSON.stringify({
                        prompt: prompt,
                        priors: priors,
                        parent_id: parentId
                    })
                });

                if (!response.ok) {
                    throw new Error(`Server returned HTTP ${response.status}`);
                }

                const data = await response.json();
                if (data.status === 'success' && data.block) {
                    const block = data.block;
                    block.isExpanded = true;

                    this.blocks.push(block);
                    this.activeBlockId = block.id;
                    this.inputPrompt = '';

                    this.persistBlocks();

                    this.$nextTick(() => {
                        const el = document.getElementById(block.id);
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        this.updateConnectors();
                    });
                }
            } catch (err) {
                console.error('Forge error:', err);
                alert('Could not forge block: ' + err.message);
            } finally {
                this.isLoading = false;
            }
        },

        // Branching from next paths
        branchFromNextPath(parentBlock, pathOption) {
            this.forgeNewInquiry(parentBlock, pathOption.question);
        },

        // Seal as Proven Foundation 🧱
        sealBlock(block) {
            block.sealed = true;
            block.status = 'sealed';
            block.sealed_at = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            this.persistBlocks();
            this.$nextTick(() => this.updateConnectors());
        },

        // Challenge an established block ⚡
        openChallenge(block) {
            this.challengingBlock = block;
            this.challengeInput = '';
            this.challengeResult = null;
            this.showChallengeModal = true;
        },

        async submitChallenge() {
            if (!this.challengeInput.trim() || !this.challengingBlock) return;

            this.isChallenging = true;
            try {
                const response = await fetch('/api/inquiry/challenge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Gemini-Key': this.apiKey || ''
                    },
                    body: JSON.stringify({
                        block: this.challengingBlock,
                        counter_evidence: this.challengeInput
                    })
                });

                const data = await response.json();
                if (data.status === 'success' && data.evaluation) {
                    this.challengeResult = data.evaluation;
                    
                    const target = this.blocks.find(b => b.id === this.challengingBlock.id);
                    if (target) {
                        target.challenged = true;
                        target.challenge_notes = data.evaluation;
                        target.status = data.evaluation.verdict === 'fractured' ? 'fractured' : 'refined';
                        
                        this.flagDescendants(target.id, data.evaluation.verdict);
                    }
                    this.persistBlocks();
                }
            } catch (err) {
                console.error('Challenge error:', err);
                alert('Challenge evaluation error: ' + err.message);
            } finally {
                this.isChallenging = false;
            }
        },

        flagDescendants(parentId, verdict) {
            this.blocks.forEach(b => {
                if (b.parent_id === parentId) {
                    b.upstreamAlert = `Foundation questioned: Upstream truth "${this.challengingBlock?.title}" was assessed as ${verdict}.`;
                    this.flagDescendants(b.id, verdict);
                }
            });
        },

        deleteBlock(blockId) {
            if (!confirm('Remove this building block from the inquiry tree?')) return;
            this.blocks = this.blocks.filter(b => b.id !== blockId);
            this.persistBlocks();
            this.$nextTick(() => this.updateConnectors());
        },

        resetToFoundations(initialBlocks) {
            if (!confirm('Reset canvas back to the 6 initial Paperz foundational blocks?')) return;
            this.blocks = JSON.parse(JSON.stringify(initialBlocks));
            this.persistBlocks();
            this.$nextTick(() => this.updateConnectors());
        },

        persistBlocks() {
            try {
                localStorage.setItem('paprz_inquiry_blocks', JSON.stringify(this.blocks));
            } catch (e) {
                console.warn('LocalStorage save error:', e);
            }
        },

        // Dynamic SVG Bezier paths joining parent blocks to child blocks
        updateConnectors() {
            const container = document.getElementById('canvas-container');
            if (!container) return;

            const containerRect = container.getBoundingClientRect();
            const newConnectors = [];

            this.blocks.forEach(child => {
                if (!child.parent_id) return;
                const parentEl = document.getElementById(child.parent_id);
                const childEl = document.getElementById(child.id);

                if (parentEl && childEl) {
                    const pRect = parentEl.getBoundingClientRect();
                    const cRect = childEl.getBoundingClientRect();

                    const x1 = pRect.left + pRect.width / 2 - containerRect.left;
                    const y1 = pRect.bottom - containerRect.top;

                    const x2 = cRect.left + cRect.width / 2 - containerRect.left;
                    const y2 = cRect.top - containerRect.top;

                    const dy = Math.max(30, (y2 - y1) * 0.45);
                    const pathData = `M ${x1} ${y1} C ${x1} ${y1 + dy}, ${x2} ${y2 - dy}, ${x2} ${y2}`;

                    const isChallenged = child.upstreamAlert || parentEl.classList.contains('border-red-500');

                    newConnectors.push({
                        id: `${child.parent_id}->${child.id}`,
                        d: pathData,
                        isChallenged: !!isChallenged
                    });
                }
            });

            this.connectors = newConnectors;
        },

        get sealedCount() {
            return this.blocks.filter(b => b.sealed).length;
        },

        get totalCount() {
            return this.blocks.length;
        }
    };
}
