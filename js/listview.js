/**
 * Copyright (c) 2011 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

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

	function setHeight(height, dateChanged) {
	}

	function setWidth(width) {
	}

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
			event.title +
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