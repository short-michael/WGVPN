#!/bin/bash
KEY=""
FILE=""
TMPFILE=/tmp/wg0.tmp

if [ ! -f /var/www/htdocs/data/REMOVAL ]; then
	echo "File Describing Removal is not Present!"
	exit 1
fi
if [ -f "${TMPFILE}" ]; then
	# echo "Deleting Temp File!"
	rm -f ${TMPFILE}
fi

currentLine=0
while read input; do
	if [ $currentLine -eq 0 ]; then
		KEY=$(echo "PublicKey = ${input}" | xargs)
	fi
	if [ $currentLine -eq 1 ]; then
		FILE=$(echo "${input}" | xargs)
	fi
	if [ $currentLine -gt 1 ]; then
		break
	fi
	(( currentLine++ ))
done <<< $(cat /var/www/htdocs/data/REMOVAL)	

if [ ! -f /etc/wireguard/wg0.conf ]; then
	echo "wg0.conf file not found!"
	exit 2
fi

START=0
END=0
FOUND=0
#currentLine=1
currentLine=0
while read line; do
	(( currentLine++ ))
	if [[ "${line}" =~ "[Peer]"* ]]; then
		if [ $START -ne 0 ]; then
			if [ $FOUND -ne 0 ]; then
				break
			fi
		fi
		START=$(( currentLine-1 ))
		# echo "START [${START}]"
	fi
	if [ "${line}" = "${KEY}" ]; then
		# echo "FOUND KEY! [$currentLine]"
		FOUND=1
	fi
	# Below should catch the last line of file if it is the last Peer Entry
	if [ $FOUND -ne 0 ]; then
		if [[ "${line}" =~ "AllowedIPs="* ]]; then
			END=$(( currentLine ))
		else
			END=$(( currentLine-1 ))
		fi
	fi
done <<< $(cat /etc/wireguard/wg0.conf)	

if [ $FOUND -ne 0 ]; then
#	echo "Found Peer Section to remove [$START to $END]"
	currentLine=1
	while read confLine; do
		if [ $currentLine -lt $START ] || [ $currentLine -gt $END ]; then
			echo "${confLine}" >> ${TMPFILE}
		fi
		(( currentLine++ ))
	done <<< $(cat /etc/wireguard/wg0.conf)
	if [ -f "${TMPFILE}" ]; then
		cp $TMPFILE /etc/wireguard/wg0.conf
	fi
fi

# Remove Client Configuration Download File
if [ -f "/var/www/htdocs/downloads/${FILE}" ]; then
	rm -f /var/www/htdocs/downloads/${FILE}
fi

# Copy New File and Restart WireGuard
if [ -f "${TMPFILE}" ]; then
	# Copy File / Fix Permisions / Clean Up
	cp $TMPFILE /etc/wireguard/wg0.conf
	chown root:root /etc/wireguard/wg0.conf
	chmod 600 /etc/wireguard/wg0.conf
	rm -f ${TMPFILE}

	# Bring WireGuard Interface Down and Then Up Again
	/usr/bin/wg-quick down wg0
	sleep 2
	sync
	/usr/bin/wg-quick up wg0

	# Cleanup by removing the REMOVAL file
	if [ -f /var/www/htdocs/data/REMOVAL ]; then
		rm -f /var/www/htdocs/data/REMOVAL
	fi
else
	# We should never get here
	echo "ERROR: Something bad happened!"
	exit 99
fi

exit 0
