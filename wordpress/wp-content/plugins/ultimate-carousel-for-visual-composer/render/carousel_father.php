<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class WPBakeryShortCode_vc_carousel_father extends WPBakeryShortCodesContainer {

	protected function content( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'padding'			=>		'15%',
			'theme'				=>		'default-tdt',
			'mbl_height'		=>		'',
			'effect'			=>		'false',
			'arrow'				=>		'true',
			'dot'				=>		'true',
			'autoplay'			=>		'true',
			// 'speed'			=>		'1500',
			'slide_visible'		=>		'1',
			'tabs'				=>		'1',
			'slide_visible_mbl'	=>		'1',
			'slide_scroll'		=>		'1',
			'spaces'			=>		'0px',
			'dotclr'			=>		'transparent',
			'borderclr'			=>		'transparent',
			'arrowclr'			=>		'#000',
			'arrowsize'			=>		'30px',
		), $atts ) );
		$some_id = rand(5, 500);
		$content = wpb_js_remove_wpautop($content, true);
		wp_enqueue_style( 'slick-carousel-css', plugins_url( '../css/slick-carousal.css' , __FILE__ ));
		wp_enqueue_script( 'slick-js', plugins_url( '../js/slick.js' , __FILE__ ), array('jquery'));
		wp_enqueue_script( 'custom-js', plugins_url( '../js/custom-tm.js' , __FILE__ ), array('jquery'));
		ob_start(); ?>
		<section class="tdt-slider slider <?php echo $theme; ?>" id="tdt-slider-<?php echo $some_id ?>" data-mobiles="<?php echo $slide_visible_mbl ?>" data-tabs="<?php echo $tabs ?>" style="width: <?php echo $width; ?>;" data-slick='{"arrows": <?php echo $arrow; ?>, "autoplaySpeed": 2200, "dots": <?php echo $dot; ?>, "autoplay": <?php echo $autoplay; ?>, "slidesToShow": <?php echo $slide_visible; ?>, "slidesToScroll": <?php echo $slide_scroll; ?>, "fade": <?php echo $effect; ?>}'>
		    <?php echo $content; ?>
		</section>

		<style>
		#tdt-slider-<?php echo $some_id ?> .slick-dots li button:before{
			color: <?php echo $dotclr ?>;
			border: 2px solid <?php echo $borderclr ?>;
		}
		#tdt-slider-<?php echo $some_id ?> .slick-next:before {
			color: <?php echo $arrowclr ?> !important;
			font-size: <?php echo $arrowsize; ?> !important;
		}
		#tdt-slider-<?php echo $some_id ?> .slick-prev:before {
			color: <?php echo $arrowclr ?> !important;
			font-size: <?php echo $arrowsize; ?> !important;
		}
		#tdt-slider-<?php echo $some_id ?> .slick-dots li.slick-active button:before {
			opacity: 1 !important;
		}
		#tdt-slider-<?php echo $some_id ?>.content-over-slider .slick-slide .content-section {
			top: <?php echo $padding ?>;
		}
		#tdt-slider-<?php echo $some_id ?> .slick-slide {
			padding: 0 <?php echo $spaces ?> !important;
		}
		@media only screen and (max-width: 480px) {
			#tdt-slider-<?php echo $some_id ?>.content-over-slider .slick-slide .content-section {
				top: 35px !important;
			}
			#tdt-slider-<?php echo $some_id ?>.content-over-slider .ultimate-slide-img {
				height: <?php echo $mbl_height; ?> !important;
				object-fit: cover;
			}
		}
		</style>
		<?php return ob_get_clean();
	}
}


