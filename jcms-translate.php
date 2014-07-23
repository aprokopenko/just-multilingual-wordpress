<?php
/**
 * @package JCMST
 * @version 0.1
 */
/*
Plugin Name: Wordpress Multisite as Multilanguage by JustCoded
Description: This plugin convert Multisite installation into full-featured multilingual site. (New sites are used as new languages and not as new user blogs)
Author: Alex Prokopenko
Version: 0.1
Author URI: http://justcoded.com
*/
/* @var $wpdb wpdb */

defined('ABSPATH') or die("No script kiddies please!");
define('JCMST_PATH', dirname(__FILE__));
define('JCMST_DB_VERSION', 0.12);

if( !function_exists('pa') )
{
	function pa($mixed, $stop = false) {
		$ar = debug_backtrace(); $key = pathinfo($ar[0]['file']); $key = $key['basename'].':'.$ar[0]['line'];
		$print = array($key => $mixed); echo( '<pre>'.(print_r($print,1)).'</pre>' );
		if($stop == 1) exit();
	}
}

include_once JCMST_PATH.'/jcmst-install.php';
include_once JCMST_PATH.'/jcmst-network-page.php';
include_once JCMST_PATH.'/jcmst-post-edit.php';

function jcmst_init(){
	global $wpdb, $jcmst_inited;
	
	if(!empty($jcmst_inited)) return;
	
	$wpdb->blog_languages = $wpdb->base_prefix . 'blog_languages';
	$wpdb->post_translations = $wpdb->base_prefix . 'post_translations';
	
	// run install
	$db_ver = get_option('jcmst_db_version', 0);
	if( $db_ver < JCMST_DB_VERSION )
	{
		jcmst_setup_db_scheme();
		update_option('jcmst_db_version', JCMST_DB_VERSION);
	}
	
	// init admin ajax actions
	add_action('wp_ajax_jcmst_get_blogs_domains_disabled', 'jcmst_autocomplete_get_blogs_domains_disabled');
	add_action('wp_ajax_jcmst_post_search_by_title', 'jcmst_autocomplete_jcmst_post_search_by_title');
	add_action('wp_ajax_jcmst_post_search_by_url', 'jcmst_autocomplete_jcmst_post_search_by_url');
	add_action('wp_ajax_jcmst_post_map_language', 'jcmst_ajax_post_map_language');
	add_action('wp_ajax_jcmst_post_detach_language', 'jcmst_ajax_post_detach_language');
	
	if( is_admin() ){
		if( !empty($_GET['page']) && $_GET['page'] == 'jcmst-lang-settings' ){
			jcmst_network_include_assets();
		}
		if( strpos($_SERVER['SCRIPT_NAME'], 'post') !== false ){
			jcmst_post_edit_include_assets();
		}
	}
}
add_action( 'admin_init', 'jcmst_init' );

/**
 * check is blog with $blog_id is mapped to some language.
 * 
 * @param int $blog_id	default to current blog
 */
function jcmst_is_blog_mapped($blog_id = null){
	if( !$blog_id )
		$blog_id = get_current_blog_id();
	
	$lang = jcmst_get_blog_language($blog_id);
	if( !empty($lang) ) return true;
	
	return false;
}

/**
 * get mapping of language to a specific blog
 * @param int $blog_id
 * @global wpdb $wpdb
 */
function jcmst_get_blog_language( $blog_id ){
	global $wpdb;
	
	$blog_id = (int)$blog_id;
	$lang = $wpdb->get_row("SELECT * FROM $wpdb->blog_languages WHERE blog_id = ".$blog_id);
	return $lang;
}

/**
 * get select option values for mapped languages
 * @global wpdb $wpdb
 * @param int $exclude  blog_id of language to exclude from the languages
 */
function jcmst_get_language_options( $exclude = 0 ){
	global $wpdb;
	
	$languages = jcmst_get_languages($exclude);
	
	$options = array();
	foreach($languages as $lang){
		$options[$lang->blog_id] = $lang->alias;
	}
	
	return $options;
}

/**
 * get objects of languages inited in system
 * @global wpdb $wpdb
 * @param int $exclude
 */
function jcmst_get_languages($exclude = 0){
	global $wpdb;
	
	$sql = "SELECT * FROM $wpdb->blog_languages bl INNER JOIN $wpdb->blogs as b ON b.blog_id = bl.blog_id"; 
	if( !empty($exclude) )
	{
		if(!is_array($exclude)) $exclude = array($exclude);
		foreach($exclude as $key => $blog_id){
			$exclude[$key] = (int)$blog_id;
		}
		$sql .= " WHERE bl.blog_id NOT IN (" . implode(',', $exclude) . ") ";
	}

	$sql .= " ORDER BY bl.alias ASC";
	
	$languages = $wpdb->get_results($sql);
	return $languages;
}

