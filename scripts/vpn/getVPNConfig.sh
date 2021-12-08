#!/bin/bash
currentLine=0
INTERFACE=0
PEER=0
peerCount=0
foundPrivate=0
foundPublic=0
entries=0
existingConfig=0

# Start Output
echo -n -e "{\n"

if [ -f /etc/wireguard/wg0.conf ]; then
  existingConfig=1
  # Parse Existing Config File
  while read input; do
    # Identify Markers
    if [[ ${input} == "[Interface]"* ]]; then
      PEER=0
      INTERFACE=1
      echo -e "\t\"Interface\": {"
      foundFirstProperty=0
      (( entries++ ))
      continue	
    fi
    if [[ ${input} == "[Peer]"* ]]; then
      PEER=1
      INTERFACE=0
      echo -e ",\n\t\"Peer${peerCount}\": {"
      foundFirstProperty=0
      (( entries++ ))
      (( peerCount++ ))
      continue	
    fi
    if [ -z "${input}" ]; then
      echo -en "\n\t}"
      PEER=0
      INTERFACE=0
      continue	
    fi

    # Parse Each Section
    # Entries for Each Property of the Section
    key=$(echo $input | cut -f1 -d'=' | xargs)
    value=$(echo $input | cut -f2- -d'=' | xargs)

    if [ ${key,,} == "privatekey" ]; then
      if [ -f /etc/wireguard/server_private.key ]; then
        prvKey=`head -n1 /etc/wireguard/server_private.key | xargs`
        if [ $prvKey == $value ]; then
          foundPrivate=1
        else
          if [ ! -z $prvKey ]; then
            value=$prvKey
          fi
        fi
      fi
      if [ $foundFirstProperty -eq 1 ]; then
        echo -en ",\n\t\t\"${key,,}\": \"${value}\""
      else
        echo -en "\t\t\"${key,,}\": \"${value}\""
        foundFirstProperty=1
      fi
      if [ -f /etc/wireguard/server_public.key ]; then
        pubKey=`head -n1 /etc/wireguard/server_public.key | xargs`
        if [ ! -z $pubKey ]; then
          foundPublic=1
          key="publickey"
          value=$pubKey
          if [ $foundFirstProperty -eq 1 ]; then
            echo -en ",\n\t\t\"${key,,}\": \"${value}\""
          else
            echo -en "\t\t\"${key,,}\": \"${value}\""
            foundFirstProperty=1
          fi
        fi
      fi
    else
      if [ $foundFirstProperty -eq 1 ]; then
        echo -en ",\n\t\t\"${key,,}\": \"${value}\""
      else
        echo -en "\t\t\"${key,,}\": \"${value}\""
        foundFirstProperty=1
      fi
    fi

    (( currentLine++ ))
  done <<< $(cat /etc/wireguard/wg0.conf)

  # Close Last Entry
  if [ $currentLine -gt 0 ]; then
    echo -en "\n\t}"
  fi
fi

# Add Entry for Outside, outsideAddress
outAddy=""
if [ -f /var/www/htdocs/data/ADDRESS ]; then
	outAddy=`cat /var/www/htdocs/data/ADDRESS | xargs`
else
	if [ -z $outAddy ]; then
		outAddy=`curl ifconfig.me/ip 2>/dev/null`
	fi
fi
if [ ! -z $outAddy ]; then
	if [ $entries -eq 0 ]; then 
		echo -e "\n\t\"Outside\": {"
	else
		echo -e ",\n\t\"Outside\": {"
	fi
	echo -en "\t\t\"outsideaddress\": \"${outAddy}\""
	echo -en "\n\t}"
	(( entries++ ))
fi

# Add Entry for number of Peers
if [ $entries -eq 0 ]; then 
	echo -e "\n\t\"Info\": {"
else
	echo -e ",\n\t\"Info\": {"
fi
echo -en "\t\t\"peerCount\": \"${peerCount}\""
internalNetworkDns=""
if [ -f /var/www/htdocs/data/INSIDEDNS ]; then
	internalNetworkDns=`cat /var/www/htdocs/data/INSIDEDNS | xargs`
fi
if [ ! -z $internalNetworkDns ]; then
	echo -en ",\n\t\t\"internalNetworkDns\": \"${internalNetworkDns}\""
fi
echo -en "\n\t}"
(( entries++ ))

# We must not have a wg0.conf file, so grab the keys and make entries for them
if [ $existingConfig -eq 0 ]; then 
	foundFirstProperty=0
	if [ -f /etc/wireguard/server_private.key ]; then
		prvKey=`head -n1 /etc/wireguard/server_private.key | xargs`
		if [ $entries -gt 0 ]; then
			echo -e "\n\t\"Interface\": {"
		else
			echo -e "\t\"Interface\": {"
		fi
        	echo -en "\t\t\"privatekey\": \"${prvKey}\""
		foundFirstProperty=1
	fi
	if [ -f /etc/wireguard/server_public.key ]; then
		pubKey=`head -n1 /etc/wireguard/server_public.key | xargs`
		if [ $foundFirstProperty -eq 1 ]; then
			echo -en ",\n\t\t\"publickey\": \"${pubKey}\""
		else
			if [ $entries -gt 0 ]; then
				echo -e "\n\t\"Interface\": {"
			else
				echo -e "\t\"Interface\": {"
			fi
			echo -en "\n\t\t\"publickey\": \"${pubKey}\""
			foundFirstProperty=1
		fi
	fi
	if [ $foundFirstProperty -eq 1 ]; then
		echo -n -e "\n\t}\n"
	fi
fi

echo -en "\n"

# End Output Data
echo -n -e "}\n"


exit 0

