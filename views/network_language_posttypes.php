
<div class="wrap">
	<h2>Language settings</h2>
	<p>You can control language mapping for your sites here.</p>


	<table>
		<form action="?page=jcml-lang-posttypes#save" method="post" class="posttype-form">
			<?php foreach( $post_types as $slug => $arr ): ?>

				<tr>
					<td><input type="checkbox" name="posttype[<?php echo $slug; ?>]" <?php echo $arr['checked'] == 1 ? 'checked' : ''; ?>><?php echo $arr['label']; ?></td>
				</tr>

			<?php endforeach; ?>
			<tr>
				<td><input type="submit" value="Save" class="button button-primary"></td>
			</tr>

	</table>

</div>