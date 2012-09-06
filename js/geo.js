/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
$(document).ready(function() {
	var timezone = jstz.determine();
	$.post(OC.filePath('calendar', 'ajax/timezone', 'set.php'), {tz: timezone.name()},
	function(data){
		if (data.status == 'success'){
			if(data.message == 'updated'){
				$('#notification').html(data.l10nmessage);
				$('#notification').slideDown();
				window.setTimeout(function(){$('#notification').slideUp();}, 5000);
			}else{
				console.log('timezone not changed since last visit');
			}
		}else{
			console.log('internal server error');
		}
	});
});