<!-- !Load some translations -->
<script type="text/javascript" src="<?php echo OC_Helper::linkTo('calendar/js', 'l10n.php');?>"></script>
<!-- !load some settings -->
<script type="text/javascript" src="<?php echo OC_Helper::linkTo('calendar/js', 'cfg.php');?>"></script>
<!-- !main calendar html stuff -->
<div id="app-navigation">
	<!-- selected date and available views -->
	<div id="selecteddate"></div>
	<!-- datepicker (slides down when user clicks on #selecteddate -->
	<div id="globaldatepicker"></div>
	<!-- buttons to navigate thru calendar -->
	<div id="datecontrol">
		<button class="button datecontrol" id="backward"><?php p($l->t('Backward'));?></button>
		<button class="button datecontrol" id="today"><?php p($l->t('Today'));?></button>
		<button class="button datecontrol" id="forward"><?php p($l->t('Forward'));?></button>
	</div>
	<br>
	<!-- available views -->
	<div id="views">
		<button class="button view" id="agendaWeek"><?php p($l->t('Week'));?></button>
		<button class="button view" id="basic2Weeks"><?php p($l->t('2 Weeks'));?></button>
		<button class="button view" id="basic4Weeks"><?php p($l->t('Month'));?></button>
		<button class="button view" id="list"><?php p($l->t('List'));?></button>
	</div>
	<br>
	<!-- list of calendars and subscriptions -->
	<div id="calendarlist">
		<!-- Calendars - calendar with read & write support -->
		<span><?php p($l->t('Calendars'));?>:</span>
		<ul id="calendars" droppable></ul>
		<br>
		<!-- Subscriptions - calendar with read support only -->
		<span><?php p($l->t('Subscriptions'));?>:</span>
		<ul id="subscriptions" droppable></ul>
	</div>
	<!-- app settings -->
	<div id="app-settings">
		<div id="app-settings-header">
			<button name="app settings" 
					class="settings-button"
					oc-click-slide-toggle="{
						selector: '#app-settings-content',
						hideOnFocusLost: true,
						cssClass: 'opened'
					}"></button>
		</div>
		<div id="app-settings-content">
			<?php print_unescaped($this->inc('part.settings')) ?>
		</div>
	</div>
</div>
<div id="app-content">
	<div id="fullcalendar"></div>
</div>