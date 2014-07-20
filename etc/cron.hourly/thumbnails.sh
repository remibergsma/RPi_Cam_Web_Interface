#!/bin/bash

# generate thumbnails for all new images in the past 60mins
find /var/www/media -name \*.jpg -cmin -60 -type f -printf "%f\n"| awk {'print "curl -s \"http://`ifconfig eth0 | grep inet | cut -d: -f2| tr -d Bcast`/timthumb.php?src=media/" $1 "&w=200\" 2>&1 >/dev/null" '} | sh
