#!/bin/bash

# Prevent running Virtuoso in Docker parent image, will do after importing default data.
sed -i 's/exec virtuoso-t +wait +foreground//g' /virtuoso.sh

# Setup Virtuoso.
/bin/bash /virtuoso.sh

# Import RDF triples.
virtuoso-t +configfile /virtuoso.ini +wait
mv $IMPORT_DIR .
./vendor/bin/robo purge
./vendor/bin/robo import

## Restart Virtuoso in foreground.
kill $(pidof virtuoso-t)
exec virtuoso-t +configfile /virtuoso.ini +wait +foreground
