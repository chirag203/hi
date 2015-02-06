<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Pro_Ads_CPTs' ) ) :


class Pro_Ads_CPTs extends Pro_Ads_CPT_Meta_Options {	
	

	public function __construct() 
	{
		add_action('init', array($this, 'register_wp_pro_ad_system_posttypes'));
		add_action( 'restrict_manage_posts', array($this, 'wp_pro_ads_restrict_manage_posts'));
		add_filter( 'enter_title_here', array($this, 'change_default_cpt_title'));
		add_filter( 'parse_query', array($this, 'wp_pro_ads_table_filter'));
		
		add_action( 'add_meta_boxes', array($this, 'wp_pro_ads_advertisers_meta_options'));
		add_action( 'save_post', array($this, 'wp_pro_ads_advertisers_meta_options_save_postdata' ));
		add_action( 'save_post', array($this, 'wp_pro_ads_campaigns_meta_options_save_postdata' ));
		add_action( 'save_post', array($this, 'wp_pro_ads_banners_meta_options_save_postdata' ));
		add_action( 'save_post', array($this, 'wp_pro_ads_adzones_meta_options_save_postdata' ));
		
		add_action( 'trashed_post', array($this, 'wp_pro_trash_action') );
		add_action( 'untrash_post', array($this, 'wp_pro_untrash_action') );
		add_action( 'delete_post', array($this, 'wp_pro_ads_delete_action') );
	}

	
	
