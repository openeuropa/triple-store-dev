#!/bin/bash
set -m

export VIRT_SPARQL_ResultSetMaxRows=100000

# Import RDF triples.
if [ ! -f ".data_imported" ] ;
then
    echo "Starting Virtuoso in background..."
    /virtuoso-entrypoint.sh start &

    echo "Waiting for Virtuoso to be ready on 1111..."
    while ! nc -z localhost 1111; do
      sleep 2
    done

    echo "Virtuoso ready, importing data..."
    ./vendor/bin/robo purge
    ./vendor/bin/robo import
    touch .data_imported

    echo "Bringing Virtuoso back to foreground..."
    fg %1
else
  echo "Start Virtuoso in foreground as data was already imported."
  /virtuoso-entrypoint.sh start
fi

