<label for="active_<?php p($_['calendar']['id']) ?>" class="calendarLabel">
	<div class="calendarCheckbox<?php print_unescaped($_['calendar']['active'] ? '' : ' unchecked') ?>" id="checkbox_<?php p($_['calendar']['id']) ?>" style="background-color:<?php print_unescaped(($_['calendar']['calendarcolor']) ? $_['calendar']['calendarcolor'] : 'rgb(58, 135, 173)') ?>"></div>
	<?php p($_['calendar']['displayname']) ?>
	<?php if ($_['calendar']['userid'] == OCP\USER::getUser()) { ?>
		<input type="checkbox" id="active_<?php p($_['calendar']['id']) ?>" class="activeCalendar" data-id="<?php p($_['calendar']['id']) ?>" <?php print_unescaped($_['calendar']['active'] ? ' checked="checked"' : '') ?>>
	<?php } ?>

<span class="utils">
	<span class="action">
	<?php if ($_['calendar']['permissions'] & OCP\PERMISSION_SHARE) { ?>
		<a href="#" class="share icon-share permanent" data-item-type="calendar" data-item="<?php p($_['calendar']['id']); ?>"
		   data-possible-permissions="<?php p($_['calendar']['permissions']) ?>"
		   title="<?php p($l->t('Share Calendar')) ?>"></a>
	<?php } ?>
	</span>

	<?php
	   if ($_['calendar']['userid'] == OCP\USER::getUser()) {
		   $caldav = rawurlencode(html_entity_decode($_['calendar']['uri'], ENT_QUOTES, 'UTF-8'));
	   } else {
		   $caldav = rawurlencode(html_entity_decode($_['calendar']['uri'], ENT_QUOTES, 'UTF-8')) . '_shared_by_' . $_['calendar']['userid'];
	   }
	?>

	<span class="action">
	<a href="#" id="chooseCalendar-showCalDAVURL" data-user="<?php p(OCP\USER::getUser()) ?>" data-caldav="<?php p($caldav) ?>" title="<?php p($l->t('CalDav Link')) ?>" class="icon-public permanent"></a>
	</span>

	<span class="action">
	<a href="<?php print_unescaped(OCP\Util::linkTo('calendar', 'export.php') . '?calid=' . $_['calendar']['id']) ?>" title="<?php p($l->t('Download')) ?>" class="icon-download"></a>

	</span>

	<span class="action">
	<?php if ($_['calendar']['permissions'] & OCP\PERMISSION_UPDATE) { ?>
		<a href="#" id="chooseCalendar-edit" data-id="<?php p($_['calendar']['id']) ?>" title="<?php p($l->t('Edit')) ?>" class="icon-rename"></a>
	<?php } ?>

	</span>

	<span class="action">
	<?php if ($_['calendar']['permissions'] & OCP\PERMISSION_DELETE) { ?>
		<a href="#"  id="chooseCalendar-delete" data-id="<?php p($_['calendar']['id']) ?>" title="<?php p($l->t('Delete')) ?>" class="icon-delete"></a>
	<?php } ?>
	</span>
	
	
	<span class="action">
	<?php if($_['calendar']['permissions'] & OCP\PERMISSION_SHARE) { ?>
		<input type="checkbox" class="displayable-control hide" id="outer-share-link-calendar-<?php p($_['calendar']['id']) ?>"/>
		<div class="displayable" style="padding-left:0.5em">
		<?php
			/* internal calendar sharing interface */
			$tmpl = new OCP\Template('calendar', 'part.internalshare');
			$tmpl->assign('item_id', $_['calendar']['id']);
			$tmpl->assign('item_type', 'calendar');
			$tmpl->assign('permissions', $_['calendar']['permissions']);
			$tmpl->assign('shared_with', $_['shared_with']);
			$tmpl->printpage();
			/* public calendar link-sharing interface */
			$tmpl = new OCP\Template('calendar', 'part.linkshare');
			$tmpl->assign('item_id', $_['calendar']['id']);
			$tmpl->assign('item_type', 'calendar');
			$tmpl->assign('permissions', $_['calendar']['permissions']);
			$tmpl->assign('link_share', $_['link_share']);
			$tmpl->printpage();
		?>
		</div>
	<?php } ?>
	</span>
	
</span>
</label>