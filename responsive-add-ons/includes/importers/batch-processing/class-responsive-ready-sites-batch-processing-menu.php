<?php
/**
 * Menu fix batch task.
 *
 * @package Responsive Addons
 * @since 2.0.3
 */

if ( ! class_exists( 'Responsive_Ready_Sites_Batch_Processing_Menu' ) ) :

	/**
	 * Responsive_Ready_Sites_Batch_Processing_Menu
	 *
	 * @since 2.0.3
	 */
	class Responsive_Ready_Sites_Batch_Processing_Menu {

		/**
		 * Instance
		 *
		 * @since 2.0.3
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 2.0.3
		 * @return object initialized object of class.
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 2.0.3
		 */
		public function __construct() {}

		/**
		 * Import
		 *
		 * @since 2.0.3
		 * @return void
		 */
		public function import() {

			self::fix_nav_menus();
		}

		/**
		 * Fix Menu.
		 */
		public static function fix_nav_menus() {
			$options            = get_option( '_responsive_ready_sites_old_site_options', array() );
			$nav_menu_locations = isset( $options['nav_menu_locations'] ) ? $options['nav_menu_locations'] : array();

			// Check customizer data if options mapping is empty or not an array.
			if ( empty( $nav_menu_locations ) || ! is_array( $nav_menu_locations ) ) {
				$customizer_data = get_option( '_responsive_sites_old_customizer_data', array() );
				if ( isset( $customizer_data['theme_mods_responsive']['nav_menu_locations'] ) ) {
					$nav_menu_locations = $customizer_data['theme_mods_responsive']['nav_menu_locations'];
				}
			}

			if ( ! empty( $nav_menu_locations ) && is_array( $nav_menu_locations ) ) {
				$theme_nav_menu_locations = get_theme_mod( 'nav_menu_locations' );

				foreach ( $nav_menu_locations as $location => $menu_ref ) {
					
					$term = null;

					if ( is_numeric( $menu_ref ) ) {
						// Look for a term that has this source ID.
						$terms = get_terms(
							array(
								'taxonomy'   => 'nav_menu',
								'hide_empty' => false,
								'meta_query' => array(
									array(
										'key'   => '_responsive_ready_sites_source_id',
										'value' => (int) $menu_ref,
									),
								),
							)
						);
						if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
							$term = $terms[0];
						}
					} else {
						$term = get_term_by( 'slug', $menu_ref, 'nav_menu' );
					}

					if ( is_object( $term ) && ! is_wp_error( $term ) ) {
						$theme_nav_menu_locations[ $location ] = $term->term_id;
					}
				}
				set_theme_mod( 'nav_menu_locations', $theme_nav_menu_locations );
			} else {
				$header_menu_term_id = term_exists( 'menu1' );
				if ( $header_menu_term_id ) {
					$theme_nav_menu_locations                = get_theme_mod( 'nav_menu_locations' );
					$theme_nav_menu_locations['header-menu'] = $header_menu_term_id;
					set_theme_mod( 'nav_menu_locations', $theme_nav_menu_locations );
				}

				$footer_menu_term_id = term_exists( 'menu2' );
				if ( $footer_menu_term_id ) {
					$theme_nav_menu_locations                   = get_theme_mod( 'nav_menu_locations' );
					$theme_nav_menu_locations['footer-menu']    = $footer_menu_term_id;
					$theme_nav_menu_locations['secondary-menu'] = $footer_menu_term_id;
					set_theme_mod( 'nav_menu_locations', $theme_nav_menu_locations );
				}
			}
		}
	}

	Responsive_Ready_Sites_Batch_Processing_Menu::get_instance();

endif;
