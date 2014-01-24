<?php /*
<div id="dropdown" class="drop" data-item-type="calendar" data-item-source="1">

  <input id="shareWith" type="text" placeholder="Share with user or group …" class="ui-autocomplete-input" autocomplete="off">

  <ul id="shareWithList">
    <li style="clear: both;" data-share-type="1" data-share-with="test-group" title="test-group">
      <a href="#" class="unshare">
        <img class="svg" alt="Unshare" src="/core/img/actions/delete.svg">
      </a>
      <span class="username">test-group (group)</span>
      <label>
        <input type="checkbox" name="edit" class="permissions" checked="checked">can edit<a href="#" class="showCruds">
          <img class="svg" alt="access control" src="/core/img/actions/triangle-s.svg">
        </a>
      </label>
      <div class="cruds" style="display:none;">
        <label><input type="checkbox" name="create" class="permissions" checked="checked" data-permissions="4">create</label>
        <label><input type="checkbox" name="update" class="permissions" checked="checked" data-permissions="2">update</label>
        <label><input type="checkbox" name="delete" class="permissions" checked="checked" data-permissions="8">delete</label>
        <label><input type="checkbox" name="share" class="permissions" checked="checked" data-permissions="16">share</label>
      </div>
    </li>
    <li style="clear: both;" data-share-type="0" data-share-with="Tester" title="Tester">
      <a href="#" class="unshare">
        <img class="svg" alt="Unshare" src="/core/img/actions/delete.svg">
      </a>
      <span class="username">Tester</span>
      <label>
        <input type="checkbox" name="edit" class="permissions" checked="checked">can edit<a href="#" class="showCruds">
          <img class="svg" alt="access control" src="/core/img/actions/triangle-s.svg">
        </a>
      </label>
      <div class="cruds" style="display:none;">
        <label><input type="checkbox" name="create" class="permissions" checked="checked" data-permissions="4">create</label>
        <label><input type="checkbox" name="update" class="permissions" checked="checked" data-permissions="2">update</label>
        <label><input type="checkbox" name="delete" class="permissions" checked="checked" data-permissions="8">delete</label>
        <label><input type="checkbox" name="share" class="permissions" checked="checked" data-permissions="16">share</label>
      </div>
    </li>
  </ul>

</div>



<div id="tabs-5" aria-labelledby="ui-id-4" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="false" style="display: block;">
  
<input type="text" id="sharewith" placeholder="Share with user or group" data-item-source="1" class="ui-autocomplete-input" autocomplete="off"><span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span>

<ul class="sharedby eventlist">
  <li data-share-with="Tester" data-item="1" data-item-type="event" data-link="true" data-permissions="17" data-share-type="0">Tester<span class="shareactions">
    <label><input class="update" type="checkbox">can edit</label>
    <label><input class="share" type="checkbox" checked="&quot;checked&quot;">can share</label>
    <img src="/core/img/actions/delete.svg" class="svg action delete" title="Unshare">
  </span></li>
</ul>*/ ?>


<div class="share-interface-container internal-share">
  <input type="text" class="share-with ui-autocomplete-input"
    placeholder="<?php p($l->t('Share with user or group')); ?>"
    data-item-source="<?php p($_['item_id']); ?>" data-item-type="<?php p($_['item_type']); ?>" autocomplete="off" />

  <ul class="shared-with-list">
  <?php foreach($_['shared_with'] as $i => $sharee): ?>
    <li
      data-share-type="1"
      data-share-with="<?php p($sharee['share_with']); ?>"
      data-item="<?php p($_['item_id']); ?>"
      data-item-type="<?php p($_['item_type']); ?>"
      data-link="true"
      data-permissions="<?php p($_['permissions']) ?>"
      title="<?php p($sharee['share_with']); ?>">
      <!-- the username -->
      <span class="username"><?php p($sharee['share_with'] . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_GROUP ? ' (group)' : '')); ?></span>
      <!-- unshare link -->
      <a href="#" class="unshare">
        <img class="svg" alt="<?php p($l->t('Unshare')); ?>" src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')); ?>">
      </a>
      <div class="displayable-container share-options">
        <!-- "can edit" info checkbox -->
        <input type="checkbox" name="edit" class="permissions" checked="checked" disabled="disabled"/>
        <!-- "can edit" displayable-control label -->
        <label for="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><?php p($l->t('can edit')); ?><img class="svg" alt="access control" src="<?php p(OCP\Util::imagePath('core', 'actions/triangle-s.svg')); ?>"></label>
        <label class="share-label"><input type="checkbox" name="share" class="permissions" data-permissions="16" <?php p(($sharee['permissions'] & OCP\PERMISSION_SHARE?'checked="checked"':''))?>><?php p($l->t('can share')); ?></label>
        <!-- edit options displayable control and displayable itself -->
        <input type="checkbox" class="displayable-control hide" name="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>" id="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"/>
        <div class="displayable edit-options">
          <label><input type="checkbox" name="create" class="permissions" data-permissions="4" <?php p(($sharee['permissions'] & OCP\PERMISSION_CREATE?'checked="checked"':''))?>><?php p($l->t('create')); ?></label>
          <label><input type="checkbox" name="update" class="permissions" data-permissions="2" <?php p(($sharee['permissions'] & OCP\PERMISSION_UPDATE?'checked="checked"':''))?>><?php p($l->t('update')); ?></label>
          <label><input type="checkbox" name="delete" class="permissions" data-permissions="8" <?php p(($sharee['permissions'] & OCP\PERMISSION_DELETE?'checked="checked"':''))?>><?php p($l->t('delete')); ?></label>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
  </ul>
</div>



<?php /*
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
         
      </label>
      <label>
        <input class="share" type="checkbox" >
         <?php p($l->t('can share')); ?>
      </label>
      <img src="" class="svg action delete"
        title="">
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
<br /> */ ?>