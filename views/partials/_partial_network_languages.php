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
					<td><span class="delete"><a href="#" id="lang_<?php echo $map->blog_id; ?>" class="detach_lang_button" ><span class="delete">detach</span></a></span>
					<img class="ajax-feedback " alt="" title="" src="<?php echo get_bloginfo('url') ?>/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

