#!/bin/bash
declare -x PATH="/home/andosto/bin:/home/andosto/.local/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games"
find /tmp/ -type f -name "br_lpdfilter_ink_gsout_*" |while read file;do age=$(expr $(date +%s ) - $(date -u +%s -r $file));if [ "$age" -gt 3600 ];then chown $(id -un) $file ; rm "$file" ;fi  & done
