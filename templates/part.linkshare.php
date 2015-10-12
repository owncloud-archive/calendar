<?php /*
	Public link-sharing of events/calendars (or, actually, any items, once integrated into app's interface)
	(c) 2014 Michał "rysiek" Woźniak <rysiek@hackerspace.pl>
	Licensed under AGPL.
*/ ?>
<div class="share-interface-container link-share displayable-container"
		 data-item-type="<?php p($_['item_type']); ?>"
		 data-item="<?php p($_['item_id']); ?>"
		 data-possible-permissions="<?php p($_['permissions']) ?>"
		 data-link="true">
	<!--
		TODO:
		add a <form>
		prepare backend for direct submission, then fill-in the details of the form;
		if JS is disabled, the submit button will be available, automagically
	-->
	<!-- the checkbox that enables and disables the whole thing -->
	<input type="checkbox" name="share-link" class="share-link displayable-control" value="0" id="share-link-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>" <?php if (isset($_['link_share']['token'])): ?> checked="checked"<?php endif; ?>/>
	<label for="share-link-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>" class="share-link-label"><h3><?php p($l->t('Share via link')); ?></h3></label>
	<!-- this should be visible only when the share-link checkbox is :checked -->
	<div class="share-link-enabled-container displayable">
		<!-- link container, contains the share link (duh) -->
		<input class="link-text" type="text" readonly="readonly" placeholder="<?php p($l->t('Sharing link will appear here')) ?>" value="<?php if ($_['link_share']['token']) { p(OCP\Util::linkToPublic('calendar') . '&t=' . $_['link_share']['token']); } ?>"/>
		<!-- do we want the password shown? default: nope -->
		<div class="password-protect-outer-container displayable-container">
			<input type="checkbox" name="password-protect" class="password-protect displayable-control" value="0" id="password-protect-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>" <?php if (isset($_['link_share']['share_with'])): ?> checked="checked"<?php endif; ?>/>
			<label for="password-protect-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>" class="password-protect-label"><?php p($l->t('Password protect')) ?></label>
			<div class="password-container displayable">
				<input class="share-link-password" type="password" placeholder="<?php if (isset($_['link_share']['share_with'])) { p($l->t('Password protected')); } else { p($l->t('Password')); }  ?>" name="share-link-password"/>
			</div>
		</div>
		<!-- do we want share expiration date? -->
		<div class="expire-date-outer-container displayable-container">
			<input type="checkbox" name="expire" class="expire displayable-control" value="0" id="expire-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>" <?php if (isset($_['link_share']['expiration'])): ?> checked="checked"<?php endif; ?>/>
			<label for="expire-<?php p($_['item_type']); ?>-<?php p($_['item_id']); ?>" class="expire-label"><?php p($l->t('Set expiration date')) ?></label>
			<div class="expire-date-container displayable noafter">
				<input class="expire-date" type="date" placeholder="<?php p($l->t('Expiration date')) ?>" name="expire-date" value="<?php if (isset($_['link_share']['expiration'])) { p(substr($_['link_share']['expiration'], 0, 10)); } ?>"/>
			</div>
		</div>
		<!-- link email form --><?php /*
		E-mail send form is disabled at this time, as OC_Calendar_App::sendEmails() is not meant to handle e-mails to non-users
		Patches welcome.
		<div class="e-mail-form-container">
			<input class="share-link-e-mail-address" value="" placeholder="<?php p($l->t('Email link to person')) ?>" type="e-mail"/>
			<!-- the send e-mail submit button (unneeded and invisible if JS is disabled) -->
			<noscript><!--</noscript>
			<input class="share-link-e-mail-send" type="submit" name="send-e-mail" value="<?php p($l->t('Send')) ?>"/>
			<noscript>--></noscript>
		</div> */ ?>
		<!-- the submit button (unneeded and invisible if JS is enabled) -->
		<noscript><input class="share-link-form-submit" type="submit" value="<?php p($l->t('Submit link-sharing settings')) ?>"/></noscript>
	</div>
</div>