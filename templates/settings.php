<form id="calendar">
	<p><b><?php p($l->t('Your calendars')); ?>:</b></p>
	<table width="100%" style="border: 0;">
	<?php
	// get user's calendars
	$option_calendars = OC_Calendar_Calendar::allCalendars(OCP\USER::getUser());

	// let's look at them, one by one
	for($i = 0; $i < count($option_calendars); $i++) {

    // flags init
    $shared     = false;   // is the calendar shared at all (with or by the current user, including link-sharing)?
    $sharedBy   = false;   // is the calendar shared *with* the current user?
    $linkShare  = array(); // is the calendar publicly link-shared
    $sharedWith = array(); // with whom is the calendar shared within this ownCloud instance?

    // starting off the calendar row
		print_unescaped("<tr data-id='".OC_Util::sanitizeHTML($option_calendars[$i]['id'])."'>");

		// calendar row contents template
		$tmpl = new OCP\Template('calendar', 'part.choosecalendar.rowfields');

		// calendar data assigned to the template
		$tmpl->assign('calendar', $option_calendars[$i]);

		// is this owned by the user?
		if ($option_calendars[$i]['userid'] != OCP\User::getUser()) {
      // nope! apparently shared *with* the user!
			$sharedBy = OCP\Share::getItemSharedWithBySource('calendar', $option_calendars[$i]['id']);
    }

    // check sharing status
    $sw = OCP\Share::getItemShared('calendar', $option_calendars[$i]['id']);
    if(is_array($sw)) {
      foreach($sw as $share) {
        // sharing with a group or user, are we?
        if($share['share_type'] == OCP\Share::SHARE_TYPE_USER || $share['share_type'] == OCP\Share::SHARE_TYPE_GROUP) {
          // noted!
          $sharedWith[] = $share;
        // public link-sharing
        } elseif($share['share_type'] == OCP\Share::SHARE_TYPE_LINK) {
          // noted also!
          $linkShare = $share;
        }
      }
    }
    
    // set the shared flag -- true if shared by, link-shared, or shared with
    $shared = ( !empty($sharedBy) or !empty($linkShare) or !empty($sharedWith) );
    
    // shared/sharing info passed to the template
		$tmpl->assign('shared', $shared);
		$tmpl->assign('shared_by', $sharedBy);
		$tmpl->assign('link_share', $linkShare);
		$tmpl->assign('shared_with', $sharedWith);
		// share status icon
    if (!$shared) {
      // not shared
      $tmpl->assign('share_icon', OCP\Util::imagePath('core', 'actions/share.svg'));
    // link-shared
    } elseif (!empty($linkShare)) {
      $tmpl->assign('share_icon', OCP\Util::imagePath('core', 'actions/public.svg'));
    // shared
    } else {
      $tmpl->assign('share_icon', OCP\Util::imagePath('core', 'actions/shared.svg'));
    }
		// print the template, yo
		$tmpl->printpage();
		// finish the job
		print_unescaped("</tr>");
	}
	?>
	<tr>
		<td width="20px">
			<input type="checkbox" id="active_shared_events" data-id="shared_events" checked="checked">
		</td>
		<td id="<?php p(OCP\USER::getUser()) ?>_shared_events">
			<label for="active_shared_events"><?php p($l->t('Shared events')) ?></label>
		</td>
	</tr>
	<tr>
		<td colspan="6">
			<input type="button" value="<?php p($l->t('New Calendar')) ?>" id="newCalendar">
		</td>
	</tr>
	<tr>
		<td colspan="6">
			<p style="margin: 0 auto;width: 90%;"><input style="display:none;width: 90%;float: left;" type="text" id="caldav_url" title="<?php p($l->t("CalDav Link")); ?>"><img id="caldav_url_close" style="height: 20px;vertical-align: middle;display: none;" src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')) ?>" alt="close"/></p>
		</td>
	</tr>
	</table><br>
	</fieldset>
