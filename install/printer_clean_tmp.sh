#!/bin/sh

find -type f /tmp/ -name "br_lpdfilter_ink_gsout_*" |while read file;do age=$(expr $(date +%s ) - $(date -u +%s -r $file));echo $age;if [ "$age" -gt 3600 ];then rm "$file" ;fi  & done