	/*
	 * Create CPTs
	 *
	 * @access public
	 * @return null
	*/
	public function register_wp_pro_ad_system_posttypes() 
	{
		$cpts = array();
		$cpts[0] = array(
			'name'               => __('Advertisers', 'wpproads'),
			'name_clean'         => 'advertisers',
			'singular_name'		 => __('Advertiser', 'wpproads'),
			'supports'           => array('title') //$supports = array('title','editor','author','thumbnail','excerpt','comments','revisions');
		);
		$cpts[1] = array(
			'name'               => __('Campaigns', 'wpproads'),
			'name_clean'         => 'campaigns',
			'singular_name'		 => __('Campaign', 'wpproads'),
			'supports'           => array('title')
		);
		$cpts[2] = array(
			'name'               => __('Banners', 'wpproads'),
			'name_clean'         => 'banners',
			'singular_name'		 => __('Banner', 'wpproads'),
			'supports'           => array('title')
		);
		$cpts[3] = array(
			'name'               => __('Adzones', 'wpproads'),
			'name_clean'         => 'adzones',
			'singular_name'		 => __('Adzone', 'wpproads'),
			'supports'           => array('title')
		);
		
		
		foreach( $cpts as $cpt )
		{	
			$labels = array(
				'name' 				=> $cpt['name'],
				'singular_name'		=> $cpt['singular_name'],
				'add_new' 			=> sprintf( __( 'Add New %s', 'wpproads' ), $cpt['singular_name']),
				'add_new_item' 		=> sprintf( __( 'Add New %s', 'wpproads' ), $cpt['singular_name']),
				'edit_item' 		=> sprintf( __( 'Edit %s', 'wpproads' ), $cpt['singular_name']),
				'new_item' 			=> sprintf( __( 'New %s', 'wpproads' ), $cpt['singular_name']),
				'view_item' 		=> sprintf( __( 'View %s', 'wpproads' ), $cpt['singular_name']),
				'search_items' 		=> sprintf( __( 'Search %s', 'wpproads' ), $cpt['name']),
				'not_found' 		=> sprintf( __( 'No %s Found', 'wpproads' ), $cpt['name']),
				'not_found_in_trash'=> sprintf( __( 'No %s Found in Trash', 'wpproads' ), $cpt['name']),
				'parent_item_colon' => '',
				'menu_name'			=> $cpt['name']
			);
			
			$taxonomies = array(); // 'post_tag', 'category'
			$supports = $cpt['supports'];
			
			$post_type_args = array(
				'labels' 			=> $labels,
				'singular_label' 	=> $cpt['name'],
				'public' 			=> true,
				'show_ui' 			=> true,
				'publicly_queryable'=> true,
				'query_var'			=> true,
				'capability_type' 	=> 'post',
				'has_archive' 		=> true,
				'hierarchical' 		=> false,
				'rewrite' 			=> array('slug' => $cpt['name_clean'], 'with_front' => false ),
				'supports' 			=> $supports,
				'show_in_menu'      => 'wp-pro-advertising',
				'taxonomies'		=> $taxonomies
			 );
			 register_post_type($cpt['name_clean'], $post_type_args);
			 
			 // Extra Filters
			 add_filter('manage_edit-'.$cpt['name_clean'].'_columns', array($this, 'wp_pro_ad_'.$cpt['name_clean'].'_columns'));
			 add_action('manage_posts_custom_column',  array($this, 'wp_pro_ad_'.$cpt['name_clean'].'_show_columns'));
		}
	}






	
	/*
	 * Change the post title placeholder for the CPT's
	 *
	 * @access public
	 * @return string
	*/
	public function change_default_cpt_title( $title )
	{
		$screen = get_current_screen();
		
		if ( $screen->post_type == 'advertisers' )
		{
			$title = 'Name';
		}
		elseif( $screen->post_type == 'banners' )
		{
			$title = 'Banner Title';
		}
		elseif( $screen->post_type == 'adzones' )
		{
			$title = 'Adzone Title';
		}
	 
		return $title;
	}


	
	
	
	
	
	/*
	 * Trash / Untrash actions
	 *
	 * @access public
	 * @return null
	*/
	public function wp_pro_trash_action( $post_id ) 
	{
		global $pro_ads_campaigns, $pro_ads_adzones;
		
		$post_type = get_post_type( $post_id );
	    if ( $post_type == 'banners' || $post_type == 'campaigns' || $post_type == 'advertisers' )
	    {
			if($post_type == 'banners' )
			{
				$linked_adzones = get_post_meta( $post_id, 'linked_adzones', true );
				if( !empty( $linked_adzones ) )
				{
					foreach($linked_adzones as $adzone )
					{
						$pro_ads_adzones->pro_ad_link_banner_to_adzone( $adzone, $post_id, 'remove' );
					}
				}
			}
			elseif($post_type == 'campaigns' )
			{
				update_post_meta( $post_id, 'campaign_status', 2 );
			}
			else
			{
				$campaigns = $pro_ads_campaigns->get_campaigns( array('meta_key' => 'campaign_advertiser_id', 'meta_value' => $post_id) );
				foreach( $campaigns as $campaign )
				{
					update_post_meta( $campaign->ID, 'campaign_status', 2 );
				}
			}
	    }
	}
	public function wp_pro_untrash_action( $post_id ) 
	{
		global $pro_ads_campaigns;
		
		$post_type = get_post_type( $post_id );
		if( $post_type == 'banners' || $post_type == 'campaigns' || $post_type == 'advertisers' )
	    {
			if($post_type == 'banners' )
			{
				
			}
			elseif($post_type == 'campaigns' )
			{
				$start_date = get_post_meta( $post_id, 'campaign_start_date', true );
				$end_date = get_post_meta( $post_id, 'campaign_end_date', true );
				$status = $pro_ads_campaigns->check_campaign_status( $start_date, $end_date );
				
				update_post_meta( $post_id, 'campaign_status', $status );
			}
			else
			{
				$campaigns = $pro_ads_campaigns->get_campaigns( array('meta_key' => 'campaign_advertiser_id', 'meta_value' => $post_id) );
				foreach( $campaigns as $campaign )
				{
					$start_date = get_post_meta( $campaign->ID, 'campaign_start_date', true );
					$end_date = get_post_meta( $campaign->ID, 'campaign_end_date', true );
					$status = $pro_ads_campaigns->check_campaign_status( $start_date, $end_date );
					
					update_post_meta( $campaign->ID, 'campaign_status', $status );
				}
			}
	    }
	}
	
	
	/*
	 * Delete actions
	 *
	 * @access public
	 * @return null
	*/
	public function wp_pro_ads_delete_action( $post_id ) 
	{
		global $pro_ads_campaigns;
		
		$post_type = get_post_type( $post_id );
		if( $post_type == 'campaigns' || $post_type == 'advertisers' )
	    {
			if($post_type == 'campaigns' )
			{
				$banners = $pro_ads_campaigns->get_linked_banners( $post_id );
				foreach($banners as $banner)
				{
					update_post_meta( $banner->ID, 'banner_campaign_id', '' );
				}
			}
			else
			{
				$campaigns = $pro_ads_campaigns->get_campaigns( array('meta_key' => 'campaign_advertiser_id', 'meta_value' => $post_id) );
				foreach($campaigns as $campaign)
				{
					$banners = $pro_ads_campaigns->get_linked_banners( $campaign->ID );
					foreach($banners as $banner)
					{
						wp_delete_post( $banner->ID, true );
					}
					
					wp_delete_post( $campaign->ID, true );
				}
			}
		}
	}
	



	
	
