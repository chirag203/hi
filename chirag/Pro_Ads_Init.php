<?php
/**
 * Init related functions and actions.
 *
 * @author 		Tunafish
 * @package 	wp_pro_ad_system/classes
 * @version     4.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Pro_Ads_Init' ) ) :


class Pro_Ads_Init {	
	
	
	

	public function __construct() 
	{
		global $pro_ads_main, $pro_ads_cpts, $pro_ads_advertisers, $pro_ads_campaigns, $pro_ads_banners, $pro_ads_adzones, $pro_ads_statistics, $pro_ads_templates, $pro_ads_shortcodes, $pro_ads_browser, $pro_ad_custom_widgets;
		
		// Run this on activation.
		register_activation_hook( WP_ADS_FILE, array( $this, 'install' ) );
		
		// Load Functions ------------------------------------------------- 
		require_once( WP_ADS_INC_DIR .'/ajax_functions.php');
		
		// Load Classes --------------------------------------------------- 
		require_once( WP_ADS_DIR.'classes/extends/Pro_Ads_CPT_Meta_Options.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_CPTs.php');	
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Main.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Advertisers.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Banners.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Campaigns.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Adzones.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Statistics.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Templates.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Shortcodes.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Browser.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Tinymce.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Api.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Custom_Widgets.php');
		require_once( WP_ADS_DIR.'classes/Pro_Ads_Vsc_Class.php');
		
		/* ----------------------------------------------------------------
		 * Set Classes
		 * ---------------------------------------------------------------- */
		$pro_ads_cpt_meta_options = new Pro_Ads_CPT_Meta_Options();
		$pro_ads_cpts = new Pro_Ads_CPTs();	
		$pro_ads_main = new Pro_Ads_Main();
		$pro_ads_advertisers = new Pro_Ads_Advertisers();
		$pro_ads_banners = new Pro_Ads_Banners();
		$pro_ads_campaigns = new Pro_Ads_Campaigns();
		$pro_ads_adzones = new Pro_Ads_Adzones();
		$pro_ads_statistics = new Pro_Ads_Statistics();
		$pro_ads_templates = new Pro_Ads_Templates();
		$pro_ads_shortcodes = new Pro_Ads_Shortcodes();
		$pro_ads_browser = new Pro_Ads_Browser();
		$pro_ads_tinymce = new Pro_Ads_Tinymce();
		$pro_ads_api = new Pro_Ads_Api();
		//$pro_ad_custom_widgets = new Pro_Ads_Custom_Widgets();
		$pro_ads_vsc_class = new WPPROADVSC_AddonClass();
		
		// Actions --------------------------------------------------------
		add_action('init', array( $this, 'init_method') );
		add_action('wp_head', array( $this, 'add_to_head') );
		add_action('admin_menu', array( $this,'admin_actions') );
		add_action('admin_head', array( $this, 'menu_highlight' ) );
		add_action('wp_footer', array($this, 'add_to_footer') );
		add_action('widgets_init', array($this, 'pro_ad_adzone_widgets_init') );
		add_action('admin_bar_menu', array($this, 'pro_ads_admin_bar'), 100);
		add_action('admin_notices', array($this, 'pro_ads_admin_notices') );
		
		// Custom Tables
		if(!class_exists('WP_List_Table')){
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		require_once( WP_ADS_INC_DIR.'/tables/stats.php');
		require_once( WP_ADS_INC_DIR.'/tables/stats_day.php');
		require_once( WP_ADS_INC_DIR.'/tables/stats_year.php');
		require_once( WP_ADS_INC_DIR.'/tables/stats_all.php');
	}
	
	
	
	
	
	/**
	 * Install WPPAS
	 */
	public function install() 
	{
		$this->create_tables();
		
		//$current_version = get_option( 'pro_ad_system_version', null );
		
		// Update version
		update_option( 'pro_ad_system_version', PAS()->version );
		// Update Settings
		$wpproads_enable_stats = get_option('wpproads_enable_stats', 0);
		if ( empty( $wpproads_enable_stats ) ) 
		{
			update_option( 'wpproads_enable_stats', 1);
			update_option( 'wpproads_enable_userdata_stats', 1);
		}
	}
	
	
	
	
	
	/*
	 * Init actions
	 *
	 * @access public
	 * @return null
	*/
	public function init_method() 
	{	
		global $pro_ads_bs_templates, $pro_ads_main;
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('pro_ad_jshowoff', WP_ADS_TPL_URL . '/js/jquery.jshowoff.min.js', array( 'jquery' ), false, true );
		
		wp_enqueue_style("wp_pro_ads_style", WP_ADS_TPL_URL."/css/wpproads.min.css", false, WP_ADS_VERSION, "all");
		
		wp_enqueue_script('wp_pro_ads_js_functions', WP_ADS_TPL_URL.'/js/wppas.js');
		
		define( 'WP_ADS_USER_CAN', current_user_can( WP_ADS_ROLE_USER ) && $pro_ads_main->buyandsell_is_active() ? WP_ADS_ROLE_USER : WP_ADS_ROLE_ADMIN );
		
		load_plugin_textdomain('wpproads', false, basename( dirname( __FILE__ ) ) . '/localization' );
		
		$pro_ads_main->daily_updates();
	}
	
	
	
	
	/*
	 * Admin page Init actions
	 *
	 * @access public
	 * @return null
	*/
	public function admin_actions() 
	{	
		wp_enqueue_style( 'wpproads_standard_admin', WP_ADS_TPL_URL . '/css/admin_standard.css', false, WP_ADS_VERSION, "all" );
		
		if(is_admin() && isset( $_GET['page'] ) || is_admin() && isset($_GET['post_type']) || is_admin() && isset($_GET['post']) && $this->wp_pro_ad_check_cpt( get_post_type( $_GET['post'] )) )
		{
			if( 
				isset( $_GET['page'] ) && $_GET['page'] == 'wp-pro-advertising' || 
				isset( $_GET['page'] ) && $_GET['page'] == 'wp-pro-ads-stats' ||  
				isset( $_GET['page'] ) && $_GET['page'] == 'wp-pro-ads-options' || 
				isset( $_GET['page'] ) && $_GET['page'] == 'pro-ads-bs-options' || 
				isset( $_GET['post_type'] ) && $this->wp_pro_ad_check_cpt( $_GET['post_type'] ) ||
				isset( $_GET['post'] ) && $this->wp_pro_ad_check_cpt( get_post_type( $_GET['post'] ))
				
			 ){
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_script('pro_ad_admin_js', WP_ADS_TPL_URL . '/js/admin.js', array( 'jquery' ), false, true );
				wp_enqueue_script('wp_pro_ads_js_font', WP_ADS_TPL_URL.'/js/ITCAvantGardeStd-Bold_700.font.js');
				
				
				// Statistics only
				if( isset( $_GET['page'] ) && $_GET['page'] == 'wp-pro-ads-stats' )
				{
					wp_enqueue_script('pro_ad_statistics_flot', WP_ADS_TPL_URL . '/js/jquery.flot.min.js');
					wp_enqueue_script('pro_ad_statistics_flot_time', WP_ADS_TPL_URL . '/js/jquery.flot.time.js');
					wp_enqueue_style( 'pro_ad_statistics_flot_style', WP_ADS_TPL_URL . '/css/graph.css', false, WP_ADS_VERSION, "all" );
				}
				
				// Chosen
				wp_enqueue_style( 'chosen_style', WP_ADS_INC_URL . '/chosen/chosen.css', false, WP_ADS_VERSION, "all" );
				wp_enqueue_script( 'chosen', WP_ADS_INC_URL . '/chosen/chosen.jquery.min.js', array( 'jquery' ), false, true );
				
				// Load media
				if( function_exists('wp_enqueue_media') )
				{
					wp_enqueue_media();
				}
				
				// Wordpress Colorpicker (http://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/)
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker');
				
				// Wordpress thickbox: Example link: <a href="" class="thickbox">Link</a>. (http://manchumahara.com/2010/03/22/using-wordpress-native-thickbox/) 
				wp_enqueue_script('thickbox',null,array('jquery'));
				wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
				
				
				wp_enqueue_style( 'tuna_admin_style', WP_ADS_TPL_URL . '/css/tuna-admin.min.css', false, WP_ADS_VERSION, "all" );
				wp_enqueue_style( 'wp_pro_ad_admin_style', WP_ADS_TPL_URL . '/css/admin.css', false, WP_ADS_VERSION, "all" );
				wp_enqueue_style( 'wp_pro_ad_UI_style', WP_ADS_TPL_URL . '/css/jqueryUI/jquery-ui.min.css', false, WP_ADS_VERSION, "all" );
			 }
		}
		
		
		// Create menu
		if( function_exists('add_object_page') ) 
		{
			add_object_page(
				__('Advertising', 'wpproads'), 
				__('Advertising', 'wpproads'), 
				WP_ADS_USER_CAN, 
				"wp-pro-advertising", 
				array( $this, "wp_pro_ad_dashboard"), 
				WP_ADS_URL."images/banner_icon_20.png"
			);
		}
		else
		{
			add_menu_page(
				__('Advertising', 'wpproads'), 
				__('Advertising', 'wpproads'), 
				WP_ADS_USER_CAN,  
				"wp-pro-advertising", 
				array( $this, "wp_pro_ad_dashboard")
			);
		}
		
		add_submenu_page("wp-pro-advertising", __('AD Dashboard', 'wpproads'), __('AD Dashboard', 'wpproads'), WP_ADS_USER_CAN, "wp-pro-advertising", array( $this, "wp_pro_ad_dashboard"));
		add_submenu_page("wp-pro-advertising", __('Statistics', 'wpproads'), __('Statistics', 'wpproads'), WP_ADS_USER_CAN, "wp-pro-ads-stats", array( $this, "wp_pro_ad_stats"));
		
		add_filter( 'custom_menu_order', array($this, 'submenu_order') );
	}
	
	
	function submenu_order( $menu_ord ) 
	{
		global $submenu;
	
		// Enable the next line to see all menu orders
		//echo '<pre>'.print_r($submenu['edit.php?post_type=advertising'],true).'</pre>';
		//echo '<pre>'.print_r($submenu['wp-pro-advertising'],true).'</pre>';
	
		$arr = array();
		$arr[] = $submenu['wp-pro-advertising'][4];
		$arr[] = $submenu['wp-pro-advertising'][0];
		$arr[] = $submenu['wp-pro-advertising'][1];
		$arr[] = $submenu['wp-pro-advertising'][2];
		$arr[] = $submenu['wp-pro-advertising'][3];
		$arr[] = $submenu['wp-pro-advertising'][5];
		//$arr[] = $submenu['wp-pro-advertising'][6];
		$submenu['wp-pro-advertising'] = $arr;
	
		return $menu_ord;
	}
	
	/*
	 * Highlights the correct top level admin menu item for post types.
	*/
	public function menu_highlight() 
	{
		global $menu, $submenu, $parent_file, $submenu_file, $self, $post_type, $taxonomy;

		if ( isset( $post_type ) ) {
			if ( in_array( $post_type, PAS()->cpts ) ) {
				$submenu_file = 'edit.php?post_type=' . esc_attr( $post_type );
				$parent_file  = 'wp-pro-advertising';
			}
		}
	}
	
	
	
	/*
	 * Admin menu functions
	 *
	 * @access public
	 * @return html
	*/
	public function wp_pro_ad_dashboard()
	{
		include( WP_ADS_TPL_DIR.'/pro_ad_dashboard.php');
	}
	public function wp_pro_ad_stats()
	{
		include( WP_ADS_TPL_DIR.'/pro_ad_stats.php');
	}
	public function wp_pro_ad_options()
	{
		include( WP_ADS_TPL_DIR.'/pro_ad_options.php');
	}
	
	
	
	
	
	
	
	/*
	 * Create the database tables the plugin needs to function.
	 *
	 * @access private
	 * @return void
	*/
	private function create_tables() 
	{
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$wpdb->hide_errors();
		
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) 
		{
			if ( ! empty($wpdb->charset ) ) 
			{
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty($wpdb->collate ) )
			{
				$collate .= " COLLATE $wpdb->collate";
			}
		}
		
		$sql_stats = "CREATE TABLE " . $wpdb->prefix . "pro_ad_system_stats (
			id int(11) NOT NULL AUTO_INCREMENT,
			advertiser_id mediumint(9) NOT NULL,
			campaign_id mediumint(9) NOT NULL,
			banner_id mediumint(9) NOT NULL,
			adzone_id mediumint(9) NOT NULL,
			date int(11) NOT NULL,
			time int(11) NOT NULL,
			type VARCHAR( 50 ) NOT NULL,
			ip_address VARCHAR( 50 ) NOT NULL,
			city  VARCHAR( 50 ) NOT NULL,
			country  VARCHAR( 50 ) NOT NULL,
			country_cd  VARCHAR( 5 ) NOT NULL,
			browser  VARCHAR( 50 ) NOT NULL,
			platform  VARCHAR( 50 ) NOT NULL,
			UNIQUE KEY id (id),
			KEY advertiser_id (advertiser_id),
			KEY banner_id (banner_id),
			KEY adzone_id (adzone_id),
			KEY date (date),
			KEY banner_id_date (banner_id, date)
		) ".$collate.";";
		
		dbDelta( $sql_stats );
	}
	
	
	
	
	
	
	
	/*
	 * Create Widget
	 *
	 * @access public
	 * @return string
	*/
	public function pro_ad_adzone_widgets_init() 
	{	
		register_widget('Pro_Ads_Custom_Widgets');
	}


	
	
	
		
	
	
	
	/*
	 * Add stuff to the website <head>
	 *
	 * @access public
	 * @return string
	*/
	public function add_to_head()
	{	
		$custom_css = get_option('wpproads_custom_css', '');
		
		// Load custom CSS ----------------------------------------
		if( !empty( $custom_css ))
		{
			return '<style type="text/css">'.$custom_css.'</style>';
		}
	}
	
	
	
	
	
	/*
	 * Add stuff to the website footer
	 *
	 * @access public
	 * @return string
	*/
	public function add_to_footer()
	{
		global $pro_ads_templates;
		
	}
	
	
	
	
	
	/*
	 * Allowed CPTs
	 *
	 * @access public
	 * @return int
	*/
	public function wp_pro_ad_check_cpt( $cpt )
	{
		//$cpts = array('advertisers', 'campaigns', 'banners', 'adzones');
		
		return in_array($cpt, PAS()->cpts) ? 1 : 0;
	}
	
	
	
	
	
	
	
	/**
	 * Draw the Admin Bar
	 * @global object $wp_admin_bar
	 * @return null
	 */
	public function pro_ads_admin_bar()
	{
		global $wp_admin_bar;
		
		if (!is_super_admin() || !is_admin_bar_showing() )
			return;
		
		$admin_url = get_admin_url();
		
		// Root Menu
		$wp_admin_bar->add_menu(array(
			'id' => 'wpproads_adminbar',
			'title' => __('Advertising', 'wpproads'),
			'href' => $admin_url.'admin.php?page=wp-pro-advertising',
			'meta' => array('html' => '')
		));
		// New Requests Menu
		$wp_admin_bar->add_menu(array(
			'parent' => 'wpproads_adminbar',
			'id' => 'wpproads_advertisers',
			'title' => __('Advertisers', 'wpproads'),
			'href' => $admin_url.'edit.php?post_type=advertisers'
		));
		// Add Campaign Menu
		$wp_admin_bar->add_menu(array(
			'parent' => 'wpproads_adminbar',
			'id' => 'wpproads_campaigns',
			'title' => __('Campaigns', 'wpproads'),
			'href' => $admin_url.'edit.php?post_type=campaigns'
		));
		// Campaigns Menu
		$wp_admin_bar->add_menu(array(
			'parent' => 'wpproads_adminbar',
			'id' => 'wpproads_banners',
			'title' => __('Banners', 'wpproads'),
			'href' => $admin_url.'edit.php?post_type=banners'
		));
		// Settings Menu
		$wp_admin_bar->add_menu(array(
			'parent' => 'wpproads_adminbar',
			'id' => 'wpproads_adzones',
			'title' => __('Adzones', 'wpproads'),
			'href' => $admin_url.'edit.php?post_type=adzones'
		));
		// Settings Menu
		$wp_admin_bar->add_menu(array(
			'parent' => 'wpproads_adminbar',
			'id' => 'wpproads_statistics',
			'title' => __('Statistics', 'wpproads'),
			'href' => $admin_url.'admin.php?page=wp-pro-ads-stats'
		));
	}
	
	
	
	
	
	/**
	 * Admin Notices
	 * @return html
	 */
	public function pro_ads_admin_notices() 
	{
		$notice = array();
		
		/*
		 * Available Notices
		*/
		//V4.0.4 statistics settings update
		$wpproads_enable_stats = get_option('wpproads_enable_stats', 0);
		$notice[] = PAS()->version >= '4.0.4' && $wpproads_enable_stats == '' ? sprintf(__('<p><strong>%s - Settings update required.</strong> Please update the Statistics settings. Select your option below.</p>','wppproads'), 'WP Pro Advertising System').'<p class="submit"><a class="button-primary" href="'.esc_url( add_query_arg( 'wpproads_stats_update', 'enable' ) ).'">'.__('Enable Statistics','wpproads').'</a> <a class="button-primary" href="'.esc_url( add_query_arg( 'wpproads_stats_update', 'disable' ) ).'">'.__('Disable Statistics','wpproads').'</a>' : '';
		if( !empty($notice) )
		{
			foreach($notice as $note)
			{
				echo !empty($note) ? '<div class="updated wpproads-message">'.$note.'</div>' : '';
			}
		}
		
		
		/*
		 * Handle Notices
		*/
		//V4.0.4 statistics settings update
		if ( !empty( $_GET['wpproads_stats_update'] ) ) 
		{
			$status = $_GET['wpproads_stats_update'] == 'enable' ? 1 : 0;
			update_option( 'wpproads_enable_stats', $status);
			update_option( 'wpproads_enable_userdata_stats', $status);
		}
	}

	
	
	
}

endif;