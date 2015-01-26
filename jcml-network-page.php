<?php

/**
 * Add custom page to network menu
 */
function jcml_network_menu(){
	add_submenu_page( 'sites.php', 'Language mapping', 'Languages', 'manage_sites', 'jcmst-lang-settings', 'jcml_network_language_settings' );	
}
add_action('network_admin_menu', 'jcml_network_menu');

/**
 * Network custom page to manage languages for sites
 */
function jcml_network_language_settings(){
	
	wp_register_script(
			'add_language',
			jcml_plugin_url().'/assets/add_language.js',
			array('jquery')
		);
	wp_enqueue_script('add_language');
	
	global $wpdb;
	
	$errors = $messages = [];
	/*
	if( !empty($_POST['add-language']) )
	{
		$input = $_POST['language'];
		if( empty($input['domain']) || empty($input['language']) || empty($input['alias']) )
		{
			$errors[] = 'Please fill all required fields';
		}
		
		$blog_id = 0;
		if( !empty($input['domain']) )
		{
			$input['domain'] = trim($input['domain']);
			$parts = explode('/', $input['domain'], 2);
			$blog_id = get_blog_id_from_url( $parts[0], '/'.$parts[1] );
			$blog = get_blog_details($blog_id);
			
			if( $blog_id < 1 || empty($blog) )
			{
				$errors[] = 'Wrong blog domain! Please choose correct path from autocomplete box.';
			}
		}
		
		if( empty($errors) )
		{
			// check again that we don't have this mapping
			$_POST['term'] = $input['domain'];
			$not_maped = jcml_autocomplete_get_blogs_domains_disabled(true, true);
			if( !in_array($input['domain'], $not_maped) )
			{
				$errors[] = 'This domain is already maped. Please detach it first.';
			}
		}
		
		if( empty($errors) )
		{
			// add mapping
			$wpdb->insert($wpdb->blog_languages, 
				array(
					'blog_id' => $blog_id,
					'language' => trim($input['language']),
					'alias' => trim($input['alias']),
				),
				array(
					'%d',
					'%s',
					'%s'
				)
			);
			$messages[] = 'Language added';
			$_POST = $input = array();
		}
	}
	
	if( !empty($_GET['detach']) )
	{
		$wpdb->delete($wpdb->blog_languages, array('blog_id' => $_GET['detach']));
		$blog = get_blog_details($_GET['detach']);
		if( $blog->blog_id == $_GET['detach'] )
			$messages[] = 'Language detached from site: <a href="'.get_site_url($blog->blog_id).'" target="_blank">'.$blog->domain.$blog->path.'</a>';
	}
	*/
	// get current settings
	$settings = $wpdb->get_results("
		SELECT * 
		FROM $wpdb->blog_languages as bl
		INNER JOIN $wpdb->blogs as b ON bl.blog_id = b.blog_id 
		ORDER BY b.path");
	
	include('views/network_language_settings.php');
}

function jcml_add_language(){
	global $wpdb;
	
	$language = unserialize($_POST['language']);
	$values = array();
    parse_str($_POST['language'], $values);
	
	if( !empty($values) )
	{
		
		$input = $values['language'];
		if( empty($input['domain']) || empty($input['language']) || empty($input['alias']) )
		{
			$errors[] = 'Please fill all required fields';
		}
		
		$blog_id = 0;
		if( !empty($input['domain']) )
		{
			$input['domain'] = trim($input['domain']);
			$parts = explode('/', $input['domain'], 2);
			$blog_id = get_blog_id_from_url( $parts[0], '/'.$parts[1] );
			$blog = get_blog_details($blog_id);
			
			if( $blog_id < 1 || empty($blog) )
			{
				$errors[] = 'Wrong blog domain! Please choose correct path from autocomplete box.';
			}
		}
		
		if( empty($errors) )
		{
			// check again that we don't have this mapping
			$_POST['term'] = $input['domain'];
			$not_maped = jcml_autocomplete_get_blogs_domains_disabled(true, true);
			if( !in_array($input['domain'], $not_maped) )
			{
				$errors[] = 'This domain is already maped. Please detach it first.';
			}
		}
		
		if( empty($errors) )
		{
			// add mapping
			$wpdb->insert($wpdb->blog_languages, 
				array(
					'blog_id' => $blog_id,
					'language' => trim($input['language']),
					'alias' => trim($input['alias']),
				),
				array(
					'%d',
					'%s',
					'%s'
				)
			);
			$messages[] = 'Language added';
			$_POST = $input = array();
		}
	}
	$clear_form = empty($errors) ? true : false;
	// get current settings
	$settings = $wpdb->get_results("
		SELECT * 
		FROM $wpdb->blog_languages as bl
		INNER JOIN $wpdb->blogs as b ON bl.blog_id = b.blog_id 
		ORDER BY b.path");
	
	//return $results;
	ob_start(); // turn on output buffering
	include('views/partials/_partial_network_languages.php');
	$languages = ob_get_contents(); // get the contents of the output buffer
	ob_end_clean();
	ob_start(); 
	include('views/partials/_partial_errors_and_messages.php');
	$messages = ob_get_contents(); 
	ob_end_clean();
	jcml_render_json(array('languages' => $languages, 'messages' => $messages, 'clear_form' => $clear_form));
	die();
}

function jcml_remove_language(){
	global $wpdb;
	
	if( !empty($_POST['id']) )
	{
		$wpdb->delete($wpdb->blog_languages, array('blog_id' => $_POST['id']));
		$blog = get_blog_details($_POST['id']);
		if( $blog->blog_id == $_POST['id'] )
			$messages[] = 'Language detached from site: <a href="'.get_site_url($blog->blog_id).'" target="_blank">'.$blog->domain.$blog->path.'</a>';
	}
	
	
	$settings = $wpdb->get_results("
		SELECT * 
		FROM $wpdb->blog_languages as bl
		INNER JOIN $wpdb->blogs as b ON bl.blog_id = b.blog_id 
		ORDER BY b.path");
	
	ob_start(); // turn on output buffering
	include('views/partials/_partial_network_languages.php');
	$languages = ob_get_contents(); // get the contents of the output buffer
	ob_end_clean();
	ob_start(); 
	include('views/partials/_partial_errors_and_messages.php');
	$messages = ob_get_contents(); 
	ob_end_clean();
	
	//return $results;
	jcml_render_json(array('languages' => $languages, 'messages' => $messages));
	die();
}


function jcml_network_include_assets(){
	/**
	 *	add custom scripts
	 */
	// ui autocomplete
	wp_register_script(
			'ui-autocomplete',
			jcml_plugin_url().'/assets/jquery-ui1.11.autocomplete.min',
			array('jquery')
		);
	wp_enqueue_script('ui-autocomplete');

	// network page script
	wp_register_script(
			'jcml_network_page',
			jcml_plugin_url().'/assets/jcml_network_page.js',
			array('jquery')
		);
	wp_enqueue_script('jcml_network_page');

	/**
	 * add custom styles
	 */
	wp_register_style('ui-autocomplete', jcml_plugin_url().'/assets/jquery-ui-1.11.autocomplete.min.css');
	wp_enqueue_style('ui-autocomplete');
	wp_register_style('jcml_network_page', jcml_plugin_url().'/assets/jcml_network_page.css');
	wp_enqueue_style('jcml_network_page');
}

/**
 * return the array of not-mapped blogs
 * @global wpdb $wpdb
 */
function jcml_autocomplete_get_blogs_domains_disabled( $return = false, $strict = false ){
	$term = trim($_POST['term']);
	
	global $wpdb;
	
	$search_string = mysql_escape_string($term);
	if( !$strict ) $search_string = '%'.$search_string.'%';
	
	// get blogs
	$not_maped_blogs = $wpdb->get_results("
		SELECT *, CONCAT(b.domain,b.path) as full_path
		FROM $wpdb->blogs as b
		LEFT JOIN $wpdb->blog_languages as bl ON bl.blog_id = b.blog_id 
		WHERE bl.language IS NULL 
		HAVING full_path LIKE '$search_string'
		ORDER BY b.path");
	
	$results = array();
	foreach($not_maped_blogs as $blog){
		$results[] = $blog->domain . $blog->path;
	}
	
	if( $return )
		return $results;
	
	jcml_render_json($results);
}