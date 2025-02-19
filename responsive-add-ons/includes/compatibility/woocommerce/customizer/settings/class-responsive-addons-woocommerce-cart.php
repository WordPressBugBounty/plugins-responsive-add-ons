<?php
/**
 * Footer Customizer Options
 *
 * @package Responsive Addons Pro Plugin Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Responsive_Addons_Woocommerce_Cart' ) ) :
	/**
	 * Footer Customizer Options
	 */
	class Responsive_Addons_Woocommerce_Cart {
		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'customize_register', array( $this, 'customizer_options' ) );
		}

		/**
		 * Customizer options
		 *
		 * @param  object $wp_customize WordPress customization option.
		 */
		public function customizer_options( $wp_customize ) {
			$theme = wp_get_theme();

			/*
			------------------------------------------------------------------
				// Cart Icon
			-------------------------------------------------------------------
			*/
			if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {

				// Cart Icon Heading.
				$spacing_separator_label = __( 'Cart Icon', 'responsive-addons' );
				responsive_separator_control( $wp_customize, 'cart_icon_separator', $spacing_separator_label, 'responsive_header_woo_cart', 20 );

				$wp_customize->add_setting(
					'responsive_cart_icon',
					array(
						'default'           => 'icon-opencart',
						'transport'         => 'refresh',
						'sanitize_callback' => 'responsive_sanitize_select',
					)
				);
				$wp_customize->add_control(
					new Responsive_Customizer_Select_Button_Control(
						$wp_customize,
						'responsive_cart_icon',
						array(
							'label'    => __( 'Cart Icon', 'responsive-addons' ),
							'section'  => 'responsive_header_woo_cart',
							'settings' => 'responsive_cart_icon',
							'priority' => 30,
							'choices'  => array(
								'icon-opencart'        => esc_html__( 'icon-opencart', 'responsive-addons' ),
								'icon-shopping-cart'   => esc_html__( 'icon-shopping-cart', 'responsive-addons' ),
								'icon-shopping-bag'    => esc_html__( 'icon-shopping-bag', 'responsive-addons' ),
								'icon-shopping-basket' => esc_html__( 'icon-shopping-basket', 'responsive-addons' ),
							),
						)
					)
				);

				responsive_horizontal_separator_control( $wp_customize, 'header_woo_cart_separator_1', 1, 'responsive_header_woo_cart', 35, 1, );

				/*
				------------------------------------------------------------------
					// Header Cart Style
				-------------------------------------------------------------------
				*/

				$wp_customize->add_setting(
					'responsive_cart_style',
					array(
						'default'           => 'outline',
						'transport'         => 'refresh',
						'sanitize_callback' => 'responsive_sanitize_select',
					)
				);
				$wp_customize->add_control(
					new Responsive_Customizer_Select_Button_Control(
						$wp_customize,
						'responsive_cart_style',
						array(
							'label'    => __( 'Icon Style', 'responsive-addons' ),
							'section'  => 'responsive_header_woo_cart',
							'settings' => 'responsive_cart_style',
							'priority' => 40,
							'choices'  => array(
								'none'    => esc_html__( 'None', 'responsive-addons' ),
								'outline' => esc_html__( 'Outline', 'responsive-addons' ),
								'fill'    => esc_html__( 'Fill', 'responsive-addons' ),
							),
						)
					)
				);

				responsive_horizontal_separator_control( $wp_customize, 'header_woo_cart_separator_2', 1, 'responsive_header_woo_cart', 45, 1, );

				// Icon Size.
				$icon_size_label = esc_html__( 'Icon Size (px)', 'responsive-addons' );
				responsive_drag_number_control( $wp_customize, 'cart_icon_size', $icon_size_label, 'responsive_header_woo_cart', 50, 20, null, 100, 0, 'postMessage' );

				responsive_horizontal_separator_control( $wp_customize, 'header_woo_cart_separator_3', 2, 'responsive_header_woo_cart', 55, 1, );

				$cart_color_label = __( 'Cart Color', 'responsive-addons' );
				responsive_color_control( $wp_customize, 'cart', $cart_color_label, 'responsive_header_woo_cart', 120, '#000000', null, '', true, '#000000', 'cart_hover' );

				responsive_horizontal_separator_control( $wp_customize, 'header_woo_cart_separator_6', 1, 'responsive_header_woo_cart', 125, 1, );

				responsive_horizontal_separator_control( $wp_customize, 'header_woo_cart_separator_7', 2, 'responsive_header_woo_cart', 135, 1, );

				// Border Heading.
				$border_heading = __( 'Border', 'responsive-addons' );
				responsive_separator_control( $wp_customize, 'cart_border_separator', $border_heading, 'responsive_header_woo_cart', 140 );

				// Cart Border Width.
				$buttons_border_width_label = __( 'Border Width (px)', 'responsive-addons' );
				responsive_drag_number_control( $wp_customize, 'cart_border_width', $buttons_border_width_label, 'responsive_header_woo_cart', 150, 1, null, 20, 0, 'postMessage' );

				// Cart Radius.
				$cart_radius_label = __( 'Radius (px)', 'responsive-addons' );
				responsive_radius_control( $wp_customize, 'cart_radius', 'responsive_header_woo_cart', 160, 0, 0, null, $cart_radius_label );
			}
		}
	}

endif;

return new Responsive_Addons_Woocommerce_Cart();
