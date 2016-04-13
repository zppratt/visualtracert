# README #

### What is this repository for? ###

* Quick summary
A basic client-server visual traceroute that uses either of two different methods to transcode ip addresses to latitude
and longitude.

* Version 0.0.1

### How do I get set up? ###

* Summary of set up
* Configuration
  - A working *ampp installation is required.
* Dependencies
* Database configuration
  - On Debian/Ubuntu:
    1. Execute: `sudo apt-get install php5-geoip php5-dev libgeoip-dev`
    2. Go to `/opt/*ampp/bin`
    3. Execute: `sudo ./pecl install geoip`
    4. Add the following to `/opt/*ampp/php.ini`: `extension=geoip.so`

  - Installation on OSX (Leopard) running MAMP

First you need MacPorts installed and operational:
http://www.macports.org

Use that to install libgeoip. From a terminal window, do:

    $ sudo port install libgeoip

This installs the library in /opt/local. Unfortunately the current version of the PECL extension doesn't know to look there, so you'll need to download and compile manually from http://pecl.php.net/package/geoip

Assuming it's in your download directory, extract it:

    $ cd ~/Downloads
    $ tar -xzf geoip-1.0.3.tgz
    $ cd geoip-1.0.3

Now edit the config.m4 file and change the SEARCH_PATH variable, as described in the following bug report:
http://pecl.php.net/bugs/bug.php?id=14795

Now you should be able to compile and install the extension as usual. Make sure you use the phpize from the MAMP package, not the one that ships with OSX:

    $ /App*/MAMP/bin/php5/bin/phpize
    $ ./configure
    $ make
    $ sudo make install

If phpize complains that it cannot find files under /Applications/MAMP/bin/php5/include, make sure you have the development version of MAMP installed (link towards the bottom of the MAMP download page).

You're nearly there! The extension will have installed in /usr/lib/php. You need to copy it into your MAMP extension directory:

    $ cd /App*/MAMP/bin/php5/lib/php/ext*/no-debug*/
    $ mv /usr/lib/php/ext*/no-debug*/geoip.so .

Now edit the php.ini file in /Applications/MAMP/conf/php5/ and add the following line:

     extension=geoip.so

Restart your web server and check phpinfo() - the geoip extension should now be listed.

Good luck!

* Deployment instructions

### Who do I talk to? ###

* Use github for contact.
