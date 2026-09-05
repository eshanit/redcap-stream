-- ============================================================================
-- Cleanup: permanently remove records with NO clinical-service data
-- Scope:   REDCap projects 76 (FCH), 78 (OI/ART), 79 (OPD) — table redcap_data6
-- Requested by: M&E Officer, 2026-09-03 (expanded rule 2026-09-03)
-- Requirement:  records must NOT re-load onto the REDCap Mobile App;
--               a backup of the deleted rows must be kept permanently.
--
-- DELETION RULE (expanded per client request):
-- Delete a record when its ONLY data is any combination of:
--   1) Demographics            demog_*
--   2) Counselling             couns_*, counc_*
--   3) Health Education        he_*, hp_*
-- i.e. the four requested cases (demog only; demog+counselling; demog+HE;
-- demog+HE+counselling) are all covered by one rule: the record has NO row
-- outside demog_/couns_/counc_/he_/hp_, record_id, and REDCap form-status
-- (*_complete) fields.
--
-- The rule is GLOBAL across the three projects: a record is deleted only if
-- it has no clinical-service data in ANY of 76/78/79 (confirmed 2026-09-03).
--
-- Prefix-safety notes:
--   - 'he\_%'  does NOT match hts_* (h-t-s) or health_education_complete
--     (h-e-a): the escaped underscore requires literally "he_".
--   - counselling_complete / health_education_complete are form-status
--     fields and were already excluded as non-data.
--   - Do NOT simplify the status list to "LIKE '%_complete'":
--     anc_tt_complete and ancr_tt_complete are CLINICAL fields (tetanus
--     toxoid), not form statuses.
--
-- NOTES FOR THE DBA
-- 1. redcap_data6 is REDCap's own sharded data table (REDCap v13+). Deleting
--    rows here with raw SQL is NOT enough to stop the Mobile App re-loading
--    the records: REDCap also keeps the record list in redcap_record_list
--    (which the app uses when downloading records) and cached counts in
--    redcap_record_counts. PATH A (REDCap API) is still preferred even with
--    full DB access (we self-host): REDCap's own delete knows every table
--    our version touches and writes the audit log. Self-hosting makes it
--    easy — create the three project API tokens and run
--    delete_demog_only_records.sh from the server. PATH B (raw SQL) is
--    acceptable as a deliberate choice on our own server.
-- 2. Before EITHER path: full mysqldump snapshot of the REDCap database.
--    Sync ALL tablets in BEFORE deleting (a device pushing unsent data for a
--    deleted record afterwards would re-create it). After the run, verify on
--    ONE test tablet that a refreshed project no longer downloads a deleted
--    record, then refresh all devices.
-- 3. Expected impact from the 2026-08 dump: ~844 records, ~34,959 rows.
--    Breakdown: 656 demog-only, 140 demog+counselling, 36 demog+HE,
--    12 demog+HE+counselling. Live counts will differ slightly — compare
--    with Step 1 before deleting.
-- 4. The backup table (Step 2) is permanent: do not drop it. Also export it
--    with mysqldump to an offline file.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- The qualification test used by every query below ("non-service row" = 0):
--   CASE
--     WHEN field_name LIKE 'demog\_%' THEN 0      -- demographics
--     WHEN field_name LIKE 'couns\_%' THEN 0      -- counselling
--     WHEN field_name LIKE 'counc\_%' THEN 0      -- counselling (counc_who)
--     WHEN field_name LIKE 'he\_%'    THEN 0      -- health education
--     WHEN field_name LIKE 'hp\_%'    THEN 0      -- health education (hp_topics_other)
--     WHEN field_name = 'record_id'   THEN 0
--     WHEN field_name IN (<form-status list>) THEN 0
--     ELSE 1                                      -- anything else = service data
--   END
-- ----------------------------------------------------------------------------

-- ----------------------------------------------------------------------------
-- STEP 1 — VERIFY: how many records/rows qualify? (read-only)
-- ----------------------------------------------------------------------------
SELECT COUNT(*)     AS records_to_remove,
       SUM(row_cnt) AS rows_to_remove
FROM (
    SELECT record, COUNT(*) AS row_cnt
    FROM redcap_data6
    WHERE project_id IN (76, 78, 79)
    GROUP BY record
    HAVING SUM(CASE
                 WHEN field_name LIKE 'demog\_%' THEN 0
                 WHEN field_name LIKE 'couns\_%' THEN 0
                 WHEN field_name LIKE 'counc\_%' THEN 0
                 WHEN field_name LIKE 'he\_%'    THEN 0
                 WHEN field_name LIKE 'hp\_%'    THEN 0
                 WHEN field_name = 'record_id'   THEN 0
                 WHEN field_name IN (
                   'demographics_complete',
                   'counselling_complete',
                   'health_education_complete',
                   'sti_register_complete',
                   'family_planning_complete',
                   'anc_initial_register_complete',
                   'anc_booking_follow_ups_complete',
                   'mother_baby_pair_initial_register_per_baby_complete',
                   'mother_baby_follow_ups_mother_complete',
                   'mother_baby_follow_ups_baby_complete',
                   'prep_initial_register_complete',
                   'prep_follow_ups_complete',
                   'mental_health_complete',
                   'peer_support_register_complete',
                   'hiv_testing_services_hts_register_complete',
                   'oiart_initial_register_complete',
                   'oiart_initial_baseline_complete',
                   'oiart_follow_ups_complete',
                   'outpatient_complete'
                 ) THEN 0
                 ELSE 1
               END) = 0
) no_service;

