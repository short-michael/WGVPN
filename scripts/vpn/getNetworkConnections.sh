#!/bin/bash
currentLine=0
toFile=0

if [ "$#" -eq 1 ]; then
	echo "Outputing to file: $1"
        toFile=1
fi

# Start Output Data
if [ $toFile -eq 1 ]; then
	echo -n -e "{\n" > "$1"
else
	echo -n -e "{\n"
fi

foundFirstEntry=0
foundFirstProperty=0
foundDevice=0
while read input; do
	if [[ $input == "connection.id"* ]]; then
		foundFirstProperty=0
		connection=$(echo $input | cut -f2- -d':' | xargs)
		if [ $foundFirstEntry -eq 1 ]; then
			echo -e ",\n\t\"${connection}\": {"
		else
			echo -e "\t\"${connection}\": {"
			foundFirstEntry=1
		fi
		continue	
	fi
	if [ -z "${input}" ]; then
		echo -en "\n\t}"
		continue	
	fi
	
	# Entries for Each Property of the Interface
	key=$(echo $input | cut -f1 -d':' | xargs)
	value=$(echo $input | cut -f2- -d':' | xargs)
	
	if [ $foundFirstProperty -eq 1 ]; then
		echo -en ",\n\t\t\"${key,,}\": \"${value}\""
	else
		echo -en "\t\t\"${key,,}\": \"${value}\""
		foundFirstProperty=1
	fi

	(( currentLine++ ))
done <<< $(nmcli -o connection show Inside 2>/dev/null; echo ; nmcli -o connection show Outside 2>/dev/null)

# Close Last Entry
if [ $currentLine -gt 0 ]; then
#	echo "Close Brace Here"
	if [ $toFile -eq 1 ]; then
		echo -en "\n\t}\n" >> "$1"
	else
		echo -en "\n\t}\n"
	fi
fi

# End Output Data
if [ $toFile -eq 1 ]; then
	echo -n -e "}\n" >> "$1"
else
	echo -n -e "}\n"
fi

exit 0

