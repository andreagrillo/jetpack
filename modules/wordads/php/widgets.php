<?php

/**
 * Widget for inserting an ad into your sidebar
 *
 * @since 4.5.0
 */
class WordAds_Sidebar_Widget extends WP_Widget {

	private static $allowed_tags = array( 'mrec', 'wideskyscraper' );

	function __construct() {
		parent::__construct(
			'wordads_sidebar_widget',
			/** This filter is documented in modules/widgets/facebook-likebox.php */
			apply_filters( 'jetpack_widget_name', 'Ads' ),
			array(
				'description' => __( 'Insert a WordAd wherever you can place a widget.', 'jetpack' ),
				'customize_selective_refresh' => true
			)
		);
	}

	public function widget( $args, $instance ) {
		global $wordads;
		if ( $wordads->should_bail() ) {
			return false;
		}

		$about = __( 'About these ads', 'jetpack' );
		$width = WordAds::$ad_tag_ids[$instance['unit']]['width'];
		$height = WordAds::$ad_tag_ids[$instance['unit']]['height'];
		$snippet = '';
		if ( $wordads->option( 'wordads_house', true ) ) {
			$ad_url = 'https://s0.wp.com/wp-content/blog-plugins/wordads/house/';
			if ( 'leaderboard' == $instance['unit'] && ! $this->params->mobile_device ) {
				$ad_url .= 'leaderboard.png';
			} else if ( 'wideskyscraper' == $instance['unit'] ) {
				$ad_url .= 'widesky.png';
			} else {
				$ad_url .= 'mrec.png';
			}

			$snippet = <<<HTML
			<a href="https://wordpress.com/create/" target="_blank">
				<img src="$ad_url" alt="WordPress.com: Grow Your Business" width="$width" height="$height" />
			</a>
HTML;
		} else {
			$section_id = 0 === $wordads->params->blog_id ? WORDADS_API_TEST_ID : $wordads->params->blog_id . '3';
			$data_tags = ( $wordads->params->cloudflare ) ? ' data-cfasync="false"' : '';
			$snippet = <<<HTML
			<script$data_tags type='text/javascript'>
				(function(g){g.__ATA.initAd({sectionId:$section_id, width:$width, height:$height});})(window);
			</script>
HTML;
		}

		echo <<< HTML
		<div class="wpcnt">
			<div class="wpa">
				<a class="wpa-about" href="https://en.wordpress.com/about-these-ads/" rel="nofollow">$about</a>
				<div class="u {$instance['unit']}">
					$snippet
				</div>
			</div>
		</div>
HTML;
	}

	public function form( $instance ) {
		// ad unit type
		if ( isset( $instance['unit'] ) ) {
			$unit = $instance['unit'];
		} else {
			$unit = 'mrec';
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'unit' ) ); ?>"><?php _e( 'Tag Dimensions:', 'jetpack' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'unit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'unit' ) ); ?>">
		<?php
		foreach ( WordAds::$ad_tag_ids as $ad_unit => $properties ) {
				if ( ! in_array( $ad_unit, self::$allowed_tags ) ) {
					continue;
				}

				$splits = explode( '_', $properties['tag'] );
				$unit_pretty = "{$splits[0]} {$splits[1]}";
				$selected = selected( $ad_unit, $unit, false );
				echo "<option value='", esc_attr( $ad_unit ) ,"' ", $selected, '>', esc_html( $unit_pretty ) , '</option>';
			}
		?>
			</select>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if ( in_array( $new_instance['unit'], self::$allowed_tags ) ) {
			$instance['unit'] = $new_instance['unit'];
		} else {
			$instance['unit'] = 'mrec';
		}

		return $instance;
	}
}

add_action(
	'widgets_init',
	create_function(
		'',
		'return register_widget( "WordAds_Sidebar_Widget" );'
	)
);
