<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class WPBakeryShortCode_na_posts_carousel extends WPBakeryShortCode {

	protected function content( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'style'				=>		'mega-post-carousel1',
			'settings'			=>		'',
			'height'			=>		'200px',
			'arrow'				=>		'true',
			'excerpt'			=>		'120',
			'dot'				=>		'true',
			'autoplay'			=>		'true',
			'speed'				=>		'1500',
			'slide_visible'		=>		'3',
			'slide_visible_mbl'	=>		'1',
			'tabs'				=>		'1',
			'slide_scroll'		=>		'1',
			'comment'			=>		'block',
			'catg'				=>		'visible',
			'txtsize'			=>		'18px',
			'descsize'			=>		'14px',
			'themeclr'			=>		'#1D5B84',
			'txtclr'			=>		'#000',
			'dateclr'			=>		'#000',
			'descclr'			=>		'#888',
			'css'				=>		'',
		), $atts ) );
		if (isset($image_id) &&  $image_id != '') {
			$image_url = wp_get_attachment_url( $image_id );		
		}
		// var_dump($settings);
		$content = wpb_js_remove_wpautop($content, true);
		$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ), $this->settings['base'], $atts );
		wp_enqueue_style( 'slick-carousel-css', plugins_url( '../css/slick-carousal.css' , __FILE__ ));
		wp_enqueue_style( 'post-design-css', plugins_url( '../css/post-design.css' , __FILE__ ));
		wp_enqueue_script( 'slick-js', plugins_url( '../js/slick.js' , __FILE__ ), array('jquery'));
		wp_enqueue_script( 'custom-js', plugins_url( '../js/custom-tm.js' , __FILE__ ), array('jquery'));
		ob_start();
		$args = array(
			'posts_per_page' => -1,
		);
		$seperate_settings = explode('|', $settings);
		// var_dump($seperate_settings);

		foreach ($seperate_settings as $setting) {
			$key_val = explode(':', $setting);
			if ($key_val[0] == 'size') {
				$args['posts_per_page'] = $key_val[1];
			} elseif($key_val[0] == 'categories') {
				$args['category__in'] = explode(',', $key_val[1]);
			} else {
				$args[$key_val[0]] = $key_val[1];
			}
		}

		// The Query
		$the_query = new WP_Query( $args );

		// The Loop
		if ( $the_query->have_posts() ) { ?>
			<section class="slider post-slider vc-post-styling" data-mobile="<?php echo $slide_visible_mbl ?>" data-tab="<?php echo $tabs ?>" data-slick='{"arrows": <?php echo $arrow; ?>, "autoplaySpeed": <?php echo $speed; ?>, "dots": <?php echo $dot; ?>, "autoplay": <?php echo $autoplay; ?>, "slidesToShow": <?php echo $slide_visible; ?>, "slidesToScroll": <?php echo $slide_scroll; ?>}'>
			<?php while ( $the_query->have_posts() ) { ?>
				<div>
				<?php $the_query->the_post(); ?>

					<?php switch ($style) {
						case 'mega-post-carousel1':
							include 'includes/style1.php';
							break;
						case 'mega-post-carousel2':
							include 'includes/style2.php';
							break;
						case 'mega-post-carousel3':
							include 'includes/style3.php';
							break;
						// case 'mega-post-carousel4':
						// 	include 'includes/style4.php';
						// 	break;
						
						default:
							include 'includes/style1.php';
							break;
					} ?>
					
				</div>
			<?php } ?>
			</section>
			<?php wp_reset_postdata();
		} else {
			// no posts found
		}
		
		return ob_get_clean();
	}
}


