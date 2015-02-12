<?php

/**
 * Add custom page to network menu
 */

function jcml_network_menu(){
	add_submenu_page('sites.php', 'Language mapping', 'Site Languages', 'manage_sites', 'jcml-lang-settings', 'jcml_network_language_settings');
	add_submenu_page( 'settings.php', 'Language mapping', 'Just Multilingual Settings', 'manage_sites', 'jcml-lang-posttypes', 'jcml_network_language_posttypes' );	

}

add_action('network_admin_menu', 'jcml_network_menu');

/*
 * Post type page, where admin can configure post types, that should be translatable
 */
function jcml_network_language_posttypes(){
	global $wpdb;

	$errors = $messages = [];

	// get current settings
	$post_types_raw = get_post_types();
	$post_types_objects = array();
	$post_types = array();
	foreach($post_types_raw as $key => $val){
		$obj = get_post_type_object($key);
		if($obj->public && $obj->name != 'attachment')
			$post_types[$key] = array('label' => $obj->labels->name,'checked' => 0);

	}
	$data = array();
	//if form submitted
	if( !empty($_POST['posttype']) ){

		
		foreach($post_types as $name => $arr){
			if(isset($_POST['posttype'][$name]))
				$data['jcml_lang_posttype_'.$name] = 1;
		}
		
		set_lang_post_types($data);
		
	}
	$set_types = array();
	$data = get_lang_post_types();
	
	if(!empty($data)){
		foreach($data as $key => $val){
			$key = explode('_',$key);
			$key = array_reverse($key);
			$key = $key[0];
			$post_types[$key]['checked'] = 1;
		}
	}
	
	
	
	
	include('views/network_language_posttypes.php');
}
/**
 * Network custom page to manage languages for sites
 */
function jcml_network_language_settings(){



	global $wpdb;

	$errors = $messages = [];

	// get current settings
	$settings = $wpdb->get_results("
		SELECT * 
		FROM $wpdb->blog_languages as bl
		INNER JOIN $wpdb->blogs as b ON bl.blog_id = b.blog_id 
		ORDER BY b.path");

	include('views/network_language_settings.php');
}

/**
 * Ajaxed add language action
 */
function jcml_add_language(){
	global $wpdb;


	$input = $_POST;
	$blog = array();

	//adding language mapping to DB
	if( !empty($input) )
	{
		$status = 0;
		$blog_id = 0;
		if( !empty($input['domain']) )
		{
			$input['domain'] = trim($input['domain']);
			$parts = explode('/', $input['domain'], 2);
			$blog_id = get_blog_id_from_url($parts[0], '/' . $parts[1]);
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
			$wpdb->insert($wpdb->blog_languages, array(
				'blog_id' => $blog_id,
				'language' => trim($input['language']),
				'alias' => trim($input['alias']),
					), array(
				'%d',
				'%s',
				'%s'
					)
			);
			$blog = array(
				'id' => $blog_id,
				'language' => trim($input['language']),
				'alias' => trim($input['alias']),
				'domain' => trim($input['domain']),
				'site_url' => get_site_url($blog_id),
				'bloginfo_url' => get_bloginfo('url'),
			);
			$_POST = $input = array();
			$status = 1;
		}
	}

	//return results;
	jcml_ajax_reposnse(array(
		'status' => $status,
		'errors' => $errors,
		'blog' => $blog,
	));
	die();
}

/*
 * returns language mapping by id
 */

function jcml_get_mapping(){
	global $wpdb;

	$id = explode('_', $_POST['id']);
	$id = $id[1];
	$res = $wpdb->get_results("
		SELECT * 
		FROM $wpdb->blog_languages as bl
		WHERE bl.blog_id=$id	
	    ");
	$blog_details = get_blog_details($id);
	$res = array('domain' => $blog_details->domain, 'language' => $res[0]->language, 'alias' => $res[0]->alias);
	jcml_ajax_reposnse(array('status' => 1, 'data' => $res));
}

/**
 * Ajaxed edit language action
 */
function jcml_edit_language(){
	global $wpdb;


	$values = $_POST;
	$blog = array();


	$id = explode('_', $values['id']);
	$values['id'] = $id[1];

	//removing language mapping from DB
	if( !empty($values['id']) )
	{
		$id = $values['id'];
		$wpdb->update($wpdb->blog_languages, array('language' => $values['language'], 'alias' => $values['alias']), array('blog_id' => $id));
		$blog = get_blog_details($id);

		$blog = array(
			'id' => $id,
			'language' => $values['language'],
			'alias' => $values['alias'],
			'domain' => trim($blog->domain),
			'site_url' => get_site_url($id),
			'bloginfo_url' => get_bloginfo('url'),
		);
	}

	//return results;
	jcml_ajax_reposnse(array(
		'blog' => $blog,
	));
	die();
}

/**
 * Ajaxed remove (detach) language action
 */
function jcml_remove_language(){
	global $wpdb;

	//removing language mapping from DB
	if( !empty($_POST['id']) )
	{
		$id = explode('_', $_POST['id']);
		$id = $id[1];
		$wpdb->delete($wpdb->blog_languages, array('blog_id' => $id));
		$blog = get_blog_details($id);
		if( $blog->blog_id == $id )
			$message = 'Language detached from site: <a href="' . get_site_url($blog->blog_id) . '" target="_blank">' . $blog->domain . $blog->path . '</a>';
	}
	jcml_ajax_reposnse(array('status' => "1", 'message' => $message));
}

function jcml_ajax_reposnse( $resp, $format = 'json' ){
	if( $format == 'json' )
	{
		$resp = json_encode($resp);
		header("Content-Type: application/json");
	}
	echo $resp;
	exit();
}

function jcml_network_include_assets(){
	/**
	 * 	add custom scripts
	 */
	// ui autocomplete

	wp_enqueue_script( 'jquery-ui-autocomplete' );


	// network page script
	wp_register_script(
			'jcml_network_page', jcml_plugin_url() . '/assets/jcml_network_page.js', array('jquery')
	);
	wp_enqueue_script('jcml_network_page');

	wp_register_script(
			'language_page', jcml_plugin_url() . '/assets/jcml_language_page.js', array('jquery')
	);
	wp_enqueue_script('language_page');

	/**
	 * add custom styles
	 */
	wp_register_style('ui-autocomplete', jcml_plugin_url() . '/assets/jquery-ui-1.11.autocomplete.min.css');
	wp_enqueue_style('ui-autocomplete');
	wp_register_style('jcml_network_page', jcml_plugin_url() . '/assets/jcml_network_page.css');
	wp_enqueue_style('jcml_network_page');
}

/**
 * return the array of not-mapped blogs
 * @global wpdb $wpdb
 */
function jcml_autocomplete_get_blogs_domains_disabled( $return = false, $strict = false ){
	$term = trim($_POST['term']);

	global $wpdb;

	$search_string = $term;
	if( !$strict )
		$search_string = '%' . $search_string . '%';

	// get blogs
	$not_maped_blogs = $wpdb->get_results("
		SELECT *, CONCAT(b.domain,b.path) as full_path
		FROM $wpdb->blogs as b
		LEFT JOIN $wpdb->blog_languages as bl ON bl.blog_id = b.blog_id 
		WHERE bl.language IS NULL 
		HAVING full_path LIKE '$search_string'
		ORDER BY b.path");

	$results = array();
	foreach( $not_maped_blogs as $blog )
	{
		$results[] = $blog->domain . $blog->path;
	}

	if( $return )
		return $results;

	jcml_ajax_reposnse($results);
}
