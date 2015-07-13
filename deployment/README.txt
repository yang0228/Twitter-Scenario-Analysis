***
Procedures to deploy distributed nodes on NeCTAR Cloud, from establish instances to configuring 
each node. Applied Amazon EC2, Boto, Fabric SSH tool. 
***


**To deploy correctly, pls keep the directory structure and working in the deployment directory

1.Download personal key from nectar
	chmod 600 [YOUR KEY PAIR](with extension)

2.Download ec2 credential from nectar
	open 'ec2rc.sh' in ec2 credential directory, modify related parameters in 'connect.py'
	EC2_ACCESS_KEY -> aws_access_key_id (input with single quotes)
	EC2_SECRET_KEY -> aws_secret_access_key (input with single quotes)

3.Put [YOUR KEY PAIR] in the deployment directory with 'connect.py' 'fabfile.py' etc. 

	
4.run -> python connect.py [YOUR KEY PAIR](without extension)
			return a list of all VM IPs in a file called 'just_ip'
			and a file with their roles called 'role_ip'

5.run -> sh / bash configure_local.sh 
	1st vm with ip in just_ip is a Merge instance
	2nd - 5th are Computing instances
	6th - 7th are harvesters
	8th is a webserver to display views
	** check roles for each VM in 'role_ip', please run backend source code for each part,
	** don't run code of one part on other VMs, e.g. run master source code on a slave VM

6.change [YOUR KEY PAIR] in upload.sh
	run -> sh / bash upload.sh 
		upload source and configuration files to vm servers

7.change [YOUR KEY PAIR] in fabfile.py
	run -> sh / bash fab.sh 

** if you can't ssh/scp to specific server, try 'sudo ssh-keygen -R [SERVER IP]' and ssh again  
** if it's "WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED!" error, 1. 'sudo ssh-keygen -R [SERVER IP]'
	2.delete the ssh-rsa key with the ip that you want to ssh to in ~/.ssh/known_hosts
