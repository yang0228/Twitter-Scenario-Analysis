#!/bin/bash

counter=0

for VARIABLE in $( < just_ip)
do
	counter=$((counter+1))

	if [ "$counter" == 8 ]; then
		sed -i '' '8s/.*/	hostname = "'$VARIABLE'"/g' '../backend/src/main/resources/application.conf'
		sudo scp -r -i qingkey.pem '../webserver/www' 'ubuntu@'$VARIABLE':www'
		sudo scp -r -i qingkey.pem couchdb 'ubuntu@'$VARIABLE':couchdb'

	else
		sed -i '' '8s/.*/	hostname = "'$VARIABLE'"/g' '../backend/src/main/resources/application.conf'
		sudo scp -r -i qingkey.pem '../backend' 'ubuntu@'$VARIABLE':twittering'
		sudo scp -r -i qingkey.pem '../webserver/www' 'ubuntu@'$VARIABLE':www'
		sudo scp -r -i qingkey.pem sbt 'ubuntu@'$VARIABLE':sbt'
		sudo scp -r -i qingkey.pem couchdb 'ubuntu@'$VARIABLE':couchdb'

	fi
	echo "upload to $VARIABLE, done!"
done
