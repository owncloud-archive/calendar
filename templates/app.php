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
		<input type="button" value="<?php echo $l->t('Week');?>" id="agendaWeek" class="controlitem" />
		<input type="button" value="<?php echo $l->t('2 Weeks');?>" id="basic2Weeks" class="controlitem" />
		<input type="button" value="<?php echo $l->t('Month');?>" id="basic4Weeks" class="controlitem" />
		<input type="button" value="<?php echo $l->t('List');?>" id="list" class="controlitem" />
		<input type="button" value="+" id="addEvent" />&nbsp;&nbsp;
		<img id="loading" src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>" />
	</form>
	<form id="choosecalendar">
		<button type="button">
			<a class="settings generalsettings" title="<?php echo $l->t('Settings'); ?>">
				<img class="svg" src="<?php echo OCP\Util::imagePath('core', 'actions/settings.svg'); ?>" alt="<?php echo $l->t('Settings'); ?>" />
			</a>
		</button>
	</form>
	<div id="datecontrols">
		<form>
			<span class="button controlitem" id="backward">&nbsp;&lt;&nbsp;</span>
			<span class="button controlitem" id="current_date"/></span>
			<input type="hidden" id="date" />
			<span class="button controlitem" id="forward">&nbsp;&gt;&nbsp;</span>
		</form>
	</div>
</div>
<div id="calendars">
	<p class="addCalendarButton" id="addNewCalendar">+</p>
	<h2><?php echo $l->t('Your calendars'); ?>:</h2>
	<?php
	if(count($_['calendars']) == 0){
		echo '<p class="info">' . $l->t('No calendars yet') . "</p>";
	}else{
		echo '<ul id="sortablecalendars">';
		foreach($_['calendars'] as $calendar){
			echo '<li class="ui-state-default">' . $calendar['displayname'] . '</li>';
		}
		echo '</ul>';
	}
	?>
	<br><br>
	<p class="addCalendarButton" id="addNewSubscription">+</p>
	<h2><?php echo $l->t('Your subscriptions');?>:</h2>
	<?php
	if(count($_['subscriptions']) == 0){
		echo '<p class="info">' . $l->t('No subscriptions yet') . "</p>";
	}else{
		
	}
	?>
	
	
</div>
<div id="fullcalendar"></div>
<div id="appsettings" class="popup topright hidden"></div>