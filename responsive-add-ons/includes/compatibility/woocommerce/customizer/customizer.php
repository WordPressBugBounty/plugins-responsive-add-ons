<?php
/**
 * Woocommerce Plugin Customizer
 *
 * @package woocommerce
 */

if ( ! function_exists( 'responsive_addons_pagination_callbacks' ) ) {
	/**
	 * Function for sanitizing
	 */
	function responsive_addons_pagination_callbacks() {
		$shop_scroll_style = get_theme_mod( 'shop_pagination', 'default' );
		if ( 'default' === $shop_scroll_style ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'responsive_addons_pagination_trigger' ) ) {
	/**
	 * Determines if infinite scroll pagination is enabled for the shop.
	 *
	 * Retrieves the 'shop_pagination' theme setting and returns true if it is set to 'infinite',
	 * otherwise returns false.
	 *
	 * @return bool True if infinite scroll pagination is enabled, false otherwise.
	 */
	function responsive_addons_pagination_trigger() {
		$shop_scroll_style = get_theme_mod( 'shop_pagination', 'default' );
		if ( 'infinite' === $shop_scroll_style ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'responsive_addons_load_more_callback' ) ) {
	/**
	 * Callback to determine if "Load More" should be enabled based on theme settings.
	 *
	 * This function checks the theme modifications to determine if the shop uses
	 * infinite scroll with a "click" event for loading more products.
	 *
	 * @return bool True if infinite scroll is enabled with click event, false otherwise.
	 */
	function responsive_addons_load_more_callback() {
		$shop_infinite_loading = get_theme_mod( 'shop-infinite-scroll-event', 'scroll' );
		$shop_scroll_style     = get_theme_mod( 'shop_pagination', 'default' );
		if ( 'infinite' === $shop_scroll_style && 'click' === $shop_infinite_loading ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'responsive_addons_separator_control' ) ) {
	/**
	 * [responsive_addons_separator_control description].
	 *
	 * @param  [type] $wp_customize [description].
	 * @param  [type] $element      [description].
	 * @param  [type] $label        [description].
	 * @param  [type] $section      [description].
	 * @param  [type] $priority     [description].
	 * @param  [type] $active_call     [description].
	 *
	 * @return void               [description].
	 */
	function responsive_addons_separator_control( $wp_customize, $element, $label, $section, $priority, $active_call = null ) {

		/**
		 *  Heading.
		 */
		$wp_customize->add_setting(
			'responsive_' . $element,
			array(
				'sanitize_callback' => 'wp_kses',
			)
		);

		$wp_customize->add_control(
			new Responsive_Customizer_Heading_Control(
				$wp_customize,
				'responsive_' . $element,
				array(
					'label'           => $label,
					'section'         => $section,
					'priority'        => $priority,
					'active_callback' => $active_call,
				)
			)
		);
	}
}
