# Paprz Venture Manifesto & Master Blueprint

## 1. Executive Vision
**Paprz** is an academic past exam paper archive, revision engine, and academic solution marketplace for university students, launching first at **JKUAT (Jomo Kenyatta University of Agriculture and Technology)** with a focus on the **School of Engineering**.

---

## 2. Core Problem & Market Reality (The Ground Truth)
1. **Extreme Liquidity Fragmentation**:
   - Past exam papers, CATs (Continuous Assessment Tests), and marking schemes are trapped in ephemeral class WhatsApp groups, personal Google Drives, and student chats.
   - Finding a past paper when revising takes 15–30+ minutes of frantic messaging.
2. **The Campus Photocopy Bottleneck**:
   - Students queue at physical campus photocopy shops during the 2-week exam panic window.
   - Photocopy shops charge **KES 3–5 per printed double-sided A4 page** for faded, crooked, illegible photocopies with cash.
3. **High Stakes in Engineering**:
   - Failing an engineering unit at JKUAT often incurs substantial repeat fees and delays graduation by an entire academic year. Grade anxiety and willingness to prepare are acute.

---

## 3. Product Architecture: The Web-to-Mobile Funnel
- **The Public Web App (Discovery & Catalog Proof)**:
   - Mobile-first, fast, SEO-indexed relational catalog (School > Course > Unit Code e.g. `SMA 2100`, `EEE 2201` > Paper).
   - Functions as the zero-friction search engine: confirms instantly that Paprz has the clean, verified paper with a 1-page watermarked preview.
   - Serves as the high-converting funnel directing students to install the native app.
- **The Native Mobile App (Secure Consumption & Reader)**:
   - Enforces **Anti-Piracy (`FLAG_SECURE`)** to prevent screenshots, screen recording, and raw PDF cache extraction.
   - Prevents one student from paying KES 5 and leaking the paper to 120 classmates on WhatsApp.
   - Houses offline encrypted reading for late-night hostel study when campus Wi-Fi drops.

---

## 4. Monetization & Unit Economics
1. **Per-Item Micro-Pricing (Initial Phase)**:
   - **Continuous Assessment Tests (CATs)**: ~KES 3 per paper.
   - **Main University Exams**: ~KES 5 per paper.
   - **Psychological Threshold**: Students perceive KES 3–5 as negligible—cheaper than physical photocopy paper and instant.
2. **Premium Content Ladder (Future Phase)**:
   - High-value academic assets: Lab manuals, industrial attachment reports, thesis templates, and final-year design project reports priced up to **KES 50–100**.
3. **Payment Collection Architecture**:
   - Resolving Safaricom Daraja STK Push micro-fee friction via in-app prepaid micro-token wallets (e.g. deposit KES 20 once via M-Pesa, spend coins on 1-tap unlocks).

---

## 5. Epistemological Lineage Tree (Current State)
- **Block 01 (Genesis)**: *The Liquidity & Distribution Bottleneck* [Draft]
- **Block 02 (Validate)**: *The Per-Item Micro-Pricing Engine* (~KES 3 CATs / ~KES 5 Exams) [Draft]
- **Block 03 (Horizon)**: *The Web-to-Mobile Anti-Piracy Funnel* (Web discovery -> Native Mobile Reader) [Draft]
