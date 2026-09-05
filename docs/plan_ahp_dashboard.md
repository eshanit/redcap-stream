# AHP Indicator Dashboard Plan (Data6)

**Goal:** deliver the 45 AHP indicators defined in `data/20260715_AHP_ Indicators.xlsx` as a comprehensive, filterable dashboard on top of the `redcap_data6` projects (76 FCH, 78 OI/ART, 79 OPD), building on the tracking layer already established (`data6_source_records`, `data6_patients`, `data6_patient_source_records`, `data6_encounters`).

This plan supersedes the indicator sections of `PROJECT_DATA6_ONBOARDING_PLAN.md` and `plan_update_1.md` where it conflicts with what the actual dump shows; the architectural rules in those documents (read-only source, canonical patient, service layer, no PII in aggregates) remain in force.

---

## 1. Confirmed history and what the actual dump shows

**Confirmed background (from the client, 2026-09-02):** the services originally ran as one large project — project **48** in `redcap_data3` — collected with the REDCap mobile app. Data volume forced a split into three projects (FCH 76, OI/ART 78, OPD 79), now in `redcap_data6`. A patient keeps the same `record` value across the split projects, and the split process replicated data across projects — **deduplication is a confirmed requirement**, not a hypothesis.

Profiling `database/redcap_data6.sql` shows exactly how the duplication works, and it differs by instrument type:

1. **Service-specific forms: a stale baseline copy sits in the two non-home projects.** At (or around) the split, each project received a copy of the full dataset; since then, new data for a form lands only in its home project. Evidence:
   - `anc_*`: home project 76 has 42,152 rows with dates through **2026-08-24**; the copies in 78/79 both have exactly 37,145 rows, frozen at **2026-03-03**.
   - `art_*`: home project 78 has 10,853 rows through **2026-08-29**; the copies in 76/79 both have exactly 9,974 rows, frozen at **2026-01-17**.
   - `opd_*`: home project 79 has 2,828 rows; the copies in 76/78 both have exactly 2,282.
   - The same clinical row appears verbatim in all three, e.g. `('52', 'art_review_date', '2025-02-08')` in events 1429 (76), 1459 (78), and 1474 (79).
   - **Rule:** read each service-specific family only from its home project — FCH forms (`fp, ancr, anc, pncr, pncm, pncb`) from 76; ART forms (`artr, artib, art`) from 78; OPD forms (`opd, op`) from 79. The stale copies are ignored for indicators and used only as reconciliation checks.

2. **Shared instruments: genuinely authored in all three projects.** These are *not* mirrors — the client accesses STI/PrEP/MH/HTS/HE/counselling at whichever entry point they visit, and the encounter is recorded in that project. Evidence: `sti_visit_date` has 15 rows in 76 (latest 2026-08-18), 1 in 78 (2026-05-08), 2 in 79 (2026-01-19); `hts_hiv_date` has 66 in 76, 0 in 78, 12 in 79. Near-equal counts (`mh_access`: 1,453 / 1,467 / 1,482) indicate a shared baseline copy plus small per-project additions.
   - **Rule:** union shared-instrument encounters across all three projects, then collapse duplicates by `(record, instrument, business date, normalized_instance)` — this removes the baseline copy while keeping genuinely distinct encounters from different entry points. Duplicates with conflicting values become data-quality findings.

   The two rules combine into one dedup key: an encounter is unique by `(record, instrument, business_date, normalized_instance)`; when the same encounter exists in multiple projects, the home project's row wins for service-specific forms, and all source projects are recorded on the canonical encounter.

   **Conflict resolution (confirmed by client, 2026-09-02):** when duplicate encounters carry conflicting values, the conflict means one copy was updated after the split — take the **updated version**. Operationally: for service-specific forms the home project is the live copy and always wins; for shared instruments, the row that diverges from the frozen baseline is the update and wins. Every resolved conflict is still logged to the data-quality report so it can be spot-checked.

   **Manual data entry caveat:** data is transcribed from paper registers into the mobile app, so expect entry lag (an August visit may be captured weeks later) and transcription errors. All period reporting uses the clinical business date (visit/review dates), never sync order, and the data-quality strip must track invalid dates, future dates, and implausible values.

