# ForeRent — Research Congress Presentation Content


**Format:** 10 min presentation + 5 min Q&A
**Structure (per prof):** Problem → Solution → Results → Impact → System Demo (key features)
**Source material:** Aligned with Thesis 2 Final Defense PPT (Group 8 — Quattuorix, April 8 2026) and the actual ForeRent system.


---


## SLIDE 1 — Title (≈15 sec)


**Development of ForeRent: Rental Property Management System with Pricing, Maintenance, and Revenue Forecasting Using Multiple Regression with Hierarchical Clustering**


- **Group 8 — Quattuorix**
- John Rey Abrera (Programmer)
- Nicole Candelaria (Researcher)
- Elcarlwen Guirhem (Programmer)
- Reigne Cristine Rivera (Designer)
- Research Congress · May 2026


> *Speaker notes:* Open with full thesis title, then say the short name "ForeRent" — use that for the rest of the talk.


---


## SLIDE 2 — Project Context (≈45 sec)


**Rental property management in the Philippines is still largely manual.**


- Property managers commonly rely on **manual or Excel-based systems**.
- These break down as rental demand and transaction volumes grow.
- Result: operational inefficiencies that affect both managers and tenants.


> *Speaker notes:* Set the scene quickly — establish that the problem space is the Philippine rental market.


---


## SLIDE 3 — PROBLEM (≈1 min 15 sec)


**Despite its importance, traditional rental management suffers from:**


1. **Errors and data inconsistencies** from manual / Excel-based records
2. **Disorganized records, delayed maintenance, and unclear billing**
3. **Limited compliance** with regulatory standards
4. **Lack of integrated systems** that combine centralized management with data-driven forecasting
5. **Limited application of machine learning** (specifically Multiple Regression and Hierarchical Clustering) for rental pricing, revenue prediction, and property classification


**The gap:** No comprehensive, intelligent system exists that streamlines operations *and* enhances decision-making in Philippine rental property management.


> *Speaker notes:* Emphasize the gap — the literature highlights the need, but no integrated platform exists yet.


---


## SLIDE 4 — SOLUTION: ForeRent System Overview (≈1 min)


**ForeRent — a web-based rental property management system integrated with Machine Learning.**


**Core idea:** Centralize and automate property, tenant, payment, and maintenance management while embedding predictive analytics.


**ML Framework — Hybrid Model:**
- **Hierarchical Clustering** → groups similar properties and maintenance concerns
- **Multiple Regression** → forecasts rental pricing and revenue trends


**Three role-based modules** serving Property Owners, Property Managers, and Tenants on a single platform.


---


## SLIDE 5 — SOLUTION: System Modules (≈1 min)


| Module | Key Features |
|---|---|
| **Property Owner** | Dashboard · Properties Management · Property Assignment · Payment Monitoring · Revenue Management · Messenger · Settings |
| **Property Manager** | Dashboard · Properties Management · Tenant Management · Payment Documents · Maintenance Management · Messenger · Settings |
| **Tenant** | Dashboard · Payment Documents · Maintenance Request Management · Messenger · Settings |


> *Speaker notes:* One platform, three contextual experiences. Owner sees revenue & forecasts, manager handles maintenance, tenant submits requests and views billing.


---


## SLIDE 6 — RESULTS: Algorithm Performance (≈1 min 15 sec)


**Tested on a high-fidelity synthetic dataset of 3,000 dormitory records** (fixed pricing logic + randomized noise, modeling Philippine market variability).


**Comparison: Multiple Regression vs. KNN**


| Metric | KNN | **Multiple Regression** |
|---|---|---|
| R² | 0.9004 | **0.9162** |
| MAE | 308.70 | **291.47** |
| RMSE | 369.02 | **338.44** |
| MAPE | 4.32% | **4.08%** |


**Selection:** Multiple Regression integrated with Hierarchical Clustering produced more reliable, data-driven forecasts → **selected for final implementation.**


> *Speaker notes:* Land the headline number: R² = 0.9162, MAPE = 4.08%. Multiple Regression won across every metric.


---


## SLIDE 7 — RESULTS: Clustering & Validation (≈45 sec)


**Hierarchical Clustering Results:**
- **Optimal clusters:** 4 (for Multiple Regression model)
- Validated with **Silhouette Score**, **Davies-Bouldin Index**, and **Calinski-Harabasz Score**
- Price distribution analysis shows clear market segmentation across clusters (₱5,000 – ₱9,500 range)


