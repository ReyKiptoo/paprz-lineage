/**
 * Paprz Lineage - Startup Epistemology & Hypothesis Thought Engine
 * Designed according to DESIGN.md: calm, warm neutrals, literary voice, quiet restraint.
 * Interactive tiny story cards with colored connection lines and 5-facet inspector drawer.
 */

export function ventureCanvasApp(initialBlocks = []) {
    return {
        // State
        blocks: [],
        activeBlockId: null,
        selectedBlockId: null,
        isInspectorOpen: false,
        inputPrompt: '',
        isLoading: false,
        loadingMessage: 'Synthesizing building block...',
        toastMessage: '',

        // Filters
        filterStatus: 'all', // 'all' | 'draft' | 'sealed' | 'challenged'

        // Theme
        theme: localStorage.getItem('paprz_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),

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

        // Branching Prompt Modal from Inspector
        showBranchModal: false,
        branchParentBlock: null,
        branchCustomPrompt: '',
        branchType: 'validate', // 'validate' | 'horizon' | 'paradox' | 'finding'

        // Dialogue / Interrogation Co-pilot State
        isDialogueOpen: false,
        dialogueMessages: [
            {
                role: 'model',
                content: "I am your Epistemological Co-Founder. I have your full canvas in context:\n• Block 01: The Liquidity & Distribution Bottleneck\n• Block 02: The Per-Item Micro-Pricing Engine (KES 3 CATs / KES 5 Exams)\n• Block 03: The Web-to-Mobile Anti-Piracy Funnel\n\nLet's interrogate: When charging KES 3 for a CAT, how do you handle Safaricom STK push fee overhead and PIN fatigue? Are you doing per-paper prompts or an in-app prepaid coin wallet?",
                proposed_block: null
            }
        ],
        dialogueInput: '',
        isDialogueSending: false,

        // SVG Connectors
        connectors: [],

        init() {
            this.applyTheme(this.theme);
            this.tempApiKey = this.apiKey;
            
            // Try loading from localStorage, and ensure all server initialBlocks are merged in!
            const saved = localStorage.getItem('paprz_inquiry_blocks');
            if (saved) {
                try {
                    const parsed = JSON.parse(saved);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        const existingIds = new Set(parsed.map(b => b.id));
                        const missingInitial = initialBlocks.filter(b => !existingIds.has(b.id));
                        this.blocks = [...parsed, ...missingInitial];
                    } else {
                        this.blocks = initialBlocks;
                    }
                } catch (e) {
                    this.blocks = initialBlocks;
                }
            } else {
                this.blocks = initialBlocks;
            }

            // Window resize for SVG connector paths
            window.addEventListener('resize', () => {
                this.$nextTick(() => this.updateConnectors());
            });

            this.$watch('blocks', () => {
                this.$nextTick(() => this.updateConnectors());
            });

            this.$nextTick(() => {
                this.updateConnectors();
                if (this.blocks.length > 0 && !this.selectedBlockId) {
                    this.selectedBlockId = this.blocks[0].id;
                }
            });
        },

        // Dialogue actions
        openDialogue() {
            this.isDialogueOpen = true;
        },

        closeDialogue() {
            this.isDialogueOpen = false;
        },

        async sendDialogueMessage() {
            const text = this.dialogueInput.trim();
            if (!text || this.isDialogueSending) return;

            this.dialogueMessages.push({
                role: 'user',
                content: text
            });
            this.dialogueInput = '';
            this.isDialogueSending = true;

            try {
                const response = await fetch('/api/inquiry/dialogue', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Gemini-Key': this.apiKey || ''
                    },
                    body: JSON.stringify({
                        messages: this.dialogueMessages,
                        canvas_blocks: this.blocks
                    })
                });

                if (!response.ok) {
                    throw new Error(`Server returned HTTP ${response.status}`);
                }

                const data = await response.json();
                if (data.status === 'success') {
                    this.dialogueMessages.push({
                        role: 'model',
                        content: data.reply,
                        proposed_block: data.proposed_block || null
                    });
                }
            } catch (err) {
                console.error('Dialogue error:', err);
                this.dialogueMessages.push({
                    role: 'model',
                    content: 'Error during dialogue: ' + err.message,
                    proposed_block: null
                });
            } finally {
                this.isDialogueSending = false;
            }
        },

        acceptProposedBlock(proposedBlock) {
            if (!proposedBlock) return;
            const newBlock = { ...proposedBlock };
            if (!newBlock.id) newBlock.id = 'blk_v_' + Math.random().toString(36).substring(2, 9);
            newBlock.status = 'draft';
            newBlock.sealed = false;

            this.blocks.push(newBlock);
            this.selectedBlockId = newBlock.id;
            this.persistBlocks();

            this.$nextTick(() => {
                const el = document.getElementById('card-' + newBlock.id);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.updateConnectors();
            });

            this.showToast(`Forged proposed block: "${newBlock.title}"`);
        },

        // Theme management
        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            this.applyTheme(this.theme);
            localStorage.setItem('paprz_theme', this.theme);
            this.$nextTick(() => this.updateConnectors());
        },

        applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
        },

        showToast(msg) {
            this.toastMessage = msg;
            setTimeout(() => {
                if (this.toastMessage === msg) this.toastMessage = '';
            }, 3000);
        },

        // Block selection & Inspector Drawer
        get selectedBlock() {
            return this.blocks.find(b => b.id === this.selectedBlockId) || null;
        },

        selectBlock(blockId) {
            this.selectedBlockId = blockId;
            this.isInspectorOpen = true;
            this.$nextTick(() => this.updateConnectors());
        },

        closeInspector() {
            this.isInspectorOpen = false;
        },

        // API Key Management
        saveApiKey() {
            const cleanKey = this.tempApiKey.trim();
            this.apiKey = cleanKey;
            if (cleanKey) {
                localStorage.setItem('paprz_gemini_api_key', cleanKey);
                this.keyNotice = 'API key saved.';
            } else {
                localStorage.removeItem('paprz_gemini_api_key');
                this.keyNotice = 'API key removed. Running in offline mode.';
            }
            setTimeout(() => {
                this.showKeyModal = false;
                this.keyNotice = '';
            }, 1000);
        },

        clearApiKey() {
            this.apiKey = '';
            this.tempApiKey = '';
            localStorage.removeItem('paprz_gemini_api_key');
            this.keyNotice = 'API key removed.';
            setTimeout(() => {
                this.showKeyModal = false;
                this.keyNotice = '';
            }, 1000);
        },

        // Quick starter prompts
        startWithStarter(starter) {
            this.inputPrompt = starter.question;
            this.forgeNewInquiry(null, null, 'genesis', 'Initial inquiry starter');
        },

        // Forge a new startup inquiry block
        async forgeNewInquiry(parentBlock = null, overridePrompt = null, branchType = null, connectionReason = null) {
            const prompt = (overridePrompt || this.inputPrompt).trim();
            if (!prompt) return;

            this.isLoading = true;
            this.loadingMessage = parentBlock
                ? `Branching from "${parentBlock.title}"...`
                : 'Synthesizing hypothesis, priors, and experiment...';

            let priors = [];
            let parentId = null;
            if (parentBlock) {
                parentId = parentBlock.id;
                priors = [
                    ...(parentBlock.things_we_know || []),
                    `Established premise: ${parentBlock.results.lesson}`
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
                        parent_id: parentId,
                        branch_type: branchType,
                        connection_reason: connectionReason
                    })
                });

                if (!response.ok) {
                    throw new Error(`Server returned HTTP ${response.status}`);
                }

                const data = await response.json();
                if (data.status === 'success' && data.block) {
                    const block = data.block;
                    block.branch_type = branchType || block.branch_type || (parentBlock ? 'validate' : 'genesis');
                    block.connection_reason = connectionReason || block.connection_reason || (parentBlock ? `Branch from: ${parentBlock.title}` : 'Genesis premise');

                    // All new blocks start in draft state
                    block.status = 'draft';
                    block.sealed = false;

                    this.blocks.push(block);
                    this.selectedBlockId = block.id;
                    this.isInspectorOpen = true;
                    this.inputPrompt = '';
                    this.showBranchModal = false;

                    this.persistBlocks();

                    this.$nextTick(() => {
                        const el = document.getElementById('card-' + block.id);
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        this.updateConnectors();
                    });

                    this.showToast('New story block forged in draft state.');
                }
            } catch (err) {
                console.error('Forge error:', err);
                alert('Could not forge block: ' + err.message);
            } finally {
                this.isLoading = false;
            }
        },

        // Branching from next paths (Facet 5)
        branchFromNextPath(parentBlock, pathOption) {
            this.forgeNewInquiry(
                parentBlock, 
                pathOption.question, 
                pathOption.type, 
                `${pathOption.label}: ${pathOption.teaser}`
            );
        },

        // Custom branch prompt
        openBranchModal(parentBlock) {
            this.branchParentBlock = parentBlock;
            this.branchCustomPrompt = '';
            this.branchType = 'validate';
            this.showBranchModal = true;
        },

        submitCustomBranch() {
            if (!this.branchCustomPrompt.trim() || !this.branchParentBlock) return;
            this.forgeNewInquiry(
                this.branchParentBlock,
                this.branchCustomPrompt,
                this.branchType,
                `Derived from: ${this.branchParentBlock.title}`
            );
        },

        // Seal as Proven Truth 🧱
        sealBlock(block) {
            block.sealed = true;
            block.status = 'sealed';
            block.sealed_at = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            this.persistBlocks();
            this.$nextTick(() => this.updateConnectors());
            this.showToast(`Sealed "${block.title}" as proven truth.`);
        },

        // Unseal back to draft if more testing needed
        unsealBlock(block) {
            block.sealed = false;
            block.status = 'draft';
            block.sealed_at = null;
            this.persistBlocks();
            this.$nextTick(() => this.updateConnectors());
            this.showToast(`Reset "${block.title}" to draft.`);
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
                    this.$nextTick(() => this.updateConnectors());
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
                    b.upstreamAlert = `Upstream premise "${this.challengingBlock?.title}" was assessed as ${verdict}.`;
                    this.flagDescendants(b.id, verdict);
                }
            });
        },

        // Consolidate multiple proven blocks into one overarching Macro Truth
        async synthesizeMacro() {
            const proven = this.blocks.filter(b => b.sealed);
            if (proven.length < 2) {
                alert('You need at least 2 proven blocks to synthesize an overarching Macro Truth.');
                return;
            }

            if (!confirm(`Synthesize ${proven.length} proven blocks into one overarching Macro Truth milestone?`)) {
                return;
            }

            this.isLoading = true;
            this.loadingMessage = 'Synthesizing overarching Macro Truth milestone...';

            try {
                const response = await fetch('/api/inquiry/synthesize-macro', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Gemini-Key': this.apiKey || ''
                    },
                    body: JSON.stringify({
                        blocks: proven
                    })
                });

                const data = await response.json();
                if (data.status === 'success' && data.macro) {
                    this.blocks.push(data.macro);
                    this.selectedBlockId = data.macro.id;
                    this.isInspectorOpen = true;
                    this.persistBlocks();
                    this.$nextTick(() => this.updateConnectors());
                    this.showToast('Macro Truth Milestone synthesized.');
                }
            } catch (err) {
                console.error('Macro synthesis error:', err);
                alert('Could not synthesize macro truth: ' + err.message);
            } finally {
                this.isLoading = false;
            }
        },

        // Permanently save current tree to disk
        async saveToBedrock() {
            try {
                const response = await fetch('/api/inquiry/save-bedrock', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        blocks: this.blocks
                    })
                });

                if (response.ok) {
                    this.showToast('Lineage tree saved permanently to server bedrock.');
                } else {
                    throw new Error('Server error while saving bedrock.');
                }
            } catch (err) {
                console.error('Save error:', err);
                alert('Could not save to bedrock: ' + err.message);
            }
        },

        deleteBlock(blockId) {
            if (!confirm('Remove this building block?')) return;
            this.blocks = this.blocks.filter(b => b.id !== blockId);
            if (this.selectedBlockId === blockId) {
                this.selectedBlockId = this.blocks[0]?.id || null;
            }
            this.persistBlocks();
            this.$nextTick(() => this.updateConnectors());
        },

        resetToFoundations(initialBlocks) {
            if (!confirm('Reset canvas back to the genesis bedrock?')) return;
            this.blocks = JSON.parse(JSON.stringify(initialBlocks));
            this.selectedBlockId = this.blocks[0]?.id || null;
            this.persistBlocks();
            this.$nextTick(() => this.updateConnectors());
            this.showToast('Canvas reset to genesis bedrock.');
        },

        persistBlocks() {
            try {
                localStorage.setItem('paprz_inquiry_blocks', JSON.stringify(this.blocks));
            } catch (e) {
                console.warn('LocalStorage save error:', e);
            }
        },

        // Dynamic SVG Bezier paths joining parent blocks to child blocks with reason tags
        updateConnectors() {
            const container = document.getElementById('canvas-container');
            if (!container) return;

            const containerRect = container.getBoundingClientRect();
            const newConnectors = [];

            this.blocks.forEach(child => {
                if (!child.parent_id) return;
                const parentEl = document.getElementById('card-' + child.parent_id);
                const childEl = document.getElementById('card-' + child.id);

                if (parentEl && childEl) {
                    const pRect = parentEl.getBoundingClientRect();
                    const cRect = childEl.getBoundingClientRect();

                    const x1 = pRect.left + pRect.width / 2 - containerRect.left;
                    const y1 = pRect.bottom - containerRect.top;

                    const x2 = cRect.left + cRect.width / 2 - containerRect.left;
                    const y2 = cRect.top - containerRect.top;

                    const dy = Math.max(30, (y2 - y1) * 0.45);
                    const pathData = `M ${x1} ${y1} C ${x1} ${y1 + dy}, ${x2} ${y2 - dy}, ${x2} ${y2}`;

                    const mx = (x1 + x2) / 2;
                    const my = (y1 + y2) / 2;

                    const isChallenged = child.upstreamAlert || child.challenged;
                    const branchType = child.branch_type || 'validate';

                    newConnectors.push({
                        id: `${child.parent_id}->${child.id}`,
                        d: pathData,
                        mx: mx,
                        my: my,
                        branch_type: branchType,
                        connection_reason: child.connection_reason || branchType,
                        isChallenged: !!isChallenged
                    });
                }
            });

            this.connectors = newConnectors;
        },

        get sealedCount() {
            return this.blocks.filter(b => b.sealed).length;
        },

        get draftCount() {
            return this.blocks.filter(b => !b.sealed).length;
        },

        get totalCount() {
            return this.blocks.length;
        },

        get filteredBlocks() {
            if (this.filterStatus === 'draft') return this.blocks.filter(b => !b.sealed);
            if (this.filterStatus === 'sealed') return this.blocks.filter(b => b.sealed);
            if (this.filterStatus === 'challenged') return this.blocks.filter(b => b.challenged);
            return this.blocks;
        }
    };
}
