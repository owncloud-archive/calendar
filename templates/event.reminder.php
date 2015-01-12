<?php p($l->t('Dear %s,', array($_['member']))); ?>
<br />

<p style="text-indent: 50px;" >
	<?php p($l->t('List of your task(s) scheduled today at ownCloud is provided below.')); ?>
</p>

<table style="border: 1px black solid" >
	<tr>
		<th style="border: 1px black solid; padding: 10px;" >
			<?php p($l->t('Event')); ?>
		</th>
	
		<th style="border: 1px black solid; padding: 10px;" >
			<?php p($l->t('Starting time')); ?>
		</th>
		
		<th style="border: 1px black solid; padding: 10px;" >
			<?php p($l->t('Ending time')); ?>
		</th>
	</tr>
	
	<?php
		$task_count = count($_['starting']);
		
		for($i = 0; $i < $task_count; $i++)
		{
	?>
			<tr>
				<td style="border: 1px black solid; padding: 10px;" >
					<?php p($_['event'][$i]); ?>
				</td>
				
				<td style="border: 1px black solid; padding: 10px;" >
					<?php p($_['starting'][$i]); ?>
				</td>
				
				<td style="border: 1px black solid; padding: 10px;" >
					<?php p($_['ending'][$i]); ?>
				</td>
			</tr>
	<?php
		}
	?>
</table>
<br />
