<?php
/* @var $wpdb wpdb */

/**
 * DB scheme for custom tables
 * @global wpdb $wpdb
 */
function jcml_setup_db_scheme(){
	global $wpdb;
	
	$sql = "CREATE TABLE $wpdb->blog_languages (
blog_id bigint(20) UNSIGNED NOT NULL,
language VARCHAR(50) NOT NULL,
alias VARCHAR(255) NULL DEFAULT NULL,
PRIMARY KEY  blog_id (blog_id)
);
CREATE TABLE $wpdb->post_translations (
	id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	blog_id bigint(20) UNSIGNED NOT NULL,
	post_id bigint(20) UNSIGNED NOT NULL,
	chain_id bigint(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY translation_id (id),
	KEY blog_id (blog_id),
	KEY post_id (post_id),
	KEY chain_id (chain_id)
);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
