#!/bin/bash
set -m

export VIRT_SPARQL_ResultSetMaxRows=100000

echo "Starting Virtuoso in background..."
/virtuoso-entrypoint.sh start &

echo "Waiting for Virtuoso to be ready on 1111..."
while ! nc -z localhost 1111; do
  sleep 2
done

echo "Virtuoso ready."

# Import RDF triples.
if [ ! -f ".data_imported" ] ; then
    echo "Importing data..."
    ./vendor/bin/robo purge
    ./vendor/bin/robo import
    touch .data_imported
else
  echo "Data was already imported."
fi

if [ "$SPARQL_UPDATE" = "true" ]; then
  echo "Setting up update permissions."
  /virtuoso-entrypoint.sh isql <  /queries/grant_update.sql
fi

echo "Bringing Virtuoso back to foreground..."
fg %1