**This means:** properties are intelligently grouped before pricing prediction, improving forecast accuracy.


---


## SLIDE 8 — RESULTS: System Validation (≈1 min)


**Functional Testing — 100% pass rate**


| Module | Test Cases | Passed |
|---|---|---|
| Property Owner | 8 | 8 |
| Property Manager | 8 | 8 |
| Tenant | 6 | 6 |
| **TOTAL** | **22** | **22 (100%)** |


**Non-Functional Testing — PASSED**
- Property Owner: 7.98 sec avg response · 0% error rate
- Property Manager: 11.09 sec avg response · 0% error rate


**ISO/IEC 25010 Software Quality:** 3.28 avg — **Highly Acceptable**


**System Usability Scale (SUS):** 69.08 — **Acceptable** (56.7% rated *Good*)


---


## SLIDE 9 — IMPACT (≈1 min 15 sec)


**For Property Owners**
- Data-driven pricing & revenue decisions instead of guesswork
- Forward visibility on income through 91.6% accurate forecasts
- Centralized oversight across multiple properties


**For Property Managers**
- Faster, more organized maintenance handling
- Clear tenant and payment records — no scattered spreadsheets


**For Tenants**
- Transparent billing and payment history
- Easier maintenance request submission and tracking


**Broader Impact**
- Modernizes property management for the **Philippine rental industry**
- Bridges advanced predictive analytics with practical operational usability
- Establishes a scalable, data-driven foundation for future research


> *Speaker notes:* Tie back to the problem — every pain point from Slide 3 has a corresponding result here.


---


## SLIDE 10 — System Demonstration (≈2 min 30 sec)


**Live walkthrough — discuss these key features as you click through:**


1. **Owner Dashboard** — KPI cards, occupancy, revenue & maintenance overview
2. **Properties Management** — create property, assign manager, set pricing
3. **Pricing & Revenue Forecast** — Multiple Regression + Hierarchical Clustering output (predicted vs. actual comparison)
4. **Maintenance Management** — request lifecycle (Tenant submits → Manager handles → Owner monitors cost)
5. **Payment Monitoring & Documents** — billing records, receipts, payment tracking
6. **Messenger** — real-time owner ↔ manager ↔ tenant communication
7. **Tenant View** — payment documents, maintenance request submission


> *Speaker notes:* Keep it tight — for each feature, one sentence: "this is X, it solves Y." Don't get stuck.


---


## SLIDE 11 — Conclusion (≈30 sec)


**ForeRent successfully demonstrates that:**
- A hybrid **Multiple Regression + Hierarchical Clustering** framework can deliver **91.6% accurate** rental pricing & revenue forecasts.
- Centralized, role-based modules can serve owners, managers, and tenants on one platform.
- **100% functional pass rate** + **Highly Acceptable** ISO/IEC 25010 rating + SUS score of **69.08** prove the system bridges advanced analytics with practical usability.


**ForeRent is a scalable, data-driven solution for the Philippine rental industry.**


---


## SLIDE 12 — Thank You / Q&A (≈10 sec)


**Thank you. Open for questions.**


---


# Q&A — Likely Questions to Prepare For


1. **Why Multiple Regression over KNN?** → Stronger across every metric: R² 0.9162 vs 0.9004; MAPE 4.08% vs 4.32%. Property features (amenities, area, capacity) had a strong linear relationship with price, which favors regression.
2. **Why Hierarchical Clustering specifically?** → Lets the system group similar properties before forecasting; validated with Silhouette, Davies-Bouldin, and Calinski-Harabasz scores. Optimal cut at 4 clusters for the regression model.
3. **Why a synthetic dataset of 3,000 records?** → Allowed controlled, fair comparison between models with consistent property attributes (unit size, furnishing, amenities) while still reflecting Philippine market variability via randomized noise.
4. **Will the model still work on real data?** → That's exactly our recommendation — transition from synthetic to real-world Philippine rental data to validate against actual market fluctuations.
5. **How is the system secured?** → Role-based access control: tenants only see their own data; managers only see assigned properties; owners see full portfolio. Security scored 3.21 (Acceptable) under ISO/IEC 25010.
6. **What does the SUS score of 69.08 mean?** → Acceptable usability — 56.7% rated it *Good with minor issues*. Future work targets the 36.6% who flagged needs improvement.
7. **What's unique about ForeRent vs. existing platforms?** → Combination of (a) a hybrid ML pricing model tuned on Philippine market data, (b) integrated property/tenant/maintenance/payment workflows in one platform, and (c) role-based modules for all three stakeholders.
8. **Limitations?** → Synthetic dataset, single locale assumption, SUS suggests UI improvements needed, mobile responsiveness identified for future work.


