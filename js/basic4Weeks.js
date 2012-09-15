$.fullCalendar.views.basic4Weeks = basic4WeeksView;
 
function basic4WeeksView(element, calendar) {
	var t = this;
	
	// exports
	t.render = render;
	
	
	// imports
	$.fullCalendar.views.basic.call(t, element, calendar, 'month');
	var opt = t.opt;
	var renderBasic = t.renderBasic;
	var formatDate = calendar.formatDate;
	
	
	
	function render(date, delta) {
		if (delta) {
			$.fullCalendar.addDays(date, delta * 7);
		}
		var start = $.fullCalendar.cloneDate(date, true);
		var end = $.fullCalendar.addDays($.fullCalendar.cloneDate(start), 22);
		var visStart = $.fullCalendar.cloneDate(start);
		var visEnd = $.fullCalendar.cloneDate(end);
		var firstDay = opt('firstDay');
		var nwe = opt('weekends') ? 0 : 1;
		if (nwe) {
			$.fullCalendar.skipWeekend(visStart);
			$.fullCalendar.skipWeekend(visEnd, -1, true);
		}
		$.fullCalendar.addDays(visStart, -((visStart.getDay() - Math.max(firstDay, nwe) + 7) % 7));
		$.fullCalendar.addDays(visEnd, (7 - visEnd.getDay() + Math.max(firstDay, nwe)) % 7);
		t.name = 'basic4Weeks';
		t.title = $.fullCalendar.formatDate(start, opt('titleFormat'));
		t.start = start;
		t.end = end;
		t.visStart = visStart;
		t.visEnd = visEnd;
		renderBasic(4, 4, nwe ? 5 : 7, true);
		$('.fc-day-number').css('opacity', 1);
	}
	
	
}