<form action="<?php p($_['URL']) ?>" method="POST">
  <?php if ($_['wrongpw'] === true) { ?><p class="error"><?php p($l->t('Wrong password!')); ?></p><?php } ?>
  <input type="password" id="public-calendar-auth-password" name="password"/>
  <input type="submit" class="submit actionsfloatright primary" id="public-calendar-auth-submit" value="<?php p($l->t('Authenticate!'));?>"/>
</form>