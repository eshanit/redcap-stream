# Data6 AHP Dashboard — Decisions Needed from the Programme Team

> **STATUS UPDATE (2026-09-05):** the revised matrix `data/20260905_AHP_ Indicators_kpq.xlsx` resolved most of these decisions and is now implemented:
> **D1 resolved** — adolescent = 10–19 at service date, bands 10–14/15–19. **D2 resolved** — HTS register only. **D3 resolved** — initiation = `hts_art_init` (HTS-3); re-initiation/TI exclusions noted as not machine-checkable in HTS. **D5 resolved forward** — a Labour & Delivery form (`ld_*` fields) is being deployed; PNCR proxies stay as interim until it is live (`config/data6_indicators.php` → `ld.enabled`). **D6 resolved** — BBA counts within home deliveries. **D7 resolved** — documented prior status (`ancr_hiv_prior`) at first booking. **D9 resolved** — substance screening = MH screening (MH-0).
> **Still open:** D4 (per-registration pregnancy grouping), D8 (breastfeeding join), D10 & AHP042 (matrix lists N/A — proxies applied), D11 (support-group count field), and one new gap: the **MH instrument has no date field**, so AHP035–040 cannot be filtered to a reporting period despite the matrix expecting Monthly/Quarterly — a `mh_date` field is the fix.

**Date:** 2026-09-02 · **Refers to:** `docs/plan_ahp_dashboard.md` and `data/20260715_AHP_ Indicators.xlsx`

The technical foundation for the AHP indicator dashboard is settled (project split history, record continuity from project 48, deduplication rules). What remains are **programme-level definitions** that only the team can decide. Eleven questions, grouped below. Each one blocks specific indicators; everything else can proceed.

**How to respond:** reply per decision number with a choice (or "agree with recommendation").

---

## Summary

| # | Decision | Blocks indicators | Type |
|---|---|---|---|
| D1 | Adolescent age definition | All 45 | Definition |
| D2 | HIV-testing entry-point scope | 4, 5, 6 | Definition |
| D3 | ART-initiation field choice | 7, 9 | Definition |
| D4 | ≥8 ANC contacts without a pregnancy ID | 19 | Accept proxy? |
| D5 | Birth outcomes (live births / stillbirths) | 20, 22 | 20: proxy · 22: **form change** |
| D6 | BBA classification | 21 | Definition |
| D7 | Tested at first ANC | 26 | Accept proxy? |
| D8 | HIV retest while breastfeeding | 27 | Accept proxy? |
| D9 | Substance-use screening | 38 | Accept proxy? |
| D10 | Substance-use referral/management | 40 | Accept proxy? |
| D11 | Support groups conducted | 45 | Accept proxy? |

---

## D1 — What is an "adolescent"?

**Why it matters:** every one of the 45 indicators counts "adolescents", but the indicator matrix never defines the term.

**What the data shows:**
- Dates of birth cluster 2005–2015 (roughly ages 10–19 today), consistent with the WHO adolescent definition of **10–19 years**.
- The PrEP register captures age in bands starting **10–14** and **15–19**, which supports 10–19.
- About **300 records carry 2025/2026 birthdates** — these are infants (babies of adolescent mothers) registered in the system. Without an age filter they would be counted as "adolescents accessing services".

**Questions:**
1. Confirm adolescent = **10–19 years**? (Alternative: 10–24 "adolescents and young people" — say which.)
2. Age calculated **at the date of the visit**, or at the end of the reporting period? (Recommended: at the visit — a client who turns 20 mid-year stops counting from that visit onward.)
3. Confirm infants are **excluded** from adolescent counts but still counted in the baby-specific PNC indicators (23)?

**Recommendation:** 10–19 at visit date, disaggregated 10–14 / 15–19; infants excluded from adolescent denominators via age filter + subject type.

---

## D2 — Which testing points count as "tested for HIV"? (Indicators 4–6)

**Why it matters:** the positivity rate (indicator 6) divides indicator 5 by indicator 4, so numerator and denominator must come from the same test universe.

**What the data shows — an HIV test can be recorded in four different registers:**

