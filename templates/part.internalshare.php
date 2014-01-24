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
      title="<?php p($sharee['share_with']); ?>"
      class="shared-with-entry-container">
      <!-- the username -->
      <span class="username"><?php p($sharee['share_with'] . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_GROUP ? ' (group)' : '')); ?></span>
      <!-- unshare link -->
      <a href="#" class="unshare">
        <img class="svg" alt="<?php p($l->t('Unshare')); ?>" src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')); ?>">
      </a>
      <div class="displayable-container share-options">
        <!--
          "can edit" checkbox
           - if the item type allows for precise sharing settings (update, create, delete), just an info box, readonly/disabled
           - if the item type allows only for editable/uneditable (actually, just update) setting, a valid checkbox
          this is checked via $_['basic_edit_options'] flag (if set and true, only editable/uneditable setting available)
        -->
        <input type="checkbox" class="permissions" 
          <?php if(empty($_['basic_edit_options'])): ?>
            name="edit" checked="checked" disabled="disabled"
          <?php else: ?>
            name="update" <?php p(($sharee['permissions'] & OCP\PERMISSION_UPDATE?'checked="checked"':''))?> id="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"
          <?php endif; ?>
        />
        <!-- "can edit" displayable-control label -->
        <label for="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><?php p($l->t('can edit')); ?><?php if(empty($_['basic_edit_options'])): ?><img class="svg" alt="access control" src="<?php p(OCP\Util::imagePath('core', 'actions/triangle-s.svg')); ?>"><?php endif; ?></label>
        <!-- "can share" label and checkbox -->
        <label class="share-label"><input type="checkbox" name="share" class="permissions" data-permissions="16" <?php p(($sharee['permissions'] & OCP\PERMISSION_SHARE?'checked="checked"':''))?>><?php p($l->t('can share')); ?></label>
        <!-- if we only have basic edit options available, there is no need for the advanced edit options controls, right? display these only when not in basic edit options regime -->
        <?php if(empty($_['basic_edit_options'])): ?>
          <!-- edit options displayable control and displayable itself -->
          <input type="checkbox" class="displayable-control hide" name="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>" id="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"/>
          <div class="displayable edit-options">
            <label><input type="checkbox" name="create" class="permissions" data-permissions="4" <?php p(($sharee['permissions'] & OCP\PERMISSION_CREATE?'checked="checked"':''))?>><?php p($l->t('create')); ?></label>
            <label><input type="checkbox" name="update" class="permissions" data-permissions="2" <?php p(($sharee['permissions'] & OCP\PERMISSION_UPDATE?'checked="checked"':''))?>><?php p($l->t('update')); ?></label>
            <label><input type="checkbox" name="delete" class="permissions" data-permissions="8" <?php p(($sharee['permissions'] & OCP\PERMISSION_DELETE?'checked="checked"':''))?>><?php p($l->t('delete')); ?></label>
          </div>
        <?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>
  </ul>
</div>