vc_map( array(
	"base" 			=> "na_posts_carousel",
	"name" 			=> __( 'Post Carousel', 'postslider' ),
	"category" 		=> __('ADC Slider'),
	"description" 	=> __('show posts as slider', ''),
	"icon" => plugin_dir_url( __FILE__ ).'../icons/post-carousel.png',
	'params' => array(
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Choose Style', 'postslider' ),
			"param_name" 	=> 	"style",
			"group" 		=> 'General',
			"value" 		=> 	array(
				"Style 1" 					=> 		"mega-post-carousel1",
				"Style 2" 					=> 		"mega-post-carousel2",
				"Style 3" 					=> 		"mega-post-carousel3",
				"Style 4 (Pro Option)" 		=> 		"none",
				"Style 5 (Pro Option)" 		=> 		"none",
				"Style 6 (Pro Option)" 		=> 		"none",
				"Style 7 (Pro Option)" 		=> 		"none",
				"Style 8 (Pro Option)" 		=> 		"none",
				"Style 9 (Pro Option)" 		=> 		"none",
			)
		),

		array(
			"type" 			=> 	"loop",
			"heading" 		=> 	__( 'Link To', 'postslider' ),
			"param_name" 	=> 	"settings",
			"description"	=>	"Add Slide Url or leave blank, use it if you select theme [top image bottom content]",
			"group" 		=> 'General',
		),

		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Image height', 'postslider' ),
			"param_name" 	=> 	"height",
			"description"	=>	__('post image height, set in pixel', 'postslider'),
			"group" 		=> 'Settings',
			"value"			=>	"200px",
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Content Excerpt', 'postslider' ),
			"param_name" 	=> 	"excerpt",
			"description"	=>	"visible content length, write only numbers default 120",
			"value"			=>	"120",
			"group" 		=> 'Settings',
		),

		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Comments', 'postslider' ),
			"param_name" 	=> 	"comment",
			"description"	=>	__('show/hide comment icon', 'postslider'),
			"group" 		=> 'Settings',
			"value" 		=> 	array(
				"Show" 		=> 		"block",
				"Hide" 	=> 		"none",
			)
		),
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Category', 'postslider' ),
			"param_name" 	=> 	"catg",
			"description"	=>	__('show/hide category name', 'postslider'),
			"group" 		=> 'Settings',
			"value" 		=> 	array(
				"Show" 		=> 		"visible",
				"Hide" 	=> 		"none",
			)
		),
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Arrows', 'postslider' ),
			"param_name" 	=> 	"arrow",
			"description"	=>	__('Show/Hide on left & right', 'postslider'),
			"group" 		=> 'Settings',
				"value" 		=> 	array(
					"Show" 			=> 		"true",
					"Hide" 			=> 		"false",
				)
		),
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Dots', 'postslider' ),
			"param_name" 	=> 	"dot",
			"description"	=>	__('Show/Hide show at bottom', 'postslider'),
			"group" 		=> 'Settings',
				"value" 		=> 	array(
					"Show" 			=> 		"true",
					"Hide" 			=> 		"false",
				)
		),
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Autoplay', 'postslider' ),
			"param_name" 	=> 	"autoplay",
			"description"	=>	__('move auto or slide on click', 'postslider'),
			"group" 		=> 'Settings',
			"value" 		=> 	array(
				"True" 		=> 		"true",
				"False" 	=> 		"false",
			)
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Slider Speed', 'postslider' ),
			"param_name" 	=> 	"speed",
			"description"	=>	__('write in ms eg, 1500 [1s = 1000]', 'postslider'),
			"value"			=>	"1500",
			"group" 		=> 'Settings',
		),
		array(
			"type"             => "text",
			"param_name"       => "wdo_title_text_typography",
			"heading"          => "<b>" . __( "Slides to Show‏", "wdo-carousel" ) . "</b>",
			"value"            => "",
			"edit_field_class" => "vc_col-sm-12 wdo_margin_top",
			"group"            => "Settings"
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'On Desktop', 'postslider' ),
			"edit_field_class" => "vc_col-sm-4 wdo_items_to_show wdo_margin_bottom",
			"param_name" 	=> 	"slide_visible",
			"description"	=>	__('set visible number of slides. default is 1', 'slider'),
			"dependency" => array('element' => "post", 'value' => 'post-slider'),
			"value"			=>	"1",
			"group" 		=> 'Settings',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'On Tabs', 'postslider' ),
			"edit_field_class" => "vc_col-sm-4 wdo_items_to_show wdo_margin_bottom",
			"param_name" 	=> 	"tabs",
			"value"			=>	"1",
			"group" 		=> 'Settings',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'On Mobile', 'postslider' ),
			"edit_field_class" => "vc_col-sm-4 wdo_items_to_show wdo_margin_bottom",
			"param_name" 	=> 	"slide_visible_mbl",
			"value"			=>	"1",
			"group" 		=> 'Settings',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Slide To Scroll', 'postslider' ),
			"param_name" 	=> 	"slide_scroll",
			"description"	=>	__('allow user to multiple slide on click or drag. default is 1', 'postslider'),
			"value"			=>	"1",
			"group" 		=> 'Settings',
		),

		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Title (Font Size)', 'postslider' ),
			"param_name" 	=> 	"txtsize",
			"description"	=>	"font size of post title, default 18px",
			"value"			=>	"18px",
			"group" 		=> 'Design',
		),

		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Description (Font Size)', 'postslider' ),
			"param_name" 	=> 	"descsize",
			"description"	=>	"font size of post content, default 14px",
			"value"			=>	"14px",
			"group" 		=> 'Design',
		),

		array(
			"type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Theme Color', 'postslider' ),
			"param_name" 	=> 	"themeclr",
			"description"	=>	"It will apply as default colors for post",
			"dependency" => array('element' => "style", 'value' => array('carousel-style8', 'carousel-style17', 'carousel-style19')),
			"value"			=>	"#1D5B84",
			"group" 		=> 'Color',
		),

		array(
			"type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Title Color', 'postslider' ),
			"param_name" 	=> 	"txtclr",
			"description"	=>	"color of post title",
			"value"			=>	"#000",
			"group" 		=> 'Color',
		),

		array(
			"type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Author/Date Color', 'postslider' ),
			"param_name" 	=> 	"dateclr",
			"description"	=>	"color of author/date",
			"value"			=>	"#000",
			"group" 		=> 'Color',
		),

		array(
			"type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Description Color', 'postslider' ),
			"param_name" 	=> 	"descclr",
			"description"	=>	"color of post content",
			"value"			=>	"#888",
			"group" 		=>  'Color',
		),

		array(
			"type" 			=> 	"css_editor",
			"heading" 		=> 	__( 'Display Design', 'postslider' ),
			"param_name" 	=> 	"css",
			"description"	=>	"color of post content",
			"group" 		=>  'Design Options',
		),
	),
) );
