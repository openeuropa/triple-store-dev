#!/bin/bash
set -m

# Prevent running Virtuoso in Docker parent image, will do after importing default data.
sed -i 's/exec virtuoso-t +wait +foreground//g' /virtuoso.sh

# Setup Virtuoso.
/bin/bash /virtuoso.sh

# Import RDF triples.
if [ ! -f ".data_imported" ] ;
then
    echo "Starting Virtuoso in background..."
    exec virtuoso-t +configfile /virtuoso.ini +wait +foreground &

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
  exec virtuoso-t +configfile /virtuoso.ini +wait +foreground
fi