3. **`record` is a shared patient identifier — and continues from project 48.** Of ~6,100 distinct records, **5,885 appear in all three projects**, 42 in two, and only 191 in one. The canonical patient keys on `record`, with the 233 non-mirrored records surfaced in the identity review queue. **Confirmed by client (2026-09-02):** record values continue from historical project 48 in `redcap_data3`, so the canonical patient can link back to pre-split history. This matters for the ART cohort indicators (8–10): initiation dates and visit history predating the split (the dump has `art_review_date` back to 2010) are valid clinical history, and project 48 in `redcap_data3` can serve as a secondary reconciliation source via the existing `ProjectData3` model.

3. **Multiple events per project** (76: events 1420–1434; 78: 1450+; 79: 1470+), not the single event assumed in `plan_update_1.md`. `event_id` maps between mirrors positionally — resolve via `redcap_events_metadata`, never by raw ID equality.

4. **Instance is mostly NULL** (616k NULL vs ~60k numbered, max observed 14+). The `normalized_instance` approach already implemented in `ProjectData6Service` is correct.

5. **Adolescent population confirmed, with infants mixed in.** Birth years cluster 2005–2015 (ages ~10–19 today), but ~300 records have `demog_dateofbirth` in 2025/2026 — infants registered under (or alongside) mother records. **Adolescent indicator denominators must filter by age at encounter date and by `subject_type`**, or babies will contaminate counts.

---

## 2. Indicator-to-field mapping

Cross-cutting definitions to confirm with the programme team before build:

- **Adolescent** = age **10–19** (assumed; confirm) at the encounter's business date, from `demog_dateofbirth`.
- **Reporting period** = calendar month/quarter over the instrument's business date (see `DATE_FIELDS` in `ProjectData6Service`).
- **Dedup unit** = canonical patient (`COUNT(DISTINCT patient_id)`) unless the indicator explicitly counts visits or sessions.

Status legend: ✅ directly computable · 🔶 computable with cohort/derivation logic · ⚠️ proxy needed / data gap — flag to stakeholders.

### Group A — Service access (1–3) · sources: all projects

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 1 | Adolescents accessing services | Distinct patients with ≥1 encounter (`*_access='Y'`) in period | ✅ from `data6_encounters` |
| 2 | First-time adolescent visits | Patients whose **first-ever** encounter date falls in period (`MIN(service_date)` per patient) | ✅ |
| 3 | Repeat adolescent visits | Encounters in period excluding each patient's first visit | ✅ |

### Group B — HIV testing (4–6) · FCH/OPD

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 4 | Tested for HIV | `hts_tested=1` (date `hts_hiv_date`); also `sti_hiv_test=1`, `prep_hiv_test=1`, `anc_hiv_test_results` present — **confirm which testing entry points count** | 🔶 |
| 5 | Testing HIV positive | `hts_hiv_result='P'` (± `sti_hiv_test_result='P'`, `prep_hiv_test_results='P'`, `anc_hiv_test_results='P'`) | 🔶 |
| 6 | HIV positivity rate | Indicator 5 ÷ Indicator 4 × 100 | ✅ once 4–5 fixed |

### Group C — ART cascade (7–17) · OI/ART (home project 78)

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 7 | Initiated on ART | `art_arv_status=2` (Start ARV) or `art_arv_category=1` (N1) in period; `hts_art_init='Y'` as linkage cross-check | 🔶 confirm definition |
| 8 | Currently on ART (TX_CURR) | Latest `art_*` follow-up per patient at period end; exclude `art_final_outcome` ∈ {3 LTFU, 4 TO, 5 Died, 6 Opted out}; `art_next_review_date` overdue < 28 days | 🔶 cohort logic — reuse the LTFU pattern from the existing NCD work |
| 9 | 12-month retention | Cohort initiated in month M (ind. 7); alive & active at M+12 ÷ cohort size | 🔶 cohort logic |
| 10 | Lost to follow-up | `art_next_review_date` > 28 days overdue at period end, not dead/TO | 🔶 |
| 11 | Transferred in | `art_arv_category=8` (N4 Transfer in) | ✅ |
| 12 | Transferred out | `art_final_outcome=4` newly recorded in period | ✅ |
| 13 | HIV-positive deaths | `art_final_outcome=5` | ✅ |
| 14 | VL test conducted | `art_viral_load=1` with `art_vl_collect_date` in period (baseline: `artib_viral_load=1`); once per patient per period | ✅ |
| 15 | TND result | `art_vl_detected=0` (target not detected) | ✅ |
| 16 | VL suppression <1000 | numeric(`art_vl_result`) < 1000 **or** TND, ÷ ind. 14 | 🔶 numeric parsing + TND merge |
| 17 | VL ≥1000 | numeric(`art_vl_result`) ≥ 1000 | 🔶 |

