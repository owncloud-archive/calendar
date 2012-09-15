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
	if (data.status == 'success'){
		console.log('timezone updated');
	}else{
		console.log('internal server error');
	}
});