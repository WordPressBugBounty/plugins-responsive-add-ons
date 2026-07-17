<?php
/**
 * Site Builder — read-only page-builder content renderers.
 *
 * Backward-compat port of Responsive Pro's compatibility classes. These
 * only *render* previously-saved content (Gutenberg blocks or Elementor
 * data already stored on the post) — they don't expose any editor.
 *
 * @package Responsive_Add_Ons
 * @since   3.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RASB_Legacy_Compatibility' ) ) {

	class RASB_Legacy_Compatibility {

		private static $instance;

		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Returns the renderer instance appropriate for how the post was built.
		 *
		 * @param int $post_id Post id.
		 * @return RASB_Legacy_Compatibility
		 */
		public function get_active_page_builder( $post_id ) {

			global $wp_post_types;
			$post_type = get_post_type( $post_id );

			if ( class_exists( '\Elementor\Plugin' ) ) {
				$document = Elementor\Plugin::$instance->documents->get( $post_id ); // phpcs:ignore PHPCompatibility.LanguageConstructs.NewLanguageConstructs.t_ns_separatorFound
				$built_with_elementor = $document ? $document->is_built_with_elementor() : false;

				if ( $built_with_elementor ) {
					return RASB_Legacy_Elementor_Compatibility::get_instance();
				}
			}

			return RASB_Legacy_Gutenberg_Compatibility::get_instance();
		}

		/**
		 * Fallback renderer: raw post_content, shortcodes processed.
		 *
		 * @param int $post_id Post id.
		 */
		public function render_content( $post_id ) {
			$current_post = get_post( $post_id, OBJECT );
			if ( ! $current_post ) {
				return;
			}
			echo do_shortcode( $current_post->post_content );
		}
	}
}

if ( ! class_exists( 'RASB_Legacy_Gutenberg_Compatibility' ) ) {

	class RASB_Legacy_Gutenberg_Compatibility extends RASB_Legacy_Compatibility {

		private static $instance;

		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Render Blocks content for post.
		 *
		 * @param int $post_id Post id.
		 */
		public function render_content( $post_id ) {
			$output       = '';
			$current_post = get_post( $post_id, OBJECT );

			if ( ! $current_post ) {
				return;
			}

			if ( has_blocks( $current_post ) ) {
				$blocks = parse_blocks( $current_post->post_content );
				foreach ( $blocks as $block ) {
					$output .= render_block( $block );
				}

				if ( class_exists( 'WP_Embed' ) ) {
					$wp_embed = new WP_Embed();
					$output   = $wp_embed->autoembed( $output );
				}
			} else {
				$output = $current_post->post_content;
			}

			echo do_shortcode( do_shortcode( shortcode_unautop( $output ) ) );
		}

		/**
		 * Enqueue Gutenberg block front-end styles for a layout, via the
		 * free plugin's own block-editor-addons frontend styles helper
		 * (if it's active — this only reads existing block attributes,
		 * it doesn't grant any editing capability).
		 *
		 * @param int $post_id Post id.
		 */
		public function enqueue_blocks_assets( $post_id ) {

			if ( ! defined( 'RESPONSIVE_BLOCK_EDITOR_ADDONS_VER' ) ) {
				return;
			}

			$post_css     = '';
			$current_post = get_post( $post_id, OBJECT );
			if ( ! $current_post ) {
				return;
			}

			$layout                  = get_post_meta( $post_id, 'responsive-site-builder-layout', true );
			$active_gutenberg_blocks = parse_blocks( $current_post->post_content );

			if ( class_exists( 'Responsive_Block_Editor_Addons_Frontend_Styles_Helper' ) ) {
				$rba_frontend_styles_helper = Responsive_Block_Editor_Addons_Frontend_Styles_Helper::get_instance();
				$post_css                  .= $rba_frontend_styles_helper->get_styles( $active_gutenberg_blocks );

				if ( function_exists( 'responsive_block_editor_addons_fetch_google_fonts' ) ) {
					responsive_block_editor_addons_fetch_google_fonts( $active_gutenberg_blocks, $layout );
				}
				do_action( 'responsive_block_editor_addons_enqueue_scripts', $post_id );
			}

			if ( ! empty( $post_css ) ) {
				echo "<style id='responsive-addons-rbea-blocks-frontend-styles-" . esc_attr( $layout ) . "'>" . $post_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
}

if ( ! class_exists( 'RASB_Legacy_Elementor_Compatibility' ) ) {

	class RASB_Legacy_Elementor_Compatibility extends RASB_Legacy_Compatibility {

		private static $instance;

		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Render previously-saved Elementor content for display.
		 * Read-only: uses Elementor's own front-end display renderer,
		 * never touches the editor.
		 *
		 * @param int $post_id Post id.
		 */
		public function render_content( $post_id ) {
			if ( ! class_exists( '\Elementor\Plugin' ) ) {
				return;
			}
			$elementor_instance = Elementor\Plugin::instance();
			echo do_shortcode( $elementor_instance->frontend->get_builder_content_for_display( $post_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
