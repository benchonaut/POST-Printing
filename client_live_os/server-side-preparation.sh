#!/bin/bash 

echo "PACKAGING UNGGOGLED_CHROMIUM"
wget -c $(curl -s  https://ungoogled-software.github.io/$(curl -s "https://ungoogled-software.github.io/ungoogled-chromium-binaries/"|grep appimage/64bit/|cut -d'"' -f4)|grep href |grep AppImage|grep -v Naviga|cut -d'"' -f2) -O /tmp/chrome.bin

file /tmp/chrome.bin|grep -e AppImage -e "64-bit LSB executable" && (
   chmod +x /tmp/chrome.bin
   cd /tmp/;tar cvzf chrome.tgz chrome.bin
   test -e /var/www/html/chrome.tgz.old && rm /var/www/html/chrome.tgz.old
   test -e /var/www/html/chrome.tgz && mv  /var/www/html/chrome.tgz /var/www/html/chrome.tgz.old
   mv /tmp/chrome.tgz /var/www/html/chrome.tgz
   chmod 0600 /var/www/html/chrome.tgz
   chmod go+r /var/www/html/chrome.tgz
   chown www-data:www-data /var/www/html/chrome.tgz
   #rm /tmp/chrome.bin
)

test -e livecd-seed/root && (
    test -e /dev/shm/.livecd-seed && rm /dev/shm/.livecd-seed
    mkdir /dev/shm/.livecd-seed
   
    chown -R root:root livecd-seed/root
    (cd livecd-seed ; tar c .)|(cd  /dev/shm/.livecd-seed;tar xv)
    customseed="$1"
    [[ -z "$customseed" ]] && echo "NO CUSTOM SEED GIVEN"
    [[ -z "$customseed" ]] || (
    
    test -e "$customseed"  && (
        cd "$customseed" && (
         test -e .git && git pull
          cp -arv * /dev/shm/.livecd-seed/
        )
        )
    )
    
    (cd  /dev/shm/.livecd-seed  && tar cvzf /tmp/livecd-seed.tgz . )
    chown www-data:www-data /tmp/livecd-seed.tgz
    chmod ugo-w /tmp/livecd-seed.tgz
    mv /tmp/livecd-seed.tgz /var/www/html   
)

cat /root/.ssh/authorized_keys > /var/www/html/authorized_keys



which dpkg && ( dpkg --get-selections|grep -v deinst|grep -q  apt-cacher||  (apt-get update apt-get -y install apt-cacher-ng ) ) 
which apk && (
which docker || ( apk add docker ; rc-update add docker)
docker ps -a|grep acng|grep 3142 || (
   docker run --name apt-cacher-ng   --restart always   --detach   --volume /var/cache/apt-cacher-ng:/var/cache/apt-cacher-ng:rw   --publish 3142:3142   deployable/acng
)

)
