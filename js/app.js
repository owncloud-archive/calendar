$(document).ready(function(){
	//initialize the button for date control
	//backward
	$('button.backward').click(function(){
		$('#fullcalendar').oCCalendar('prev');
	});
	//move to today
	$('button.today').click(function(){
		$('#fullcalendar').oCCalendar('today');
	});
	//forward
	$('button.forward').click(function(){
		$('#fullcalendar').oCCalendar('next');
	});
	
	//initialize the button for views
	//agendaWeek = Week
	$('#agendaWeek').click(function(){
		$('#fullcalendar').oCCalendar('changeView', 'agendaWeek');
	});
	//basic2Weeks = 2 Weeks
	$('#basic2Weeks').click(function(){	
		$('#fullcalendar').oCCalendar('changeView', 'basic2Weeks');
	});
	//basic4Weeks = month
	$('#basic4Weeks').click(function(){
		$('#fullcalendar').oCCalendar('changeView', 'basic4Weeks');
	});
	//list = list
	$('#list').click(function(){
		$('#fullcalendar').oCCalendar('changeView', 'list');
	});
	
	//initialize the new fancy calendar list
	$('#calendarList').oCCalendarList({
		calendars: eventSources,
		editable: iseditable
	});
	
	//initialize the calendar
	$('#fullcalendar').oCCalendar({
		calendars: eventSources,
		scrollNavigation: true,
		keyboardNavigation: true,
		editable: iseditable,
		dragdropimport: iseditable,
		fullCalendar: {
			timeFormat: {
				agenda: agendatime,
				'': defaulttime
			},
			firstDay: 0,
			defaultView: defaultView
		}
	});
	
	//some UI tweaks
	$(window).resize(function() {
		fillWindow($('#content'));
		$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#header').height() - 15);
		$('#calendars').css('height', $(window).height() - $('#header').height() - 20);
	});
	$(window).trigger('resize');
});