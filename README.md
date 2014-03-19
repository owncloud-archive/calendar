Calendar app
============

Maintainers:
------------
- [Georg Ehrke](https://github.com/georgehrke)
- [Thomas Tanghus](https://github.com/tanghus)
- [Bart Visscher](https://github.com/bartv2)

Remainder support:
------------------
- [Suresh Prakash](https://github.com/sureshprakash)

Note:
-----
\# command => Run the command in terminal as super user

Configuration steps:
--------------------
1. # gedit /etc/cron.d/owncloud
2. Add the line "*/15  *  *  *  * php -f /var/www/owncloud6/cron.php" without double quotes. Note: &lt;tab&gt; is in between the asterisks
3. Save the file and close text editor
4. Replace the calendar application with this one (Copy paste into /var/www/owncloud/apps directory)
5. Go to 'Starup Applications' from Dash Home (on Ubuntu)
6. Click 'Add' and then type some name to 'Name:' and paste "crontab /etc/cron.d/owncloud" into command. Click 'Ok' and quit the window
7. # chmod -R 777 /etc/cron.d/ownlcoud
8. # chmod -R 777 /var/www/owncloud/cron.php
9. Go to ownCloud Admin panel & select 'Cron'. (Default is AJAX)
10. Set your email in the Admin panel
11. Configure email settings in the /var/www/owncloud/config/config.php file. (Settings for gmail can be found [here](http://stackoverflow.com/questions/712392/send-email-using-gmail-smtp-server-from-php-page))
12. Restart your computer

If you keep your machine turned on &amp; your machine is connected to the internet, an email will be sent everyday remainding your tasks. If you want to change the time period of the email, you can edit it in the /var/www/owncloud/apps/calendar/lib/alarm.php file.
For example, if you want an email to be sent every hour (for demo purpose), you can change the line
$this->interval = 60 * 60 * 24;
to
$this->interval = 60 * 60;

Developer setup info:
---------------------
### Master branch:
Just clone this repo into your apps directory.

### Rework branch:

##### The rework branch is still in development. Do not use it in a productive environment.


The calendar rework depends on the appframework.
Get the latest version of the appframework:
```bash
git clone git://github.com/owncloud/appframework.git
```
Enable the appframework in the app settings of ownCloud.

Get the lastest version of the rework:
```bash
git clone git://github.com/owncloud/calendar.git
cd calendar
git checkout rework
```
