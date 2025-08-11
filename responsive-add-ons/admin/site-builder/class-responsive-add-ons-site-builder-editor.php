<?php

/**
 * Responsive Add-ons Site Builder Editor
 * 
 * @package Responsive_Add_Ons
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Responsive_Add_Ons_Site_Builder_Editor' ) ) {

    /**
     * Class Responsive_Add_Ons_Site_Builder_Editor
     */
    class Responsive_Add_Ons_Site_Builder_Editor {

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
            add_action( 'enqueue_block_editor_assets', array( $this, 'responsive_site_builder_editor_enqueue_scripts' ) );
            add_action( 'init', array( $this, 'responsive_site_builder_post_type' ) );
			add_action( 'init', array( $this, 'register_meta_settings' ) );
        }

        /**
         * Enqueue editor scripts and styles for the site builder.
         */
        public function responsive_site_builder_editor_enqueue_scripts() {

            $post_type = get_post_type();

			if ( RESPONSIVE_BUILDER_POST_TYPE !== $post_type ) {
				return;
			}

            wp_enqueue_script( 'responsive-site-builder-editor-script', RESPONSIVE_ADDONS_SITE_BUILDER_URI . 'editor/build/index.js', array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ), RESPONSIVE_ADDONS_VER, true );
            wp_enqueue_style( 'responsive-site-builder-editor-style', RESPONSIVE_ADDONS_SITE_BUILDER_URI . 'editor/build/index.min.css', array(), RESPONSIVE_ADDONS_VER );
            // Localize script to pass data to JavaScript.
            wp_localize_script( 'responsive-site-builder-editor-script', 'responsiveAddonsSiteBuilderEditor', array(
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'title'         => __( 'Site Builder', 'responsive-addons' ),
                'layouts'       => $this->get_layout_type(),
				'deviceOptions' => $this->get_device_type(),
				'displayRules'  => Responsive_Add_Ons_Site_Builder_Display_Rules::get_location_selections(),
				'ajax_nonce'    => wp_create_nonce( 'responsive-sb-get-posts-by-query' ),
				'userRoles'     => Responsive_Add_Ons_Site_Builder_Display_Rules::get_user_selections(),
				'siteUrl'       => get_site_url(),
            ) );
        }

        /**
         * Create Site Builder custom post type
         * 
         * @since 3.3.0
         */
        public function responsive_site_builder_post_type() {
            $labels = array(
				'name'          => esc_html__( 'Site Builder', 'responsive-addons' ),
				'singular_name' => esc_html__( 'Site Builder Layout', 'responsive-addons' ),
				'search_items'  => esc_html__( 'Search Layout', 'responsive-addons' ),
				'all_items'     => esc_html__( 'All Layouts', 'responsive-addons' ),
				'edit_item'     => esc_html__( 'Edit Layout', 'responsive-addons' ),
				'view_item'     => esc_html__( 'View Layout', 'responsive-addons' ),
				'add_new'       => esc_html__( 'Add New', 'responsive-addons' ),
				'update_item'   => esc_html__( 'Update Layout', 'responsive-addons' ),
				'add_new_item'  => esc_html__( 'Add New', 'responsive-addons' ),
				'new_item_name' => esc_html__( 'New Layout Name', 'responsive-addons' ),
			);

			$args = array(
				'labels'              => $labels,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'query_var'           => true,
				'can_export'          => true,
				'show_in_admin_bar'   => true,
				'exclude_from_search' => true,
				'show_in_rest'        => true,
				'supports'            => apply_filters( 'responsive_site_builder_post_type_supports', array( 'title', 'editor', 'elementor', 'custom-fields' ) ),
				'rewrite'             => array( 'slug' => apply_filters( 'responsive_site_builder_post_type_rewrite_slug', 'responsive-site-builder' ) ),
			);

			register_post_type( RESPONSIVE_BUILDER_POST_TYPE, apply_filters( 'responsive_site_builder_post_type_args', $args ) );
        }

        /**
		 * Get all layout types.
		 *
		 * @since 3.3.0
		 */
		public function get_layout_type() {
			return array(
				'0'        => __( '— Select —', 'responsive-addons' ),
				'header'   => __( 'Header', 'responsive-addons' ),
				'footer'   => __( 'Footer', 'responsive-addons' ),
				'404-page' => __( '404 Page', 'responsive-addons' ),
			);
		}

		/**
		 * Get device types.
		 *
		 * @since 3.3.0
		 */
		public function get_device_type() {
			return array(
				'desktop' => __( 'Desktop', 'responsive-addons' ),
				'mobile'  => __( 'Mobile', 'responsive-addons' ),
				'both'    => __( 'Desktop + Mobile', 'responsive-addons' ),
			);
		}

		/**
		 * Register Post Meta options for Site Builder Post types.
		 *
		 * @since 3.3.0
		 */
		public function register_meta_settings() {
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout',
				array(
					'single'        => true,
					'type'          => 'string',
					'auth_callback' => '__return_true',
					'show_in_rest'  => true,
				)
			);

			// Register Meta for Header Sticky Settings.
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout-header',
				array(
					'single'        => true,
					'type'          => 'object',
					'auth_callback' => '__return_true',
					'default'       => array( 
						'sticky' => '',
						'sticky-header-on-devices' => 'desktop' 
					),
					'show_in_rest'  => array(
						'schema' => array(
							'type'       => 'object',
							'properties' => array(
								'sticky' => array(
									'type' => 'string',
								),
								'sticky-header-on-devices' => array(
									'type' => 'string',
								),
							),
						),
					),
				)
			);

			// Register Meta for Layout Display Conditions.
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout-location',
				array(
					'single'        => true,
					'type'          => 'object',
					'default'       => array(
						'rule'         => array(),
						'specific'     => array(),
						'specificText' => array(),
					),
					'auth_callback' => '__return_true',
					'show_in_rest'  => array(
						'schema' => array(
							'type'       => 'object',
							'properties' => array(
								'rule'         => array(
									'type' => 'array',
								),
								'specific'     => array(
									'type' => 'array',
								),
								'specificText' => array(
									'type' => 'array',
								),
							),
						),
					),
				)
			);

			// Register Meta for Layout Do Not Display Conditions.
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout-exclusion',
				array(
					'single'        => true,
					'type'          => 'object',
					'default'       => array(
						'rule'         => array(),
						'specific'     => array(),
						'specificText' => array(),
					),
					'auth_callback' => '__return_true',
					'show_in_rest'  => array(
						'schema' => array(
							'type'       => 'object',
							'properties' => array(
								'rule'         => array(
									'type' => 'array',
								),
								'specific'     => array(
									'type' => 'array',
								),
								'specificText' => array(
									'type' => 'array',
								),
							),
						),
					),
				)
			);

			// Register Meta for Layout User Roles.
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout-users',
				array(
					'single'        => true,
					'type'          => 'array',
					'default'       => array( 'all' ),
					'auth_callback' => '__return_true',
					'show_in_rest'  => array(
						'schema' => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
							),
						),
					),
				)
			);

			// Register Meta for Layout responsive visibility.
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout-display-device',
				array(
					'single'        => true,
					'type'          => 'array',
					'auth_callback' => '__return_true',
					'default'       => array( 'desktop', 'mobile', 'tablet' ),
					'show_in_rest'  => array(
						'schema' => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
							),
						),
					),
				)
			);

			// Register Meta for Layout Time Duration.
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout-time-duration',
				array(
					'single'        => true,
					'type'          => 'object',
					'auth_callback' => '__return_true',
					'show_in_rest'  => array(
						'schema' => array(
							'type'       => 'object',
							'properties' => array(
								'enabled'  => array(
									'type' => 'string',
								),
								'start-dt' => array(
									'type' => 'string',
								),
								'end-dt'   => array(
									'type' => 'string',
								),
							),
						),
					),
				)
			);

			// Register Meta for Footer Sticky Settings.
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout-footer',
				array(
					'single'        => true,
					'type'          => 'object',
					'auth_callback' => '__return_true',
					'default'       => array( 
						'sticky' => '',
						'sticky-footer-on-devices' => 'desktop' 
					),
					'show_in_rest'  => array(
						'schema' => array(
							'type'       => 'object',
							'properties' => array(
								'sticky' => array(
									'type' => 'string',
								),
								'sticky-footer-on-devices' => array(
									'type' => 'string',
								),
							),
						),
					),
				)
			);

			// Register Meta for 404 Settings.
			register_post_meta(
				RESPONSIVE_BUILDER_POST_TYPE,
				'responsive-site-builder-layout-404',
				array(
					'single'        => true,
					'type'          => 'object',
					'auth_callback' => '__return_true',
					'show_in_rest'  => array(
						'schema' => array(
							'type'       => 'object',
							'properties' => array(
								'disable_header' => array(
									'type' => 'string',
								),
								'disable_footer' => array(
									'type' => 'string',
								),
							),
						),
					),
				)
			);
		}
    }
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Responsive_Add_Ons_Site_Builder_Editor::get_instance();