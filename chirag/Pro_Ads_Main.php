<?php
class Pro_Ads_Main {	

	public function __construct() 
	{
		
	}
	
	
	
	
	
	/*
	 * daily update function
	 *
	 * @access public
	 * @return array
	*/
	public function daily_updates( $force = 0) 
	{	
		global $pro_ads_campaigns, $pro_ads_banners;
		
		$last_update = get_option( 'wpproads_daily_update', 0 );
		$today = date('Y').date('m').date('d');
		
		if( $last_update < $today || $force )
		{
			$pro_ads_campaigns->update_campaign_status();
			
			$banners = $pro_ads_banners->get_banners( 
				array(
					'meta_key'       => 'banner_contract',
					'meta_value'     => 3
				)
			);
			
			foreach( $banners as $banner )
			{
				$pro_ads_banners->update_banner_status( $banner->ID );
			}
				
			update_option( 'wpproads_daily_update', $today );
		}
	}
	
	
	
	
	
	
	/*
	 * Check if ADD_ON Buy and Sell ads is active
	 *
	 * @access public
	 * @return array
	*/
	public function buyandsell_is_active() 
	{
		global $pro_ads_bs_templates;
		
		if( method_exists( $pro_ads_bs_templates, 'buyandsell_placeholder' ) )
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	
	
	
	
	
	/*
	 * Geo Info - get city and country
	 *
	 * @access public
	 * @return array
	*/
	public function get_geo_info() 
	{	
		$ip = $_SERVER['REMOTE_ADDR'];
		@$content = file_get_contents("http://www.geoplugin.net/xml.gp?ip=".$ip);
		/*preg_match('/<geoplugin_city>(.*)/i', $content, $city);
		preg_match('/<geoplugin_countryName>(.*)/i', $content, $country);
		preg_match('/<geoplugin_countryCode>(.*)/i', $content, $country_cd);*/
		preg_match('/<geoplugin_city(.*)?>(.*)?<\/geoplugin_city>/', $content, $city);
		preg_match('/<geoplugin_countryName(.*)?>(.*)?<\/geoplugin_countryName>/', $content, $country);
		preg_match('/<geoplugin_countryCode(.*)?>(.*)?<\/geoplugin_countryCode>/', $content, $country_cd);
		
		$geo = array(
			'city'        => !empty($city[2]) ? $city[2] : '',
			'country'     => !empty($country[2]) ? $country[2] : '',
			'country_cd'  => !empty($country_cd[2]) ? $country_cd[2] : '',
		);
		
		return $geo;
	}
	
	
	
	
	
	
	/*
	 * Detect search engine bots
	 *
	 * @access public
	 * @return int $isbot
	*/
	public function detect_bots()
	{
		$isbot = 0;
		$bots = array(
			'Arachnoidea',
			'FAST-WebCrawler',
			'Fluffy the spider',
			'Googlebot',
			'Gigabot',
			'Gulper',
			'ia_archiver',
			'MantraAgent',
			'MSN',
			'Scooter',
			'Scrubby',
			'Teoma_agent1',
			'Winona',
			'ZyBorg',
			'WebCrawler',
			'W3C_Validator',
			'WDG_Validator',
			'Zealbot',
			'Robozilla',
			'Almaden'
		);
		
		foreach( $bots as $i => $bot )
		{
			if(strstr(strtolower($_SERVER['HTTP_USER_AGENT']), $bot))
			{
				$isbot = 1;
				return $isbot;
			}
		}
		
		return $isbot;
	}
	
}