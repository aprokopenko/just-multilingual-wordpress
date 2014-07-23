<?php

/* @var $wpdb wpdb */
/* @var $lang StdClass */
/* @var $post WP_Post */
/* @var $translations array */
/* @var $language_options array */
/* @var $languages array */

$maped_languages = array();
?>
<div class="misc-pub-section misc-pub-post-status">
	<label>Current language:</label>
	<strong><?php echo esc_html($lang->alias); ?></strong>
	
	<?php if( !empty($translate_of['post']) ) : ?>
	<br/><br/>
	<label>You're translating from <strong><?php echo esc_html($translate_of['lang']->alias); ?></strong>:</label><br/>
	&nbsp; <span><?php echo esc_html($translate_of['post']->post_title); ?></span>
	<?php endif; ?>
</div>

<div id="jcmst_available_translations_wrapper" class="misc-pub-section misc-pub-post-status <?php if(empty($translations['chain'])) echo ' hide-if-js'; ?>">
	<label>Available translations:</label><br/>
	<div>
		<table width="100%"><tbody id="jcmst_available_translations">
		<?php if(!empty($translations['chain'])) 
			foreach($translations['chain'] as $trans) : 
				$maped_languages[$trans->blog_id] = $trans->post_id;
			?>
			<tr>
				<td class="language"><strong><?php echo esc_html($trans->alias); ?></strong></td>
				<td><a href="<?php echo $trans->post->guid; ?>" target="_blank"><?php echo esc_html($trans->post->post_title); ?></a> 
					<a href="#" class="jcmst-delete-translation" data-translation_id="<?php echo $trans->translation_id; ?>">delete</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody></table>
	</div>
</div>

<div class="jcmst-translate-actions">
	<div class="jcmst-left-btn">
		<input type="button" class="button" value="Set relation" data-target="#jcmst_set_relation_wrap">
	</div>
	<?php if($post->post_status != 'auto-draft') : ?>
		<div>
			<input type="button" class="button" value="Translate" data-target="#jcmst_translate_wrap">
		</div>
	<?php endif; ?>
	<div class="clear"></div>
</div>

<div id="jcmst_set_relation_wrap" class="hide-if-js">
	<div class="wp-tab-panel">
		<p>Please choose language and correct post.</p>
		<select id="jcmst_set_rel_language" class="full-width" name="jcmst_translate_of[blog_id]">
			<option value="">Select language...</option>
			<?php echo jcmst_html_options($language_options, $translate_of['blog_id']); ?>
		</select>
		<input type="text" id="jcmst_set_rel_post_search" placeholder="Start typing post title..." 
			   name="jcmst_translate_of[post_id]"
			   value="<?php echo esc_attr($translate_of['post_id']); ?>" class="jcmst-hidden full-width"
			   data-post_type="<?php echo esc_attr($post->post_type); ?>"
			   />
		<input type="hidden" id="jcmst_set_rel_post_id" value="" />
		
		<p>or specify post full URL</p>
		<input type="text" id="jcmst_set_rel_post_url" placeholder="Copy post URL here..." 
			   value="" class="full-width"
			   data-post_type="<?php echo esc_attr($post->post_type); ?>"
			   />
		<input type="button" class="button" id="jcmst_save_rel_btn" value="Save">
		<input type="button" class="button" id="jcmst_cancel_rel_btn" value="Cancel" data-target="#jcmst_set_relation_wrap">
		<br><br>
	</div>
</div>

<div id="jcmst_translate_wrap" class="hide-if-js">
	<div class="wp-tab-panel">
		
		<p>Please choose language you want to add.<br/>
			You will be forwarded to the Add content page in the correct language.
		</p>
		<?php foreach($languages as $lang) : 
				if( isset($maped_languages[$lang->blog_id]) ) continue;
				$missing_translations = true;
				$link = !empty($_SERVER['HTTPS'])? 'https://' : 'http://';
				$link.= $lang->domain . $lang->path;
				$link.= 'wp-admin/post-new.php?post_type=' . $post->post_type . '&translate_of=' . $blog_id . ':' . $post->ID;
		?>
			<strong>to <a href="<?php echo esc_attr($link); ?>" target="_blank"><?php echo esc_html($lang->alias); ?></a></strong><br/>
		<?php endforeach; ?>
			
		<?php if( empty($missing_translations) ) : ?>
			<p style="color:#d00;">...It seems you already have all translations.</p>
		<?php endif; ?>

		<input type="button" class="button" id="jcmst_cancel_trnsl_btn" value="Cancel" data-target="#jcmst_translate_wrap">
	</div>
</div>

