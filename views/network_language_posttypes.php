
<div class="wrap">
	<h2>Language settings</h2>
	<p>You can control language mapping for your sites here.</p>
	
	
	<table>
	<?php foreach($post_types as $name => $label): ?>
	<!--
		<tr>
			<td><input type="checkbox" name="posttype[<?php echo $name; ?>]"><?php echo $label; ?></td>
		</tr>
	-->
	<?php endforeach; ?>
		<tr>
			<td><input type="submit" value="Save" class="button button-primary"></td>
		</tr>
		
	</table>
	<?php 
	pa($post_types);
	

	?>
</div>