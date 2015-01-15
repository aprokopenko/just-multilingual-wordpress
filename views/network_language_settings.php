
<div class="wrap">
	<h2>
		Language settings
		<a class="add-new-h2" href="#">Add New Language</a>
	</h2>
	<p>You can control language mapping for your sites here.</p>
	
	<?php if(!empty($errors)) : ?>
		<div class="error below-h2" id="error"><p><?php echo implode('<br/>', $errors); ?></p></div>
	<?php endif; ?>
	<?php if(!empty($messages)) : ?>
		<div class="updated below-h2" id="message"><p><?php echo implode('<br/>', $messages); ?></p></div>
	<?php endif; ?>
	
	<div class="jcml-row">
		<div class="jcml-column-8">
			<table class="wp-list-table widefat fixed ">
				<thead>
					<tr>
						<th class="manage-column column-blogname"><span>Site Path</span></th>
						<th class="manage-column column-blogname column-format"><span>Lang Code</span></th>
						<th class="manage-column column-blogname column-format"><span>Lang Alias</span></th>
						<th class="manage-column column-blogname column-format"><span>&nbsp;</span></th>
					</tr>
				</thead>
				<tbody>
					<?php if(empty($settings)) : ?>
						<tr><td colspan="4">You don't have any language mapping yet.</td></tr>
					<?php else : ?>
						<?php foreach($settings as $map) : ?>
							<tr>
								<td><a target="_blank" href="<?php echo get_site_url($map->blog_id); ?>"><?php echo $map->domain.$map->path; ?></a></td>
								<td><?php echo esc_html($map->language); ?></td>
								<td><?php echo esc_html($map->alias); ?></td>
								<td>
									<a href="#">edit</a> | 
									<span class="delete"><a href="?page=jcml-lang-settings&detach=<?php echo $map->blog_id; ?>" onclick="if(!confirm('Are you sure you want detach this language from site?'))return false;"><span class="delete">detach</span></a></span>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div class="jcml-column-4">
			
			<h3 class="jcml-container-header" id="add-new-lang">Add new language</h3>
			<div class="jcml-form-container">
				<?php if(!empty($errors)) : ?>
					<div class="error below-h2" id="error"><p><?php echo implode('<br/>', $errors); ?></p></div>
				<?php endif; ?>
				<?php if(!empty($messages)) : ?>
					<div class="updated below-h2" id="message"><p><?php echo implode('<br/>', $messages); ?></p></div>
				<?php endif; ?>

				<form action="?page=jcml-lang-settings#add-new-lang" method="post" class="add-lng-form">
					<fieldset>
							<p>
								<label>Site Address *</label>
								<input id="blog_domain" type="text" title="Domain" placeholder="Start typing the domain..." class="regular-text" name="language[domain]"
										   value="<?php echo esc_attr(@$input['domain']); ?>">
							</p>
							<p>
								<label>Language code *</label>
								<input type="text" title="Language code" class="regular-text" name="language[language]"
										value="<?php echo esc_attr(@$input['language']); ?>">
								<span class="hint">ex. 'en'</span>
							</p>
							<p>
								<label>Language Alias *</label>
								<input type="text" title="Language alias" class="regular-text" name="language[alias]"
										value="<?php echo esc_attr(@$input['alias']); ?>">
								<span class="hint">ex. 'English'</span>
							</p>

							<p>Note: All fields are mandatory.</p>
					</fieldset>
					<p class="submit"><input type="submit" value="Add Language" class="button button-primary" name="add-language"></p>	
				</form>	
			</div>
		</div>
	</div>

	
</div>