<!-- !load calendar data -->
<script type="text/javascript" src="<?php echo OCP\Util::linkToRoute('calendar_data');?>"></script>
<!-- !load view settings -->
<script type="text/javascript" src="<?php echo OCP\Util::linkToRoute('view_data');?>"></script>
<!-- !main calendar html stuff -->
<div id="app-navigation">
	<!-- selected date and available views -->
	<div id="selecteddate"></div>
	<!-- datepicker (slides down when user clicks on #selecteddate -->
	<div id="globaldatepicker"></div>
	<!-- buttons to navigate thru calendar -->
	<div id="datecontrol">
		<button class="button datecontrol" id="backward"><?php p($l->t('Previous'));?></button>
		<button class="button datecontrol" id="today"><?php p($l->t('Today'));?></button>
		<button class="button datecontrol" id="forward"><?php p($l->t('Next'));?></button>
	</div>
	<!-- <br> I am not yet 100% sure if this br fits in here -->
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
		<img src="<?php echo OCP\Util::imagePath('core', 'loading.gif'); ?>" alt="Loading" class="loading">
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