### Group D — ANC / delivery / PNC (18–28) · FCH (home project 76)

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 18 | New ANC bookings | `ancr_access='Y'` and `ancr_first_booking=1`, date `ancr_date` | ✅ |
| 19 | ≥8 ANC contacts | max(`ancr_contact_number`, `anc_contact_number`) ≥ 8 per patient; **no pregnancy ID exists** — grouping multiple pregnancies of one record is ambiguous | ⚠️ |
| 20 | Institutional live births | `pncr_place_of_delivery=1`, counted per baby (`pncr_*` is per-baby) | 🔶 see note below |
| 21 | Home deliveries | `pncr_place_of_delivery` ∈ {2 Home, 3 BBA} — confirm whether BBA counts as home | 🔶 |
| 22 | Institutional stillbirths | **No stillbirth field exists in any instrument** — PNCR registers live mother-baby pairs only | ⚠️ data gap |
| 23 | Early neonatal deaths | `pncb_infant_follow_ups=6` (Infant dead) where `pncb_visit_date − pncr_date_of_birth ≤ 7 days` | 🔶 |
| 24 | Maternal deaths | `pncm_mother_follow_up=5` (Dead) | ✅ |
| 25 | PNC within 72 hrs | `pncm_visit` ∈ {Day 1, Day 3} or `pncm_visit_date − pncr_date_of_birth ≤ 3 days` ÷ deliveries (PNCR count) | 🔶 |
| 26 | Tested for HIV at first ANC | `ancr_first_booking=1` with `ancr_hiv_current_status` ∈ {0,1} — **no explicit "tested at this visit" field on ANCR**; proxy via documented status | ⚠️ proxy |
| 27 | HIV retest while breastfeeding | `pncm_hiv_tested=1` among mothers with `pncr_hiv_status_post='N'`; breastfeeding status only exists on the **baby** form (`pncb_feeding_option`) — requires mother↔baby join via `pncr_mother_baby` | ⚠️ proxy |
| 28 | On ART at delivery | `pncr_hiv_status_art=1` ÷ `pncr_hiv_status_post='P'` | ✅ |

> Note on 20/22: because PNCR has no birth-outcome field, "institutional live births" = institutional PNCR registrations, and stillbirths are invisible. Raise both with the programme team; if stillbirth capture matters, it needs a REDCap form change, not app logic.

### Group E — Family planning (29–30) · FCH

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 29 | New FP users | `fp_client_category='N'`, date `fp_date` | ✅ |
| 30 | Repeat FP users | `fp_client_category='R'` | ✅ |

### Group F — PrEP (31–34) · all projects (shared instrument)

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 31 | Screened for PrEP eligibility | `prepr_screened=1` | ✅ |
| 32 | Initiated on PrEP | `prepr_visit_status='N'` or `prepr_prep_initiate=1` with `prepr_prep_start_date` in period | ✅ |
| 33 | Continuing on PrEP | `prepr_visit_status='C'` or latest `prep_follow_up_status='Px'` in period | 🔶 |
| 34 | Discontinued from PrEP | `prepr_visit_status='D'`, `prep_client_outcome=3`, or `prep_follow_up_status` ∈ {OO, WTH} | 🔶 confirm precedence |

### Group G — Mental health & substance use (35–40) · all projects

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 35 | Screened for MH conditions | `mh_screening_tools=1` (optionally + `art_mental_disorders=1` — confirm whether ART-visit screening counts) | ✅ |
| 36 | Screening MH positive | `mh_screening_results='P'` | ✅ |
| 37 | Referred/managed for MH | `mh_management_outcome` ∈ {R, M, B} | ✅ |
| 38 | Screened for substance use | No dedicated screening field — the MH tool covers it; proxy = `mh_screening_tools=1` where `mh_substance_identified` answered | ⚠️ proxy |
| 39 | Screening positive for substance use | `mh_substance_identified=1` (substances in `mh_substance_used`) | ✅ |
| 40 | Referred/managed for substance use | No substance-specific outcome field; proxy = `mh_management_outcome` ∈ {R,M,B} where `mh_substance_identified=1` | ⚠️ proxy |

### Group H — STI (41–42) · all projects

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 41 | Screened for STIs | `sti_access='Y'` encounter (syndromes in `sti_syndrome`); also `prepr_sti_screening=1` — confirm scope | 🔶 |
| 42 | Treated for STIs | `sti_patient_treated=1` | ✅ |

### Group I — Peer support (43–45) · peer support register

These count **sessions**, not unique patients.

