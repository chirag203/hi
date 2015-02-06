<?php
class Pro_Ads_CPT_Meta_Options {
	
	
	
	
	
	
	/*
	 * Adds a box to the main column on the Post and Page edit screens.
	 *
	 * @access public
	*/
	public function wp_pro_ads_advertisers_meta_options() 
	{
		$screens = array( 'advertisers', 'campaigns', 'banners', 'adzones' );
	
		foreach ( $screens as $screen ) 
		{	
			add_meta_box( 'wp_pro_ads_'.$screen.'_meta_options_id', sprintf(__( '%s Options:', 'wpproads' ), $screen), array($this, 'wp_pro_ads_'.$screen.'_meta_options_custom_box'), $screen, 'normal', 'high' );
			
			if( $screen == 'banners' )
			{
				add_meta_box( 'wp_pro_ads_banners_meta_upload_id', __( 'Banner type:', 'wpproads' ), array($this, 'wp_pro_ads_banners_meta_upload_custom_box'), $screen, 'normal', 'default' );
				add_meta_box( 'wp_pro_ads_banners_meta_link_adzones_id', __( 'Link banner to Adzone:', 'wpproads' ), array($this, 'wp_pro_ads_banners_meta_link_adzones_box'), $screen, 'normal', 'default' );	
				add_meta_box( 'wp_pro_ads_banners_meta_optional_settings_id', __( 'Optional Settings:', 'wpproads' ), array($this, 'wp_pro_ads_banners_meta_optional_settings_box'), $screen, 'normal', 'default' );	
				add_meta_box( 'wp_pro_ads_banners_meta_side_stats_id', __( 'Banner Stats:', 'wpproads' ), array($this, 'wp_pro_ads_banners_meta_side_stats_box'), $screen, 'side', 'default' );	
			}
			/*elseif( $screen == 'adzones' )
			{
				add_meta_box( 'wp_pro_ads_adzones_meta_popup_id', __( 'Popup Options:', 'wpproads' ), array($this, 'wp_pro_ads_adzones_meta_popup_box'), $screen, 'normal', 'default' );
			}*/
		}
	}
	
	
	