-- STEP 1a — breakdown by the client's four cases (for the M&E report):
SELECT CONCAT(
         'demog',
         IF(has_couns, ' + counselling', ''),
         IF(has_he,    ' + health education', '')
       ) AS case_type,
       COUNT(*) AS records
FROM (
    SELECT record,
           MAX(field_name LIKE 'couns\_%' OR field_name LIKE 'counc\_%') AS has_couns,
           MAX(field_name LIKE 'he\_%'    OR field_name LIKE 'hp\_%')    AS has_he
    FROM redcap_data6
    WHERE project_id IN (76, 78, 79)
    GROUP BY record
    HAVING SUM(CASE
                 WHEN field_name LIKE 'demog\_%' THEN 0
                 WHEN field_name LIKE 'couns\_%' THEN 0
                 WHEN field_name LIKE 'counc\_%' THEN 0
                 WHEN field_name LIKE 'he\_%'    THEN 0
                 WHEN field_name LIKE 'hp\_%'    THEN 0
                 WHEN field_name = 'record_id'   THEN 0
                 WHEN field_name IN (
                   'demographics_complete','counselling_complete',
                   'health_education_complete','sti_register_complete',
                   'family_planning_complete','anc_initial_register_complete',
                   'anc_booking_follow_ups_complete',
                   'mother_baby_pair_initial_register_per_baby_complete',
                   'mother_baby_follow_ups_mother_complete',
                   'mother_baby_follow_ups_baby_complete',
                   'prep_initial_register_complete','prep_follow_ups_complete',
                   'mental_health_complete','peer_support_register_complete',
                   'hiv_testing_services_hts_register_complete',
                   'oiart_initial_register_complete','oiart_initial_baseline_complete',
                   'oiart_follow_ups_complete','outpatient_complete'
                 ) THEN 0
                 ELSE 1
               END) = 0
) t
GROUP BY case_type;

-- STEP 1b — export the record list (M&E spot-check AND Path A input).
-- For Path A run this THREE times, once per project (replace <PID> with
-- 76, 78, 79) and save as records_<PID>.txt — one record per line. Each
-- project needs its OWN list because the delete API rejects a whole batch
-- if any record in it does not exist in that project:
--
--   SELECT DISTINCT d.record
--   FROM redcap_data6 d
--   JOIN ( <the Step 1 subquery> ) q ON q.record = d.record
--   WHERE d.project_id = <PID>
--   ORDER BY CAST(d.record AS UNSIGNED), d.record;

-- ----------------------------------------------------------------------------
-- STEP 2 — BACKUP (permanent): copy the doomed rows into a dated table.
-- ----------------------------------------------------------------------------
CREATE TABLE redcap_data6_removed_noservice_20260903 AS
SELECT d.*
FROM redcap_data6 d
JOIN (
    SELECT record
    FROM redcap_data6
    WHERE project_id IN (76, 78, 79)
    GROUP BY record
    HAVING SUM(CASE
                 WHEN field_name LIKE 'demog\_%' THEN 0
                 WHEN field_name LIKE 'couns\_%' THEN 0
                 WHEN field_name LIKE 'counc\_%' THEN 0
                 WHEN field_name LIKE 'he\_%'    THEN 0
                 WHEN field_name LIKE 'hp\_%'    THEN 0
                 WHEN field_name = 'record_id'   THEN 0
                 WHEN field_name IN (
                   'demographics_complete','counselling_complete',
                   'health_education_complete','sti_register_complete',
                   'family_planning_complete','anc_initial_register_complete',
                   'anc_booking_follow_ups_complete',
                   'mother_baby_pair_initial_register_per_baby_complete',
                   'mother_baby_follow_ups_mother_complete',
                   'mother_baby_follow_ups_baby_complete',
                   'prep_initial_register_complete','prep_follow_ups_complete',
                   'mental_health_complete','peer_support_register_complete',
                   'hiv_testing_services_hts_register_complete',
                   'oiart_initial_register_complete','oiart_initial_baseline_complete',
                   'oiart_follow_ups_complete','outpatient_complete'
                 ) THEN 0
                 ELSE 1
               END) = 0
) x ON x.record = d.record
WHERE d.project_id IN (76, 78, 79);

-- Must equal rows_to_remove from Step 1:
SELECT COUNT(*) AS backed_up_rows FROM redcap_data6_removed_noservice_20260903;

-- Offline copy (run from the shell):
--   mysqldump <db> redcap_data6_removed_noservice_20260903 > noservice_backup_20260903.sql