| # | Indicator | Numerator logic | Status |
|---|---|---|---|
| 43 | Peer-led sessions conducted | `SUM(pls_number)` where `pls_session_conducted=1`, date `pls_date` | ✅ |
| 44 | Adolescents reached via peer activities | `SUM(pls_ado_number)` | ✅ |
| 45 | Support groups conducted | `COUNT` of encounters with `pls_support_conducted=1` — there is **no session-count field** for support groups, only yes/no per encounter | ⚠️ confirm interpretation |

**Tally: 20 ✅ · 17 🔶 · 8 ⚠️.** The ⚠️ items need a stakeholder decision before build; everything else is implementable from the dictionary as documented.

---

## 3. Architecture

### 3.1 Why the current layer isn't enough

`data6_encounters` knows *that* a service happened (instrument, date, instance) but not *what* happened (test results, outcomes, statuses). Indicators need field values. Computing them directly against `redcap_data6` would require wide conditional-aggregation pivots over 680k+ EAV rows per request — workable for one-off reconciliation, too slow and too fragile for a dashboard.

### 3.2 Proposed extension: an encounter *fact* layer

Extend the existing sync (`ProjectData6Service::syncRecord`) rather than adding a parallel pipeline:

1. **`data6_encounters.fields` (JSON)** — during sync, pivot the ~70 indicator-relevant fields (the union of every field named in §2) into a JSON map on the encounter row: `{"art_arv_status": "2", "art_vl_result": "40", ...}`. Raw values, keyed by field name — auditable, and new indicators need no schema change.
2. **Generated/typed columns for hot paths** — promote the handful of fields used in cohort logic to real indexed columns on `data6_encounters`: `next_appointment_date` (from `art_next_review_date` / `prep_next_visit_date`), `outcome_code` (from `art_final_outcome` / `pncm_mother_follow_up` / `prep_follow_up_status`), `vl_result_numeric`, `vl_tnd` (bool), `arv_status`, `arv_category`.
3. **`data6_patient_dimensions`** (or columns on `data6_patients`) — dob, gender, district, facility, client_profile, marital status, education, resolved once per patient at sync with conflict flags. Age-at-encounter is computed in SQL from dob + `service_date`.
4. **Dedup at sync time** — implement the §1 rules: encounters are unique by `(record, instrument, business_date, normalized_instance)`. Service-specific forms are read only from their home project (stale copies skipped with a data-quality counter); shared-instrument encounters are unioned across projects and collapsed on the dedup key, keeping every source project on the canonical encounter. `COUNT(DISTINCT ...)` then never counts a copied row.
5. **`data6_indicator_snapshots`** (cache) — computed indicator values keyed by `(indicator_id, period, filters_hash)` with a refresh command `php artisan data6:indicators:refresh`; invalidated by sync.

### 3.3 Indicator engine