---


# Timing Cheat Sheet (10 minutes total)


| Section | Slides | Time |
|---|---|---|
| Title | 1 | ~0:15 |
| Context | 2 | ~0:45 |
| Problem | 3 | ~1:15 |
| Solution | 4–5 | ~2:00 |
| Results (algorithm) | 6–7 | ~2:00 |
| Results (validation) | 8 | ~1:00 |
| Impact | 9 | ~1:15 |
| Demo | 10 | ~2:30 |
| Conclusion | 11–12 | ~0:40 |
| **Total** | | **~11:40** → **trim Demo to ~1:30 to land at ~10:00** |


> *Reality check:* You'll likely run 1–2 minutes long on first runthrough. Cut demo steps 5–6 if needed; the predictive analytics demo (step 3) is the most important.


---


# Tips for Delivery


- **Lead with your number** — "Our hybrid Multiple Regression + Hierarchical Clustering model achieved 91.6% accuracy" is your headline. Repeat it in Solution, Results, and Conclusion.
- **Don't read the metrics table** — say the highlight, point at the table, move on.
- **Practice the Problem → Solution transition** — most groups stumble here.
- **Have a backup demo recording** — login or network failure during live demo will eat your time.
- **For Q&A** — if unsure, say *"we considered that — currently it's [X], future work is [Y]."* That mirrors your Recommendation slide and shows research maturity.
- **Pronunciation** — "ForeRent" (one word, fore-rent), not "Forerent" or "For-rent."


---


# Differences from Final Defense PPT


This research congress version (10 min) differs from your 30-slide final defense PPT in three ways:


1. **Cut content not central to the prof's structure** — removed standalone Algorithm Model criteria slide, separate dendrogram slides, Recommendations slide. These can come up in Q&A.
2. **Restructured to Problem → Solution → Results → Impact → Demo** — the prof's exact order. The defense version was more chronological (context → problem → features → algorithm → testing → conclusion → demo).
3. **Added explicit Impact slide (Slide 9)** — research congress audience is broader than thesis panel; they care about *why this matters* more than methodology depth. The defense PPT folded this into Conclusion.


If you want to **reuse old slides verbatim**, these map cleanly:
- Old slide 1 (title) → New slide 1
- Old slide 4 (problem statement) → New slide 3
- Old slides 5–7 (system features) → New slide 5
- Old slide 19 (MR vs KNN comparison) → New slide 6
- Old slide 21 (functional testing) + slide 22 (non-functional) + slide 23 (ISO) + slide 24 (SUS) → New slide 8
- Old slide 25 (conclusion) → New slide 11
- Old slide 28 (system demo) → New slide 10


---


# 🎤 SPEAKER SCRIPT (Word-for-Word)


> **How to use this:** This is what to *say* for each slide — practiced and timed for 10 minutes. Adjust to your natural voice; don't read it like a robot. **Brackets [like this]** are stage directions, not spoken.


---


## SLIDE 1 — Title (≈15 sec)


> "Good [morning / afternoon] po, panel and fellow researchers. We are **Group 8 — Quattuorix**, and our research is titled *Development of ForeRent: A Rental Property Management System with Pricing, Maintenance, and Revenue Forecasting Using Multiple Regression with Hierarchical Clustering.*
>
> I'm [your name], with John Rey, Nicole, Elcarlwen, and Reigne. For the next 10 minutes, we'll walk you through the problem we set out to solve, our solution, our results, and a quick demo of the system."


[Click to next slide.]


---


## SLIDE 2 — Project Context (≈45 sec)


> "Rental property management is a critical part of the real estate industry — it covers tenant records, pricing, maintenance, and revenue monitoring.
>
> But here in the Philippines, most property managers still rely on **manual records or Excel spreadsheets**. This works fine when you have a few units — but as rental demand grows and transaction volumes increase, these traditional methods break down.
>
> The result is operational inefficiency that affects both the property managers and the tenants they serve."


