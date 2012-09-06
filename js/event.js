/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

Event={
	smartAdd:function(start, end, allday){
		start = Math.round(start.getTime()/1000);
		if (end){
			end = Math.round(end.getTime()/1000);
		}
		if($('#event').dialog('isOpen') == true){
			// TODO: save event
			$('#event').dialog('destroy').remove();
		}else{
			Calendar.UI.loading(true);
			$('#dialog_holder').load(OC.filePath('calendar', 'ajax/event', 'new.form.php'), {start:start, end:end, allday:allday?1:0}, Calendar.UI.startEventDialog);
		}
	},
}