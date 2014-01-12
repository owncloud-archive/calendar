<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('calendar/js', 'l10n.php'));?>"></script>

<div id="notification" style="display:none;"></div>
<div id="app-navigation">
	<ul id="navigation-list">
		<li>
				<div id="datecontrol_current"></div><input type="button" value="&nbsp;&lt;&nbsp;" id="datecontrol_left"/><input type="button" value="&nbsp;&gt;&nbsp;" id="datecontrol_right"/>
		</li>
		<li>
			<form id="datecontrol">
				<div id="datecontrol_date"></div>
			</form>
		</li>
		<li>
			<form id="view">
				<input type="button" value="<?php p($l->t('Day'));?>" id="onedayview_radio"/><input type="button" value="<?php p($l->t('Week'));?>" id="oneweekview_radio"/><input type="button" value="<?php p($l->t('Month'));?>" id="onemonthview_radio"/>&nbsp;&nbsp;
				<input type="button" value="<?php p($l->t('Today'));?>" id="datecontrol_today"/>
				<img id="loading" src="<?php print_unescaped(OCP\Util::imagePath('calendar', 'loading.gif')); ?>" />
			</form>
			<!--
			<form id="choosecalendar">
				<button class="settings generalsettings" title="<?php p($l->t('Settings')); ?>"><img class="svg" src="<?php print_unescaped(OCP\Util::imagePath('core', 'actions/settings.svg')); ?>" alt="<?php p($l->t('Settings')); ?>" /></button>
			</form>-->
		</li>
	</ul>
	<div id="app-settings">
			<div id="app-settings-header">
				<button class="settings-button" tabindex="0"></button>
			</div>
			<div id="app-settings-content">
				Test 123
			</div>
		</div>
</div>
<div id="fullcalendar"></div>
<div id="dialog_holder"></div>
<div id="appsettings" class="popup topright hidden"></div>
