#!/bin/bash
sed -i 's/exec virtuoso-t +wait +foreground//g' /virtuoso.sh
/bin/bash /virtuoso.sh

cd /
virtuoso-t +wait
/vendor/bin/robo import
kill $(ps aux | grep '[v]irtuoso-t' | awk '{print $2}')

exec virtuoso-t +wait +foreground
