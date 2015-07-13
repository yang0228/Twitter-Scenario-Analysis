
from fabric.api import *
from fabric.api import cd,run,env,hosts

env.key_filename=['qingkey.pem']

remote_home_dir = '/home/ubuntu'
    

def deploy():
    with cd('/'):
        sudo('apt-get update')
    with cd('/home/ubuntu'):
        sudo('chmod u+w ../ubuntu')
        sudo('pwd')
        # Install scala
        run('sudo apt-get -y install scala')
        #Install sbt
        sudo('mkdir bin')
        sudo('mv sbt ~/bin')
    with cd('/home/ubuntu/bin'):
        sudo('wget http://repo.typesafe.com/typesafe/ivy-releases/org.scala-sbt/sbt-launch/0.13.2/sbt-launch.jar')
       
    with cd('/home/ubuntu'):
        sudo('echo -ne "export PATH=$PATH:~/bin" >> ~/.bashrc')
        sudo('chmod u+x ~/bin/sbt')
        #Install Git
        sudo('apt-get -y install git')
        #Install CouchDb
        sudo('apt-get -y install couchdb')
        #Install tmux
        sudo('apt-get -y install tmux')
        sudo('echo -ne "source ~/.bashrc" >> .bash_profile')
        sudo('cp /usr/share/doc/tmux/examples/screen-keys.conf ./.tmux.conf')
        sudo('kill -9 `sudo lsof -t -i:5984`')

    with cd('/etc/couchdb/'):

        sudo('sed -i "12s/;port = 5984/port = 5984/g" local.ini')
        sudo('sed -i "13s/;bind_address = 127.0.0.1/bind_address = 0.0.0.0/g" local.ini')
        sudo('kill -9 `sudo lsof -t -i:5984`')



def  client_deploy():
    with cd('/home/ubuntu'):
        sudo('pwd')
        sudo('chmod u+w ../ubuntu')
        run("du -sh")
        run('sudo mv www /var')
        run('sudo apt-get update')

        run('sudo apt-get -y install couchdb')
        run('sudo apt-get -y install apache2')
	    #run('sudo apt-get install php') E: Unable to locate package php
        run('sudo apt-get -y install php5')
        run('sudo apt-get -y install libapache2-mod-php5')
        run('sudo apachectl restart')
        run('sudo apt-get -y install php5-mysql php5-memcache')
        run('sudo apt-get -y install memcached')
        run('sudo apt-get -y install php-pear')
        run('sudo apt-get -y install build-essential')
        run('sudo pecl install memcache')
        run('sudo apt-get -y install php5-dev')
        run('sudo apt-get -y install curl')
        run('sudo apt-get -y install php5-curl')
        run('sudo apachectl restart')
        #Set Up Mod_Rewrite of URL, patterns stored in .htaccess
        run('sudo a2enmod rewrite')
        run('sudo service apache2 restart')
        
    with cd('/etc/php5/apache2/'):
        run('sudo chmod 666 php.ini')
        run('sudo echo "extension=memcache.so" >> php.ini')
    with cd('/etc/apache2/sites-available/'):
        run('sudo mv 000-default.conf default')
        sudo('kill -9 `sudo lsof -t -i:5984`')

    with cd('/etc/couchdb/'):
        sudo('sed -i "12s/;port = 5984/port = 5984/g" local.ini')
        sudo('sed -i "13s/;bind_address = 127.0.0.1/bind_address = 0.0.0.0/g" local.ini')
        sudo('kill -9 `sudo lsof -t -i:5984`')

