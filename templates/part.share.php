<?php
$calid = isset($_['calendar']) ? $_['calendar'] : null;
$eventid = isset($_['eventid']) ? $_['eventid'] : null;
$location = isset($_['location']) ? $_['location'] : null;
$description = isset($_['description']) ? $_['description'] : null;
$dtstart = isset($_['dtstart']) ? $_['dtstart'] : null;
$dtend = isset($_['dtend']) ? $_['dtend'] : null;

$calsharees = array();
$eventsharees = array();

// "event is publicly shared via a link-shared calendar" flag
$linkSharedCalendar = false;

// "event is publicly link-shared itself" flag/data array
$linkShare = array();

$sharedwithByCalendar = OCP\Share::getItemShared('calendar', $calid);
$sharedwithByEvent = OCP\Share::getItemShared('event', $eventid);


if(is_array($sharedwithByCalendar)) {
	foreach($sharedwithByCalendar as $share) {
		if($share['share_type'] == OCP\Share::SHARE_TYPE_USER || $share['share_type'] == OCP\Share::SHARE_TYPE_GROUP) {
			$calsharees[] = $share;
    // public link-sharing
		} elseif($share['share_type'] == OCP\Share::SHARE_TYPE_LINK) {
      $linkSharedCalendar = true;
		}
	}
}
if(is_array($sharedwithByEvent)) {
	foreach($sharedwithByEvent as $share) {
		if($share['share_type'] == OCP\Share::SHARE_TYPE_USER || $share['share_type'] == OCP\Share::SHARE_TYPE_GROUP) {
			$eventsharees[] = $share;
		} else {
      $linkShare = $share;
		}
	}
}

?>

<input type="text" id="sharewith"
	placeholder="<?php p($l->t('Share with user or group')); ?>"
	data-item-source="<?php p($eventid); ?>" />

<ul class="sharedby eventlist">
<?php foreach($eventsharees as $sharee): ?>
	<li data-share-with="<?php p($sharee['share_with']); ?>"
		data-item="<?php p($eventid); ?>"
		data-item-type="event"
		data-link="true"
		data-permissions="<?php p($sharee['permissions']); ?>"
		data-share-type="<?php p($sharee['share_type']); ?>">
		<?php p($sharee['share_with'] . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_GROUP ? ' (group)' : '')); ?>
		<span class="shareactions">
			<label>
				<input class="update" type="checkbox" <?php p(($sharee['permissions'] & OCP\PERMISSION_UPDATE?'checked="checked"':''))?>>
				 <?php p($l->t('can edit')); ?>
			</label>
			<label>
				<input class="share" type="checkbox" <?php p(($sharee['permissions'] & OCP\PERMISSION_SHARE?'checked="checked"':''))?>>
				 <?php p($l->t('can share')); ?>
			</label>
			<img src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')); ?>" class="svg action delete"
				title="<?php p($l->t('Unshare')); ?>">
		</span>
	</li>
<?php endforeach; ?>
</ul>
<?php if(!$eventsharees) {
	$nobody = $l->t('Not shared with anyone');
	print_unescaped('<div id="sharedWithNobody">' . OC_Util::sanitizeHTML($nobody) . '</div>');
} ?>
<br />
<input type="button" id="sendemailbutton" style="float:right;" class="submit" value="<?php p($l->t("Send Email")); ?>" data-eventid="<?php p($eventid);?>" data-location="<?php p($location);?>" data-description="<?php p($description);?>" data-dtstart="<?php p($dtstart);?>" data-dtend="<?php p($dtend);?>">
<br />
<!-- link-sharing an event -->
<noscript><style>
  /* if JS is disabled, .share-link-form-submit will be available,
     hence .share-link-e-mail-send is un-needed*/
  .share-link-e-mail-send {
    display:none;
  }
