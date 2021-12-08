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
tar xvfz /root/defaults.tgz
sync
reboot

