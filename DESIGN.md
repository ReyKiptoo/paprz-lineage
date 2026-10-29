# DESIGN.md

> A design system for calm, simple, professional interfaces in the spirit of Claude's own product design: warm neutrals, a literary serif for voice, a quiet sans for function, one restrained accent, and generous space.
>
> Drop this file in the root of a repo or into a Claude Project's knowledge. When building any UI, read it first and follow it. Where this file and a specific request disagree, the request wins.

---

## 1. Design philosophy

**One sentence:** An interface that feels like a well-made notebook, not a dashboard.

### Core principles

1. **Calm over clever.** Nothing blinks, bounces, or competes. The user's content (their words, their data, their work) is always the loudest thing on screen.
2. **Warmth, not sterility.** Neutrals lean warm (cream, stone, charcoal) rather than blue-grey or pure white/black. The product should feel human and considered.
3. **Typography is the interface.** Most of the design is the quality of the type, the line length, and the spacing. If the type is right, little else is needed.
4. **One accent, used rarely.** A single warm clay color marks the primary action and the brand moment. If everything is highlighted, nothing is.
5. **Structure carries meaning.** A border, divider, or background change exists because it separates two different things. Never decorate.
6. **Quiet confidence in copy.** Short, plain, specific, sentence case. No hype, no exclamation marks, no filler.
7. **Restraint is the feature.** Before shipping, remove one element. Then check again.

### What it should feel like

Unhurried. Trustworthy. Intelligent but not cold. Closer to a good book or a well-edited memo than a SaaS landing page.

---

## 2. Color

### 2.1 Palette (light theme)

| Token | Hex | Role |
|---|---|---|
| `--bg` | `#FAF9F5` | Page background (warm ivory) |
| `--bg-raised` | `#FFFFFF` | Inputs, modals, popovers (the only pure white) |
| `--bg-sunken` | `#F0EEE6` | Sidebars, code blocks, secondary surfaces, hover fills |
| `--bg-tint` | `#E8E6DC` | Pressed states, selected rows, chips |
| `--border` | `#E3E0D5` | Default hairline borders |
| `--border-strong` | `#CFCBBC` | Input borders, emphasized dividers |
| `--text` | `#1F1E1D` | Primary text (warm near-black, never `#000`) |
| `--text-muted` | `#5E5D59` | Secondary text, descriptions |
| `--text-faint` | `#8A8880` | Placeholders, timestamps, disabled |
| `--accent` | `#C6613F` | Primary buttons, key links, focus emphasis (clay) |
| `--accent-hover` | `#B0532F` | Accent hover/pressed |
| `--accent-soft` | `#F5E3DA` | Accent tinted backgrounds (selected, notice) |
| `--accent-contrast` | `#FFFFFF` | Text on accent |

### 2.2 Palette (dark theme)

| Token | Hex | Role |
|---|---|---|
| `--bg` | `#262624` | Page background (warm charcoal, not black) |
| `--bg-raised` | `#30302E` | Inputs, modals, popovers |
| `--bg-sunken` | `#1F1E1D` | Sidebars, code blocks |
| `--bg-tint` | `#3A3936` | Hover, selected |
| `--border` | `#3D3C39` | Hairlines |
| `--border-strong` | `#55534E` | Input borders |
| `--text` | `#F2F0E8` | Primary text |
| `--text-muted` | `#B5B2A8` | Secondary text |
| `--text-faint` | `#807D74` | Placeholders |
| `--accent` | `#D97757` | Primary actions (slightly lighter for dark) |
| `--accent-hover` | `#E48A6C` | Hover |
| `--accent-soft` | `#43302A` | Accent tinted backgrounds |
| `--accent-contrast` | `#1F1E1D` | Text on accent |

### 2.3 Semantic colors (muted, never neon)

| State | Light text / bg | Dark text / bg |
|---|---|---|
| Success | `#3D6B4A` / `#E6EFE7` | `#8FC29C` / `#26352B` |
| Warning | `#8A5A12` / `#F6EAD2` | `#E0B56A` / `#38301F` |
| Error | `#A63A2B` / `#F6E1DC` | `#E58B7C` / `#3B2622` |
| Info | `#3F5F7F` / `#E3EBF2` | `#8FB0D1` / `#242F3B` |

### 2.4 Color rules