	/*
	 * Example for adding a custom filter to your CPT
	 * Gets called by add_action('restrict_manage_posts');
	 * http://www.smashingmagazine.com/2013/12/05/modifying-admin-post-lists-in-wordpress/
	 *
	 * @access public
	 * @return string
	*/
	public function wp_pro_ads_restrict_manage_posts() 
	{
		global $typenow, $pro_ads_advertisers, $pro_ads_campaigns;
		
		$html = '';
		$advertisers = $pro_ads_advertisers->get_advertisers();
		
		if( $typenow == 'campaigns' )
		{
			$html = $html . '<select name="filter_advertisers" class="chosen-select">';
				$html = $html . '<option value="">'.__('Show All Advertisers', 'wpproads').'</option>';
				
				foreach( $advertisers as $advertiser )
				{
					$selected = isset($_GET['filter_advertisers']) && $_GET['filter_advertisers'] == $advertiser->ID ? 'selected' : '';
					$html = $html . '<option value="'.$advertiser->ID.'" '.$selected.'>'.$advertiser->post_title.'</option>';
				}
				
			$html = $html . '</select>';
		}
		elseif($typenow == 'banners') 
		{
			$html = $html . '<select id="filter_advertisers" name="filter_advertisers" class="chosen-select">';
				$html = $html . '<option value="">'.__('Show All Advertisers', 'wpproads').'</option>';
				foreach( $advertisers as $advertiser )
				{
					$selected = isset($_GET['filter_advertisers']) && $_GET['filter_advertisers'] == $advertiser->ID ? 'selected' : '';
					$html = $html . '<option value="'.$advertiser->ID.'" '.$selected.'>'.$advertiser->post_title.'</option>';
				}
			$html = $html . '</select>';
			
			$html.= '<span id="select_cont">';
				$html.= '<select name="filter_campaigns" class="chosen-select">';
					$html.= '<option value="">'.__('Show All Campaigns', 'wpproads').'</option>';
					if( isset( $_GET['filter_advertisers'] ) && !empty($_GET['filter_advertisers']) ) // && ($_GET['banner_campaign_id']) && !empty($_GET['banner_campaign_id'])
					{
						$campaigns = $pro_ads_campaigns->get_campaigns( array('meta_key' => 'campaign_advertiser_id', 'meta_value' => $_GET['filter_advertisers']) );
						
						foreach( $campaigns as $campaign )
						{
							$select = $_GET['filter_campaigns'] == $campaign->ID ? 'selected' : '';
							$html.= '<option value="'.$campaign->ID.'" '.$select.'>'.$campaign->post_title.'</option>';
						}
					}
				$html.= '</select>';
			$html.= '</span>';	
		}
		
		echo $html;
	}

	
	
	/*
	 * Action to filter posts
	 * Gets called by add_filter('parse_query');
	 *
	 * @access public
	 * @return null
	*/
	public function wp_pro_ads_table_filter( $query ) 
	{
		if( is_admin() && $query->query['post_type'] == 'campaigns' ) 
		{
			$qv = &$query->query_vars;
			$qv['meta_query'] = array();
			
			if( isset( $_GET['filter_advertisers'] ) && !empty( $_GET['filter_advertisers'] ))
			{
				$qv['meta_query'][] = array(
					'field' => 'campaign_advertiser_id',
					'value' => $_GET['filter_advertisers'],
					'compare' => '=',
				);
			}
		}
		
		if( is_admin() && $query->query['post_type'] == 'banners' ) 
		{
			$qv = &$query->query_vars;
			$qv['meta_query'] = array();
			
			if( isset( $_GET['filter_advertisers'] ) && !empty( $_GET['filter_advertisers'] ))
			{
				$qv['meta_query'][] = array(
					'field' => 'campaign_advertiser_id',
					'value' => $_GET['filter_advertisers'],
					'compare' => '=',
				);
			}
	
		}
	}







