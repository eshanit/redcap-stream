After analyzing both the **onboarding plan** and the **actual database structure** (from `redcap_data6.sql`), here is a **refined and unified technical plan** to build a Laravel‑based reporting and analysis application for the new split service projects (FCH, OI/ART, OPD). This plan respects the existing `redcap_data3` setup, adds a dedicated data layer for `redcap_data6`, and addresses all open decisions mentioned in the original document.

---

## 1. Data & Schema Insights

- The table `redcap_data6` uses the **standard REDCap long format**:
  - `(project_id, record, event_id, field_name, instance)` uniquely identifies a data point.
  - `record` is a **free‑text identifier** (numeric or alphanumeric) – its stability across projects is **unknown** until profiling.
  - The sample data (project 76) contains **only demographics** fields (`demog_*`) – service‑specific fields (`sti_*`, `opd_*`, etc.) are not shown but will appear once actual service forms are included.
  - All rows share the same `event_id` (1420), suggesting a **single event** per project, but we must code generically.

**Implications**  
- We cannot rely on an auto‑increment `id` – all queries must use the composite key.
- The table is **read‑only** for analytics – never write or alter source rows.
- The `value` column is raw REDCap codes – decoding requires metadata.

### Project and Instrument Matrix

The three `redcap_data6` projects are:

| Project | Project ID | Common instruments | Project-specific instruments |
|---------|------------|--------------------|-----------------------------|
| FCH | `76` | `demog_*`, `sti_*`, `prepr_*`, `prep_*`, `mh_*`, `he_*`, `couns_*`, `pls_*`, `hts_*` | `fp_*`, `ancr_*`, `anc_*`, `pncr_*`, `pncm_*`, `pncb_*` |
| OI/ART | `78` | `demog_*`, `sti_*`, `prepr_*`, `prep_*`, `mh_*`, `he_*`, `couns_*`, `pls_*`, `hts_*` | `artr_*`, `artib_*`, `art_*` |
| OPD | `79` | `demog_*`, `sti_*`, `prepr_*`, `prep_*`, `mh_*`, `he_*`, `couns_*`, `pls_*`, `hts_*` | `opd_*` |

The prefix is used to map a `redcap_data6.field_name` to its instrument. Project membership must still be applied because the same prefix can occur in more than one project. The original REDCap CSV/data dictionary and live REDCap metadata are authoritative if a prefix or instrument name differs from this documentation.

### Registration and Follow-up Instrument Rules

The field-name prefix identifies the stage and subject of a service workflow. It is not merely a naming convention; it is required when reconstructing registrations, follow-ups, and encounters from the long-format table.

| Workflow | One-time registration | Repeated follow-ups | Identifier / relationship |
|----------|------------------------|---------------------|---------------------------|
| ANC | `ancr_*` (ANC Initial Register) | `anc_*` (ANC follow-ups) | The ANC registration belongs to the REDCap `record`; follow-up instances belong to the same record and event. |
| Mother-baby | `pncr_*` (mother-baby pair initial register) | `pncm_*` for mother follow-ups and `pncb_*` for baby follow-ups | `pncr_mother_baby` is the unique mother-baby number. A separate mother-baby registration is expected for each baby. |
| PrEP | `prepr_*` (PrEP initial register) | `prep_*` (PrEP follow-ups) | The PrEP number and REDCap record must be retained as separate identifiers. |
| OI/ART | `artr_*` (OI/ART Initial Register), then `artib_*` (OI/ART Initial Baseline) | `art_*` (OI/ART follow-ups) | `artr_*` and `artib_*` are initial one-time forms; follow-up instances belong to the same record and event. Use `art_review_date` as the follow-up business date. |

The following instruments do not have a separate follow-up prefix. Their repeated visits are represented by the same prefixed fields with either a date field, an `instance`, or both:

| Instrument | Field prefix | Visit/repeat rule |
|------------|--------------|-------------------|
| STI Register | `sti_*` | Prefer `instance` as the repeat discriminator; use `sti_visit_date` (and `sti_date` where applicable) as the visit date and fallback repeat key when no usable instance exists. |
| Family Planning | `fp_*` | Prefer `instance` as the repeat discriminator; use `fp_date` as the encounter date and fallback repeat key when no usable instance exists. |
| Mental Health | `mh_*` | Repeated visits are distinguished by `instance`. |
| Health Education | `he_*` | Repeated visits are distinguished by `instance`. |
| Counselling | `couns_*` | Repeated visits are distinguished by `instance`. |
| Peer Support Register | `pls_*` | Repeated visits are distinguished by `instance`. |
| HIV Testing Services | `hts_*` | Repeated visits are distinguished by `instance`. |

