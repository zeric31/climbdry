#!/bin/bash

function islocalfoldercomplete()
{
	local count="$(find ./data/"${localfolder}" -maxdepth 1 -type f -name '*.grb' | wc -l)";
    if [ "$count" -ge "20" ] ; then
		return 0
	else
		return 1
	fi
}

function isuploaded()
{
	local website="//mywebsite.com/upload/"
	local data="$(curl -N -f -s -k --retry 4 --retry-delay 30 --continue-at - ${website})"
	if [ $? -ne 0 ] ; then
		exit 1
	fi
	local count="$(echo "$data" | grep "${localfolder}" | wc -l)"
	if [ "$count" -ge "1" ] ; then
		return 0
	else
		return 1
	fi
}

function isdistantfoldercomplete()
{

	local data="$(curl -N -l -f -s --retry 4 --retry-delay 30 --continue-at - ${hostname}gfs.${distantfolder}/)"
	if [ $? -ne 0 ] ; then
		exit 1
	fi
	local list="$(echo "$data" | grep '.pgrb2\.0p25\.f.' | grep -v 'idx')" 

	local count=0
	for file in $list ; do
		local num="$(expr "${file: -3}" + 0)"
		if [ "$(($num%6))" -eq 0 ] && [ "$num" -ne 0 ] && [ "$num" -le 120 ] ; then
			count=$((count+1))
		fi
	done

    if [ "$count" -ge "20" ] ; then
		return 0
	else
		return 1
	fi
}

function download()
{
	local data="$(curl -N -l -f -s --retry 4 --retry-delay 30 --continue-at - ${hostname}gfs.${localfolder}/)"
	if [ $? -ne 0 ] ; then
		exit 1
	fi
	local list="$(echo "$data" | grep '.pgrb2\.0p25\.f.' | grep -v 'idx')" 
	local existing=($(find ./data/"${localfolder}" -maxdepth 1 -type f -name '*.grb' | sed 's|\./data/'${localfolder}'/||g'));

	for file in $list ; do
		local num="$(expr "${file: -3}" + 0)"
		if [ "$(($num%6))" -eq 0 ] && [ "$num" -ne 0 ] && [ "$num" -le 120 ] && ! contains ${file}.grb "${existing[@]}" $1; then
			local url=http://www.ftp.ncep.noaa.gov/data/nccf/com/gfs/prod/gfs.${localfolder}/${file}
			echo "Downloading "${url}
			perl ./scripts/get_inv.pl "${url}.idx" | grep ":APCP:" | perl ./scripts/get_grib.pl "${url}" ./data/${localfolder}/${file}.grb
		fi
	done
	
	return 0
}

function contains()
{
  local e match="$1"
  shift
  for e; do [[ "$e" == "$match" ]] && return 0; done
  return 1
}

function isdegribbed()
{
	local existing="$(find ./data/"${localfolder}" -maxdepth 1 -type f -name '*.csv')"
	for file in $existing ; do
		if [ "$(($(cat "${file}" | wc -l) + 1))" -ne 1038242 ] && [ "$file" != "./data/${localfolder}/${localfolder}.csv" ] ; then
			rm $file
		fi
	done
	local count="$(find ./data/"${localfolder}" -maxdepth 1 -type f -name '*.csv' | wc -l)";
    if [ "$count" -ge "20" ] ; then
		return 0
	else
		return 1
	fi
}

function degrib()
{
	local files="$(find ./data/"${localfolder}" -maxdepth 1 -type f -name '*.grb' | sed 's|\./data/'${localfolder}'/||g' | sort -n)";

	for file in $files ; do
		target="$(echo "${file: -7}" | cut -f1 -d'.')".csv
		if [ "$(find ./data/"${localfolder}" -maxdepth 1 -type f -name "*${target}" | wc -l)" -ne 1 ] ; then
			echo "Degribbing "${file}
			./degrib/bin/degrib ./data/${localfolder}/${file} -C -msg all -nMet -Csv -Unit m -nameStyle "./data/"${localfolder}"/%p.csv" -Decimal 5
		fi
	done
	
	return 0
}

function ismerged()
{
	local count="$(find ./data/"${localfolder}" -maxdepth 1 -type f -name "*${localfolder}.csv" | wc -l)";
    if [ "$count" -eq "1" ] ; then
		return 0
	else
		return 1
	fi
}

function merge()
{
	files=( ./data/${localfolder}/???.csv)
	eval "paste -d',' $(printf "<( cut -d, -f5 %q ) " "${files[@]}") | sed -r 's/\s+//g' > ./data/${localfolder}/${localfolder}.csv"
	return 0
}

function header()
{
	local string="$(head -n 1 ./data/${localfolder}/${localfolder}.csv)"
	local a=();
	while read -rd,; do
		a+=("$(echo "${REPLY::-2}" | cut -f2 -d'_')");
	done <<<"$string,";
	local ref=$localfolder
	for i in "${!a[@]}"; do
		a[$i]="$((${a[$i]} - $ref))"
		a[$i]="$(((${a[$i]} + 23) / 100 * 24 + (${a[$i]} + 23) % 100 - 23))"
	done
	local var="$(IFS=,; echo "${a[*]}")"
	sed -i "1s/.*/${var}/" ./data/${localfolder}/${localfolder}.csv	
	truncate -s -1 file ./data/${localfolder}/${localfolder}.csv	

	return 0
}

function isheaded()
{
	local lines="$(wc -l < ./data/${localfolder}/${localfolder}.csv)"
	if [ "$lines" -lt "1038241" ] ; then
		return 0
	else
		return 1
	fi
}

function upload() #CHANGE USER AND HOST ACCORDINGLY TO FTP SERVER
{
	rsync -P -e ssh --partial-dir=~/upload/tmp ./data/${localfolder}/${localfolder}.csv USER@HOST:~/upload/
	return 0
}

function isinmysql()
{
	local website="//mywebsite.com/upload/"
	local data="$(curl -N -f -s -k --retry 4 --retry-delay 30 --continue-at - ${website})"
	if [ $? -ne 0 ] ; then
		exit 1
	fi
	local count="$(echo "$data" | grep "success" | wc -l)"
	if [ "$count" -ge "1" ] ; then
		return 0
	else
		return 1
	fi
}

function tomysql(){
	lynx -dump //mywebsite.com/upload/tomysql.php?date=${localfolder}
	return 0
}

function clean(){ #CHANGE USER AND HOST ACCORDINGLY TO FTP SERVER
	ssh USER@HOST <<EOF
rm -r ~/upload/${localfolder}.csv
rm -r ~/upload/success
EOF
	rm -r ./data/${localfolder}/
	mkdir -m 777 ./data/${distantfolder}/
	return 0
}