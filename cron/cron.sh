#!/bin/sh

find /var/www/html_paintstrap/output -type f -mtime +7 -delete
find /var/www/html_paintstrap/output -type d -empty -delete
