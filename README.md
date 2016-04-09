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
* Deployment instructions

### Who do I talk to? ###

* Use github for contact.
