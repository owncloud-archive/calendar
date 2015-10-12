/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

Calendar={
	Util:{
		sendmail: function(eventId, location, description, dtstart, dtend){
			Calendar.UI.loading(true);
			$.post(
			OC.filePath('calendar','ajax/event','sendmail.php'),
			{
				eventId:eventId,
				location:location,
				description:description,
				dtstart:dtstart,
				dtend:dtend
			},
			function(result){
				if(result.status !== 'success'){
					OC.dialogs.alert(result.data.message, t('calendar', 'Error sending mail'));
				}
				Calendar.UI.loading(false);
			}
		);
		},
		dateTimeToTimestamp:function(dateString, timeString){
			dateTuple = dateString.split('-');
			timeTuple = timeString.split(':');
			
			var day, month, year, minute, hour;
			day = parseInt(dateTuple[0], 10);
			month = parseInt(dateTuple[1], 10);
			year = parseInt(dateTuple[2], 10);
			hour = parseInt(timeTuple[0], 10);
			minute = parseInt(timeTuple[1], 10);
			
			var date = new Date(year, month-1, day, hour, minute);
			
			return parseInt(date.getTime(), 10);
		},
		formatDate:function(year, month, day){
			if(day < 10){
				day = '0' + day;
			}
			if(month < 10){
				month = '0' + month;
			}
			return day + '-' + month + '-' + year;
		},
		formatTime:function(hour, minute){
			if(hour < 10){
				hour = '0' + hour;
			}
			if(minute < 10){
				minute = '0' + minute;
			}
			return hour + ':' + minute;
		}, 
		adjustDate:function(){
			var fromTime = $('#fromtime').val();
			var fromDate = $('#from').val();
			var fromTimestamp = Calendar.Util.dateTimeToTimestamp(fromDate, fromTime);

			var toTime = $('#totime').val();
			var toDate = $('#to').val();
			var toTimestamp = Calendar.Util.dateTimeToTimestamp(toDate, toTime);

			if(fromTimestamp >= toTimestamp){
				fromTimestamp += 30*60*1000;
				
				var date = new Date(fromTimestamp);
				movedTime = Calendar.Util.formatTime(date.getHours(), date.getMinutes());
				movedDate = Calendar.Util.formatDate(date.getFullYear(),
						date.getMonth()+1, date.getDate());

				$('#to').val(movedDate);
				$('#totime').val(movedTime);
			}
		},
		getDayOfWeek:function(iDay){
			var weekArray=['sun','mon','tue','wed','thu','fri','sat'];
			return weekArray[iDay];
		},
		setTimeline : function() {
			var curTime = new Date();
			if (curTime.getHours() == 0 && curTime.getMinutes() <= 5)// Because I am calling this function every 5 minutes
			{
				// the day has changed
				var todayElem = $(".fc-today");
				todayElem.removeClass("fc-today");
				todayElem.removeClass("fc-state-highlight");

				todayElem.next().addClass("fc-today");
				todayElem.next().addClass("fc-state-highlight");
			}

			var parentDiv = $(".fc-agenda-slots:visible").parent();
			var timeline = parentDiv.children(".timeline");
			if (timeline.length == 0) {//if timeline isn't there, add it
				timeline = $("<hr>").addClass("timeline");
				parentDiv.prepend(timeline);
			}

			var curCalView = $('#fullcalendar').fullCalendar("getView");
			if (curCalView.visStart < curTime && curCalView.visEnd > curTime) {
				timeline.show();
			} else {
				timeline.hide();
			}

			var curSeconds = (curTime.getHours() * 60 * 60) + (curTime.getMinutes() * 60) + curTime.getSeconds();
			var percentOfDay = curSeconds / 86400;
			//24 * 60 * 60 = 86400, # of seconds in a day
			var topLoc = Math.floor(parentDiv.height() * percentOfDay);
			var appNavigationWidth = ($(window).width() > 768) ? $('#app-navigation').width() : 0;
			timeline.css({'left':($('.fc-today').offset().left-appNavigationWidth),'width': $('.fc-today').width(),'top':topLoc + 'px'});
		},
		openLocationMap:function(){
			var address = $('#event-location').val();
			address = encodeURIComponent(address);
			var newWindow = window.open('http://open.mapquest.com/?q='+address, '_blank');
			newWindow.focus();
		}
	},
	UI:{
		/*
		 * checking if the calendar is link-shared and hence not editable
		 */
		isLinkShared: function() {
			// simple enough, eh?
			return ( $('#linksharedinfo').length > 0 )
		},
		loading: function(isLoading){
			if (isLoading){
				$('#loading').show();
			}else{
				$('#loading').hide();
			}
		},
		startEventDialog:function(){
			Calendar.UI.loading(false);
			$('#fullcalendar').fullCalendar('unselect');
			Calendar.UI.lockTime();
			$( "#from" ).datepicker({
				minDate: null,
				maxDate: null,
				dateFormat : 'dd-mm-yy',
				onSelect: function(){ Calendar.Util.adjustDate(); }
			});
			$( "#to" ).datepicker({
				minDate: null,
				maxDate: null,
				dateFormat : 'dd-mm-yy'
			});
			$('#fromtime').timepicker({
				showPeriodLabels: false,
				onSelect: function(){ Calendar.Util.adjustDate(); }
			});
			$('#totime').timepicker({
				showPeriodLabels: false
			});
			$('#category').multiple_autocomplete({source: categories});
			Calendar.UI.repeat('init');
			$('#end').change(function(){
				Calendar.UI.repeat('end');
			});
			$('#repeat').change(function(){
				Calendar.UI.repeat('repeat');
			});
			$('#advanced_year').change(function(){
				Calendar.UI.repeat('year');
			});
			$('#advanced_month').change(function(){
				Calendar.UI.repeat('month');
			});
			$('#event-title').bind('keydown', function(event){
				if (event.which == 13){
					$('#event_form #submitNewEvent').click();
				}
			});
			$( "#event" ).tabs({ selected: 0});
			$('#event').dialog({
				width : 500,
				height: 600,
				resizable: false,
				draggable: false,
				close : function(event, ui) {
					$(this).dialog('destroy').remove();
				}
			});
			Calendar.UI.Share.init();
			$('#sendemailbutton').click(function() {
				Calendar.Util.sendmail($(this).attr('data-eventid'), $(this).attr('data-location'), $(this).attr('data-description'), $(this).attr('data-dtstart'), $(this).attr('data-dtend'));
			});
			// Focus the title, and reset the text value so that it isn't selected.
			var val = $('#event-title').val();
			$('#event-title').focus().val('').val(val);
		},
		newEvent:function(start, end, allday){

			// nothing to do for link-shared public calendars
			if (Calendar.UI.isLinkShared()) return false;

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
		editEvent:function(calEvent, jsEvent, view){
			if (calEvent.editable == false || calEvent.source.editable == false) {
				return;
			}
			var id = calEvent.id;
			if($('#event').dialog('isOpen') == true){
				// TODO: save event
				$('#event').dialog('destroy').remove();
			}else{
				Calendar.UI.loading(true);
				$('#dialog_holder').load(OC.filePath('calendar', 'ajax/event', 'edit.form.php'), {id: id}, Calendar.UI.startEventDialog);
			}
		},
		submitDeleteEventForm:function(url){
			var id = $('input[name="id"]').val();
			$('#errorbox').empty();
			Calendar.UI.loading(true);
			$.post(url, {id:id}, function(data){
					Calendar.UI.loading(false);
					if(data.status == 'success'){
						$('#fullcalendar').fullCalendar('removeEvents', $('#event_form input[name=id]').val());
						$('#event').dialog('destroy').remove();
					} else {
						$('#errorbox').html(t('calendar', 'Deletion failed'));
					}

			}, "json");
		},
		validateEventForm:function(url){
			var post = $( "#event_form" ).serialize();
			$("#errorbox").empty();
			Calendar.UI.loading(true);
			$.post(url, post,
				function(data){
					Calendar.UI.loading(false);
					if(data.status == "error"){
						var output = missing_field + ": <br />";
						if(data.title == "true"){
							output = output + missing_field_title + "<br />";
						}
						if(data.cal == "true"){
							output = output + missing_field_calendar + "<br />";
						}
						if(data.from == "true"){
							output = output + missing_field_fromdate + "<br />";
						}
						if(data.fromtime == "true"){
							output = output + missing_field_fromtime + "<br />";
						}
						if(data.interval == "true"){
							output = output + missing_field_interval + "<br />";
						}
						if(data.to == "true"){
							output = output + missing_field_todate + "<br />";
						}
						if(data.totime == "true"){
							output = output + missing_field_totime + "<br />";
						}
						if(data.endbeforestart == "true"){
							output = output + missing_field_startsbeforeends + "!<br/>";
						}
						if(data.dberror == "true"){
							output = "There was a database fail!";
						}
						$("#errorbox").html(output);
					} else
					if(data.status == 'success'){
						$('#event').dialog('destroy').remove();
						$('#fullcalendar').fullCalendar('refetchEvents');
					}
				},"json");
		},
		moveEvent:function(event, dayDelta, minuteDelta, allDay, revertFunc){
			// nothing to do for link-shared public calendars
			if (Calendar.UI.isLinkShared()) return false;
			
			if($('#event').length != 0) {
				revertFunc();
				return;
			}

			Calendar.UI.loading(true);
			$.post(OC.filePath('calendar', 'ajax/event', 'move.php'), { id: event.id, dayDelta: dayDelta, minuteDelta: minuteDelta, allDay: allDay?1:0, lastmodified: event.lastmodified},
			function(data) {
				Calendar.UI.loading(false);
				if (data.status == 'success'){
					event.lastmodified = data.lastmodified;
					console.log("Event moved successfully");
				}else{
					revertFunc();
					$('#fullcalendar').fullCalendar('refetchEvents');
				}
			});
		},
		resizeEvent:function(event, dayDelta, minuteDelta, revertFunc){

			// nothing to do for link-shared public calendars
			if (Calendar.UI.isLinkShared()) return false;

			Calendar.UI.loading(true);
			$.post(OC.filePath('calendar', 'ajax/event', 'resize.php'), { id: event.id, dayDelta: dayDelta, minuteDelta: minuteDelta, lastmodified: event.lastmodified},
			function(data) {
				Calendar.UI.loading(false);
				if (data.status == 'success'){
					event.lastmodified = data.lastmodified;
					console.log("Event resized successfully");
				}else{
					revertFunc();
					$('#fullcalendar').fullCalendar('refetchEvents');
				}
			});
		},
		showadvancedoptions:function(){
			$("#advanced_options").slideDown('slow');
			$("#advanced_options_button").css("display", "none");
		},
		showadvancedoptionsforrepeating:function(){
			if($("#advanced_options_repeating").is(":hidden")){
				$('#advanced_options_repeating').slideDown('slow');
			}else{
				$('#advanced_options_repeating').slideUp('slow');
			}
		},
		getEventPopupText:function(event){
			if (event.allDay){
				var timespan = $.fullCalendar.formatDates(event.start, event.end, 'ddd d MMMM[ yyyy]{ - [ddd d] MMMM yyyy}', {monthNamesShort: monthNamesShort, monthNames: monthNames, dayNames: dayNames, dayNamesShort: dayNamesShort}); //t('calendar', "ddd d MMMM[ yyyy]{ - [ddd d] MMMM yyyy}")
			}else{
				var timespan = $.fullCalendar.formatDates(event.start, event.end, 'ddd d MMMM[ yyyy] ' + defaulttime + '{ - [ ddd d MMMM yyyy]' + defaulttime + '}', {monthNamesShort: monthNamesShort, monthNames: monthNames, dayNames: dayNames, dayNamesShort: dayNamesShort}); //t('calendar', "ddd d MMMM[ yyyy] HH:mm{ - [ ddd d MMMM yyyy] HH:mm}")
				// Tue 18 October 2011 08:00 - 16:00
			}
			var html =
				'<div class="summary">' + escapeHTML(event.title) + '</div>' +
				'<div class="timespan">' + timespan + '</div>';
			if (event.description){
				html += '<div class="description">' + escapeHTML(event.description) + '</div>';
			}
			return html;
		},
		lockTime:function(){
			if($('#allday_checkbox').is(':checked')) {
				$("#fromtime").attr('disabled', true)
					.addClass('disabled');
				$("#totime").attr('disabled', true)
					.addClass('disabled');
			} else {
				$("#fromtime").attr('disabled', false)
					.removeClass('disabled');
				$("#totime").attr('disabled', false)
					.removeClass('disabled');
			}
		},
		showCalDAVUrl:function(username, calname){
			$('#caldav_url').val(totalurl + '/' + encodeURIComponent(username) + '/' + calname);
			$('#caldav_url').show();
			$("#caldav_url_close").show();
		},
		repeat:function(task){
			if(task=='init'){
				
				var byWeekNoTitle = $('#advanced_byweekno').attr('title');
				$('#byweekno').multiselect({
					header: false,
					noneSelectedText: byWeekNoTitle,
					selectedList: 2,
					minWidth : 60
				});
				
				var weeklyoptionsTitle = $('#weeklyoptions').attr('title');
				$('#weeklyoptions').multiselect({
					header: false,
					noneSelectedText: weeklyoptionsTitle,
					selectedList: 2,
					minWidth : 110
				});
				$('input[name="bydate"]').datepicker({
					dateFormat : 'dd-mm-yy'
				});
				
				var byyeardayTitle = $('#byyearday').attr('title');
				$('#byyearday').multiselect({
					header: false,
					noneSelectedText: byyeardayTitle,
					selectedList: 2,
					minWidth : 60
				});
				
				var bymonthTitle = $('#bymonth').attr('title');
				$('#bymonth').multiselect({
					header: false,
					noneSelectedText: bymonthTitle,
					selectedList: 2,
					minWidth : 110
				});
				
				var bymonthdayTitle = $('#bymonthday').attr('title');
				$('#bymonthday').multiselect({
					header: false,
					noneSelectedText: bymonthdayTitle,
					selectedList: 2,
					minWidth : 60
				});
				Calendar.UI.repeat('end');
				Calendar.UI.repeat('month');
				Calendar.UI.repeat('year');
				Calendar.UI.repeat('repeat');
			}
			if(task == 'end'){
				$('#byoccurrences').css('display', 'none');
				$('#bydate').css('display', 'none');
				if($('#end option:selected').val() == 'count'){
					$('#byoccurrences').css('display', 'block');
				}
				if($('#end option:selected').val() == 'date'){
					$('#bydate').css('display', 'block');
				}
			}
			if(task == 'repeat'){
				$('#advanced_month').css('display', 'none');
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_weekofmonth').css('display', 'none');
				$('#advanced_byyearday').css('display', 'none');
				$('#advanced_bymonth').css('display', 'none');
				$('#advanced_byweekno').css('display', 'none');
				$('#advanced_year').css('display', 'none');
				$('#advanced_bymonthday').css('display', 'none');
				if($('#repeat option:selected').val() == 'monthly'){
					$('#advanced_month').css('display', 'block');
					Calendar.UI.repeat('month');
				}
				if($('#repeat option:selected').val() == 'weekly'){
					$('#advanced_weekday').css('display', 'block');
				}
				if($('#repeat option:selected').val() == 'yearly'){
					$('#advanced_year').css('display', 'block');
					Calendar.UI.repeat('year');
				}
				if($('#repeat option:selected').val() == 'doesnotrepeat'){
					$('#advanced_options_repeating').slideUp('slow');
				}
			}
			if(task == 'month'){
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_weekofmonth').css('display', 'none');
				if($('#advanced_month_select option:selected').val() == 'weekday'){
					$('#advanced_weekday').css('display', 'block');
					$('#advanced_weekofmonth').css('display', 'block');
				}
			}
			if(task == 'year'){
				$('#advanced_weekday').css('display', 'none');
				$('#advanced_byyearday').css('display', 'none');
				$('#advanced_bymonth').css('display', 'none');
				$('#advanced_byweekno').css('display', 'none');
				$('#advanced_bymonthday').css('display', 'none');
				if($('#advanced_year_select option:selected').val() == 'byyearday'){
					//$('#advanced_byyearday').css('display', 'block');
				}
				if($('#advanced_year_select option:selected').val() == 'byweekno'){
					$('#advanced_byweekno').css('display', 'block');
				}
				if($('#advanced_year_select option:selected').val() == 'bydaymonth'){
					$('#advanced_bymonth').css('display', 'block');
					$('#advanced_bymonthday').css('display', 'block');
					$('#advanced_weekday').css('display', 'block');
				}
			}

		},
		setViewActive: function(view){
			$('#view input[type="button"]').removeClass('active');
			var id;
			switch (view) {
				case 'agendaWeek':
					id = 'oneweekview_radio';
					break;
				case 'month':
					id = 'onemonthview_radio';
					break;
				case 'agendaDay':
					id = 'onedayview_radio';
					break;
			}
			$('#'+id).addClass('active');
		},
		categoriesChanged:function(newcategories){
			categories = $.map(newcategories, function(v) {return v.name;});
			console.log('Calendar categories changed to: ' + categories);
			$('#category').multiple_autocomplete('option', 'source', categories);
		},
		lastView:null,
		isToday:true,
		timerHolder:null,
		timerInterval:300000, // 300000 = 5*60*1000ms = 5 min
		changeView:function(view){
			switch (view){
				case 'today':
				case 'prev':
				case 'next':
					$('#fullcalendar').fullCalendar(view);
					if (view=='today' && Calendar.UI.isToday) {
						Calendar.UI.changeView('refresh')
					}
					if (view=='today'){
						Calendar.UI.isToday = true;
					}else{
						Calendar.UI.isToday = false;
					}
					break;

				case 'agendaDay':
				case 'agendaWeek':
				case 'month':
					$('#fullcalendar').fullCalendar('changeView', view);
					if (Calendar.UI.lastView == view) {
						Calendar.UI.changeView('refresh')
					}
					Calendar.UI.lastView = view;
					break;

				case 'refresh':
					// refetch the events.
					$('#fullcalendar').fullCalendar('refetchEvents');
				case 'auto_refresh':
					// reset the timer not to refetch before new 5 min.
					if (Calendar.UI.timerHolder){
						window.clearTimeout(Calendar.UI.timerHolder)
					}
					Calendar.UI.timerHolder = window.setTimeout( function(){Calendar.UI.changeView('refresh')}, Calendar.UI.timerInterval);
					break;

				default:
					console.error('unsupported change view to:' + view);
			}
		},
		Calendar:{
			overview:function(){
				if($('#choosecalendar_dialog').dialog('isOpen') == true){
					$('#choosecalendar_dialog').dialog('moveToTop');
				}else{
					Calendar.UI.loading(true);
					$('#dialog_holder').load(OC.filePath('calendar', 'ajax/calendar', 'overview.php'), function(){
						$('#choosecalendar_dialog').dialog({
							width : 600,
							height: 400,
							close : function(event, ui) {
								$(this).dialog('destroy').remove();
							}
						});
						Calendar.UI.loading(false);
					});
				}
			},
			activation:function(checkbox, calendarid)
			{
				if(checkbox.checked?1:0) {
					$('#checkbox_'+calendarid).removeClass('unchecked');
				}
				else {
					$('#checkbox_'+calendarid).addClass('unchecked');
				}
				Calendar.UI.loading(true);
				$.post(OC.filePath('calendar', 'ajax/calendar', 'activation.php'), { calendarid: calendarid, active: checkbox.checked?1:0 },
					function(data) {
					Calendar.UI.loading(false);
					if (data.status == 'success'){
						checkbox.checked = data.active == 1;
						if (data.active == 1){
							$('#fullcalendar').fullCalendar('addEventSource', data.eventSource);
						}else{
							$('#fullcalendar').fullCalendar('removeEventSource', data.eventSource.url);
						}
					}
					});
			},
			sharedEventsActivation:function(checkbox)
			{
				if (checkbox.checked){
					$('#fullcalendar').fullCalendar('addEventSource', sharedEventSource);
				}else{
					$('#fullcalendar').fullCalendar('removeEventSource', sharedEventSource.url);
				}
			},
			newCalendar:function(object){
				var div = $('<div />')
					.load(OC.filePath('calendar', 'ajax/calendar', 'new.form.php'),
						function(){
							Calendar.UI.Calendar.colorPicker(this);
							$('#displayname_new').focus();
						});
				
				var bodyListener = function(e) {
					if($('#newcalendar_dialog').find($(e.target)).length === 0) {
						$('#newcalendar_dialog').parent().remove();
						$("#newCalendar").css('display', '');
						$('body').unbind('click', bodyListener);
					}
				};
				$('body').bind('click', bodyListener);
				
				$('#newCalendar').after(div);
				$('#newCalendar').css('display', 'none');
			},
			edit:function(object, calendarid){
				var li = $(document.createElement('li'))
					.load(OC.filePath('calendar', 'ajax/calendar', 'edit.form.php'), {calendarid: calendarid},
						function(){Calendar.UI.Calendar.colorPicker(this)});
				
				var bodyListener = function(e) {
					if($('#editcalendar_dialog').find($(e.target)).length === 0) {
						$(object).closest('li').before(li).show();
						$('#editcalendar_dialog').parent().remove();
						$('body').unbind('click', bodyListener);
					}
				};
				$('body').bind('click', bodyListener);
				
				$(object).closest('li').after(li).hide();
			},
			deleteCalendar:function(calid){
				var check = confirm(t('calendar', 'Do you really want to delete this calendar?'));
				if(check == false){
					return false;
				}else{
					$.post(OC.filePath('calendar', 'ajax/calendar', 'delete.php'), { calendarid: calid},
						function(data) {
						if (data.status == 'success'){
							var url = 'ajax/events.php?calendar_id='+calid;
							$('#fullcalendar').fullCalendar('removeEventSource', url);
							$('#navigation-list li[data-id="'+calid+'"]').fadeOut(400,function(){
								$('#navigation-list li[data-id="'+calid+'"]').remove();
							});
							$('#fullcalendar').fullCalendar('refetchEvents');
						}
						});
				}
			},
			submit:function(button, calendarid){
				var displayname = $.trim($("#displayname_"+calendarid).val());
				var active = $("#active_"+calendarid).attr("checked") ? 1 : 0;
				
				var description = $("#description_"+calendarid).val();
				var calendarcolor = $("#calendarcolor_"+calendarid).val();
				if(displayname == ''){
					$("#displayname_"+calendarid).css('background-color', '#FF2626');
					$("#displayname_"+calendarid).focus(function(){
						$("#displayname_"+calendarid).css('background-color', '#F8F8F8');
					});
				}

				var url;
				if (calendarid == 'new'){
					url = OC.filePath('calendar', 'ajax/calendar', 'new.php');
				}else{
					url = OC.filePath('calendar', 'ajax/calendar', 'update.php');
				}
				$.post(url, { id: calendarid, name: displayname, active: active, description: description, color: calendarcolor },
					function(data){
						if(data.status == 'success'){
							if(active) {
								$('#fullcalendar').fullCalendar('removeEventSource', data.eventSource.url);
								$('#fullcalendar').fullCalendar('addEventSource', data.eventSource);
							}
							if (calendarid == 'new'){
								$('#newcalendar_dialog').parent().remove();
								$("#newCalendar").css('display', '');
								var li = $(document.createElement('li')).append(data.page);
								li.attr('data-id', data.calendarid);
								$("#navigation-list").append(li);
								$('#caldav_url_entry').appendTo("#navigation-list");
							}
							else {
								$('#editcalendar_dialog').parent().remove();
								$('#navigation-list li[data-id="'+calendarid+'"]').html(data.page).show();
							}
						}else{
							$("#displayname_"+calendarid).css('background-color', '#FF2626');
							$("#displayname_"+calendarid).focus(function(){
								$("#displayname_"+calendarid).css('background-color', '#F8F8F8');
							});
						}
					}, 'json');
			},
			cancel:function(button, calendarid){
				$('#newcalendar_dialog').parent().remove();
				$("#newCalendar").css('display', '');
			},
			colorPicker:function(container){
				// based on jquery-colorpicker at jquery.webspirited.com
				var obj = $('.colorpicker', container);
				var picker = $('<div class="calendar-colorpicker"></div>');
				//build an array of colors
				var colors = {};
				$(obj).children('option').each(function(i, elm) {
					colors[i] = {};
					colors[i].color = $(elm).val();
					colors[i].label = $(elm).text();
				});
				for (var i in colors) {
					picker.append('<span class="calendar-colorpicker-color ' + (colors[i].color == $(obj).children(":selected").val() ? ' active' : '') + '" rel="' + colors[i].label + '" style="background-color: ' + colors[i].color + ';"></span>');
				}
				picker.delegate(".calendar-colorpicker-color", "click", function() {
					$(obj).val($(this).attr('rel'));
					$(obj).change();
					picker.children('.calendar-colorpicker-color.active').removeClass('active');
					$(this).addClass('active');
				});
				$(obj).after(picker);
				$(obj).css({
					position: 'absolute',
					left: -10000
				});
			}
		},
		Share:{
			init:function(){
				if(typeof OC.Share !== typeof undefined){
					//var itemShares = [OC.Share.SHARE_TYPE_USER, OC.Share.SHARE_TYPE_GROUP]; // huh? what is that supposed to do?..
					$('.internal-share .share-with.ui-autocomplete-input').live('keydown.autocomplete', function(){
						// we need itemshares
						var itemShares = [];
						$(this)
						  .siblings('.shared-with-list')
						    .children('li:not(.stub)')
						      .each(function(){
						        var stype = $(this).attr('data-share-type')
						        var swith = $(this).attr('data-share-with')
						        if (typeof itemShares[stype] == "undefined") itemShares[stype] = []
						        itemShares[stype].push(swith)
						      })
						// now, handle the damn thing!
						$(this).autocomplete({
						  minLength: 1,
						  source: function(search, response) {
						    $.get(OC.filePath('core', 'ajax', 'share.php'), { fetch: 'getShareWith', search: search.term, itemShares: itemShares }, function(result) {
						      if (result.status == 'success' && result.data.length > 0) {
						        response(result.data);
						      } else {
						        response([t('core', 'No people found')]);
						      }
						    });
						  },
						  focus: function(event, focused) {
						    event.preventDefault();
						  },
						  select: function(event, selected) {
						    // checking if we got a valid sharewith partner
						    if ( (typeof selected.item.value.shareType == "undefined") || (typeof selected.item.value.shareWith == "undefined") )
						      return false;
						    // okay, on with the show
						    var itemType = $(this).data('item-type');
						    var itemSource = $(this).data('item-source');
						    var shareType = selected.item.value.shareType;
						    var shareWith = selected.item.value.shareWith;
						    $(this).val(shareWith);
						    var shareWithInput = $(this)
						    // getting the default permissions from data-permissions
						    // shouldn't it be OC.PERMISSION_READ | OC.PERMISSION_SHARE instead, as a sensible default?
						    var permissions = $(this).data('permissions')
						    OC.Share.share(itemType, itemSource, shareType, shareWith, permissions, function(data) {
						      // we need to "fix" the share-can-edit-ITEMPTYPE-ITEMSOURCE-0 checkbox and label
						      var editCheckboxIdStub = {
						        'can': 'share-can-edit-' + itemType + '-' + itemSource + '-',
						        'collective': 'share-collective-edit-' + itemType + '-' + itemSource + '-'
						      }
						      var permsCheckboxIdStub = {
						        'create': 'share-permissions-create-' + itemType + '-' + itemSource + '-',
						        'update': 'share-permissions-update-' + itemType + '-' + itemSource + '-',
						        'delete': 'share-permissions-delete-' + itemType + '-' + itemSource + '-'
						      }
						      var curShareWithId = $(shareWithInput).parents('.share-interface-container.internal-share').find('.shared-with-entry-container').length
						      // find the stub
						      var newitem = $(shareWithInput)
						        .parents('.share-interface-container.internal-share')
						          .find('.shared-with-entry-container.stub')
						            // clone it
						            .clone()
						              // populate the stub with data
						              .attr('data-item-type', itemType)
						              .attr('data-share-with', shareWith)
						              .attr('data-permissions', permissions)
						              .attr('data-share-type', shareType)
						              .attr('data-item-soutce', itemSource)
						              .attr('title', shareWith)
						              // populate stub's elements
						              .find('.username')
						                .html(shareWith + (shareType === OC.Share.SHARE_TYPE_GROUP ? ' ('+t('core', 'group')+')' : ''))
						                .end()
						              .find('.share-options input[name="create"]')
						                .prop('checked', permissions & OC.PERMISSION_CREATE)
						                .end()
						              .find('.share-options input[name="update"]')
						                .prop('checked', permissions & OC.PERMISSION_UPDATE)
						                .end()
						              .find('.share-options input[name="delete"]')
						                .prop('checked', permissions & OC.PERMISSION_DELETE)
						                .end()
						              .find('.share-options input[name="share"]')
						                .prop('checked', permissions & OC.PERMISSION_SHARE)
						                .end()
						              .find('.share-options input[name="edit"]')
						                .prop('checked', permissions & ( OC.PERMISSION_CREATE | OC.PERMISSION_UPDATE | OC.PERMISSION_DELETE ) )
						                .end()
						              // handle the share-(CAN|COLLECTIVE)-edit-ITEMPTYPE-ITEMSOURCE-0 checkboxes and labels
						              .find('#' + editCheckboxIdStub['can'] + '0')
						                .prop('id', editCheckboxIdStub['can'] + curShareWithId)
						                .end()
						              .find('label[for=' + editCheckboxIdStub['can'] + '0]')
						                .prop('for', editCheckboxIdStub['can'] + curShareWithId)
						                .end()
						              .find('#' + editCheckboxIdStub['collective'] + '0')
						                .prop('id', editCheckboxIdStub['collective'] + curShareWithId)
						                .end()
						              .find('label[for=' + editCheckboxIdStub['collective'] + '0]')
						                .prop('for', editCheckboxIdStub['collective'] + curShareWithId)
						                .end()
						              // handle the share-permissions-(CREATE|UPDATE|DELETE)-ITEMPTYPE-ITEMSOURCE-0 checkboxes and labels
						              .find('#' + permsCheckboxIdStub['create'] + '0')
						                .prop('id', permsCheckboxIdStub['create'] + curShareWithId)
						                .end()
						              .find('label[for=' + permsCheckboxIdStub['create'] + '0]')
						                .prop('for', permsCheckboxIdStub['create'] + curShareWithId)
						                .end()
						              .find('#' + permsCheckboxIdStub['update'] + '0')
						                .prop('id', permsCheckboxIdStub['update'] + curShareWithId)
						                .end()
						              .find('label[for=' + permsCheckboxIdStub['update'] + '0]')
						                .prop('for', permsCheckboxIdStub['update'] + curShareWithId)
						                .end()
						              .find('#' + permsCheckboxIdStub['delete'] + '0')
						                .prop('id', permsCheckboxIdStub['delete'] + curShareWithId)
						                .end()
						              .find('label[for=' + permsCheckboxIdStub['delete'] + '0]')
						                .prop('for', permsCheckboxIdStub['delete'] + curShareWithId)
						                .end()
						              // remove the "stub" class
						              .removeClass('stub')
						      // append it where it's needed most
						      $(shareWithInput)
						        .parents('.share-interface-container.internal-share')
						          .children('.shared-with-list')
						            .append(newitem.fadeIn(500))
						      // clear
						      $(shareWithInput).val('');
						    });
						    return false;
						  }
						});
					});

					// using .off() to make sure the event is only attached once
					$(document)
						.off('change', '.shared-with-entry-container input:checkbox[data-permissions]')
						.on('change', '.shared-with-entry-container input:checkbox[data-permissions]', function(){
						  // get the data
						  var container = $(this).parents('li').first();
						  var permissions = parseInt(container.attr('data-permissions'));
						  var itemType = container.data('item-type');
						  var shareType = container.data('share-type');
						  var itemSource = container.data('item');
						  var shareWith = container.data('share-with');
						  var permission = $(this).data('permissions');

						  // find the required perms
						  if($(this).is(':checked')) {
						    permissions |= permission;
						  } else {
						    permissions &= ~permission;
						  }
						  
						  // save current permissions on the container
						  container.attr('data-permissions', permissions);
						  
						  // set statuses of all the checkboxes
						  switch ($(this).attr('name')) {
						    case 'create':
						    case 'update':
						    case 'delete':
						      $(this)
						        .parents('.share-options')
						          .find('input[type="checkbox"][name="edit"]')
						            .prop('checked', permissions & ( OC.PERMISSION_CREATE | OC.PERMISSION_UPDATE | OC.PERMISSION_DELETE ) )
						      break;
						    case 'edit':
						      $(this)
						        .parents('.share-options')
						          .find('input[type="checkbox"][name="create"]').prop('checked', permissions & OC.PERMISSION_CREATE )
						          .siblings('input[type="checkbox"][name="update"]').prop('checked', permissions & OC.PERMISSION_UPDATE )
						          .siblings('input[type="checkbox"][name="delete"]').prop('checked', permissions & OC.PERMISSION_DELETE )
						      break;
						  }

						  // run the request
						  OC.Share.setPermissions(itemType, itemSource, shareType, shareWith, permissions);
						});
	
					// using .off() to make sure the event is only attached once
					$(document)
						.off('click', '.shared-with-entry-container .unshare')
						.on('click', '.shared-with-entry-container .unshare', function(e) {
						  var container = $(this).parents('li').first();
						  var itemType = container.data('item-type');
						  var shareType = container.data('share-type');
						  var itemSource = container.data('item');
						  var shareWith = container.data('share-with');
						  OC.Share.unshare(itemType, itemSource, shareType, shareWith, function() {
						    container.fadeOut(500, function(){ $(this).remove() });
						  });

						});
				}
			}
		},
		Drop:{
			init:function(){
				if (typeof window.FileReader === 'undefined') {
					console.log('The drop-import feature is not supported in your browser :(');
					return false;
				}
				droparea = document.getElementById('fullcalendar');
				droparea.ondrop = function(e){
					e.preventDefault();
					Calendar.UI.Drop.drop(e);
				}
				console.log('Drop initialized successfully');
			},
			drop:function(e){
				var files = e.dataTransfer.files;
				for(var i = 0;i < files.length;i++){
					var file = files[i];
					var reader = new FileReader();
					reader.onload = function(event){
						Calendar.UI.Drop.doImport(event.target.result);
						$('#fullcalendar').fullCalendar('refetchEvents');
					}
					reader.readAsDataURL(file);
				}
			},
			doImport:function(data){
				$.post(OC.filePath('calendar', 'ajax/import', 'dropimport.php'), {'data':data},function(result) {
					if(result.status == 'success'){
						$('#fullcalendar').fullCalendar('addEventSource', result.eventSource);
						$('#notification').html(result.message);
						$('#notification').slideDown();
						window.setTimeout(function(){$('#notification').slideUp();}, 5000);
						return true;
					}else{
						$('#notification').html(result.message);
						$('#notification').slideDown();
						window.setTimeout(function(){$('#notification').slideUp();}, 5000);
					}
				});
			}
		}
	},
	Settings:{
		//
	},

}
$.fullCalendar.views.list = ListView;
function ListView(element, calendar) {
	var t = this;

	// imports
	jQuery.fullCalendar.views.month.call(t, element, calendar);
	var opt = t.opt;
	var trigger = t.trigger;
	var eventElementHandlers = t.eventElementHandlers;
	var reportEventElement = t.reportEventElement;
	var formatDate = calendar.formatDate;
	var formatDates = calendar.formatDates;
	var addDays = $.fullCalendar.addDays;
	var cloneDate = $.fullCalendar.cloneDate;
	function skipWeekend(date, inc, excl) {
		inc = inc || 1;
		while (!date.getDay() || (excl && date.getDay()==1 || !excl && date.getDay()==6)) {
			addDays(date, inc);
		}
		return date;
	}

	// overrides
	t.name='list';
	t.render=render;
	t.renderEvents=renderEvents;
	t.setHeight=setHeight;
	t.setWidth=setWidth;
	t.clearEvents=clearEvents;

	function clearEvents() {
		this.reportEventClear();
	}

	// main
	function sortEvent(a, b) {
		return a.start - b.start;
	}

	function render(date, delta) {
		if (!t.start){
			t.start = addDays(cloneDate(date, true), -7);
			t.end = addDays(cloneDate(date, true), 7);
		}
		if (delta) {
			if (delta < 0){
				addDays(t.start, -7);
				addDays(t.end, -7);
				if (!opt('weekends')) {
					skipWeekend(t.start, delta < 0 ? -1 : 1);
				}
			}else{
				addDays(t.start, 7);
				addDays(t.end, 7);
				if (!opt('weekends')) {
					skipWeekend(t.end, delta < 0 ? -1 : 1);
				}
			}
		}
		t.title = formatDates(
			t.start,
			t.end,
			opt('titleFormat', 'week')
		);
		t.visStart = cloneDate(t.start);
		t.visEnd = cloneDate(t.end);
	}

	function eventsOfThisDay(events, theDate) {
		var start = cloneDate(theDate, true);
		var end = addDays(cloneDate(start), 1);
		var retArr = new Array();
		for (i in events) {
			var event_end = t.eventEnd(events[i]);
			if (events[i].start < end && event_end >= start) {
				retArr.push(events[i]);
			}
		}
		return retArr;
	}

	function renderEvent(event) {
		if (event.allDay) { //all day event
			var time = opt('allDayText');
		}
		else {
			var time = formatDates(event.start, event.end, opt('timeFormat', 'agenda'));
		}
		var classes = ['fc-event', 'fc-list-event'];
		classes = classes.concat(event.className);
		if (event.source) {
			classes = classes.concat(event.source.className || []);
		}
		var html = '<tr>' +
			'<td>&nbsp;</td>' +
			'<td class="fc-list-time">' +
			time +
			'</td>' +
			'<td>&nbsp;</td>' +
			'<td class="fc-list-event">' +
			'<span id="list' + event.id + '"' +
			' class="' + classes.join(' ') + '"' +
			'>' +
			'<span class="fc-event-title">' +
			escapeHTML(event.title) +
			'</span>' +
			'</span>' +
			'</td>' +
			'</tr>';
		return html;
	}

	function renderDay(date, events) {
		var dayRows = $('<tr>' +
			'<td colspan="4" class="fc-list-date">' +
			'<span>' +
			formatDate(date, opt('titleFormat', 'day')) +
			'</span>' +
			'</td>' +
			'</tr>');
		for (i in events) {
			var event = events[i];
			var eventElement = $(renderEvent(event));
			triggerRes = trigger('eventRender', event, event, eventElement);
			if (triggerRes === false) {
				eventElement.remove();
			}else{
				if (triggerRes && triggerRes !== true) {
					eventElement.remove();
					eventElement = $(triggerRes);
				}
				$.merge(dayRows, eventElement);
				eventElementHandlers(event, eventElement);
				reportEventElement(event, eventElement);
			}
		}
		return dayRows;
	}

	function renderEvents(events, modifiedEventId) {
		events = events.sort(sortEvent);

		var table = $('<table class="fc-list-table"></table>');
		var total = events.length;
		if (total > 0) {
			var date = cloneDate(t.visStart);
			while (date <= t.visEnd) {
				var dayEvents = eventsOfThisDay(events, date);
				if (dayEvents.length > 0) {
					table.append(renderDay(date, dayEvents));
				}
				date=addDays(date, 1);
			}
		}

		this.element.html(table);
	}
}
$(document).ready(function(){
	Calendar.UI.lastView = defaultView;
	Calendar.UI.changeView('auto_refresh');
	
	/**
	* Set an interval timer to make the timeline move 
	*/
	setInterval(Calendar.Util.setTimeline,60000);	
	$(window).resize(_.debounce(function() {
		/**
		* When i use it instant the timeline is walking behind the facts
		* A little timeout will make sure that it positions correctly
		*/
		setTimeout(Calendar.Util.setTimeline,500);
	}));
	$('#fullcalendar').fullCalendar({
		header: false,
		firstDay: firstDay,
		editable: !Calendar.UI.isLinkShared(),
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
			week: t('calendar', "MMM d[ yyyy]{ '–'[ MMM] d yyyy}"),
					// Sep 7 - 13 2009
			day: t('calendar', 'dddd, MMM d, yyyy'),
					// Tuesday, Sep 8, 2009
			},
		axisFormat: defaulttime,
		monthNames: monthNames,
		monthNamesShort: monthNamesShort,
		dayNames: dayNames,
		dayNamesShort: dayNamesShort,
		allDayText: allDayText,
		viewRender: function(view) {
			$('#datecontrol_current').html($('<p>').html(view.title).text());
			$( "#datecontrol_date" ).datepicker("setDate", $('#fullcalendar').fullCalendar('getDate'));
			if (view.name != defaultView) {
				$.post(OC.filePath('calendar', 'ajax', 'changeview.php'), {v:view.name});
				defaultView = view.name;
			}
			if(view.name === 'agendaDay') {
				$('td.fc-state-highlight').css('background-color', '#ffffff');
			} else{
				$('td.fc-state-highlight').css('background-color', '#ffc');
			}
			Calendar.UI.setViewActive(view.name);
			if (view.name == 'agendaWeek') {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 0.1);
			}
			else {
				$('#fullcalendar').fullCalendar('option', 'aspectRatio', 1.35);
			}
			try {
				Calendar.Util.setTimeline();
			} catch(err) {
			}
		},
		selectable: true,
		selectHelper: true,
		select: Calendar.UI.newEvent,
		eventClick: Calendar.UI.editEvent,
		eventDrop: Calendar.UI.moveEvent,
		eventResize: Calendar.UI.resizeEvent,
		eventRender: function(event, element) {
			element.find('.fc-event-title').text($("<div/>").html(escapeHTML(event.title)).text())
		},
		loading: Calendar.UI.loading,
		eventSources: eventSources
	});
	$('#datecontrol_date').datepicker({
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
			
			var view = $('#fullcalendar').fullCalendar('getView');
			if(view.name == 'agendaWeek'){
				$("[class*='fc-col']").removeClass('activeDay');
				daySel=Calendar.Util.getDayOfWeek(date.getDay());
				$('td.fc-'+daySel).addClass('activeDay');
			}
			if (view.name == 'month') {
					$('td.fc-day').removeClass('activeDay');
					prettyDate = $.datepicker.formatDate( 'yy-mm-dd',date);
					$('td[data-date=' + prettyDate + ']').addClass('activeDay');
			}
		}
	});

	$(OC.Tags).on('change', function(event, data) {
		if(data.type === 'event') {
			Calendar.UI.categoriesChanged(data.tags);
		}
	});

	$('#oneweekview_radio').click(function(){
		Calendar.UI.changeView('agendaWeek');
	});
	$('#onemonthview_radio').click(function(){
		Calendar.UI.changeView('month');
	});
	$('#onedayview_radio').click(function(){
		Calendar.UI.changeView('agendaDay');
	});
	$('#today_input').click(function(){
		Calendar.UI.changeView('today');
	});
	$('#datecontrol_left').click(function(){
		Calendar.UI.changeView('prev');
	});
	$('#datecontrol_today').click(function(){
		Calendar.UI.changeView('today');
	});
	$('#datecontrol_right').click(function(){
		Calendar.UI.changeView('next');
	});
	$('#datecontrol_current').click(function() {
		$('#datecontrol_date').slideToggle(500);
	});
	$('#datecontrol_date').hide();
	$('#app-settings-header').on('click keydown',function(event) {
		if(wrongKey(event)) {
			return;
		}
		var bodyListener = function(e) {
			if($('#app-settings').find($(e.target)).length === 0) {
				$('#app-settings').switchClass('open', '');
			}
		};
		if($('#app-settings').hasClass('open')) {
			$('#app-settings').switchClass('open', '');
			$('body').unbind('click', bodyListener);
		} else {
			$('#app-settings').switchClass('', 'open');
			$('body').bind('click', bodyListener);
		}
	});
	Calendar.UI.Share.init();
	Calendar.UI.Drop.init();
	
	// Save the eventSource for shared events.
	for (var i in eventSources) {
		if (eventSources[i].url.substr(-13) === 'shared_events') {
			sharedEventSource = eventSources[i];
		}
	}
	
	$('#choosecalendar .generalsettings').on('click keydown', function(event) {
		event.preventDefault();
		OC.appSettings({appid:'calendar', loadJS:true, cache:false, scriptName:'settingswrapper.php'});
	});
	$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#controls').height() - $('#header').height());

	/* link-sharing/unsharing of single events and calendars done right */
	$('.share-interface-container.link-share input[type="checkbox"].share-link').live('change', function(e){
		// get the data
		slcontainer = $(this).parents('.share-interface-container.link-share')
		itemType = slcontainer.attr('data-item-type')
		itemSource = slcontainer.attr('data-item')
		itemSourceName = slcontainer.attr('data-item-source-name')
		
		// sharing?
		if ($(this).is(':checked')) {
			// share it!
			// we're sharing the item for the first time, so no password, no expiration date for sure
			OC.Share.share(itemType, itemSource, OC.Share.SHARE_TYPE_LINK, '', OC.PERMISSION_READ, itemSourceName, function(data) {
				// update the data
				$(slcontainer)
					.find('.link-text')
						.val(
						  window.location.protocol + '//' + location.host + OC.linkTo('', 'public.php') + '?service=calendar&t='+data.token
						)
						// @tanghus' suggestion
						// https://github.com/owncloud/calendar/pull/308#issuecomment-38424997
						.select()
						.focus();
			})

		// nope, un-sharing!
		} else {
			OC.Share.unshare(itemType, itemSource, OC.Share.SHARE_TYPE_LINK, '', function(data) {
				// clear data
				$(slcontainer)
					.find('.link-text, .share-link-password, .expire-date')
						.val('')
				// clear checkboxes
				$(slcontainer)
					.find('.password-protect, .expire')
						.attr('checked', false)
			});
		}
	})
	
	/* setting the password */
	$('.share-interface-container.link-share input[type="password"].share-link-password').live('blur', function(e){
		// get the data
		slcontainer = $(this).parents('.share-interface-container.link-share')
		itemType = slcontainer.attr('data-item-type')
		itemSource = slcontainer.attr('data-item')
		itemSourceName = slcontainer.attr('data-item-source-name')
		itemPassword = $(this).val()
		
		// set the password!
		OC.Share.share(itemType, itemSource, OC.Share.SHARE_TYPE_LINK, itemPassword, OC.PERMISSION_READ, itemSourceName, function(data) {
			console.log(data)
			$(slcontainer)
				.find('input.share-link-password')
					.attr('placeholder', 'Password protected')
					.val('')
		})
	})
	
	/* what about Enter and Escape keys? */
	$('.share-interface-container.link-share input[type="password"].share-link-password').live('keydown', function(e){
		// Enter? submit!
		if (e.which == 13) {
			e.preventDefault();
			$(this).blur()
			return false;
		}
		// escape? ignore!
		// if (e.which == 13) { TODO?
	})
	
	/* removing password */
	$('.share-interface-container.link-share input[type="checkbox"].password-protect').live('change', function(e){
		// clear the data input
		$(this)
			.siblings('.displayable')
				.children('input')
					.attr('placeholder', 'Password')
					.val('')
		// get the data
		slcontainer = $(this).parents('.share-interface-container.link-share')
		itemType = slcontainer.attr('data-item-type')
		itemSource = slcontainer.attr('data-item')
		itemSourceName = slcontainer.attr('data-item-source-name')
		itemPassword = slcontainer.find('input.share-link-password').val()
		
		// we only handle removal of password
		if (!$(this).is(':checked')) {
			OC.Share.share(itemType, itemSource, OC.Share.SHARE_TYPE_LINK, itemPassword, OC.PERMISSION_READ, itemSourceName, function(data) {
			});
		}
	})
	
	/* setting the expiration date */
	$('.share-interface-container.link-share input.expire-date').live('change', function(e){
		// get the data
		slcontainer = $(this).parents('.share-interface-container.link-share')
		itemType = slcontainer.attr('data-item-type')
		itemSource = slcontainer.attr('data-item')
		itemSourceName = slcontainer.attr('data-item-source-name')
		itemPassword = slcontainer.find('input.share-link-password').val()
		expiryDate = $(this).val()
		
		// set the date!
		$.post(OC.filePath('core', 'ajax', 'share.php'), { action: 'setExpirationDate', itemType: itemType, itemSource: itemSource, date: expiryDate }, function(result) {
			if (!result || result.status !== 'success') {
				OC.dialogs.alert(t('core', 'Error setting expiration date'), t('core', 'Error'));
			}
		});
	})
	
	/* removing password */
	$('.share-interface-container.link-share input[type="checkbox"].password-protect').live('change', function(e){
		// clear the data input
		$(this)
			.siblings('.displayable')
				.children('input')
					.val('')
		// get the data
		slcontainer = $(this).parents('.share-interface-container.link-share')
		itemType = slcontainer.attr('data-item-type')
		itemSource = slcontainer.attr('data-item')
		itemSourceName = slcontainer.attr('data-item-source-name')
		itemPassword = slcontainer.find('input.share-link-password').val()
		
		// we only handle removal of expiry date
		if (!$(this).is(':checked')) {
			$.post(OC.filePath('core', 'ajax', 'share.php'), { action: 'setExpirationDate', itemType: itemType, itemSource: itemSource, date: '' }, function(result) {
				if (!result || result.status !== 'success') {
					OC.dialogs.alert(t('core', 'Error unsetting expiration date'), t('core', 'Error'));
				}
			});
		}
	})

	/* datepicker, because firefox can't into datepicker control */
	$('.share-link-enabled-container .expire-date:not(.hasDatepicker)').live('click', function(){
		$(this)
			.attr('type', 'text')
			.datepicker({
				dateFormat : datepickerFormatDate,
				minDate : 1
			})
			.datepicker('show');
	});
	
	/* clear the expire date picker when expire date checkbox gets unselected */
	$('.displayable-control.expire').live('change', function(){
		if (! $(this).is(':checked')) {
			$(this)
				.siblings('.expire-date-container')
					.children('.expire-date')
						.val('')
						.change()
		}
	})
});

var wrongKey = function(event) {
	return ((event.type === 'keydown' || event.type === 'keypress') 
		&& (event.keyCode !== 32 && event.keyCode !== 13));
};
