#!/bin/sh

test -e /tmp/.printerstatus || mkdir /tmp/.printerstatus
primacy_stat()    { 
    pingres=$(ping -c1 -w2  $1 2>&1 )
    echo "$pingres"|grep -q "bytes from" || echo -n "OFFLINE"
    echo "$pingres"|grep -q "bytes from" && (res=$(wget -q -O- http://$1/info.htm);echo -n "$res"|grep "Printer status"|cut -d">" -f5|cut -d"<" -f1 ;
                    res=$(echo "$res"|grep Firmware|cut -d">" -f3,5) ; res=${res//td/} ; res=${res/\<\//} ;res=${res/\//}; echo "|"$res;) |tr -d '\n' ;  } ; 
ql720_stat()    { 
    pingres=$(ping -c1 -w2  $1 2>&1 )
    echo "$pingres"|grep -q "bytes from" || echo -n "OFFLINE"
    echo "$pingres"|grep -q "bytes from" && (echo -n $(snmpwalk -v2c -c public $1 iso.3.6.1.2.1.43.16.5.1.2.1.1|cut -d":" -f2 ; echo "|";
                    #echo "|TotalPages:" ;snmpwalk -v2c -c public $1 iso.3.6.1.2.1.43.10.2.1.4.1.1|cut -d":" -f2  ;
                    #^^^oldschool SNMP
                    curl -s "http://"$1"/cgi/host/viewinfo.csv?M1=1&M2=1&M3=1&M4=1&M5=1&M6=1&M7=1&M8=1&M9=1&M10=1&M11=1&M12=1&M13=1&M14=1&M15=1&M16=1&M17=1&M18=1&M19=1&M20=1&M21=1&M22=1&M23=1&M24=1&M25=1&M26=1&M27=1&M28=1&M29=1&M30=1&M31=1&M32=1&M33=1&M34=1&M35=1&M36=1&M37=1&M38=1&M39=1&M40=1&M41=1&M42=1&M43=1&M44=1&M45=1&M46=1&M47=1&M48=1&M49=1&M50=1&M51=1"|(read a;read b;cnt=1;echo -e ${a//,/'\n'}|while read c;do echo $c":"$(echo $b|cut -d"," -f$cnt ) ;let cnt+=1 ;done)|sed 's/Total Print Length/Total/g;s/Total Page Count/PageSUM/g;s/Total Cut Count/CutSUM/g;s/Printer Firmware Version/Firmware/g;s/Memory Size/Mem-MB/g;s/IP Address/IP/g;s/Serial no./Serial/g;s/Node Name/Name/g;s/Printer Type/PrntrType/g;s/Network Version/NetFW/g;s/$/|/g'|grep -e Firm -e Tota -e SUM -e Net |tr -d '\n'
                    echo "Paper:";snmpwalk -v2c -c public $1 iso.3.6.1.2.1.43.8.2.1.12.1.1 |cut -d":" -f2|sed 's/"//g;s/\\//g;s/iso.3.6.1.2.1.43.8.2.1.12.1.1 =//g' ) ) ;  } ; 
replace() { sed 's/^/'$1'/g;s///g' ; } ;

for CARD in $(lpstat -s|grep CARD|cut -d: -f1,3|sed 's/^.\+CARD//g');do
    i=$(echo $CARD |cut -d":" -f1) ; IP=$(echo $CARD |cut -d"/" -f3); 
    find /tmp/.printerstatus/ -mindepth 1 -maxdepth 1 -newermt '10 seconds ago' -type f -name "status_card.$i"|grep -q "status_card.$i"|| (
      (echo -n '"card-'$i'":"';primacy_stat $IP;
      echo -n "|↻Front:" ;lpoptions -p CARD$i -l  |grep FPageRotate |cut -d ":" -f2|sed 's/ ON//;s/ OFF//g' |tr -d '\n';    
      echo -n "|↻Back:" ; lpoptions -p CARD$i -l  |grep BPageRotate |cut -d ":" -f2|sed 's/ ON//;s/ OFF//g' |tr -d '\n';
      echo -n '",' ;) | sed 's/.\+"".\+//g' > /tmp/.printerstatus/status_card.$i ) &
    done

for LABEL in $(lpstat -s|grep LABEL|cut -d: -f1,3|sed 's/^.\+LABEL//g');do
    i=$(echo $LABEL |cut -d":" -f1) ; IP=$(echo $LABEL |cut -d"/" -f3);
    find /tmp/.printerstatus/ -mindepth 1 -maxdepth 1 -newermt '10 seconds ago' -type f -name "status_label.$i"|grep -q "status_label.$i"|| (
        (echo -n '"label-'$i'":"';ql720_stat $IP|sed 's/"//g';echo -n '",'     ) | sed 's/"\+/"/g' > /tmp/.printerstatus/status_label.$i ) &
    done
wait
( echo -n "{";cat /tmp/.printerstatus/status_{card,label}.* |sed 's/,$//g';echo -n "}" ) |tee $1
bash /etc/printer_clean_tmp.sh &>/dev/null & 
#rm /tmp/.printerstatus/status_{card,label}.*
