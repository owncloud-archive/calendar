/**
 * Copyright (c) 2012 Georg Ehrke <ownclouddev at georgswebsite dot de>
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
 * This file:
 *  - provides the basic javascripts of the calendar app
 *  - loads other javascript files dynamically on demand
 * 
 */
Calendar={
	/*
	 * Current timezone of the user
	 * will be properly set in ./init.js
	 */
	timezone:'UTC',
	/**
	 * This wrapper contains all basic methods for event management
	 * Methods: 
	 *  - getEventPopupText
	 *  - quickAdd
	 *  - smartAdd
	 *  - validate
	 */
	Event:{
		/**
		 * gets the event info
		 *
		 * @brief event info
		 * @return Boolean
		 */
		getEventPopupText:function(event){
			if (event.allDay){
				var timespan = $.fullCalendar.formatDates(event.start, event.end, 'ddd d MMMM[ yyyy]{ -[ddd d] MMMM yyyy}');
			}else{
				var timespan = $.fullCalendar.formatDates(event.start, event.end, 'ddd d MMMM[ yyyy] ' + defaulttime + '{ -[ ddd d MMMM yyyy]' + defaulttime + '}');
			}
			var html =
				'<div class="summary">' + event.title + '</div>' +
				'<div class="timespan">' + timespan + '</div>';
			if (event.description){
				html += '<div class="description">' + event.description + '</div>';
			}
			return html;
		},
		/**
		 * loads the quickadd UI
		 *
		 * @brief quickadd events
		 * @return Boolean
		 */
		quickAdd:function(start, end, allDay){
			$('#fullcalendar').fullCalendar('unselect');
			if($('#quickAdd_newEvent').length != 0){
				return false;
			}
			//take background- / border- and text color from default calendar
			var bgColor = eventSources[0]['backgroundColor'],
				borderColor = eventSources[0]['borderColor'],
				textColor = eventSources[0]['textColor'];
			var event = {className: 'quickAdd_newEvent',
						 title: '',
						 start: start,
						 end: end,
						 allDay: allDay,
						 backgroundColor: bgColor,
						 borderColor: borderColor,
						 textColor: textColor}
			$('#fullcalendar').fullCalendar('renderEvent', event);
			$('.quickAdd_newEvent:first > div > span.fc-event-title').html('<input type="text" id="quickAdd_newEvent"></input>');
			$('#quickAdd_newEvent').focus();
			$('#quickAdd_newEvent').focusout(function(){
				var title = $('#quickAdd_newEvent').val();
				if(title != ''){
					start = Math.round(start.getTime()/1000);
					console.log(start);
					end = Math.round(end.getTime()/1000);
					console.log(end);
					$.post(OC.filePath('calendar', 'ajax/event', 'quickAdd.php'), {title: title, start: start, end: end, allDay: allDay});
				}
				$('#fullcalendar').fullCalendar('refetchEvents');
				$('.quickAdd_newEvent').remove();
			});
			return true;
		},
		/**
		 * loads the smartadd UI
		 *
		 * @brief smartadd events
		 * @return Boolean
		 */
		smartAdd:function(){
			
			OC.addScript('calendar', 'event');
			return true;
		},
		/**
		 * validate the user's input
		 *
		 * @brief validates user
		 * @return Boolean
		 */
		validate:function(){
			
			if(valid){
				return true;
			}
			return false;
		}
		
	},
	/**
	 * This wrapper contains all basic methods for user interface and user experience
	 * Methods: 
	 *  - resize
	 *  - scroll
	 *  - smartadd
	 *  - validate
	 */
	UI:{
		resize:function(){
			$('.tipsy').remove();
			$('#fullcalendar').fullCalendar('option', 'height', $(window).height() - $('#controls').height() - $('#header').height() - 15);
			$('#calendars').css('height', $(window).height() - $('#controls').height() - $('#header').height() - 20);
		},
		/**
		 * sets the active calendar view
		 *
		 * @brief scrolls
		 * @return Boolean
		 */
		setViewActive: function(view){
			$('#view input[type="button"]').removeClass('active');
			$('#'+view).addClass('active');
		},
		/**
		 * executes the calendar scroll
		 *
		 * @brief scrolls
		 * @return Boolean
		 */
		scrollCalendar: function(event){
			//remove all tipsy pop-ups
			$('.tipsy').remove();
			//get current view
			var view = $('#fullcalendar').fullCalendar('getView');
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
			$('#fullcalendar').fullCalendar(direction);
			//prevent the execution of the default scroll behavior
			event.preventDefault();
		}
	}
}