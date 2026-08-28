# Project Data 6 Onboarding Plan

## Objective

Onboard the REDCap mobile data currently stored in `redcap_data6` so the application can analyse the three split service projects while preserving the existing analysis for `redcap_data3` projects `32`, `39`, and `48`.

The new service partitions belong to one analytical project. They are:

| Project ID | Working name | Expected scope |
|---:|---|---|
| 76 | FCH | FCH service data and shared demographics |
| 78 | OI/ART | OI/ART service data and shared demographics |
| 79 | OPD | Outpatient service data and shared demographics |

The supplied REDCap exports show a long-format table with rows identified by:

```text
(project_id, event_id, record, field_name, value, instance)
```

`record` is the candidate patient identifier. It must be validated across projects before it is treated as a global patient key.

All combined-project indicators must query project IDs `76`, `78`, and `79` together by default. Source project ID remains available for service-level breakdowns, reconciliation, and drill-down, but it must not create three separate patient populations in the main analysis.

## Current Findings

- `ProjectData3` is hard-coded to the `redcap_data3` table.
- `ProjectDataService` and several package services query `ProjectData3` directly.
- The main dashboard currently lists only project IDs `32`, `39`, and `48`.
- No `ProjectData6` model or `redcap_data6` service abstraction currently exists.
- `redcap_data6` contains common demographic fields, including `record_id`, district, facility, name, date of birth, gender, contact, and client profile.
- Service-specific fields are identifiable by prefixes/forms, for example `sti_*`, `opd_*`, and fields belonging to FCH and OI/ART forms.
- Values are stored as REDCap raw codes. Reporting must use metadata/code labels rather than embedding labels throughout query logic.
- The current schema has no declared primary key. Queries must therefore avoid assumptions that one row is uniquely addressable by an auto-increment ID.

## Guiding Decisions

1. Keep `redcap_data3` and its existing analytics working while onboarding data6.
2. Add a dedicated `ProjectData6` model first; do not silently point `ProjectData3` at another table.
3. Put cross-project patient and encounter logic in a service layer, not in controllers or Vue components.
4. Treat `record` as a source-system identifier until profiling proves that it is globally stable across projects `76`, `78`, and `79`.
5. Keep raw REDCap values available for auditability, while exposing decoded labels through metadata-backed mappings.
6. Use database aggregation and pagination for high-volume views. Do not load the entire data6 table into PHP collections.
7. Avoid including personally identifiable information in aggregate analytics responses unless a patient-detail workflow explicitly requires it.

## Phase 0: Profile and Confirm the Data

Before implementation, run a read-only profile against the live database and save the results with the implementation notes:

- Row count by project ID (`76`, `78`, `79`).
- Distinct record count by project ID.
- Distinct records across all three projects.
- Records appearing in more than one split project.
- Duplicate rows for the same `(project_id, record, event_id, field_name, instance)`.
- Null or blank records and `record_id` mismatches.
- Distinct event IDs and instances by project.
- Minimum and maximum values for date-like fields.
- Distinct field names by project, grouped by form/prefix.
- Missingness of core demographics and service access/date fields.
- Whether the same record has conflicting name, date-of-birth, gender, facility, or contact values across projects.

Example profiling queries should be written as a repeatable Artisan command or documented SQL, rather than run manually only once.

### Identity questions to resolve

- Is `record` the same identifier in FCH, OI/ART, and OPD for a client who uses more than one service?
- Does `record_id` always equal `record`?
- Can a client have more than one `record` because of a facility, year, or migration change?
- Is `record` unique across the old project 48 and the new projects, or do old and new namespaces overlap?
- Which field is the authoritative service date for each form?
- Does `instance` represent a repeat visit, and is it scoped by event and project?

Do not build a deduplicated patient dashboard until these questions have answers from the data owner or profiling results.

## Phase 1: Establish the Data Access Boundary

Create the following application pieces:

- `App\Models\ProjectData6`, mapped to `redcap_data6`.
- A shared read/query abstraction for long-format REDCap data, or a narrowly scoped `ProjectData6Service` if introducing a shared abstraction would create unnecessary risk.
- Query helpers for:
  - project-scoped rows;
  - one patient/record across projects;
  - one event and repeat instance;
  - field existence and value lookup;
  - distinct record counts;
  - date filtering;
  - aggregate counts by facility, district, sex, age group, and service.
- Proper indexes after profiling confirms the workload. Candidate composite indexes are `(project_id, record)`, `(project_id, field_name)`, and `(project_id, record, event_id, instance)`; validate index size and query plans before applying them.

The model should not pretend that the table has a conventional single-column primary key. Use explicit query constraints for project, record, event, field, and instance.

## Phase 2: Centralise REDCap Metadata and Decoding

Use the existing REDCap metadata tables if they contain the data6 definitions. If data6 metadata is not present, add an import process for the supplied CSV dictionaries.

The metadata layer should provide:

- field name;
- form name;
- field label;
- field type;
- choice code to label mapping;
- required/branching information where useful;
- project/version association.

Decoding rules:

- Preserve the raw value in the data-access result.
- Decode radio, dropdown, yes/no, and checkbox values using metadata.
- Handle checkbox fields as multiple selected codes, not as a single scalar value.
- Treat blank, `NA`, `NIL`, `UN`, and similar values according to an explicit reporting policy.
- Parse dates with validation and expose invalid dates in a data-quality report instead of silently changing them.
- Keep code mappings project/version-aware because forms can change over time.

## Phase 3: Define the Unified Patient and Encounter Contract

Introduce a read model or DTO for analytics rather than passing raw Eloquent rows into every controller.

