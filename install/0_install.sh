#get http://releases.ubuntu.com/16.04/ubuntu-16.04.3-desktop-amd64.iso #>install (e.g. unetbootin, tftp)

mkdir /var/spool/lpd/ -p

dpkg --add-architecture i386;
apt-get update && apt-get -y --force-yes install sshfs git pv socat snmp nginx curl python3-pip php-fpm tor openssh-server apparmor-utils avahi-utils libjansson4 byobu cups cups-ipp-utils cups-bsd cups-common openssl libc6:i386 libstdc++6:i386

#Evolis Primacy installation
cp evolis-primacyE.ppd.gz /usr/share/cups/model/ && cp evorasterizer /usr/lib/cups/filter/ && chmod 755 /usr/share/cups/model/evolis-primacyE.ppd.gz /usr/lib/cups/filter/evorasterizer

##ql720 install is done below
### DO NOT ! dpkg -i --force-all ql720nwlpr-1.1.4-0.i386.deb && dpkg -i --force-all ql720nwcupswrapper-1.1.4-0.i386.deb
echo "++ql720++"
tar xzf ql720nw.tgz -C /
rm /usr/lib/cups/filter/brother_lpdwrapper_ql720nw
cp -aurv brother_lpdwrapper_ql720nw /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw
ln -s /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw
chown root:root /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw 
chmod go-w /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw
#cp -aurv brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw

#QL810w needs some special attention
pip3 install --upgrade https://github.com/pklaus/brother_ql/archive/master.zip
cd brother_ql;python setup.py install;cd ..

aa-complain cupsd

grep "^HiddenServiceDir /var/lib/tor/ssh/" /etc/tor/torrc || (/etc/init.d/tor stop 2>&1 >/dev/null;echo "CkhpZGRlblNlcnZpY2VEaXIgL3Zhci9saWIvdG9yL3NzaC8KSGlkZGVuU2VydmljZVBvcnQgMjIgMTI3LjAuMC4xOjIyCkhpZGRlblNlcnZpY2VBdXRob3JpemVDbGllbnQgc3RlYWx0aCBzc2gKCgo=" |base64 -d  >> /etc/tor/torrc ;rm /lib/systemd/system/tor.service;service tor@default stop;service tor@default start)

(grep "^PermitRootLogin without-password" /etc/ssh/sshd_config -q || grep "^PermitRootLogin prohibit-password" /etc/ssh/sshd_config -q || grep "^PermitRootLogin no" /etc/ssh/sshd_config -q) && echo SAFE || ( echo "PermitRootLogin without-password" >> /etc/ssh/sshd_config )

for base in *base64 ; do  base64 -d $base > "/etc/cups/ppd/"${base/.base64/};done
for type in CARD LABEL;do for i in {02..16};do cp -aurv /etc/cups/ppd/$type"01.ppd" /etc/cups/ppd/$type$i.ppd 2>/dev/null;cp -aurv /etc/cups/ppd/$type"01.ppd.O" /etc/cups/ppd/$type$i.ppd.O 2>/dev/null;done;done

/etc/init.d/cups stop ;
(cat stamp.header;for i in {01..16};do nonzeroed=${i/#0/};zeroed=$i;labelip=$(expr $zeroed + 20) ;
cat stamp.card stamp.label |sed 's/CARDNUMBERZEROED/'$zeroed'/g;s/CARDNUMBER/'$nonzeroed'/g;s/LABELIP/'$labelip'/g;s/LABELNUMBERZEROED/'$zeroed'/g;s/LABELNUMBER/'$nonzeroed'/g' ;done ) > /etc/cups/printers.conf;

cp -aurv default /etc/nginx/sites-available
cp -aurv printer_status.sh printer_clean_tmp.sh /etc/
cp -aurv print.php router.php /var/www/html/
tar xvzf favicon.tgz -C /var/www/html/
chown -R www-data:www-data /var/www/
chown -R root:lp /etc/cups
adduser www-data lpadmin

echo  "MAKE SURE PHP_FPM is RUNNING BEFORE YOU START INSTALLER OR START AGAIN"
test -f $(find /var/run/ -name "*fpm.sock" |tail -n1 ) || ( echo "NO FPM SOCK .. EXITING" ; exit 0 ) 
ln -s  $(find /var/run/ -name "*fpm.sock" |tail -n1 )  /etc/php-fpm.sock

(test -e /etc/ssl/private/nginx.key && test -e /etc/ssl/private/crt.pem ) ||   openssl req -x509 -nodes -days 3650 -subj "/C=US/ST=Denial/L=Springfield/O=Dis/CN=printserver.local" -newkey rsa:4096 -keyout /etc/ssl/private/nginx.key -out /etc/ssl/private/crt.pem
test -e /etc/ssl/private/ca.pem || ln -s /etc/ssl/private/crt.pem /etc/ssl/private/ca.pem
/etc/init.d/cups start
test -f /var/www/html/index.nginx-debian.html && rm /var/www/html/index.nginx-debian.html
cp index.html /var/www/html/;chown www-data:www-data /var/www/html/index.html
/etc/init.d/nginx restart

grep -q "www-data ALL=NOPASSWD: /usr/local/bin/brother_ql" /etc/sudoers || (echo "www-data ALL=NOPASSWD: /usr/local/bin/brother_ql" >> /etc/sudoers; echo "+ sudoers updated" )  && echo "→  sudoers modded already"
grep printer_clean_tmp /etc/crontab  || (echo "++ Installing cron printer dat cleanr"; echo "Ki81ICogICAqICogKiAgIHJvb3QgICAgL2Jpbi9iYXNoIC9ldGMvcHJpbnRlcl9jbGVhbl90bXAuc2ggMj4mMSA+IC90bXAvY2xlYW5sb2cK"|base64 -d |tee -a /etc/crontab )

grep 'find /var/log/ -name ' /etc/crontab |grep 'gz" -delete'  || (echo Installing cron log.gz cleaner;echo "KiAqLzIgICAqICogKiAgIHJvb3QgICAgZmluZCAvdmFyL2xvZy8gLW5hbWUgIipneiIgLWRlbGV0ZSAyPiYxID4gL3RtcC9jbGVhbmd6bG9nCg=="|base64 -d |tee -a /etc/crontab )

test -e /var/www/html/branding-www.png || ln -s /etc/branding-www.png /var/www/html/branding-www.png
for imagi in /etc/ImageMagick-6/policy.xml /etc/ImageMagick/policy.xml;do 
	test -f "$imagi" && ( sed 's/rights="none" pattern="PDF/rights="read|write" pattern="PDF/g' "$imagi" -i ;
	  	grep -q 'policy domain="coder" rights="read|write" pattern="LABEL"' $imagi || echo '<policy domain="coder" rights="read|write" pattern="LABEL" />' |tee -a "$imagi"  )
done


echo "..";echo "--";echo "##";
echo "DON'T FORGET TO DEPLOY SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem AND /etc/ssl/private/ca.pem(fullchain)"
echo "DON'T FORGET TO DEPLOY SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem AND /etc/ssl/private/ca.pem(fullchain)"
echo "DON'T FORGET TO DEPLOY SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem AND /etc/ssl/private/ca.pem(fullchain)"

sleep 10
echo REMOTE: $(cat /var/lib/tor/ssh/hostname |sed 's/$/-'$(hostname)'-/g;s/$/'$(cat /etc/machine-id)'/g;')
echo
echo DONE
