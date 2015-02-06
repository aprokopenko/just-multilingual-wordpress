<?php

/* @var $wpdb wpdb */

function jcml_post_add_meta_boxes(){
	$post_type = get_post_type();

	// first check the language mapping
	if( !jcml_is_blog_mapped() || !get_option('jcml_lang_posttype_' . $post_type) )
		return;

	$all_post_types = get_post_types();

	// get all registered post types
	$screens = $all_post_types;

	foreach( $screens as $screen )
	{

		add_meta_box(
				'jcml_translate_box', 'Translations', 'jcml_post_translate_meta_box', $screen, 'side', 'high'
		);
	}
}

add_action('add_meta_boxes', 'jcml_post_add_meta_boxes');

/**
 * 	add custom scripts and styles
 */
function jcml_post_edit_include_assets(){
	// ui autocomplete

	wp_enqueue_script( 'jquery-ui-autocomplete' );


	// network page script
	wp_register_script(
			'jcml_post_edit', jcml_plugin_url() . '/assets/jcml_post_edit.js', array('jquery')
	);
	wp_enqueue_script('jcml_post_edit');

	// styles
	wp_register_style('ui-autocomplete', jcml_plugin_url() . '/assets/jquery-ui-1.11.autocomplete.min.css');
	wp_enqueue_style('ui-autocomplete');
	wp_register_style('jcml_post_edit', jcml_plugin_url() . '/assets/jcml_post_edit.css');
	wp_enqueue_style('jcml_post_edit');
}

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function jcml_post_translate_meta_box( $post ){
//pa($post,1);
	// Add an nonce field so we can check for it later.
	wp_nonce_field('jcml_translate_box', 'jcml_translate_box_nonce');

	$blog_id = get_current_blog_id();
	$lang = jcml_get_blog_language($blog_id);

	$language_options = jcml_get_language_options($blog_id);
	$languages = jcml_get_languages($blog_id);
	$translations = jcml_get_post_translate_chain($post->ID, null, true);

	$translate_of = array('blog_id' => '', 'post_id' => '');
	if( !empty($_GET['translate_of']) )
	{
		$parts = explode(':', trim($_GET['translate_of']));
		if( count($parts) == 2 )
		{
			if( $translate_of_post = jcml_get_post($parts[1], $parts[0]) )
			{
				$translate_of['blog_id'] = $parts[0];
				$translate_of['post_id'] = $parts[1];
				$translate_of['post'] = $translate_of_post;
				$translate_of['lang'] = jcml_get_blog_language($translate_of['blog_id']);
			}
		}
	}
	//pa($translate_of,1);

	include(dirname(__FILE__) . '/views/post_translate_meta_box.php');
}

/**
 * search post by keyword match in title
 * @global wpdb $wpdb
 */
