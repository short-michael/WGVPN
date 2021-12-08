#!/bin/bash
YEAR=`date +'%Y'`

echo ${YEAR}
while (( $YEAR < 2021)); do
	ntpdate 0.pool.ntp.org 1>/dev/null 2>&1
	RESULT=$?	
	YEAR=`date +'%Y'`
	if (( $YEAR < 2021 )); then
		sleep 60
		continue
	fi
done
exit 0
