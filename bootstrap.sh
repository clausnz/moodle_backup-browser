#!/usr/bin/env bash

echo "---------- Installing Software ----------"
sudo apt-get update
sudo apt-get install -y apache2 php5 php5-cli vim curl postgresql postgresql-contrib php5-pgsql


echo "---------- Doing Apache stuff ----------"
sudo a2enmod rewrite
rm -rf /var/www
ln -fs /vagrant /var/www
sudo chmod -R 777 /var/www
sed -i "s/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/public/" /etc/apache2/sites-enabled/000-default.conf
sed -i "s/<\/VirtualHost>/\n<Directory \/var\/www\/public\/>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n<\/Directory>\n\n<\/VirtualHost>/" /etc/apache2/sites-enabled/000-default.conf
sudo service apache2 restart


echo "---------- Setup Postgres Demo DB ----------"
sudo -u postgres createdb moodle
sudo -u postgres psql moodle < /var/www/dbfiles/mdl_course.sql
sudo -u postgres psql moodle < /var/www/dbfiles/mdl_course_categories.sql
sudo -u postgres psql moodle < /var/www/dbfiles/mdl_context.sql
sudo -u postgres psql moodle < /var/www/dbfiles/mdl_role_assignments.sql
sudo -u postgres psql moodle < /var/www/dbfiles/mdl_user.sql
sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"
sudo service postgresql restart


echo "---------- Setup Demo Filesystem ----------"
sudo mkdir -p /mnt/moodlebackup/backup/tagessicherung
sudo mkdir -p /mnt/moodlebackup/backup/archiv
sudo mkdir -p /mnt/moodlebackup/backup/tagessicherung.bak
sudo mkdir -p /mnt/moodlebackup/backup/database

#echo "---------- Installing Composer ----------"
#curl -Ss https://getcomposer.org/installer | php
#sudo mv composer.phar /usr/bin/composer


