#!/bin/bash

## example usage ( from inside live cd )
## wget -O- http://10.0.0.1/gen.sh |bash /dev/stdin 10.0.0.1
## scp /tmp/slax-custom.iso user@10.0.0.1:

slax deactivate 05-chromium.sb
#echo "127.111.111.11 ipv4.connman.net" >> /etc/hosts
NAMSRV=$1
[[ -z "$NAMSRV" ]] && echo "NO NAMESERVER GIVEN..QUITTING"
[[ -z "$NAMSRV" ]] && exit 1

echo nameserver "$NAMSRV"  > /etc/resolv.conf
(
timedatectl set-timezone Europe/Berlin
#echo "Europe/London" > /etc/timezone
dpkg-reconfigure -f noninteractive tzdata

) &

## rc.local
(echo '[Unit]
 Description=/etc/rc.local Compatibility
 ConditionPathExists=/etc/rc.local

[Service]
 Type=forking
 ExecStart=/etc/rc.local start
 TimeoutSec=0
 StandardOutput=tty
 RemainAfterExit=yes
 SysVStartPriority=99

[Install]
 WantedBy=multi-user.target' > /etc/systemd/system/rc-local.service
 (echo '#!/bin/bash';echo  'bash /etc/rc.local.real 2>&1 |tee  /dev/shm/system.init.1.rc_local.log';echo  'exit 0' )| tee  /etc/rc.local
 chmod +x /etc/rc.local
 systemctl enable rc-local
 
 ) &
(
#echo 'Acquire::http { Proxy "http://printserver.local:3142"; };
#Acquire::https { Proxy "https://"; };' > /etc/apt/apt.conf.d/99proxy

sed 's~http://~http://printserver.local:3142/~g;s~https://~http://printserver.local:3142/~g' -i /etc/apt/sources.list /etc/apt/sources.list.d/* -i 

apt-get update ) &
wait
setxkbmap de;apt-get update;apt-get -y upgrade;
apt-get -y install locales

( apt-get -y install sudo avahi-utils mtr libnss3 lftp xinput avahi-daemon screen libnotify-bin dunst curl jq;apt-get -y install $(apt list --upgradable|grep -v -e linux-image)
apt-get remove -y pcmanfm
apt-get clean all 
rm /var/lib/apt/lists/* 
## remove entries/patch entries
sed 's/.\+xterm.\+//g;s/.\+pcmanfm.\+//g;s/.\+connman-gtk.\+//g;s/&& galculator /&& sudo -u guest galculator /g;s/&& scite /&& sudo -u guest scite /g' -i /root/.fluxbox/menu
echo 'session.screen0.toolbar.autoHide:       false' >> /root/.fluxbox/init

sed 's/^# de_DE/de_DE/g' -i /etc/locale.gen
locale-gen 
) &

(cd /;wget -c -O- http://printserver.local/livecd-seed.tgz |tar xvz ) &


wait 
( 
cd /usr/share/applications
rm *connman* *pcmanfm* lftp.desktop xarchiver.desktop *terminal*
)
rm /var/log/*.log
rm -rf /usr/share/man/*
cd /tmp
journalctl --vacuum-time 1s
savechanges 99-custom.sb
genslaxiso -e 'chromium' slax-custom.iso 99-custom.sb
