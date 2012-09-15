$(document).ready(function(){
	//initialize buttons in control-bar
	//week view
	$('#agendaWeek').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'agendaWeek');
	});
	//2 weeks view
	$('#basic2Weeks').click(function(){	
		$('#fullcalendar').fullCalendar('changeView', 'basic2Weeks');
	});
	//"month" view
	$('#basic4Weeks').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'basic4Weeks');
	});
	//list view
	$('#list').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'list');
	});
	$('#addEvent').click(function(){
		Calendar.Event.smartAdd();
	});
	//buttons in the top middle
	$('#backward').click(function(){
		$('#fullcalendar').fullCalendar('prev');
	});
	//TODO - improve datepicker implementation
	$('#date').datepicker({
		changeMonth: true,
		changeYear: true,
		showButtonPanel: true,
		beforeShow: function(input, inst) {
			var calendar_holder = $('#fullcalendar');
			var date = calendar_holder.fullCalendar('getDate');
			inst.input.datepicker('setDate', date);
			inst.input.val(calendar_holder.fullCalendar('getView').title);
			return inst;
		},
		onSelect: function(value, inst) {
			var date = inst.input.datepicker('getDate');
			$('#fullcalendar').fullCalendar('gotoDate', date);
		}
	});
	$('#forward').click(function(){
		$('#fullcalendar').fullCalendar('next');
	});
	//buttons in the top right corner
	$('#choosecalendar .generalsettings').on('click keydown', function() {
		OC.appSettings({appid:'calendar', loadJS:true, cache:false});
	});
	$('#showcalendarmanagement').on('click keydown', 
	function(){
		Calendar.UI.toggleCalendarManagement()
	});
	//some UI tweaks
	fillWindow($('#content'));
	$(window).resize(function() {
		Calendar.UI.resize();
	});
	//initialize Drag&Drop import
	//TODO - add initialization script for drag&drop import 
	//initialize scroll
	document.addEventListener('DOMMouseScroll', Calendar.UI.scrollCalendar, false);
	document.onmousewheel = Calendar.UI.scrollCalendar;
	//
	//initialize some arrays necessary for localization
	var dayNames = new Array		 (t('calendar', 'Sunday'),
									  t('calendar', 'Monday'),
									  t('calendar', 'Tuesday'),
									  t('calendar', 'Wednesday'),
									  t('calendar', 'Thursday'),
									  t('calendar', 'Friday'),
									  t('calendar', 'Saturday'));
	var dayNamesShort = new Array	 (t('calendar', 'Sun.'),
									  t('calendar', 'Mon.'),
									  t('calendar', 'Tue.'),
									  t('calendar', 'Wed.'),
									  t('calendar', 'Thu.'),
									  t('calendar', 'Fri.'),
									  t('calendar', 'Sat.'));
	var monthNames = new Array		 (t('calendar', 'January'),
									  t('calendar', 'February'),
									  t('calendar', 'March'),
									  t('calendar', 'April'),
									  t('calendar', 'May'),
									  t('calendar', 'June'),
									  t('calendar', 'July'),
									  t('calendar', 'August'),
									  t('calendar', 'September'),
									  t('calendar', 'October'),
									  t('calendar', 'November'),
									  t('calendar', 'December'));
	var monthNamesShort = new Array	 (t('calendar', 'Jan.'),
									  t('calendar', 'Feb.'),
									  t('calendar', 'Mar.'),
									  t('calendar', 'Apr.'),
									  t('calendar', 'May.'),
									  t('calendar', 'Jun.'),
									  t('calendar', 'Jul.'),
									  t('calendar', 'Aug.'),
									  t('calendar', 'Sep.'),
									  t('calendar', 'Oct.'),
									  t('calendar', 'Nov.'),
									  t('calendar', 'Dec.'));
	//initialize fullcalendar with all necessary parameters
	$('#fullcalendar').fullCalendar({
		//no default header
		header: false,
		//set first day with variable assigned in index.php
		firstDay: firstDay,
		//calendar is editable for sure
		editable: true,
		//set defaultview with variable assigned in index.php
		defaultView: defaultView,
		//set some formats
		timeFormat: {
			agenda: agendatime,
			'': defaulttime
			},
		columnFormat: {
			month: t('calendar', 'ddd'),    // Mon
			week: t('calendar', 'ddd M/d'), // Mon 9/7
			day: t('calendar', 'dddd M/d')  // Monday 9/7
			},
		titleFormat: {
			month: t('calendar', 'MMMM yyyy'),
					// September 2009
			week: t('calendar', "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}"),
					// Sep 7 - 13 2009
			day: t('calendar', 'dddd, MMM d, yyyy'),
					// Tuesday, Sep 8, 2009
			},
		axisFormat: defaulttime,
		columnFormat: {
		    week: 'ddd d. MMM'
		},
		//localization
		monthNames: monthNames,
		monthNamesShort: monthNamesShort,
		dayNames: dayNames,
		dayNamesShort: dayNamesShort,
		allDayText: t('calendar', 'All day'),
		viewDisplay: function(view) {
			//set new date informations
			$('#current_date').html($('<p>').html(view.title).text());
			//save the current view
			if (view.name != defaultView) {
				$.post(OC.filePath('calendar', 'ajax', 'changeview.php'), {v:view.name});
				defaultView = view.name;
			}
			//highlight the button of the current view
			Calendar.UI.setViewActive(view.name);
			//some UI tweaks
			if (view.name == 'agendaWeek') {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 0.1);
			} else {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 1.35);
			}
		},
		selectable: true,
		selectHelper: true,
		select: Calendar.Event.quickAdd,
		eventClick: Calendar.UI.editEvent,
		eventDrop: Calendar.UI.moveEvent,
		eventResize: Calendar.UI.resizeEvent,
		eventRender: function(event, element) {
			//render sth. for current selected event
			//fix display of event title
			element.find('.fc-event-title').html(element.find('.fc-event-title').text());
			//show tipsy - the fancy event info
			element.tipsy({
				className: 'tipsy-event',
				opacity: 0.9,
				gravity:$.fn.tipsy.autoBounds(150, 's'),
				fade:true,
				delayIn: 400,
				html:true,
				title:function() {
					return Calendar.Event.getEventPopupText(event);
				}
			});
		},
		loading: Calendar.UI.loading,
		eventSources: eventSources
	});
	//initialize category system
	//OCCategories.changed = Calendar.UI.categoriesChanged;
	//OCCategories.app = 'calendar';
	//UI tweak - fix height of fullcalendar
	$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#controls').height() - $('#header').height() - 15);
	$('#calendars').css('height', $(window).height() - $('#controls').height() - $('#header').height() - 20);
});