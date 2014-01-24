<div class="share-interface-container internal-share">
  <input type="text" class="share-with ui-autocomplete-input"
    placeholder="<?php p($l->t('Share with user or group')); ?>"
    data-item-source="<?php p($_['item_id']); ?>"
    data-item-type="<?php p($_['item_type']); ?>"
    data-permissions="<?php p($_['permissions']) ?>"
    autocomplete="off" />

  <ul class="shared-with-list">
  <?php
    /*
     * iterating through shared-with array
     * we also need a single stub to be used with JS when adding new share-withs
     * let's add that as index 0
     */
    array_unshift(
      $_['shared_with'],
      // all the data will be filled-in by PHP below or by JS upon creating an entry from the stub
      array (
        'share_type' => '',
        'share_with' => '',
        'permissions' => ''
      )
    );
    /* 
     * iterate!
     */
    foreach($_['shared_with'] as $i => $sharee): ?>
    <li
      data-share-type="<?php p($sharee['share_type']); ?>"
      data-share-with="<?php p($sharee['share_with']); ?>"
      data-item="<?php p($_['item_id']); ?>"
      data-item-source="<?php p($_['item_id']); ?>"
      data-item-type="<?php p($_['item_type']); ?>"
      data-link="true"
      data-permissions="<?php p($sharee['permissions']); ?>"
      title="<?php p($sharee['share_with']); ?>"
      class="shared-with-entry-container <?php if($i === 0): ?>stub<?php endif; ?>">
      <!-- the username -->
      <span class="username"><?php p($sharee['share_with'] . ($sharee['share_type'] == OCP\Share::SHARE_TYPE_GROUP ? ' (group)' : '')); ?></span>
      <!-- unshare link -->
      <a href="#" class="unshare">
        <img class="svg" alt="<?php p($l->t('Unshare')); ?>" src="<?php p(OCP\Util::imagePath('core', 'actions/delete.svg')); ?>">
      </a>
      <div class="displayable-container share-options">
        <!--
          "can edit" checkbox
            - if the item type allows for precise sharing settings (update, create, delete), setting/un-setting all three at the same time via JS (and reflecting their state)
            - if the item type allows only for editable/uneditable (actually, just update) setting, the "update" checkbox
          this is checked via $_['basic_edit_options'] flag (if set and true, only editable/uneditable setting available)

          this checkbox is available only when JS is enabled
          otherwise the noscript tag makes space for a CSS/HTML "checkbox" showing the state of the create/update/delete checkboxes
        -->
        <noscript class="share-can-edit-space"><!--</noscript>
        
        <input type="checkbox" class="permissions" 
          <?php if(empty($_['basic_edit_options'])): ?>
            name="edit" data-permissions="<?php p(OCP\PERMISSION_UPDATE | OCP\PERMISSION_CREATE | OCP\PERMISSION_DELETE); ?>" <?php if ($sharee['permissions'] & (OCP\PERMISSION_UPDATE | OCP\PERMISSION_CREATE | OCP\PERMISSION_DELETE ) ): ?> checked="checked"<?php endif; ?> id="share-collective-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"
          <?php else: ?>
            name="update" data-permissions="<?php p(OCP\PERMISSION_UPDATE); ?>" <?php if ($sharee['permissions'] & OCP\PERMISSION_UPDATE): ?> checked="checked"<?php endif; ?> id="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"
          <?php endif; ?>
        />

        <?php /* "can edit" label for the above share-can-edit checkbox */ ?>
        <label
          <?php if(empty($_['basic_edit_options'])): ?>
          for="share-collective-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"
          <?php else: ?>
          for="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"
          <?php endif; ?>
        ><?php p($l->t('can edit')); ?></label>

        <noscript>-->
        <!-- "can edit" displayable-control label OR if basic_edit_options a label for the above share-can-edit checkbox -->
        <label for="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><?php p($l->t('can edit')); ?></label>
        </noscript>
        
        <!-- "can edit triangle" displayable-control label - not needed when only basic edit options are available -->
        <?php if(empty($_['basic_edit_options'])): ?>
        <label for="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><?php if(empty($_['basic_edit_options'])): ?><img class="svg" alt="access control" src="<?php p(OCP\Util::imagePath('core', 'actions/triangle-s.svg')); ?>"><?php endif; ?></label>
        <?php endif; ?>

        <!-- "can share" label and checkbox -->
        <label class="share-label"><input type="checkbox" name="share" class="permissions" data-permissions="<?php p(OCP\PERMISSION_SHARE); ?>" <?php if ($sharee['permissions'] & OCP\PERMISSION_SHARE): ?> checked="checked"<?php endif; ?>><?php p($l->t('can share')); ?></label>
        <!-- if we only have basic edit options available, there is no need for the advanced edit options controls, right? display these only when not in basic edit options regime -->
        <?php if(empty($_['basic_edit_options'])): ?>
          <!-- edit options displayable control and displayable itself -->
          <input type="checkbox" class="displayable-control hide" name="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>" id="share-can-edit-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"/>
          <div class="displayable edit-options">
            <input type="checkbox" name="create" class="permissions" data-permissions="<?php p(OCP\PERMISSION_CREATE); ?>" <?php if ($sharee['permissions'] & OCP\PERMISSION_CREATE): ?> checked="checked"<?php endif; ?> id="share-permissions-create-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><label for="share-permissions-create-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><?php p($l->t('create')); ?></label>
            <input type="checkbox" name="update" class="permissions" data-permissions="<?php p(OCP\PERMISSION_UPDATE); ?>" <?php if ($sharee['permissions'] & OCP\PERMISSION_UPDATE): ?> checked="checked"<?php endif; ?> id="share-permissions-update-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><label for="share-permissions-update-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><?php p($l->t('update')); ?></label>
            <input type="checkbox" name="delete" class="permissions" data-permissions="<?php p(OCP\PERMISSION_DELETE); ?>" <?php if ($sharee['permissions'] & OCP\PERMISSION_DELETE): ?> checked="checked"<?php endif; ?> id="share-permissions-delete-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><label for="share-permissions-delete-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>-<?php p($i); ?>"><?php p($l->t('delete')); ?></label>
            <!-- a CSS/HTML "checkbox" showing the state of the create/update/delete checkboxes -->
            <noscript class="share-can-edit-checkbox"></noscript>
          </div>
        <?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>
  </ul>
</div>
