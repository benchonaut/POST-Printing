#!/bin/bash


#get http://releases.ubuntu.com/16.04/ubuntu-16.04.3-desktop-amd64.iso #>install (e.g. unetbootin, tftp)
pwd

[[ -z "$CRONUSER" ]] && CRONUSER=root
mkdir /var/spool/lpd/ -p
#echo Europe/Berlin >/etc/timezone
which dpkg && dpkg --add-architecture i386;
which apt-get && apt-get update

SELECTION="supervisor apache2-utils nginx tor sudo openssh-server  git socat nginx curl python3 python3-pip php-fpm php-curl   avahi-utils libjansson4 snmp  cron cups cups-ipp-utils cups-bsd cups-common openssl libc6:i386 libstdc++6:i386"
which apt-get && (cd /var/cache/apt/archives/;apt-get install -y $SELECTION --print-uris 2>&1|cut -d"'" -f2|grep -e http: -e https: |while read url;do wget -c "$url" ;done)
which apt-get && apt-get -y --force-yes install $SELECTION

ALPSELECTION="byobu pv ipptool cups-filters cups-openrc sshfs php81-pdo_sqlite php81-fpm nginx iptables curl bash socat wget grep sed websocat cups-dev  libc-dev  avahi-tools docker py3-pip  sed grep procps psmisc shadow jq mtr nano  net-snmp-tools nginx-openrc openssh openssh-client php81-cli php81-curl lftp vnstat tor findutils "
which apk && apk add $ALPSELECTION


cups_cmd=/etc/init.d/cups
test -e /etc/init.d/cupsd && cups_cmd=/etc/init.d/cupsd

test -e /etc/localtime || ln -sf /usr/share/zoneinfo/Europe/Berlin /etc/localtime

## forks after this line

(
echo '#!/bin/bash

test -e /dev/shm/printer-events.log||mkdir  /dev/shm/printer-events.log ; python3 -u $(which pycups-notify) 2>&1|websocat --websocket-ignore-zeromsg --async-stdio ws://192.168.88.254:11111/ |while read line ;do find /dev/shm/printer-events.log/ -mmin +30 -type f -delete &>/dev/null &  echo $line >  /dev/shm/printer-events.log/printer-events.$(date +%F_%H.%M);   done ' > /etc/run.cups.notify.sh


) &

(

cp wordlist/words.list /etc/.wordlist

echo '#!/bin/bash

echo "generating default htpassword (/setup/)";
    #randompass=$( for rounds in $(seq 1 24);do tr -cd "[:alnum:]_\-." < /dev/urandom  |head -c48;echo ;done|grep -e "_" -e "\-" -e "\."|grep ^[a-zA-Z0-9]|grep [a-zA-Z0-9]$|tail -n1 );
    len=$(wc -l /etc/.wordlist |cut -d " " -f1) ;facta=$(($(date +%s)*$RANDOM));pswa=$(head -n $(($facta%len)) /etc/.wordlist|tail -n1);factb=$(($(date +%s)*$RANDOM));pswb=$(head -n $(($factb%len)) /etc/.wordlist|tail -n1);num=$((($facta)%9999));randompass=$pswa"_"$pswb"_"$num;
    echo "user: printadmin | password: $randompass"; 
    echo "$randompass" > /etc/.printadminpass ;
    chmod go-rwx /etc/.printadminpass;htpasswd  -b -B -C 12 /var/www/.htpasswd printadmin "$randompass" ' > /usr/bin/reset-pass-printadmin.sh &

echo '#!/bin/bash

echo "generating default htpassword (/setup/)";
    #randompass=$( for rounds in $(seq 1 24);do tr -cd "[:alnum:]_\-." < /dev/urandom  |head -c48;echo ;done|grep -e "_" -e "\-" -e "\."|grep ^[a-zA-Z0-9]|grep [a-zA-Z0-9]$|tail -n1 );
    len=$(wc -l /etc/.wordlist |cut -d " " -f1) ;facta=$(($(date +%s)*$RANDOM));pswa=$(head -n $(($facta%len)) /etc/.wordlist|tail -n1);factb=$(($(date +%s)*$RANDOM));pswb=$(head -n $(($factb%len)) /etc/.wordlist|tail -n1);num=$((($facta)%9999));randompass=$pswa"_"$pswb"_"$num;
    echo "user: eventadmin | password: $randompass"; 
    echo "$randompass" > /etc/.eventadminpass ;
    chmod go-rwx /etc/.printadminpass;htpasswd  -b -B -C 12 /var/www/.htpasswd eventadmin "$randompass" ' > /usr/bin/reset-pass-eventadmin.sh & 
    
chmod +x /usr/bin/reset-pass-eventadmin.sh  /usr/bin/reset-pass-printadmin.sh

) &

