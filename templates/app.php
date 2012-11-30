<script>
var	defaultView = '<?php echo $_['defaultView'];?>',
	eventSources = <?php echo json_encode($_['eventSources']) ?>,
	categories = <?php echo json_encode($_['categories']); ?>,
	firstDay = <?php echo $_['firstDay']; ?>,
	agendatime = '<?php echo $_['agendatime']; ?>',
	defaulttime = '<?php echo $_['defaulttime']; ?>';
</script>
<div id="notification" style="display:none;"></div><!--
<div id="controls">
	<form id="choosecalendar">
		<button type="button">
			<a class="settings generalsettings" title="<?php echo $l->t('Settings'); ?>">
				<img class="svg" src="<?php echo OCP\Util::imagePath('core', 'actions/settings.svg'); ?>" alt="<?php echo $l->t('Settings'); ?>" />
			</a>
		</button>
	</form>
	<div id="datecontrols">
		<form>
		</form>
	</div>
</div>-->
<div id="calendars">
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
	<hr>
	<!-- find better alternative to current + -->
	<p class="addCalendarButton" id="addNewCalendar">+</p>
	<h2><?php echo $l->t('Your calendars'); ?>:</h2>
	<?php
	if(count($_['calendars']) == 0){
		echo '<p class="info">' . $l->t('No calendars yet') . "</p>";
	}else{
		echo '<ul id="sortablecalendars">';
		foreach($_['calendars'] as $calendar){
			echo '<li class="ui-state-default">';
			echo '<span style="background-color: ' . $calendar['calendarcolor'] . ';">';
			echo '<input type="checkbox" style="background: ' . $calendar['calendarcolor'] . '"/>';
			echo '</span>';
			echo $calendar['displayname'];
			echo '</li>';
		}
		echo '</ul>';
	}
	?>
	<br>
	<!-- find better alternative to current + -->
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
<div id="appsettings" class="popup bottomright hidden"></div>