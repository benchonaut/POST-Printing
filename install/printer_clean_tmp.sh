#!/bin/bash
declare -x PATH="/home/andosto/bin:/home/andosto/.local/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games"
find -type f /tmp/ -name "br_lpdfilter_ink_gsout_*" |while read file;do age=$(expr $(date +%s ) - $(date -u +%s -r $file));echo $age;if [ "$age" -gt 3600 ];then rm "$file" ;fi  & done