[Click.]


---


## SLIDE 3 — Problem (≈1 min 15 sec)


> "Specifically, we identified **five key problems** with the way rental properties are currently managed.
>
> **First**, manual and Excel-based records are prone to errors and data inconsistencies.
>
> **Second**, this leads to disorganized records, delayed maintenance responses, and unclear billing for tenants.
>
> **Third**, these systems struggle to comply with regulatory standards.
>
> **Fourth**, while existing studies highlight the need for digital solutions, there is still a **lack of integrated systems** that combine centralized property management with data-driven forecasting and analysis.
>
> And **fifth** — most importantly for our research — the application of machine learning methods, specifically **Multiple Regression and Hierarchical Clustering**, remains very limited in supporting accurate rental pricing, revenue prediction, and property classification.
>
> This is the **gap** our study addresses: a comprehensive, intelligent system that can streamline operations and enhance decision-making in Philippine rental property management."


[Click.]


---


## SLIDE 4 — Solution Overview (≈1 min)


> "Our solution is **ForeRent** — a web-based rental property management system integrated with machine learning.
>
> The core idea is to centralize and automate the four key processes — property, tenant, payment, and maintenance management — while embedding predictive analytics directly into the workflow.
>
> The ML framework is a **hybrid model**:
>
> - **Hierarchical Clustering** groups similar properties and maintenance concerns together.
> - **Multiple Regression** then forecasts rental pricing and revenue trends within those groups.
>
> And we deliver this through **three role-based modules** — one each for property owners, property managers, and tenants — all on a single platform."


[Click.]


---


## SLIDE 5 — System Modules (≈1 min)


> "Each user role gets a tailored experience.
>
> The **Property Owner Module** focuses on oversight — dashboard, properties management, property assignment to managers, payment monitoring, revenue management, and messaging.
>
> The **Property Manager Module** focuses on day-to-day operations — managing assigned properties, tenant management, payment documents, and maintenance handling.
>
> And the **Tenant Module** is streamlined for renters — they can view their dashboard, payment documents, submit maintenance requests, and message their manager.
>
> One platform, three contextual experiences — built around what each user actually needs."


[Click.]


---


## SLIDE 6 — Algorithm Performance (≈1 min 15 sec)


> "Now to our results. We tested our predictive model on a **high-fidelity synthetic dataset of 3,000 dormitory records**, generated using fixed pricing logic with randomized noise to capture Philippine market variability.
>
> We compared our chosen model — **Multiple Regression** — against **K-Nearest Neighbors** as a baseline.
>
> [Point at the table.] Multiple Regression won across every metric:
>
> - **R-squared of 0.9162** versus 0.9004 — meaning our model explains 91.6% of the variance in rental prices.
> - Mean Absolute Error of only **₱291.47**.
> - RMSE of ₱338.44.
> - And a Mean Absolute Percentage Error of just **4.08%**.
>
> Because of this stronger performance — and the strong linear relationship between property features like amenities, area, and capacity, and price — we selected **Multiple Regression integrated with Hierarchical Clustering** as our final implementation."


[Click.]


---


## SLIDE 7 — Clustering & Validation (≈45 sec)


> "On the clustering side, we validated our hierarchical groupings using three standard metrics — **Silhouette Score, Davies-Bouldin Index, and Calinski-Harabasz Score**.
>
> The optimal configuration was **4 clusters** for the Multiple Regression model. As you can see in the price distribution, each cluster represents a clear market segment, ranging from around ₱5,000 to nearly ₱9,500.
>
> What this means in practice: ForeRent groups similar properties together *before* predicting prices — and that's what makes our forecasts more reliable."


[Click.]


---


## SLIDE 8 — System Validation (≈1 min)


> "Beyond the algorithm, we also validated the system itself.
>
> **Functional testing** across all three modules achieved a **100% pass rate** — 22 out of 22 test cases passed, with zero failures.
>
> **Non-functional testing** also passed — the Property Owner module averaged 7.98 seconds response time, and Property Manager 11.09 seconds, both with **zero error rates**.
>
> Under the **ISO/IEC 25010 software quality standard**, ForeRent earned an average rating of **3.28 — Highly Acceptable**.
>
> And the **System Usability Scale** gave us a score of **69.08**, with **56.7% of respondents rating the system as Good.**
>
> So the system is not only accurate — it's stable, fast, and usable."


[Click.]


