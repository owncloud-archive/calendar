OC.Router.registerLoadedCallback(function(){
$(function(){
	
	var Calendar = Backbone.Model.extend();

	var Event = Backbone.Model.extend();

	var Calendars = Backbone.Collection.extend({
		model: Calendar,
		url: OC.Router.generate('calendar_get_all_calendars')
	});

	var Events = Backbone.Collection.extend({
		model: Event,
		url: 'events.php'
	}); 

	var CalendarsView = Backbone.View.extend({
		el: $('#calendarlist'),
		cal: $('#fullcalendar'),
		initialize: function(){
			_.bindAll(this);
			
			this.collection.bind('reset', this.addAll);
			this.collection.bind('add', this.addOne);
            this.collection.bind('change', this.change);
            this.collection.bind('destroy', this.destroy);
            
            this.CalendarView = new CalendarView();
        },
        render: function() {
        	//readAndWrite
            var calendars = this.collection.where({writable: '1'});
            
            //readonly
            var subscriptions = this.collection.where({writable: '0'});
            
            //calendar list object
            var html = this.$el.empty();
            
            //create two lists
			var html_calendars = $('<ul>').addClass('calendarlist calendars').attr('droppable', 'droppable');
			var html_subscriptions = $('<ul>').addClass('calendarlist subscriptions').attr('droppable', 'droppable');
			
            //create two titles
			var title_calendars = $('<span>').text(t('calendar', 'Your calendars') + ':');
			var title_subscriptions = $('<span>').text(t('calendar', 'Your subscriptions') + ':');
			
			//br item
            var br = $('<br>');
            
            var renderElement = function(element){
				if(element.attributes){
					element = element.attributes;
				}
				var li = $('<li>');
				//add all necessary attributes
				//li.attr('data-backgroundColor', element.backgroundColor);
				//li.attr('data-borderColor', element.borderColor);
				//li.attr('data-cache', element.cache);
				//li.attr('data-calendarid', element.calendarid);
				//li.attr('data-className', element.className);
				//li.attr('data-displayname', element.displayname);
				//li.attr('data-editable', element.editable);
				//li.attr('data-enabled', element.enabled);
				//li.attr('data-md5', element.md5);
				//li.attr('data-textColor', element.textColor);
				//add jquery class to style the list item
				li.addClass('ui-state-default');
				//register onClick event
				//li.click(clickItem);
				//register onDoubleClick event
				//li.dblclick(dblclickItem);
				//register hover event				//create the colorbox on the left side
				var colorbox = $('<span>');
				colorbox.addClass('calendarListItemColorbox');
				//color the colorbox if the calendar is enabled
				//if(element.enabled){
					//colorbox.css('background-color', element.backgroundColor);
				//}else{
					//colorbox.css('background-color', 'transparent');
				//}
				//create the title right in the middle
				var title = $('<p>');
				title.addClass('calendarListItemTitle');
				title.text(element.displayname);
				//center the text if it's the dummy calendar for creating a new calendar
				//if(element.calendarid == 'calendar.new' || element.calendarid == 'subscription.new'){
					//title.css('text-align', 'center');
				//}
				//append the content
				li.append(colorbox);
				li.append(title);
				//return the generated listItem
				return li;
			}
            
            //create elements for each calendar
			$(calendars).each(function(index, element){
				html_calendars.append(renderElement(element));
			});
			
			//create element for new calendar
			html_calendars.append(renderElement({
				calendarid: 'new',
				displayname: escapeHTML('+ ' + t('calendar', 'New calendar')),
				editable: 'new'
			}));
			
			//create elements for each subscription
			$(subscriptions).each(function(index, element){
            	html_subscriptions.append(renderElement(element));
			});
			
			//create element for new subscription
			html_subscriptions.append(renderElement({
				calendarid: 'new',
				displayname: escapeHTML('+ ' + t('calendar', 'New subscription')),
				editable: 'new'
			}));
			
			//put everything together
            $(html).append(title_calendars).append(html_calendars).append(br).append(title_subscriptions).append(html_subscriptions);
            
            //reset old view
			this.el.innerHTML = html.html();
        },
        addAll: function() {
        	this.render();
            this.cal.fullCalendar('addCalendarSource', this.collection.toJSON());
        },
        addOne: function(Calendar) {
            this.$el.calendarList('renderCalendar', Calendar.toJSON());
        },        
        select: function(startDate, endDate) {
            this.CalendarView.collection = this.collection;
            this.CalendarView.model = new Calendar({start: startDate, end: endDate});
            this.CalendarView.render();            
        },
        CalendarClick: function(fcCalendar) {
            this.CalendarView.model = this.collection.get(fcCalendar.id);
            this.CalendarView.render();
        },
        change: function(Calendar) {
            // Look up the underlying Calendar in the calendar and update its details from the model
            var fcCalendar = this.$el.calendarList('clientCalendars', Calendar.get('id'))[0];
            fcCalendar.title = Calendar.get('title');
            fcCalendar.color = Calendar.get('color');
            this.$el.calendarList('updateCalendar', fcCalendar);           
        },
        CalendarDropOrResize: function(fcCalendar) {
            // Lookup the model that has the ID of the Calendar and update its attributes
            this.collection.get(fcCalendar.id).save({start: fcCalendar.start, end: fcCalendar.end});            
        },
        destroy: function(Calendar) {
            this.$el.calendarList('removeCalendars', Calendar.id);         
        }        
    });

    var CalendarView = Backbone.View.extend({
        el: $('#CalendarDialog'),
        initialize: function() {
            _.bindAll(this);           
        },
        render: function() {
            var buttons = {'Ok': this.save};
            if (!this.model.isNew()) {
                _.extend(buttons, {'Delete': this.destroy});
            }
            _.extend(buttons, {'Cancel': this.close});            
            
            this.$el.dialog({
                modal: true,
                title: (this.model.isNew() ? 'New' : 'Edit') + ' Calendar',
                buttons: buttons,
                open: this.open
            });

            return this;
        },        
        open: function() {
            this.$('#title').val(this.model.get('title'));
            this.$('#color').val(this.model.get('color'));            
        },        
        save: function() {
            this.model.set({'title': this.$('#title').val(), 'color': this.$('#color').val()});
            
            if (this.model.isNew()) {
                this.collection.create(this.model, {success: this.close});
            } else {
                this.model.save({}, {success: this.close});
            }
        },
        close: function() {
            this.$el.dialog('close');
        },
        destroy: function() {
            this.model.destroy({success: this.close});
        }        
    });
    


 
    var EventsView = Backbone.View.extend({
        initialize: function(){
            _.bindAll(this); 

            this.collection.bind('reset', this.addAll);
            this.collection.bind('add', this.addOne);
            this.collection.bind('change', this.change);            
            this.collection.bind('destroy', this.destroy);
            
            this.eventView = new EventView();            
        },
        render: function() {
            this.$el.fullCalendar({
            	header: false,
            	calendars: {},
            	scrollNavigation: true,
            	keyboardNavigation: true,
            	editable: true,
            	dragdropimport: true,
            	fullCalendar: {
					timeFormat: {
						agenda: 'HH:mm',
						'': 'HH:mm'
					},
				},
				firstDay: 1,
                selectable: true,
                selectHelper: true,
                editable: true,
                ignoreTimezone: false,
                select: this.select,
                eventClick: this.eventClick,
                eventDrop: this.eventDropOrResize,
                eventResize: this.eventDropOrResize,
                defaultView: 'basic4Weeks',
                contentHeight: $('#app-content').height(),
                viewDisplay: function(view) {
					$('#selecteddate').html($('<p>').html(view.title).text());
					var date = $(this).fullCalendar('getDate');
					$('#globaldatepicker').datepicker( 'setDate', date);
				},
            });
        },
        addAll: function() {
            this.$el.fullCalendar('addEventSource', this.collection.toJSON());
        },
        addOne: function(event) {
            this.$el.fullCalendar('renderEvent', event.toJSON());
        },        
        select: function(startDate, endDate) {
            this.eventView.collection = this.collection;
            this.eventView.model = new Event({start: startDate, end: endDate});
            this.eventView.render();
        },
        eventClick: function(fcEvent) {
            this.eventView.model = this.collection.get(fcEvent.id);
            this.eventView.render();
        },
        change: function(event) {
            // Look up the underlying event in the calendar and update its details from the model
            var fcEvent = this.$el.fullCalendar('clientEvents', event.get('id'))[0];
            fcEvent.title = event.get('title');
            fcEvent.color = event.get('color');
            this.$el.fullCalendar('updateEvent', fcEvent);           
        },
        eventDropOrResize: function(fcEvent) {
            // Lookup the model that has the ID of the event and update its attributes
            this.collection.get(fcEvent.id).save({start: fcEvent.start, end: fcEvent.end});            
        },
        destroy: function(event) {
            this.$el.fullCalendar('removeEvents', event.id);         
        }        
    });

    var EventView = Backbone.View.extend({
        el: $('#eventDialog'),
        initialize: function() {
            _.bindAll(this);           
        },
        render: function() {
            var buttons = {'Ok': this.save};
            if (!this.model.isNew()) {
                _.extend(buttons, {'Delete': this.destroy});
            }
            _.extend(buttons, {'Cancel': this.close});            
            
            this.$el.dialog({
                modal: true,
                title: (this.model.isNew() ? 'New' : 'Edit') + ' Event',
                buttons: buttons,
                open: this.open
            });

            return this;
        },        
        open: function() {
            this.$('#title').val(this.model.get('title'));
            this.$('#color').val(this.model.get('color'));            
        },
        save: function() {
            this.model.set({'title': this.$('#title').val(), 'color': this.$('#color').val()});
            
            if (this.model.isNew()) {
                this.collection.create(this.model, {success: this.close});
            } else {
                this.model.save({}, {success: this.close});
            }
        },
        close: function() {
            this.$el.dialog('close');
        },
        destroy: function() {
            this.model.destroy({success: this.close});
        }        
    });
    
    var events = new Events();
    new EventsView({el: $('#fullcalendar'), collection: events}).render();
    events.fetch();
    
    console.log(window.calendarData);
	var Calendars = new Calendars(window.calendarData);
	new CalendarsView({collection: Calendars}).render();
	//Calendars.fetch();
});
});