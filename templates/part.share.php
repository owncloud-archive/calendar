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

/* sharing an event internally */
$tmpl = new OCP\Template('calendar', 'part.internalshare');
$tmpl->assign('item_id', $_['eventid']);
$tmpl->assign('item_type', 'event');
$tmpl->assign('permissions', $_['permissions']);
$tmpl->assign('basic_edit_options', true);
$tmpl->assign('shared_with', $eventsharees);
$tmpl->printpage();
/* link-sharing an event */
$tmpl = new OCP\Template('calendar', 'part.linkshare');
$tmpl->assign('item_id', $_['eventid']);
$tmpl->assign('item_type', 'event');
$tmpl->assign('permissions', $_['permissions']);
$tmpl->assign('link_share', $linkShare);
$tmpl->printpage();
/* end link-sharing an event */
?><br />
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
<?php if(!$calsharees and !$linkSharedCalendar) {
	$nobody = $l->t('Not shared with anyone via calendar');
	print_unescaped('<div>' . OC_Util::sanitizeHTML($nobody) . '</div>');
} ?>
