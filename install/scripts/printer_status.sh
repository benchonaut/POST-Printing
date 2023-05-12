#!/bin/sh

test -e /tmp/.printerstatus || mkdir /tmp/.printerstatus
primacy_stat()    { 
    pingres=$(ping -c1 -w2 "$1" &>/dev/null && echo "YES" )
    echo "$pingres"|grep -q "YES" || echo -n "OFFLINE @ $1"
    echo "$pingres"|grep -q "YES" && (
                                      rawres=$(wget -q -O- http://$1/info.htm);
                                      res=$(echo "$rawres"|grep Firmware|cut -d">" -f3,5) ; 
                                      res=${res//td/} ; 
                                      res=${res/\<\//} ;
                                      res=${res/\//}; 
                                      printerstatus=$(echo -n "$rawres"|grep "Printer status"|cut -d">" -f5|cut -d"<" -f1 )
                                      [[ "$printerstatus" = "                " ]] && echo "   POWER OFF    "
                                      [[ "$printerstatus" = "                " ]] || echo "$printerstatus"
                                      echo "|"$res;
                                      echo "$rawres"|grep -q "Kit nb:" && (
                                           echo "|Kit-NB:";echo -n "$rawres"|grep "Kit nb:"|cut -d">" -f5|cut -d"<" -f1 ;
                                      )
                                      ) |tr -d '\n' ;  } ; 
ql720_stat()    { 
    pingres=$(ping -c1 -w2 "$1" &>/dev/null && echo "YES" )
    echo "$pingres"|grep -q "YES" || echo -n "OFFLINE @ $1"
    echo "$pingres"|grep -q "YES" && (echo -n $(
                    #echo "|TotalPages:" ;snmpwalk -v2c -c public $1 iso.3.6.1.2.1.43.10.2.1.4.1.1|cut -d":" -f2  ;
                    #^^^oldschool SNMP
                    cgiinfo=$(curl -s "http://"$1"/cgi/host/viewinfo.csv?M1=1&M2=1&M3=1&M4=1&M5=1&M6=1&M7=1&M8=1&M9=1&M10=1&M11=1&M12=1&M13=1&M14=1&M15=1&M16=1&M17=1&M18=1&M19=1&M20=1&M21=1&M22=1&M23=1&M24=1&M25=1&M26=1&M27=1&M28=1&M29=1&M30=1&M31=1&M32=1&M33=1&M34=1&M35=1&M36=1&M37=1&M38=1&M39=1&M40=1&M41=1&M42=1&M43=1&M44=1&M45=1&M46=1&M47=1&M48=1&M49=1&M50=1&M51=1")
                    [[ -z "$cgiinfo" ]] || ( 
                      snmpwalk -v2c -c public $1 iso.3.6.1.2.1.43.16.5.1.2.1.1|cut -d":" -f2 ; echo "|";
                      echo "$cgiinfo" |(read a;read b;cnt=1;echo -e ${a//,/'\n'}|while read c;do echo $c":"$(echo $b|cut -d"," -f$cnt ) ;let cnt+=1 ;done)|sed 's/Total Print Length/Total/g;s/Total Page Count/PageSUM/g;s/Total Cut Count/CutSUM/g;s/Printer Firmware Version/Firmware/g;s/Memory Size/Mem-MB/g;s/IP Address/IP/g;s/Serial no./Serial/g;s/Node Name/Name/g;s/Printer Type/PrntrType/g;s/Network Version/NetFW/g;s/$/|/g'|grep -e Firm -e Tota -e SUM -e Net |tr -d '\n'
                      echo "Paper:";snmpwalk -v2c -c public $1 iso.3.6.1.2.1.43.8.2.1.12.1.1 |grep -v Hex-STRING |cut -d":" -f2|sed 's/"//g;s/\\//g;s/iso.3.6.1.2.1.43.8.2.1.12.1.1 =//g'
                    ) 
                    
                     ) ) ;  } ; 
replace() { sed 's/^/'$1'/g;s///g' ; } ;

cupsprinters=$(cat /etc/cups/printers.conf|grep '^<Printer')
curlpstat=$(lpstat -s)

for CARD in $(echo {01..16});do

    #i=$(echo $CARD |cut -d":" -f1) ; IP=$(echo $CARD |cut -d"/" -f3); 
    i=$CARD;
    find /tmp/.printerstatus/ -mindepth 1 -maxdepth 1 -newermt '10 seconds ago' -type f -name "status_card.$i"|grep -q "status_card.$i"|| (
      cardinfo=$(lpoptions -p "CARD$i" -l)
      IP=""
      IP=$(echo "$curlpstat"|grep "CARD$i"|grep socket|sed 's~.\+socket://~~g')
      (echo -n '"card-'$i'":"';
      [[ -z "IP" ]] && echo "NO IP FOUND"
      [[ -z "IP" ]] || primacy_stat $IP;
      echo -n "|↻Front:" ; echo "$cardinfo" |grep FPageRotate |cut -d ":" -f2|sed 's/ ON//;s/ OFF//g;s/*//g' |tr -d '\n';    
      echo -n "|↻Back:"  ; echo "$cardinfo" |grep BPageRotate |cut -d ":" -f2|sed 's/ ON//;s/ OFF//g;s/*//g' |tr -d '\n';
      echo -n '",' ;) | sed 's/.\+"".\+//g' > /tmp/.printerstatus/status_card.$i ) &
    done

#for LABEL in $(echo "$cupsprinters"|grep LABEL|cut -d" " -f2|cut -d'>' -f1|sed 's/LABEL//g');do
for LABEL in $(echo {01..16});do
    ## CARD PRINTERS CAN ALSO BE NON_CUPS( via python)
    #i=$(echo $LABEL |cut -d":" -f1) ; IP=$(echo $LABEL |cut -d"/" -f3);
    i=$LABEL;
    ( echo "$cupsprinters" |grep -q  "LABEL$i")  && IP=$(echo "$curlpstat"|grep "LABEL$i"|grep lpd|sed 's~.\+lpd://~~g'|cut -d"/" -f1)
    ( echo "$cupsprinters" |grep -q  "LABEL$i")  || IP="192.168.88."$((220+$LABEL))
    find /tmp/.printerstatus/ -mindepth 1 -maxdepth 1 -newermt '10 seconds ago' -type f -name "status_label.$i"|grep -q "status_label.$i"|| (
        (echo -n '"label-'$i'":"';
        [[ -z "IP" ]] && echo "NO IP FOUND"
        [[ -z "IP" ]] || (ql720_stat $IP|sed 's/"//g');
        echo -n '",'     ) | sed 's/"\+/"/g' > /tmp/.printerstatus/status_label.$i ) &
    done
sleep 0.2
wait
( echo -n "{";cat /tmp/.printerstatus/status_{card,label}.* |sed 's/,$//g';echo -n "}" ) |tee $1
bash /etc/printer_clean_tmp.sh &>/dev/null & 
#rm /tmp/.printerstatus/status_{card,label}.*
