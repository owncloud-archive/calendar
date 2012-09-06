<script>
var	defaultView = '<?php echo $_['defaultView'];?>',
	eventSources = <?php echo json_encode($_['eventSources']) ?>,
	categories = <?php echo json_encode($_['categories']); ?>,
	firstDay = <?php echo $_['firstDay']; ?>,
	agendatime = '<?php echo $_['agendatime']; ?>',
	defaulttime = '<?php echo $_['defaulttime']; ?>';
</script>
<div id="notification" style="display:none;"></div>
<div id="controls">
	<form id="view">
		<input type="button" value="<?php echo $l->t('Week');?>" id="week"/>
		<input type="button" value="<?php echo $l->t('Month');?>" id="month"/>
		<input type="button" value="<?php echo $l->t('List');?>" id="list"/>
		<input type="button" value="+" id="addEvent" />&nbsp;&nbsp;
		<img id="loading" src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>" />
	</form>
	<form id="choosecalendar">
		<button type="button">
			<a class="settings calendarsettings" title="<?php echo $l->t('Calendars'); ?>">
				<img class="svg" src="<?php echo OCP\Util::imagePath('calendar', 'icon.svg'); ?>" alt="<?php echo $l->t('Calendars'); ?>" />
			</a>
		</button>
		<button type="button">
			<a class="settings generalsettings" title="<?php echo $l->t('Settings'); ?>">
				<img class="svg" src="<?php echo OCP\Util::imagePath('core', 'actions/settings.svg'); ?>" alt="<?php echo $l->t('Settings'); ?>" />
			</a>
		</button>
	</form>
	<form id="datecontrol">
		<input type="button" value="&nbsp;&lt;&nbsp;" id="backward"/>
		<input type="button" id="date"/>
		<input type="button" value="&nbsp;&gt;&nbsp;" id="forward"/>
	</form>
</div>
<div id="fullcalendar"></div>
<div id="appsettings" class="popup topright hidden"></div>