#!/bin/bash

for m in $(seq 1 16);do lpadmin -x LABEL$(printf "%02d" $m);done &
for m in $(seq 1 16);do lpadmin -x CARD$(printf "%02d" $m);done  &
wait

test -e /usr/share/ppd/cups-pdf/cups-pdf.ppd || echo "FAILED: NO CUPS PDF PPD FOUND"
test -e /usr/share/ppd/cups-pdf/cups-pdf.ppd || exit 1

for m in $(seq 1 16);do mkdir "/var/spool/cups-pdf/LABEL"$(printf "%02d" $m) -p ;( echo "Grp lp";echo "Log /var/log/cups";echo LogType 2;echo "Out /var/spool/cups-pdf/LABEL"$(printf "%02d" $m) )  > /etc/cups/cups-pdf-LABEL$(printf "%02d" $m).conf; lpadmin -p LABEL$(printf "%02d" $m) -v cups-pdf:/LABEL$(printf "%02d" $m) -E -P /usr/share/ppd/cups-pdf/cups-pdf.ppd ;done &
for m in $(seq 1 16);do mkdir "/var/spool/cups-pdf/CARD"$(printf "%02d" $m)  -p ;( echo "Grp lp";echo "Log /var/log/cups";echo LogType 2;echo "Out /var/spool/cups-pdf/LABEL"$(printf "%02d" $m) )  > /etc/cups/cups-pdf-CARD$(printf "%02d" $m).conf;lpadmin -p CARD$(printf "%02d" $m) -v cups-pdf:/CARD$(printf "%02d" $m) -E -P /usr/share/ppd/cups-pdf/cups-pdf.ppd ;done &

wait

chown -R www-data:lp  "/var/spool/cups-pdf" -R  &
