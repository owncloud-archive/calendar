$(document).ready(function(){
	Calendar.UI.Scroll.init();
	$('#fullcalendar').fullCalendar({
		header: false,
		firstDay: firstDay,
		editable: true,
		defaultView: defaultView,
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
		monthNames: new Array(t('calendar', 'January'), t('calendar', 'February'), t('calendar', 'March'), t('calendar', 'April'), t('calendar', 'May'), t('calendar', 'June'), t('calendar', 'July'), t('calendar', 'August'), t('calendar', 'September'), t('calendar', 'October'), t('calendar', 'November'), t('calendar', 'December')),
		monthNamesShort: new Array(t('calendar', 'Jan.'), t('calendar', 'Feb.'), t('calendar', 'Mar.'), t('calendar', 'Apr.'), t('calendar', 'May.'), t('calendar', 'Jun.'), t('calendar', 'Jul.'), t('calendar', 'Aug.'), t('calendar', 'Sep.'), t('calendar', 'Oct.'), t('calendar', 'Nov.'), t('calendar', 'Dec.')),
		dayNames: new Array(t('calendar', 'Sunday'), t('calendar', 'Monday'), t('calendar', 'Tuesday'), t('calendar', 'Wednesday'), t('calendar', 'Thursday'), t('calendar', 'Friday'), t('calendar', 'Saturday')),
		dayNamesShort: new Array(t('calendar', 'Sun.'), t('calendar', 'Mon.'), t('calendar', 'Tue.'), t('calendar', 'Wed.'), t('calendar', 'Thu.'), t('calendar', 'Fri.'), t('calendar', 'Sat.')),
		allDayText: t('calendar', 'All day'),
		viewDisplay: function(view) {
			$('#date').val($('<p>').html(view.title).text());
			if (view.name != defaultView) {
				$.post(OC.filePath('calendar', 'ajax', 'changeview.php'), {v:view.name});
				defaultView = view.name;
			}
			Calendar.UI.setViewActive(view.name);
			if (view.name == 'agendaWeek') {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 0.1);
			}
			else {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 1.35);
			}
		},
		columnFormat: {
		    week: 'ddd d. MMM'
		},
		selectable: true,
		selectHelper: true,
		select: Event.smartAdd,
		eventClick: Calendar.UI.editEvent,
		eventDrop: Calendar.UI.moveEvent,
		eventResize: Calendar.UI.resizeEvent,
		eventRender: function(event, element) {
			element.find('.fc-event-title').html(element.find('.fc-event-title').text());
			element.tipsy({
				className: 'tipsy-event',
				opacity: 0.9,
				gravity:$.fn.tipsy.autoBounds(150, 's'),
				fade:true,
				delayIn: 400,
				html:true,
				title:function() {
					return Calendar.UI.getEventPopupText(event);
				}
			});
		},
		loading: Calendar.UI.loading,
		eventSources: eventSources
	});
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
	fillWindow($('#content'));
	OCCategories.changed = Calendar.UI.categoriesChanged;
	OCCategories.app = 'calendar';
	$('#week').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'agendaWeek');
	});
	$('#month').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'month');
	});
	$('#list').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'list');
	});
	$('#backward').click(function(){
		$('#fullcalendar').fullCalendar('prev');
	});
	$('#forward').click(function(){
		$('#fullcalendar').fullCalendar('next');
	});
	Calendar.Import.init();
	$('#choosecalendar .generalsettings').on('click keydown', function() {
		OC.appSettings({appid:'calendar', loadJS:true, cache:false});
	});
	$('#choosecalendar .calendarsettings').on('click keydown', function() {
		OC.appSettings({appid:'calendar', loadJS:true, cache:false, scriptName:'calendar.php'});
	});
	$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#controls').height() - $('#header').height() - 15);
	$(window).resize(function() {
		Calendar.UI.resize();
	});
});

/*
				
								<script type='text/javascript'>
				var agendatime = '<?php echo ((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt'); ?>{ - <?php echo ((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt'); ?>}';
				var defaulttime = '<?php echo ((int) OCP\Config::getUserValue(OCP\USER::getUser(), 'calendar', 'timeformat', '24') == 24 ? 'HH:mm' : 'hh:mm tt'); ?>';
				var allDayText = '<?php echo addslashes($l->t('All day')) ?>';
				var newcalendar = '<?php echo addslashes($l->t('New Calendar')) ?>';
				var missing_field = '<?php echo addslashes($l->t('Missing fields')) ?>';
				var missing_field_title = '<?php echo addslashes($l->t('Title')) ?>';
				var missing_field_calendar = '<?php echo addslashes($l->t('Calendar')) ?>';
				var missing_field_fromdate = '<?php echo addslashes($l->t('From Date')) ?>';
				var missing_field_fromtime = '<?php echo addslashes($l->t('From Time')) ?>';
				var missing_field_todate = '<?php echo addslashes($l->t('To Date')) ?>';
				var missing_field_totime = '<?php echo addslashes($l->t('To Time')) ?>';
				var missing_field_startsbeforeends = '<?php echo addslashes($l->t('The event ends before it starts')) ?>';
				var missing_field_dberror = '<?php echo addslashes($l->t('There was a database fail')) ?>';
				var totalurl = '<?php echo OCP\Util::linkToRemote('caldav'); ?>calendars';
				$(document).ready(function() {
				<?php
				if(array_key_exists('showevent', $_)){
					$data = OC_Calendar_App::getEventObject($_['showevent']);
					$date = substr($data['startdate'], 0, 10);
					list($year, $month, $day) = explode('-', $date);
					echo '$(\'#calendar_holder\').fullCalendar(\'gotoDate\', ' . $year . ', ' . --$month . ', ' . $day . ');';
					echo '$(\'#dialog_holder\').load(OC.filePath(\'calendar\', \'ajax\', \'editeventform.php\') + \'?id=\' +  ' . $_['showevent'] . ' , Calendar.UI.startEventDialog);';
				}
				?>
				});
				</script>*/