For these instruments, retain the raw `instance` and date values. When `instance` is present it remains the primary repeat identity; when it is absent or unusable, the relevant date may be used as a provisional repeat key and must be marked as date-derived. Duplicate or conflicting dates should become data-quality findings rather than silently merging rows.

The term `pnc_*` may be used as a family prefix in reporting discussions, but the concrete dictionary fields are `pncm_*` and `pncb_*`; these must remain separate so that mother and baby observations are not merged.

For a single REDCap record, the application must therefore distinguish:

- a one-time registration form;
- an optional one-time baseline form after registration (for OI/ART, `artib_*`);
- each repeated follow-up form;
- the form subject (for example, mother or baby); and
- identifiers captured inside the form, such as `pncr_mother_baby` or `prepr_prep_number`.

The effective repeatable encounter key is:

```text
project_id + record + event_id + form_name + normalized_instance
```

`event_id` identifies the longitudinal event and is resolved through `redcap_events_metadata`; its `arm_id` identifies the event arm. `instance` identifies a repetition of a particular repeating instrument within that event. REDCap exports may represent the first/non-repeating occurrence as `NULL` or blank (and some application code may expose it as `0`), so the raw value must be retained and a separate normalized instance should be used for reporting. Never combine instance numbers across different forms.

### Cross-Project Patient and Service Tracking

Patient tracking uses two identities. The immutable source identity is `project_id + record`; the application-level patient identity is a separate canonical patient ID. A source record is never automatically merged with another project record solely because names look similar. Source records are linked to a canonical patient with a match method, confidence, review status, and audit fields so uncertain matches can be reviewed or undone.

The analytics read model contains `data6_source_records`, `data6_patients`, `data6_patient_source_records`, and `data6_encounters`. The first synchronization creates one canonical patient for each source record. Cross-project links can subsequently be added only through a confirmed identifier or a reviewed match. Mother-baby data retains separate mother and baby subjects and uses `pncr_mother_baby` as the pair relationship identifier, not as a replacement for patient identity.

### Manager Reporting Model

The primary dashboard audience is project managers. The main reporting unit is a **unique canonical patient**, grouped by facility, service, and reporting period. For example: “How many unique patients accessed STI at facility X during quarter Y?”

Reports must use `COUNT(DISTINCT canonical_patient_id)` after joining encounters through the patient/source-record link. They must never add counts from projects 76, 78, and 79, because one patient may appear in more than one project. Each report must show the matching scope, unresolved source records, and the rule used for date and facility selection.

Required dimensions are facility, service/instrument family, project where needed, calendar period based on the authoritative business date, and subject type, especially client versus mother versus baby. Facility/service/period analysis is the primary dashboard workflow; patient timeline lookup is a secondary drill-down for investigating a count.

---

## 2. Architectural Decisions

- **Keep `redcap_data3` untouched** – its existing models, services, and UI continue to work for projects 32, 39, 48.
- **Introduce a new model `ProjectData6`** mapped explicitly to `redcap_data6` (no primary key).
- **Build a dedicated service layer** (`Data6Service`, `MetadataService`, `PatientService`) – **not** controllers or Vue components – to encapsulate all business logic.
- **Use metadata (data dictionary)** to decode values; raw values remain available for audit.
- **Implement a canonical patient key** – initially use `record` as the key, but with a **configurable mapping table** once cross‑project identity is proven.

---

## 3. Recommended Database Extensions (New Tables)

For performance and maintainability, add the following tables **alongside** `redcap_data6` (do not alter REDCap tables):

| Table | Purpose |
|-------|---------|
| `redcap_metadata` | Store field definitions per project (field_name, form, label, type, choices as JSON, required, etc.) |
| `patient_canonical` | Map source `(project_id, record)` to a `canonical_id` (auto‑increment). Include confidence flags, merge status, and audit timestamps. |
| `aggregated_patients` (optional) | Materialised view or table for patient‑level aggregated data (demographics, first/last service dates) to speed up dashboards. |
| `indicator_cache` | Cache for expensive aggregate queries with cache keys based on filters. |

