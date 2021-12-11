#!/bin/bash
# What Files do we remove/reset when we do a wipe?
# Log Files
rm -f /var/log/btmp.*
rm -f /var/log/cron.*
cat /dev/null > /var/log/cron
rm -f /var/log/debug.*
cat /dev/null > /var/log/debug
rm -f /var/log/messages.*
cat /dev/null > /var/log/messages
rm -f /var/log/secure.*
cat /dev/null > /var/log/secure
rm -f /var/log/syslog.*
cat /dev/null > /var/log/syslog

rm -f /var/log/httpd/*_log.*
cat /dev/null > /var/log/htpd/access_log
cat /dev/null > /var/log/htpd/error_log

# WireGuard Keys and Config
rm -f /etc/wireguard/*

# Web Server Data Files and Downloads
rm -f /var/www/htdocs/downloads/*
rm -f /var/www/htdocs/data/*
rm -f /var/www/htdocs/data/client/*

rm -f /root/.bash_history
rm -rf /root/.ssh

cd /
/usr/bin/tar xvfz /root/defaults.tgz

# ensure services that should be on/off are set properly
chmod +x /etc/rc.d/rc.acpid
chmod +x /etc/rc.d/rc.cpufreq
chmod +x /etc/rc.d/rc.crond
chmod +x /etc/rc.d/rc.elogind
chmod +x /etc/rc.d/rc.httpd
chmod +x /etc/rc.d/rc.inet1
chmod +x /etc/rc.d/rc.inet2
chmod +x /etc/rc.d/rc.local
chmod +x /etc/rc.d/rc.loop
chmod +x /etc/rc.d/rc.messagebus
chmod +x /etc/rc.d/rc.modules*
chmod +x /etc/rc.d/rc.networkmanager
chmod +x /etc/rc.d/rc.setterm
chmod +x /etc/rc.d/rc.syslog
chmod +x /etc/rc.d/rc.sysvinit
chmod +x /etc/rc.d/rc.udev

chmod -x /etc/rc.d/rc.mysqld
chmod -x /etc/rc.d/rc.ntpd
chmod -x /etc/rc.d/rc.php-fpm
chmod -x /etc/rc.d/rc.saslauthd
chmod -x /etc/rc.d/rc.serial
chmod -x /etc/rc.d/rc.sshd

sync
/sbin/reboot

