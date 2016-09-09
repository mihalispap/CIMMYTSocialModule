#!/bin/bash

./export_dspace.sh "photos"

cd flickr
php upload.php
#rm -r images
#mkdir images

./export_dspace.sh "videos"

cd youtube
php upload.php
#rm -r videos
#mkdir videos

exit

./export_dspace.sh "presentations"

cd slideshare
php upload.php
#rm -r presentations
#mkdir presentations


