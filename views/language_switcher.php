
	<?php if($output_type == JCMST_UOTPUT_LIST):?> 
		<ul class="drop-down"> 
	<?php endif;?>
	
	<?php foreach( $languages as $value ):?>

		<?php if($output_type == JCMST_UOTPUT_LIST):?>
		<?php endif;?>
		<?php if($output_type == JCMST_UOTPUT_LINKS):?>
		<?php endif;?>

	<?php endforeach;?>

	<?php if($output_type == JCMST_UOTPUT_LIST):?> 
		</ul>
	<?php endif;?>