<script>
var	defaultView  = '<?php echo $_['defaultView'];?>',
	eventSources = <?php echo json_encode($_['eventSources']) ?>,
	categories   = <?php echo json_encode($_['categories']); ?>,
	firstDay     = <?php echo $_['firstDay']; ?>,
	agendatime   = '<?php echo $_['agendatime']; ?>',
	defaulttime  = '<?php echo $_['defaulttime']; ?>',
	iseditable   = true;
</script>
<div id="notification" style="display:none;"></div>
<div id="calendars">
	<div id="calendarnavigation">
		<button class="button" id="currentdate"></button>
		<button class="button arrowbutton backward">&larr;</button>
		<button class="button today"><?php echo $l->t('Today');?></button>
		<button class="button arrowbutton forward">&rarr;</button>
		<br>
		<div id="views">
			<button class="button view" id="agendaWeek"><?php echo $l->t('Week');?></button>
			<button class="button view" id="basic2Weeks"><?php echo $l->t('2 Weeks');?></button>
			<button class="button view" id="basic4Weeks"><?php echo $l->t('Month');?></button>
			<button class="button view" id="list"><?php echo $l->t('List');?></button>
		</div>
	</div>
	<hr>
	<div id="calendarList"></div>
</div>
<div id="fullcalendar"></div>
<div id="appsettings" class="popup bottomright hidden"></div>