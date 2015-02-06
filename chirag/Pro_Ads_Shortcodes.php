<?php
class Pro_Ads_Shortcodes {	

	public function __construct() 
	{
		add_shortcode('pro_ad_display_adzone', array($this, 'sc_display_adzone'));
		add_shortcode('pro_ad_background_ad', array($this, 'sc_background_ad'));
	}
	
	
	
	
	/*
	 * Shortcode function - [pro_ad_display_adzone id="1" popup="0"]
	 *
	 * @access public
	 * @return array
	*/
	public function sc_display_adzone( $atts, $content = null ) 
	{	
		global $pro_ads_templates, $pro_ads_adzones;
		
		extract( shortcode_atts( array(
			'id' => 1,
			'popup' => 0,
			'popup_bg' => '',
			'popup_opacity' => '',
			'background' => '',
			'container'  => '',
			'repeat'     => '',
			'stretch'    => '',
			'bg_color'   => ''
		), $atts ) );
		
		$grid_horizontal   = get_post_meta( esc_attr($id), 'adzone_grid_horizontal', true );
		$grid_vertical     = get_post_meta( esc_attr($id), 'adzone_grid_vertical', true );
		
		// Check if adzone is popup
		if( esc_attr($popup) )
		{
			if( !empty($grid_horizontal) && !empty($grid_vertical) )
			{
				$adzone = $pro_ads_templates->pro_ad_popup_screen( 
					array(
						'html' => $pro_ads_adzones->display_adzone_grid( esc_attr($id) ), 
						'adzone_id' => $id,
						'popup_bg'  => $popup_bg,
						'popup_opacity' => $popup_opacity
					) 
				);
			}
			else
			{
				$adzone = $pro_ads_templates->pro_ad_popup_screen( 
					array(
						'html' => $pro_ads_adzones->display_adzone( esc_attr($id) ), 
						'adzone_id' => $id,
						'popup_bg'  => $popup_bg,
						'popup_opacity' => $popup_opacity 
					) 
				);
			}
		}
		elseif( esc_attr($background) )
		{
			$adzone = $pro_ads_adzones->display_adzone_as_background( $atts );
		}
		else
		{
			if( !empty($grid_horizontal) && !empty($grid_vertical) )
			{
				$adzone = $pro_ads_adzones->display_adzone_grid( esc_attr($id) );
			}
			else
			{
				$adzone = $pro_ads_adzones->display_adzone( esc_attr($id) );
			}
		}
		
		return $adzone;
	}
	
	
	
	
	
	
	
	
	/*
	 * Shortcode function - [sc_background_ad]
	 *
	 * @access public
	 * @return array
	*/
	/*
	public function sc_background_ad( $atts, $content = null ) 
	{
		global $pro_ads_adzones;
		
		$html = $pro_ads_adzones->display_adzone_as_background( $atts );
		
		return $html;
	}
	*/
}
?>