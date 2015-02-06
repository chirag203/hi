<?php
class Pro_Ads_Adzones {	

	public function __construct() 
	{
		
	}
	
	
	
	
	
	/*
	 * Get all adzones
	 *
	 * @access public
	 * @return null
	*/
	public function get_adzones( $custom_args = array() ) 
	{	
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'adzones',
			'post_status'      => 'publish'
		);
		
		return get_posts( array_merge( $args, $custom_args ) );
	}
	
	
	
	
	
	
	
	/*
	 * Get all adzones
	 *
	 * @access public
	 * @return array
	*/
	public function get_adzone_data( $adzone_id ) 
	{
		$linked_banner_ids   = get_post_meta( $adzone_id, 'linked_banners', true );
		$adzone_description  = get_post_meta( $adzone_id, 'adzone_description', true );
		$adzone_rotation     = get_post_meta( $adzone_id, 'adzone_rotation', true );
		$adzone_size         = get_post_meta( $adzone_id, 'adzone_size', true );
		$responsive          = get_post_meta( $adzone_id, 'adzone_responsive', true );
		$grid_horizontal     = get_post_meta( $adzone_id, 'adzone_grid_horizontal', true );
		$grid_vertical       = get_post_meta( $adzone_id, 'adzone_grid_vertical', true );
		$max_banners         = get_post_meta( $adzone_id, 'adzone_max_banners', true );
		$adzone_center       = get_post_meta( $adzone_id, 'adzone_center', true );
		$adzone_hide_empty   = get_post_meta( $adzone_id, 'adzone_hide_empty', true );
		//$adzone_is_popup     = get_post_meta( $adzone_id, 'adzone_is_popup', true );
		
		//buyandsell
		$adzone_buyandsell_contract        = get_post_meta( $adzone_id, 'adzone_buyandsell_contract', true );
		$adzone_buyandsell_duration        = get_post_meta( $adzone_id, 'adzone_buyandsell_duration', true );
		$adzone_buyandsell_price           = get_post_meta( $adzone_id, 'adzone_buyandsell_price', true );
		$adzone_buyandsell_est_impressions = get_post_meta( $adzone_id, 'adzone_buyandsell_est_impressions', true );
		
		$grid_horizontal = !empty($grid_horizontal) ? $grid_horizontal : 1;
		$grid_vertical = !empty($grid_vertical) ? $grid_vertical : 1;
		
		$limit = $adzone_rotation ? count($linked_banner_ids) : $grid_horizontal * $grid_vertical;
		$size = !empty($adzone_size) ? explode('x', $adzone_size) : '';
		$size_str = !empty($size) ? 'width:'.$size[0].'px; height:'.$size[1].'px;' : 'width:100%; height:auto;';
		
		$orderby = $adzone_rotation ? 'post__in' : 'rand';
		$margin = 3;
		
		$array = array(
			'linked_banner_ids'                 => $linked_banner_ids,
			'adzone_description'                => $adzone_description,
			'adzone_rotation'                   => $adzone_rotation,
			'adzone_size'                       => $adzone_size,
			'responsive'                        => $responsive,
			'custom'                            => get_post_meta( $adzone_id, 'adzone_custom_size', true ),
			'orderby'                           => $orderby,
			'grid_horizontal'                   => $grid_horizontal,
			'grid_vertical'                     => $grid_vertical,
			'max_banners'                       => $max_banners,
			'limit'                             => $limit,
			'size'                              => $size,
			'size_str'                          => $size_str,
			'margin'                            => $margin,
			'adzone_center'                     => $adzone_center,
			'adzone_hide_empty'                 => $adzone_hide_empty,
			//'adzone_is_popup'                   => $adzone_is_popup,
			'adzone_buyandsell_contract'        => $adzone_buyandsell_contract,
			'adzone_buyandsell_duration'        => $adzone_buyandsell_duration,
			'adzone_buyandsell_price'           => $adzone_buyandsell_price,
			'adzone_buyandsell_est_impressions' => $adzone_buyandsell_est_impressions
		);
		
		return $array;
	}
	
	
	
	
	
	
	
	
	/*
	 * Check if adzone is sill available
	 *
	 * @param int $adzone_id, int $force_active_if_selected  (default: 0), int $banner_id (default: 0 // only used if $force_active_if_selected = 1)
	 * @access public
	 * @return int
	*/
	public function check_if_adzone_is_active( $adzone_id, $force_active_if_selected = 0, $banner_id = 0 ) 
	{
		$active = 1;
		$linked_banners = get_post_meta( $adzone_id, 'linked_banners', true );
		$max_banners    = get_post_meta( $adzone_id, 'adzone_max_banners', true );
		
		if( !empty($max_banners) && count($linked_banners) >= $max_banners )
		{
			if( $force_active_if_selected )
			{
				$linked_adzones = get_post_meta( $banner_id, 'linked_adzones', true );
				$active = !empty($linked_adzones) ? in_array($adzone_id, $linked_adzones) ? 1 : 0 : 0;
			}
			else
			{
				$active = 0;
			}
		}
		
		return $active;
	}
	
	
	
	
	
	
	
	/*
	 * Display Adzone
	 *
	 * @access public
	 * @return null
	*/
	public function display_adzone( $id ) 
	{	
		global $pro_ads_main, $pro_ads_banners, $pro_ads_statistics, $pro_ads_bs_templates;
		
		if( isset( $id ) )
		{
			$arr = $this->get_adzone_data($id);
			$active_banners = 0;
			$banners = '';
			
			if( !empty($arr['linked_banner_ids']) )
			{
				$banners = $pro_ads_banners->get_banners( 
					array(
						'posts_per_page' => $arr['limit'],
						'post__in'       => $arr['linked_banner_ids'], 
						'orderby'        => $arr['orderby'], 
						'meta_key'       => 'banner_status',
						'meta_value'     => 1
						/*'meta_query'     => array(
							array(
								'key'     => 'banner_status',
								'value'   => 1,
								'compare' => '='
							)
						) */
					)
				);
			}
			
			$adzone_center = get_post_meta( $id, 'adzone_center', true );
			$css_center = $arr['responsive'] ? 'text-align:center;' : 'margin: 0 auto;';
			$center_css = $adzone_center ? $css_center : '';
			
			$html = '';
			
			if( empty($banners) && $arr['adzone_hide_empty'] )
			{
				// hide ampty adzones is active
			}
			else
			{
				$html.= '<div class="wpproadszone proadszone-'.$id.'" style="overflow:hidden; '.$arr['size_str'].' '.$center_css.'">';
						
				if(!empty($banners))
				{	
					foreach( $banners as $banner )
					{
						// check if campaign is active
						$campaign_id = get_post_meta( $banner->ID, 'banner_campaign_id', true );
						$campaign_status = get_post_meta( $campaign_id, 'campaign_status', true );
						
						if( $campaign_status == 1 )
						{
							$html.= '<div>'.$pro_ads_banners->get_banner_item( $banner->ID, $id).'</div>';
							$pro_ads_statistics->save_impression( $banner->ID, $id );
							$active_banners++;
						}
					}
				}
				else
				{
					/*
					 * ADD-ON: Buy and Sell
					 *
					 *_______________________________________________________________________________________________________________
					 * Check if "Buy and Sell Plugin" is installed.
					*/
					if( $pro_ads_main->buyandsell_is_active() )
					{
						$html.= $pro_ads_bs_templates->buyandsell_placeholder( $id );
					}
					/*
					 *_______________________________________________________________________________________________________________
					*/
				}
				$html.= '</div>';
				
				if( $arr['adzone_rotation'] && count($arr['linked_banner_ids']) > 1 && $active_banners > 1 )
				{
					$rotation_effect  = get_post_meta( $id, 'adzone_rotation_effect', true );
					$rotation_time = get_post_meta( $id, 'adzone_rotation_time', true );
					$rotation_time = !empty($rotation_time) ? $rotation_time*1000 : 5000;
					
					$html.= '<script type="text/javascript">';
						$html.= 'jQuery(document).ready(function($){';
							$html.= '$(".proadszone-'.$id.'").jshowoff({';
								$html.= 'speed: '.$rotation_time.',';
								$html.= 'effect: "'.$rotation_effect.'",';
								//$html.= 'hoverPause: false,';
								$html.= 'controls: false,';
								$html.= 'links: false';
							$html.= '});'; 
						$html.= '});';
					$html.= '</script>';
				}
			}
		}
		else
		{
			$html.= __('Woops! we cannot find the adzone your looking for!', 'wpproads');	
		}
		
		return $html;
	}
	
	
	
	
	
	/*
	 * Display Adzone Grid
	 *
	 * @access public
	 * @return null
	*/
	public function display_adzone_grid( $id )
	{
		global $pro_ads_main, $pro_ads_banners, $pro_ads_statistics, $pro_ads_templates, $pro_ads_bs_templates;
		
		if( isset( $id ) )
		{
			$arr = $this->get_adzone_data($id);
			$responsive = 0;
			$active_banners = 0;
			$banners = '';
			
			if( !empty($arr['size']) )
			{
				$size_str = 'width:'.$arr['size'][0].'px; height:'.$arr['size'][1].'px;';
			}
			else
			{
				$responsive = 1;
				$w = 100/$arr['grid_horizontal'] - $arr['margin']*$arr['grid_horizontal'];
				$size_str = 'width:'.$w.'%; height:auto; min-height:80px;';
			}
			
			if( !empty($arr['linked_banner_ids'] ))
			{
				$banners = $pro_ads_banners->get_banners( 
					array(
						'posts_per_page' => $arr['limit'],
						'post__in'       => $arr['linked_banner_ids'], 
						'orderby'        => 'rand', 
						'meta_key'       => 'banner_status',
						'meta_value'     => 1
					)
				);
			}
			
			$adzone_center = get_post_meta( $id, 'adzone_center', true );
			$center_css = $adzone_center ? 'text-align:center;' : '';
			
			$html = '';
			
			if( empty($banners) && $arr['adzone_hide_empty'] )
			{
				// hide ampty adzones is active
			}
			else
			{
				$html.= '<div class="wpproadszone proadszone-'.$id.' wpproadgrid" style="overflow:hidden; '.$center_css.'">';
					$html.= '<div style="display:inline-block;">';
					
						$b = 1;
						for($i = 0; $i < $arr['limit']; $i++ )
						{
							// check if campaign is active
							$campaign_status = 1;
							if( !empty($banners[$i]) )
							{
								$campaign_id = get_post_meta( $banners[$i]->ID, 'banner_campaign_id', true );
								$campaign_status = get_post_meta( $campaign_id, 'campaign_status', true );
							}
							
							if( !empty($banners[$i]) && $campaign_status == 1 )
							{
								if( !$responsive )
								{
									$html.= '<div style="float:left; '.$size_str.' margin:'.$arr['margin'].'px;">'.$pro_ads_banners->get_banner_item( $banners[$i]->ID, $id, $arr['adzone_size']).'</div>';
								}
								else
								{
									$html.= '<div style="float:left; '.$size_str.' margin:'.$arr['margin'].'px;">'.$pro_ads_banners->get_banner_item( $banners[$i]->ID, $id).'</div>';
								}
								$pro_ads_statistics->save_impression( $banners[$i]->ID, $id );
								$active_banners++;
							}
							else
							{
								$html.= '<div style="float:left; '.$size_str.' margin:'.$arr['margin'].'px; background:#EEE;">';
									
									/*
									 * ADD-ON: Buy and Sell
									 *
									 *_______________________________________________________________________________________________________________
									 * Check if "Buy and Sell Plugin" is installed.
									*/
									if( $pro_ads_main->buyandsell_is_active() )
									{
										$html.= $pro_ads_bs_templates->buyandsell_placeholder( $id );
									}
									/*
									 *_______________________________________________________________________________________________________________
									*/
									
								$html.= '</div>';
								
							}
							
							if( $b == $arr['grid_horizontal'] )
							{
								$html.= '<div style="clear:both;"></div>';
								$b = 0;
							}
							$b++;
						}
						
						$html.= '<div style="clear:both;"></div>';
					$html.= '</div>';
				$html.= '</div>';
			}
		}
		
		return $html;
	}
	
	
	
	
	
	
	
	/*
	 * Display Adzone as Background ad
	 *
	 * @access public
	 * @return null
	*/
	public function display_adzone_as_background( $atts ) 
	{	
		global $pro_ads_main, $pro_ads_banners, $pro_ads_statistics, $pro_ads_bs_templates;
		
		extract( shortcode_atts( array(
			'id' => '',
			'container' => '',
			'repeat'   => '',
			'stretch'   => '',
			'bg_color'  => ''
		), $atts ) );
		
		$html = '';
		$adzone_id = $id;
		
		if( !empty($adzone_id))
		{
			$arr = $this->get_adzone_data($adzone_id);
			$active_banners = 0;
			$banners = '';
			
			if( !empty($arr['linked_banner_ids']) )
			{
				$banners = $pro_ads_banners->get_banners( 
					array(
						'posts_per_page' => $arr['limit'],
						'post__in'       => $arr['linked_banner_ids'], 
						'orderby'        => $arr['orderby'], 
						'meta_key'       => 'banner_status',
						'meta_value'     => 1
					)
				);
				
				$banner_type = get_post_meta( $banners[0]->ID, 'banner_type', true );
				$banner_is_image = $pro_ads_banners->check_if_banner_is_image($banner_type);
				$banner_url = get_post_meta( $banners[0]->ID, 'banner_url', true );
				$banner_link = get_post_meta( $banners[0]->ID, 'banner_link', true );
				$banner_target = get_post_meta( $id, 'banner_target', true );
				
				$pas_container = empty($container) ? 'body' : $container;
				$bg_repeat = empty($repeat) ? 'no-repeat' : 'repeat';
				$bg_stretch = empty($stretch) ? '' : '-webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;';
				$bg_color = empty($bg_color) ? '' : $bg_color;
				//http://work.tunasite.com/wp-content/uploads/2014/12/bg_ad_example.jpg
				$html.= '<style type="text/css" id="custom-background-css">';
				$html.= 'body { background-position:top center !important; background-color: '.$bg_color.' !important; background-image: url("'.$banner_url.'") !important; background-repeat: '.$bg_repeat.' !important; background-attachment: fixed !important; '.$bg_stretch.'}';
				$html.= '</style>';
				$html.= '<script type="text/javascript">/* <![CDATA[ */';
					$html.= 'var clickable_paszone = {';
					//$html.= '"link_left":"http://www.tunasite.com",';
					//$html.= '"link_right":"http://www.tunasite.com",';
					//$html.= '"link_left_target":"_blank",';
					//$html.= '"link_right_target":"_blank",';
					$html.= '"link_full":"'.addslashes( $pro_ads_banners->pro_ads_create_banner_link($banners[0]->ID, $adzone_id) ).'",';
					$html.= '"link_full_target":"'.$banner_target.'",';
					$html.= '"pas_container":"'.$pas_container.'"';
					$html.= '};';
				$html.= '/* ]]> */</script>';
			}
			
			
		}
		else
		{
			$html.= __('Woops! we cannot find the adzone your looking for!', 'wpproads');	
		}
		
		return $html;
	}
	
	
	
	
	
	
	/*
	 * Output Adzones
	 *
	 * @access public
	 * @param string $size, int $custom (default: 0), int $responsive (default:0)
	 * @return array or string
	*/
	public function pro_ad_output_adzone_size( $size, $custom = 0, $responsive = 0 )
	{
		if( !$custom && !$responsive )
		{
			$arr = array(
				'468x60'  => 'IAB Full Banner (468 x 60)',
				'120x600' => 'IAB Skyscraper (120 x 600)',
				'728x90'  => 'IAB Leaderboard (728 x 90)',
				'300x250' => 'IAB Medium Rectangle (300 x 250)',
				'120x90'  => 'IAB Button 1 (120 x 90)',
				'160x600' => 'IAB Wide Skyscraper (160 x 600)',
				'120x60'  => 'IAB Button 2 (120 x 60)',
				'125x125' => 'IAB Square Button (125 x 125)',
				'180x150' => 'IAB Rectangle (180 x 150)'
			);
			
			return $arr[$size];
		}
		elseif( $custom )
		{
			$sz = explode('x', $size);	
			return 'Custom ('.$sz[0].' x '.$sz[1].')';
		}
		else
		{
			return 'Responsive';
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	/*
	 * Link Banner to adzone
	 *
	 * @access public
	 * @param 
	 * @return void
	*/
	public function pro_ad_link_banner_to_adzone( $adzone_id, $banner_id, $action_type = '' )
	{
		global $pro_ads_adzones, $pro_ads_banners;
	
		// link banner to adzone
		//update_post_meta( $_POST['aid'], 'linked_banners', ''  );
		$this->pro_ad_adzone_clean_linked_banners_array( $adzone_id );
		$linked_banners = get_post_meta( $adzone_id, 'linked_banners', true );
		$max_banners    = get_post_meta( $adzone_id, 'adzone_max_banners', true );
		$banner_status  = get_post_meta( $banner_id, 'banner_status', true );
		
		if( empty( $linked_banners ))
		{
			if( $pro_ads_adzones->check_if_adzone_is_active( $adzone_id ) && $banner_status == 1)
			{
				$linked_banners = array( $banner_id );
				update_post_meta( $adzone_id, 'linked_banners', array_values(array_filter($linked_banners))  );
				
				// link adzone to banner
				$pro_ads_banners->pro_ad_link_adzone_to_banner( $banner_id, $adzone_id, $action_type );
			}
		}
		else
		{
			if( $action_type == 'remove' )
			{
				if (($key = array_search($banner_id, $linked_banners)) !== false) unset($linked_banners[$key]);
				// link adzone to banner
				$pro_ads_banners->pro_ad_link_adzone_to_banner( $banner_id, $adzone_id, $action_type );
			}
			else
			{
				if( $pro_ads_adzones->check_if_adzone_is_active( $adzone_id ) && $banner_status == 1)
				{
					array_push($linked_banners, $banner_id);
					// link adzone to banner
					$pro_ads_banners->pro_ad_link_adzone_to_banner( $banner_id, $adzone_id, $action_type );
				}
			}
			update_post_meta( $adzone_id, 'linked_banners', array_values(array_filter($linked_banners)) );
		}
	}
	
	
	
	
	
	
	
	/*
	 * Clean linkedbanners array
	 *
	 * @access public
	 * @param 
	 * @return void
	*/
	public function pro_ad_adzone_clean_linked_banners_array( $adzone_id )
	{
		global $pro_ads_adzones, $pro_ads_banners;
		
		$linked_banners = get_post_meta( $adzone_id, 'linked_banners', true );
		
		if( !empty( $linked_banners ))
		{
			foreach( $linked_banners as $banner )
			{
				$check_banner = $pro_ads_banners->get_banners( array( 'post__in' => array( $banner ) ) );
				
				if(empty($check_banner))
				{
					if (($key = array_search($banner, $linked_banners)) !== false) unset($linked_banners[$key]);
					update_post_meta( $adzone_id, 'linked_banners', array_values(array_filter($linked_banners)) );	
				}
			}
		}
		
	}
	
}
?>