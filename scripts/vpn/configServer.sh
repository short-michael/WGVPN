#!/bin/bash

PEERS=0
if [ ! -f /var/www/htdocs/data/wgServer.conf ]; then
	echo "New Header File Not Found!"
	exit 1
fi

if [ -f /etc/wireguard/wg0.conf ]; then
	grep "\[Peer\]" /etc/wireguard/wg0.conf
	result=$?
	if [ $result -eq 0 ]; then
		PEERS=`grep -n -m1 "\[Peer\]" /etc/wireguard/wg0.conf | cut -f1 -d':'`
		PEERS=$(( PEERS ))
		currentLine=1
		while read input; do
			if [ $currentLine -ge $PEERS ]; then
				echo "${input}" >> /var/www/htdocs/data/wgServer.conf
			fi
			(( currentLine++ ))
		done <<< $(cat /etc/wireguard/wg0.conf)	
	fi
fi

# Copy File / Fix Permisions / Clean Up
cp /var/www/htdocs/data/wgServer.conf /etc/wireguard/wg0.conf
chown root:root /etc/wireguard/wg0.conf
chmod 600 /etc/wireguard/wg0.conf
rm -f /var/www/htdocs/data/wgServer.conf

# Bring WireGuard Interface Down and Then Up Again
/usr/bin/wg-quick down wg0
sleep 2
sync
/usr/bin/wg-quick up wg0

exit 0
