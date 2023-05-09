#!/bin/bash
declare -x PATH="~/bin:~/.local/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games"
find /tmp/ -type f -name "br_lpdfilter_ink_gsout_*" |while read file;do ( age=$(expr $(date +%s ) - $(date -u +%s -r $file));if [ "$age" -gt 3600 ];then chown $(id -un) $file &>/dev/null; rm "$file" &>/dev/null;fi  ) & done
find /var/spool/cups-pdf/ /srv/print-virtual/ -type f -mmin +30 -delete &>/dev/null
sed 's~.\+Unable to PUT / from 127.0.0.1 on port.\+: 200 OK~~g' -i /var/log/cups/*_log  &>/dev/null
test -e /tmp/.debug_out && find /tmp/.debug_out -type f -mmin +15 -delete &>/dev/null
find /tmp/.debug_mode_active -mmin +30 -delete &>/dev/null
