<ul>
	<li class="error">
		<?php if (isset($_['message'])) {
			p($l->t( $_['message']) );
		} else {
			p($l->t( 'Public calendar not found' ));
		} ?><br/>
		<p class='hint'><?php if(isset($_['file'])) p($_['file'])?></p>
	</li>
</ul>