</style></noscript>
<div class="share-link-container displayable-container"
     data-item-type="event"
     data-item="<?php p($_['eventid']); ?>"
     data-possible-permissions="<?php p($_['permissions']) ?>"
     data-link="true"
     data-js-enabled="javascript:alert('test')">
  <!-- the checkbox that enables and disables the whole thing -->
  <form>
  <h3><?php p($l->t('Share via link')); ?></h3>
  <input type="checkbox" name="share-link" class="share-link displayable-control" value="0" id="share-link-event-<?php p($_['eventid']); ?>" <?php if (isset($linkShare['token'])): ?> checked="checked"<?php endif; ?>/>
  <label for="share-link-event-<?php p($_['eventid']); ?>"><?php p($l->t('Share link')) ?></label>
  <!-- this should be visible only when the share-link checkbox is :checked -->
  <div class="share-link-enabled-container displayable">
    <!-- link container, contains the share link (duh) -->
    <input class="link-text" type="text" readonly="readonly" placeholder="<?php p($l->t('Sharing link will appear here')) ?>" value="<?php if ($linkShare['token']) { p(OCP\Util::linkToPublic('calendar') . '&t=' . $linkShare['token']); } ?>"/>
    <!-- do we want the password shown? default: nope -->
    <div class="password-protect-outer-container displayable-container">
      <input type="checkbox" name="password-protect" class="password-protect displayable-control" value="0" id="password-protect-event-<?php p($_['eventid']); ?>" <?php if (isset($linkShare['share_with'])): ?> checked="checked"<?php endif; ?>/>
      <label for="password-protect-event-<?php p($_['eventid']); ?>" class="password-protect-label"><?php p($l->t('Password protect')) ?></label>
      <div class="password-container displayable">
        <input class="share-link-password" type="password" placeholder="<?php if (isset($linkShare['share_with'])) { p($l->t('Password protected')); } else { p($l->t('Password')); }  ?>" name="share-link-password"/>
      </div>
    </div>
    <!-- do we want share expiration date? -->
    <div class="expire-date-outer-container displayable-container">
      <input type="checkbox" name="expire" class="expire displayable-control" value="0" id="expire-event-<?php p($_['eventid']); ?>" <?php if (isset($linkShare['expiration'])): ?> checked="checked"<?php endif; ?>/>
      <label for="expire-event-<?php p($_['eventid']); ?>" class="expire-label"><?php p($l->t('Set expiration date')) ?></label>
      <div class="expire-date-container displayable">
        <input class="expire-date" type="date" placeholder="<?php p($l->t('Expiration date')) ?>" name="expire-date" value="<?php if (isset($linkShare['expiration'])) { p(substr($linkShare['expiration'], 0, 10)); } ?>"/>
      </div>
    </div>
    <!-- link email form -->
    <div class="e-mail-form-container">
      <input class="share-link-e-mail-address" value="" placeholder="<?php p($l->t('Email link to person')) ?>" type="e-mail"/>
      <!-- the send e-mail submit button (unneeded and invisible if JS is disabled) -->
      <input class="share-link-e-mail-send" type="submit" value="<?php p($l->t('Send')) ?>"/>
    </div>
    <!-- the submit button (unneeded and invisible if JS is enabled) -->
    <noscript><input class="share-link-form-submit" type="submit" value="<?php p($l->t('Submit link-sharing settings')) ?>"/></noscript>
  </div>
  </form>
</div>
<!-- end link-sharing an event -->
<br />
<strong><?php p($l->t('Shared via calendar')); ?></strong>
<ul class="sharedby calendarlist">
<?php foreach($calsharees as $sharee): ?>
	<li data-share-with="<?php p($sharee['share_with']); ?>"
		data-item="<?php p($calid); ?>"
		data-item-type="calendar"
		data-link="true"
		data-permissions="<?php p($sharee['permissions']); ?>"
		data-share-type="<?php p($sharee['share_type']); ?>">
		<?php p($sharee['share_with'] . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_GROUP ? ' (group)' : '')); ?>
		<span class="shareactions">
			<label>
				<input class="update" type="checkbox"
					<?php p(($sharee['permissions'] & OCP\PERMISSION_UPDATE?'checked="checked"':''))?>
					disabled="disabled">
				<?php p($l->t('can edit')); ?>
			</label>
			<label>
				<input class="share" type="checkbox"
					<?php p(($sharee['permissions'] & OCP\PERMISSION_SHARE?'checked="checked"':''))?>
					disabled="disabled">
				<?php p($l->t('can share')); ?>
			</label>
		</span>
	</li>
<?php endforeach; ?>
<?php if ($linkSharedCalendar): ?>
  <li data-share-with=""
    data-item=""
    data-item-type="calendar"
    data-link="true"
    data-permissions=""
    data-share-type=""
    style="text-align:center;">
    <em><strong><?php p($l->t('This event is publicly link-shared via the calendar.')); ?></strong></em>
  </li>
<?php endif; ?>
</ul>
<?php if(!$calsharees) {
	$nobody = $l->t('Not shared with anyone via calendar');
	print_unescaped('<div>' . OC_Util::sanitizeHTML($nobody) . '</div>');
} ?>