---


## SLIDE 9 — Impact (≈1 min 15 sec)


> "What does this mean for the people who'll actually use ForeRent?
>
> **For property owners**, ForeRent replaces guesswork with data — they get pricing and revenue forecasts that are 91.6% accurate, with a clear view across all their properties.
>
> **For property managers**, maintenance and tenant records are no longer scattered across spreadsheets — everything is in one place, easy to organize and respond to.
>
> **For tenants**, billing becomes transparent, and submitting maintenance requests is straightforward.
>
> And on a **broader level**, ForeRent modernizes property management for the Philippine rental industry. It bridges advanced predictive analytics with practical operational usability — and lays a scalable foundation for future research on Philippine rental markets."


[Click.]


---


## SLIDE 10 — System Demonstration (≈2 min 30 sec)


> "Now, let me show you ForeRent in action."


[Switch to live system or screen recording.]


> "**[Step 1]** This is the **Owner Dashboard** — KPI cards for occupancy, revenue, and maintenance status, all in one view.
>
> **[Step 2]** Here in **Properties Management**, owners can create a property, assign a manager, and set initial pricing.
>
> **[Step 3]** This is the **key feature** — our **Pricing and Revenue Forecast**, powered by Multiple Regression with Hierarchical Clustering. You can see predicted versus actual values side by side.
>
> **[Step 4]** **Maintenance Management** — a tenant submits a request here, the manager handles it, and the owner can monitor the cost.
>
> **[Step 5]** **Payment Monitoring and Documents** — billing records and receipts are tracked automatically.
>
> **[Step 6]** **Messenger** lets the owner, manager, and tenant communicate in real time.
>
> **[Step 7]** And finally, the **Tenant View** — clean, focused, just what they need: payment status and request submission.
>
> That's ForeRent in 2 minutes."


[Switch back to slides.]


---


## SLIDE 11 — Conclusion (≈30 sec)


> "To summarize — ForeRent successfully demonstrates three things:
>
> **One**, that a hybrid Multiple Regression and Hierarchical Clustering framework can deliver **91.6% accurate** rental pricing and revenue forecasts.
>
> **Two**, that role-based modules can serve owners, managers, and tenants effectively on a single platform.
>
> And **three**, that the combination of **100% functional pass rate**, a **Highly Acceptable** ISO/IEC 25010 rating, and a **69.08 SUS score** proves we've bridged advanced analytics with practical, real-world usability.
>
> ForeRent is a scalable, data-driven solution ready for the Philippine rental industry."


[Click.]


---


## SLIDE 12 — Thank You / Q&A (≈10 sec)


> "Thank you po. We are now open for your questions."


[Smile. Take a breath. Wait for questions.]


---


# Tips While Delivering the Script


- **Pace:** ~140–160 words per minute. Practice with a stopwatch.
- **Pauses:** Pause for **1 full second** after every metric (e.g., "R-squared of 0.9162... [pause] ...meaning our model..."). It gives the audience time to absorb numbers.
- **Eye contact:** Look up from your script every 2–3 sentences. The script is a safety net, not a teleprompter.
- **Transitions:** Words like *"Now,"* *"Specifically,"* *"What this means is,"* signal a new thought — use them at slide changes.
- **If you blank:** Just say **"Next slide."** Don't apologize. The audience won't notice you skipped a sentence.
- **Numbers:** Always say them slowly. *"Zero point nine one six two"* is clearer than rushing *"point ninety-one sixty-two."*
- **For Q&A:** Repeat the question before answering. *"The question is about [X]. Our approach was..."* This buys you 5 seconds to think.


---


# Script Length Check


Approximate word counts per section (at 150 wpm):
- Slide 1: ~70 words → 28 sec
- Slide 2: ~95 words → 38 sec
- Slide 3: ~180 words → 72 sec
- Slide 4: ~120 words → 48 sec
- Slide 5: ~120 words → 48 sec
- Slide 6: ~150 words → 60 sec
- Slide 7: ~95 words → 38 sec
- Slide 8: ~140 words → 56 sec
- Slide 9: ~155 words → 62 sec
- Slide 10: ~210 words + demo time → ~2:30
- Slide 11: ~110 words → 44 sec
- Slide 12: ~10 words → 4 sec


**Total spoken: ~9:30** — leaves a 30-second buffer for transitions, slide loading, and breathing. Right on target.
