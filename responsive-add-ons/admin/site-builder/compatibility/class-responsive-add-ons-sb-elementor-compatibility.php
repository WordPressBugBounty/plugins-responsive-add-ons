<?php
/**
 * Responsive Addons Site Builder Elementor Compatibility 
 *
 * @package Responsive_Add_Ons
 * @since 3.3.0
 */

if ( ! class_exists( 'Responsive_Add_Ons_SB_Elementor_Compatibility' ) ) {

    /**
	 * Responsive Addons Site Builder Compatibility base class
	 *
	 * @since 3.3.0
	 */
    class Responsive_Add_Ons_SB_Elementor_Compatibility extends Responsive_Add_Ons_Site_Builder_compatibility {

        /**
		 * Instance
		 *
		 * @since 3.3.0
		 *
		 * @var object Class object.
		 */
        private static $instance;

        /**
		 * Initiator
		 *
		 * @since 3.3.0
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

        /**
		 * Render content for post.
		 *
		 * @param int $post_id Post id.
		 *
		 * @since 3.3.0
		 */
		public function render_content( $post_id ) {

			// set post to glabal post.
			$elementor_instance = Elementor\Plugin::instance();
			echo do_shortcode( $elementor_instance->frontend->get_builder_content_for_display( $post_id ) );
		}

        /**
		 * Load styles and scripts.
		 *
		 * @param int $post_id Post id.
		 *
		 * @since 3.3.0
		 */
		public function enqueue_scripts( $post_id ) {

			if ( '' !== $post_id ) {
				if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
					$css_file = new \Elementor\Core\Files\CSS\Post( $post_id );
				} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
					$css_file = new \Elementor\Post_CSS_File( $post_id );
				}

				$css_file->enqueue();
			}
		}
    }
}