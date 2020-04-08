# General
```bash
sudo vi /etc/hostname
sudo hostname <servername>
sudo vi /etc/hosts
```
```bash
sudo apt update
sudo apt -y upgrade
sudo apt install lamp-server^
sudo apt install -y git p7zip-full vim htop php-gmp php-xml php-curl php-gd php-mbstring lib32z1
```
For desktop only
```bash
sudo apt install -y giggle
```

Change to your timezone by
```bash
sudo dpkg-reconfigure tzdata
```
Update bashrc
```bash
cd ~
vi .bashrc
```
Customized alias
```
alias cd..='cd ..'
alias cd...='cd ../..'
alias cd....='cd ../../..'
alias kill='kill -9 '
alias x='exit'
alias sudo='sudo '
alias restart='sudo shutdown -r now'
alias shutdown='sudo shutdown -h now'
alias c='clear'
```
### Add SWAP

[http://askubuntu.com/questions/178712/how-to-increase-swap-space](http://askubuntu.com/questions/178712/how-to-increase-swap-space)
```
sudo mkdir -p /media/fasthdd
sudo touch /media/fasthdd/swapfile.img
sudo dd if=/dev/zero of=/media/fasthdd/swapfile.img bs=2048 count=1M
sudo mkswap /media/fasthdd/swapfile.img
sudo chmod 600 /media/fasthdd/swapfile.img
```
Add this line to /etc/fstab
```
/media/fasthdd/swapfile.img swap swap sw 0 0
```
Run
```
sudo swapon /media/fasthdd/swapfile.img
```
### Add another disk
Create hd on virtualbox
```bash
sudo fdisk -l
sudo mkfs.ext4 /dev/xvdb
sudo mkdir /hd1
sudo vi /etc/fstab
```
Add this line:
```
/dev/xvdb /hd1 auto noatime 0 0
```
Run
```bash
sudo mount -a
sudo chown ubuntu:ubuntu /hd1
```


### To move home folder
```
mkdir /hd1/home
sudo rsync -aXS /home/. /hd1/home/.
sudo mv /home /home_bkup
sudo ln -s /hd1/home /home
```
### Release space
```bash
sudo apt autoremove
sudo apt autoremove --purge snapd
```
### Remove old linux kernels
```
dpkg --list | grep linux-image | awk '{ print $2 }' | sort -V | sed -n '/'`uname -r`'/q;p' | xargs sudo apt-get -y purge
```
### User control
Add existing user to group
```
usermod -a -G <group> <username>
```
Add user
```
adduser <username>
```
### Other system command
Diff only file name recursively
```
diff -qr
```
rsync
```
rsync -chavzXSP
```
# Mysql
### Move mysql db folder
```
sudo service mysql stop
sudo cp -r /var/lib/mysql /data/mysql
sudo mv /var/lib/mysql /var/lib/mysql.bak
sudo chown -Rf mysql:mysql /data/mysql
sudo ln -s /data/mysql /var/lib/mysql
sudo sed -i 's/\/var\/lib/\/data/g' /etc/apparmor.d/usr.sbin.mysqld
sudo service apparmor restart
sudo service mysql start
```

### Mysql command

Get each table size in DB
```mysql
set @db=''; SELECT table_name AS "Tables", round(((data_length + index_length) / 1024 / 1024), 2) "Size in MB" FROM information_schema.TABLES WHERE table_schema = @db ORDER BY (data_length + index_length) DESC;
```

Get each DB size in MB
```mysql
SELECT table_schema "Database Name", sum( data_length + index_length ) / 1024 / 1024 "Database Size in MB" FROM information_schema.TABLES GROUP BY table_schema;
```
Mysqldump and zip
```bash
mysqldump -u user -p database | gzip > database.sql.gz
gunzip < database.sql.gz | mysql -u user -p database
```
Group by downgrade on ONLY_FULL_GROUP_BY

```
sudo vi /etc/mysql/mysql.conf.d/mysqld.cnf
````
Edit
```
[mysqld]

sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
```

Mysql general log setting
```
show variables like '%general_log%';
SET GLOBAL general_log = 'ON';
```

Mysql password validate setting for dev env

```
set global validate_password_policy = LOW;
set global validate_password_number_count = 0;
set global validate_password_mixed_case_count = 0;
set global validate_password_length = 1;
set global validate_password_special_char_count = 0;
SHOW VARIABLES LIKE 'validate_password%';
```

# VirtualBox

### virtualbox uuid confliction solution
```
VBOXMANAGE.EXE internalcommands sethduuid <PathOfNewVHD>
```
### Virtualbox use whole disk as virtual drive
```
VBoxManage.exe internalcommands createrawvmdk -filename "C:\Users\<user_name>\VirtualBox VMs\<VM_folder_name>\<file_name>.vmdk" -rawdisk \\.\PhysicalDrive#
```




# Apache
### Use absolute tmp folder
For Dev env, change php tmp folder to be absolute, edit file
```
sudo vi /etc/systemd/system/multi-user.target.wants/apache2.service
```
Edit
```
PrivateTmp=false
```



### Shrink vdi file
Run defrag in the guest (Windows)
nullify free space:
With Linux guest run this:
```
sudo dd if=/dev/zero of=/bigemptyfile bs=4096k
sudo rm -rf /bigemptyfile
```
With Windows guest, download SysinternalsSuite and run this:
```
sdelete -z
```
shutdown the guest VM
now run VBoxManage's compact command
```
VBoxManage.exe modifyhd <thedisk>.vdi --compact
```
Or
```
VBoxmanage.exe modifyhd <thedisk>.vdi --resize <sizeInMB>
```

# Git

### Add gitignore
```
git rm -r --cached .
git add .
git commit -m "fixed untracked files"
```
### Rebase
```
git rebase -i HEAD~5
```

# SSL (https) setting
### Self signing
```
openssl genrsa -des3 -passout pass:x -out servername.pass.key 2048
openssl rsa -passin pass:x -in servername.pass.key -out servername.key
rm servername.pass.key
openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:2048 -out servername.key
openssl req -new -key servername.key -out servername.csr
openssl x509 -req -days 365 -in servername.csr -signkey servername.key -out servername.crt
a2enmod ssl
```
In apache .conf file add
```
<VirtualHost *:443>

ServerName servername.com

ServerAlias www.servername.com

SSLEngine on

SSLCertificateFile /path/to/servername.crt

SSLCertificateKeyFile /path/to/servername.key

DocumentRoot /path/to/index/

</VirtualHost>
```
Run
```
sudo service apache2 restart
```

### By ecdsa
Step 0. Prepare /data/secure folder
```
mkdir -p /data/secure
cd /data/secure
```
Step 1. Generate a private key by
```
openssl ecparam -genkey -name secp256r1 | openssl ec -out ecdsa.<sitename>.key -aes128
```
Or without password
```
openssl ecparam -genkey -name secp256r1 > ecdsa.<sitename>.key
```
Step 2. Generate csr file
```
openssl req -new -sha256 -key ecdsa.<sitename>.key -out ecdsa.<sitename>.csr
```
Step 3.
```
openssl x509 -req -days 365 -in ecdsa.<sitename>.csr -signkey ecdsa.<sitename>.key -out ecdsa.<sitename>.crt
```
Step 4.
```
a2enmod ssl
```
Step 5. Add following into apache conf file
```
<VirtualHost *:443>
ServerName <sitename>
SSLEngine on
SSLCertificateFile /data/secure/ecdsa.<sitename>.crt
SSLCertificateKeyFile /data/secure/ecdsa.<sitename>.key
...

</VirtualHost>
```
Step 6. Restart apache
```
Sudo service apache2 restart
```

### Generate csr
```
openssl req -new -newkey rsa:2048 -nodes -keyout mydomain.key -out mydomain.csr
```
# 7-zip
Compression method
```
-m0=lzma2
```
Multicore
```
-mmt=<x>
```
# Android Firefox proxy

In the Firefox address bar browse to “about:config” with no quotes.  
In the page that loads search and modify the following values:  
```
network.proxy.proxy_over_tls = true  
network.proxy.socks = 127.0.0.1
network.proxy.socks_port = 3128
network.proxy.socks_remote_dns = true
network.proxy.socks_version = 5
network.proxy.type = 1
```
# Ubuntu 18.04 on VirtualBox

Dual screen : Enable the second screen from View menu

Share folder vbox, if not auto mount, use
```
sudo mount -t vboxsf D_DRIVE /media/sf_D_DRIVE/
```
# Magento
```
sudo apt install lamp-server^  
sudo mysql_secure_installation  
sudo apt install openssl php7.2-bcmath php7.2-curl php7.2-gd php7.2-intl php7.2-mbstring php7.2-mysql php7.2-xml php7.2-soap php7.2-zip imagemagick
sudo a2enmod rewrite
```
# Image processing
### Batch resize photo
[https://stackoverflow.com/questions/35180900/batch-resize-images-within-folders-and-keep-folders-structure](https://stackoverflow.com/questions/35180900/batch-resize-images-within-folders-and-keep-folders-structure)
```
find . -depth -type d \! -name '.' -exec bash -c 'cd $0 || exit; mkdir thumbs 2> /dev/null; shopt -s nullglob; mogrify -path thumbs -resize 1024x -format jpg *.jpg *.svg *.png *.tif' {} \;
```
# Email and postfix
To test email from command line on postfix 2.x
```
echo -e "Test email" | mail -a From:"admin@yesijoin.com" -s "Test subject" -t test@yesijoin.com
```
To test email from command line on postfix 3.x
```
echo -e "Test email" | mail -a From:"admin@yesijoin.com" -s "Test subject" test@yesijoin.com
```
or
```
date=”$(date)” ; echo -e "Test email ${date}" | mail -a From:"admin@yesijoin.com" -s "Test subject" test@yesijoin.com
```
# PHP
trace calling method
```php
\Dat\Utils\CmnUtil::debug(debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'],__FUNCTION__);
```
