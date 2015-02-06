<?php
class Pro_Ads_Tinymce
{
	function __construct() {
		add_action( 'admin_init', array( $this, 'wpproads_action_admin_init' ) );
	}
	
	function wpproads_action_admin_init() {
		// only hook up these filters if we're in the admin panel, and the current user has permission
		// to edit posts and pages
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			add_filter( 'mce_buttons', array( $this, 'wpproads_filter_mce_button' ) );
			add_filter( 'mce_external_plugins', array( $this, 'wpproads_filter_mce_plugin' ) );
		}
	}
	
	function wpproads_filter_mce_button( $buttons ) {
		// add a separation before our button, here our button's id is "mygallery_button"
		array_push( $buttons, '|', 'wpproads_button' );
		return $buttons;
	}
	
	function wpproads_filter_mce_plugin( $plugins ) {
		// this plugin file will work the magic of our button
		$plugins['wpproads'] = WP_ADS_TPL_URL . '/js/proads_tinymce.js';
		return $plugins;
	}
}
?>