> *All new tables should be in the same database but clearly separated from REDCap source data.*

---

## 4. Detailed Implementation Phases

### Phase 0 – Data Profiling (Artisan Command)
Create a command `php artisan data6:profile` that runs **read‑only** SQL queries and outputs a JSON report.

**Must‑have checks**:
- Row count by `project_id` (76, 78, 79).
- Distinct `record` counts per project and overall.
- Overlap of `record` values across projects (to determine if they are shared).
- Duplicate rows for same `(project_id, record, event_id, field_name, instance)`.
- Null/blank `record` or `record_id` mismatches.
- Distinct `event_id` and `instance` per project.
- Date‑like field min/max (e.g., `demog_dateofbirth`).
- Distinct `field_name` grouped by prefix/form.
- Missingness of core demographics and service date fields.
- Demographic conflicts for records appearing in >1 project.

> This command must be **repeatable** and saved with a timestamp for later reference.

### Phase 1 – Data Access Layer

#### 1.1 Model `ProjectData6`
```php
class ProjectData6 extends Model
{
    protected $table = 'redcap_data6';
    public $timestamps = false;
    // No primary key – use query scopes
}
```

#### 1.2 Service `Data6Service`
Provide methods like:
- `getRows(array $projects, array $records = null, ...)`
- `getFieldValue(int $project, string $record, string $field)`
- `getDistinctRecords(array $projects)`
- `getAggregates($projects, $filters)` – using raw SQL for performance.

**Index recommendations** (after profiling):
- Composite index on `(project_id, record)` for patient lookups.
- Index on `(project_id, field_name)` for field‑specific queries.
- Index on `(project_id, record, event_id, instance)` if instance is frequently used.

#### 1.3 Query Helpers
Write **Eloquent scopes** for common filters:
- `scopeForProject($query, $projectId)`
- `scopeForRecord($query, $record)`
- `scopeFieldEquals($query, $fieldName, $value)`
- Use **cursor()** or **chunk()** to avoid memory exhaustion.

---

### Phase 2 – Metadata & Decoding

#### 2.1 Metadata Import
- Provide an Artisan command `php artisan metadata:import` that reads the supplied CSV dictionaries (per project).
- Store in `redcap_metadata` table with columns: `project_id`, `field_name`, `form_name`, `field_label`, `field_type`, `choices` (JSON array of code→label), `required`, `branching_logic` (if needed).

#### 2.2 `MetadataService`
- Decode raw values:
  - For `radio`, `dropdown`, `yesno`: map code to label from `choices`.
  - For `checkbox`: explode codes (e.g., `1,4,7`) and return array of labels.
  - For dates: parse with `Carbon`; invalid dates are logged and exposed as `null` with a data‑quality flag.
- Always keep `raw_value` in DTOs for audit.

#### 2.3 Handling special codes
- Define a configuration or policy for `NA`, `NIL`, `UN`, blank – e.g., treat as `null` unless explicitly needed.

---

### Phase 3 – Patient Identity & Canonical Key

#### 3.1 Initial Approach
- **Assume `record` is globally unique** across the three new projects (this is the “open decision” to confirm).
- Use `record` as the `canonical_key` until profiling proves otherwise.

#### 3.2 Mapping Table (if needed)
If records overlap or are inconsistent, create `patient_canonical`:
- `source_project_id`, `source_record`, `canonical_id` (auto‑increment), `confidence`, `merged_by`, `merged_at`.
- A service `PatientService::resolve($projectId, $record)` returns the canonical ID.
- For now, simply return `record` as a string.

#### 3.3 DTOs (Read Models)
- **PatientDto**: `canonicalKey`, `sourceProjects`, `facility`, `district`, `dob`, `ageBand`, `gender`, `clientProfile`, `firstObservedDate`, `lastObservedDate`, `servicesAccessed`, `dataQualityFlags`, `demographicConflicts`.
- **EncounterDto**: `canonicalKey`, `sourceProjectId`, `sourceRecord`, `eventId`, `instance`, `serviceName`, `accessStatus`, `serviceDate`, `condition/intervention`, `rawRowReferences`.

---

### Phase 4 – Unified Dashboard & UI