	/*
	 * Custom table values for CPTs
	 *
	 * @access public
	*/
	
	// Advertisers --------------------------------------------------------
	public function wp_pro_ad_advertisers_columns($columns) 
	{
		//$columns = array();
		unset($columns['date']);
		$columns['title'] = __('Name', 'wpproads');
		$columns['email'] = __('Email', 'wpproads');
		$columns['wp_uid'] = __('Wordpress ID', 'wpproads');
		
		return $columns;
	}
	public function wp_pro_ad_advertisers_show_columns($name) 
	{
		global $post;
		
		switch ($name) 
		{
			case 'email':
				echo get_post_meta( $post->ID, 'proad_advertiser_email', true );
			break;
			case 'wp_uid':
				echo get_post_meta( $post->ID, 'proad_advertiser_wpuser', true);
			break;
		}
	}
	
	// Campaigns --------------------------------------------------------
	public function wp_pro_ad_campaigns_columns($columns) 
	{
		unset($columns['date']);
		$columns['c_advertiser'] = __('Advertiser', 'wpproads');
		$columns['c_linked_banners'] = __('Linked Banners', 'wpproads');
		$columns['c_status'] = __('Status', 'wpproads');
		
		return $columns;
	}
	public function wp_pro_ad_campaigns_show_columns($name) 
	{
		global $post, $pro_ads_main, $pro_ads_campaigns;
		
		switch ($name) 
		{
			case 'c_advertiser':
				$advertiser_id = get_post_meta( $post->ID, 'campaign_advertiser_id', true );
				echo !empty( $advertiser_id ) ? get_the_title( $advertiser_id ) : '<span class="not_linked_warning">'. __('Not linked to an advertiser!', 'wpproads').'</span>';
			break;
			case 'c_linked_banners':
				$banners = $pro_ads_campaigns->get_linked_banners( $post->ID );
				echo count($banners);
			break;
			case 'c_status':
				$status = get_post_meta( $post->ID, 'campaign_status', true );
				$status = $pro_ads_campaigns->get_status( $status );
				echo '<span class="'.$status['name_clean'].'">'. $status['name'] .'</span>';
			break;
		}
	}
	
	// Banners ----------------------------------------------------------
	public function wp_pro_ad_banners_columns( $existing_columns ) 
	{
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}
		unset( $existing_columns['title'], $existing_columns['comments'], $existing_columns['date'] );

		$columns          = array();
		$columns['cb']    = '<input type="checkbox" />';
		$columns['b_banner'] = __('<img src="'.WP_ADS_URL.'images/banner_icon_20.png" />', 'wpproads');
		$columns['b_name'] = __('Name', 'wpproads');
		$columns['b_advertiser'] = __('Advertiser', 'wpproads');
		$columns['b_campaign'] = __('Campaign', 'wpproads');
		$columns['b_status'] = __('Status', 'wpproads');
		//$columns['b_filetype'] = __('Type', 'wpproads');
		$columns['b_adzone'] = __('Adzone', 'wpproads');
		