/**
 * generate html for select options
 * @param array $values		array of (key => value) pairs
 * @param string $selected	index of selected item
 */
function jcmst_html_options($values, $selected = null){
	$html = '';
	foreach( $values as $val => $label ) {
		$html .= '<option value="'.esc_attr($val).'" '.selected($val, $selected, false).'>'.esc_html(ucfirst($label)).'</option>' . "\n";
	}
	return $html;
}

/**
 * parse url to get: blog_id, blog domain, post slug
 * @param string $url
 */
function jcmst_parse_post_url( $url, $post_type = 'page' ){
	if( ! defined('SUBDOMAIN_INSTALL') ) return null;
		
	// make sure we always have ending slash
	$url = rtrim($url, '/') . '/';
	
	// parse parts with php standard func
	$parsed = parse_url($url);
	
	$domain = $parsed['host'];
	$blog_path = '/';
	$post_path = @$parsed['path'];
	
	if( !SUBDOMAIN_INSTALL ){
		$uri_parts = explode('/', $post_path, 2);
		
		// try to find blog with first part
		$blog_id = get_blog_id_from_url($domain, '/'.$uri_parts[0]);
		if( !empty($blog_id) )
		{
			$blog_path = '/'.$uri_parts[0];
			$post_path = @$uri_parts[1];
		}
	}
	
	// get blog_id
	$blog_id = get_blog_id_from_url($domain, $blog_path);
	
	// prepare results
	$results = array(
		'blog_domain' => $domain,
		'blog_path' => $blog_path,
		'blog_id' => $blog_id,
		'post_path' => $post_path,
		'post_id' => 0,
		'post' => null,
	);
	
	// check post
	if( !empty($blog_id) && !empty($post_path) )
	{
		switch_to_blog($blog_id);
		
		$post = get_page_by_path($post_path, OBJECT, $post_type);
		if( $post ){
			$results['post_id'] = $post->ID;
			$results['post'] = $post;
		}
		
		restore_current_blog();
	}
	
	return $results;
}

/**
 * get full info about existed tranlation chains
 * 
 * @global wpdb $wpdb
 * @param int $post_id
 * @param int $blog_id		Default: current blog
 */
function jcmst_get_post_translate_chain($post_id, $blog_id = null, $detailed = false){
	global $wpdb;
	if( is_null($blog_id) ) 
		$blog_id = get_current_blog_id();
	
	// get chain id
	$transl_row = $wpdb->get_row("
		SELECT *, pt.id as translation_id
		FROM $wpdb->post_translations as pt
		WHERE pt.blog_id = $blog_id AND pt.post_id = $post_id
	");
	
	if( empty($transl_row) ) return null;
	
	$chain_id = $transl_row->chain_id;
	
	$chain_sql = "
		SELECT *, pt.id as translation_id
		FROM $wpdb->post_translations as pt
		INNER JOIN $wpdb->blog_languages as bl ON bl.blog_id = pt.blog_id
		";
	if($detailed){
		$chain_sql .= "
			INNER JOIN $wpdb->blogs as b ON b.blog_id = pt.blog_id";
	}
	$chain_sql .= "
		WHERE pt.chain_id = $chain_id AND NOT (pt.post_id = $post_id AND pt.blog_id = $blog_id)
	";
	
	$chain = $wpdb->get_results($chain_sql);
	
	if( !empty($chain) ){
		foreach($chain as $key => $transl){
			$table_prefix = $wpdb->base_prefix;
			if( $transl->blog_id > 1 )
				$table_prefix .= $transl->blog_id . '_';
			$posts_table = $table_prefix . 'posts';
			$chain[$key]->post = jcmst_get_post($transl->post_id, $transl->blog_id);
		}
	}
	
	$info = array(
		'row' => $transl_row,
		'chain' => $chain,
	);
	return $info;
}

/**
 * get post db object by blog and post id
 * @global wpdb $wpdb
 * @param int $post_id
 * @param int $blog_id
 */
function jcmst_get_post($post_id, $blog_id = null){
	global $wpdb;
	
	if(  is_null($blog_id) ) $blog_id = get_current_blog_id();
	
	$table_prefix = $wpdb->base_prefix;
	if( $blog_id > 1 )
		$table_prefix .= $blog_id . '_';
	$posts_table = $table_prefix . 'posts';
	$post = $wpdb->get_row("SELECT * FROM $posts_table WHERE `ID` = ".$post_id);
	return $post;
}

/**
 * echo the json data with good headers and stop the script
 * @param mixed $data
 */
function jcmst_render_json( $data ){
	header('Content-type: application/json, charset=utf-8');
	echo json_encode($data);
	exit;		
}