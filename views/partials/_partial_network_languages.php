<table class="wp-list-table multilang-half-wide widefat fixed ">
		<thead>
			<tr class="multilang-table-header">
				<th class="manage-column column-blogname"><span>Site Path</span></th>
				<th class="manage-column column-blogname"><span>Language</span></th>
				<th class="manage-column column-blogname"><span>Icon</span></th>
				<th class="manage-column column-blogname"><span>&nbsp;</span></th>
			</tr>
		</thead>
		<tbody>
			<?php if( empty($settings) ) : ?>
				<tr><td colspan="4">You don't have any language mapping yet.</td></tr>
			<?php else : ?>
				<?php foreach( $settings as $map ) : ?>
					<tr>
						<td><a target="_blank" href="<?php echo get_site_url($map->blog_id); ?>"><?php echo $map->domain . $map->path; ?></a></td>
						<td><?php echo esc_html($map->language); ?></td>
						<td><?php echo esc_html($map->alias); ?></td>
						<td><span class="delete"><a href="#" class="detach_lang_button" onclick="return detachLang(<?php echo $map->blog_id; ?>);"><span class="delete">detach</span></a></span></td>
					</tr>
				<?php endforeach; ?>
<?php endif; ?>
		</tbody>
	</table>

