
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
		<div id="langs-table" class="language-box">
			<table class="wp-list-table multilang-half-wide widefat fixed ">
				<thead>
					<tr class="multilang-table-header">			
						<th class="manage-column column-blogname table-header-path"><span>Site Path</span></th>
						<th class="manage-column column-blogname table-header-language"><span>Language</span></th>
						<th class="manage-column column-blogname table-header-alias"><span>Language Alias</span></th>
						<th class="manage-column column-blogname table-header-actions"><span>&nbsp;</span></th>
					</tr>
				</thead>
				<tbody id="jcml_mapped_languages_table">
					<?php if( empty($settings) ) : ?>
						<tr><td colspan="4">You don't have any language mapping yet.</td></tr>
					<?php else : ?>
						<?php foreach( $settings as $map ) : ?>
							<tr id="jcml_row_lang_<?php echo $map->blog_id; ?>">
								<td><a target="_blank" href="<?php echo get_site_url($map->blog_id); ?>"><?php echo $map->domain . $map->path; ?></a></td>
								<td class="map-lang"><?php echo esc_html($map->language); ?></td>
								<td class="map-alias"><?php echo esc_html($map->alias); ?></td>
								<td id="lang_<?php echo $map->blog_id; ?>">
									<span class=""><a href="#" class="jcml_edit_lang_button" ><span class="">edit</span></a></span> | 
									<span class="delete"><a href="#"  class="jcml_detach_lang_button" ><span class="delete">detach</span></a></span>
									<img class="ajax-feedback " alt="" title="" src="<?php echo get_bloginfo('url') ?>/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			<div style="margin: 5px;">
				<input type="submit" value="Add New Language" class="button button-primary" id="jcml_new_lang_button">
			</div>
		</div>
		<div id="jcml-add-form-holder" class="language-box">
			<table class="wp-list-table add-lng-form widefat fixed ">
				<form action="?page=jcml-lang-settings#add-new-lang" method="post" class="add-lng-form" id="jcml-add-language-form" name="jcml-add-language-form">
					<tr class="multilang-table-header">
						<th colspan="2">Add New Language</th>
					</tr>

					<tr class="form-field form-required">
						<td>Site Path</td>
						<td class="autocomplete-field">
							<input id="jcml_blog_domain" type="text" title="Site Path" placeholder="Start typing the domain..." class="regular-text" name="language[domain]"
								   value="<?php echo esc_attr(@$input['domain']); ?>">
						</td>
					</tr>
					<tr class="form-field form-required">
						<td>Language Code</td>
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

		<div id="jcml-edit-form-holder" class="language-box">
			<table class="wp-list-table add-lng-form widefat fixed ">
				<form action="#" method="post" class="add-lng-form" id="jcml-edit-language-form" >
					<input id="jcml-edit-domain-id" type="hidden"   class="regular-text-edit" name="language[id]">
					<tr class="multilang-table-header">
						<th colspan="2">Edit Language</th>
					</tr>

					<tr class="form-field">
						<td>Site Path</td>
						<td>
							<input type="text" title="Language alias" class="regular-text-edit" name="language[domain]" id='jcml-edit-domain' readonly
								   value="<?php echo esc_attr(@$input['domain']); ?>">

						</td>
					</tr>
					<tr class="form-field form-required">
						<td>Language Code</td>
						<td>
							<input type="text" title="Language code" class="regular-text-edit" name="language[language]" id='jcml-edit-language'
								   value="<?php echo esc_attr(@$input['language']); ?>">
							<p class="hint">ex. 'en'</p>
						</td>
					</tr>
					<tr class="form-field">
						<td>Language Alias</td>
						<td>
							<input type="text" title="Language alias" class="regular-text-edit" name="language[alias]" id='jcml-edit-alias'
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