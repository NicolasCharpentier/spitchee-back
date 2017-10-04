#!/usr/bin/env bash

MYSQL_ROOT_MDP=root

sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $MYSQL_ROOT_MDP"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_MDP"

sudo apt-get update

sudo apt-get install -y mysql-server

aptitude -y install expect

sudo mysql_install_db
sudo mysql_install_db # Oui deux fois

SECURE_MYSQL=$(expect -c "
set timeout 10
spawn mysql_secure_installation
expect \"Enter current password for root (enter for none):\"
send \"$MYSQL_ROOT_MDP\r\"
expect \"Change the root password?\"
send \"n\r\"
expect \"Remove anonymous conferences?\"
send \"y\r\"
expect \"Disallow root login remotely?\"
send \"y\r\"
expect \"Remove test database and access to it?\"
send \"y\r\"
expect \"Reload privilege tables now?\"
send \"y\r\"
expect eof
")

echo "$SECURE_MYSQL"

aptitude -y purge expect
#apt-get install -y php5-mysql php5 libapache2-mod-php5 php5-mcrypt #subversion emacs
#sudo service apache2 restart
