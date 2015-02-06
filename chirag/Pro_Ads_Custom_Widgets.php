<?php
/* ----------------------------------------------------------------
 * Create adzone widgets
 * ---------------------------------------------------------------- */
class Pro_Ads_Custom_Widgets extends WP_Widget 
{

	function Pro_Ads_Custom_Widgets() {
		$widget_ops = array('classname' => 'pro_ad_adzone', 'description' => __( 'Display your ads.','wpproads') );
		$this->WP_Widget('Pro_Ads_Custom_Widgets', '<img src="'.WP_ADS_URL.'images/banner_icon_20.png">'.__('Display your ads.','wpproads'), $widget_ops);
	}
	
	function widget($args,$instance) 
	{
		global $pro_ads_adzones;
		extract($args);
		
		if( !empty( $instance['adzone_id'] ))
		{
			$grid_horizontal = get_post_meta( $instance['adzone_id'], 'adzone_grid_horizontal', true );
			$grid_vertical   = get_post_meta( $instance['adzone_id'], 'adzone_grid_vertical', true );
		
			echo $before_widget;
			if( !empty($grid_horizontal) && !empty($grid_vertical) )
			{
				echo $pro_ads_adzones->display_adzone_grid( $instance['adzone_id'] );
			}
			else
			{
				echo $pro_ads_adzones->display_adzone( $instance['adzone_id'] );
			}
			echo $after_widget;
		}
	}
	
	
	function update($new_instance,$old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['adzone_id'] = $new_instance['adzone_id'];

		return $instance;
	}

	function form($instance) {
		
		global $pro_ads_adzones;
		
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'adzone_id' => ''));
		?>
        <p>
        	<label for="<?php echo $this->get_field_id('adzone_id'); ?>"><?php _e('Select your adzone:'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'adzone_id' ); ?>" name="<?php echo $this->get_field_name( 'adzone_id' ); ?>" width="20%">
            
            	<?php
				$adzones = $pro_ads_adzones->get_adzones();
				foreach( $adzones as $i => $adzone )
				{
					?>
					<option value="<?php echo $adzone->ID; ?>" <?php if ( $adzone->ID == $instance['adzone_id'] ) echo 'selected="selected"'; ?>>
						<?php echo get_the_title($adzone->ID); ?>
                    </option>
                    <?php
				}
				?>
				
			</select>
		</p><?php
	}
}
?>