- **`config/data6_indicators.php`** — declarative registry: id (1–45), key, label, group, frequency, numerator/denominator description (verbatim from the xlsx for traceability), handler class, disaggregation dims, status (`active` / `proxy` / `blocked`).
- **`app/Services/Data6/Indicators/`** — one class per group (`AccessIndicators`, `HivTestingIndicators`, `ArtCascadeIndicators`, `MnchIndicators`, `FpPrepIndicators`, `MentalHealthIndicators`, `StiIndicators`, `PeerIndicators`), each exposing `compute(IndicatorRequest $req): IndicatorResult` returning value, numerator, denominator, trend series, and data-quality counters (missing dates, unmatched records, invalid values).
- **Cohort logic** (ind. 8–10, 33): a `Data6ArtStatusService` that materialises each patient's latest-visit status per month-end — same shape as the existing NCD LTFU logic, but written fresh against `art_*` fields (per the onboarding plan's warning not to reuse NCD field semantics).
- **Reconciliation**: `php artisan data6:indicators:reconcile {period}` re-computes each indicator with independent raw SQL against `redcap_data6` and diffs — required before stakeholder sign-off (per original plan's testing section).

### 3.4 API

```
GET  /api/data6/indicators?period=2026-Q2&district=&facility=&gender=&age_band=&project_id=
     → all indicator values + trends for the filter set
GET  /api/data6/indicators/{id}/breakdown?dim=facility|district|age_band|gender|month
     → disaggregation table for one indicator
GET  /api/data6/indicators/{id}/patients   (authorized roles only)
     → paginated drill-down list backing a numerator
```

Validation, policies, and caching follow the conventions already in `ProjectDashboardController`.

---

## 4. Dashboard design (Vue / Inertia)

Extend `resources/js/pages/Data6/` keeping the visual language of the existing `Index.vue`:

**Layout** — one dashboard page with global filter bar + six tabs mirroring the indicator groups:

| Tab | Indicators | Key visuals |
|---|---|---|
| Overview | 1–3 + headline stats from every group | KPI cards, monthly encounter trend, facility league table |
| HIV Testing & ART | 4–17 | Testing→positive→initiated→current→suppressed **cascade funnel**; TX_CURR trend; retention cohort chart; LTFU/TO/death outcome breakdown |
| ANC & PNC | 18–28 | Booking trend, delivery-place split, PNC-within-72h gauge, PMTCT panel |
| FP & PrEP | 29–34 | New vs repeat FP, PrEP continuum (screened→initiated→continuing→discontinued) |
| Mental Health & Substance Use | 35–40 | Screened→positive→managed funnels ×2 |
| Peer & Community | 43–45 (+41–42 STI can live here or in FP/PrEP tab) | Session counts, reach trend |

**Global filters:** period (month/quarter + custom range), district, facility, gender, age band (10–14 / 15–19), service entry project. Every chart and table respects them.

**Per-indicator card:** value, period-over-period delta, sparkline, numerator/denominator shown explicitly, and a ⓘ popover with the exact definition from the xlsx. Proxy indicators (⚠️ in §2) carry a visible "proxy definition" badge until confirmed.

**Data-quality strip (admin-visible):** unmatched records, mirror conflicts, invalid/missing dates, infants-in-adolescent-counts — per the manager reporting rules in `data_dictionary.md`.

**Export:** per-tab XLSX/CSV export of the disaggregated tables (monthly/quarterly report hand-off).

---

## 5. Delivery phases

| Phase | Scope | Depends on |
|---|---|---|
| **A. Confirm & profile** | Formal `php artisan data6:profile` command producing the §1 findings as a saved JSON/report; sign off with the data owner on: mirror/home-project rule, adolescent age band, the 8 ⚠️ items | — |
| **B. Fact layer** | Migration for `fields` JSON + typed columns + patient dimensions + dedup rule; extend `syncRecord`; full backfill command `data6:sync-all`; indexes | A |
| **C. Engine core + Group A/B** | Indicator registry, `IndicatorResult` DTO, snapshots cache, indicators 1–6, reconciliation command, feature tests | B |
| **D. Dashboard shell** | Filter bar, tab layout, Overview tab wired to 1–6, KPI/chart components | C |
| **E. ART cascade** | `Data6ArtStatusService` (month-end status), indicators 7–17, HIV/ART tab | C |
| **F. Remaining groups** | MNCH 18–28, FP/PrEP 29–34, MH 35–40, STI 41–42, Peer 43–45 + their tabs (deliverable per group, in stakeholder priority order) | C |
| **G. Hardening & sign-off** | Exports, caching TTL + manual refresh, authorization review, full reconciliation vs REDCap exports, stakeholder validation of every indicator against a fixed period | E, F |

Each phase lands with its tests; existing project 32/39/48 suites must stay green throughout (guiding decision #1 of the onboarding plan).

---

## 6. Open decisions (blockers before their phase)

1. ~~Dedup rule~~ **Resolved (2026-09-02):** dedup confirmed; conflicting duplicates resolve to the updated version (home project for service forms, divergent-from-baseline for shared instruments), logged to data quality. Only the home-project assignments per form remain to be double-checked against the split-copy cutoff dates during Phase A profiling.
1b. ~~Project 48 linkage~~ **Resolved (2026-09-02):** record values continue from project 48; pre-split history is valid clinical history, entered from manual registers. Treat `redcap_data3` project 48 as a reconciliation source for ART cohort baselines.
2. **Adolescent definition** — 10–19 assumed; and how to treat the ~300 infant records with 2025/26 birthdates in access counts (blocks Phase C).
3. **HIV testing scope (ind. 4–5)** — which entry points count: HTS only, or HTS+STI+PrEP+ANC?
4. **ART initiation definition (ind. 7)** — `art_arv_status=2` vs `art_arv_category=1` vs `hts_art_init`.
5. **Stillbirths (ind. 22)** — no source field; needs REDCap form change or removal from the dashboard scope.
6. **≥8 ANC contacts (ind. 19)** — pregnancy grouping rule without a pregnancy ID.
7. **Proxy indicators 26, 27, 38, 40, 45** — accept the documented proxies or amend forms.
8. **BBA classification (ind. 21)** — home or separate category.