#### 4.1 Backend API Endpoints (Laravel Controllers)
- `GET /api/data6/overview` – returns unique clients, encounters, facilities, date range.
- `GET /api/data6/clients` – paginated list with filters (date, district, facility, gender, age band, service).
- `GET /api/data6/client/{id}/timeline` – combines all three projects for one client.
- `GET /api/data6/indicators/{service}` – returns predefined indicators for FCH, OI/ART, OPD.

**Important**: All queries should be **aggregate SQL** (using `DB::select()`) for summary views; avoid loading full rows into PHP collections.

#### 4.2 Frontend (Vue)
- **Single dashboard** with a toggle for the grouped data6 project.
- Service tabs (FCH, OI/ART, OPD) that apply source‑project filters.
- Filters and chart components reusing existing layout conventions.

---

### Phase 5 – Indicators (Phase 5 of original)

Write **SQL definitions** for each indicator (numerator, denominator, inclusion/exclusion, date field, deduplication).  
Implement them as **reusable scoped queries** in the `Data6Service` or as **separate reporting classes**.

**Example structure**:
```php
class FchIndicators
{
    public function getEnrolments($filters) { /* SQL */ }
    public function getFollowUpRates($filters) { /* SQL */ }
}
```
Each indicator must have a **written definition** and be validated by **independent SQL reconciliation**.

---

### Phase 6 – Performance & Safety

- **Indexing** – apply after profiling and before production.
- **Caching** – use Laravel’s cache with tags (e.g., `data6:overview:{$filterHash}`). Set a TTL (e.g., 15 minutes) and provide a manual refresh button for admins.
- **Pagination** – all patient lists use `paginate()`.
- **Authorization** – use Laravel Policies to control access to patient‑level data.
- **Monitoring** – log row counts, failed imports, invalid dates; send alerts if data volume changes unexpectedly.

---

### Phase 7 – Testing & Reconciliation

- **Unit tests** – decoding, date parsing, age bands, patient matching, deduplication.
- **Feature tests** – each API endpoint, filters, uniqueness of client counts, authorisation.
- **Data reconciliation** – run an Artisan command that compares application aggregates with raw SQL counts for a fixed date range; produce a report with differences.

---

## 5. Open Decisions (to be resolved before coding)

1. **Is `record` globally shared across 76, 78, 79?**  
   → Run Phase 0 profiling and confirm.

2. **How does project 48 connect to the new projects?**  
   → If no relation, keep it separate; if some clients overlap, we may need a separate mapping.

3. **Is project 48 active or historical only?**  
   → This affects whether we need to include it in the new combined view.

4. **Authoritative service date fields** – for each form, identify the field that holds the encounter date (e.g., `opd_date`, `fch_date`, `art_date`).

5. **Which identifying fields may be displayed to which roles?**  
   → Define data classification (PII, sensitive) and apply appropriate masking.

6. **First stakeholder‑approved indicators** – get a list of specific metrics for FCH, OI/ART, OPD.

7. **Metadata availability** – confirm if data6 metadata is already in the app database or must be imported from CSV.

---

## 6. Delivery Sequence (Revised)

1. **Run Phase 0 profiling** and share report with stakeholders.
2. **Resolve open decisions** (identity, date fields, metadata).
3. **Create database migrations** for the new tables (metadata, patient mapping, cache tables).
4. **Implement `ProjectData6` model and `Data6Service`** with basic query methods.
5. **Implement metadata import command** and `MetadataService`.
6. **Build the unified overview API** (aggregates) and test with raw SQL.
7. **Add patient timeline and drill‑down endpoints** (with authorisation).
8. **Implement the first set of indicators** for one service (e.g., FCH) and reconcile.
9. **Add caching, indexes, and monitoring**.
10. **Develop the UI** (Vue) dashboard, service tabs, and filters.
11. **Repeat indicators for OI/ART and OPD**.
12. **Final reconciliation and stakeholder sign‑off**.

---

## 7. Summary of Improvements over Original Plan

- **Detailed database schema** suggestions beyond the model.
- **Explicit service layer** for patient identity and metadata.
- **Concrete index and caching strategies**.
- **Separation of raw and decoded values** in DTOs.
- **Integration with existing Laravel structure** (models, commands, policies).
- **Clearer testing and reconciliation** steps.
