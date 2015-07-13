#!/bin/bash
counter=0

for VARIABLE in $( < just_ip)
do
	counter=$((counter+1))

	if [ "$counter" == 8 ]; then
		sudo fab -H 'ubuntu@'$VARIABLE':22' client_deploy

	else
		sudo fab -H 'ubuntu@'$VARIABLE':22' deploy
	

	fi

done