	function wp_pro_ads_advertisers_meta_options_custom_box( $post ) 
	{
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wp_pro_ads_advertisers_meta_options_inner_custom_box', 'wp_pro_ads_advertisers_meta_options_inner_custom_box_nonce' );
	
		/*	
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		*/
		$advertiser_email       = get_post_meta( $post->ID, 'proad_advertiser_email', true );
		$wpuser_id              = get_post_meta( $post->ID, 'proad_advertiser_wpuser', true );
		?>
		<div class="tuna_meta">
			<table class="form-table">
				<tbody>
		  			<tr valign="top">
                        <th scope="row">
                            <?php _e( "Email", 'wpproads' ); ?>
                            <span class="description"><?php _e('If the email address matches an existing Wordpress user account this advertiser will be linked to the WP account.', 'wpproads'); ?></span>
                        </th>
                        <td>
                            <input type="text" name="proad_advertiser_email" value="<?php echo !empty( $advertiser_email ) ? $advertiser_email : ''; ?>" placeholder="<?php _e('Email', 'wpproads'); ?>" />
                            <span class="description"></span>
                        </td>
                    </tr>
                    
                    <?php
					if( !empty( $wpuser_id ))
					{
						$wpuser = get_user_by( 'id', $wpuser_id );
						?>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Wordpress user", 'wpproads' ); ?>
                                <span class="description"><?php _e('This email is linked to an existing Wordpress user.', 'wpproads'); ?></span>
                            </th>
                            <td>
                            	<table>
                                	<tbody>
                                    	<tr>
                                        	<td>Username: </td>
                                            <td><?php echo $wpuser->user_login; ?></td>
                                        </tr>
                                        <tr>
                                        	<td>Name: </td>
                                            <td><?php echo !empty($wpuser->first_name) && !empty($wpuser->last_name) ? $wpuser->first_name.' '.$wpuser->last_name : __('n/a', 'wpproads'); ?></td>
                                        </tr>
                                        <tr>
                                			<td>ID:</td>
                                            <td><?php echo $wpuser_id; ?></td>
                                        </tr>
                                        <tr>
                                			<td>Registered:</td>
                                            <td><?php echo $wpuser->user_registered; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <span class="description"></span>
                            </td>
                        </tr>
                        <?php
					}
					?>
                    
                </tbody>
            </table>
        </div>
        <?php
	}
	
	
	
	
	function wp_pro_ads_advertisers_meta_options_save_postdata( $post_id ) 
	{
	  /*
	   * We need to verify this came from the our screen and with proper authorization,
	   * because save_post can be triggered at other times.
	   */
	
	  // Check if our nonce is set.
	  if ( ! isset( $_POST['wp_pro_ads_advertisers_meta_options_inner_custom_box_nonce'] ) )
		return $post_id;
	
	  $nonce = $_POST['wp_pro_ads_advertisers_meta_options_inner_custom_box_nonce'];
	
	  // Verify that the nonce is valid.
	  if ( ! wp_verify_nonce( $nonce, 'wp_pro_ads_advertisers_meta_options_inner_custom_box' ) )
		  return $post_id;
	
	  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		  return $post_id;
	
	  // Check the user's permissions.
	  if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;
	  } else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
	  }
	  /* OK, its safe for us to save the data now. */
	  
	  // Check if email exists in our user database.
	  $wpuser = get_user_by( 'email', $_POST['proad_advertiser_email'] );
	  $wpuid = !empty($wpuser) ? $wpuser->ID : '';
	
	  // Sanitize user input.
	  $advertiser_email  = sanitize_text_field( $_POST['proad_advertiser_email'] );
	
	  // Update the meta field in the database.
	  update_post_meta( $post_id, 'proad_advertiser_email', $advertiser_email );
	  update_post_meta( $post_id, 'proad_advertiser_wpuser', $wpuid );
	
	}
	
	
	
	
	
	
	
	
	
	function wp_pro_ads_campaigns_meta_options_custom_box( $post ) 
	{
		global $pro_ads_advertisers;

		wp_nonce_field( 'wp_pro_ads_campaigns_meta_options_inner_custom_box', 'wp_pro_ads_campaigns_meta_options_inner_custom_box_nonce' );
	
		$start_date         = get_post_meta( $post->ID, 'campaign_start_date', true );
		$end_date           = get_post_meta( $post->ID, 'campaign_end_date', true );
		$advertiser_id      = get_post_meta( $post->ID, 'campaign_advertiser_id', true );
		$advertisers        = $pro_ads_advertisers->get_advertisers();
		?>
		<div class="tuna_meta">
			<table class="form-table">
				<tbody>
		  			<tr valign="top">
                        <th scope="row">
                            <?php _e( "Campaign for:", 'wpproads' ); ?>
                            <span class="description"><?php _e('Select an advertiser for this campaign.', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="campaign_advertiser_id" class="chosen-select" required="required">
                            	<option value=""><?php _e('Select an advertiser', 'wpproads'); ?></option>
                                <?php
								foreach( $advertisers as $advertiser )
								{
									$select = $advertiser_id == $advertiser->ID ? 'selected' : '';
                            		echo '<option value="'.$advertiser->ID.'" '.$select.'>'.$advertiser->post_title.'</option>';
								}
								?>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <?php _e( "Campaign period:", 'wpproads' ); ?>
                            <span class="description"><?php _e('Add a start and end date por this campaign.', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<input id="start_date" readonly="readonly" name="start_date" placeholder="<?php _e('Start date', 'wpproads' ); ?>" value="<?php echo !empty($start_date) ? date('m.d.Y', $start_date) : ''; ?>" class="input" style="width:150px;">
                            <input id="end_date" readonly="readonly" name="end_date" placeholder="<?php _e('End date', 'wpproads' ); ?>" value="<?php echo !empty($end_date) ? date('m.d.Y', $end_date) : ''; ?>" class="input" style="width:150px;">
                            <span class="description"><?php _e('Leave empty to keep campaign active.', 'wpproads'); ?></span>
                        </td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
        <?php
	}
	function wp_pro_ads_campaigns_meta_options_save_postdata( $post_id ) 
	{
		// Check if our nonce is set.
		if ( ! isset( $_POST['wp_pro_ads_campaigns_meta_options_inner_custom_box_nonce'] ) )
		return $post_id;
		$nonce = $_POST['wp_pro_ads_campaigns_meta_options_inner_custom_box_nonce'];
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'wp_pro_ads_campaigns_meta_options_inner_custom_box' ) )
		  return $post_id;
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		  return $post_id;
		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;
		} else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
		}
		/* OK, its safe for us to save the data now. */
		
		// Create startdate
		if( !empty( $_POST['start_date'] ))
		{
			$dt = explode('.', $_POST['start_date']);
			$sdate = mktime(0,0,0,$dt[0],$dt[1],$dt[2]);
		}
		else
		{
			$sdate = time();
		}
		// Create enddate
		if( !empty( $_POST['end_date'] ))
		{
			$dt = explode('.', $_POST['end_date']);
			$edate = mktime(0,0,0,$dt[0],$dt[1],$dt[2]);
		}
		else
		{
			$edate = '';
		}
		
		/* 
		 * Check/ update status
		 * 0 = draft, 1 = running, 2 = finished
		*/
		if( !empty($edate) && time() > $edate )
		{
			$status = 2;
		}
		elseif( !empty($sdate) && time() < $sdate )
		{
			$status = 0;
		}
		else
		{
			$status = 1;
		}
		
		// Sanitize user input.
		$advertiser_id  = sanitize_text_field( $_POST['campaign_advertiser_id'] );
		$start_date  = sanitize_text_field( $sdate );
		$end_date  = sanitize_text_field( $edate );
		// Update the meta field in the database.
		update_post_meta( $post_id, 'campaign_advertiser_id', $advertiser_id );
		update_post_meta( $post_id, 'campaign_start_date', $start_date );
		update_post_meta( $post_id, 'campaign_end_date', $end_date );
		update_post_meta( $post_id, 'campaign_status', $status );
	}
	
	
	
	
	
	// BANNER
	function wp_pro_ads_banners_meta_options_custom_box( $post ) 
	{
		global $pro_ads_advertisers, $pro_ads_campaigns;
		
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wp_pro_ads_banners_meta_options_inner_custom_box', 'wp_pro_ads_banners_meta_options_inner_custom_box_nonce' );
		
		$advertiser_id       = get_post_meta( $post->ID, 'banner_advertiser_id', true );
		$campaign_id         = get_post_meta( $post->ID, 'banner_campaign_id', true );
		$banner_url          = get_post_meta( $post->ID, 'banner_url', true );
		$banner_link         = get_post_meta( $post->ID, 'banner_link', true );
		$banner_target       = get_post_meta( $post->ID, 'banner_target', true );
		$banner_status       = get_post_meta( $post->ID, 'banner_status', true );
		
		$advertisers        = $pro_ads_advertisers->get_advertisers();
		$campaigns          = $pro_ads_campaigns->get_campaigns( array('meta_key' => 'campaign_advertiser_id', 'meta_value' => $advertiser_id) );
		?>
		<div class="tuna_meta">
			<table class="form-table">
				<tbody>
                	<tr valign="top">
                        <th scope="row">
                            <?php _e( "Banner for:", 'wpproads' ); ?>
                            <span class="description"><?php _e('Select an advertiser for this banner.', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="banner_advertiser_id" class="chosen-select select_banner_advertiser" required="required">
                            	<option value=""><?php _e('Select an advertiser', 'wpproads'); ?></option>
                                <?php
								foreach( $advertisers as $advertiser )
								{
									$select = $advertiser_id == $advertiser->ID ? 'selected' : '';
                            		echo '<option value="'.$advertiser->ID.'" '.$select.'>'.$advertiser->post_title.'</option>';
								}
								?>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr valign="top" class="<?php echo empty($campaign_id) ? 'hidden_row' : ''; ?> hide_row">
                        <th scope="row">
                            <?php _e( "Banner campaign:", 'wpproads' ); ?>
                            <span class="description"><?php _e('Select a campaign for this banner.', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<!-- Campaign select gets loaded here by ajax -->
                        	<div id="select_cont">
                            	<select name="banner_campaign_id" class="chosen-select select_banner_campaign" required="required">
                                	<option value=""><?php _e('Select a campaign', 'wpproads'); ?></option>
                                    <?php
									foreach( $campaigns as $campaign )
									{
										$select = $campaign_id == $campaign->ID ? 'selected' : '';
										echo '<option value="'.$campaign->ID.'" '.$select.'>'.$campaign->post_title.'</option>';
									}
									?>
                                </select>
                            </div> 
                        
                            <span class="description"></span>
                        </td>
                    </tr>
		  			<tr valign="top">
                        <th scope="row">
                            <?php _e( "Link", 'wpproads' ); ?>
                            <span class="description"><?php _e('', 'wpproads'); ?></span>
                        </th>
                        <td>
                            <input type="text" name="banner_link" value="<?php echo !empty( $banner_link ) ? $banner_link : ''; ?>" placeholder="<?php _e('http://www.yourlink.com', 'wpproads'); ?>" />
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Target", 'wpproads' ); ?>
                            <span class="description"><?php _e('', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="banner_target">
                            	<option value="_blank" <?php echo empty($banner_target) || $banner_target == '_blank' ? 'selected' : ''; ?>>
									<?php _e('_blank, Load in a new window.', 'wpproads'); ?>
                                </option>
                                <option value="_self" <?php echo $banner_target == '_self' ? 'selected' : ''; ?>>
									<?php _e('_self, Load in the same frame as it was clicked.', 'wpproads'); ?>
                                </option>
                                <option value="_parent" <?php echo $banner_target == '_parent' ? 'selected' : ''; ?> >
									<?php _e('_parent, Load in the parent frameset.', 'wpproads'); ?>
                                </option>
                                <option value="_top" <?php echo $banner_target == '_top' ? 'selected' : ''; ?>>
									<?php _e('_top, Load in the full body of the window.', 'wpproads'); ?>
                                </option>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Status", 'wpproads' ); ?>
                            <span class="description"><?php _e('', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="banner_status">
                            	<option value="0" <?php echo $banner_status == 0 ? 'selected' : ''; ?>><?php _e('Draft', 'wpproads'); ?></option>
                            	<option value="1" <?php echo $banner_status == 1 ? 'selected' : ''; ?>><?php _e('Active', 'wpproads'); ?></option>
                                <option value="2" <?php echo $banner_status == 2 ? 'selected' : ''; ?>><?php _e('Inactive', 'wpproads'); ?></option>
                                <option value="3" <?php echo $banner_status == 3 ? 'selected' : ''; ?>><?php _e('Awaiting payment', 'wpproads'); ?></option>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
					
                </tbody>
            </table>
        </div>
        <?php
	}
	function wp_pro_ads_banners_meta_options_save_postdata( $post_id ) 
	{
		// Check if our nonce is set.
		if ( ! isset( $_POST['wp_pro_ads_banners_meta_options_inner_custom_box_nonce'] ) )
		return $post_id;
		$nonce = $_POST['wp_pro_ads_banners_meta_options_inner_custom_box_nonce'];
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'wp_pro_ads_banners_meta_options_inner_custom_box' ) )
		  return $post_id;
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		  return $post_id;
		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;
		} else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
		}
		/* OK, its safe for us to save the data now. */
		
		$banner_start_date = get_post_meta( $post_id, 'banner_start_date', true );
		
		// Sanitize user input.
		$advertiser_id    = sanitize_text_field( $_POST['banner_advertiser_id'] );
		$campaign_id      = sanitize_text_field( $_POST['banner_campaign_id'] );
		$banner_url       = sanitize_text_field( $_POST['banner_url'] );
		$banner_html      = $_POST['banner_html'];
		$banner_link      = sanitize_text_field( $_POST['banner_link'] );
		$banner_target    = sanitize_text_field( $_POST['banner_target'] );
		$banner_no_follow = sanitize_text_field( $_POST['banner_no_follow'] );
		$banner_contract  = sanitize_text_field( $_POST['banner_contract'] );
		$banner_duration  = sanitize_text_field( $_POST['banner_duration'] );
		
		$banner_duration = !empty($banner_contract) ? $banner_duration : '';
		
		$path_info = !empty( $banner_url ) ? pathinfo( $banner_url ) : '';
		$banner_type = !empty( $banner_html ) ? 'html' : '';
		$banner_type = !empty( $path_info['extension'] ) ? $path_info['extension'] : $banner_type;
		
		$banner_size = !empty( $banner_url ) ? getimagesize($banner_url) : '';
		$size = !empty($banner_size) ? $banner_size[0].'x'.$banner_size[1] : '';
		
		$banner_status  = !empty($banner_type) ? sanitize_text_field( $_POST['banner_status'] ) : 0;
		
		// Update the meta field in the database.
		update_post_meta( $post_id, 'banner_advertiser_id', $advertiser_id );
		update_post_meta( $post_id, 'banner_campaign_id', $campaign_id );
		update_post_meta( $post_id, 'banner_url', $banner_url );
		update_post_meta( $post_id, 'banner_html', $banner_html );
		update_post_meta( $post_id, 'banner_link', $banner_link );
		update_post_meta( $post_id, 'banner_target', $banner_target );
		update_post_meta( $post_id, 'banner_status', $banner_status );
		update_post_meta( $post_id, 'banner_type', $banner_type );
		update_post_meta( $post_id, 'banner_size', $size );
		update_post_meta( $post_id, 'banner_no_follow', $banner_no_follow );
		update_post_meta( $post_id, 'banner_contract', $banner_contract );
		update_post_meta( $post_id, 'banner_duration', $banner_duration );
		
		if( empty( $banner_start_date ) && $banner_status == 1)
		{
			update_post_meta( $post_id, 'banner_start_date', time() );
		}
	}
	
	// BANNER - upload
	function wp_pro_ads_banners_meta_upload_custom_box($post)
	{
		wp_nonce_field( 'wp_pro_ads_banners_meta_options_inner_custom_box', 'wp_pro_ads_banners_meta_options_inner_custom_box_nonce' );
		
		$banner_url          = get_post_meta( $post->ID, 'banner_url', true );
		$banner_html         = get_post_meta( $post->ID, 'banner_html', true );
		
		$path_info = !empty( $banner_url ) ? pathinfo( $banner_url ) : '';
		$banner_type = !empty( $banner_html ) ? 'html' : '';
		$banner_type = !empty( $path_info['extension'] ) ? $path_info['extension'] : $banner_type;
		?>
		<div class="tuna_meta">
			<table class="form-table">
				<tbody>
		  			<tr valign="top">
						<th scope="row">
							<?php _e( "Option 1", 'wpproads' ); ?>
							<span class="description"><?php _e('Upload/Select a banner.', 'wpproads'); ?></span>
						</th>
						<td>
							<!--<div class="img_preview" style="float:left; margin:0 0 0 -60px;"><?php //echo !empty( $banner_url ) ? '<img src="'.$banner_url.'" height="40" />' : '<img id="banner-img-preview" src="" height="40" />'; ?></div>-->
							<div style="float:left; width:500px;">
                            	<input type="text" size="40" id="banner_url" name="banner_url" value="<?php echo !empty( $banner_url ) ? $banner_url : ''; ?>" placeholder="<?php _e('Banner url', 'wpproads'); ?>" />
								<input class="upload_image_button button" type="button" value="<?php _e('Upload Banner', 'wpproads'); ?>" />
                                
								<span class="description"></span>
							</div>
							<div style="clear:both;"></div>
						</td>
					</tr>
                    <tr valign="top">
						<th scope="row">
							<?php _e( "Option 2", 'wpproads' ); ?>
							<span class="description"><?php _e('HTML Code (adSense, iframes, text ads, ...)', 'wpproads'); ?></span>
						</th>
						<td>
                            <textarea name="banner_html" style="width:100%; height:200px;"><?php echo !empty( $banner_html ) ? $banner_html : ''; ?></textarea>
                            
                            <span class="description"></span>
						</td>
					</tr>
                    <tr>
                    	<td colspan="2">
                        	<div class="img_preview"><?php echo !empty( $banner_url ) ? '<img src="'.$banner_url.'" style="max-width:100%;" />' : '<img id="banner-img-preview" />'; ?></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
	}
	
	
	// BANNER - LInk to adzones
	function wp_pro_ads_banners_meta_link_adzones_box($post)
	{
		global $pro_ads_adzones;
		
		wp_nonce_field( 'wp_pro_ads_banners_meta_options_inner_custom_box', 'wp_pro_ads_banners_meta_options_inner_custom_box_nonce' );
		?>
		<div class="tuna_meta">
			<table class="form-table">
				<tbody>
		  			<tr valign="top">
						<th scope="row">
							<?php _e( "Link to Adzone", 'wpproads' ); ?>
							<span class="description"><?php _e('Link your banner to one or more adzones.', 'wpproads'); ?></span>
						</th>
						<td>
							<?php
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
							?>
							<div style="clear:both;"></div>
						</td>
					</tr>
                </tbody>
            </table>
        </div>
        <?php
	}
	
	
	// BANNER - Optional Settings
	function wp_pro_ads_banners_meta_optional_settings_box($post)
	{
		wp_nonce_field( 'wp_pro_ads_banners_meta_options_inner_custom_box', 'wp_pro_ads_banners_meta_options_inner_custom_box_nonce' );
		
		$banner_no_follow       = get_post_meta( $post->ID, 'banner_no_follow', true );
		$banner_contract        = get_post_meta( $post->ID, 'banner_contract', true );
		$banner_duration        = get_post_meta( $post->ID, 'banner_duration', true );
		?>
		<div class="tuna_meta">
			<table class="form-table">
				<tbody>
		  			<tr valign="top">
						<th scope="row">
							<?php _e( "No Follow", 'wpproads' ); ?>
							<span class="description"><?php _e('Do you want to add rel nofollow to your link?', 'wpproads'); ?></span>
						</th>
						<td>
							<select name="banner_no_follow">
                            	<option value="0" <?php echo $banner_no_follow == 0 ? 'selected' : ''; ?>></option>
                            	<option value="1" <?php echo $banner_no_follow == 1 ? 'selected' : ''; ?>><?php _e('rel="nofollow"', 'wpproads'); ?></option>
                          	</select>
							<div style="clear:both;"></div>
						</td>
					</tr>
                    <tr valign="top">
						<th scope="row">
							<?php _e( "Contract", 'wpproads' ); ?>
							<span class="description"><?php _e('Select the contract type and duration for this banner.', 'wpproads'); ?></span>
						</th>
						<td>
                            <select id="banner_contract" name="banner_contract">
                            	<option value="0" <?php echo $banner_contract == 0 ? 'selected' : ''; ?> txt=""></option>
                            	<option value="1" <?php echo $banner_contract == 1 ? 'selected' : ''; ?> txt="<?php _e('Amount of clicks', 'wpproads'); ?>"><?php _e('Pay per click', 'wpproads'); ?></option>
                            	<option value="2" <?php echo $banner_contract == 2 ? 'selected' : ''; ?> txt="<?php _e('Amount of views', 'wpproads'); ?>"><?php _e('Pay per view', 'wpproads'); ?></option>
                                <option value="3" <?php echo $banner_contract == 3 ? 'selected' : ''; ?> txt="<?php _e('Amount of days', 'wpproads'); ?>"><?php _e('Duration', 'wpproads'); ?></option>
                          	</select>
                            
                            <span class="description"><?php _e('Leave empty to keep this banner active.', 'wpproads'); ?></span>
						</td>
					</tr>
                    <tr id="banner_duration_tr" <?php echo !empty($banner_duration) && $banner_contract ? '' : 'style="display:none;"'; ?>>
                    	<th scope="row">
                            <span class="banner_contract_duration"><?php _e('Amount of clicks', 'wpproads'); ?></span>
                            <span class="description"><?php _e('', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<input type="text" name="banner_duration" value="<?php echo !empty($banner_duration) ? $banner_duration : ''; ?>" style="width:50px;">
                            <span class="description"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
	}
	
	
	// BANNER - sidebar
	function wp_pro_ads_banners_meta_side_stats_box($post)
	{
		$banner_clicks          = get_post_meta( $post->ID, 'banner_clicks', true );
		$banner_impressions     = get_post_meta( $post->ID, 'banner_impressions', true );
		$banner_start_date      = get_post_meta( $post->ID, 'banner_start_date', true );
		
		$ctr = !empty($banner_clicks) && !empty($banner_impressions) ? $banner_clicks / $banner_impressions * 100 : 0;
		$round_ctr = round($ctr,2).'%';
		?>
        <div class="stats_header_cont">
        	<div class="stats_header_box" style="width:27%;">
                <div style="font-size:11px;"><?php _e('Total Clicks','wpproads'); ?></div>
                <div style="font-size:16px; font-weight:bold; margin:7px 0;"><?php echo !empty($banner_clicks) ? $banner_clicks : 0; ?></div>
           	</div>
            <div class="stats_header_box" style="width:27%;">
                <div style="font-size:11px;"><?php _e('Total Views','wpproads'); ?></div>
                <div style="font-size:16px; font-weight:bold; margin:7px 0;"><?php echo !empty($banner_impressions) ? $banner_impressions : 0; ?></div>
           	</div>
            <div class="stats_header_box" style="width:27%;">
                <div style="font-size:11px;"><?php _e('CTR','wpproads'); ?></div>
                <div style="font-size:16px; font-weight:bold; margin:7px 0;"><?php echo $round_ctr; ?></div>
           	</div>
            <div class="clearFix"></div>
        </div>
        <?php
	}
	
	
	
	
	
	
	
	function wp_pro_ads_adzones_meta_options_custom_box( $post ) 
	{
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wp_pro_ads_adzones_meta_options_inner_custom_box', 'wp_pro_ads_adzones_meta_options_inner_custom_box_nonce' );
	
		$description        = get_post_meta( $post->ID, 'adzone_description', true );
		$size               = get_post_meta( $post->ID, 'adzone_size', true );
		$custom             = get_post_meta( $post->ID, 'adzone_custom_size', true );
		$responsive         = get_post_meta( $post->ID, 'adzone_responsive', true );
		$adzone_rotation    = get_post_meta( $post->ID, 'adzone_rotation', true );
		$rotation_time      = get_post_meta( $post->ID, 'adzone_rotation_time', true );
		$rotation_effect    = get_post_meta( $post->ID, 'adzone_rotation_effect', true );
		$horizontal         = get_post_meta( $post->ID, 'adzone_grid_horizontal', true );
		$vertical           = get_post_meta( $post->ID, 'adzone_grid_vertical', true );
		$max_banners        = get_post_meta( $post->ID, 'adzone_max_banners', true );
		$adzone_center      = get_post_meta( $post->ID, 'adzone_center', true );
		$adzone_hide_empty  = get_post_meta( $post->ID, 'adzone_hide_empty', true );
		?>
		<div class="tuna_meta">
			<table class="form-table">
				<tbody>
		  			<tr valign="top">
						<th scope="row">
							<?php _e( "Description", 'wpproads' ); ?>
							<span class="description"><?php _e('', 'wpproads'); ?></span>
						</th>
						<td>
                            <textarea name="adzone_description" style="width:100%; height:100px;"><?php echo !empty( $description ) ? $description : ''; ?></textarea>
                            
                            <span class="description"></span>
						</td>
					</tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Size", 'wpproads' ); ?>
                            <span class="description"><?php _e('Select a size for this adzone.', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="adzone_size" id="size_list">
                            	<option value="468x60" <?php echo empty( $size ) || $size == '468x60' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Full Banner', 'wpproads'); ?> (468 x 60)
                                </option>
                                <option value="120x600" <?php echo $size == '120x600' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Skyscraper', 'wpproads'); ?> (120 x 600)
                                </option>
                                <option value="728x90" <?php echo $size == '728x90' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Leaderboard', 'wpproads'); ?> (728 x 90)
                                </option>
                                <option value="300x250" <?php echo $size == '300x250' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Medium Rectangle', 'wpproads'); ?> (300 x 250)
                                </option>
                                <option value="120x90" <?php echo $size == '120x90' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Button 1', 'wpproads'); ?> (120 x 90)
                                </option>
                                <option value="160x600" <?php echo $size == '160x600' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Wide Skyscraper', 'wpproads'); ?> (160 x 600)
                                </option>
                                <option value="120x60" <?php echo $size == '120x60' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Button 2', 'wpproads'); ?> (120 x 60)
                                </option>
                                <option value="125x125" <?php echo $size == '125x125' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Square Button', 'wpproads'); ?> (125 x 125)
                                </option>
                                <option value="180x150" <?php echo $size == '180x150' ? 'selected="selected"' : ''; ?>>
                                    IAB <?php _e('Rectangle', 'wpproads'); ?> (180 x 150)
                                </option>
                                <option value="custom" <?php echo !empty($custom) ? 'selected="selected"' : ''; ?>>
                                    <?php _e('Custom', 'wpproads'); ?>
                                </option>
                                <option value="responsive" <?php echo !empty($responsive) ? 'selected="selected"' : ''; ?>>
                                    <?php _e('Responsive', 'wpproads'); ?>
                                </option>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr id="custom_size" <?php echo !empty($custom) ? '' : 'style="display:none;"'; ?>>
                    	<th scope="row">
                            <?php _e('Custom size', 'wpproads'); ?>
                            <span class="description"><?php _e('', 'wpproads'); ?></span>
                        </th>
                        <td>
                            
							<?php
                            if( !empty($custom) )
                            {
                                $sz = explode('x', $size);	
                            }
                            ?>
                            <div style="float:left; width:100px;">
                                <div><small><?php _e('Width', 'wpproads'); ?>:</small></div>
                                <div><input type="text" name="custom_w" value="<?php echo !empty($sz[0]) ? $sz[0] : ''; ?>" style="width:50px;"><small>Px.</small></div>
                            </div>
                        	
                            <div style="float:left;">
                                <div><small><?php _e('Height', 'wpproads'); ?>:</small></div>
                                <div><input type="text" name="custom_h" value="<?php echo !empty($sz[1]) ? $sz[1] : ''; ?>" style="width:50px;"><small>Px.</small></div>
                            </div>
                            <div class="clearFix"></div>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Max. amount of banners", 'wpproads' ); ?>
                            <span class="description"><?php _e('How many banners are allowd in this adzone?', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<input type="text" name="adzone_max_banners" value="<?php echo !empty($max_banners) ? $max_banners : ''; ?>" style="width:50px;">
                            <span class="description"><?php _e('Leave empty to allow unlimited banners.', 'wpproads'); ?></span>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Rotate Banners", 'wpproads' ); ?>
                            <span class="description"><?php _e('', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="adzone_rotation">
                            	<option value="0" <?php echo $adzone_rotation == 0 ? 'selected' : ''; ?>><?php _e('No', 'wpproads'); ?></option>
                            	<option value="1" <?php echo $adzone_rotation == 1 ? 'selected' : ''; ?>><?php _e('Yes', 'wpproads'); ?></option>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Rotation Time", 'wpproads' ); ?>
                            <span class="description"><?php _e('', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<input type="text" name="adzone_rotation_time" value="<?php echo !empty($rotation_time) ? $rotation_time : ''; ?>" style="width:50px;">
                            <small><?php _e('Sec.', 'wpproads'); ?></small>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Rotation Effect", 'wpproads' ); ?>
                            <span class="description"><?php _e('', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="adzone_rotation_effect">
                            	<option value="fade" <?php echo $rotation_effect == 'fade' ? 'selected' : ''; ?>><?php _e('Fade', 'wpproads'); ?></option>
                            	<option value="slideLeft" <?php echo $rotation_effect == 'slideLeft' ? 'selected' : ''; ?>><?php _e('Slide', 'wpproads'); ?></option>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e('AD Grid', 'wpproads'); ?>
                            <span class="description"><?php _e('Show multiple ads at once. <br><strong>note:</strong> This option has no rotation effect. Banners will load in random order on each page refresh.', 'wpproads'); ?></span>
                        </th>
                        <td>
                            <div style="float:left; width:100px;">
                                <div><small><?php _e('Horizontal', 'wpproads'); ?>:</small></div>
                                <div><input type="text" name="adzone_grid_horizontal" value="<?php echo !empty($horizontal) ? $horizontal : ''; ?>" style="width:50px;"></div>
                            </div>
                        	
                            <div style="float:left;">
                                <div><small><?php _e('Vertical', 'wpproads'); ?>:</small></div>
                                <div><input type="text" name="adzone_grid_vertical" value="<?php echo !empty($vertical) ? $vertical : ''; ?>" style="width:50px;"></div>
                            </div>
                            <div class="clearFix"></div>
                            <span class="description"><?php _e('Leave empty to show one banner at the time.', 'wpproads'); ?></span>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Center Adzone", 'wpproads' ); ?>
                            <span class="description"><?php _e('Do you want this adzone to be centered?', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="adzone_center">
                            	<option value="0" <?php echo empty($adzone_center) ? 'selected' : ''; ?>><?php _e('No', 'wpproads'); ?></option>
                            	<option value="1" <?php echo $adzone_center ? 'selected' : ''; ?>><?php _e('Yes', 'wpproads'); ?></option>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                            <?php _e( "Hide adzone if empty", 'wpproads' ); ?>
                            <span class="description"><?php _e('Do you want to hide this adzone if its empty?', 'wpproads'); ?></span>
                        </th>
                        <td>
                        	<select name="adzone_hide_empty">
                            	<option value="0" <?php echo empty($adzone_hide_empty) ? 'selected' : ''; ?>><?php _e('No', 'wpproads'); ?></option>
                            	<option value="1" <?php echo $adzone_hide_empty ? 'selected' : ''; ?>><?php _e('Yes', 'wpproads'); ?></option>
                          	</select>
                            <span class="description"></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
	}
	
	// ADZONE - popup options
	/*
	function wp_pro_ads_adzones_meta_popup_box( $post )
	{
		wp_nonce_field( 'wp_pro_ads_adzones_meta_options_inner_custom_box', 'wp_pro_ads_adzones_meta_options_inner_custom_box_nonce' );
		
		$adzone_is_popup = get_post_meta( $post->ID, 'adzone_is_popup', true );
		?>
        <div class="tuna_meta">
			<table class="form-table">
				<tbody>
		  			<tr valign="top">
						<th scope="row">
							<?php _e( "Open Adzone as a Popup", 'wpproads' ); ?>
							<span class="description"><?php _e('This option allows you to open the adzone as a popup window.', 'wpproads'); ?></span>
						</th>
						<td>
                            <select name="adzone_is_popup">
                            	<option value="0" <?php echo empty($adzone_is_popup) ? 'selected' : ''; ?>><?php _e('No', 'wpproads'); ?></option>
                            	<option value="1" <?php echo $adzone_is_popup ? 'selected' : ''; ?>><?php _e('Yes', 'wpproads'); ?></option>
                          	</select>
                            
                            <span class="description"></span>
						</td>
					</tr>
                </tbody>
            </table>
        </div>
        <?php
	}
	*/
	
	function wp_pro_ads_adzones_meta_options_save_postdata( $post_id ) 
	{
		// Check if our nonce is set.
		if ( ! isset( $_POST['wp_pro_ads_adzones_meta_options_inner_custom_box_nonce'] ) )
		return $post_id;
		$nonce = $_POST['wp_pro_ads_adzones_meta_options_inner_custom_box_nonce'];
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'wp_pro_ads_adzones_meta_options_inner_custom_box' ) )
		  return $post_id;
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		  return $post_id;
		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;
		} else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
		}
		/* OK, its safe for us to save the data now. */
		
		
		if( $_POST['adzone_size'] == 'custom' )
		{
			$size = $_POST['custom_w'].'x'.$_POST['custom_h'];
		}
		elseif( $_POST['adzone_size'] == 'responsive' )
		{
			$size = '';
		}
		else
		{
			$size = $_POST['adzone_size'];
		}
		$custom = $_POST['adzone_size'] == 'custom' ? 1 : 0;
		$responsive = $_POST['adzone_size'] == 'responsive' ? 1 : 0;
			
		// Sanitize user input.
		$description        = sanitize_text_field( $_POST['adzone_description'] );
		$rotation           = sanitize_text_field( $_POST['adzone_rotation'] );
		$rotation_time      = sanitize_text_field( $_POST['adzone_rotation_time'] );
		$rotation_effect    = sanitize_text_field( $_POST['adzone_rotation_effect'] );
		$horizontal         = sanitize_text_field( $_POST['adzone_grid_horizontal'] );
		$vertical           = sanitize_text_field( $_POST['adzone_grid_vertical'] );
		$max_banners        = sanitize_text_field( $_POST['adzone_max_banners'] );
		$adzone_center      = sanitize_text_field( $_POST['adzone_center'] );
		$adzone_hide_empty  = sanitize_text_field( $_POST['adzone_hide_empty'] );
		//$adzone_is_popup    = sanitize_text_field( $_POST['adzone_is_popup'] );
		
		// Update the meta field in the database.
		update_post_meta( $post_id, 'adzone_description', $description );
		update_post_meta( $post_id, 'adzone_size', $size );
		update_post_meta( $post_id, 'adzone_custom_size', $custom );
		update_post_meta( $post_id, 'adzone_responsive', $responsive );
		update_post_meta( $post_id, 'adzone_rotation', $rotation );
		update_post_meta( $post_id, 'adzone_rotation_time', $rotation_time );
		update_post_meta( $post_id, 'adzone_rotation_effect', $rotation_effect );
		update_post_meta( $post_id, 'adzone_grid_horizontal', $horizontal );
		update_post_meta( $post_id, 'adzone_grid_vertical', $vertical );
		update_post_meta( $post_id, 'adzone_max_banners', $max_banners );
		update_post_meta( $post_id, 'adzone_center', $adzone_center );
		update_post_meta( $post_id, 'adzone_hide_empty', $adzone_hide_empty );
		//update_post_meta( $post_id, 'adzone_is_popup', $adzone_is_popup );
	}
   
}
?>