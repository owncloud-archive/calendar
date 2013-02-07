/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
(function($){
	
	var methods = {
		init : function( options ) {
			//set default options
			var settings = $.extend( {
				//list of all calendars (enabled and disabled)
				calendars: {},
				//navigate through calendar by scrolling
				scrollNavigation: false,
				//navigate through calendar by using cursor keys
				keyboardNavigation: false,
				//readonly or editable
				editable: false,
				//enable drag and drop import
				dragdropimport: false,
				//is the calendar embedded into another site?
				embedded: false,
				//launch options for fullCalendar
				fullCalendar: {},
			}, options);
			
			return this.each( function () {
				var $this = $(this);
				//embedded calendars can't be editted yet
				if(settings.editable && settings.embedded){
					console.log('a calendar can\'t be editable and embedded yet, sry');
					return false;
				}
				
				//define function t if it doesn't exist				
				if(settings.embedded && typeof t != 'function'){
					window.t = function(app, string){
						return string;
					};
				}
				
				//get some translated arrays
				var dayNames = new Array		 (t('calendar', 'Sunday'),
												  t('calendar', 'Monday'),
												  t('calendar', 'Tuesday'),
												  t('calendar', 'Wednesday'),
												  t('calendar', 'Thursday'),
												  t('calendar', 'Friday'),
												  t('calendar', 'Saturday')),
				    dayNamesShort = new Array	 (t('calendar', 'Sun.'),
												  t('calendar', 'Mon.'),
												  t('calendar', 'Tue.'),
												  t('calendar', 'Wed.'),
												  t('calendar', 'Thu.'),
												  t('calendar', 'Fri.'),
												  t('calendar', 'Sat.')),
				    monthNames = new Array		 (t('calendar', 'January'),
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
												  t('calendar', 'December')),
				    monthNamesShort = new Array	 (t('calendar', 'Jan.'),
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
				
				//define some functions or set
				//you'll have to define your own, if you want to embed the calendar into your website
				var viewDisplay = !settings.embedded ? function(view){
					//initialize the routing framework
					OC.Router.registerLoadedCallback(function(){
						//set new title
						$('#selecteddate').html($('<p>').html(view.title).text());
						//update settings
						$.post(OC.Router.generate( 'calendar_set_view', { view: view.name } ));
						//update aspectRatio
						if (view.name == 'agendaWeek') {
							$this.fullCalendar('option', 'aspectRatio', 0.1);
						}
						else {
							$this.fullCalendar('option', 'aspectRatio', 1.35);
						}
						//rerender event
						$this.fullCalendar('rerenderEvents');
					});
				} : null;
				
				var select = (!settings.embedded || !settings.editable) ? function(start, end, allDay){
					//remove selection
					$this.fullCalendar('unselect');
					//you can't create a new event if you are still creating another
					if($('#quickAdd_newEvent').length != 0){
						return false;
					}
					//create new event
					var event = {className: 'quickAdd_newEvent',
								 title: '',
								 start: start,
								 end: end,
								 allDay: allDay,
								 editable: false}
					//render the new event
					$this.fullCalendar('renderEvent', event);
					//create input field
					$('.quickAdd_newEvent:first > div > span.fc-event-title').html('<input type="text" id="quickAdd_newEvent"></input>');
					//set focus on input field
					$('#quickAdd_newEvent').focus();
					//disable the focus if the user presses enter (=> keycode 13)
					$('#quickAdd_newEvent').keypress(function(e) {
						if(e.which == 13) {
							$('#quickAdd_newEvent').trigger('focusout');
						}
					});
					//sent information about new event to server
					$('#quickAdd_newEvent').focusout(function(){
						//initialize the routing framework
						OC.Router.registerLoadedCallback(function(){
							var title = $('#quickAdd_newEvent').val();
							//check if the title is empty
							if(title == ''){
								title = t('calendar', 'Untitled event');
							}
							//get the event's start and end
							start = Math.round(start.getTime()/1000);
							end = Math.round(end.getTime()/1000);
							//sent information about new event to server
							//USE OC.ROUTER !!! TODO
							$.post(OC.filePath('calendar', 'ajax/event', 'quickAdd.php'), {title: title, start: start, end: end, allDay: allDay});
							//refetch the events
							$this.fullCalendar('refetchEvents');
							//remove the event with the input field
							$('.quickAdd_newEvent').remove();
						});
					});
					return true;
				} : null;
				
				var eventClick = !settings.embedded ? function(){
					//initialize the routing framework
					OC.Router.registerLoadedCallback(function(){
						
					});
				} : null;
				
				var eventDrop = !settings.embedded ? function(event, dayDelta, minuteDelta, allDay, revertFunc){
					//initialize the routing framework
					OC.Router.registerLoadedCallback(function(){
						//remove fancy event info
						$('.tipsy').remove();
						//send informations to the server
						$.post(OC.filePath('calendar', 'ajax/event', 'move.php'), { id: event.id, dayDelta: dayDelta, minuteDelta: minuteDelta, allDay: allDay?1:0, lastmodified: event.lastmodified},
						function(data) {
							if (data.status == 'success'){
								//update lastmodified informations
								event.lastmodified = data.lastmodified;
								//celebrate
								console.log('Event moved successfully');
							}else{
								revertFunc();
								$(this).fullCalendar('refetchEvents');
							}
						});
					});
				} : null;
				
				var eventResize = !settings.embedded ? function(event, dayDelta, minuteDelta, revertFunc){
					//initialize the routing framework
					OC.Router.registerLoadedCallback(function(){
						//remove fancy event info
						$('.tipsy').remove();
						//send information to the server
						//TODO use router
						$.post(OC.filePath('calendar', 'ajax/event', 'resize.php'), { id: event.id, dayDelta: dayDelta, minuteDelta: minuteDelta, lastmodified: event.lastmodified},
						function(data) {
							if (data.status == 'success'){
								//update lastmodified informations
								event.lastmodified = data.lastmodified;
								//celebrate
								console.log('Event resized successfully');
							}else{
								revertFunc();
								$(this).fullCalendar('refetchEvents');
							}
						});
					});
				} : null;
				
				var eventRender = !settings.embedded ? function(){
					//initialize the routing framework
					OC.Router.registerLoadedCallback(function(){
						
					});
				} : null;
				
				var loading = !settings.embedded ? function(status){
					if(status){
						document.body.style.cursor = 'wait';
					}else{
						document.body.style.cursor = 'default';
					}
				} : null;
				
				var fullCalendarsettings = $.extend( {
					header: false,
					defaultView : 'basic4Weeks',
					ignoreTimezone: false,
					editable: options.editable,
					selectable: options.editable,
					selectHelper: options.editable,
					timeFormat: {
						agenda: options.agendatime,
						'': options.defaulttime
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
					columnFormat: {
					    week: t('calendar', 'ddd d. MMM')
					},
					monthNames: monthNames,
					monthNamesShort: monthNamesShort,
					dayNames: dayNames,
					dayNamesShort: dayNamesShort,
					allDayText: t('calendar', 'All day'),
					viewDisplay: viewDisplay,
					select: select,
					eventClick: eventClick,
					eventDrop: eventDrop,
					eventResize: eventResize,
					eventRender: eventRender,
					loading: loading				
				}, options.fullCalendar)
				
				//initialize fullCalendar
				$this.fullCalendar(fullCalendarsettings);
				//initialize the routing framework
				OC.Router.registerLoadedCallback(function(){
					//add all event sources
					$(options.calendars).each(function(index, element){
						element.url = OC.Router.generate( 'calendar_get_events', {calendarid: element.calendarid})
						//check if calendar is enabled
						if(element.enabled){
							//add source if calendar is enabled
							$this.fullCalendar( 'addEventSource' , element);
						}
					});
				});
				
				if(settings.scrollNavigation){
					//handler for scrolling
					var scroll = function (event) {
						//remove all tipsy pop-ups
						$('.tipsy').remove();
						//get current view
						var view = $this.fullCalendar('getView');
						//no scroll in agendaWeek view, because it's a pain
						if(view.name == 'agendaWeek'){
							//return true so the standard scroll behavior still works
							return true;
						}
						//get direction of scrolling
						var direction;
						//Chromium, Firefox, Safari
						if(event.detail){
							if(event.detail < 0){
								direction = 'prev';
							}else{
								direction = 'next';
							}
						}
						//Internet Explorer, Opera
						if (event.wheelDelta){
							if(event.wheelDelta > 0){
								direction = 'prev';
							}else{
								direction = 'next';
							}
						}
						//move backward or forward
						$this.fullCalendar(direction);
						//prevent the execution of the default scroll behavior
						event.preventDefault();
					}
					//register handler
					document.onmousewheel = scroll;
				}
				
				if(settings.keyboardNavigation){
					$(document).keydown(function(e){
						//!TODO - check if there is an opened dialog!!!
						//get current view
						var view = $this.fullCalendar('getView');
						//get the firstday
						//var firstday = $(this);
						//up
						if (e.keyCode == 38 || e.keyCode == 75) {
							//remove tipsy
							$('.tipsy').remove();
							//no scroll in agendaWeek view, because it's a pain
							if(view.name != 'agendaWeek'){
								$this.fullCalendar('prev');
							}
						}
						//down
						if(e.keyCode == 40 || e.keyCode == 74) {
							//remove tipsy
							$('.tipsy').remove();
							//no scroll in agendaWeek view, because it's a pain
							if(view.name != 'agendaWeek'){
								$this.fullCalendar('next');
							}
						}
<<<<<<< HEAD
						//left
						if(e.keyCode == 37 || e.keyCode == 72) {
							//no scroll in agendaWeek view, because it's a pain
							if(view.name == 'agendaWeek'){
								//you can't edit the firstDay value after initializing fullCalendar yet
								//feature request already submitted
								//$('#fullCalendar').fullCalendar('next');
=======
					  });
				}
			},
			submit:function(button, calendarid){
				var displayname = $.trim($("#displayname_"+calendarid).val());
				var active = $("#edit_active_"+calendarid+":checked").length;
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
							$(button).closest('tr').prev().html(data.page).show().next().remove();
							$('#fullcalendar').fullCalendar('removeEventSource', data.eventSource.url);
							$('#fullcalendar').fullCalendar('addEventSource', data.eventSource);
							if (calendarid == 'new'){
								$('#choosecalendar_dialog > table:first').append('<tr><td colspan="6"><a href="#" id="chooseCalendar"><input type="button" value="' + newcalendar + '"></a></td></tr>');
>>>>>>> master
							}
						}
						//right
						if(e.keyCode == 39 || e.keyCode == 76) {
							//no scroll in agendaWeek view, because it's a pain
							if(view.name == 'agendaWeek'){
								//you can't edit the firstDay value after initializing fullCalendar yet
								//feature request already submitted
								//$('#fullCalendar').fullCalendar('next');
							}
						}
					});
				}
				
				if(settings.dragdropimport){
					//TODO
				}
			});
		},
		
	}
	
	$.fn.oCCalendar = function ( method ) {
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			try{
				return $(this).fullCalendar(method, arguments[1]);
			}catch(err){
				$.error( 'Method ' +  method + ' does not exist on jQuery.calendarList' );
			}
		}
<<<<<<< HEAD
	};
})( jQuery );
=======
	});
	fillWindow($('#content'));
	OCCategories.changed = Calendar.UI.categoriesChanged;
	OCCategories.app = 'calendar';
	OCCategories.type = 'event';
	$('#oneweekview_radio').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'agendaWeek');
	});
	$('#onemonthview_radio').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'month');
	});
	$('#listview_radio').click(function(){
		$('#fullcalendar').fullCalendar('changeView', 'list');
	});
	$('#today_input').click(function(){
		$('#fullcalendar').fullCalendar('today');
	});
	$('#datecontrol_left').click(function(){
		$('#fullcalendar').fullCalendar('prev');
	});
	$('#datecontrol_right').click(function(){
		$('#fullcalendar').fullCalendar('next');
	});
	Calendar.UI.Share.init();
	Calendar.UI.Drop.init();
	$('#choosecalendar .generalsettings').on('click keydown', function(event) {
		event.preventDefault();
		OC.appSettings({appid:'calendar', loadJS:true, cache:false, scriptName:'settingswrapper.php'});
	});
	$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#controls').height() - $('#header').height() - 15);
});
>>>>>>> master
