
<div class="wrap" id="page_wrap">
	<h2>Language settings</h2>
	<p>You can control language mapping for your sites here.</p>
	<div class="errors_and_messages">
		<?php if( !empty($errors) ) : ?>
			<div class="error below-h2" id="error"><p><?php echo implode('<br/>', $errors); ?></p></div>
		<?php endif; ?>
		<?php if( !empty($messages) ) : ?>
			<div class="updated below-h2" id="message"><p><?php echo implode('<br/>', $messages); ?></p></div>
		<?php endif; ?>
	</div>
	<div id="partial_network_languages">
		<div id="langs-table" >
		<table class="wp-list-table multilang-half-wide widefat fixed ">
			<thead>
				<tr class="multilang-table-header">			
					<th class="manage-column column-blogname"><span>Site Path</span></th>
					<th class="manage-column column-blogname"><span>Language</span></th>
					<th class="manage-column column-blogname"><span>Icon</span></th>
					<th class="manage-column column-blogname"><span>&nbsp;</span></th>
				</tr>
			</thead>
			<tbody id="mapped_languages_table">
				<?php if( empty($settings) ) : ?>
					<tr><td colspan="4">You don't have any language mapping yet.</td></tr>
				<?php else : ?>
					<?php foreach( $settings as $map ) : ?>
						<tr id="jcml_fieldset_lang_<?php echo $map->blog_id; ?>">
							<td><a target="_blank" href="<?php echo get_site_url($map->blog_id); ?>"><?php echo $map->domain . $map->path; ?></a></td>
							<td><?php echo esc_html($map->language); ?></td>
							<td><?php echo esc_html($map->alias); ?></td>
							<td>
								<span class=""><a href="#" id="editlang_<?php echo $map->blog_id; ?>" class="edit_lang_button" ><span class="">edit</span></a></span>
								|
								<span class="delete"><a href="#" id="deletelang_<?php echo $map->blog_id; ?>" class="detach_lang_button" ><span class="delete">detach</span></a></span>
								<img class="ajax-feedback " alt="" title="" src="<?php echo get_bloginfo('url') ?>/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
			<div style="margin: 5px;">
			<input type="submit" value="Add New Language" class="button button-primary" id="new_lang_button">
			</div>
		</div>
		<div id="add-form-holder">
	<table class="wp-list-table add-lng-form widefat fixed ">
		<form action="?page=jcmst-lang-settings#add-new-lang" method="post" class="add-lng-form" id="add-language-form" >
			<tr class="multilang-table-header">
				<th colspan="2"><h3 id="add-new-lang">Add new language</h3></th>
			</tr>
			<tr>
				<td colspan="2">
					<div class="errors_and_messages">
						<?php if( !empty($errors) ) : ?>
							<div class="error below-h2" id="error"><p><?php echo implode('<br/>', $errors); ?></p></div>
						<?php endif; ?>
						<?php if( !empty($messages) ) : ?>
							<div class="updated below-h2" id="message"><p><?php echo implode('<br/>', $messages); ?></p></div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
			<tr class="form-field form-required">
				<td>Site Address</td>
				<td class="autocomplete-field">
					<input id="blog_domain" type="text" title="Domain" placeholder="Start typing the domain..." class="regular-text" name="language[domain]"
						   value="<?php echo esc_attr(@$input['domain']); ?>">
				</td>
			</tr>
			<tr class="form-field form-required">
				<td>Language code</td>
				<td>
					<input type="text" title="Language code" class="regular-text" name="language[language]"
						   value="<?php echo esc_attr(@$input['language']); ?>">
					<p class="hint">ex. 'en'</p>
				</td>
			</tr>
			<tr class="form-field">
				<td>Language Alias</td>
				<td>
					<input type="text" title="Language alias" class="regular-text" name="language[alias]"
						   value="<?php echo esc_attr(@$input['alias']); ?>">
					<p class="hint">ex. 'English'</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="submit"><input type="submit" value="Save" class="button button-primary" name="add-language">
						<img class="ajax-feedback" alt="" title="" src="<?php echo get_bloginfo('url') ?>/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">
					</p>
				</td>
			</tr>
		</form>	
	</table>
	</div>
		
		<div id="edit-form-holder">
	<table class="wp-list-table add-lng-form widefat fixed ">
		<form action="#" method="post" class="add-lng-form" id="edit-language-form" >
			<input id="edit-domain-id" type="hidden"   class="regular-text-edit" name="language[id]"
			<tr class="multilang-table-header">
				<th colspan="2"><h3 id="edit-lang">Edit <span id="domain_edited"></span></h3></th>
			</tr>
			<tr>
				<td colspan="2">
					<div class="errors_and_messages">
						<?php if( !empty($errors) ) : ?>
							<div class="error below-h2" id="error"><p><?php echo implode('<br/>', $errors); ?></p></div>
						<?php endif; ?>
						<?php if( !empty($messages) ) : ?>
							<div class="updated below-h2" id="message"><p><?php echo implode('<br/>', $messages); ?></p></div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
			
			<tr class="form-field form-required">
				<td>Language code</td>
				<td>
					<input type="text" title="Language code" class="regular-text-edit" name="language[language]"
						   value="<?php echo esc_attr(@$input['language']); ?>">
					<p class="hint">ex. 'en'</p>
				</td>
			</tr>
			<tr class="form-field">
				<td>Language Alias</td>
				<td>
					<input type="text" title="Language alias" class="regular-text-edit" name="language[alias]"
						   value="<?php echo esc_attr(@$input['alias']); ?>">
					<p class="hint">ex. 'English'</p>
				</td>
			</tr>
			<tr>
				<td>
					<p class="submit"><input type="submit" value="Save" class="button button-primary" name="edit-language">
						<img class="ajax-feedback" alt="" title="" src="<?php echo get_bloginfo('url') ?>/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">
					</p>
				</td>
			</tr>
		</form>	
	</table>
	</div>
	</div>
	
	
	
</div>