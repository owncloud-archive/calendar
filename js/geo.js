$(document).ready(function(){
	var timezone = jstz.determine();
	var timezoneName = timezone.name();

	$.post(OC.filePath('calendar', 'ajax/settings', 'guesstimezone.php'), {timezone: timezoneName},
		function(data){
			if (data.status == 'success' && typeof(data.message) != 'undefined'){
				$('#notification').html(data.message);
				$('#notification').slideDown();
				window.setTimeout(function(){$('#notification').slideUp();}, 5000);
				$('#fullcalendar').fullCalendar('refetchEvents');
			}
		});
});