		//return $columns;
		return array_merge( $columns, $existing_columns );
	}
	public function wp_pro_ad_banners_show_columns($name) 
	{
		global $post, $pro_ads_banners, $pro_ads_adzones, $pro_ads_campaigns;
		
		switch ($name) 
		{
			case 'b_banner' :
				$banner_type = get_post_meta( $post->ID, 'banner_type', true );
				$banner_url = get_post_meta( $post->ID, 'banner_url', true );
				
				$banner_is_image = $pro_ads_banners->check_if_banner_is_image($banner_type);
				$html = '';
				
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
				
				echo $html;
			break;
			case 'b_name' :
				$edit_link        = get_edit_post_link( $post->ID );
				$title            = _draft_or_post_title();
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title.'</a>';
					_post_states( $post );
				echo '</strong>';

				if ( $post->post_parent > 0 ) 
				{
					echo '&nbsp;&nbsp;&larr; <a href="'. get_edit_post_link( $post->post_parent ) .'">'. get_the_title( $post->post_parent ) .'</a>';
				}

				// Excerpt view
				if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) 
				{
					echo apply_filters( 'the_excerpt', $post->post_excerpt );
				}

				// Get actions
				$actions = array();

				$actions['id'] = 'ID: ' . $post->ID;

				if ( $can_edit_post && 'trash' != $post->post_status ) 
				{
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'wpproads' ) ) . '">' . __( 'Edit', 'wpproads' ) . '</a>';
					$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline', 'wpproads' ) ) . '">' . __( 'Quick&nbsp;Edit', 'wpproads' ) . '</a>';
				}
				if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) 
				{
					if ( 'trash' == $post->post_status ) 
					{
						$actions['untrash'] = '<a title="' . esc_attr( __( 'Restore this item from the Trash', 'wpproads') ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . '">' . __( 'Restore', 'wpproads' ) . '</a>';
					} elseif ( EMPTY_TRASH_DAYS ) {
						$actions['trash'] = '<a class="submitdelete" title="' . esc_attr( __( 'Move this item to the Trash', 'wpproads' ) ) . '" href="' . get_delete_post_link( $post->ID ) . '">' . __( 'Trash', 'wpproads' ) . '</a>';
					}

					if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) 
					{
						$actions['delete'] = '<a class="submitdelete" title="' . esc_attr( __( 'Delete this item permanently', 'wpproads' ) ) . '" href="' . get_delete_post_link( $post->ID, '', true ) . '">' . __( 'Delete Permanently', 'wpproads' ) . '</a>';
					}
				}
				if ( $post_type_object->public ) 
				{
					if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) 
					{
						if ( $can_edit_post )
							$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'wpproads' ), $title ) ) . '" rel="permalink">' . __( 'Preview', 'wpproads' ) . '</a>';
					} elseif ( 'trash' != $post->post_status ) {
						$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'wpproads' ), $title ) ) . '" rel="permalink">' . __( 'View', 'wpproads' ) . '</a>';
					}
				}

				$actions = apply_filters( 'post_row_actions', $actions, $post );

				echo '<div class="row-actions">';

				$i = 0;
				$action_count = sizeof($actions);

				foreach ( $actions as $action => $link ) 
				{
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo '<span class="' . $action . '">' . $link . $sep . '</span>';
				}
				echo '</div>';
				
				get_inline_data( $post );

			break;
			case 'b_advertiser':
				$advertiser_id = get_post_meta( $post->ID, 'banner_advertiser_id', true );
				echo !empty( $advertiser_id ) ? '<a href="post.php?post='.$advertiser_id.'&action=edit">'.get_the_title( $advertiser_id ).'</a>' : '<span class="na">&ndash;</span>';
			break;
			case 'b_campaign':
				$campaign_id = get_post_meta( $post->ID, 'banner_campaign_id', true );
				$campaign_status = get_post_meta( $campaign_id, 'campaign_status', true );
				$campaign_status = $pro_ads_campaigns->get_status($campaign_status);
				
				echo !empty( $campaign_id ) ? '<a href="post.php?post='.$campaign_id.'&action=edit">'.get_the_title( $campaign_id ).'</a><br><small style="color:#999;">Campaign status:</small> <small class="'.$campaign_status['name_clean'].'"><em>['.$campaign_status['name'].']</em></small>' : '<span class="na">&ndash;</span>';
			break;
			case 'b_status':
				$banner_status = get_post_meta( $post->ID, 'banner_status', true );
				$status = $pro_ads_banners->get_status($banner_status);
				echo '<span class="'.$status['name_clean'].'">'. $status['name'].'</span>';
			break;
			/*case 'b_filetype':
				$banner_type = get_post_meta( $post->ID, 'banner_type', true );
				echo !empty($banner_type) ? '<img src="'.WP_ADS_URL.'/images/filetypes/'.$banner_type.'.png" width="25" />' : '<span class="na">&ndash;</span>';
			break;*/
			case 'b_adzone':
				
				$banner_size = get_post_meta( $post->ID, 'banner_size', true );
				$recommended_adzones = $pro_ads_adzones->get_adzones( 
					array( 
						'meta_query'  => array(
							'relation' => 'OR',
							array(
								'key' => 'adzone_size',
								'value' => $banner_size,
								'compare' => '='
							),
							array(
								'key' => 'adzone_size',
								'value' => '',
								'compare' => '='
							),
						)
					)
				);
				
				// Get linked adzones for this banner
				$linked_adzones = get_post_meta( $post->ID, 'linked_adzones', true );
				
				$html = '';
				$html.= '<div style="position:relative;">';
					$html.= '<div class="loading_adzone loading_adzone_'.$post->ID.'" style="position:absolute; margin:7px; z-index:1; display:none;">'.__('Loading...', 'wpproads').'</div>';
					$html.= '<div class="select-adzone-cont-'.$post->ID.'">';
						$html.= '<select data-placeholder="'.__('No adzone selected.', 'wpproads').'" style="width:100%;" class="chosen-select select-adzone select-adzone-'.$post->ID.'" multiple>';
							$html.= '<option value=""></option>';
							$html.= '<optgroup label="'.__('Recommended', 'wpproads').'">';
								foreach( $recommended_adzones as $adzone )
								{
									$disabled = !$pro_ads_adzones->check_if_adzone_is_active( $adzone->ID, 1, $post->ID ) ? 'disabled="true"' : '';
									$selected = !empty($linked_adzones) ? in_array($adzone->ID, $linked_adzones) ? 'selected' : '' : '';
									$html.= '<option '.$disabled.'  value="'.$adzone->ID.'" bid="'.$post->ID.'" '.$selected.'>'.$adzone->post_title.'</option>';
								}
							$html.= '</optgroup>';
							
							// Get all other adzones (all not recommended adzones)
							$all_adzones = $pro_ads_adzones->get_adzones(
								array( 
									'meta_query'  => array(
										'relation' => 'AND',
										array(
											'key' => 'adzone_size',
											'value' => $banner_size,
											'compare' => '!='
										),
										array(
											'key' => 'adzone_size',
											'value' => '',
											'compare' => '!='
										),
									)
								)
							);
							
							$html.= '<optgroup label="'.__('All', 'wpproads').'">';
								foreach( $all_adzones as $adzone )
								{
									$disabled = !$pro_ads_adzones->check_if_adzone_is_active( $adzone->ID, 1, $post->ID ) ? 'disabled="true"' : '';
									$selected = !empty($linked_adzones) ? in_array($adzone->ID, $linked_adzones) ? 'selected' : '' : '';
									$html.= '<option '.$disabled.' value="'.$adzone->ID.'" bid="'.$post->ID.'" '.$selected.'>'.$adzone->post_title.'</option>';
								}
							$html.= '</optgroup>';
						$html.= '</select>';
					$html.= '</div>';
				$html.= '</div>';
					
				echo $html;
			break;
		}
	}
	
	// Adzones ----------------------------------------------------------
	public function wp_pro_ad_adzones_columns($columns) 
	{
		unset($columns['date']);
		$columns['title'] = __('Name', 'wpproads');
		$columns['a_size'] = __('Size', 'wpproads');
		$columns['a_linked'] = '';
		$columns['a_code'] = '';
		
		return $columns;
	}
	public function wp_pro_ad_adzones_show_columns($name) 
	{
		global $post, $pro_ads_adzones, $pro_ads_templates;
		
		switch ($name) 
		{
			case 'a_size' : 
				$size = get_post_meta( $post->ID, 'adzone_size', true );
				$custom = get_post_meta( $post->ID, 'adzone_custom_size', true );
				$responsive = get_post_meta( $post->ID, 'adzone_responsive', true );
				
				echo $pro_ads_adzones->pro_ad_output_adzone_size( $size, $custom, $responsive );
			break;
			case 'a_linked' : 
				$html = '';
				$html.= $pro_ads_templates->pro_ad_adzone_order_popup_screen( $post->ID );
				$html.= '<a href="#TB_inline?width=600&inlineId=pro_ads_adzone_order_popup_'.$post->ID.'" class="adzone_linked_order thickbox" title="'.__('Linked Banners', 'wpproads').'">';
					$html.= '<img src="'.WP_ADS_URL.'images/popupIcon.gif" /> '.__('Linked Banners', 'wpproads');
                $html.= '</a>';
				
				echo $html;
			break;
			case 'a_code' : 
				$html = '';
				$html.= $pro_ads_templates->pro_ad_adzone_popup_screen( $post->ID );
				$html.= '<a href="#TB_inline?width=600&inlineId=pro_ads_adzone_popup_'.$post->ID.'" class="adzone_code thickbox" title="'.__('Get Adzone Code', 'wpproads').'">';
					$html.= '<img src="'.WP_ADS_URL.'images/popupIcon.gif" /> ' . __('Get code', 'wpproads');
                $html.= '</a>';
				
				echo $html;
			break;
		}
	}
	

}


endif;
?>