| Register | Field(s) | Notes |
|---|---|---|
| HTS (dedicated testing register) | `hts_tested`, `hts_hiv_result` | The purpose-built testing register |
| STI register | `sti_hiv_test`, `sti_hiv_test_result` | Test offered during STI visit |
| PrEP follow-up | `prep_hiv_test`, `prep_hiv_test_results` | Routine retest on PrEP |
| ANC | `ancr_hiv_current_status`, `anc_hiv_test_results` | Testing during antenatal care |

The indicator matrix says source = "FCH/OPD", which doesn't resolve this: all four registers exist in FCH, and HTS/STI/PrEP exist in OPD too.

**Question:** does "adolescents tested for HIV" mean (a) **HTS register only**, or (b) **any documented test** across all four registers, deduplicated so a client tested twice in a quarter counts once?

**Recommendation:** option (b) — union of all testing points, counted once per client per period, with an entry-point breakdown on the dashboard. Choose (a) only if facility practice mandates that every test is *also* entered in the HTS register (please confirm whether that is the actual practice).

---

## D3 — What counts as "initiated on ART"? (Indicator 7, feeds retention indicator 9)

**Why it matters:** the 12-month retention cohort (indicator 9) is built from whoever indicator 7 counts, so this definition compounds.

**What the data shows — there is no single "ART start date" field.** Candidate signals:

| Field | Meaning | Caveat |
|---|---|---|
| `art_arv_status` = 2 ("Start ARV") | Clinician records starting ARVs at a follow-up visit | Visit-level; date = `art_review_date` |
| `art_arv_category` = 1 ("Newly Initiated, N1") | MoH client category at the visit | Also has re-initiation/re-engagement codes (N2.x, N3.x) |
| `hts_art_init` = Y | HTS register notes client was initiated | Linkage note, not the clinical record |
| `artr_registration_date` | Enrolment into **HIV care** | Enrolment ≠ ART initiation |

**Questions:**
1. Confirm initiation = `art_arv_status = 2` at the earliest such visit, with `art_review_date` as the initiation date?
2. Do **re-initiations** (categories N2.1–N3.3) count in indicator 7, or only first-time initiations (N1)? (National reporting usually counts them separately.)

**Recommendation:** first-time initiation = earliest visit with `art_arv_status = 2`, validated against category N1; re-initiations reported as a separate line, not mixed into indicator 7. `hts_art_init` used only as a cross-check.

---

## D4 — ≥8 ANC contacts, but there is no pregnancy ID (Indicator 19)

**The gap:** the indicator asks for the percentage of pregnant adolescents completing ≥8 ANC contacts **per pregnancy**. REDCap has no pregnancy identifier — if the same client has two pregnancies, their ANC visits cannot be reliably separated.

**Proposed proxy:** group ANC contacts by **ANC registration** (`ancr_*` record): a "pregnancy" = one ANC Initial Register entry plus all follow-ups until the next registration or delivery (PNCR). Numerator = registrations where the highest recorded contact number (`ancr_contact_number` / `anc_contact_number`) reaches 8.

**Question:** accept this per-registration grouping? (Risk: a client re-registered mid-pregnancy — e.g. transfer-in — would look like two pregnancies.)

---

## D5 — Birth outcomes: live births and stillbirths (Indicators 20 and 22)

**The gap:** the Mother-Baby Pair Initial Register (PNCR) records **place** of delivery (`pncr_place_of_delivery`: Institutional / Home / BBA) but has **no birth-outcome field**. There is no way to distinguish a live birth from a stillbirth anywhere in the forms.

- **Indicator 20 (institutional live births) — proxy available:** PNCR registers living mother-baby pairs, so "institutional live births" ≈ institutional PNCR registrations. Slight undercount if a live-born baby dies before the pair is registered.
- **Indicator 22 (institutional stillbirths) — cannot be built at all.** No proxy exists. This needs either **a REDCap form change** (add a birth-outcome field to PNCR) or **removal of the indicator from the dashboard scope**, sourcing stillbirths from the facility delivery register instead.

