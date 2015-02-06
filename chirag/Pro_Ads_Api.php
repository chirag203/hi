<?php
class Pro_Ads_API {	

	public function __construct() 
	{
		// API adzone request ---------------------------------------------------
		add_action( 'wp_loaded', array( $this, 'wp_pro_ad_api_load_adzone' ) );	
	}
	
	
	
	
	/*
	 * Api load adzone
	 *
	 * @access public
	 * @return int
	*/
	public function wp_pro_ad_api_load_adzone()
	{
		global $pro_ads_adzones, $pro_ads_main;
		
		if( isset( $_GET['wpproadszoneid'] ) && !empty( $_GET['wpproadszoneid'] ) )
		{
			if( method_exists( $pro_ads_adzones, 'display_adzone' ) )
			{
				$custom_css = get_option('wpproads_custom_css', '');
				$pro_ads_main->daily_updates();
				?>
                <!DOCTYPE>
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <script type='text/javascript' src='<?php bloginfo('wpurl'); ?>/wp-admin/load-scripts.php?load=jquery-core'></script>
				<script type="text/javascript" src="<?php echo WP_ADS_TPL_URL.'/js/jquery.jshowoff.min.js'; ?>"></script>
                <script type="text/javascript" src="<?php echo WP_ADS_TPL_URL.'/js/wppas.js'; ?>"></script>
                <link rel="stylesheet" id="wp_pro_ads_style-css" href="<?php echo WP_ADS_TPL_URL; ?>/css/wpproads.css" type="text/css" media="all">
				<link rel="stylesheet" id="wp_pro_ads_style-css" href="<?php echo WP_ADS_TPL_URL; ?>/css/responsive_ads.css" type="text/css" media="all">
                <?php
				// Buy and sell
				if( $pro_ads_main->buyandsell_is_active() )
				{
					?>
                    <script type="text/javascript" src="<?php echo WP_ADS_BS_TPL_URL.'/js/buyandsell.js'; ?>"></script>
                	<link rel="stylesheet" id="wp_pro_ads_style-css" href="<?php echo WP_ADS_BS_TPL_URL; ?>/css/buyandsell.css" type="text/css" media="all">
                    <?php
				}
				?>
				<style type="text/css">
					body { margin:0; padding:0; }
					<?php echo $custom_css; ?>
				</style>
                <title>WP PRO ADVERTISING SYSTEM</title>
                </head>
                
                <body>
				
				<?php
				
				$grid_horizontal   = get_post_meta( $_GET['wpproadszoneid'], 'adzone_grid_horizontal', true );
				$grid_vertical     = get_post_meta( $_GET['wpproadszoneid'], 'adzone_grid_vertical', true );
				
				if( !empty($grid_horizontal) && !empty($grid_vertical) )
				{
					echo $pro_ads_adzones->display_adzone_grid( $_GET['wpproadszoneid'] );
				}
				else
				{
					echo $pro_ads_adzones->display_adzone( $_GET['wpproadszoneid'] );
				}
				?>
                </body>
                </html>
                <?php
			}
			else
			{
				_e('WP Pro Ad System Error: function "pro_ad_display_adzone" does not exists.','wpproads');	
			}
			exit();
		}
		
	}
	
}
?>