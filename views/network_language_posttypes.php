
<div class="wrap">
	<h2>Post types</h2>
	<p>Here you can choose which post types should be translatable.</p>


	<table>
		<form action="?page=jcml-lang-posttypes#save" method="post" class="posttype-form">
			<?php foreach( $post_types as $slug => $arr ): ?>

				<tr>
					<td><input type="checkbox" id="post_type_<?php echo $slug; ?>" name="posttype[<?php echo $slug; ?>]" <?php echo $arr['checked'] == 1 ? 'checked' : ''; ?>><label for="post_type_<?php echo $slug; ?>"><?php echo $arr['label']; ?></label></td>
				</tr>

			<?php endforeach; ?>
				
			<tr>
				<td><input style="margin-top: 1em;" type="submit" value="Save" class="button button-primary"></td>
			</tr>

	</table>
</div>