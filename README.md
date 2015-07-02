Calendar app
============
Fork from the owncloud/calendar with Alarm/Reminder/Notifications.

Install
=======
```
cd apps
git clone https://github.com/ElieSauveterre/calendar.git
```

In ownCloud:
`Enable the Calendar app.`

Database
========
If the `oc_clndr_alarms` does not exist after the install, you may manually create the table:

```
CREATE TABLE `oc_clndr_alarms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objid` int(10) unsigned NOT NULL DEFAULT '0',
  `value` varchar(255) COLLATE utf8_bin NOT NULL,
  `timetype` varchar(255) COLLATE utf8_bin NOT NULL,
  `senddate` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `type` varchar(255) COLLATE utf8_bin NOT NULL,
  `sent` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
```
