#!/bin/bash

# Prevent running Virtuoso in Docker parent image, will do after importing default data.
sed -i 's/exec virtuoso-t +wait +foreground//g' /virtuoso.sh

# Setup Virtuoso.
/bin/bash /virtuoso.sh

# Import RDF triples.
if [ ! -f ".data_imported" ] ;
then
    # Start Virtuoso in background.
    virtuoso-t +configfile /virtuoso.ini +wait

    # Import triples.
    ./vendor/bin/robo purge
    ./vendor/bin/robo import

    # Create flag file so we won't re-import if container is restarted.
    touch .data_imported

    # Restart Virtuoso in foreground.
    kill $(pidof virtuoso-t)
fi

echo "Going to sleep..."
sleep 10
echo "Starting Virtuoso..."
exec virtuoso-t +configfile /virtuoso.ini +wait +foreground
