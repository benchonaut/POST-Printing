
#!/bin/bash

for m in $(seq 1 16);do lpadmin -x LABEL$(printf "%02d" $m);done &
for m in $(seq 1 16);do lpadmin -x CARD$(printf "%02d" $m);done  &
wait

test -e /usr/share/ppd/cupsfilters/Generic-PDF_Printer-PDF.ppd || echo "FAILED: NO CUPS PDF PPD FOUND"
test -e /usr/share/ppd/cupsfilters/Generic-PDF_Printer-PDF.ppd || exit 1


for m in $(seq 1 16);do  lpadmin -p LABEL$(printf "%02d" $m) -v pdf-writer:/var/spool/cups-pdf/LABEL$(printf "%02d" $m) -E -P /usr/share/ppd/cupsfilters/Generic-PDF_Printer-PDF.ppd ;done &
for m in $(seq 1 16);do  lpadmin -p CARD$(printf "%02d" $m)  -v pdf-writer:/var/spool/cups-pdf/CARD$(printf "%02d" $m)  -E -P /usr/share/ppd/cupsfilters/Generic-PDF_Printer-PDF.ppd ;done &

wait

chown -R www-data:lp  "/var/spool/cups-pdf" -R  &
