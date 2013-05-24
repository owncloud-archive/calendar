$(document).ready(function(){
	//initialize the button for date control
	//backward
	$('#backward').click(function(){
		$('#fullcalendar').fullCalendar('prev');
	});
	//move to today
	$('#today').click(function(){
		$('#fullcalendar').fullCalendar('today');
	});
	//forward
	$('#forward').click(function(){
		$('#fullcalendar').fullCalendar('next');
	});
	
	//initialize the button for views
	//agendaWeek = Week
	$('#agendaWeek').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'agendaWeek');
	});
	//basic2Weeks = 2 Weeks
	$('#basic2Weeks').click(function(){	
		$('#fullcalendar').fullCalendar('changeView', 'basic2Weeks');
	});
	//basic4Weeks = month
	$('#basic4Weeks').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'basic4Weeks');
	});
	//list = list
	$('#list').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'list');
	});
	
	//initialize datepicker
	$('#selecteddate').click(function(){
		$('#globaldatepicker').slideToggle();
	});
	
	$('#globaldatepicker').datepicker({
		changeMonth: true,
		changeYear: true,
		showOtherMonths: true,
		selectOtherMonths: true,
		onSelect: function(value, inst) {
			var date = inst.input.datepicker('getDate');
			$('#fullcalendar').fullCalendar('gotoDate', date);
		}
	});
	
	//keyboard navigation
	/* List of used keyCodes
	 * Arrow keys
	 * + 37 - left arrow
	 * + 38 - up arrow
	 * + 39 - right arrow
	 * + 40 - down arrow
	 * Vi Mode
	 * + 72 - h
	 * + 74 - j
	 * + 75 - k
	 * + 76 - l
	 * Gamer Mode
	 * + 87 - w
	 * + 65 - a
	 * + 83 - s
	 * + 68 - d
	 */
	$(document).keydown(function(e){
		//check if there is an open dialog
		if($('.dialog').length !== 0){
			return false;
		}
		
		//remove tipsy
		$('.tipsy').remove();
		
		//get current view
		var view = $('#fullcalendar').fullCalendar('getView');
		
		//up
		if (e.keyCode == 38 || e.keyCode == 75 || e.keyCode == 87) {
			if(view.name != 'agendaWeek'){
				$('#fullcalendar').fullCalendar('prev');
			}
		}
		
		//down
		if(e.keyCode == 40 || e.keyCode == 74 || e.keyCode == 83) {
			if(view.name != 'agendaWeek'){
				$('#fullcalendar').fullCalendar('next');
			}
		}
		
		//left
		if(e.keyCode == 37 || e.keyCode == 72 || e.keyCode == 65) {
			if(view.name == 'agendaWeek'){
				//$('#fullCalendar').fullCalendar('next');
			}
		}
		
		//right
		if(e.keyCode == 39 || e.keyCode == 76 || e.keyCode == 68) {
			if(view.name == 'agendaWeek'){
				//$('#fullCalendar').fullCalendar('next');
			}
		}
	});
	
	//some UI tweaks
	$(window).resize(function() {
		var height = $('#app-content').height();
		$('#fullcalendar').fullCalendar('option', 'contentHeight', height);
		$('#fullcalendar').height(height);
	});
	$(window).trigger('resize');
});