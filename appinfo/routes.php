<?php
$this->create('calendar_index', '/')
	->actionInclude('calendar/index.php');

// /ajax/
$this->create('calendar_changeview', 'ajax/changeview.php')
	->actionInclude('calendar/ajax/changeview.php');

$this->create('calendar_events', 'ajax/events.php')
	->actionInclude('calendar/ajax/events.php');

$this->create('calendar_search_location', 'ajax/search-location.php')
	->actionInclude('calendar/ajax/search-location.php');


// /ajax/scan
$this->create('calendar_cache_rescan', 'ajax/cache/rescan.php')
	->actionInclude('calendar/ajax/cache/rescan.php');

$this->create('claendar_cache_status', 'ajax/cache/status.php')
	->actionInclude('calendar/ajax/cache/status.php');


// /ajax/calendar
$this->create('calendar_calendar_activation', 'ajax/calendar/activation.php')
	->actionInclude('calendar/ajax/calendar/activation.php');

$this->create('calendar_calendar_delete', 'ajax/calendar/delete.php')
	->actionInclude('calendar/ajax/calendar/delete.php');

$this->create('calendar_calendar_edit_form', 'ajax/calendar/edit.form.php')
	->actionInclude('calendar/ajax/calendar/edit.form.php');

$this->create('calendar_calendar_edit', 'ajax/calendar/edit.php')
	->actionInclude('calendar/ajax/calendar/edit.php');

$this->create('calendar_calendar_new_form', 'ajax/calendar/new.form.php')
	->actionInclude('calendar/ajax/calendar/new.form.php');

$this->create('calendar_calendar_new', 'ajax/calendar/new.php')
	->actionInclude('calendar/ajax/calendar/new.php');

$this->create('calendar_calendar_overview', 'ajax/calendar/overview.php')
	->actionInclude('calendar/ajax/calendar/overview.php');

$this->create('calendar_calendar_update', 'ajax/calendar/update.php')
	->actionInclude('calendar/ajax/calendar/update.php');


// /ajax/categories
$this->create('calendar_categories_rescan', 'ajax/categories/rescan.php')
	->actionInclude('calendar/ajax/categories/rescan.php');


// /ajax/event
$this->create('calendar_event_delete', 'ajax/event/delete.php')
	->actionInclude('calendar/ajax/event/delete.php');

$this->create('calendar_event_edit_form', 'ajax/event/edit.form.php')
	->actionInclude('calendar/ajax/event/edit.form.php');

$this->create('calendar_event_edit', 'ajax/event/edit.php')
	->actionInclude('calendar/ajax/event/edit.php');

$this->create('calendar_event_move', 'ajax/event/move.php')
	->actionInclude('calendar/ajax/event/move.php');

$this->create('calendar_event_new_form', 'ajax/event/new.form.php')
	->actionInclude('calendar/ajax/event/new.form.php');

$this->create('calendar_event_new', 'ajax/event/new.php')
	->actionInclude('calendar/ajax/event/new.php');

$this->create('calendar_event_overview', 'ajax/event/resize.php')
	->actionInclude('calendar/ajax/event/resize.php');

$this->create('calendar_event_update', 'ajax/event/sendmail.php')
	->actionInclude('calendar/ajax/event/sendmail.php');


// /ajax/import
$this->create('calendar_import_calendarcheck', 'ajax/import/calendarcheck.php')
	->actionInclude('calendar/ajax/import/calendarcheck.php');

$this->create('calendar_import_dialog', 'ajax/import/dialog.php')
	->actionInclude('calendar/ajax/import/dialog.php');

$this->create('calendar_import_dropimport', 'ajax/import/dropimport.php')
	->actionInclude('calendar/ajax/import/dropimport.php');

$this->create('calendar_import_import', 'ajax/import/import.php')
	->actionInclude('calendar/ajax/import/import.php');


// /ajax/settings

$this->create('calendar_settings_getfirstday', 'ajax/settings/getfirstday.php')
	->actionInclude('calendar/ajax/settings/getfirstday.php');

$this->create('calendar_settings_gettimezonedetection', 'ajax/settings/gettimezonedetection.php')
	->actionInclude('calendar/ajax/settings/gettimezonedetection.php');

$this->create('calendar_settings_guesstimezone', 'ajax/settings/guesstimezone.php')
	->actionInclude('calendar/ajax/settings/guesstimezone.php');

$this->create('calendar_settings_setfirstday', 'ajax/settings/setfirstday.php')
	->actionInclude('calendar/ajax/settings/setfirstday.php');

$this->create('calendar_settings_settimeformat', 'ajax/settings/settimeformat.php')
	->actionInclude('calendar/ajax/settings/settimeformat.php');

$this->create('calendar_settings_settimezone', 'ajax/settings/settimezone.php')
	->actionInclude('calendar/ajax/settings/settimezone.php');

$this->create('calendar_settings_timeformat', 'ajax/settings/timeformat.php')
	->actionInclude('calendar/ajax/settings/timeformat.php');

$this->create('calendar_settings_timezonedetection', 'ajax/settings/timezonedetection.php')
	->actionInclude('calendar/ajax/settings/timezonedetection.php');


$this->create('calendar_root_calendar', 'calendar.php')
	->actionInclude('calendar/calendar.php');


$this->create('calendar_root_export', 'export.php')
	->actionInclude('calendar/export.php');


$this->create('calendar_root_settingswrapper', 'settingswrapper.php')
	->actionInclude('calendar/settingswrapper.php');

// /js
$this->create('calendar_js_idtype', 'js/idtype.php')
	->actionInclude('calendar/js/idtype.php');

$this->create('calendar_js_l10n', 'js/l10n.php')
	->actionInclude('calendar/js/l10n.php');
