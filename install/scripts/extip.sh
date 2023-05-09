#!/bin/bash

ip=$1
echo "$ip"|grep -q ":" || ( 

echo "ext_IPv4=$ip"
(echo "${ip}"|grep -q -e ^127\. -e ^10.\. -e ^192\.168\. -e '^172.1[6-9]\.' -e '^172.2[0-9]\.' ) || ( (echo "$ip"|grep  -v ":") | grep -q '\.') && [[ -z "$ip" ]] || (

##ip was found

target=$(echo $ip|awk -F. '{print $4"."$3"." $2"."$1}');
          (  timeout 10 host -t txt $target.sa.senderbase.org   || timeout 5 host -t txt $target.sa.senderbase.org 1.1.1.1 ) |sed 's/|/\n/g;s/senderbase.org descriptive text.\+|//g'|grep -e ^1= -e 20= -e 53= -e 54= -e 55= -e 50= |sed 's/50=/CITY=/g;s/54=/LAT=/g;s/55=/LON=/g;s/53=/CC=/g;s/^20=/0000=/g;s/^1=/0005=/g;s/^26=/0002=/g;s/^47=/0001=/g'|sort|grep -v 0000=|sed 's/0000=//g;s/0002=/SCORE_DOMAIN=/g;s/0001=/SCORE_IP=/g;s/0005=/ORG=/g;s/^/|/g'|tr -d '\n'|sed 's/||/|/g' |sed 's/|CC=/\nCC=/g;s/|LAT=/\nLAT=/g';  
echo "|"$( ( timeout 10 host    $target.score.senderscore.com   || timeout 5 host    $target.score.senderscore.com 1.1.1.1 ) |grep 127.0.4.|grep -v NXDOMAIN|sed 's/.\+127\.0\.4\.//g;s/^/senderscore: /g' 2>&1 )"|" ;

        ) |sed 's/^|//g;s/|$//g'
         ## end ip was found
) ## end ipv4

dnsres=$(nslookup -type=AAAA he.net |grep -A4 ^Name|grep ^Add)
echo "$dnsres"|grep -e "Address: 2[a-f]" -e "Address: 2[0-9]" -q && echo -n "DNS6(he):OK |"
echo "$dnsres"|grep -e "Address: 2[a-f]" -e "Address: 2[0-9]" -q || echo -n "DNS6(he):ERR|"
dnsres=$(nslookup -type=AAAA ya.ru  |grep -A4 ^Name|grep ^Add)
echo "$dnsres"|grep -e "Address: 2[a-f]" -e "Address: 2[0-9]" -q && echo -n "DNS6(ya):OK |"
echo "$dnsres"|grep -e "Address: 2[a-f]" -e "Address: 2[0-9]" -q || echo -n "DNS6(ya):ERR|"
echo
dnsres=$(nslookup -type=AAAA cloudflare.com |grep -A4 ^Name|grep ^Add)
echo "$dnsres"|grep -e "Address: 2[a-f]" -e "Address: 2[0-9]" -q && echo -n "DNS6(cf):OK |"
echo "$dnsres"|grep -e "Address: 2[a-f]" -e "Address: 2[0-9]" -q || echo -n "DNS6(cf):ERR|"
dnsres=$(nslookup -type=AAAA heise.de |grep -A4 ^Name|grep ^Add)
echo "$dnsres"|grep -e "Address: 2[a-f]" -e "Address: 2[0-9]" -q && echo -n "DNS6(de):OK |"
echo "$dnsres"|grep -e "Address: 2[a-f]" -e "Address: 2[0-9]" -q || echo -n "DNS6(de):ERR|"
echo 
ipv6works=no
( curl -s -6 https://www.heise.de|grep --line-buffered  -m1 "<html" |head -n1|grep "<html" -q ) && (  curl -s -6 https://cloudflare.com |grep --line-buffered  -m1 "<html" |head -n1|grep "<html" -q ) && ( curl -s -6 https://|grep --line-buffered  -m1 "<html" |head -n1|grep "<html" -q ) && ipv6works=yes
echo "IPv6 OK=$ipv6works"
test -e /var/www/.starturl && (RCODE=$(curl -Ls -o /dev/null -w "%{http_code}" $(cat /var/www/.starturl));echo -n "startURL: "$(cat /var/www/.starturl)" | RCODE: $RCODE";
                               echo "$RCODE"|grep -e ^301 -e ^302 -q && (echo ;echo "FINAL_URL: "$(curl -Ls -o /dev/null -w "%{url_effective}" $(cat /var/www/.starturl)));echo)
