<?php
class Pro_Ads_Advertisers {	

	public function __construct() 
	{
		
	}
	
	
	/*
	 * Get all advertisers
	 *
	 * @access public
	 * @return null
	*/
	public function get_advertisers( $custom_args = array() ) 
	{	
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'advertisers',
			'post_status'      => 'publish'
		);
		
		return get_posts( array_merge( $args, $custom_args ) );
	}
	
}
?>