#get http://releases.ubuntu.com/16.04/ubuntu-16.04.3-desktop-amd64.iso #>install (e.g. unetbootin, tftp)

mkdir /var/spool/lpd/ -p

dpkg --add-architecture i386;
apt-get update && apt-get -y --force-yes install sshfs git pv snmp nginx curl php-fpm openssh-server avahi-utils libjansson4 byobu cups cups-ipp-utils cups-bsd cups-common openssl libc6:i386 libstdc++6:i386
dpkg -i --force-all ql720nwlpr-1.1.4-0.i386.deb && dpkg -i --force-all ql720nwcupswrapper-1.1.4-0.i386.deb
cp evolis-primacyE.ppd.gz /usr/share/cups/model/ && cp evorasterizer /usr/lib/cups/filter/ && chmod 755 /usr/share/cups/model/evolis-primacyE.ppd.gz /usr/lib/cups/filter/evorasterizer

for base in *base64 ; do  base64 -d $base > "/etc/cups/ppd/"${base/.base64/};done
for type in CARD LABEL;do for i in {02..16};do ln -s /etc/cups/ppd/$type"01.ppd" /etc/cups/ppd/$type$i.ppd 2>/dev/null;ln -s /etc/cups/ppd/$type"01.ppd.O" /etc/cups/ppd/$type$i.ppd.O 2>/dev/null;done;done

/etc/init.d/cups stop ;
(cat stamp.header;for i in {01..16};do nonzeroed=${i/#0/};zeroed=$i;labelip=$(expr $zeroed + 20) ;
cat stamp.card stamp.label |sed 's/CARDNUMBERZEROED/'$zeroed'/g;s/CARDNUMBER/'$nonzeroed'/g;s/LABELIP/'$labelip'/g;s/LABELNUMBERZEROED/'$zeroed'/g;s/LABELNUMBER/'$nonzeroed'/g' ;done ) > /etc/cups/printers.conf;

tar xzf ql720nw.tgz -C /
ln -s /opt/brother/PTouch/ql720nw/cupswrapper/brother_lpdwrapper_ql720nw /usr/lib/cups/filter/
#cp -aurv brother_lpdwrapper_ql720nw /usr/lib/cups/filter/brother_lpdwrapper_ql720nw
cp -aurv default /etc/nginx/sites-available
cp -aurv printer_status.sh /etc/
cp -aurv print.php router.php /var/www/html/
chown -R www-data:www-data /var/www/
chown -R root:lp /etc/cups

(test -e /etc/ssl/private/nginx.key && test -e /etc/ssl/private/crt.pem ) ||   openssl req -x509 -nodes -days 3650 -subj "/C=US/ST=Denial/L=Springfield/O=Dis/CN=printserver.local" -newkey rsa:4096 -keyout /etc/ssl/private/nginx.key -out /etc/ssl/private/crt.pem
ln -s /etc/ssl/private/crt.pem /etc/ssl/private/ca.pem
/etc/init.d/cups start
/etc/init.d/nginx restart
echo;echo;echo;echo;echo;
echo "DON'T FORGET TO DEPLOY SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem"
echo "DON'T FORGET TO DEPLOY SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem"
echo "DON'T FORGET TO DEPLOY SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem"
echo "DON'T FORGET TO DEPLOY SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem"
echo "DON'T FORGET TO DEPLOY SSL KEYS UNDER /etc/ssl/private/nginx.key AND /etc/ssl/private/crt.pem"

