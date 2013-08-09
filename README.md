![osef](http://i.imgur.com/hq6SAZf.png)


# Description
Please visit the [official website](http://www.elabftw.net) to see some screenshots and use a demo :)

# Installation
Thank you for choosing eLabFTW as a lab manager =)
Please report bugs on [github](https://github.com/NicolasCARPi/elabftw/issues).

eLabFTW was designed to be installed on a server, and people from the team would just log into it from their browser.

Don't have a server ? That's okay, you can use an old computer with 1 Go of RAM and an old CPU, it's more than enough. Just install a recent GNU/Linux distribution on it.

Don't have an old computer ? That's okay, you can install eLabFTW on a Raspberry Pi (you can buy one on [Radiospares](http://www.rs-components.com/index.html)). It's a 30€ computer on which you can install GNU/Linux and run a server in no time ! That's what we use in our lab.

But you can also install it locally and use it for yourself only. Here is how :

### Install locally on Mac OS
Please [follow the instructions on the wiki](https://github.com/NicolasCARPi/elabftw/wiki/installmac).
### Install locally on Windows
Please [follow the instructions on the wiki](https://github.com/NicolasCARPi/elabftw/wiki/installwin).
## Install on Unix-like OS (GNU/Linux, BSD, Solaris, etc…) (the recommended way !)
Please refer to your distribution's documentation to install :
* a webserver (Apache2 is recommended)
* php5
* mysql
* git

The quick way to do that on a Debian/Ubuntu setup :
~~~ sh 
$ sudo apt-get update
$ sudo apt-get upgrade
$ sudo apt-get install mysql-server mysql-client apache2 php5 php5-mysql libapache2-mod-php5 phpmyadmin git
~~~

Make sure to put a root password on your mysql installation :
~~~ sh
$ sudo /usr/bin/mysql_secure_installation
~~~


## Getting the files

### Connect to your server with SSH
~~~ sh
ssh user@12.34.56.78
~~~

### Cd to the public directory where you want eLabFTW to be installed
(can be /var/www, ~/public\_html, or any folder you'd like)
~~~ sh
$ cd /var/www
# make the directory writable by your user
$ sudo chown `whoami`:`whoami` .
~~~
Note the `.` at the end that means `current folder`.

### Get latest stable version via git :
~~~ sh
$ git clone https://github.com/NicolasCARPi/elabftw.git
~~~
(this will create a folder `elabftw`)

If you cannot connect, try exporting your proxy settings in your shell like so :
~~~ sh
$ export https_proxy="proxy.example.com:3128"
~~~
If you still cannot connect, tell git your proxy :
~~~ sh
$ git config --global http.proxy http://proxy.example.com:8080
~~~

If you can't install git or don't manage to get the files, you can [download a zip archive](https://github.com/NicolasCARPi/elabftw/archive/master.zip). But it's better to use git, it will allow easier updates.

### Create the uploads folders and fix the permissions
~~~ sh
$ cd elabftw
$ mkdir -p uploads/{tmp,export}
$ chmod -R 777 uploads
~~~

## SQL part
The second part is putting the database in place.
### Command line way (graphical way below)
~~~ sh
# first we connect to mysql
$ mysql -u root -p
# we create the database (note the ; at the end !)
mysql> create database elabftw;
# we create the user that will connect to the database.
mysql> grant usage on *.* to elabftw@localhost identified by 'YOUR_PASSWORD';
# we give all rights to this user on this database
mysql> grant all privileges on elabftw.* to elabftw@localhost;
mysql> exit
# now we import the database structure
$ mysql -u elabftw -p elabftw < install/elabftw.sql
~~~
You will be asked for the password you put after `identified by` three lines above.

*<- Ignore this (it's to fix a markdown syntax highlighting problem)


### Graphical way with phpmyadmin
You need to install the package `phpmyadmin` if it's not already done.

~~~sh
$ sudo apt-get install phpmyadmin
~~~

Now you will connect to the phpmyadmin panel from your browser on your computer. Type the IP address of the server followed by /phpmyadmin.

Example : http://12.34.56.78/phpmyadmin

Login with the root user on PhpMyAdmin panel (use the password you setup for mysql root user).
#### 1) create a user `elabftw` with all rights on the database `elabftw`

Now click the `Users` tab and click Add new user.

Do like this :

![phpmyadmin add user](http://i.imgur.com/kE1gtT1.png)


#### 2) import the database structure :
* On the menu on the left, select the newly created database `elabftw`
* Click the Import tab
* Select the file /path/to/elabftw/install/elabftw.sql
* Click Go

## Config file
Copy the file `admin/config.ini-EXAMPLE` to `admin/config.ini`.
~~~ sh
$ cp admin/config.ini-EXAMPLE admin/config.ini
~~~

Check that this file isn't served by your webserver (point to it in a browser).

If you see a 403 Error, all is good.

If you see the config file be sure to edit AllowOverride in your 
~~~ sh
<Directory "/var/www/elabftw">
~~~ 
in the file `/etc/apache2/conf/httpd.conf` and set it to All.

Reload the webserver :
~~~ sh
# on Debian/Ubuntu
$ sudo service apache2 reload 
# on Archlinux
$ sudo systemctl reload httpd.service
~~~
Now edit this file with nano, a simple text editor. (Use vim/emacs at will, of course !)
~~~ sh
$ nano admin/config.ini
~~~

## Final step
Finally, point your browser to the install folder (install/) and read onscreen instructions.

# Updating
To update, just cd in the `elabftw` folder and do :
~~~ sh
$ git pull
$ php update.php
~~~

# Backup
It is important to backup your files to somewhere else, in case anything bad happens.
Please refer to the [wiki](https://github.com/NicolasCARPi/elabftw/wiki/backup).

# Bonus stage
* It's a good idea to use a php optimizer to increase speed. I recommand installing XCache.
* You can show a TODOlist by pressing 't'.
* You can duplicate an experiment in one click.
* You can export in a .zip, a .pdf or a spreadsheet.
* You can share an experiment by just sending the URL of the page to someone else.

# Chemistry

If you want to use chemistry functions, you need to do a bit more stuff!

We need to install mychem: see http://mychem.sourceforge.net/
and openbabel:  http://openbabel.org/wiki/Main_Page

 svn revid 351 is known to work with MAMP 2.1.4 (mysql 5.5.29) and Debian Wheezy (mysql 5.5.31, on raspberry pi).

## Debian Wheezy
~~~ sh
$ sudo apt-get install gcc cmake libmysqlclient-dev libopenbabel-dev openbabel subversion build-essential
$ svn co https://mychem.svn.sourceforge.net/svnroot/mychem/mychem3@351 mychem
$ cd mychem
$ mkdir build
$ cd build
$ cmake ..
$ make
$ sudo make install
$ mysql -u root -p < ../src/mychemdb.sql
~~~

On a new Wheezy install, you may need to pass the following flags to cmake:
-DOPENBABEL2_INCLUDE_DIR=/usr/include/openbabel-2.0 -DOPENBABEL2_LIBRARIES=/usr/lib/libopenbabel.so

To check installation in mysql, either use the tests provided in the mychem package or:
~~~ mysql
mysql> SELECT MYCHEM_VERSION();
~~~

## MAMP 2.1.4 (Mac OS X)

This is a bit awkward because we need to download mysql again (in a redundant installation) because MAMP doesn't expose the necessary library. This is nasty and hacky! You'd probably be better off setting up your own MAMP stack following, say ( http://silicos-it.com/cookbook/configuring_osx_for_chemoinformatics/configuring_osx_for_chemoinformatics.html#mychem-cartridge-for-openbabel ) 

Make sure that you install the openbabel binary as directed on openbabel.org.

Get cmake from http://www.cmake.org/cmake/resources/software.html

Make sure you have a C/C++ compiler (ideally GCC), via macports or XCode, and subversion.

Get the relevant mysql package depending on your architecture (x86 or x86_64) from:

 * x86 (32 bit) http://downloads.skysql.com/archive/signature/p/mysql/f/mysql-5.5.25-osx10.6-x86.dmg/v/5.5.25
 * x86_64 (64 bit) http://downloads.skysql.com/archive/signature/p/mysql/f/mysql-5.5.25-osx10.6-x86_64.dmg/v/5.5.25

 and install mysql.

With the pre-requisites taken care of:
~~~ sh
$ svn co https://mychem.svn.sourceforge.net/svnroot/mychem/mychem3@351 mychem
$ cd mychem
$ mkdir build
$ cd build
$ cmake -DOPENBABEL2_INCLUDE_DIR=/usr/local/include/openbabel-2.0/ -DOPENBABEL2_LIBRARIES=/usr/local/lib/libopenbabel.dylib -DMYSQL_INCLUDE_DIR=/usr/local/mysql/include -DMYSQL_LIBRARIES=/usr/local/mysql/lib/libmysqlclient.dylib -DCMAKE_INSTALL_PREFIX=/Applications/MAMP/Library/lib/plugin/ ..
$ make
$ make install
$ cd /Applications/MAMP/Library/lib/plugin
$ ln -s lib/libmychem.dylib libmychem.so
$ otool -L libmychem.so  # this will show that libmychem.so does not specify the location of the libmysqlclient library. So we add it
$ install_name_tool -change libmysqlclient.18.dylib /usr/local/mysql-5.5.29-osx10.6-{x86 or x86_64}/lib/libmysqlclient.18.dylib libmychem.so
$ mysql -u root -p < ../src/mychemdb.sql
~~~

~Thank you for using eLabFTW :)

http://www.elabftw.net

\o/