### Patient-level fields

At minimum:

- canonical patient key;
- source records and source project IDs;
- facility and district;
- date of birth or derived age band;
- gender;
- client profile;
- first and last observed dates;
- services accessed;
- data-quality flags;
- conflict flags for demographics.

### Encounter/service-level fields

At minimum:

- canonical patient key;
- source project ID and source record;
- event ID and repeat instance;
- form/service name;
- service access status;
- service date;
- selected condition or intervention;
- raw source row references for drill-down/audit.

The canonical patient key must be configurable. Initially use a documented key based on the source record only if profiling confirms that record values are shared across projects. Otherwise create a mapping table with explicit source-to-canonical links and an exception queue for ambiguous matches.

## Phase 4: Onboard the Three Projects in the UI

Add one grouped data6 project to the dashboard through configuration. Project IDs `76`, `78`, and `79` should be shown as service partitions inside the grouped project, rather than as three independent projects.

Recommended first UI slice:

1. A combined project overview showing unique clients, service encounters, facilities, and date range.
2. Service tabs or filters for FCH, OI/ART, and OPD.
3. Filters for date range, district, facility, gender, age band, client profile, and service status.
4. A patient timeline that combines all three projects for one canonical client.
5. Patient detail/drill-down with source project, form, event, instance, field labels, and raw values where permitted.
6. Data-quality indicators visible to administrators, separate from programme indicators.

Do not copy the existing NCD/AHP controllers wholesale. Reuse layout and authorization conventions, but keep data6-specific query and indicator logic in new services/controllers.

## Phase 5: Initial Analysis Scope

Start with indicators that can be defined directly from the supplied common and service fields:

### Cross-project overview

- unique clients overall and by project/service;
- clients using one, two, or all three services;
- encounters by month/quarter;
- clients and encounters by facility and district;
- demographic distributions and missingness;
- new versus repeat encounters where the source field supports it.

### FCH

- confirm the FCH form names and service date/access fields from metadata;
- client volume and encounter trends;
- service-specific outcomes and follow-up indicators after field mapping is approved.

### OI/ART

- confirm OI/ART form names and fields from metadata;
- ART/OI enrolment, visits, outcomes, and retention indicators only after definitions are agreed;
- avoid reusing NCD outcome logic because the source fields and meanings differ.

### OPD

- OPD access and visit trends;
- disease/condition categories from `op_a1` and related fields;
- facility/district breakdowns;
- treatment/referral/outcome indicators after confirming all OPD fields.

Each indicator must have a written definition, numerator, denominator, inclusion/exclusion rules, date field, deduplication rule, and source fields.

## Phase 6: Performance and Operational Safety

- Use aggregate SQL queries for summary cards and charts.
- Paginate patient lists and timelines.
- Add request validation for project IDs and filter dates.
- Add authorization checks before exposing patient-level data.
- Cache expensive aggregate queries with a scoped key and a deliberate invalidation/refresh policy.
- Add indexes based on measured query plans.
- Add monitoring for row counts, latest observed service date, failed imports, invalid dates, and unmatched records.
- Never alter the REDCap source rows during analytics processing.

## Testing Plan

### Unit tests

- raw REDCap value decoding, including checkbox values;
- date parsing and invalid-date handling;
- age-band calculation;
- project/service classification;
- canonical patient matching and conflict detection;
- encounter deduplication.

### Feature tests

- project 76/78/79 overview loads;
- filters affect counts correctly;
- a record appearing in multiple projects is counted once in unique-client totals;
- encounters remain separately countable;
- patient detail is authorization-protected;
- project 32/39/48 existing analytics remain unchanged;
- empty projects and missing optional fields do not cause errors.

### Data validation checks

Compare application aggregates with independent SQL counts for a fixed date window and a small set of sampled records. Include a reconciliation report that lists:

- total source rows;
- rows consumed by each indicator;
- unmatched/ambiguous patient records;
- invalid or missing dates;
- demographic conflicts;
- duplicate source rows.

## Delivery Sequence

1. Run and review Phase 0 profiling.
2. Confirm the identity model and service/date field dictionary with the data owner.
3. Add `ProjectData6` and query tests.
4. Add metadata decoding and field mapping configuration.
5. Build the combined overview with aggregate queries.
6. Add patient timeline and controlled drill-down.
7. Implement approved FCH, OI/ART, and OPD indicators one service at a time.
8. Add indexes, caching, authorization review, and reconciliation monitoring.
9. Validate against REDCap exports and obtain stakeholder sign-off.

## Definition of Done

- Projects 76, 78, and 79 are visible and selectable in the application.
- Combined unique-client counts do not double-count a client across split projects.
- Service encounters retain their source project, event, and repeat-instance context.
- All displayed coded values have approved labels or are clearly marked as raw/unmapped.
- Indicators have documented definitions and pass independent SQL reconciliation.
- Patient-level access is authorized and audited.
- Existing project 32, 39, and 48 functionality continues to pass its test suite.
- Data-quality exceptions are measurable and visible to administrators.
- The implementation remains performant on the full mobile dataset.

## Open Decisions Before Coding

- Confirm whether `record` is a globally shared patient identifier across 76, 78, and 79.
- Confirm how project 48 historical records should connect to the new split projects.
- Confirm whether project 48 remains an active source or is historical only.
- Confirm the authoritative date and access fields for every form/service.
- Confirm which patient-identifying fields may be displayed and to which roles.
- Confirm the first stakeholder-approved indicators for FCH, OI/ART, and OPD.
- Confirm whether data6 metadata is already available in the application database or must be imported from CSV.