- Backgrounds are warm. Never use cool grey (`#F3F4F6`-style) or pure `#000` / `#FFF` as the page color.
- The accent covers **under 5%** of any screen: the primary button, a selected indicator, a focus ring, an inline link. Never a full-width banner, never a gradient.
- No gradients, glows, or colored shadows. Flat color only.
- Text contrast: body text must meet WCAG AA (4.5:1). Muted text at least 4.5:1 on its background; faint text is for non-essential hints only (3:1 minimum).
- Never convey state by color alone. Pair with an icon or label.

---

## 3. Typography

### 3.1 Typefaces

Two families with distinct jobs:

| Role | Family | Fallback stack |
|---|---|---|
| **Voice** (headlines, long-form reading, empty states, the product's "speaking") | A refined text serif. Recommended: `Source Serif 4`, `Newsreader`, or `Tiempos Text` if licensed | `Georgia, "Times New Roman", serif` |
| **Function** (UI, labels, buttons, nav, tables, forms) | A neutral humanist/grotesque sans. Recommended: `Inter`, `Geist`, or `Styrene` if licensed | `system-ui, -apple-system, "Segoe UI", Roboto, sans-serif` |
| **Code** | `JetBrains Mono` or `Berkeley Mono` | `ui-monospace, "SF Mono", Menlo, Consolas, monospace` |

**Rule of thumb:** If a human is *reading*, use serif. If a human is *operating*, use sans. Don't mix them within one component except a serif heading over sans body copy.

### 3.2 Type scale (modular, ~1.25 ratio)

| Token | Size / line-height | Weight | Family | Use |
|---|---|---|---|---|
| `display` | 48px / 1.1 | 400 | Serif | Hero or landing headline only |
| `h1` | 36px / 1.15 | 400 | Serif | Page title |
| `h2` | 28px / 1.25 | 400 | Serif | Section title |
| `h3` | 20px / 1.35 | 500 | Serif | Subsection |
| `body-lg` | 18px / 1.65 | 400 | Serif | Long-form reading |
| `body` | 16px / 1.6 | 400 | Sans | Default UI text |
| `body-sm` | 14px / 1.5 | 400 | Sans | Secondary text, table cells |
| `caption` | 12.5px / 1.4 | 400 | Sans | Timestamps, helper text |
| `button` | 14px / 1 | 500 | Sans | Buttons, tabs |
| `code` | 13.5px / 1.6 | 400 | Mono | Inline and block code |

On screens under 640px, scale `display` to 36px, `h1` to 30px, `h2` to 24px.

---

## 11. Implementation tokens (copy-paste)

```css
:root {
  /* color */
  --bg: #FAF9F5;
  --bg-raised: #FFFFFF;
  --bg-sunken: #F0EEE6;
  --bg-tint: #E8E6DC;
  --border: #E3E0D5;
  --border-strong: #CFCBBC;
  --text: #1F1E1D;
  --text-muted: #5E5D59;
  --text-faint: #8A8880;
  --accent: #C6613F;
  --accent-hover: #B0532F;
  --accent-soft: #F5E3DA;
  --accent-contrast: #FFFFFF;

  /* type */
  --font-serif: "Newsreader", "Source Serif 4", Georgia, "Times New Roman", serif;
  --font-sans: Inter, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  --font-mono: "JetBrains Mono", ui-monospace, "SF Mono", Menlo, Consolas, monospace;

  /* space */
  --space-1: 4px;  --space-2: 8px;   --space-3: 12px; --space-4: 16px;
  --space-5: 24px; --space-6: 32px;  --space-7: 48px; --space-8: 64px;
  --space-9: 96px; --space-10: 128px;

  /* shape */
  --radius-sm: 6px; --radius-md: 10px; --radius-lg: 14px;
  --radius-xl: 22px; --radius-full: 9999px;

  /* elevation */
  --shadow-1: 0 1px 2px rgba(31,30,29,0.06);
  --shadow-2: 0 4px 16px rgba(31,30,29,0.08), 0 0 0 1px var(--border);
  --shadow-3: 0 12px 40px rgba(31,30,29,0.14), 0 0 0 1px var(--border);

  /* motion */
  --ease: cubic-bezier(0.2, 0, 0, 1);
  --dur-fast: 120ms; --dur-base: 200ms; --dur-slow: 320ms;

  color-scheme: light;
}
```