</form>
<h2 id="title_general"><?php p($l->t('General')); ?></h2>
<div id="general">
	<table class="nostyle">
		<tr>
			<td>
				<label for="timezone" class="bold"><?php p($l->t('Timezone'));?></label>
				&nbsp;&nbsp;
			</td>
			<td>
				<select style="display: none;" id="timezone" name="timezone">
				<?php
				$continent = '';
				foreach($_['timezones'] as $timezone):
					$ex=explode('/', $timezone, 2);//obtain continent,city
					if (!isset($ex[1])) {
						$ex[1] = $ex[0];
						$ex[0] = "Other";
					}
					if ($continent!=$ex[0]):
						if ($continent!="") print_unescaped('</optgroup>');
						print_unescaped('<optgroup label="'.OC_Util::sanitizeHTML($ex[0]).'">');
					endif;
					$city=strtr($ex[1], '_', ' ');
					$continent=$ex[0];
					print_unescaped('<option value="'.OC_Util::sanitizeHTML($timezone).'"'.($_['timezone'] == $timezone?' selected="selected"':'').'>'.OC_Util::sanitizeHTML($city).'</option>');
				endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;&nbsp;
			</td>
			<td>
				<input type="checkbox" name="timezonedetection" id="timezonedetection">
				&nbsp;
				<label for="timezonedetection"><?php p($l->t('Update timezone automatically')); ?></label>
			</td>
		</tr>
		<tr>
			<td>
				<label for="timeformat" class="bold"><?php p($l->t('Time format'));?></label>
				&nbsp;&nbsp;
			</td>
			<td>
				<select style="display: none; width: 60px;" id="timeformat" title="<?php p("timeformat"); ?>" name="timeformat">
					<option value="24" id="24h"><?php p($l->t("24h")); ?></option>
					<option value="ampm" id="ampm"><?php p($l->t("12h")); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="firstday" class="bold"><?php p($l->t('Start week on'));?></label>
				&nbsp;&nbsp;
			</td>
			<td>
				<select style="display: none;" id="firstday" title="<?php p("First day"); ?>" name="firstday">
					<option value="mo" id="mo"><?php p($l->t("Monday")); ?></option>
					<option value="su" id="su"><?php p($l->t("Sunday")); ?></option>
					<option value="sa" id="sa"><?php p($l->t("Saturday")); ?></option>
				</select>
			</td>
		</tr>
		<tr class="advancedsettings">
			<td>
				<label for="" class="bold"><?php p($l->t('Cache'));?></label>
				&nbsp;&nbsp;
			</td>
			<td>
				<input id="cleancalendarcache" type="button" class="button" value="<?php p($l->t('Clear cache for repeating events'));?>">
			</td>
		</tr>
	</table>
</div>
<h2 id="title_urls"><?php p($l->t('URLs')); ?></h2>
<div id="urls">
		<?php p($l->t('Calendar CalDAV syncing addresses')); ?> (<a href="http://owncloud.org/synchronisation/" target="_blank" class="link"><?php p($l->t('more info')); ?></a>)
		<dl>
		<dt><?php p($l->t('Primary address (Kontact et al)')); ?></dt>
		<dd><input type="text" style="width: 90%;float: left;" value="<?php print_unescaped(OCP\Util::linkToRemote('caldav')); ?>" readonly></dd>
		<dt><?php p($l->t('iOS/OS X')); ?></dt>
		<dd><input type="text" style="width: 90%;float: left;" value="<?php print_unescaped(OCP\Util::linkToRemote('caldav')); ?>principals/<?php p(urlencode(OCP\USER::getUser())); ?>/" readonly></dd>
		<dt><?php p($l->t('Read only iCalendar link(s)')); ?></dt>
		<dd>
			<?php foreach($_['calendars'] as $calendar) {
			if($calendar['userid'] == OCP\USER::getUser()){
				$uri = rawurlencode(html_entity_decode($calendar['uri'], ENT_QUOTES, 'UTF-8'));
			}else{
				$uri = rawurlencode(html_entity_decode($calendar['uri'], ENT_QUOTES, 'UTF-8')) . '_shared_by_' . $calendar['userid'];
			}
			?>
			<a href="<?php p(OCP\Util::linkToRemote('caldav').'calendars/'.urlencode(OCP\USER::getUser()).'/'.urlencode($uri)) ?>?export" class="link"><?php p($calendar['displayname']) ?></a><br />
			<?php } ?>
		</dd>
		</dl>
	</div>
</div>
