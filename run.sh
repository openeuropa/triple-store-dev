#!/bin/bash

# Prevent running Virtuoso in Docker parent image, will do after importing default data.
sed -i 's/exec virtuoso-t +wait +foreground//g' /virtuoso.sh

# Setup Virtuoso.
/bin/bash /virtuoso.sh

# Import RDF data.
cd /
virtuoso-t +wait
/vendor/bin/robo import

# Restart Virtuoso in foreground.
kill $(ps aux | grep '[v]irtuoso-t' | awk '{print $2}')
exec virtuoso-t +wait +foreground