**Questions:**
1. Accept the indicator-20 proxy?
2. For indicator 22: request the form change, or drop it from the dashboard? (If the form changes, the indicator only works from the change date forward.)

---

## D6 — Does "Born Before Arrival" count as a home delivery? (Indicator 21)

`pncr_place_of_delivery` has three codes: 1 = Institutional, 2 = Home, 3 = BBA (born before arrival).

**Question:** for "home deliveries among adolescents", does BBA count as (a) home, (b) its own category shown separately, or (c) institutional (because the mother reached the facility)? **Recommendation:** (b) count BBA within non-institutional deliveries but show it as its own line — no information is lost.

---

## D7 — "Tested for HIV at first ANC contact" has no test-event field (Indicator 26)

**The gap:** the ANC Initial Register records the client's HIV **status** (`ancr_hiv_prior`, `ancr_hiv_current_status`) but not whether a test was **performed at that visit**.

**Proposed proxy:** numerator = first bookings (`ancr_first_booking` = yes) where `ancr_hiv_current_status` is documented as Positive or Negative (i.e. status known at booking, "Unknown" fails). Denominator = new ANC bookings (indicator 18).

**Question:** accept documented-status-at-booking as the proxy for tested-at-first-contact?

---

## D8 — HIV retesting during breastfeeding spans two forms (Indicator 27)

**The gap:** the mother's retest is on the **mother** follow-up form (`pncm_hiv_tested`), but breastfeeding status is only on the **baby** follow-up form (`pncb_feeding_option`). The two must be joined through the mother-baby number (`pncr_mother_baby`).

**Proposed proxy:** denominator = HIV-negative mothers (`pncr_hiv_status_post` = N) whose linked baby has a breastfeeding feeding option at that period's visits; numerator = those with `pncm_hiv_tested` = yes in the period.

**Question:** accept this joined definition? Where the mother-baby number is missing or does not match, the pair is excluded and counted in the data-quality report — confirm that treatment.

---

## D9 — Substance-use "screening" has no dedicated field (Indicator 38)

**The gap:** there is no standalone substance-use screening instrument. Substance use is asked **inside the mental-health screening**: `mh_substance_identified` (yes/no), with substances listed in `mh_substance_used`.

**Proposed proxy:** "screened for substance use" = completed an MH screening (`mh_screening_tools` = yes) in which the substance-use question was answered.

**Question:** accept that substance-use screening is a component of MH screening, so indicators 35 and 38 will have very similar denominators? (Alternative: a form change to add a distinct substance-screening tool such as ASSIST/CRAFFT.)

---

## D10 — Substance-use referral/management has no dedicated outcome field (Indicator 40)

**The gap:** the MH form has one management outcome (`mh_management_outcome`: Referred / Managed / Both) covering the whole screening — it does not say whether the referral was **for the substance-use finding specifically**.

**Proposed proxy:** numerator = clients with `mh_substance_identified` = yes **and** `mh_management_outcome` ∈ {Referred, Managed, Both}. This may overcount if the referral was for a different MH condition found in the same screening.

**Question:** accept this proxy, with the overcount risk noted on the dashboard?

---

## D11 — Support groups: yes/no exists, a count does not (Indicator 45)

**The gap:** peer-led sessions have a count field (`pls_number`) but support groups only have `pls_support_conducted` = yes/no per register entry — if two support-group meetings happen before the next entry, only one is visible.

**Proposed proxy:** count register entries with `pls_support_conducted` = yes.

**Question:** accept the entry-count proxy, or add a "number of support-group meetings" field to the form (as exists for peer-led sessions)?

---

## What happens next

- Decisions **D1–D3** unblock the first dashboard release (access + HIV testing + ART cascade).
- The **proxy acceptances (D4, D5.1, D6–D11)** each unblock their indicator; any proxy the team rejects is either dropped or waits for a form change.
- **D5.2 (stillbirths)** is the only item that cannot be delivered from current data under any interpretation.
- Accepted proxies will carry a visible "proxy definition" badge on the dashboard until/unless forms are amended.
