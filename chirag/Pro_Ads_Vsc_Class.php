<?php
// don't load directly
if (!defined('ABSPATH')) die('-1');



class WPPROADVSC_AddonClass 
{
    function __construct() 
	{
		add_action( 'init', array( $this, 'integrateProAdWithVC' ) );
	}
	
	public function integrateProAdWithVC() 
	{	
        // Check if Visual Composer is installed
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
			// Display notice that Visual Compser is required
			//add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
			return;
		}
		
		global $pro_ads_adzones;
		
		// Select all available adzones
		//$adzones = $pro_ads_adzones->pro_ad_load_adzones( "ORDER BY name ASC" );
		$adzones = $pro_ads_adzones->get_adzones();
		$adzone_arr = array();
		foreach( $adzones as $adzone )
		{
			$adzone_arr[$adzone->post_title] = $adzone->ID;
		}
		
		vc_map( array(
			"name" => __("Pro Advertising"),
			"description" => __("Add advertisements to your website", 'wpproads'),
			"base" => "pro_ad_display_adzone",
			"icon" => plugins_url('../images/banners_icon.png', __FILE__),
			"class" => "",
			"category" => __('Content','wpproads'),
			//'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
			//'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
			"params" => array(
				array(
					"type" => "html",
					"holder" => "div",
					"class" => "",
					"heading" => __("<h2 style='margin-bottom:0'>Adzone</h2>", 'wpproads'),
					"description" => __("<hr>", 'wpproads')
				),
				array(
					"type" => "dropdown",
					"holder" => "div",
					"class" => "",
					"heading" => __("Adzone ID", "wpproads"),
					"param_name" => "id",
					"value" => $adzone_arr,
					//"value" => array( 'name 1' => "2", 'name 2' => "3", 'name 3' => "6"),
					"description" => __("Select the adzone you want to add.", "wpproads") 
				),
				array(
					"type" => "html",
					"holder" => "div",
					"class" => "",
					//"heading" => __("<h2 style='margin-bottom:0'>Popup Ads</h2>", 'wpproads'),
					"description" => __("<div style='background:#EEE; padding:5px;'><h2 style='margin:0'><small><em>Optional</em> - </small> Popup Ads</h2> Popup ads allow you to open the adzone in a popup window.<hr></div>", 'wpproads')
				),
				array(
					"type" => "dropdown",
					"holder" => "div",
					"class" => "",
					"heading" => __("Adzone is Popup"),
					"param_name" => "popup",
					"value" => array( __('No','wpproads') => "", __('Yes','wpproads') => "1"),
					"description" => __("Select if you want the adzone to open as a popup.<br><br>", "wpproads") 
				),
				array(
					"type" => "colorpicker",
					"holder" => "div",
					"class" => "",
					"heading" => __("Popup Background Color", 'wpproads'),
					"param_name" => "popup_bg",
					"value" => '', //Default Red color
					"description" => __("Select the Popup background color", 'wpproads')
				),
				array(
					"type" => "dropdown",
					"holder" => "div",
					"class" => "",
					"heading" => __("Adzone is Popup"),
					"param_name" => "popup_opacity",
					"value" => array('', 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1),
					"description" => __("Select the Popup background opacity.<br><br>", "wpproads") 
				),
				array(
					"type" => "html",
					"holder" => "div",
					"class" => "",
					//"heading" => __("<h2 style='margin-bottom:0'>Popup Ads</h2>", 'wpproads'),
					"description" => __("<div style='background:#EEE; padding:5px;'><h2 style='margin:0'><small><em>Optional</em> - </small> Background Ads</h2> Background ads make it possible to load your adzone as the post/page background.<hr></div>", 'wpproads')
				),
				array(
					"type" => "dropdown",
					"holder" => "div",
					"class" => "",
					"heading" => __("Adzone is Background"),
					"param_name" => "background",
					"value" => array( __('No','wpproads') => "", __('Yes','wpproads') => "1"),
					"description" => __("Select if you want the adzone to loaded as the page background.", "wpproads") 
				),
				array(
					"type" => "textfield",
					"holder" => "div",
					"class" => "",
					"heading" => __("Container", 'wpproads'),
					"param_name" => "container",
					"value" => __("", 'wpproads'),
					"description" => __("Add the ID of your websites main container. (default <strong>body</strong>)", 'wpproads')
				),
				array(
					"type" => "colorpicker",
					"holder" => "div",
					"class" => "",
					"heading" => __("Background Color", 'wpproads'),
					"param_name" => "bg_color",
					"value" => '', //Default Red color
					"description" => __("Select the page background color", 'wpproads')
				),
				array(
					"type" => "dropdown",
					"holder" => "div",
					"class" => "",
					"heading" => __("Stretch"),
					"param_name" => "stretch",
					"value" => array( __('No','wpproads') => "", __('Yes','wpproads') => "1"),
					"description" => __("Stretch the background image to the full width of the page.", "wpproads") 
				),
				array(
					"type" => "dropdown",
					"holder" => "div",
					"class" => "",
					"heading" => __("Repeat"),
					"param_name" => "repeat",
					"value" => array( __('No','wpproads') => "", __('Yes','wpproads') => "1"),
					"description" => __("", "wpproads") 
				),
			)
		) );
	}

	
	
    /*Show notice if your plugin is activated but Visual Composer is not
    
    public function showVcVersionNotice() 
	{
        $plugin_data = get_plugin_data(__FILE__);
        echo '<div class="updated"><p>'.sprintf(__('<strong>%s</strong> requires <strong><a href="http://bit.ly/vcomposer" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'wpproads'), $plugin_data['Name']).'</p></div>';
    }
	*/
}
?>