vc_map( array(
	"base" 			=> "vc_carousel_father",
	"name" 			=> __( 'Carousel Slider', 'slider' ),
	"as_parent" 	=> array('only' => 'vc_carousel_son'),
	"content_element" => true,
	"js_view" 		=> 'VcColumnView',
	"category" 		=> __('ADC Slider'),
	"description" 	=> __('show as slider', ''),
	"icon" => plugin_dir_url( __FILE__ ).'../icons/carousal-slider.png',
	'params' => array(
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Select Theme', 'slider' ),
			"param_name" 	=> 	"theme",
			"description"	=>	__('<a href="https://www.topdigitaltrends.net/advanced-carousal-slider/">See Demo</a> or <a href="https://www.topdigitaltrends.net/how-ultimate-carousel-work/">How to Use</a> or <a href="https://www.topdigitaltrends.net/wp-content/uploads/2020/01/ultimate-carousel-vc-shrtcode.txt" target="_blank">Copy Demo Page VC Shortocde</a>', 'slider'),
			"group" 		=> 'Settings',
				"value" 		=> 	array(
					"Top Image Bottom Content" 		=> 		"default-tdt",
					"Content Over Image" 			=> 		"content-over-slider",
				)
		),
		/*array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Width', 'slider' ),
			"param_name" 	=> 	"width",
			"description"	=>	__('container width in percentage eg, 100%', 'slider'),
			"value"			=>	"100%",
			"group" 		=> 'Settings',
		),*/
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
			"heading" 		=> 	__( 'On Desktop', 'slider' ),
			"edit_field_class" => "vc_col-sm-4 wdo_items_to_show wdo_margin_bottom",
			"param_name" 	=> 	"slide_visible",
			"description"	=>	__('set visible number of slides. default is 1', 'slider'),
			"value"			=>	"1",
			"group" 		=> 'Settings',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'On Tabs', 'slider' ),
			"edit_field_class" => "vc_col-sm-4 wdo_items_to_show wdo_margin_bottom",
			"param_name" 	=> 	"tabs",
			"value"			=>	"1",
			"group" 		=> 'Settings',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'On Mobile', 'slider' ),
			"edit_field_class" => "vc_col-sm-4 wdo_items_to_show wdo_margin_bottom",
			"param_name" 	=> 	"slide_visible_mbl",
			"value"			=>	"1",
			"group" 		=> 'Settings',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Slide To Scroll', 'slider' ),
			"param_name" 	=> 	"slide_scroll",
			"description"	=>	__('allow user to multiple slide on click or drag. default is 1', 'slider'),
			"value"			=>	"1",
			"group" 		=> 'Settings',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Spaces between two items In (px)', 'slider' ),
			"edit_field_class" => "vc_col-sm-4 wdo_items_to_show wdo_margin_bottom",
			"param_name" 	=> 	"spaces",
			"value"			=>	"0px",
			"group" 		=> 'Settings',
		),
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Slide Effect', 'slider' ),
			"param_name" 	=> 	"effect",
			"description"	=>	__('choose slider effect', 'slider'),
			"group" 		=> 'Settings',
				"value" 		=> 	array(
					"Slide [Right To Left]" 		=> 		"false",
					"Fade (available in pro)" 			=> 		"",
				)
		),
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Autoplay', 'slider' ),
			"param_name" 	=> 	"autoplay",
			"description"	=>	__('move auto or slide on click', 'slider'),
			"group" 		=> 'Settings',
			"value" 		=> 	array(
				"True" 						=> 		"true",
				"False (available in pro)" 	=> 		"",
			)
		),
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Adaptive Height', 'slider' ),
			"param_name" 	=> 	"adaptiveheight",
			"description"	=>	__('resize height automatically to fill the gap If each slide has different height', 'slider'),
			"group" 		=> 'Settings',
			"value" 		=> 	array(
				"False" 						=> 		"false",
				"True (available in pro)" 		=> 		"true",
			)
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Slider Speed (available in pro)', 'slider' ),
			"param_name" 	=> 	"speed",
			"description"	=>	__('write in ms eg, 1500 [1s = 1000]', 'slider'),
			"value"			=>	"2500",
			"group" 		=> 'Settings',
		),

		array(
			"type" 			=> "vc_links",
			"param_name" 	=> "caption_url",
			"class"			=>	"ult_param_heading",
			"description" 	=> __( '<span style="Background: #ddd;padding: 10px; display: block; color: #0073aa;font-weight:600;"><a href="https://1.envato.market/nJQOa" target="_blank" style="text-decoration: none;">Get the Pro version for more stunning elements and customization options.</a></span>', 'ihover' ),
			"group" 		=> 'Settings',
		),

		// Navigation Section Setting 
		
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Arrows', 'slider' ),
			"param_name" 	=> 	"arrow",
			"description"	=>	__('Show/Hide on left & right', 'slider'),
			"group" 		=> 'Navigation',
				"value" 		=> 	array(
					"Show" 			=> 		"true",
					"Hide" 			=> 		"false",
				)
		),
		array(
			"type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Arrow Color', 'slider' ),
			"param_name" 	=> 	"arrowclr",
			"dependency" 	=> array('element' => "arrow", 'value' => 'true'),
			"value"			=>	"#000",
			"group" 		=> 'Navigation',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Arrow Font Size', 'slider' ),
			"param_name" 	=> 	"arrowsize",
			"description"	=>	"set in pixel eg, 30px",
			"dependency" 	=> array('element' => "arrow", 'value' => 'true'),
			"value"			=>	"30px",
			"group" 		=> 'Navigation',
		),
		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Dots', 'slider' ),
			"param_name" 	=> 	"dot",
			"description"	=>	__('Show/Hide show at bottom', 'slider'),
			"group" 		=> 'Navigation',
				"value" 		=> 	array(
					"Show" 			=> 		"true",
					"Hide" 			=> 		"false",
				)
		),

		array(
			"type" 			=> 	"dropdown",
			"heading" 		=> 	__( 'Dot/Border', 'slider' ),
			"param_name" 	=> 	"style",
			"group" 		=> 'Navigation',
			"dependency" => array('element' => "dot", 'value' => 'true'),
			"value"			=>	array(
				"Dot"		=>		"dot",
				"Border"	=>		"border",
			)
		),
		array(
			"type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Dot Color', 'slider' ),
			"param_name" 	=> 	"dotclr",
			"dependency" => array('element' => "style", 'value' => 'dot'),
			"value"			=>	"#000",
			"group" 		=> 'Navigation',
		),
		array(
			"type" 			=> 	"colorpicker",
			"heading" 		=> 	__( 'Border Color', 'slider' ),
			"param_name" 	=> 	"borderclr",
			"dependency" => array('element' => "style", 'value' => 'border'),
			"value"			=>	"#000",
			"group" 		=> 'Navigation',
		),


		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Carousel Height (For Mobile)', 'slider' ),
			"param_name" 	=> 	"mbl_height",
			"description"	=>	__( 'set in pixel eg, 250px or leave blank', 'slider' ),
			"dependency" => array('element' => "theme", 'value' => 'content-over-slider'),
			"group" 		=> 'Mobile Option',
		),
		array(
			"type" 			=> 	"textfield",
			"heading" 		=> 	__( 'Padding Top (For PC)', 'slider' ),
			"param_name" 	=> 	"padding",
			"description"	=>	__('set in pixel or % eg 100px. padding will apply from top for the content', 'slider'),
			"dependency" => array('element' => "theme", 'value' => 'content-over-slider'),
			"value"			=>	"15%",
			"group" 		=> 'Mobile Option',
		),

		// Pro Options

	)
) );