-- ============================================================================
-- STEP 3, PATH A (RECOMMENDED) — delete through the REDCap API.
-- Removes the record from the data table, record list, counts, and writes
-- the audit log; the Mobile App no longer receives these records after a
-- project refresh. Use delete_demog_only_records.sh with the per-project
-- lists from Step 1b (dry-run first, then --live), once per project token.
-- After Path A, skip Path B and go to Step 4.
-- ============================================================================

-- ============================================================================
-- STEP 3, PATH B (FALLBACK) — raw SQL. Must clean the record-list cache too,
-- or the Mobile App re-loads the records. COMMIT only after Step 4 passes.
--
-- RUNNING AGAINST A LOCAL COPY: partial imports of the REDCap schema (e.g.
-- a dev machine that has redcap_data6 and redcap_projects but not the cache
-- tables) will throw #1146 on 3B.2/3B.3 — redcap_record_list and
-- redcap_record_counts only matter on the LIVE server. On a copy, run 3B.1
-- only, then Step 4, then COMMIT. Cleaning a copy does NOT satisfy the
-- Mobile App requirement: the authoritative delete must still run on the
-- live REDCap server (Path A there, or this Path B where the cache tables
-- exist). A re-import from the uncleaned server also restores the records
-- locally.
-- ============================================================================
START TRANSACTION;

CREATE TEMPORARY TABLE tmp_noservice_records AS
SELECT record FROM redcap_data6
WHERE project_id IN (76, 78, 79)
GROUP BY record
HAVING SUM(CASE
             WHEN field_name LIKE 'demog\_%' THEN 0
             WHEN field_name LIKE 'couns\_%' THEN 0
             WHEN field_name LIKE 'counc\_%' THEN 0
             WHEN field_name LIKE 'he\_%'    THEN 0
             WHEN field_name LIKE 'hp\_%'    THEN 0
             WHEN field_name = 'record_id'   THEN 0
             WHEN field_name IN (
               'demographics_complete','counselling_complete',
               'health_education_complete','sti_register_complete',
               'family_planning_complete','anc_initial_register_complete',
               'anc_booking_follow_ups_complete',
               'mother_baby_pair_initial_register_per_baby_complete',
               'mother_baby_follow_ups_mother_complete',
               'mother_baby_follow_ups_baby_complete',
               'prep_initial_register_complete','prep_follow_ups_complete',
               'mental_health_complete','peer_support_register_complete',
               'hiv_testing_services_hts_register_complete',
               'oiart_initial_register_complete','oiart_initial_baseline_complete',
               'oiart_follow_ups_complete','outpatient_complete'
             ) THEN 0
             ELSE 1
           END) = 0;

-- 3B.1 the data rows ("rows affected" must equal Step 1's rows_to_remove):
DELETE d FROM redcap_data6 d
JOIN tmp_noservice_records t ON t.record = d.record
WHERE d.project_id IN (76, 78, 79);

-- 3B.2 the record-list cache (what the Mobile App downloads):
DELETE rl FROM redcap_record_list rl
JOIN tmp_noservice_records t ON t.record = rl.record
WHERE rl.project_id IN (76, 78, 79);

-- 3B.3 refresh cached record counts (forces REDCap to recount):
DELETE FROM redcap_record_counts WHERE project_id IN (76, 78, 79);

-- ----------------------------------------------------------------------------
-- STEP 4 — POST-CHECKS (before COMMIT on Path B; after API run on Path A).
-- ----------------------------------------------------------------------------
-- (a) Re-run Step 1: records_to_remove must be 0.
-- (b) No serviced record lost its demographics:
SELECT COUNT(DISTINCT d.record) AS serviced_records_missing_demog
FROM redcap_data6 d
WHERE d.project_id IN (76, 78, 79)
  AND NOT EXISTS (
      SELECT 1 FROM redcap_data6 g
      WHERE g.record = d.record
        AND g.project_id IN (76, 78, 79)
        AND g.field_name LIKE 'demog\_%'
  );
-- Expect 0 (or unchanged from a pre-delete baseline).

COMMIT;   -- Path B only, and only when (a) and (b) pass; otherwise ROLLBACK;

-- ----------------------------------------------------------------------------
-- STEP 5 — OPERATIONAL FOLLOW-UP (both paths)
-- ----------------------------------------------------------------------------
-- 1. Every tablet: refresh / re-set-up projects 76, 78, 79 in the REDCap
--    Mobile App so locally cached copies of the deleted records disappear.
-- 2. Keep redcap_data6_removed_noservice_20260903 permanently + the
--    mysqldump file offline.
-- 3. If the analytics app has already synced, clean its tracking tables:
--    DELETE FROM data6_encounters
--      WHERE source_record_id IN (SELECT id FROM data6_source_records sr
--        WHERE sr.project_id IN (76,78,79)
--          AND sr.redcap_record IN (SELECT DISTINCT record
--                FROM redcap_data6_removed_noservice_20260903));
--    DELETE FROM data6_source_records
--      WHERE project_id IN (76,78,79)
--        AND redcap_record IN (SELECT DISTINCT record
--              FROM redcap_data6_removed_noservice_20260903);
--    -- then delete data6_patients left with no source records.
