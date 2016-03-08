<?php
/** @var array $_ */

$calendarColor = $_['calendar']['calendarcolor'];
if (!preg_match('/^#([a-fA-F0-9]){3}(([a-fA-F0-9]){3})$/i', $calendarColor)) {
	$calendarColor = 'rgb(58, 135, 173)';
}
?>

<label for="active_<?php p($_['calendar']['id']) ?>" class="calendarLabel">
	<div class="calendarCheckbox<?php print_unescaped($_['calendar']['active'] ? '' : ' unchecked') ?>" id="checkbox_<?php p($_['calendar']['id']) ?>" style="background-color:<?php print_unescaped($calendarColor) ?>"></div>
	<?php p($_['calendar']['displayname']) ?>
	<?php if ($_['calendar']['userid'] == OCP\USER::getUser()) { ?>
		<input type="checkbox" id="active_<?php p($_['calendar']['id']) ?>" class="activeCalendar" data-id="<?php p($_['calendar']['id']) ?>" <?php print_unescaped($_['calendar']['active'] ? ' checked="checked"' : '') ?>>
	<?php } ?>
</label>
<div class="app-navigation-entry-utils">
  <ul>
    <li class="app-navigation-entry-utils-counter">
	<?php if ($_['calendar']['permissions'] & OCP\PERMISSION_SHARE) { ?>
		<a href="#" class="share icon-share permanent" data-item-type="calendar" data-item="<?php p($_['calendar']['id']); ?>"
		   data-possible-permissions="<?php p($_['calendar']['permissions']) ?>"
		   title="<?php p($l->t('Share Calendar')) ?>"></a>
	<?php } ?>
    </li><li>
	<?php
	   if ($_['calendar']['userid'] == OCP\USER::getUser()) {
		   $caldav = rawurlencode(html_entity_decode($_['calendar']['uri'], ENT_QUOTES, 'UTF-8'));
	   } else {
		   $caldav = rawurlencode(html_entity_decode($_['calendar']['uri'], ENT_QUOTES, 'UTF-8')) . '_shared_by_' . $_['calendar']['userid'];
	   }
	?>
    </li>
    <li class="app-navigation-entry-utils-menu-button svg"><button></button></li>
    </ul>
</div>
<div class="app-navigation-entry-menu">
    <ul>
	<li>
	<a href="#" id="chooseCalendar-showCalDAVURL" data-user="<?php p(OCP\USER::getUser()) ?>" data-caldav="<?php p($caldav) ?>" title="<?php p($l->t('CalDav Link')) ?>" class="icon-public permanent"></a>
	</li>

	<li>
	<a href="<?php print_unescaped(OCP\Util::linkTo('calendar', 'export.php') . '?calid=' . $_['calendar']['id']) ?>" title="<?php p($l->t('Download')) ?>" class="icon-download"></a>

	</li>

	<li>
	<?php if ($_['calendar']['permissions'] & OCP\PERMISSION_UPDATE) { ?>
		<a href="#" id="chooseCalendar-edit" data-id="<?php p($_['calendar']['id']) ?>" title="<?php p($l->t('Edit')) ?>" class="icon-rename"></a>
	<?php } ?>

	</li>

	<li>
	<?php if ($_['calendar']['permissions'] & OCP\PERMISSION_CREATE) { ?>
		<a href="#" id="chooseCalendar-make-def" data-id="<?php p($_['calendar']['id']) ?>" title="<?php p($l->t('Set as Default Calendar')) ?>" <?php OCP\Config::getUserValue(OCP\User::getUser(), 'calendar', 'defaultcalendar')==$_['calendar']['id']? print_unescaped('class="icon-default"') : print_unescaped('class="icon-default not-set"')?>></a>
	<?php } ?>

	</li>

	<li>
	<?php if ($_['calendar']['permissions'] & OCP\PERMISSION_DELETE) { ?>
		<a href="#"  id="chooseCalendar-delete" data-id="<?php p($_['calendar']['id']) ?>" title="<?php p($l->t('Delete')) ?>" class="icon-delete"></a>
	<?php } ?>
	</li>
    </ul>
</div>