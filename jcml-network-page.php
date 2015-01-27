<?php

/**
 * Add custom page to network menu
 */
function jcml_network_menu()
{
	add_submenu_page('sites.php', 'Language mapping', 'Languages', 'manage_sites', 'jcmst-lang-settings', 'jcml_network_language_settings');
}

add_action('network_admin_menu', 'jcml_network_menu');

/**
 * Network custom page to manage languages for sites
 */
function jcml_network_language_settings()
{

	wp_register_script(
			'language_page', jcml_plugin_url() . '/assets/jcml_language_page.js', array('jquery')
	);
	wp_enqueue_script('language_page');

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
function jcml_add_language()
{
	global $wpdb;

	$language = unserialize($_POST['language']);
	$values = array();
	$blog = array();
	parse_str($_POST['language'], $values);

	//adding language mapping to DB
	if( !empty($values) )
	{
		$status = 0;
		$input = $values['language'];

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
	jcml_render_json(array(
		'status' => $status,
		'errors' => $errors,
		'blog' => $blog,
	));
	die();
}

/**
 * Ajaxed add language action
 */
function jcml_edit_language()
{
	global $wpdb;
	
	$language = unserialize($_POST['language']);
	$values = array();
	$blog = array();
	parse_str($_POST['language'], $values);
	
	$id = explode('_',$values['language']['id']);
	$values['language']['id'] = $id[1];
	
		//removing language mapping from DB
	if( !empty($values['language']['id']) )
	{
		$id = $values['language']['id'];
		$query = $wpdb->update($wpdb->blog_languages, array('language' => $values['language']['language'],'alias' => $values['language']['alias']), array('blog_id' => $id));
		$blog = get_blog_details($id);
		
		$blog = array(
				'id' => $id,
				'language' =>  $values['language']['language'],
				'alias' => $values['language']['alias'],
				'domain' => trim($blog->domain),
				'site_url' => get_site_url($id),
				'bloginfo_url' => get_bloginfo('url'),
			);
		
	}
	
	//return results;
	jcml_render_json(array(
		'blog' => $blog,
	));
	die();
}

/**
 * Ajaxed remove (detach) language action
 */
function jcml_remove_language()
{
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

function jcml_ajax_reposnse( $resp, $format = 'json' )
{
	if( $format == 'json' )
	{
		$resp = json_encode($resp);
		header("Content-Type: application/json");
	}
	echo $resp;
	exit();
}

function jcml_network_include_assets()
{
	/**
	 * 	add custom scripts
	 */
	// ui autocomplete
	wp_register_script(
			'ui-autocomplete', jcml_plugin_url() . '/assets/jquery-ui1.11.autocomplete.min', array('jquery')
	);
	wp_enqueue_script('ui-autocomplete');

	// network page script
	wp_register_script(
			'jcml_network_page', jcml_plugin_url() . '/assets/jcml_network_page.js', array('jquery')
	);
	wp_enqueue_script('jcml_network_page');

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
function jcml_autocomplete_get_blogs_domains_disabled( $return = false, $strict = false )
{
	$term = trim($_POST['term']);

	global $wpdb;

	$search_string = mysql_escape_string($term);
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

	jcml_render_json($results);
}
