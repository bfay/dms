<?php
// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )   
 exit ();
global $wpdb;require_once('dvin-woocommerce-quotelist.php');	// Delete option from options table
delete_option( 'dvin_wcql_settings' );
delete_option( 'dvin_wcql_email_subject');
delete_option( 'dvin_wcql_email_msg');
delete_option( 'dvin_wcql_admin_email');//delete pages created for this plugin
wp_delete_post(get_option('dvin_quotelist_pageid'),true);
delete_option( 'dvin_quotelist_pageid');
?>