/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
//initialize our timezone object
var timezone = jstz.determine();
//send the timezone informations to the server
$.post(OC.filePath('calendar', 'ajax/timezone', 'set.php'), {tz: timezone.name()}, function(data){
	console.log('current timezone send to server');
	if (data.status == 'success'){
		Calendar.timezone = timezone.name();
		console.log('timezone updated');
	}else{
		console.warn('internal server error - timezone wasn\'t set properly');
	}
});