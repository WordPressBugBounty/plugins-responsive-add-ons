<?php
/**
 * Responsive Add-ons Site Builder
 * 
 * @package Responsive_Add_Ons
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'RESPONSIVE_ADDONS_SITE_BUILDER_URI', RESPONSIVE_ADDONS_URI . 'admin/site-builder/' );
define( 'RESPONSIVE_ADDONS_SITE_BUILDER_DIR', RESPONSIVE_ADDONS_DIR . 'admin/site-builder/' );
define ( 'RESPONSIVE_BUILDER_POST_TYPE', 'resp-site-builder' );

if ( ! class_exists( 'Responsive_Add_Ons_Site_Builder' ) ) {

    /**
     * Class Responsive_Add_Ons_Site_Builder
     */
    class Responsive_Add_Ons_Site_Builder {

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
         * Constructor
         */
        public function __construct() {
            // Initialize the site builder functionality here.
            add_action( 'admin_enqueue_scripts', array( $this, 'responsive_site_builder_admin_enqueue_scripts' ) );
            add_action( 'admin_init', array( $this, 'responsive_site_builder_disable_notices' ) );
            add_action( 'rest_api_init', array( $this, 'register_routes' ) );
			add_shortcode( 'responsive-site-builder', array( $this, 'render_shortcode_output' ) );
			add_filter( 'responsive_add_ons_dynamic_css', array( $this, 'responsive_site_builder_dynamic_css' ) );
			add_action( 'wp_ajax_responsive_addons_dismiss_sb_childtheme_warning', array( $this, 'responsive_addons_dismiss_sb_childtheme_warning' ), 10 );
			add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );
			add_action( 'admin_init', array( $this, 'responsive_addons_site_builder_redirect' ) );
            require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/class-responsive-add-ons-site-builder-editor.php';
            require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/class-responsive-add-ons-site-builder-display-rules.php';
            require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/class-responsive-add-ons-site-builder-markup.php';
            require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/compatibility/class-responsive-add-ons-site-builder-compatibility.php';
            require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/compatibility/class-responsive-add-ons-sb-gutenberg-compatibility.php';
            require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/compatibility/class-responsive-add-ons-sb-elementor-compatibility.php';
        }

        /**
         * Enqueue admin scripts and styles for the site builder.
         */
        public function responsive_site_builder_admin_enqueue_scripts( $hook = '' ) {

			$settings   = get_option( 'rpro_elementor_settings' );
			$theme_name = ! empty( $settings['theme_name'] ) ? mb_strtolower( $settings['theme_name'], 'UTF-8' ) : 'responsive';
			$theme_name = str_replace( [ ' ', '/' ], '-', $theme_name );

			$characters_to_remove = [ "'", '\\', '?', '|', '*', '"', '`' ];
			$theme_name           = str_replace( $characters_to_remove, '', $theme_name );
			$theme_name 		  = preg_replace( '/[^\p{L}\p{N}_-]/u', '', $theme_name );

			$parts              = explode('_page_responsive-site-builder', $hook);
			$encoded_part       = isset($parts[0]) ? $parts[0] : '';
			$decoded_theme_name = urldecode($encoded_part);

			// Check if the current page is the Responsive Site Builder page.
            if( $theme_name . '_page_responsive-site-builder' !== $hook && $theme_name . '_page_responsive-site-builder' !== $decoded_theme_name . '_page_responsive-site-builder' ) return;

            wp_enqueue_script( 'responsive-site-builder-script', RESPONSIVE_ADDONS_SITE_BUILDER_URI . 'react/build/index.js', array( 'react', 'react-dom', 'wp-components', 'wp-api-fetch' ), RESPONSIVE_ADDONS_VER, true );
            wp_enqueue_style( 'responsive-site-builder-style', RESPONSIVE_ADDONS_SITE_BUILDER_URI . 'react/build/output.css', array(), RESPONSIVE_ADDONS_VER );
			wp_enqueue_style( 'wp-components' );
            // Localize script to pass data to JavaScript.
            wp_localize_script( 'responsive-site-builder-script', 'responsive_addons_site_builder', array(
                'ajax_url'         => admin_url( 'admin-ajax.php' ),
                'title'            => __( 'Site Builder', 'responsive-addons' ),
                'admin_url'        => admin_url(),
				'res_dash_url'     => admin_url().'admin.php?page=responsive',
                'nonce'            => wp_create_nonce( 'wp_rest' ),
				'rest_url'         => get_rest_url( '', '/responsive-addons/v1/' ),
				'displayRules'     => Responsive_Add_Ons_Site_Builder_Display_Rules::get_location_selections(),
				'userRoles'        => Responsive_Add_Ons_Site_Builder_Display_Rules::get_user_selections(),
				'builderPostType'  => RESPONSIVE_BUILDER_POST_TYPE,
				'ajax_nonce'       => wp_create_nonce( 'responsive-sb-get-posts-by-query' ),
				'docsPageURL'      => 'https://cyberchimps.com/docs/responsive-plus/modules-settings/site-builder/',
				'childThemeDetected'           => is_child_theme(),
				'isChildThemeWarningDismissed' => get_option( 'responsive_addons_sb_childtheme_warning_dismiss' ),
				'dismiss_nonce'                => wp_create_nonce( 'responsive-addons-sb-childtheme-notice' ),
            ) );
        }

        /**
		 * Disable notices for Site Builder page.
		 *
		 * @since 3.3.0
		 * @return void
		 */
		public function responsive_site_builder_disable_notices() {

			if ( isset( $_GET['page'] ) && 'responsive-site-builder' === $_GET['page'] ) {
				remove_all_actions( 'admin_notices' );
				remove_all_actions( 'all_admin_notices' );
			}
		}

        public function register_routes() {
            register_rest_route(
                'responsive-addons/v1',
                'site-builder-layouts',
                array(
                    array(
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => array( $this, 'get_site_builder_layouts' ),
                        'permission_callback' => array( $this, 'get_permissions_check' ),
                        'args'                => array(),
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                )
            );

			register_rest_route(
				'responsive-addons/v1',
				'site-builder-layouts/(?P<id>\d+)',
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_site_builder_layout' ),
					'permission_callback' => array( $this, 'delete_permissions_check' ),
					'args'                => array(
						'id' => array(
							'validate_callback' => 'rest_validate_request_arg',
							'required'          => true,
							'type'              => 'integer',
						),
					),
				)
			);

			register_rest_route(
                'responsive-addons/v1',
                'site-builder-layout-status',
                array(
                    array(
                        'methods'             => WP_REST_Server::EDITABLE,
                        'callback'            => array( $this, 'update_layout_status' ),
                        'permission_callback' => array( $this, 'get_permissions_check' ),
                        'args'                => array(),
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                )
            );
        }

        /**
		 * Check whether a given request has permission access route.
		 *
		 * @since 3.3.0
		 * @param  WP_REST_Request $request Full details about the request.
		 * @return WP_Error|bool
		 */
        public function get_permissions_check( $request ) {
            if( ! current_user_can( 'manage_options' ) ) {
                return new WP_Error( 'responsive_rest_cannot_view', __( 'Sorry, you cannot list layouts.', 'responsive-addons' ), array( 'status' => rest_authorization_required_code() ) );
            }
            return true;
        }

        /**
		 * Get Site Builder Layouts.
		 *
		 * @since 3.3.0
		 * @param WP_REST_Request $request Full details about the request.
		 * @return array $updated_option defaults + set DB option data.
		 */
        public function get_site_builder_layouts( $request ) {

            $custom_layouts = new WP_Query(
				array(
					'post_type'      => 'resp-site-builder',
					'orderby'        => 'ID',
					'post_status'    => array( 'publish', 'draft', 'private', 'pending', 'future' ),
					'posts_per_page' => -1,
				)
			);

			$results = array();
			// Defined layout types for user selection.
			$defined_layouts = array('header', 'footer', '404-page');

			foreach ( $custom_layouts->posts as $post ) {

				// Get the author information for each post
				$author_id = $post->post_author;
				$author    = get_userdata( $author_id );

				// Get the author's avatar URL
				$author_avatar_url = get_avatar_url( $author_id );

				// Generate the edit link manually
				$edit_post_link = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );

				// Get the post preview link
				$post_preview_link = get_preview_post_link( $post );

				// Get the custom field values
				$layout_value  = get_post_meta( $post->ID, 'responsive-site-builder-layout', true );
				$layout_status = get_post_meta( $post->ID, 'responsive-site-builder-layout-status', true );

				if( in_array(  $layout_value, $defined_layouts ) ) {
					$layout_data = array(
						'ID'             => $post->ID,
						'post_author'    => $post->post_author,
						'post_title'     => $post->post_title,
						'author_name'    => $author->display_name,
						'author_image'   => $author_avatar_url,
						'post_modified'  => date( 'F d, Y', strtotime( $post->post_modified ) ),
						'post_name'      => $post->post_name,
						'post_status'    => $post->post_status,
						'post_link'      => $post_preview_link,
						'edit_post_link' => $edit_post_link,
						'layout_value'   => $layout_value,
						'layout_status'  => $layout_status,
					);
	
					$results[] = $layout_data;
				}
			}

			return $results;
        }

		/**
		 * Delete custom layout
		 *
		 * @since 3.3.0
		 * @param  WP_REST_Request $request Full details about the request.
		 * @return WP_Error|bool
		 */
		public function delete_site_builder_layout( $request ) {
			$post_id = $request['id'];
			if ( get_post_status( $post_id ) ) {
				if ( ! current_user_can( 'manage_options', $post_id ) ) {
					return new WP_Error(
						'rest_forbidden',
						__( 'Sorry, you are not allowed to move this item to the Trash.', 'responsive-addons' ),
						array( 'status' => 403 )
					);

				}
				wp_trash_post( $post_id );
				return new WP_REST_Response( array( 'message' => 'Post deleted successfully' ), 200 );
			}

			return new WP_Error( 'post_not_found', 'Post not found', array( 'status' => 404 ) );
		}

		/**
		 * Check whether a given request has permission to delete route.
		 *
		 * @since 3.3.0
		 * @param  WP_REST_Request $request Full details about the request.
		 * @return WP_Error|bool
		 */
		public function delete_permissions_check( $request ) {
			$post_id = $request['id'];
			if ( current_user_can( 'manage_options', $post_id ) ) {
				return true;
			}

			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to delete this item.', 'responsive-addons' ),
				array( 'status' => 403 )
			);
		}

		/**
		 * Render Shortcode.
		 *
		 * @param array $atts the shortcode args.
		 * @return string
		 */
		public function render_shortcode_output( $atts ) {
			$atts = shortcode_atts(
				array(
					'id' => 0,
				),
				$atts,
				'responsive-site-builder'
			);

			if ( empty( $atts['id'] ) ) {
				return;
			}

			$post = get_post( $atts['id'] );
			if ( empty( $post ) ) {
				return;
			}

			ob_start();
			do_action( 'responsive_site_builder_shortcode_before' );
			Responsive_Add_Ons_Site_Builder_Markup::get_instance()->get_action_content( $atts['id'] );
			do_action( 'responsive_site_builder_shortcode_after' );
			return ob_get_clean();
		}

		/**
		 * Update Layouts Status.
		 *
		 * @since 3.3.0
		 * @param  WP_REST_Request $request Full details about the request.
		 * @return WP_Error|bool
		 */
		public function update_layout_status( WP_REST_Request $request ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				return new WP_REST_Response( [ 'error' => 'Unauthorized' ], 403 );
			}

			$post_id = intval( $request->get_param( 'layout_id' ) );
			$status  = sanitize_text_field( $request->get_param( 'status' ) );

			if ( ! $post_id || ! in_array( $status, [ 'enabled', 'disabled' ], true ) ) {
				return new WP_REST_Response( [ 'error' => 'Invalid data' ], 400 );
			}

			update_post_meta( $post_id, 'responsive-site-builder-layout-status', $status );

			return new WP_REST_Response( [ 'success' => true ], 200 );
		}

		/**
		 * Responsive Site Builder Dynamic CSS.
		 *
		 * @param string $dynamic_css CSS.
		 * @return string
		 */
		public function responsive_site_builder_dynamic_css( $dynamic_css ) {

			$dynamic_css .= "
			@media (min-width: 922px) {
				.responsive-hide-display-device-desktop {
					display: none;
				}
			}
			
			@media (min-width: 545px) and (max-width: 921px) {
				.responsive-hide-display-device-tablet {
					display: none;
				}
			}

			@media (max-width: 544px) {
				.responsive-hide-display-device-mobile {
					display: none;
				}
			}
			.responsive-site-builder-footer-layout-preview .responsive-site-builder-footer {
				position: fixed;
				bottom: 0;
				left: 0;
				z-index: 9999;
				width: 100%;
			}
			";

			return $dynamic_css;
		}

		/**
		 * Dismiss Site Builder Child theme compatibility warning notice.
		 * 
		 * @since 3.3.0
		 */
		public function responsive_addons_dismiss_sb_childtheme_warning() {

			check_ajax_referer( 'responsive-addons-sb-childtheme-notice', 'nonce' );

			if( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( esc_html__( 'You do not have permission to dismiss the notice', 'responsive-addons' ) );
			}

			update_option( 'responsive_addons_sb_childtheme_warning_dismiss', true, false );
			wp_send_json_success();
		}

		/**
		 * Admin Body Classes
		 *
		 * @since 3.3.0
		 * @param string $classes Space separated class string.
		 * @return void
		 */
		public function admin_body_class( $classes = '' ) {
			$theme_builder_class = isset( $_GET['page'] ) && 'responsive-site-builder' === $_GET['page'] ? 'responsive-site-builder' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Fetching a $_GET value, no nonce available to validate.
			$classes            .= ' ' . $theme_builder_class . ' ';

			return $classes;
		}

		/**
		 * Redirect conditionally as per current page.
		 * Redirect to Site Builder page if user accessing default wp post type page.
		 *
		 * @since 3.3.0
		 * @return void
		 */
		public function responsive_addons_site_builder_redirect() {
			global $pagenow;
			$sb_layout_page = isset( $_GET['post_type'] ) && $_GET['post_type'] === 'resp-site-builder';
			if ( isset( $pagenow ) && 'edit.php' === $pagenow && $sb_layout_page ) {
				wp_safe_redirect( admin_url( 'admin.php?page=responsive-site-builder' ) );
				exit;
			}
		}

    }
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Responsive_Add_Ons_Site_Builder::get_instance();