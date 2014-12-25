<?php

/** 
 * This file contains functions which can be used inside themes or post shortcodes
 * 
 * @version UNDER CONSTRUCTION
 */

/**
 * get language switcher data for current post 
 * @global post $post
 */
function jcml_get_language_switcher_data(){
	global $post;
	$blog_id			= get_current_blog_id();
	$full_chain			= jcml_get_post_translate_chain($post->ID);
	$chain				= $full_chain['chain'];
	$current_lang_obj	= jcml_get_blog_language($blog_id);
	$current_lang_name	= $current_lang_obj->alias;
	$current_lang		= $current_lang_obj->language;
	$languages			= jcml_get_languages();	
	
	//add translated links
	if(!empty($chain)){
		foreach($chain as $lang_post){
			$links[$lang_post->language] = array(
				'url'	=> get_permalink($lang_post->post->ID),
				'alias' => $lang_post->alias
			);
		}
	}
 
	//add untranslated links (icluding current link)
	foreach($languages as $lang){
		//already in translated list. 
		if(isset($links[$lang->language]))
			continue;
		
		if($current_lang == $lang->language && is_singular()){
				$links[$lang->language] = array(
				'url'	=> get_permalink($post->ID),
				'alias' => $lang->alias,
				'active'=> true 
			);
		}
		else{
			$links[$lang->language] = array(
				'url'	=> 'http://' . $lang->domain . '/' ,
				'alias' => $lang->alias,
				'active'=> false
			);	
		}
	}
	
	return $languages;
}

define('JCMST_UOTPUT_LIST', 'list');
define('JCMST_UOTPUT_LINKS', 'links');

/**
 * print language switcher for current post 
 */
function jcml_get_language_switcher($output_type = JCMST_UOTPUT_LIST){

	$languages = jcml_get_language_switcher_data();
	
	include(dirname(__FILE__) . '/views/language_switcher.php');
}
