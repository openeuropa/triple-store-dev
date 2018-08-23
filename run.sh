#!/bin/bash

# Prevent running Virtuoso in Docker parent image, will do after importing default data.
sed -i 's/exec virtuoso-t +wait +foreground//g' /virtuoso.sh

# Setup Virtuoso.
/bin/bash /virtuoso.sh
[ -z "$DBA_PASSWORD" ] && export DBA_PASSWORD=dba

# Import RDF triples.
virtuoso-t +configfile /virtuoso.ini +wait
./vendor/bin/robo purge
./vendor/bin/robo import

# Restart Virtuoso in foreground.
isql-v -U dba -P $DBA_PASSWORD -K
while [ -f /data/virtuoso.lck ] ; do sleep 1 ; done
exec virtuoso-t +configfile /virtuoso.ini +wait +foreground
