#!/bin/bash
cd ${0%/*}
. ./functions.sh

hostname="ftp://ftp.ncep.noaa.gov/pub/data/nccf/com/gfs/prod/"
hostlist="$(curl -N -l -f -s --retry 4 --retry-delay 30 --continue-at - "$hostname")"
if [ $? -ne 0 ] ; then
	exit 1
fi
distantfolder="$(echo "$hostlist" | grep '^gfs\.' | cut -f2 -d'.' | sort -nr | head -n1)"
#distantfolder="$(echo "$hostlist" | grep '^gfs\.' | cut -f2 -d'.' | sort -nr | head -2 | tail -1)"
localfolder="$(find ./data/ -mindepth 1 -maxdepth 1 -type d | sed 's|\./data/||g')"

if islocalfoldercomplete $1 ; then
	echo "Local data is complete"
	if isdegribbed $1 ; then
		echo "Local data has been completely degribbed"
		if ismerged $1 ; then
			echo "Local data has been merged"
			if isheaded $1 ; then
				echo "Local data has been headed"
				if isuploaded $1 ; then
					echo "Local data has been uploaded"
					if isinmysql $1 ; then
						echo "Local data has been imported to MySQL"
						if [ "$localfolder" != "$distantfolder" ] && isdistantfoldercomplete $1 ; then
							echo "New distant data is available"
							clean
							localfolder=$distantfolder
							echo "Local data has been deleted and distant data will be downloaded"
							download
						else
							echo "Waiting for new distant data to be available"
						fi
					else
						echo "Local data is being imported to MySQL"
						tomysql
					fi
				else
					upload
				fi
			else
				echo "Local data is being headed"
				header
			fi
		else
			echo "Local data is being merged..."
			merge
			if [ $? -ne 0 ] && ismerged $1 ; then
				rm ./data/${localfolder}/${localfolder}.csv
			else
				echo "Local data has been merged successfully"
			fi
		fi
	else
		echo "Local data has not been completely degribbed yet"
		degrib
	fi
else
	echo "Local data is not complete"
	download
fi