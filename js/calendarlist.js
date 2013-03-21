/**
 * Copyright (c) 2013 Georg Ehrke <ownclouddev at georgswebsite dot de>
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
				//readonly or editable
				editable: false,
				//is the list sortable?
				sortable: true,
			}, options);
			
			//create some functions for list items
			var clickItem = function(){
				if($(this).attr('data-calendarid') == 'calendar.new' || $(this).attr('data-calendarid') == 'subscription.new'){
					console.log($(this));
					return false;
				}else{
					var isenabled = $(this).attr('data-enabled');
					$this = $(this);
					OC.Router.registerLoadedCallback(function(){
						if(isenabled == 'true'){
							//TODO disable calendar on server!
							$this.attr('data-enabled', false);
							$this.css('opacity', 0.8);
							$this.find('.calendarListItemColorbox').css('background-color', 'transparent');
							var url = OC.Router.generate( 'calendar_get_events', {calendarid: $this.attr('data-calendarid')});
							$('.fc').oCCalendar( 'removeEventSource', url)
						}else{
							//TODO enable calendar on server!
							$this.attr('data-enabled', true);
							$this.css('opacity', 1);
							$this.find('.calendarListItemColorbox').css('background-color', $this.attr('data-backgroundColor'));
							var url = OC.Router.generate( 'calendar_get_events', {calendarid: $this.attr('data-calendarid')});
							var element = {
								"displayname": $this.attr('data-displayname'),
								"backgroundColor": $this.attr('data-backgroundcolor'),
								"borderColor": $this.attr('data-bordercolor'),
								"textColor": $this.attr('data-textcolor'),
								"editable": $this.attr('data-editable'),
								"enabled": true,
								"calendarid": $this.attr('data-calendarid'),
								"md5": $this.attr('data-md5'),
								"className": $this.attr('data-classname'),
								"cache": $this.attr('data-cache'),
								"url": url
							};
							$('.fc').oCCalendar( 'addEventSource', element);
						}
					});
				}
			};
			
			var dblclickItem = function(){
				//what's about disabling all calendars but the clicked one?
			};
			
			var mouseoverItem = function(){
				if($(this).attr('data-calendarid') == 'calendar.new' || $(this).attr('data-calendarid') == 'subscription.new' || $(this).attr('data-enabled') == 'false'){
					return false;
				}
				var classname = $(this).attr('data-classname');
				$('.fc-event:not(.' + classname + ')').css('opacity', 0.5);
			};
			
			var mouseoutItem = function (){
				$('.fc-event').css('opacity', 1);
			};
			
			var editCalendar = function(){
				
			}
			
			var removeCalendar = function(){
				
			}
			
			//create the list items
			var generateItem = function(index, element){
				var li = $('<li>');
				//add all necessary attributes
				li.attr('data-backgroundColor', element.backgroundColor);
				li.attr('data-borderColor', element.borderColor);
				li.attr('data-cache', element.cache);
				li.attr('data-calendarid', element.calendarid);
				li.attr('data-className', element.className);
				li.attr('data-displayname', element.displayname);
				li.attr('data-editable', element.editable);
				li.attr('data-enabled', element.enabled);
				li.attr('data-md5', element.md5);
				li.attr('data-textColor', element.textColor);
				//add jquery class to style the list item
				li.addClass('ui-state-default');
				//register onClick event
				li.click(clickItem);
				//register onDoubleClick event
				li.dblclick(dblclickItem);
				//register hover event
				if(element.calendarid != 'calendar.new' && element.calendarid != 'subscription.new'){
					li.tipsy({
						gravity: 'w',
						title: 'foo',
						fallback: 'LOL'
					});
				}
				//create the colorbox on the left side
				var colorbox = $('<span>');
				colorbox.addClass('calendarListItemColorbox');
				//color the colorbox if the calendar is enabled
				if(element.enabled){
					colorbox.css('background-color', element.backgroundColor);
				}else{
					colorbox.css('background-color', 'transparent');
				}
				//create the title right in the middle
				var title = $('<p>');
				title.addClass('calendarListItemTitle');
				title.text(element.displayname);
				//center the text if it's the dummy calendar for creating a new calendar
				if(element.calendarid == 'calendar.new' || element.calendarid == 'subscription.new'){
					title.css('text-align', 'center');
				}
				//append the content
				li.append(colorbox);
				li.append(title);
				//return the generated listItem
				return li;
			}
			
			return this.each( function () {
				//create two empty arrays for calendars
				var calendars_rw = new Array();
				var calendars_ro = new Array();
				//sort all calendars by editability
				$(settings.calendars).each(function(index, element){
					if(element.editable){
						//add element to array with writable calendars
						calendars_rw.push(element);
					}else{
						//add element to array with readonly calendars
						calendars_ro.push(element);
					}
				});
				//create dummy calendar for creating a new calendar
				var new_calendar = {
					backgroundColor: 'transparent',
					calendarid: 'calendar.new',
					displayname: '...'
				};
				calendars_rw.push(new_calendar);
				//create dummy calendar for creating a new subscription
				var new_subscription = {
					backgroundColor: 'transparent',
					calendarid: 'subscription.new',
					displayname: '...'
				};
				calendars_ro.push(new_subscription);
				//create basic html structure
				var html = $('<div>');
				html.addClass('calendarListContainer');
				//create title for calendar list
				var title_calendars = $('<h2>');
				title_calendars.text(t('calendar', 'Your calendars') + ':');
				//create calendar list
				var calendars = $('<ul>');
				calendars.addClass('calendars calendarList');
				calendars.attr('data-count', calendars_rw.length);
				//create title for subscription list
				var title_subscriptions = $('<h2>');
				title_subscriptions.text(t('calendar', 'Your subscriptions') + ':');
				//create subscription list
				var subscriptions = $('<ul>')
				subscriptions.addClass('subscriptions calendarList')
				subscriptions.attr('data-count', calendars_ro.length);
				//generate the items
				$(calendars_rw).each(function(index, element){
					var item = generateItem(index, element);
					calendars.append(item);
				});
				$(calendars_ro).each(function(index, element){
					var item = generateItem(index, element);
					subscriptions.append(item);
				});
				//make calendars sortable
				calendars.sortable();
				calendars.disableSelection();
				//make subscriptions sortable
				subscriptions.sortable();
				subscriptions.disableSelection();
				//inset all the content
				html.append(title_calendars);
				html.append(calendars);
				html.append('<br>');
				html.append(title_subscriptions);
				html.append(subscriptions);
				//display the new awesome calendar list
				$(this).html(html);
			});
		}
	}
	
	$.fn.oCCalendarList = function ( method ) {
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.calendarList' );
		}
	};
})( jQuery );