websocat --version|grep ^websocat  || (
wget -qO /usr/bin/websocat https://github.com/vi/websocat/releases/latest/download/websocat.x86_64-unknown-linux-musl
chmod a+x /usr/bin/websocat
) & 


## packages that only a real host will have
which apt-get && ( cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ || apt-get -y --force-yes install openssh-server byobu sshfs apparmor-utils pv) 

#Evolis Primacy installation
( cp drivers/evolis-primacyE.ppd.gz /usr/share/cups/model/ && cp drivers/evorasterizer /usr/lib/cups/filter/ && chmod 755 /usr/share/cups/model/evolis-primacyE.ppd.gz /usr/lib/cups/filter/evorasterizer ) &


##ql720 install is done below
### DO NOT ! dpkg -i --force-all ql720nwlpr-1.1.4-0.i386.deb && dpkg -i --force-all ql720nwcupswrapper-1.1.4-0.i386.deb
(
echo "++ql720++"
tar xzf drivers/ql720nw.tgz -C /
test -e /usr/lib/cups/filter/brother_lpdwrapper_ql720nw && rm /usr/lib/cups/filter/brother_lpdwrapper_ql720nw
cp -arv drivers/brother_lpdwrapper_ql720nw /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw
ln -s /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw
chown root:root /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw
chmod go-w /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw
#cp -arv brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw

#QL810w needs some special attention
pip3 install --upgrade https://github.com/pklaus/brother_ql/archive/master.zip
ls -1d brother_ql/.git 2>/dev/null|wc -l |grep ^0$ && git submodule update --init

cd brother_ql;python setup.py install||python3 setup.py install;cd ..
) &

( which aa-complain && aa-complain cupsd ) & 

test -e /etc/torrc || ln  -s /etc/tor/torrc  /etc/
grep "^HiddenServiceDir /var/lib/tor/ssh/" /etc/tor/torrc || (/etc/init.d/tor stop 2>&1 >/dev/null;echo "CkhpZGRlblNlcnZpY2VEaXIgL3Zhci9saWIvdG9yL3NzaC8KSGlkZGVuU2VydmljZVBvcnQgMjIgMTI3LjAuMC4xOjIyCkhpZGRlblNlcnZpY2VBdXRob3JpemVDbGllbnQgc3RlYWx0aCBzc2gKCgo=" |base64 -d |grep -v HiddenServiceAuthorizeClient  >> /etc/tor/torrc )
rm /lib/systemd/system/tor.service;service tor@default stop;
#cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ ||
service tor@default start &
#cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ ||
service tor start &

## in docker we swap ssh port to 2222 and also direct our hidden service there
cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ && sed 's/HiddenServicePort 22 127.0.0.1:22/HiddenServicePort 22 127.0.0.1:2222/g' /etc/torrc -i
cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ && sed 's/^Port 22/#Port 22/g' /etc/ssh/sshd_config -i
cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ && echo 'Port 2222' >> /etc/ssh/sshd_config
systemctl daemon-reload

( (grep "^PermitRootLogin without-password" /etc/ssh/sshd_config -q || grep "^PermitRootLogin prohibit-password" /etc/ssh/sshd_config -q || grep "^PermitRootLogin no" /etc/ssh/sshd_config -q) && echo SAFE || ( echo "PermitRootLogin without-password" >> /etc/ssh/sshd_config ) ) & 

( cd drivers && for base in *base64 ; do  base64 -d $base > "/etc/cups/ppd/"${base/.base64/};done ) & 
( for type in CARD LABEL;do for i in {02..16};do cp -arv /etc/cups/ppd/$type"01.ppd" /etc/cups/ppd/$type$i.ppd 2>/dev/null;cp -arv /etc/cups/ppd/$type"01.ppd.O" /etc/cups/ppd/$type$i.ppd.O 2>/dev/null;done;done ) &

$cups_cmd stop ;
wait



