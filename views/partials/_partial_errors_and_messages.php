<?php if( !empty($errors) ) : ?>
	<div class="error below-h2" id="error"><p><?php echo implode('<br/>', $errors); ?></p></div>
<?php endif; ?>
<?php if( !empty($messages) ) : ?>
	<div class="updated below-h2" id="message"><p><?php echo implode('<br/>', $messages); ?></p></div>
<?php endif; ?>