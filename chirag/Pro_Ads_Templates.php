<?php
class Pro_Ads_Templates {	

	public function __construct() 
	{
		
	}


	/*
	 * Create Adzone Code Popup screen
	 *
	 * NOTE: <p> tags in the HTML output are needed to show the content!!
	 *
	 * @access public
	 * @param int $id, int $i
	 * @return html
	*/
	public function pro_ad_adzone_popup_screen( $id )
	{
		global $pro_ads_adzones;

		/* ----------------------------------------------------------------
		 * Create AdZones Export Codes
		 * ---------------------------------------------------------------- */
		if( isset( $id ) )
		{
			$size = get_post_meta( $id, 'adzone_size', true );
			$custom = get_post_meta( $id, 'adzone_custom_size', true );
			$responsive = get_post_meta( $id, 'adzone_responsive', true );
			$adzone_is_popup = get_post_meta( $id, 'adzone_is_popup', true );
			
			$adzone_shortcode = $adzone_is_popup ? '[pro_ad_display_adzone id="'.$id.'" popup="1"]' : '[pro_ad_display_adzone id="'.$id.'"]';
			?>
			<div id="pro_ads_adzone_popup_<?php echo $id; ?>" style="display:none;">
				<p style="padding:0; margin:0;"></p>
                
                <div class="tuna_meta">
					<table class="form-table">
                    	<tbody>
                        
                        	<tr valign="top">
                            	<th scope="row">
									<?php _e('Post tag [shortcode]', 'wpproads'); ?>
                                    <span class="description"><?php _e('If you want to show this ad zone into a single post/page you can use this <em>Post Tag</em>. Just copy the shortcode into your post\'s textfield', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<textarea id="sc_<?php echo $id; ?>" class="input" style="width:100%; height:50px; font-size:10px;"><?php echo $adzone_shortcode; ?></textarea>
                                    <!--<span class="description"><?php _e('<strong>Shortcode attributes:</strong> id = int (the adzone id), popup = boolean (open the adzone as a popup)', 'wpproads'); ?></span>-->
                                    <a class="wpproads_button open_sc_editor_<?php echo $id; ?>" form_id="<?php echo $id; ?>" ><?php _e('Open Shortcode Editor','wpproads'); ?></a>
                                </td>
                            </tr>
                            <tr id="sc_editor_<?php echo $id; ?>" style="display:none;">
                            	<td colspan="2">
                                	<?php 
									$this->pro_ads_shortcode_creator($id, $id); ?>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                            	<th scope="row">
									<?php _e('Template tag', 'wpproads'); ?>
                                    <span class="description"><?php _e('If you want to use this ad zone on a fixed place inside your website, you can use this <em>Template tag</em>. Just copy the function into your website template, there where you want to show the banners.', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<textarea class="input" style="width:100%; height:50px; font-size:10px;"><?php echo htmlentities( '<?php echo do_shortcode("[pro_ad_display_adzone id='.$id.']"); ?>' ); ?></textarea>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                            	<th scope="row">
									<?php _e('Iframe tag', 'wpproads'); ?>
                                    <span class="description"><?php _e('The Iframe tag allows you to export adzones to other websites. This way you can manage adzones on 1 site but have them displayed on multiple websites at the same time.', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<?php 
									if( !$responsive )
									{
										$sz = explode('x', $size);
									}
									else
									{
										$sz[0] = '100%';
										$sz[1] = 'auto';
									}
									?>
									<textarea class="input" style="width:100%; height:50px; font-size:10px;"><?php echo htmlentities( '<iframe id="wp_pro_ad_system_ad_zone" frameborder="0" src="'.get_bloginfo('url').'/?wpproadszoneid='.$id.'" width="'.$sz[0].'" height="'.$sz[1].'" scrolling="no"></iframe>' ); ?></textarea>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
                
			</div>
            <script type="text/javascript">
			jQuery(document).ready(function($){
				$('.open_sc_editor_<?php echo $id; ?>').on('click', function(){
					$('#sc_editor_'+ $(this).attr('form_id')).slideToggle();
				});
			});
			</script>
			<?php
		}
		else
		{
			echo _e('Woops! we cannot find the adzone your looking for!', 'wpproads');	
		}		
	}
	
	
	
	
	
	
	
	/*
	 * Create Adzone Order Popup screen
	 *
	 * NOTE: <p> tags in the HTML output are needed to show the content!!
	 *
	 * @access public
	 * @param int $id, int $i
	 * @return html
	*/
	public function pro_ad_adzone_order_popup_screen( $id )
	{
		global $pro_ads_adzones, $pro_ads_banners;
		
		if( isset( $id ) )
		{
			$linked_banner_ids = get_post_meta( $id, 'linked_banners', true );
			$banners = !empty($linked_banner_ids) ? $pro_ads_banners->get_banners( array('post__in' => $linked_banner_ids, 'orderby'=>'post__in') ) : '';
			?>
            <div id="pro_ads_adzone_order_popup_<?php echo $id; ?>" style="display:none;">
				<p>
                	<?php _e('Drag the banners to change the order of appearance.', 'wpproads'); ?>
                </p>
                
                <ul class="order_banners order_banners_<?php echo $id; ?>" id="adzone_order_sortable" aid="<?php echo $id; ?>">
                	<li class="loading"><?php _e('Updating', 'wpproads'); ?></li>
                    <?php
					if( !empty($banners) )
					{ 
						foreach( $banners as $i => $banner )
						{
							$preview = $pro_ads_banners->get_banner_preview( $banner->ID );
							$name = !empty( $banner->post_title ) ? $banner->post_title : $banner->post_name;
							?>
							<li id="order-item-<?php echo $i; ?>" bid="<?php echo $banner->ID; ?>">
								<div class="btn"><?php _e('Drag', 'wpproads'); ?></div>
								<div class="preview info_item"><?php echo $preview; ?></div>
								<div class="info_item">
									<div class="banner_name"><?php echo $name; ?></div>
									<div class="banner_info"><small>ID: <?php echo $banner->ID; ?></small></div>
								</div>
								<div class="clearFix"></div>
							</li>
							<?php
						}
					}
					else
					{
						echo '<li>'.__('No linked banners found.', 'wpproads').'</li>';
					}
                    ?>
                </ul>
                
            </div>
            <?php
		}
		else
		{
			echo _e('Woops! we cannot find the adzone your looking for!', 'wpproads');	
		}
		
	}
	
	
	
	
	/*
	 * Create Stats user info Popup screen
	 *
	 * NOTE: <p> tags in the HTML output are needed to show the content!!
	 *
	 * @access public
	 * @param int $id, int $i
	 * @return html
	*/
	public function stats_user_info_popup_screen( $item )
	{
		if( !empty( $item->id ) )
		{
			?>
            <div id="stats_user_info_popup_<?php echo $item->id; ?>" style="display:none;">
				<p></p>
                <div class="tuna_meta">
					<table class="form-table">
                    	<tbody>
                        
                        	<tr valign="top">
                            	<th scope="row">
									<?php _e('Browser','wpproads'); ?>:
                                    <span class="description"><?php _e('', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<?php echo !empty($item->browser) ? $item->browser.' <img src="'.WP_ADS_URL.'images/browser/'.$item->browser.'.png" />' : __('n/a','wpproads'); ?>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            <tr valign="top">
                            	<th scope="row">
									<?php _e('Platform','wpproads'); ?>:
                                    <span class="description"><?php _e('', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<?php echo !empty($item->platform) ? $item->platform.' <img src="'.WP_ADS_URL.'images/platform/'.$item->platform.'.png" />' : __('n/a','wpproads'); ?>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            
                            
                            <tr valign="top">
                            	<th scope="row">
									<?php _e('Country','wpproads'); ?>:
                                    <span class="description"><?php _e('', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<?php echo !empty($item->country) ? $item->country.' ('.$item->country_cd.')' : __('n/a','wpproads'); ?>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            <tr valign="top">
                            	<th scope="row">
									<?php _e('City','wpproads'); ?>:
                                    <span class="description"><?php _e('', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<?php echo !empty($item->city) ? $item->city : __('n/a','wpproads'); ?>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            <tr valign="top">
                            	<th scope="row">
									<?php _e('IP adress','wpproads'); ?>:
                                    <span class="description"><?php _e('', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<?php echo !empty($item->ip_address) ? $item->ip_address : __('n/a','wpproads'); ?>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            <tr valign="top">
                            	<th scope="row">
									<?php _e('Adzone','wpproads'); ?>:
                                    <span class="description"><?php _e('', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<?php echo !empty($item->adzone_id) ? get_the_title($item->adzone_id).' ('.$item->adzone_id.')' : __('n/a','wpproads'); ?>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            <tr valign="top">
                            	<th scope="row">
									<?php _e('Campaign','wpproads'); ?>:
                                    <span class="description"><?php _e('', 'wpproads'); ?></span>
                                </th>
                            	<td>
                                	<?php echo !empty($item->campaign_id) ? get_the_title($item->campaign_id).' ('.$item->campaign_id.')' : __('n/a','wpproads'); ?>
                                    <span class="description"></span>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
                
            </div>
            <?php	
		}
		else
		{
			echo _e('Woops! we cannot find the adzone your looking for!', 'wpproads');	
		}
	}
	
	
	
	
	
	
	
	
	
	
	/*
	 * Tiny Mce Editor Wpproads shortcode editor.
	 *
	 * @access public
	 * @return html
	*/
	public function get_shortcode_editor_form() 
	{
		global $pro_ads_adzones;
		
		$adzones = $pro_ads_adzones->get_adzones();
		?>
        <link rel="stylesheet" id="proad-admin_style-css"  href="<?php echo WP_ADS_TPL_URL; ?>/css/admin.css" type="text/css" media="all" />
        <link rel="stylesheet" id="tuna_admin_style-css"  href="<?php echo WP_ADS_TPL_URL; ?>/css/tuna-admin.css" type="text/css" media="all" />
        
		<div class="wrap theme_settings" id="wpproads-shortcode-editor-form">
        
            <div id="icon-themes" class="icon32 wpproads_shortcode_editor"><br /></div>
			<h2>WP Pro Advertising System - <?php _e('Shortcode Generator', 'wpproads'); ?></h2>
            <p>
            	<?php _e('Select the adzone you want to use. To add the shortcode click <em>Insert Shortdode</em>.', 'wpproads'); ?>
            </p>
                
            <div class="tuna_meta tuna_theme_options metabox-holder">
            
            
            	<!-- ADZONE -->
                <div class="adpostbox"><!-- closed -->
                    <div class="handlediv" title="<?php _e('Click to change', 'wpproads'); ?>">-</div>
                    <h3 class="hndle"><span><?php _e( "Adzones", 'wpproads'); ?></span></h3>
                    <div class="inside">
                    <table class="form-table">
                            <tbody>
                            	<tr valign="top">
                                    <th scope="row">
                                        <?php _e( "Select an adzone", 'wpproads'); ?>
                                        <span class="description"><?php _e('','wpproads'); ?></span>
                                    </th>
                                    <td>
                                        <select id="adzone_id">
                                        	<option value=""><?php _e( "Select an adzone", 'wpproads'); ?></option>
                                            <?php
											foreach( $adzones as $adzone )
											{
												?>
                                            	<option value="<?php echo $adzone->ID; ?>"><?php echo get_the_title($adzone->ID).' ('.$adzone->ID.')'; ?></option>
                                                <?php
											}
											?>
                                        </select>
                                        <span class="description"><?php _e('','wpproads'); ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!--<p><input type="submit" id="adzone_submit" value="<?php _e('Insert Shortcode', 'wpproads'); ?>" class="button-primary" /></p>-->
                    </div>
                </div>
                <!-- end .postbox - Buttons --> 
                
                <?php
				$this->adzone_popup_options( $adzone->ID ); 
				$this->adzone_background_ads_options( $adzone->ID );
				/*
                <!-- ADZONE POPUP -->
                <div class="adpostbox closed"><!--  -->
                    <div class="handlediv" title="<?php _e('Click to change', 'wpproads'); ?>">+</div>
                    <h3 class="hndle"><span><?php _e( "Popup options", 'wpproads'); ?></span></h3>
                    <div class="inside">
                    <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row">
                                        <?php _e( "Open Adzone as a Popup", 'wpproads'); ?>
                                        <span class="description"><?php _e('Do you want the adzone to open as a popup?','wpproads'); ?></span>
                                    </th>
                                    <td>
                                        <select id="adzone_is_popup">
                                        	<option value="0"><?php _e( "No", 'wpproads'); ?></option>
                                            <option value="1"><?php _e( "Yes", 'wpproads'); ?></option>
                                               
                                        </select>
                                        <span class="description"><?php _e('','wpproads'); ?></span>
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>
                        
                    </div>
                </div>
                <!-- end .postbox - popup -->
				*/
				?> 
                
                <p><input type="submit" id="adzone_submit" value="<?php _e('Insert Shortcode', 'wpproads'); ?>" class="button-primary" /></p>
                
                
            </div>
        </div>
        
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('.my-color-field').wpColorPicker();
                
                //postboxes.add_postbox_toggles('wpproads-shortcode-editor-form');
				
					jQuery('.adpostbox h3').click( function() {
						jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
						if( jQuery(jQuery(this).parent().get(0)).hasClass('closed') ){
							jQuery(this).prev('.handlediv').html('+');
						}else{
							jQuery(this).prev('.handlediv').html('-');
						}
					});
				
            });
        </script>
    <?php
	}
	
	
	
	
	
	/*
	 * Shortcode creator for NON Tiny Mce use.
	 *
	 * @access public
	 * @return html
	*/
	public function pro_ads_shortcode_creator( $adzone_id = 0, $form_id = 0 ) 
	{
		global $pro_ads_adzones;
		
		// $form_id = !$adzone_id ? rand() : $adzone_id;
		$adzones = !$adzone_id ? $pro_ads_adzones->get_adzones() : '';
		?>
        <link rel="stylesheet" id="tuna_admin_style-css"  href="<?php echo WP_ADS_TPL_URL; ?>/css/admin.css" type="text/css" media="all" />
        <link rel="stylesheet" id="admin_standard_style-css"  href="<?php echo WP_ADS_TPL_URL; ?>/css/admin_standard.css" type="text/css" media="all" />
        
		<div class="wrap theme_settings" id="wpproads-shortcode-editor-form" style="border: solid 1px #C0C3C5; background:#EEE; padding:10px;">
        
            <div id="icon-themes" class="icon32 wpproads_shortcode_editor"><br /></div>
			<h2><?php _e('Adzone Shortcode Editor', 'wpproads'); ?></h2>
            <p>
            	<?php _e('', 'wpproads'); ?>
            </p>
                
            <div id="<?php echo $form_id; ?>" class="tuna_meta tuna_theme_options metabox-holder">
            
            
            	<!-- ADZONE -->
                <div class="adpostbox adpostbox_<?php echo $form_id; ?> <?php echo $adzone_id ? 'closed' : ''; ?>"><!-- closed -->
                    <div class="handlediv" title="<?php _e('Click to change', 'wpproads'); ?>"><?php echo $adzone_id ? '' : '-'; ?></div>
                    <?php echo $adzone_id ? '<h4 class="hndle"><span>'. sprintf(__( "Selected Adzone: %s", 'wpproads'), '<strong>'.get_the_title($adzone_id).'</strong> <em>(ID:'.$adzone_id.')</em>').'</span></h4>' : '<h3 class="hndle"><span>'. __( "Adzones", 'wpproads').'</span></h3>'; ?>
                    <div class="inside">
                    <table class="form-table">
                            <tbody>
                            	<tr valign="top">
                                    <th scope="row">
                                        <?php echo $adzone_id ? __( "Selected adzone", 'wpproads') : __( "Select an adzone", 'wpproads'); ?>
                                        <span class="description"><?php _e('','wpproads'); ?></span>
                                    </th>
                                    <td>
                                    	<?php
										if( !empty($adzones))
										{
											?>
                                            <select class="adzone_id">
                                                <option value=""><?php _e( "Select an adzone", 'wpproads'); ?></option>
                                                <?php
                                                    foreach( $adzones as $adzone )
                                                    {
                                                        ?>
                                                        <option value="<?php echo $adzone->ID; ?>"><?php echo get_the_title($adzone->ID).' ('.$adzone->ID.')'; ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                            </select>
                                        	<?php
										}
										else
										{
											?>
                                            <input type="text" class="adzone_id" name="adzone_id" readonly="readonly" value="<?php echo $adzone_id; ?>">
                                            <?php	
										}
										?>
                                        <span class="description"><?php _e('','wpproads'); ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!--<p><input type="submit" id="adzone_submit" value="<?php _e('Insert Shortcode', 'wpproads'); ?>" class="button-primary" /></p>-->
                    </div>
                </div>
                <!-- end .postbox - Buttons --> 
                
                <?php 
				$this->adzone_popup_options( $form_id ); 
				$this->adzone_background_ads_options( $form_id );
				?> 
                
                <p>
                	<input type="submit" id="create_shortcode" value="<?php _e('Create Shortcode', 'wpproads'); ?>" class="button-primary" />
                    <input type="button" id="close_shortcode_editor" value="<?php _e('Close', 'wpproads'); ?>" class="button-secondary" />
                </p>
                
                
            </div>
        </div>
        
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('.my-color-field').wpColorPicker();
                
				$('#<?php echo $form_id; ?> #create_shortcode').on('click', function(){
					
					var is_popup = $('#<?php echo $form_id; ?> #adzone_is_popup').val();
					var shortcode = '[pro_ad_display_adzone';
					
					if( $('#adzone_id').val() != '' ){
						shortcode += ' id="' + $('#<?php echo $form_id; ?> .adzone_id').val() + '"';
					}
					// Popup
					if( $('#<?php echo $form_id; ?> #adzone_is_popup').val() == 1 ){
						shortcode += ' popup="1"';
					}
					if( $('#<?php echo $form_id; ?> .adzone_popup_bg_color').val() != '' ){
						shortcode += ' popup_bg="'+ $('#<?php echo $form_id; ?> .adzone_popup_bg_color').val() +'"';
					}
					if( $('#<?php echo $form_id; ?> .adzone_popup_opacity').val() != '' ){
						shortcode += ' popup_opacity="'+ $('#<?php echo $form_id; ?> .adzone_popup_opacity').val() +'"';
					}
					// Background
					if( $('#<?php echo $form_id; ?> #adzone_is_background').val() == 1 ){
						shortcode += ' background="1"';
					}
					if( $('#<?php echo $form_id; ?> .adzone_background_container').val() != '' ){
						shortcode += ' container="'+ $('#<?php echo $form_id; ?> .adzone_background_container').val() +'"';
					}
					if( $('#<?php echo $form_id; ?> .adzone_background_repeat').val() != '' ){
						shortcode += ' repeat="'+ $('#<?php echo $form_id; ?> .adzone_background_repeat').val() +'"';
					}
					if( $('#<?php echo $form_id; ?> .adzone_background_stretch').val() != '' ){
						shortcode += ' stretch="'+ $('#<?php echo $form_id; ?> .adzone_background_stretch').val() +'"';
					}
					if( $('#<?php echo $form_id; ?> .adzone_background_bg_color').val() != '' ){
						shortcode += ' bg_color="'+ $('#<?php echo $form_id; ?> .adzone_background_bg_color').val() +'"';
					}
					
					shortcode += ']';
					
					$('#sc_<?php echo $form_id; ?>').val(shortcode);
					
					$('#sc_editor_<?php echo $form_id; ?>').hide();
				});
				
				$('#<?php echo $form_id; ?> #close_shortcode_editor').on('click', function(){
					$('#sc_editor_<?php echo $form_id; ?>').hide();
				});
                
				
				jQuery('.adpostbox_<?php echo $form_id; ?> h3').on('click', function() {
					
					jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
					if( jQuery(jQuery(this).parent().get(0)).hasClass('closed') ){
						jQuery(this).prev('.handlediv').html('+');
					}else{
						//jQuery(this).prepend('<a class="togbox">-</a> ');
						jQuery(this).prev('.handlediv').html('-');
					}
				});
				
            });
        </script>
    <?php
	}
	
	
	
	
	
	
	
	public function adzone_popup_options( $form_id )
	{
		?>
        <!-- ADZONE POPUP -->
        <div class="adpostbox adpostbox_<?php echo $form_id; ?> closed"><!--  -->
            <div class="handlediv" title="<?php _e('Click to change', 'wpproads'); ?>">+</div>
            <h3 class="hndle"><span><?php _e( "Popup options", 'wpproads'); ?></span></h3>
            <div class="inside">
            <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Open Adzone as a Popup", 'wpproads'); ?>
                                <span class="description"><?php _e('Do you want the adzone to open as a popup?','wpproads'); ?></span>
                            </th>
                            <td>
                                <select id="adzone_is_popup">
                                    <option value="0"><?php _e( "No", 'wpproads'); ?></option>
                                    <option value="1"><?php _e( "Yes", 'wpproads'); ?></option>   
                                </select>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Popup Background Color", 'wpproads'); ?>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </th>
                            <td>
                                <input type="text" value="" class="my-color-field adzone_popup_bg_color" />
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Popup Opacity", 'wpproads'); ?>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </th>
                            <td>
                                <select class="adzone_popup_opacity">
                                    <option value=""></option>
                                    <?php
                                    for($i = 0; $i < 10; $i++)
                                    {
                                        ?>
                                        <option value="<?php echo !$i ? '0' : '0.'.$i; ?>"><?php echo !$i ? '0' : '0.'.$i; ?></option> 
                                        <?php
                                    }
                                    ?>
                                    <option value="1">1</option> 
                                </select>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
                
            </div>
        </div>
        <!-- end .postbox - popup -->
        <?php	
	}
	
	
	
	
	
	
	
	public function adzone_background_ads_options( $form_id )
	{
		?>
        <!-- ADZONE BACKGROUND ADS -->
        <div class="adpostbox adpostbox_<?php echo $form_id; ?> closed"><!--  -->
            <div class="handlediv" title="<?php _e('Click to change', 'wpproads'); ?>">+</div>
            <h3 class="hndle"><span><?php _e( "Background ad options", 'wpproads'); ?></span></h3>
            <div class="inside">
            <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Load Adzone as Background", 'wpproads'); ?>
                                <span class="description"><?php _e('Do you want the adzone to load as the page background?','wpproads'); ?></span>
                            </th>
                            <td>
                                <select id="adzone_is_background">
                                    <option value="0"><?php _e( "No", 'wpproads'); ?></option>
                                    <option value="1"><?php _e( "Yes", 'wpproads'); ?></option>   
                                </select>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Background Container", 'wpproads'); ?>
                                <span class="description"><?php _e('Select the main website container. Default <strong>body</strong>','wpproads'); ?></span>
                            </th>
                            <td>
                                <input type="text" value="" placeholder="body" class="adzone_background_container" />
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Background Color", 'wpproads'); ?>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </th>
                            <td>
                                <input type="text" value="" class="my-color-field adzone_background_bg_color" />
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Repeat", 'wpproads'); ?>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </th>
                            <td>
                                <select class="adzone_background_repeat">
                                    <option value=""><?php _e( "No repeat", 'wpproads'); ?></option>
                                    <option value="1"><?php _e( "Repeat", 'wpproads'); ?></option>
                                </select>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <?php _e( "Stretch", 'wpproads'); ?>
                                <span class="description"><?php _e('','wpproads'); ?></span>
                            </th>
                            <td>
                                <select class="adzone_background_stretch">
                                    <option value=""><?php _e( "No stretch", 'wpproads'); ?></option>
                                    <option value="1"><?php _e( "Stretch", 'wpproads'); ?></option>
                                </select>
                                <span class="description"><?php _e('Stretch the background image to the full width of the page.','wpproads'); ?></span>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
                
            </div>
        </div>
        <!-- end .postbox - background ads -->
        <?php	
	}
	
	
	
	
	
	
	
	public function pro_ad_popup_screen( $arr = array() )
	{
		//$adzone_is_popup    = get_post_meta( $arr['adzone_id'], 'adzone_is_popup', true );
		$popup_bg = !empty($arr['popup_bg']) ? ' background-color:'.$arr['popup_bg'].'; ' : '';
		$popup_opacity = !empty($arr['popup_opacity']) ? ' opacity:'.$arr['popup_opacity'].'; ' : '';
		
		$html = '';
	
		$html.= '<div id="backgroundPasPopup" style="'.$popup_bg.$popup_opacity.'"></div>';
        $html.= '<div class="PasPopupCont" style="display:none;">';
        	$html.= '<div class="paspopup_content">';
                $html.= '<div class="close_paspopup"><span>x</span></div>';
                //$html.= do_shortcode("[pro_ad_display_adzone id=147]");
				$html.= $arr['html'];
            $html.= '</div>';
        $html.= '</div>';
		$html.= '<script>jQuery(document).ready(function($){loadPASPopup( 0, "success", "'.admin_url('admin-ajax.php').'" );});</script>';
        
		return $html;
	}
	
}
?>