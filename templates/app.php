<!-- !Load some translations -->
<script type="text/javascript" src="<?php echo OC_Helper::linkTo('calendar/js', 'l10n.php');?>"></script>
<!-- !load some settings -->
<script type="text/javascript" src="<?php echo OC_Helper::linkTo('calendar/js', 'loader.php');?>"></script>
<!-- !main calendar html stuff-->
<div id="notification" style="display:none;"></div>
<div id="calendarsidebar">
	<div id="calendarlist"></div>
	<div class="bottom">
		<div id="selecteddate" class="ribbon"></div>
		<div id="views">
			<button class="button view" id="agendaWeek"><?php echo $l->t('Week');?></button>
			<button class="button view" id="list"><?php echo $l->t('List');?></button>
			<button class="button view" id="basic2Weeks"><?php echo $l->t('2 Weeks');?></button>
			<button class="button view" id="basic4Weeks"><?php echo $l->t('Month');?></button>
		</div>
		<div id="globaldatepicker"></div>
	</div>
</div>
<div id="fullcalendar"></div>