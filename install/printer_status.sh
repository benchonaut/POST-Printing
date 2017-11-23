#!/bin/sh

primacy_stat()	{ ping -c1 -w2 -D $1|grep -q "bytes from" && (res=$(wget -q -O- http://$1/info.htm);echo -n "$res"|grep "Printer status"|cut -d">" -f5|cut -d"<" -f1 ;
					res=$(echo "$res"|grep Firmware|cut -d">" -f3,5) ; res=${res//td/} ; res=${res/\<\//} ;res=${res/\//}; echo "|"$res;) |tr -d '\n' ;  } ; 
ql720_stat()	{ ping -c1 -w2 -D $1|grep -q "bytes from" && (echo -n $(snmpwalk -v2c -c public $1 iso.3.6.1.2.1.43.16.5.1.2.1.1|cut -d":" -f2 ;
					echo "|TotalPages:" ;snmpwalk -v2c -c public $1 iso.3.6.1.2.1.43.10.2.1.4.1.1|cut -d":" -f2 ) ) ;  } ; 
replace() { sed 's/^/'$1'/g;s///g' ; } ;

for CARD in $(lpstat -s|grep CARD|cut -d: -f1,3|sed 's/^.\+CARD//g');do
	i=$(echo $CARD |cut -d":" -f1) ; IP=$(echo $CARD |cut -d"/" -f3); 
	(echo -n '"card-'$i'":"';primacy_stat $IP;echo -n '",' ) > /tmp/.status_card.$i & done
	
for LABEL in $(lpstat -s|grep LABEL|cut -d: -f1,3|sed 's/^.\+LABEL//g');do
	i=$(echo $LABEL |cut -d":" -f1) ; IP=$(echo $LABEL |cut -d"/" -f3);
	(echo -n '"label-'$i'":"';ql720_stat $IP;echo -n '",' )	> /tmp/.status_label.$i & done
wait
( echo -n "{";cat /tmp/.status_{card,label}.* |grep -v '"",'|sed 's/,$//g';echo -n "}" ) |tee $1
#rm /tmp/.status_{card,label}.*
