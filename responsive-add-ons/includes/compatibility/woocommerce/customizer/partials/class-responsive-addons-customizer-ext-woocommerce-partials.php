<?php
/**
 * WooCommerce - Customizer Partials.
 *
 * @package Responsive Addons
 * @since 1.1.0
 */

// No direct access, please.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Responsive_Addons_Customizer_Ext_WooCommerce_Partials' ) ) {

	/**
	 * Responsive_Addons_Customizer_Ext_WooCommerce_Partials initial setup.
	 *
	 * @since 1.1.0
	 */
	class Responsive_Addons_Customizer_Ext_WooCommerce_Partials {

		/**
		 * Instance of this class.
		 *
		 * @var Responsive_Addons_Customizer_Ext_WooCommerce_Partials|null
		 */
		private static $instance = null;

		/**
		 * Gets an instance of this class.
		 *
		 * @return Responsive_Addons_Customizer_Ext_WooCommerce_Partials
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Class constructor.
		 */
		public function __construct() {
		}

		/**
		 * Render the "Load More" text for selective refresh partial.
		 *
		 * @since 1.1.0
		 *
		 * @return string The "Load More" button text.
		 */
		public function render_shop_load_more() {
			return get_theme_mod( 'shop-load-more-text', 'Load More' );
		}
	}
}

new Responsive_Addons_Customizer_Ext_WooCommerce_Partials();
