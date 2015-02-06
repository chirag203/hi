<?php
class Pro_Ads_Campaigns {	

	public function __construct() 
	{	
		
	}
	
	
	/*
	 * Get all campaigns
	 *
	 * @access public
	 * @return null
	*/
	public function get_campaigns( $custom_args = array() ) 
	{	
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'campaigns',
			'post_status'      => 'publish'
		);
		
		//return get_posts( array_merge( $args, $custom_args ) );
		$query = new WP_Query( array_merge( $args, $custom_args ) );
		
		return $query->get_posts();
	}
	
	
	
	
	
	/*
	 * Check campaign status
	 *
	 * @access public
	 * @return array
	*/
	public function get_status( $status_nr ) 
	{	
		if( $status_nr == 1 )
		{
			$status = array( 
				'name'       => 'Running', 
				'name_clean' => 'running', 
			);
		}
		elseif( $status_nr == 2 )
		{
			$status = array( 
				'name'       => 'Finished', 
				'name_clean' => 'finished', 
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
	 * Check Campaign Status
	 *
	 * 0 = draft, 1 = running, 2 = finished
	 *
	 * @access public
	 * @param int $status, string $sdate, string $edate, int $campaign_id (default: 0)
	 * @return int $status
	*/
	public function check_campaign_status( $sdate, $edate, $campaign_id = 0 )
	{	
		global $pro_ads_banners;
		
		$now = time();
		if( $now < $sdate )
		{
			$status = 0;
		}
		elseif( !empty( $edate) && $now > $edate )
		{
			$status = 2;
			
			// update banner status
			if( !empty($campaign_id))
			{
				$banners = $this->get_linked_banners( $campaign_id );
				foreach( $banners as $banner )
				{
					update_post_meta( $banner->ID, 'banner_status', 2 );
					$pro_ads_banners->remove_banner_from_adzone( $banner->ID );
				}
			}
		}
		else
		{
			$status = 1;
		}
		
		return $status;
	}
	
	
	
	
	
	
	
	
	
	/*
	 * UPDATE CAMPAIGNS STATUS
	 *
	 * @access public
	 * @return array
	*/
	public function update_campaign_status( $arr = array() ) 
	{	
		/*
		array(
			'meta_key'  => 'campaign_status',
			'meta_value' => 1,
			'meta_compare' => '!='
		)
		*/
		$campaigns = $this->get_campaigns( $arr );
		
		foreach($campaigns as $campaign )
		{
			$start_date = get_post_meta( $campaign->ID, 'campaign_start_date', true );
			$end_date = get_post_meta( $campaign->ID, 'campaign_end_date', true );
			$status = $this->check_campaign_status( $start_date, $end_date, $campaign->ID );
			
			update_post_meta( $campaign->ID, 'campaign_status', $status );
		}
	}
	
	
	
	
	/*
	 * Get linked banners for a campaign
	 *
	 * @access public
	 * @param int $id (campaign id)
	 * @return array
	*/
	public function get_linked_banners( $id )
	{
		global $pro_ads_banners;
		
		$banners = $pro_ads_banners->get_banners( 
			array(
				'meta_key'  => 'banner_campaign_id',
				'meta_value' => $id
			)
		);
		
		return $banners;
	}
	
}
?>