#!/usr/bin/env bash
# ============================================================================
# Path A runner: delete demographics-only records via the REDCap API.
# Run ON the REDCap server (or any host that can reach the API endpoint).
#
# Usage:
#   ./delete_demog_only_records.sh <API_URL> <TOKEN> <RECORDS_FILE> [--live]
#
#   API_URL       e.g. https://redcap.example.org/api/
#   TOKEN         the API token for ONE project (76, 78 or 79)
#   RECORDS_FILE  records_<PID>.txt from Step 1b — one record per line,
#                 exported per project (the API rejects a whole batch if any
#                 record in it does not exist in that project)
#   --live        actually delete. Without it the script only prints what it
#                 WOULD do (dry run).
#
# Run once per project with that project's token and that project's list.
# Everything is logged to delete_log_<timestamp>.txt in the current dir.
#
# PREREQUISITES (do these first, in this order):
#   1. Run STEP 1 + 1b + STEP 2 (backup) of remove_demog_only_records.sql.
#   2. Full mysqldump snapshot of the REDCap database.
#   3. M&E Officer has signed off on the exported record lists.
#   4. Smoke test: run with a 2-3 record test file and --live on ONE project,
#      confirm in the REDCap UI that those records are gone and logged, then
#      run the full lists.
# ============================================================================
set -euo pipefail

API_URL="${1:?API URL required}"
TOKEN="${2:?API token required}"
RECORDS_FILE="${3:?records file required}"
MODE="${4:-dry-run}"
BATCH_SIZE=100
LOG="delete_log_$(date +%Y%m%d_%H%M%S).txt"

[[ -f "$RECORDS_FILE" ]] || { echo "Records file not found: $RECORDS_FILE"; exit 1; }

mapfile -t RECORDS < <(tr -d '\r' < "$RECORDS_FILE" | sed '/^[[:space:]]*$/d')
TOTAL=${#RECORDS[@]}
echo "Loaded $TOTAL records from $RECORDS_FILE (mode: $MODE)" | tee -a "$LOG"

DELETED=0
FAILED_BATCHES=0

for ((i=0; i<TOTAL; i+=BATCH_SIZE)); do
  BATCH=("${RECORDS[@]:i:BATCH_SIZE}")

  # Build the curl form fields: records[0]=..., records[1]=...
  FIELDS=()
  for j in "${!BATCH[@]}"; do
    FIELDS+=(--data-urlencode "records[$j]=${BATCH[$j]}")
  done

  FIRST="${BATCH[0]}"; LAST="${BATCH[-1]}"
  if [[ "$MODE" != "--live" ]]; then
    echo "[dry-run] batch $((i/BATCH_SIZE+1)): would delete ${#BATCH[@]} records ($FIRST .. $LAST)" | tee -a "$LOG"
    continue
  fi

  # REDCap returns the number of deleted records on success, or an error
  # message (with returnFormat=json, a JSON {"error": "..."}).
  RESPONSE=$(curl -sS -X POST "$API_URL" \
      --data-urlencode "token=$TOKEN" \
      --data-urlencode "action=delete" \
      --data-urlencode "content=record" \
      --data-urlencode "returnFormat=json" \
      "${FIELDS[@]}")

  if [[ "$RESPONSE" =~ ^[0-9]+$ ]]; then
    DELETED=$((DELETED + RESPONSE))
    echo "batch $((i/BATCH_SIZE+1)): deleted $RESPONSE records ($FIRST .. $LAST)" | tee -a "$LOG"
  else
    FAILED_BATCHES=$((FAILED_BATCHES + 1))
    echo "batch $((i/BATCH_SIZE+1)) FAILED ($FIRST .. $LAST): $RESPONSE" | tee -a "$LOG"
    echo "  -> fix the list (usually a record missing from this project) and re-run; already-deleted batches are skipped naturally." | tee -a "$LOG"
  fi

  sleep 1   # be gentle with the server
done

echo "----------------------------------------" | tee -a "$LOG"
echo "Done. Deleted: $DELETED / $TOTAL. Failed batches: $FAILED_BATCHES. Log: $LOG" | tee -a "$LOG"
[[ $FAILED_BATCHES -eq 0 ]] || exit 2
