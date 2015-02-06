<?php
class Pro_Ads_Statistics {	

	public function __construct() 
	{
		
	}
	
	
	
	
	/*
	 * Load Statistics
	 *
	 * @access public
	 * @return array
	*/
	public function load_statistics( $query = '', $group = 'type' )
	{
		global $wpdb;
		
		$res = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "pro_ad_system_stats ".$query ); //GROUP BY ".$group
		
		return $res;
	}
	
	
	
	
	/*
	 * Statistic types
	 *
	 * @access public
	 * @return array
	*/
	public function stat_types( $type )
	{
		return $type == 'click' ? __('Clicks', 'wpproads') : __('Impressions', 'wpproads');
	}
	public function stat_type_color( $type )
	{
		return $type == 'click' ? '#66b71a' : '#77B7C5';
	}
	
	
	
	
	
	/*
	 * Statistics Header
	 *
	 * @access public
	 * @return html
	*/
	public function pro_ad_show_statistics_header( $res )
	{
		global $wpdb, $pro_ads_advertisers;
		
		$click_str = '';
		$impr_str = '';
		$ctr_str = '';
		
		if( $res['rid'] == 1 )
		{
			$all_years = $wpdb->get_results('SELECT YEAR(FROM_UNIXTIME(time)) as year FROM '.$wpdb->prefix.'pro_ad_system_stats GROUP BY year');
			$first_year = reset($all_years);
    		$last_year = end($all_years);
			
			$fyear = !empty($first_year->year) ? $first_year->year : date('Y');
			$lyear = !empty($last_year->year) ? $last_year->year : date('Y');
			
			$sdate = mktime(0,0,0,1, 1, $fyear);
			$edate = mktime(23,59,59,12, 31, $lyear);
			
			$click_str = '<div>'.__('Total amount of clicks:', 'wpproads').'</div>';
			$impr_str = '<div>'.__('Total amount of impressions:', 'wpproads').'</div>';
			$ctr_str = '<div>'.__('CTR:', 'wpproads').'</div>';
		}
		elseif( $res['rid'] == 2 )
		{
			$sdate = mktime(0,0,0, 1,1, $res['year']);
			$edate = mktime(23,59,59, 12,1, $res['year']);
			
			$click_str = '<div>'.sprintf(__('Total amount of clicks in %s:', 'wpproads'), $res['year'] ).'</div>';
			$impr_str = '<div>'.sprintf(__('Total amount of impressions in %s:', 'wpproads'), $res['year'] ).'</div>';
			$ctr_str = '<div>'.sprintf(__('CTR (%s):', 'wpproads'), $res['year'] ).'</div>';
		}
		elseif( $res['rid'] == 3 )
		{
			$am_days = cal_days_in_month(CAL_GREGORIAN, $res['month'], $res['year']);
			$sdate = mktime(0,0,0, $res['month'],1, $res['year']);
			$edate = mktime(23,59,59, $res['month'],$am_days, $res['year']);
			
			$click_str = '<div>'.sprintf(__('All clicks on %s:', 'wpproads'), date('F', $sdate) ).'</div>';
			$impr_str = '<div>'.sprintf(__('All impressions on %s:', 'wpproads'), date('F', $sdate) ).'</div>';
			$ctr_str = '<div>'.sprintf(__('CTR (%s):', 'wpproads'), date('F', $sdate) ).'</div>';
		}
		elseif( $res['rid'] == 4 )
		{
			$sdate = mktime(0,0,0, $res['month'],$res['day'], $res['year']);
			$edate = mktime(23,59,59, $res['month'],$res['day'], $res['year']);
			
			$click_str = empty($res['text']['click']) ? '<div>'.sprintf(__('Clicks on %s:', 'wpproads'), date('l, F d', $sdate) ).'</div>' : '<div>'.$res['text']['click'].'</div>';
			$impr_str = empty($res['text']['impr']) ? '<div>'.sprintf(__('Impressions on %s:', 'wpproads'), date('l, F d', $sdate) ).'</div>' : '<div>'.$res['text']['impr'].'</div>';
			$ctr_str = empty($res['text']['ctr']) ? '<div>'.sprintf(__('CTR (%s):', 'wpproads'), date('l, F d', $sdate) ).'</div>' : '<div>'.$res['text']['ctr'].'</div>';
		}
		
		if( current_user_can(WP_ADS_ROLE_ADMIN))
		{
			$clicks = $this->load_statistics( $query = 'WHERE type = "click" AND time >= '.$sdate.' AND time <= '.$edate );
			$impr = $this->load_statistics( $query = 'WHERE type = "impression" AND time >= '.$sdate.' AND time <= '.$edate );
		}
		else
		{
			$advertiser = $pro_ads_advertisers->get_advertisers( array('meta_key' => 'proad_advertiser_wpuser', 'meta_value' => get_current_user_id()) );
				
			$clicks = $this->load_statistics( $query = 'WHERE type = "click" AND time >= '.$sdate.' AND time <= '.$edate.' AND advertiser_id='.$advertiser[0]->ID );
			$impr = $this->load_statistics( $query = 'WHERE type = "impression" AND time >= '.$sdate.' AND time <= '.$edate.' AND advertiser_id='.$advertiser[0]->ID );
		}
		
		
		// CTR
		$ctr = !empty($clicks) && !empty($impr) ? count($clicks) / count($impr) * 100 : 0;
		$round_ctr = round($ctr,2).'%';
											
		$html = '';
		$html.= '<div class="stats_header_cont">';
			$html.= '<div class="stats_header_box">';
				$html.= $click_str;
				$html.= '<div class="am_data">'.count($clicks).'</div>';
			$html.= '</div>';
			$html.= '<div class="stats_header_box">';
				$html.= $impr_str;
				$html.= '<div class="am_data">'.count($impr).'</div>';
			$html.= '</div>';
			$html.= '<div class="stats_header_box" style="margin:0;">';
				$html.= $ctr_str;
				$html.= '<div class="am_data">'.$round_ctr.'</div>';
			$html.= '</div>';
			$html.= '<div class="clearFix"></div>';
		$html.= '</div>';
		
		return $html;
	}
	
	
	
	
	
	/*
	 * Show Statistics
	 *
	 * @access public
	 * @return html
	*/
	public function pro_ad_show_statistics( $arr = array() )
	{
		$array = array(
			'type'   => array('slug' => 'click', 'name' => __('Clicks', 'wpproads')),
			'color'  => '#66b71a',
			'range'  => 'month',
			'rid'    => 3, // 1 = all, 2 = year, 3 = month, 4 = day
			'day'    => date('d'),
			'month'  => date('m'),
			'year'   => date('Y')
		);
		$res = array_merge($array, $arr);
		
		// STATISTICS GRAPH/TABLE HOLDER
		echo '<div class="pro_ad_stats_graph">';
			echo $this->pro_ad_statistics( $res );
			$this->get_stats_table( $res );
		echo '</div>';
	}
	


	/*
	 * Statistics
	 *
	 * @access public
	 * @param string $range [day|month|year]
	 * @return html
	*/
	public function pro_ad_statistics( $arr = array() )
	{
		$array = array(
			'type'     => array('slug' => 'click', 'name' => __('Clicks', 'wpproads')),
			'color'    => '#66b71a',
			'range'    => 'month',
			'rid'      => 3,
			'day'      => date('d'),
			'month'    => date('m'),
			'year'     => date('Y')
		);
		$res = array_merge($array, $arr);
		
		$html = '';
		$html.= $this->pro_ad_show_statistics_header( $res );
		$html.= $this->statistics_menu( $res );
		
		/*
		 * Flot stats
		 * http://designmodo.com/create-interactive-graph-css3-jquery/
		*/
		$html.= '<div id="graph-wrapper">';
			$html.= '<div class="graph-container">';
				$html.= '<div id="graph-lines"></div>';
			$html.= '</div>';
		$html.= '</div>';
		
		// start JS
		$html.= '<script type="text/javascript">';
			
			$html.= 'jQuery(document).ready(function($) {';	
				$html.= $this->get_statistics_data($res);
			$html.= '});';
			
			$html.= 'function five_multiple( i, v){';
				$html.= 'return v*(Math.round(i/v));';
			$html.= '}';
        
        $html.= '</script>';
		
		return $html;
	}
	
	
	
	
	
	
	/*
	 * Statistics Menu
	 *
	 * @access public
	 * @param array $res
	 * @return html
	*/
	public function statistics_menu( $res )
	{
		$html = '';
		$html.= '<div class="graph-info">';
			$html.= '<a href="javascript:void(0)" class="stats_btn clicks" type="click" color="#66b71a" rid="'.$res['rid'].'" year="'.$res['year'].'" month="'.$res['month'].'" day="'.$res['day'].'">'.__('Clicks', 'wpproads').'</a>';
			$html.= '<a href="javascript:void(0)" class="stats_btn impressions" type="impression" color="#77b7c5" rid="'.$res['rid'].'" year="'.$res['year'].'" month="'.$res['month'].'" day="'.$res['day'].'">'.__('Impressions', 'wpproads').'</a>';
			
			$html.= $res['rid'] != 1 ? '<a href="javascript:void(0)" class="time_frame_btn" style="margin: 0 0 0 20px;" rid="1" year="'.$res['year'].'" month="'.$res['month'].'">'.__('All Time', 'wpproads').'</a>' : '<strong style="display:inline-block; margin: 7px 0 0 20px;">'.__('All Time', 'wpproads').'</strong>';
			$html.= $res['rid'] >= 2 ? ' / ' : '';
			if( $res['rid'] > 2 )
			{
				$html.= '<a href="javascript:void(0)" class="time_frame_btn" rid="2" year="'.$res['year'].'" month="'.$res['month'].'">'.$res['year'].'</a>';
			}
			elseif( $res['rid'] == 2 )
			{
				$html.= '<strong>'.$res['year'].'</strong>';
			}
			
			$html.= $res['rid'] >= 3 ? ' / ' : '';
			if( $res['rid'] > 3 )
			{
				$html.= '<a href="javascript:void(0)" class="time_frame_btn" rid="3" year="'.$res['year'].'" month="'.$res['month'].'">'.date('F', mktime(0,0,0, $res['month'], $res['day'], $res['year'])).'</a>';
			}
			elseif( $res['rid'] == 3 )
			{
				$html.= '<strong>'.date('F', mktime(0,0,0, $res['month'], $res['day'], $res['year'])).'</strong>';
			}
			$html.= $res['rid'] >= 4 ? ' / ' : '';
			$html.= $res['rid'] >= 4 ? '<strong>'.date('l, F d', mktime(0,0,0, $res['month'], $res['day'], $res['year'])).'</strong>' : '';
			
		$html.= '</div>';
		
		return $html;
	}
	
	
	
	
	
	
	
	/*
	 * Get Statistics Data
	 *
	 * @access public
	 * @param string $range [day|month|year]
	 * @return html
	*/
	public function get_statistics_data($arr = array())
	{
		global $wpdb, $pro_ads_advertisers;
		
		$array = array(
			'type'   => array('slug' => 'click', 'name' => __('Clicks', 'wpproads')),
			'color'  => '#66b71a',
			'range'  => 'month',
			'rid'    => 3,
			'day'    => date('d'),
			'month'  => date('m'),
			'year'   => date('Y')
		);
		$res = array_merge($array, $arr);
		
		$html = '';
		
		// Points Text array
		if( $res['rid'] == 1 )
		{
			$html.= 'var point_txt = [];';
			$all_years = $wpdb->get_results('SELECT YEAR(FROM_UNIXTIME(time)) as year FROM '.$wpdb->prefix.'pro_ad_system_stats GROUP BY year');
			
			if(count($all_years) < 2)
			{
				$year = !empty($all_years[0]->year) ? $all_years[0]->year-1 : date('Y')-1;
				$html.= 'point_txt.push("'.__('in', 'wpproads').' '.date('Y', mktime(0,0,0, 1, 1, $year)).'");';
			}
				
			foreach( $all_years as $i => $year )
			{
				$html.= 'point_txt.push("'.__('in', 'wpproads').' '.date('Y', mktime(0,0,0, 1, 1, $year->year)).'");';
			}
		}
		elseif( $res['rid'] == 2 )
		{
			$html.= 'var point_txt = [];';
			
			for( $i = 1; $i <= 12; $i++ )
			{
				$html.= 'point_txt.push("'.__('in', 'wpproads').' '.date('F', mktime(0,0,0, $i, $res['day'], $res['year'])).'");';
			}
		}
		elseif( $res['rid'] == 3 )
		{
			$am_days = cal_days_in_month(CAL_GREGORIAN, $res['month'], $res['year']);
			$html.= 'var point_txt = [];';
			
			for( $i = 1; $i <= $am_days; $i++ )
			{
				$html.= 'point_txt.push("'.__('on', 'wpproads').' '.date('F', mktime(0,0,0, $res['month'], $i, $res['year'])).' '.$i.'");';
			}
		}
		elseif( $res['rid'] == 4 )
		{
			$html.= 'var point_txt = [];';
			
			for( $i = 0; $i < 24; $i++ )
			{
				$u = $i+1;
				$html.= 'point_txt.push("'.__('between', 'wpproads').' '.date('G:i', mktime($i,0,0, $res['month'], $res['day'], $res['year'])).' and '.date('G:i', mktime($u,0,0, $res['month'], $res['day'], $res['year'])).'");';
			}
		}
		
		// Graph Data ##############################################
		$data_arr = array();
		$html.= 'var graphData = [{';
			
			if( $res['rid'] == 1 )
			{
				$all_years = $wpdb->get_results('SELECT YEAR(FROM_UNIXTIME(time)) as year FROM '.$wpdb->prefix.'pro_ad_system_stats GROUP BY year');
				
				$html.= 'data: [';
				
				if(count($all_years) < 2)
				{
					$year = !empty($all_years[0]->year) ? $all_years[0]->year-1 : date('Y')-1;
					$html.= '["'.($year).'", 0],';
				}
				
				foreach( $all_years as $i => $year )
				{
					$stime = mktime(0,0,0,1, 1, $year->year);
					$etime = mktime(23,59,59,12, 31, $year->year);
					if( current_user_can(WP_ADS_ROLE_ADMIN))
					{
						$res_data = $this->load_statistics( 'WHERE time >= '.$stime.' AND time <= '.$etime.' AND type = "'.$res['type']['slug'].'"' );
					}
					else
					{
						$advertiser = $pro_ads_advertisers->get_advertisers( array('meta_key' => 'proad_advertiser_wpuser', 'meta_value' => get_current_user_id()) );
						$res_data = $this->load_statistics( 'WHERE time >= '.$stime.' AND time <= '.$etime.' AND type = "'.$res['type']['slug'].'" AND advertiser_id = '.$advertiser[0]->ID );
					}
					
					$data_arr[] = count($res_data);
					$html.= '["'.$year->year.'", '.count($res_data).'],';
				}
				
				$html.= '],';
			}
			elseif( $res['rid'] == 2 )
			{
				$data_arr = array();
				
				// Data
				$html.= 'data: [';
				
				for( $i = 1; $i <= 12; $i++ )
				{
					$am_days = cal_days_in_month(CAL_GREGORIAN, $i, $res['year']);
					
					$stime = mktime(0,0,0,$i, 1, $res['year']);
					$etime = mktime(23,59,59,$i, $am_days, $res['year']);
					
					if( current_user_can(WP_ADS_ROLE_ADMIN))
					{
						$res_data = $this->load_statistics( 'WHERE time >= '.$stime.' AND time <= '.$etime.' AND type = "'.$res['type']['slug'].'"' );
					}
					else
					{
						$advertiser = $pro_ads_advertisers->get_advertisers( array('meta_key' => 'proad_advertiser_wpuser', 'meta_value' => get_current_user_id()) );
						$res_data = $this->load_statistics( 'WHERE time >= '.$stime.' AND time <= '.$etime.' AND type = "'.$res['type']['slug'].'" AND advertiser_id = '.$advertiser[0]->ID );
					}
					$data_arr[] = count($res_data);
					$html.= '["'.$i.'", '.count($res_data).'],';
				}
				
				$html.= '],';
			}
			elseif( $res['rid'] == 3 )
			{
				$am_days = cal_days_in_month(CAL_GREGORIAN, $res['month'], $res['year']);
				$data_arr = array();
				
				// Data
				$html.= 'data: [';
				
				for( $i = 1; $i <= $am_days; $i++ )
				{
					if( current_user_can(WP_ADS_ROLE_ADMIN))
					{
						$res_data = $this->load_statistics( 'WHERE date = '.mktime(0,0,0, $res['month'], $i, $res['year']).' AND type = "'.$res['type']['slug'].'"' ); //WHERE time >= '.$sdate.' AND time <= '.$edate 
					}
					else
					{
						$advertiser = $pro_ads_advertisers->get_advertisers( array('meta_key' => 'proad_advertiser_wpuser', 'meta_value' => get_current_user_id()) );
						$res_data = $this->load_statistics( 'WHERE date = '.mktime(0,0,0, $res['month'], $i, $res['year']).' AND type = "'.$res['type']['slug'].'" AND advertiser_id = '.$advertiser[0]->ID );
					}
			
					$data_arr[] = count($res_data);
					$html.= '["'.$i.'", '.count($res_data).'],';
					 //[6, 3], [7, 2], [8, 40], [9, 14], [10, 5] ';
				}
				
				$html.= '],';
			}
			elseif( $res['rid'] == 4 )
			{
				$data_arr = array();
				
				// Data
				$html.= 'data: [';
				
				for( $i = 0; $i < 24; $i++ )
				{
					$stime = mktime($i,0,0,$res['month'], $res['day'], $res['year']);
					$etime = mktime($i,59,59,$res['month'], $res['day'], $res['year']);
					
					if( current_user_can(WP_ADS_ROLE_ADMIN))
					{
						$res_data = $this->load_statistics( 'WHERE time >= '.$stime.' AND time <= '.$etime.' AND type = "'.$res['type']['slug'].'"' );
					}
					else
					{
						$advertiser = $pro_ads_advertisers->get_advertisers( array('meta_key' => 'proad_advertiser_wpuser', 'meta_value' => get_current_user_id()) );
						$res_data = $this->load_statistics( 'WHERE time >= '.$stime.' AND time <= '.$etime.' AND type = "'.$res['type']['slug'].'" AND advertiser_id = '.$advertiser[0]->ID );
					}
					
					$data_arr[] = count($res_data);
					$html.= '["'.mktime($i,0,0,$res['month'], $res['day'], $res['year']).'000", '.count($res_data).'],';
				}
				
				$html.= '],';
			}
		
			
			$html.= 'color: "'.$res['color'].'"';
			// $html.= 'points: { radius: 4, fillColor: '#77b7c5' }'; 
		
		$html.= '}];';
		
		$html.= $this->get_lines_graph($data_arr, $res);
		$html.= $this->get_point_tooltip( $res['type']['name'] );
		
		return $html;
	}
	
	
	
	
	
	
	
	
	
	
	/*
	 * Get Lines Graph
	 *
	 * @access public
	 * @param array $data_arr
	 * @return html
	*/
	public function get_lines_graph( $data_arr, $res )
	{
		global $wpdb;
		
		$max = !empty($data_arr) ? max($data_arr) : 0;
		$html = '';
		
		// Lines Graph #############################################
		$html.= '$.plot($("#graph-lines"), graphData, {';
			$html.= 'series: {';
				$html.= 'points: {';
					$html.= 'show: true,';
					$html.= 'radius: 3';
				$html.= '},';
				$html.= 'lines: {';
					$html.= 'show: true,';
					$html.= 'fill:.2,';
				$html.= '},';
				$html.= 'shadowSize: 0';
			$html.= '},';
			$html.= 'grid: {';
				//$html.= 'color: "#646464",';
				$html.= 'borderColor: {bottom: "#EEEEEE", left: "#EEEEEE"},';
				$html.= 'borderWidth: {top: 0, right: 0, bottom: 2, left: 2},';
				$html.= 'hoverable: true';
			$html.= '},';
			
			if( $res['rid'] == 4 )
			{
				$html.= 'xaxis: {';
					$html.= 'tickColor: "#F5F5F5",';
					$html.= 'mode: "time",';
					$html.= 'tickSize: [1, "hour"],';
					$html.= 'min: (new Date('.$res['year'].', '.($res['month']-1).', '.$res['day'].', 00, 00, 00, 00)).getTime(),'; // months in javascript Date() start from 0 to 11!
					$html.= 'max: (new Date('.$res['year'].', '.($res['month']-1).', '.$res['day'].', 23, 00, 00, 00)).getTime(),'; // months in javascript Date() start from 0 to 11!
				$html.= '},';
			}
			elseif( $res['rid'] == 1 )
			{
				$all_years = $wpdb->get_results('SELECT YEAR(FROM_UNIXTIME(time)) as year FROM '.$wpdb->prefix.'pro_ad_system_stats GROUP BY year');
				if( !empty($all_years))
				{
					$start_year = count($all_years) > 1 ? $all_years[0]->year : $all_years[0]->year-1;
				}
				else
				{
					$start_year = date('Y')-1;
				}
				
				$html.= 'xaxis: {';
					$html.= 'tickColor: "#F5F5F5",';
					$html.= 'tickDecimals: 0,';
					$html.= 'tickSize:1,';
					$html.= 'min:'.$start_year;
				$html.= '},';
			}
			else
			{
				$html.= 'xaxis: {';
					$html.= 'tickColor: "#F5F5F5",';
					$html.= 'tickDecimals: 0,';
					$html.= 'tickSize:1,';
					$html.= 'min:1';
				$html.= '},';
			}
			$html.= 'yaxis: {';
				$html.= 'tickColor: "#EEEEEE",';
				$html.= 'tickSize: five_multiple( five_multiple( '.$max.', 5)/4, 5 ),';
				$html.= 'tickDecimals: 0,'; 
				$html.= 'min:0';
			$html.= '}';
		$html.= '});';
		
		return $html;
	}
	
	
	
	
	
	/*
	 * Get tooltip
	 *
	 * @access public
	 * @param array $data_arr
	 * @return html
	*/
	public function get_point_tooltip( $type )
	{
		$html = '';
		$html.= 'function showTooltip(x, y, contents) {';
			$html.= '$(\'<div id="tooltip">\' + contents + \'</div>\').css({';
				$html.= 'top: y - 16,';
				$html.= 'left: x + 20';
			$html.= '}).appendTo("body").fadeIn();';
		$html.= '}';
	
		$html.= 'var previousPoint = null;';
	
		$html.= '$("#graph-lines").bind("plothover", function (event, pos, item) {';
			$html.= 'if (item) {';
				$html.= 'if (previousPoint != item.dataIndex) {';
					$html.= 'previousPoint = item.dataIndex;';
					$html.= '$("#tooltip").remove();';
					$html.= 'var x = item.datapoint[0],';
						$html.= 'y = item.datapoint[1];';
						$html.= 'showTooltip(item.pageX, item.pageY, y + " '.$type.' " + point_txt[item.dataIndex]);';
				$html.= '}';
			$html.= '} else {';
				$html.= '$("#tooltip").remove();';
				$html.= 'previousPoint = null;';
			$html.= '}';
		$html.= '});';
		
		return $html;
	}
	
	
	
	
	
	/*
	 * stats table
	 *
	 * @access public
	 * @param array $data_arr
	 * @return html
	*/
	public function get_stats_table( $arr = array() )
	{
		global $hook_suffix;
		
		$array = array(
			'type'   => array('slug' => 'click'), 
			'range'  => 'month',
			'rid'    => 3,
			'day'    => date('d'),
			'month'  => date('m'),
			'year'   => date('Y')
		);
		$res = array_merge($array, $arr);
		
		echo '<div class="pro_ad_stats_table">';
			
			if( $res['rid'] == 1 )
			{
				$statsTable = new Pro_Ad_All_Stats_List_Table();
				$filter_str = 'all-stats-filter';
				$range = 'all';
			}
			elseif( $res['rid'] == 2 )
			{
				$statsTable = new Pro_Ad_Stats_Year_List_Table();
				$filter_str = 'year-stats-filter';
				$range = 'year';
			}
			elseif( $res['rid'] == 3)
			{
				$statsTable = new Pro_Ad_Stats_List_Table();
				$filter_str = 'month-stats-filter';
				$range = 'month';
			}
			elseif( $res['rid'] == 4 )
			{
				$statsTable = new Pro_Ad_Stats_Day_List_Table();
				$filter_str = 'day-stats-filter';
				$range = 'day';
			}
			
			$statsTable->prepare_items( $res );
			
			echo '<form id="'.$filter_str.'" class="stats-filter" range="'.$range.'" rid="'.$res['rid'].'" type="'.$res['type'].'" day="'.$res['day'].'" month="'.$res['month'].'" year="'.$res['year'].'" method="get">';
				//echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />';
				$statsTable->display();
			echo '</form>';
        echo '</div>';
	}


	
	
	
	
	
	/*
	 * Save Impression
	 *
	 * @access public
	 * @param int $banner_id
	 * @return null
	*/
	public function save_impression( $banner_id, $adzone_id = '' )
	{
		global $wpdb, $pro_ads_main, $pro_ads_browser, $pro_ads_banners;
		
		// Check if satistics are enabled
		$wpproads_enable_stats = get_option('wpproads_enable_stats', 0);
		$wpproads_enable_userdata_stats = get_option('wpproads_enable_userdata_stats', 0);
		
		if( $wpproads_enable_stats )
		{
			$isbot = $pro_ads_main->detect_bots();
				
			if( !$isbot )
			{
				$today           = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
				$geo             = $wpproads_enable_userdata_stats ? $pro_ads_main->get_geo_info() : '';
				$browser         = $wpproads_enable_userdata_stats ? $pro_ads_browser->getBrowser() : '';
				$platform        = $wpproads_enable_userdata_stats ? $pro_ads_browser->getPlatform() : '';
				$ip_adress       = $wpproads_enable_userdata_stats ? $_SERVER['REMOTE_ADDR'] : '';
				$advertiser_id   = get_post_meta( $banner_id, 'banner_advertiser_id', true );
				$campaign_id     = get_post_meta( $banner_id, 'banner_campaign_id', true );
				$banner_contract = get_post_meta( $banner_id, 'banner_contract', true );
				
				// Update statistics ...
				$res = $wpdb->get_results("SELECT id FROM " . $wpdb->prefix . "pro_ad_system_stats 
					WHERE 
						type = 'impression' AND 
						banner_id = '".$banner_id."' AND 
						ip_address = '".$ip_adress."' AND 
						date = ".$today
				);
				
				if( !$res )
				{
					$wpdb->query("INSERT INTO " . $wpdb->prefix . "pro_ad_system_stats 
						SET 
							advertiser_id   = '".$advertiser_id."',
							campaign_id     = '".$campaign_id."',
							banner_id       = '".$banner_id."',
							adzone_id       = '".$adzone_id."',
							date            = '".$today."',
							time            = '".time()."',
							type            = 'impression',
							ip_address      = '".$ip_adress."',
							city            = '".$geo['city']."',
							country         = '".$geo['country']."',
							country_cd      = '".$geo['country_cd']."',
							browser         = '".$browser."',
							platform        = '".$platform."'									 
					");
					
					// Update banner impressions
					$banner_impressions = get_post_meta( $banner_id, 'banner_impressions', true );
					$banner_impressions = $banner_impressions+1;
					update_post_meta( $banner_id, 'banner_impressions', $banner_impressions );
					
					if( $banner_contract == 2 )
					{
						// Update banner status
						$pro_ads_banners->update_banner_status( $banner_id );
					}
				}
			}
		}
		else
		{
			// Update banner impressions
			$banner_impressions = get_post_meta( $banner_id, 'banner_impressions', true );
			$banner_impressions = $banner_impressions+1;
			update_post_meta( $banner_id, 'banner_impressions', $banner_impressions );
			
			if( $banner_contract == 2 )
			{
				// Update banner status
				$pro_ads_banners->update_banner_status( $banner_id );
			}
		}
	}
	
	
	
	
	
	
	
	
	/*
	 * Save Clicks
	 *
	 * @access public
	 * @param int $banner_id, $adzone_id
	 * @return null
	*/
	public function save_clicks( $banner_id, $adzone_id )
	{
		global $wpdb, $pro_ads_main, $pro_ads_browser, $pro_ads_banners;
		
		// Check if satistics are enabled
		$wpproads_enable_stats = get_option('wpproads_enable_stats', 0);
		$wpproads_enable_userdata_stats = get_option('wpproads_enable_userdata_stats', 0);
		
		if( $wpproads_enable_stats )
		{
			$isbot = $pro_ads_main->detect_bots();
				
			if( !$isbot )
			{
				$today           = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
				$geo             = $wpproads_enable_userdata_stats ? $pro_ads_main->get_geo_info() : '';
				$browser         = $wpproads_enable_userdata_stats ? $pro_ads_browser->getBrowser() : '';
				$platform        = $wpproads_enable_userdata_stats ? $pro_ads_browser->getPlatform() : '';
				$ip_adress       = $wpproads_enable_userdata_stats ? $_SERVER['REMOTE_ADDR'] : '';
				$advertiser_id   = get_post_meta( $banner_id, 'banner_advertiser_id', true );
				$campaign_id     = get_post_meta( $banner_id, 'banner_campaign_id', true );
				
				// Update statistics ...
				$res = $wpdb->get_results("SELECT id FROM " . $wpdb->prefix . "pro_ad_system_stats 
					WHERE 
						type = 'click' AND 
						banner_id = '".$banner_id."' AND 
						ip_address = '".$ip_adress."' AND 
						date = ".$today
				);
				
				if( !$res )
				{
					$wpdb->query("INSERT INTO " . $wpdb->prefix . "pro_ad_system_stats 
						SET 
							advertiser_id   = '".$advertiser_id."',
							campaign_id     = '".$campaign_id."',
							banner_id       = '".$banner_id."',
							adzone_id       = '".$adzone_id."',
							date            = '".$today."',
							time            = '".time()."',
							type            = 'click',
							ip_address      = '".$ip_adress."',
							city            = '".$geo['city']."',
							country         = '".$geo['country']."',
							country_cd      = '".$geo['country_cd']."',
							browser         = '".$browser."',
							platform        = '".$platform."'									 
					");
					
					// Update banner clicks
					$banner_clicks = get_post_meta( $banner_id, 'banner_clicks', true );
					$banner_clicks = $banner_clicks+1;
					update_post_meta( $banner_id, 'banner_clicks', $banner_clicks );
					
					// Update banner status
					$pro_ads_banners->update_banner_status( $banner_id );
				}
				
			}
		}
		else
		{
			// Update banner clicks
			$banner_clicks = get_post_meta( $banner_id, 'banner_clicks', true );
			$banner_clicks = $banner_clicks+1;
			update_post_meta( $banner_id, 'banner_clicks', $banner_clicks );
			
			// Update banner status
			$pro_ads_banners->update_banner_status( $banner_id );
		}
	}
	
}
?>