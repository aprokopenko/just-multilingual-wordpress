<?php

/**
 * Add custom page to network menu
 */
function jcmst_network_menu(){
	add_submenu_page( 'sites.php', 'Language mapping', 'Languages', 'manage_sites', 'jcmst-lang-settings', 'jcmst_network_language_settings' );	
}
add_action('network_admin_menu', 'jcmst_network_menu');

/**
 * Network custom page to manage languages for sites
 */
function jcmst_network_language_settings(){
	global $wpdb;
	
	$errors = $messages = [];
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
			$not_maped = jcmst_autocomplete_get_blogs_domains_disabled(true, true);
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
	
	// get current settings
	$settings = $wpdb->get_results("
		SELECT * 
		FROM $wpdb->blog_languages as bl
		INNER JOIN $wpdb->blogs as b ON bl.blog_id = b.blog_id 
		ORDER BY b.path");
	
	include('views/network_language_settings.php');
}

function jcmst_network_include_assets(){
	/**
	 *	add custom scripts
	 */
	// ui autocomplete
	wp_register_script(
			'ui-autocomplete',
			WP_PLUGIN_URL.'/jcms-translate/assets/jquery-ui1.11.autocomplete.min',
			array('jquery')
		);
	wp_enqueue_script('ui-autocomplete');

	// network page script
	wp_register_script(
			'jcmst_network_page',
			WP_PLUGIN_URL.'/jcms-translate/assets/jcmst_network_page.js',
			array('jquery')
		);
	wp_enqueue_script('jcmst_network_page');

	/**
	 * add custom styles
	 */
	wp_register_style('ui-autocomplete', WP_PLUGIN_URL.'/jcms-translate/assets/jquery-ui-1.11.autocomplete.min.css');
	wp_enqueue_style('ui-autocomplete');
	wp_register_style('jcmst_network_page', WP_PLUGIN_URL.'/jcms-translate/assets/jcmst_network_page.css');
	wp_enqueue_style('jcmst_network_page');
}

/**
 * return the array of not-mapped blogs
 * @global wpdb $wpdb
 */
function jcmst_autocomplete_get_blogs_domains_disabled( $return = false, $strict = false ){
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
	
	jcmst_render_json($results);
}