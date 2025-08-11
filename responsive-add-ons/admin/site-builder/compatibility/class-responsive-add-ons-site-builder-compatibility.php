<?php
/**
 * Site Builder Compatibility
 * 
 * @package Responsive_Add_Ons
 * @since 3.3.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'Responsive_Add_Ons_Site_Builder_compatibility' ) ) {

    /**
     * Site Builder Compatibility
     * 
     * @since 3.3.0
     */
    class Responsive_Add_Ons_Site_Builder_compatibility {

        /**
		 * Member Variable
		 *
		 * @var object instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 *
		 * @since 3.3.0
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

        		/**
		 * Returns instance for active page builder.
		 *
		 * @param int $post_id Post id.
		 *
		 * @since 1.6.0
		 */
		public function get_active_page_builder( $post_id ) {

			global $wp_post_types;
			$post      = get_post( $post_id );
			$post_type = get_post_type( $post_id );

			if ( class_exists( '\Elementor\Plugin' ) ) {
				$document = Elementor\Plugin::$instance->documents->get( $post_id ); // phpcs:ignore PHPCompatibility.LanguageConstructs.NewLanguageConstructs.t_ns_separatorFound
				if ( $document ) {
					$deprecated_handle = $document->is_built_with_elementor();
				} else {
					$deprecated_handle = false;
				}

				if ( ( version_compare( ELEMENTOR_VERSION, '1.5.0', '<' ) &&
					'builder' === Elementor\Plugin::$instance->db->get_edit_mode( $post_id ) ) || $deprecated_handle ) {
					return Responsive_Add_Ons_SB_Elementor_Compatibility::get_instance();
				}
			}

			$has_rest_support = isset( $wp_post_types[ $post_type ]->show_in_rest ) ? $wp_post_types[ $post_type ]->show_in_rest : false;

			if ( $has_rest_support ) {
				return new Responsive_Add_Ons_SB_Gutenberg_Compatibility();
			}

			return self::get_instance();
		}
    }
}

Responsive_Add_Ons_Site_Builder_compatibility::get_instance();