function jcml_autocomplete_jcml_post_search_by_title( $strict = false ){
	global $wpdb;

	$term = trim($_POST['term']);
	$search_string = mysql_escape_string($term);
	if( !$strict )
		$search_string = '%' . $search_string . '%';

	$blog_id = (int) $_POST['blog_id'];
	switch_to_blog($blog_id);

	$post_type = mysql_escape_string($_POST['post_type']);

	// get posts
	$posts = $wpdb->get_results("
		SELECT *
		FROM $wpdb->posts as p
		WHERE post_title LIKE '$search_string'
			AND post_type = '$post_type'
			AND post_status != 'auto-draft'
			AND post_status != 'inherit'
		ORDER BY post_title
		LIMIT 10");

	$results = array();
	foreach( $posts as $post )
	{
		$results[] = array('label' => $post->post_title, 'value' => $post->ID);
	}

	restore_current_blog();

	if( $return )
		return $results;

	jcml_render_json($results);
}

function jcml_autocomplete_jcml_post_search_by_url(){
	global $wpdb;

	$term = trim($_POST['term']);

	$parsed = jcml_parse_post_url($term, $_POST['post_type']);

	$results = array(
		'status' => 'notfound',
	);
	if( $parsed['post_id'] > 0 )
	{
		$results['status'] = 'ok';
	}

	jcml_render_json($results);
}

function jcml_ajax_post_map_language(){
	global $wpdb;

	$errors = [];
	$translations = [];

	$blog_id = (int) $_POST['blog_id'];
	$post_id = (int) $_POST['post_id'];

	$origin_post = (int) $_POST['id'];
	$origin_blog = get_current_blog_id();

	// validate
	if( empty($post_id) && !empty($_POST['post_url']) )
	{
		$parsed = jcml_parse_post_url($_POST['post_url'], $_POST['post_type']);
		if( !empty($parsed['post_id']) )
		{
			$post_id = $parsed['post_id'];
			$blog_id = $parsed['blog_id'];
		}
	}

	if( empty($post_id) )
	{
		$errors[] = 'Please choose page from autocomplete tool or specify the valid URL.';
	}

	$maping_errors = jcml_map_posts(
			array('blog_id' => $blog_id, 'post_id' => $post_id), array('blog_id' => $origin_blog, 'post_id' => $origin_post)
	);

	$errors = array_merge($errors, $maping_errors);

	// get updated info
	$translations = jcml_get_post_translate_chain($origin_post, $origin_blog, true);

	// prepare response
	$results = array(
		'status' => 'failed',
		'errors' => implode('<br>', $errors),
		'translations' => $translations,
	);

	if( empty($errors) )
	{
		$results['status'] = 'ok';
	}

	jcml_render_json($results);
}

/**
 * map 2 posts together (check for errors first)
 * @global wpdb $wpdb
 * @param array $from	pair of (blog_id, post_id)
 * @param array $to		pair of (blog_id, post_id)
 */
function jcml_map_posts( $from, $to ){
	global $wpdb;

	$errors = [];

	// get existed chains
	$from_info = jcml_get_post_translate_chain($from['post_id'], $from['blog_id']);
	$to_info = jcml_get_post_translate_chain($to['post_id'], $to['blog_id']);

	// check for translation match errors
	if( $from['blog_id'] == $to['blog_id'] )
	{
		$errors[] = "It seems you're trying to add the same language";
	}

	if( !empty($to_info['chain']) )
	{
		$blogs_maped_to = $blogs_maped_new = array();
		// prepare pairs blog_id => post_id (same as language => post_id)
		foreach( $to_info['chain'] as $trans )
		{
			$blogs_maped_to[$trans->blog_id] = $trans->post_id;
		}

		// this means we're trying to add the language, which we added before
		if( isset($blogs_maped_to[$from['blog_id']]) )
		{
			$errors[] = "You already added this language, please remove previous match first.";
		}

		// now check the relation chain to have intersections
		if( !empty($from_info['chain']) )
		{
			// again: prepare pairs blog_id => post_id (same as language => post_id)
			foreach( $from_info['chain'] as $trans )
			{
				$blogs_maped_from[$trans->blog_id] = $trans->post_id;
			}

			// now search for intersection for same language. if post_ids are different - we can't map - this is error
			foreach( $blogs_maped_to as $maped_blog_id => $maped_post_id )
			{
				if( isset($blogs_maped_from[$maped_blog_id]) && $blogs_maped_from[$maped_blog_id] != $maped_post_id )
				{
					$errors[] = "The post, which you're trying to add, already has some languages assigned and they conflict with your Available translations.";
				}
			}
		}
	}

	if( empty($errors) )
	{
		// chain id is always the ID of current post translation
		$chain_id = !empty($to_info['row']) ? $to_info['row']->translation_id : null;
		if( empty($to_info['row']) )
		{
			// if we don't have one - we create new row and get autoincremented ID
			$wpdb->insert($wpdb->post_translations, array(
				'blog_id' => $to['blog_id'],
				'post_id' => $to['post_id']
					), array('%d', '%d')
			);
			$chain_id = $wpdb->insert_id;
			// update chain id field
			$wpdb->update($wpdb->post_translations, array('chain_id' => $chain_id), array('id' => $chain_id));
		}

		// if we don't have record for related post - create it
		if( empty($from_info['row']) )
		{
			$wpdb->insert($wpdb->post_translations, array(
				'blog_id' => $from['blog_id'],
				'post_id' => $from['post_id'],
				'chain_id' => $chain_id,
					), array('%d', '%d', '%d')
			);
		}

		// if we have some rows already - we can merge it in one big array and update in one look
		$rows_to_update = array();
		// add rows from origin post and chain
		if( !empty($to_info['row']) )
			$rows_to_update = array_merge($rows_to_update, array($to_info['row']));
		if( !empty($to_info['chain']) )
			$rows_to_update = array_merge($rows_to_update, $to_info['chain']);
		// add rows from new post and its chains
		if( !empty($from_info['row']) )
			$rows_to_update = array_merge($rows_to_update, array($from_info['row']));
		if( !empty($from_info['chain']) )
			$rows_to_update = array_merge($rows_to_update, $from_info['chain']);

		foreach( $rows_to_update as $row )
		{
			$wpdb->update($wpdb->post_translations, array('chain_id' => $chain_id), array('id' => $row->translation_id));
		}
	}

	return $errors;
}

function jcml_ajax_post_detach_language(){
	global $wpdb;

	$post_id = (int) $_POST['id'];
	$translation_id = (int) $_POST['translation_id'];
	$wpdb->delete($wpdb->post_translations, array('id' => $translation_id));

	$translations = jcml_get_post_translate_chain($post_id, null, true);
	$results = array(
		'status' => 'ok',
		'errors' => '',
		'translations' => $translations,
	);
	jcml_render_json($results);
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function jcml_post_translate_save_box( $post_id ){

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if( !isset($_POST['jcml_translate_box_nonce']) )
	{
		return;
	}

	// Verify that the nonce is valid.
	if( !wp_verify_nonce($_POST['jcml_translate_box_nonce'], 'jcml_translate_box') )
	{
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
	{
		return;
	}

	// Check the user's permissions.
	if( isset($_POST['post_type']) && 'page' == $_POST['post_type'] )
	{

		if( !current_user_can('edit_page', $post_id) )
		{
			return;
		}
	}
	else
	{

		if( !current_user_can('edit_post', $post_id) )
		{
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if( !isset($_POST['jcml_translate_of']) )
	{
		return;
	}

	// check that the post is not the revision
	$post = get_post($post_id);
	if( $post->post_type == 'revision' )
	{
		return;
	}

	// actually add translation
	$translate_of = $_POST['jcml_translate_of'];

	$blog_id = get_current_blog_id();
	$map_errors = jcml_map_posts(
			array('blog_id' => $translate_of['blog_id'], 'post_id' => $translate_of['post_id']), array('blog_id' => $blog_id, 'post_id' => $post_id)
	);
}

add_action('save_post', 'jcml_post_translate_save_box');