(cat drivers/stamp.header;for i in {01..16};do nonzeroed=${i/#0/};zeroed=$i;labelip=$(expr $zeroed + 20) ;
 cat drivers/stamp.card drivers/stamp.label |sed 's/CARDNUMBERZEROED/'$zeroed'/g;s/CARDNUMBER/'$nonzeroed'/g;s/LABELIP/'$labelip'/g;s/LABELNUMBERZEROED/'$zeroed'/g;s/LABELNUMBER/'$nonzeroed'/g' ;done ) > /etc/cups/printers.conf;

( grep "^FileDevice Yes"  /etc/cups/cups-files.conf -q || ( echo "FileDevice Yes" >> /etc/cups/cups-files.conf) ) &


(
# debuntu ↓
test -e /etc/nginx/sites-enabled && cp -arv nginx-config/default /etc/nginx/sites-enabled/default.conf
test -e /etc/nginx/http.d && cp -arv nginx-config/default /etc/nginx/http.d/default.conf
# alpine  ↑ 
) & 
(

test -e /var/www/html/lib/test.print.assets || mkdir -p /var/www/html/lib/test.print.assets/
cp -aurv ../tests/*.pdf /var/www/html/lib/test.print.assets/

cp -arv scripts/extip.sh scripts/printer_status.sh scripts/printer_clean_tmp.sh /etc/
cp -arv webroot/*.php webroot/html /var/www/html/
#cp -arv print.php router.php /var/www/html/
#cp -arv printer-status.php router.php /var/www/html/
cp -arv assets/supervisor-cups-notification.ini /etc/supervisor.d/cups-websocket-server.conf
systemctl enable supervisor

cp -arv assets/avahi-daemon.conf /etc/avahi/avahi-daemon.conf
ln -s /var/www/html/setup/select-arrows.svg /var/www/html/
tar xvzf assets/favicon.tgz -C /var/www/html/
chown -R www-data:www-data /var/www/
chown -R root:lp /etc/cups
adduser www-data lpadmin

) & 

# bind cups to all interfaces
#$cups_cmd stop ;sed 's/Listen localhost:631/Listen 0.0.0.0:631/g' -i /etc/cups/cupsd.conf
pip3 install pycups-notify 
wait 

$cups_cmd start
sleep 3
cupsctl --remote-admin --remote-any --share-printers
cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ && service cups stop &




for file in /etc/init.d/php*fpm;do $file start;done
echo  "MAKE SURE PHP_FPM is RUNNING BEFORE YOU START INSTALLER OR START AGAIN"
[[ -z "$(find /var/run/ -name "*fpm.sock" |sort -n|tail -n1 )" ]] && { echo "NO FPM SOCK .. EXITING" ; exit 3 ; } ;
test -e $(find /var/run/ -name "*fpm.sock" |sort -n|tail -n1 )    || { echo "NO FPM SOCK .. EXITING" ; exit 3 ; } ;
ln -s  $(find /var/run/ -name "*fpm.sock"  |sort -n|tail -n1 )  /etc/php-fpm.sock

for file in $( ls -1 /etc/init.d/php-fpm* /etc/init.d/php*fpm );do $file stop;done

(test -e /etc/ssl/private/nginx.key && test -e /etc/ssl/private/crt.pem ) ||   openssl req -x509 -nodes -days 3650 -subj "/C=US/ST=Denial/L=Springfield/O=Dis/CN=printserver.local" -newkey rsa:4096 -keyout /etc/ssl/private/nginx.key -out /etc/ssl/private/crt.pem
test -e /etc/ssl/private/ca.pem || ln -s /etc/ssl/private/crt.pem /etc/ssl/private/ca.pem

cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ || $cups_cmd start

test -f /var/www/html/index.nginx-debian.html && rm /var/www/html/index.nginx-debian.html
#cp index.html /var/www/html/;chown www-data:www-data /var/www/html/index.html

cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ ||  /etc/init.d/nginx restart


grep -q "www-data ALL=NOPASSWD: /usr/local/bin/brother_ql" /etc/sudoers || (echo "www-data ALL=NOPASSWD: /usr/local/bin/brother_ql" >> /etc/sudoers; echo "+ sudoers updated" )  && echo "→  sudoers modded already"
crontab -l -u $CRONUSER |grep printer_clean_tmp  || (echo "++ Installing cron printer data cleaner"; (crontab -l -u $CRONUSER;echo "Ki81ICogICAqICogKiAgIHJvb3QgICAgL2Jpbi9iYXNoIC9ldGMvcHJpbnRlcl9jbGVhbl90bXAuc2ggMj4mMSA+IC90bXAvY2xlYW5sb2cK"|base64 -d ) | crontab -u $CRONUSER - )

crontab -l -u $CRONUSER | grep 'find /var/log/ -name ' |grep 'gz" -delete'  || (echo Installing cron log.gz cleaner;(crontab -l -u $CRONUSER; echo "KiAqLzIgICAqICogKiAgIHJvb3QgICAgZmluZCAvdmFyL2xvZy8gLW5hbWUgIipneiIgLWRlbGV0ZSAyPiYxID4gL3RtcC9jbGVhbmd6bG9nCg=="|base64 -d) | crontab -u $CRONUSER -  )

( bash 0_1_default-branding.sh 
test -e /var/www/html/branding-www.png || ln -s /etc/branding-www.png /var/www/html/branding-www.png ) & 


for imagi in /etc/ImageMagick-*/policy.xml /etc/ImageMagick/policy.xml;do
	test -f "$imagi" && ( sed 's/rights="none" pattern="PDF/rights="read|write" pattern="PDF/g' "$imagi" -i ;
	  	grep -q 'policy domain="coder" rights="read|write" pattern="LABEL"' $imagi || echo '<policy domain="coder" rights="read|write" pattern="LABEL" />' |tee -a "$imagi"  )
done &

cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ && service tor@default stop &
cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ && service tor stop &

sleep 10
echo REMOTE: $(cat /var/lib/tor/ssh/hostname |sed 's/$/-'$(hostname)'-/g;s/$/'$(cat /etc/machine-id)'/g;')

for file in $(ls -1 /etc/init.d/php-fpm* /etc/init.d/php*fpm );do $file start;done & 
wait

## disable ubuntu/debian power saving "features" , enable shutdown by power button


which hostnamectl && hostnamectl set-chassis vm
which systemctl && (
systemctl mask sleep.target suspend.target hibernate.target hybrid-sleep.target
dpkg-divert --no-rename /etc/systemd/system/sleep.target
dpkg-divert --no-rename /etc/systemd/system/suspend.target
dpkg-divert --no-rename /etc/systemd/system/hibernate.target
dpkg-divert --no-rename /etc/systemd/system/hybrid-sleep.target
)




echo
echo DONE



test -e /var/www/.htpasswd || ( 
    rm -f /etc/.printadminpass 
    echo "generating default htpassword (/setup/)";
    #randompass=$( for rounds in $(seq 1 24);do tr -cd '[:alnum:]_\-.' < /dev/urandom  |head -c48;echo ;done|grep -e "_" -e "\-" -e "\."|grep ^[a-zA-Z0-9]|grep [a-zA-Z0-9]$|tail -n1 );
    len=$(wc -l /etc/.wordlist |cut -d " " -f1) ;facta=$(($(date +%s)*$RANDOM));pswa=$(head -n $(($facta%len)) /etc/.wordlist|tail -n1);factb=$(($(date +%s)*$RANDOM));pswb=$(head -n $(($factb%len)) /etc/.wordlist|tail -n1);num=$((($facta)%9999));randompass=$pswa"_"$pswb"_"$num;
    echo "user: printadmin | password: $randompass";
    echo "user: eventadmin | password: $randompass";
    echo "$randompass" > /etc/.printadminpass ;
    echo "$randompass" > /etc/.eventadminpass ;
    chmod go-rwx /etc/.printadminpass;
    htpasswd -c -b -B -C 12 /var/www/.htpasswd printadmin "$randompass" 
    htpasswd    -b -B -C 12 /var/www/.htpasswd eventadmin "$randompass" 
    )
#echo "..";echo "--";echo "##";
echo "             DON'T FORGET TO DEPLOY VALID SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem AND /etc/ssl/private/ca.pem(fullchain)"
echo "AGAIN!       DON'T FORGET TO DEPLOY VALID SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem AND /etc/ssl/private/ca.pem(fullchain)"


#cat /proc/1/cgroup |cut -d: -f3- |sort -u | grep  -e lxc/ -e docker/ && apt-get -y remove python3-pip
#apt-get -y install python3

which apt-get && ( 
apt-get -y autoremove
ls /var/cache/apt/archives/ -1|wc -l
apt-get clean all
ls /var/cache/apt/archives/ -1|wc -l
)
