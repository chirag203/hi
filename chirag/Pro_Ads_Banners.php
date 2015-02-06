<?php
class Pro_Ads_Banners {	

	public function __construct() 
	{
		// Banner click ---------------------------------------------------
		add_action( 'wp_loaded', array( $this, 'pro_ad_click_action' ) );	
	}
	
	
	
	
	/*
	 * Get all banners
	 *
	 * @access public
	 * @return array
	*/
	public function get_banners( $custom_args = array() ) 
	{	
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'banners',
			'post_status'      => 'publish'
		);
		
		//$query = new WP_Query( array_merge( $args, $custom_args ) );
		//return $query->get_posts();
		return get_posts( array_merge( $args, $custom_args ) );
	}
	
	
	
	
	
	/*
	 * Check banner status
	 *
	 * @access public
	 * @return array
	*/
	public function get_status( $status_nr ) 
	{	
		if( $status_nr == 1 )
		{
			$status = array( 
				'name'       => 'Active', 
				'name_clean' => 'active', 
			);
		}
		elseif( $status_nr == 2 )
		{
			$status = array( 
				'name'       => 'Inactive', 
				'name_clean' => 'inactive', 
			);
		}
		elseif( $status_nr == 3 )
		{
			$status = array( 
				'name'       => 'Awaiting Payment', 
				'name_clean' => 'awaiting-payment', 
			);
		}
		else
		{
			$status = array( 
				'name'       => 'Draft', 
				'name_clean' => 'draft', 
			);
		}
		
		return $status;
	}
	
	
	
	
	
	
	
	
	
	
	/*
	 * Update banner Status - for updating
	 *
	 * status: 0 = draft, 1 = active, 2 = inactive, 3 = awaiting payment
	 * contract: 0 = no contract, 1 = pay per click, 2 = pay per view, 3 = duration (days)
	 *
	 * @access public
	 * @param int $status, string $sdate, string $edate
	 * @return int $status
	*/
	public function update_banner_status( $banner_id )
	{
		$banner_contract     = get_post_meta( $banner_id, 'banner_contract', true );
		$banner_duration     = get_post_meta( $banner_id, 'banner_duration', true );
		$status              = get_post_meta( $banner_id, 'banner_status', true );
		
		if( !empty($banner_contract) && !empty($banner_duration) )
		{
			if($banner_contract == 1)
			{
				$banner_clicks = get_post_meta( $banner_id, 'banner_clicks', true );
				if($banner_clicks >= $banner_duration)
				{
					$status = 2;
					$this->remove_banner_from_adzone( $banner_id );
				}
			}
			elseif($banner_contract == 2)
			{
				$banner_impressions = get_post_meta( $banner_id, 'banner_impressions', true );
				if($banner_impressions >= $banner_duration)
				{
					$status = 2;
					$this->remove_banner_from_adzone( $banner_id );
				}
			}
			elseif($banner_contract == 3)
			{
				$banner_start_date = get_post_meta( $banner_id, 'banner_start_date', true );
				$day_str = $banner_duration > 1 ? 'days' : 'day';
				$end_date = strtotime('+'.$banner_duration.' '.$day_str, $banner_start_date);
				if( $end_date < time() )
				{
					$status = 2;
					$this->remove_banner_from_adzone( $banner_id );
				}
			}
			
			update_post_meta( $banner_id, 'banner_status', $status );
		}
		
		return $status;
	}
	
	
	
	
	
	
	
	/*
	 * Remove Banner from all adzones it's linked to
	 *
	 * @param 
	 * @access public
	 * @return null
	*/
	public function remove_banner_from_adzone( $banner_id ) 
	{
		$linked_adzones = get_post_meta( $banner_id, 'linked_adzones', true );
		
		if(!empty($linked_adzones))
		{
			foreach($linked_adzones as $adzone_id)
			{
				$linked_banners = get_post_meta( $adzone_id, 'linked_banners', true );
				if( !empty( $linked_banners ))
				{
					if (($key = array_search($banner_id, $linked_banners)) !== false) unset($linked_banners[$key]);
					//print_r(array_values(array_filter($linked_banners)));
					update_post_meta( $adzone_id, 'linked_banners', array_values(array_filter($linked_banners)) );
				}
			}
			
			update_post_meta( $banner_id, 'linked_adzones', ''  );
		}
	}
	
	
	
	
	
	
	
	
	
	/*
	 * Preview banner
	 *
	 * @access public
	 * @return array
	*/
	public function check_if_banner_is_image( $type ) 
	{	
		$res = 0;
		
		if( $type == 'jpg' || $type == 'png' || $type == 'gif' )
		{
			$res = 1;
		}
		
		return $res;
	}
	
	
	
	
	
	
	
	/*
	 * Load banner IDs linked by specific adzone
	 *
	 * @access public
	 * @return array
	*/
	
	public function load_banner_ids_by_adzone( $aid ) 
	{	
		$args = array(
			'numberposts' => -1,
			'post_type' => 'banners'
		 );		
		$banners = get_posts( $args );
		$ids = array();
		
		foreach( $banners as $banner)
		{
			$links = get_post_meta($banner->ID, "linked_adzones", true);
			$res = !empty($links) ? in_array( $aid, $links ) ? $banner->ID : '' : '';
			if( !empty($res) )
			{
				$ids[] = $res;
			}
		}
		
		return $ids;
	}
	
	
	
	
	
	
	/*
	 * Get banner preview, image - object - or placeholder
	 *
	 * @access public
	 * @return html
	*/
	public function get_banner_preview( $id ) 
	{	
		$banner_type = get_post_meta( $id, 'banner_type', true );
		$banner_url = get_post_meta( $id, 'banner_url', true );
		$banner_is_image = $this->check_if_banner_is_image($banner_type);
	
		if( $banner_is_image )
		{
			$img = !empty($banner_url) ? $banner_url : WP_ADS_URL.'images/placeholder.png';
			$html.= '<div class="preview_banner" style="background: url('.$img.') no-repeat center center; width:40px; height:40px;"></div>';
		}
		elseif( $banner_type == 'swf')
		{
			$html.= "<object>";
				$html.=  "<embed allowscriptaccess='always' id='banner-swf' width='40' height='40' src='".$banner_url."'>";
			$html.= "</object>";
		}
		else
		{
			$html.= '<img src="'.WP_ADS_URL.'images/placeholder.png" width="40" />';
		}
		
		return $html;
	}
	
	
	
	
	
	
	/*
	 * Get banner, image - object - or html
	 *
	 * @access public
	 * @return html
	*/
	public function get_banner_item( $id, $aid = '', $force_size = '' )
	{	
		$banner_type       = get_post_meta( $id, 'banner_type', true );
		$banner_url        = get_post_meta( $id, 'banner_url', true );
		$banner_link       = get_post_meta( $id, 'banner_link', true );
		$banner_target     = get_post_meta( $id, 'banner_target', true );
		$banner_size       = get_post_meta( $id, 'banner_size', true );
		$banner_no_follow  = get_post_meta( $id, 'banner_no_follow', true );
		$banner_start_date = get_post_meta( $id, 'banner_start_date', true );
		$rel = $banner_no_follow ? 'rel="nofollow"' : '';
		$banner_is_image = $this->check_if_banner_is_image($banner_type);
		$click_tag = '';
		
		$size = !empty($banner_size) ? explode('x', $banner_size ) : '';
		$size = !empty($force_size) ? explode('x', $force_size ) : $size;
		$size_str = !empty($force_size) ? 'width="'.$size[0].'" ' : '';
		$today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		
		$html = '';
		
		if( $banner_is_image )
		{
			$img = !empty($banner_url) ? $banner_url : WP_ADS_URL.'images/placeholder.png';
			$html.= '<img src="'.$img.'" alt="'.get_the_title($id).'" border="0" '.$size_str.' />';
		}
		elseif( $banner_type == 'swf')
		{
			$html.= '<object width="'.$size[0].'" height="'.$size[1].'" style="cursor:pointer;">';
				$html.= $fallback_and_link;
				$html.= '<param name="movie" value="'.$banner_url.$click_tag.'"></param>';
				$html.= '<param name="allowFullScreen" value="true"></param>';
				$html.= '<param name="allowscriptaccess" value="always"></param>';
				$html.= '<param name="wmode" value="transparent"></param>';
				$html.= '<embed src="'.$banner_url.$click_tag.'" type="application/x-shockwave-flash" width="'.$size[0].'" height="'.$size[1].'" allowscriptaccess="always" allowfullscreen="true"></embed>';
			$html.= '</object>';
		}
		else
		{
			$banner_html = get_post_meta( $id, 'banner_html', true );
			$html.= $banner_html;
		}
		
		// Create link
		$adzone_str = !empty( $aid ) ? '&pasZONE='.base64_encode($aid) : '';
		$html = !empty( $banner_link ) ? '<a href="'.get_bloginfo('url').'?pasID='.base64_encode($id).$adzone_str.'" target="'.$banner_target.'" '.$rel.'>'.$html.'</a>' : $html;
		
		return $html;
	}
	
	
	
	
	
	
	
	/*
	 * Create banner link
	 *
	 * @access public
	 * @return html
	*/
	public function pro_ads_create_banner_link($banner_id, $adzone_id)
	{
		$banner_link       = get_post_meta( $banner_id, 'banner_link', true );
		$banner_target     = get_post_meta( $banner_id, 'banner_target', true );
		
		// Create link
		$adzone_str = !empty( $adzone_id ) ? '&pasZONE='.base64_encode($adzone_id) : '';
		$link = !empty( $banner_link ) ? get_bloginfo('url').'?pasID='.base64_encode($banner_id).$adzone_str : '';
		
		return $link;
	}
	
	
	
	
	
	
	/*
	 * Banner Click - Redirect
	 *
	 * @access public
	 * @return null
	*/
	public function pro_ad_click_action()
	{
		global $wpdb, $pro_ads_main, $pro_ads_browser, $pro_ads_statistics;
		
		if( isset( $_GET['pasID'] ) && !empty( $_GET['pasID'] ) )
		{
			$banner_id = base64_decode($_GET['pasID']);
			$adzone_id = isset($_GET['pasZONE']) && !empty($_GET['pasZONE']) ? base64_decode($_GET['pasZONE']) : '';
			
			$banner_link = get_post_meta( $banner_id, 'banner_link', true );
			
			$pro_ads_statistics->save_clicks( $banner_id, $adzone_id );
			
			header('Location: '. $banner_link);
			exit;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	 * Link Adzone to Banner
	 *
	 * @access public
	 * @param 
	 * @return void
	*/
	public function pro_ad_link_adzone_to_banner( $banner_id, $adzone_id, $action_type = '' )
	{
		global $pro_ads_adzones;
	
		// link adzone to banner
		//update_post_meta( $_POST['aid'], 'linked_banners', ''  );
		$linked_adzones = get_post_meta( $banner_id, 'linked_adzones', true );
		$banner_status  = get_post_meta( $banner_id, 'banner_status', true );
		
		if( empty( $linked_adzones ))
		{
			if( $banner_status == 1)
			{
				$linked_adzones = array( $adzone_id );
				update_post_meta( $banner_id, 'linked_adzones', array_values(array_filter($linked_adzones))  );
			}
		}
		else
		{
			if( $action_type == 'remove' )
			{
				if (($key = array_search($adzone_id, $linked_adzones)) !== false) unset($linked_adzones[$key]);
			}
			else
			{
				if( $banner_status == 1)
				{
					array_push($linked_adzones, $adzone_id);
				}
			}
			update_post_meta( $banner_id, 'linked_adzones', array_values(array_filter($linked_adzones)) );
		}
	}
	
	
	
}
?>