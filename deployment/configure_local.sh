#!/bin/bash

counter=0
slave=0

for VARIABLE in $( < just_ip)
do
	counter=$((counter+1))
	echo $counter

	if [ "$counter" == 8 ]; then
	sed -i '' '1s/.*/webserver='$VARIABLE'/g' '../backend/src/main/resources/webserver.conf'
	elif [ "$counter" == 2 ]; then
		sed -i '' '1s/.*/slave='$VARIABLE'/g' '../backend/src/main/resources/slave.conf'
		sed -i '' ''$((slave+15))'s/.*/	\"akka\.tcp\:\/\/ClusterSystem\@'$VARIABLE'\:2551\",/g' '../backend/src/main/resources/application.conf'
		slave=$((slave+1))
	elif [ "$counter" -gt 2 ]; then
		if [ "$counter" -lt 5 ]; then
			sed -i '' '/^slave=/ s/$/ '$VARIABLE'/' '../backend/src/main/resources/slave.conf'
			sed -i '' ''$((slave+15))'s/.*/	\"akka\.tcp\:\/\/ClusterSystem\@'$VARIABLE'\:2551\",/g' '../backend/src/main/resources/application.conf'
			slave=$((slave+1))
		elif [ "$counter" == 5 ]; then
			sed -i '' '/^slave=/ s/$/ '$VARIABLE'/' '../backend/src/main/resources/slave.conf'
			sed -i '' ''$((slave+15))'s/.*/	\"akka\.tcp\:\/\/ClusterSystem\@'$VARIABLE'\:2551\"/g' '../backend/src/main/resources/application.conf'
			slave=$((slave+1))
		fi
	fi
	echo $slave
	
done

echo "Local configuration, done! Follow next step to upload"



