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
		calendars: {},
		editable: iseditable
	});
	
	//initialize the calendar
	$('#fullcalendar').oCCalendar({
		calendars: {},
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
		$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#header').height());
		$('#calendarsidebar').css('height', $(window).height() - $('#header').height() - 6);
	});
	$(window).trigger('resize');
});