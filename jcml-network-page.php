<?php

/**
 * Add custom page to network menu
 */
function jcml_network_menu(){
	add_submenu_page( 'sites.php', 'Language mapping', 'Languages', 'manage_sites', 'jcmst-lang-settings', 'jcml_network_language_settings' );	
	add_submenu_page( 'sites.php', 'Language mapping', 'Post Types', 'manage_sites', 'jcml-lang-posttypes', 'jcml_network_language_posttypes' );	
}
add_action('network_admin_menu', 'jcml_network_menu');

/*
 * Post type page, where admin can configure post types, that should be translatable
 */
function jcml_network_language_posttypes(){
	global $wpdb;

	$errors = $messages = [];

	// get current settings
	$post_types = get_post_types();
	$post_types_objects = array();
	
	foreach($post_types as $key => $val){
		$obj = get_post_type_object($key);
		$post_types[$key] = array('label' => $obj->labels->name,'checked' => 0);
		
	}
	//if form submitted
	if( !empty($_POST['posttype']) ){
		
		foreach($post_types as $name => $arr){
			if(isset($_POST['posttype'][$name]))
				add_option('jcml_lang_posttype_'.$name,1);
			else
				delete_option('jcml_lang_posttype_' .$name);
		}
		
	}
	$set_types = array();
	
	foreach($post_types as $key => $val){
		$set_type = get_option('jcml_lang_posttype_'.$key);
		
		if($set_type)
			$post_types[$key]['checked'] = 1;
	}
	
	
	
	include('views/network_language_posttypes.php');
}
/**
 * Network custom page to manage languages for sites
 */
function jcml_network_language_settings(){
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
	
	// get current settings
	$settings = $wpdb->get_results("
		SELECT * 
		FROM $wpdb->blog_languages as bl
		INNER JOIN $wpdb->blogs as b ON bl.blog_id = b.blog_id 
		ORDER BY b.path");
	
	include('views/network_language_settings.php');
}

function jcml_network_include_assets(){
	/**
	 *	add custom scripts
	 */
	// ui autocomplete
	wp_enqueue_script( 'jquery-ui-autocomplete' );
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