#!/bin/bash
set -m

print_info() {
  echo -e "[INFO][$(date '+%H:%M:%S')] $1"
}

export VIRT_SPARQL_ResultSetMaxRows=100000

print_info "Starting Virtuoso in background..."
/virtuoso-entrypoint.sh start &

print_info "Waiting for Virtuoso to be ready on 1111..."
while ! nc -z localhost 1111; do
  sleep 2
done
print_info "Virtuoso ready."

FLAG_FILE=".first_run_executed"
if [ ! -f "$FLAG_FILE" ]; then
  print_info "Importing data..."
  ./vendor/bin/robo purge
  ./vendor/bin/robo import
  # Ensure a new line from previous ISQL execution.
  echo
  print_info "Import complete."

  if [ "$SPARQL_UPDATE" = "true" ]; then
    print_info "Granting update permission..."
    # The "isql" command of virtuoso-entrypoint.sh has an hardcoded value for the password,
    # and it won't work if the password is changed via env variables.
    "$VIRTUOSO_HOME/bin/isql" localhost:1111 dba $DBA_PASSWORD < /queries/grant_update.sql
    # Ensure a new line from previous ISQL execution.
    echo
    print_info "Update permission granted."
  fi

  touch "$FLAG_FILE"
else
  print_info "Data was already imported."

  # Give a visual feedback when SPARQL_UPDATE is set.
  if [ "$SPARQL_UPDATE" = "true" ]; then
    print_info "Update permission already granted."
  fi
fi

print_info "Bringing Virtuoso back to foreground..."
fg %1
