#!/bin/bash

#for specific item
#/mnt/dspace-vol/dspace/bin/dspace export -m -t ITEM -i 10883/4217 -d /social_module -n 1


if [ "$1" == "photos" ]
then
	echo "exporting photos.."
	exit
	#Collection: Photography
	/mnt/dspace-vol/dspace/bin/dspace export -t COLLECTION -i 10883/4048 -d ./flickr/images/ -n 2
	exit
fi

if [ "$1" == "videos" ]
then
	echo "exporting videos"
	exit

	#Collection: Video
	/mnt/dspace-vol/dspace/bin/dspace export -t COLLECTION -i 10883/4049 -d /youtube/videos/ -n 2
	exit
fi

exit

if [ "$1" == "presentations" ]
then
	#Collection: Presentation
	/mnt/dspace-vol/dspace/bin/dspace export -t COLLECTION -i 10883/17610 -d ./slideshare/presentations/ -n 2
	exit
fi
