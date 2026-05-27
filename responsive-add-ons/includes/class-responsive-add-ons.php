<?php
/**
 * Responsive Addons setup
 *
 * @package Responsive_Addons
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Responsive_Add_Ons Class.
 *
 * @class Responsive_Add_Ons
 */
class Responsive_Add_Ons {

	/**
	 * Options
	 *
	 * @since 1.0.0
	 * @var   array Options
	 */
	public $options;

	/**
	 * Options
	 *
	 * @since 1.0.0
	 * @var   array Plugin Options
	 */
	public $plugin_options;

	/**
	 * API Url
	 *
	 * @since 2.0.0
	 * @var   string API Url
	 */
	public static $api_url;

	/**
	 * RST Blocks API Url
	 *
	 * @since 2.9.1
	 * @var   string API Url
	 */
	public static $rst_blocks_api_url;

	/**
	 * Favorite Sites
	 *
	 * @since 2.8.6
	 * @var   array Favorite Sites
	 */

	public static $new_favorites;

	/**
	 * The cyberchimps app auth instance.
	 *
	 * @var Responsive_Add_Ons_App_Auth
	 */
	public $cc_app_auth;

	/**
	 * Custom Font CSS.
	 *
	 * @since 3.0.2
	 * @var string $font_css
	 */
	protected $font_css = '';
	/**
	 * Indicates whether the Responsive Add-Ons plugin has been activated.
	 *
	 * @var bool
	 */
	private $responsive_activated = false;
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_notices', array( $this, 'add_theme_installation_notice' ), 1 );
		add_action( 'admin_notices', array( $this, 'admin_notices_product_welcome_banner' ), 1 );
		add_action( 'wp_head', array( $this, 'responsive_head' ) );
		add_action( 'plugins_loaded', array( $this, 'responsive_addons_translations' ) );
		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", array( $this, 'plugin_settings_link' ) );

		$settings = self::raddons_get_white_label_settings();
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) ) {
			$this->load_responsive_addons_nav_walkers();
		}
		$this->load_responsive_customizer_settings();

		if ( ! get_option( 'rplus_custom_fonts_enable' ) ) {
			update_option( 'rplus_custom_fonts_enable', 'on' );
		}

		if ( ! get_option( 'rpro_woocommerce_enable' ) ) {
			update_option( 'rpro_woocommerce_enable', 'on' );
		}
		
		// Display Custom Fonts only when Responsive theme is active.
		$theme                      = wp_get_theme();
		$this->responsive_activated = 'Responsive' === $theme->get( 'Name' ) ? true : false;

		// Responsive Ready Site Importer Menu.
		add_action( 'admin_enqueue_scripts', array( $this, 'responsive_ready_sites_admin_enqueue_scripts' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'responsive_ready_sites_admin_enqueue_styles' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'responsive_addons_admin_enqueue_getting_started_scripts_styles' ) );

		add_action( 'elementor/editor/footer', array( $this, 'responsive_ready_sites_insert_templates' ) );

		add_action( 'elementor/editor/footer', array( $this, 'responsive_ready_sites_register_widget_scripts' ), 99 );

		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'responsive_ready_sites_elementor_styles' ) );

		add_action( 'elementor/preview/enqueue_styles', array( $this, 'responsive_ready_sites_elementor_styles' ) );

		if ( ! is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) && 'on' === get_option( 'rplus_custom_fonts_enable' ) && $this->responsive_activated ) {

			require_once plugin_dir_path( __DIR__ ) . 'includes/custom-fonts/class-responsive-add-ons-custom-fonts-taxonomy.php';

			add_action( 'admin_enqueue_scripts', array( $this, 'responsive_addons_enqueue_custom_fonts' ) );

			add_action( 'admin_menu', array( $this, 'responsive_addons_register_custom_fonts_menu' ), 101 );

			add_action( 'admin_head', array( $this, 'responsive_addons_custom_fonts_menu_highlight' ) );

			add_filter( 'manage_edit-' . Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug . '_columns', array( $this, 'responsive_addons_manage_columns' ) );

			add_action( Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug . '_add_form_fields', array( $this, 'responsive_addons_add_new_taxonomy_data' ) );

			add_action( Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug . '_edit_form_fields', array( $this, 'responsive_addons_edit_taxonomy_data' ) );

			add_action( 'edited_' . Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug, array( $this, 'responsive_addons_save_metadata' ) );
			add_action( 'create_' . Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug, array( $this, 'responsive_addons_save_metadata' ) );

			add_filter( 'upload_mimes', array( $this, 'responsive_addons_add_fonts_to_allowed_mimes' ) );
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'responsive_addons_update_mime_types' ), 10, 3 );

			add_action( 'responsive_render_fonts', array( $this, 'responsive_addons_render_fonts' ) );
			add_action( 'responsive_customizer_font_list', array( $this, 'responsive_addons_add_customizer_font_list' ) );
			add_action( 'wp_head', array( $this, 'responsive_addons_add_style' ) );
		}

		if ( is_admin() ) {
			if ( ! is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) && 'on' === get_option( 'rplus_custom_fonts_enable' ) && $this->responsive_activated ) {
				add_action( 'enqueue_block_assets', array( $this, 'responsive_addons_add_style' ) );
			}
			add_action( 'wp_ajax_responsive-ready-sites-activate-theme', array( $this, 'activate_theme' ) );
			add_action( 'wp_ajax_responsive-ready-sites-required-plugins', array( $this, 'required_plugin' ) );
			add_action( 'wp_ajax_responsive-ready-sites-install-required-pro-plugins', array( $this, 'install_pro_plugin' ) );
			add_action( 'wp_ajax_responsive-ready-sites-required-plugin-activate', array( $this, 'required_plugin_activate' ) );
			add_action('wp_ajax_responsive-ready-sites-install-plugin',array($this,'responsive_ready_sites_handle_install_plugin') );

			add_action( 'wp_ajax_responsive-ready-sites-remote-request', array( $this, 'remote_request' ) );
			add_action( 'wp_ajax_responsive-ready-sites-elementor_page_import_process', array( $this, 'elementor_page_import_process' ) );
			add_action( 'wp_ajax_responsive-ready-sites-backup-settings', array( $this, 'backup_settings' ) );
			add_action( 'wp_ajax_responsive-is-theme-active', array( $this, 'check_responsive_theme_active' ) );
			add_action( 'wp_ajax_get-responsive', array( $this, 'get_responsive_theme' ) );
			add_action( 'wp_ajax_responsive-sites-create-template', array( $this, 'create_elementor_template' ) );
			add_action( 'wp_ajax_nopriv_responsive_ready_sites_welcome_banner_dismiss_notice', array( $this, 'responsive_ready_sites_welcome_banner_dismiss_notice' ) );
			add_action( 'wp_ajax_responsive_ready_sites_welcome_banner_dismiss_notice', array( $this, 'responsive_ready_sites_welcome_banner_dismiss_notice' ) );

			// Dismiss admin notice.
			add_action( 'wp_ajax_responsive-notice-dismiss', array( $this, 'dismiss_notice' ) );
			// Check if Responsive Addons pro plugin is active.
			add_action( 'wp_ajax_check-responsive-add-ons-pro-installed', array( $this, 'is_responsive_pro_is_installed' ) );

			// Check if Responsive Addons pro license is active.
			add_action( 'wp_ajax_check-responsive-add-ons-pro-license-active', array( $this, 'is_responsive_pro_license_is_active' ) );

			// Update first time activation.
			add_action( 'wp_ajax_update-first-time-activation', array( $this, 'update_first_time_activation_variable' ) );

			add_action( 'wp_ajax_responsive-sites-favorite', array( $this, 'add_to_favorite' ) );
			add_action( 'wp_ajax_responsive-favorite-site-details', array( $this, 'get_favorite_template_site_details' ) );
			add_action( 'wp_ajax_responsive-update_all_sites_fav_status', array( $this, 'update_all_sites_fav_status' ) );
			add_filter( 'wp_prepare_themes_for_js', __CLASS__ . '::responsive_theme_white_label_update_branding' );
			add_filter( 'update_right_now_text', array( $this, 'admin_dashboard_page' ) );
			add_filter( 'gettext', array( $this, 'theme_gettext' ), 20, 3 );

			if ( ! empty( $settings['theme_icon_url'] ) ) {
				add_filter( 'responsive_admin_menu_icon', array( $this, 'update_admin_brand_logo' ) );
				add_filter( 'responsive_admin_menu_footer_icon', array( $this, 'update_admin_brand_logo' ) );
			}

			add_action( 'responsive_addons_getting_started_settings_tab', array( $this, 'responsive_addons_getting_started_settings_tab' ) );
			add_action( 'responsive_addons_getting_started_settings_tab_content', array( $this, 'responsive_addons_getting_started_settings_tab_content' ) );
			add_action( 'responsive_add_ons_white_label_section', array( $this, 'responsive_add_ons_white_label_section' ) );
			add_action( 'wp_ajax_responsive-pro-white-label-settings', array( $this, 'responsive_pro_white_label_settings' ) );
			add_action( 'wp_ajax_responsive-pro-enable-megamenu', array( $this, 'responsive_pro_enable_megamenu' ) );
			add_action( 'wp_ajax_responsive-pro-enable-woocommerce', array( $this, 'responsive_pro_enable_woocommerce' ) );
			add_action( 'wp_ajax_responsive-plus-enable-custom-fonts', array( $this, 'responsive_plus_enable_custom_fonts' ) );
			add_action( 'wp_ajax_responsive-plus-enable-site-builder', array( $this, 'responsive_plus_enable_site_builder' ) );

			// Get current installation import permissions.
			add_action( 'wp_ajax_responsive-ready-sites-get-import-capabilities', array( $this, 'responsive_addons_get_user_import_capabilities' ) );
		}

		if ( ! empty( $settings['theme_name'] ) ) {
			add_filter( 'responsive_theme_footer_theme_text', array( $this, 'white_label_theme_powered_by_text' ) );
		}
		if ( ! empty( $settings['plugin_website_uri'] ) ) {
			add_filter( 'responsive_theme_footer_link', array( $this, 'white_label_theme_powered_by_link' ) );
		}

		// Responsive Addons Menu.
		add_action( 'admin_menu', array( $this, 'responsive_add_ons_admin_menu' ) );
		add_action( 'responsive_register_admin_menu', array( $this, 'rst_register_admin_menu' ), 11 );

		// Remove all admin notices from specific pages.
		add_action( 'admin_init', array( $this, 'responsive_add_ons_on_admin_init' ) );

		$this->options        = get_option( 'responsive_theme_options' );
		$this->plugin_options = get_option( 'responsive_addons_options' );

		$this->load_responsive_sites_importer();
		$this->load_responsive_addons_cc_app_auth();

		add_action( 'responsive_addons_importer_page', array( $this, 'menu_callback' ) );

		// Add rating links to plugin's description in plugins table.
		add_filter( 'plugin_row_meta', array( $this, 'responsive_addons_rate_plugin_link' ), 10, 2 );

		// Add rating links to the Responsive Addons Admin Page.
		add_filter( 'admin_footer_text', array( $this, 'responsive_addons_admin_rate_us' ) );

		add_filter( 'plugin_action_links_responsive-add-ons/responsive-add-ons.php', array( $this, 'responsive_add_view_library_btn' ) );
		$theme = wp_get_theme();

		// Theme installed and activate.
		if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
			add_filter( 'plugin_action_links_responsive-add-ons/responsive-add-ons.php', array( $this, 'responsive_add_view_settings_btn' ) );
		}

		add_action( 'init', array( $this, 'app_output_buffer' ) );

		add_action( 'responsive_theme_setting_item', array( $this, 'responsive_theme_app_connection_setting_item' ) );

		add_action( 'responsive_add_ons_app_connection_setting', array( $this, 'responsive_add_ons_app_connection_setting_content' ) );
		if ( ! function_exists( 'responsive_pro_css' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'responsive_pro_css' ) );
		}

		if ( 'on' === get_option( 'rpro_woocommerce_enable' ) ) {
			add_action( 'after_setup_theme', array( $this, 'load_woocommerce' ) );
		}

		// Ask for review notice.
		add_action( 'admin_notices', array( $this, 'responsive_addons_ask_for_review_notice' ) );
		add_action( 'admin_init', array( $this, 'responsive_addons_notice_dismissed' ) );
		add_action( 'admin_init', array( $this, 'responsive_addons_notice_change_timeout' ) );

		self::set_api_url();
		self::set_rst_blocks_api_url();

		// Load class early in the plugin lifecycle
		if ( $this->responsive_addons_is_theme_site_builder_compatible() && 'on' === get_option( 'rplus_site_builder_enable' ) ) {
			require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/class-responsive-add-ons-site-builder.php';
		}
		// Update user consent.
		add_action( 'wp_ajax_responsive-addons-update-user-consent', array( $this, 'responsive_addons_update_user_consent' ) );


		add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'responsive_addons_enqueue_preview_script' ) );
	}

	/**
	 * Enqueue script for customizer preview.
	 */
	public function responsive_addons_enqueue_preview_script() {
		wp_enqueue_script( 'responsive-addons-preview-handler', RESPONSIVE_ADDONS_DIR_URL . 'admin/js/preview-handler.js', array(), RESPONSIVE_ADDONS_VER, true );
	}

	public function responsive_ready_sites_handle_install_plugin() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'responsive-add-ons' ) ] );
		}
		
		$slug = sanitize_text_field( $_POST['slug'] );

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/file.php';

		if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
			wp_send_json_success( [ 'message' => __( 'Plugin already installed.', 'responsive-add-ons' ) ] );
		}

		$api = plugins_api( 'plugin_information', [ 'slug' => $slug, 'fields' => [ 'sections' => false ] ] );

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( [ 'message' => $api->get_error_message() ] );
		}

		if ( empty( $api->download_link ) ) {
			wp_send_json_error( [ 'message' => __( 'No download link found for this plugin.', 'responsive-add-ons' ) ] );
		}

		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success( [ 'message' => __( 'Plugin installed successfully.', 'responsive-add-ons' ) ] );
	}

	/**
	 * Ask for Review.
	 */
	public function responsive_addons_ask_for_review_notice() {
		if ( isset( $_GET['page'] ) && ( 'responsive' === $_GET['page'] ) ) {
			return;
		}

		if ( false === get_option( 'responsive_addons_review_notice' ) ) {
			set_transient( 'responsive_addons_ask_review_flag', true, DAY_IN_SECONDS * 7 );
			update_option( 'responsive_addons_review_notice', true );
		} elseif ( false === (bool) get_transient( 'responsive_addons_ask_review_flag' ) && false === get_option( 'responsive_addons_review_notice_dismissed' ) ) {

			$image_path = RESPONSIVE_ADDONS_DIR_URL . 'admin/images/svgs/responsive-starter-templates-thumbnail.svg';
			printf(
				'<div class="notice notice-warning rst-ask-for-review-notice">
					<div class="rst-ask-for-review-notice-container">
						<div class="rst-notice-image">
							<img src="%1$s" class="custom-logo" alt="Responsive Addons for Elementor" itemprop="logo">
						</div>
						<div class="rst-notice-content">
							<div class="rst-notice-heading">
								%3$s
							</div>
							%4$s<br />
							<div class="rst-review-notice-container">
								<a href="%2$s" class="responsive-notice-close responsive-review-notice button-primary" target="_blank">
								%5$s
								</a>
								<span class="dashicons dashicons-calendar"></span>
								<a href="?responsive-addons-review-notice-change-timeout=true" data-repeat-notice-after="60" class="responsive-notice-close responsive-review-notice">
								%6$s
								</a>
								<span class="dashicons dashicons-smiley"></span>
								<a href="?responsive-addons-notice-dismissed=true" class="responsive-notice-close responsive-review-notice">
								%7$s
								</a>
							</div>
						</div>
					</div>
					<div class="rst-review-notice-dismiss">
						<a href="?responsive-addons-notice-dismissed=true"><span class="dashicons dashicons-no"></span></a>
					</div>
				</div>',
				esc_url( $image_path ),
				'https://wordpress.org/support/plugin/responsive-add-ons/reviews/#new-post',
				esc_html__( 'Hello! Seems like you have used Responsive Starter Templates plugin to build this website — Thanks a ton!', 'responsive-add-ons' ),
				esc_html__( 'Could you please do us a BIG favor and give it a 5-star rating on WordPress? This would boost our motivation and help other users make a comfortable decision while choosing the Responsive Starter Templates plugin.', 'responsive-add-ons' ),
				esc_html__( 'Ok, you deserve it', 'responsive-add-ons' ),
				esc_html__( 'Nope, maybe later', 'responsive-add-ons' ),
				esc_html__( 'I already did', 'responsive-add-ons' )
			);
			do_action( 'tag_review' );
		}
	}

	/**
	 * Removed Ask For Review Admin Notice when dismissed.
	 */
	public function responsive_addons_notice_dismissed() {
		if ( isset( $_GET['responsive-addons-notice-dismissed'] ) ) {
			update_option( 'responsive_addons_review_notice_dismissed', true );
			wp_safe_redirect( remove_query_arg( array( 'responsive-addons-notice-dismissed' ), wp_get_referer() ) );
		}
	}

	/**
	 * Removed Ask For Review Admin Notice when dismissed.
	 */
	public function responsive_addons_notice_change_timeout() {
		if ( isset( $_GET['responsive-addons-review-notice-change-timeout'] ) ) {
			set_transient( 'responsive_addons_ask_review_flag', true, DAY_IN_SECONDS );
			wp_safe_redirect( remove_query_arg( array( 'responsive-addons-review-notice-change-timeout' ), wp_get_referer() ) );
		}
	}

	/**
	 * Updates the variable defined for first time activation.
	 */
	public function update_first_time_activation_variable() {
		update_option( 'ra_first_time_activation', false );
	}

	/**
	 * Loads Responsive Nav Walkers.
	 */
	public function load_responsive_addons_nav_walkers() {
		if ( ! class_exists( 'Responsive_Addons_Custom_Nav_Walker' ) ) {
			require_once plugin_dir_path( __DIR__ ) . '/includes/megamenu/class-responsive-addons-nav-walker.php';
			require_once plugin_dir_path( __DIR__ ) . '/includes/megamenu/class-responsive-addons-custom-nav-walker.php';
		}
	}

	/**
	 * Loads Responsive Woocommerce Customizer Settings.
	 */
	public function load_responsive_customizer_settings() {

		/**
		 * Responsive Addons Pro Customizer Controls.
		 */
		require plugin_dir_path( __FILE__ ) . 'customizer/class-responsive-addons-pro-customizer-controls.php';

		$theme = wp_get_theme();

		if ( 'on' === get_option( 'rpro_woocommerce_enable' ) && ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) ) {
			/**
			 * The class responsible for loading the Woocommerce Typography options
			 */
			if ( ! class_exists( 'Responsive_Addons_Woocommerce_Typography' ) ) {
				require_once plugin_dir_path( __DIR__ ) . 'includes/compatibility/woocommerce/customizer/settings/class-responsive-addons-woocommerce-typography.php';
			}

			/**
			 * The class responsible for loading the Shop Pagination options
			 */
			if ( ! class_exists( 'Responsive_Addons_Woocommerce_Shop_Pagination' ) ) {
				require_once plugin_dir_path( __DIR__ ) . 'includes/compatibility/woocommerce/customizer/settings/class-responsive-addons-woocommerce-shop-pagination.php';
			}

			/**
			 * The class responsible for loading the Breadcrumb and Toolbar disable options
			 */
			if ( ! class_exists( 'Responsive_Addons_Woocommerce_Product_Catalog' ) ) {
				require_once plugin_dir_path( __DIR__ ) . 'includes/compatibility/woocommerce/customizer/settings/class-responsive-addons-woocommerce-product-catalog.php';
			}

			/**
			 * The class responsible for loading the Header Cart Icon options
			 */
			if ( ! class_exists( 'Responsive_Addons_Woocommerce_Cart' ) ) {
				require_once plugin_dir_path( __DIR__ ) . 'includes/compatibility/woocommerce/customizer/settings/class-responsive-addons-woocommerce-cart.php';
			}

			/**
			 * The class responsible for loading the Woocommerce Typography options
			 */
			if ( ! class_exists( 'Responsive_Addons_Woocommerce_Single_Product' ) ) {
				require_once plugin_dir_path( __DIR__ ) . 'includes/compatibility/woocommerce/customizer/settings/class-responsive-addons-woocommerce-single-product.php';
			}
		}

		/**
		 * The class responsible for loading the Custom Styles
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/customizer/custom-styles.php';

		/**
		 * The class responsible for loading the footer customizer options
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/compatibility/woocommerce/customizer/customizer.php';

		/**
		 * The class responsible for loading the helper functions for Customizer
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/customizer/helper.php';
	}

	/**
	 * Admin notice - install responsive theme
	 */
	public function add_theme_installation_notice() {

		$theme = wp_get_theme();
		global $pagenow;

		if ( 'index.php' !== $pagenow || 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme || $this->is_activation_theme_notice_expired() || is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) ) {
			return;
		}

		$class = 'responsive-notice notice notice-error';

		$theme_status = 'responsive-sites-theme-' . $this->get_theme_status();

		$image_path = RESPONSIVE_ADDONS_URI . 'admin/images/responsive-starter-templates-thumbnail.png';
		?>
			<div id="responsive-theme-activation" class="<?php echo esc_attr( $class ); ?>">
				<div class="responsive-addons-message-inner">
					<div class="responsive-addons-message-icon">
						<div class="">
							<img src="<?php echo esc_attr( $image_path ); ?>" alt="Responsive Starter Templates">
						</div>
					</div>
					<div class="responsive-addons-message-content">
						<p><?php echo esc_html( 'Responsive theme needs to be active to use the Responsive Starter Templates plugin.' ); ?> </p>
						<p class="responsive-addons-message-actions">
							<a href="#" class="<?php echo esc_attr( $theme_status ); ?> button button-primary" data-theme-slug="responsive">Install & Activate Now</a>
						</p>
					</div>
				</div>
			</div>
			<?php
	}

	/**
	 * Dashboard Welcome Banner.
	 */
	public function admin_notices_product_welcome_banner() {
		global $pagenow;
		if ( ( 'index.php' === $pagenow ) && ! get_transient( 'responsive_ready_sites_welcome_banner_dismissed_notice' ) ) {
			$image_path_close     = RESPONSIVE_ADDONS_URI . 'admin/images/close_icon.png';
			$image_path_logo      = RESPONSIVE_ADDONS_URI . 'admin/images/' . rawurlencode( 'group-22.svg' );
			$image_path_frame_9   = RESPONSIVE_ADDONS_URI . 'admin/images/' . rawurlencode( 'frame-9.png' );

			$features = array(
				__( 'Loads Blazing Fast', 'responsive-add-ons' ),
				__( 'Pre-designed pages', 'responsive-add-ons' ),
				__( 'Contact Form', 'responsive-add-ons' ),
				__( 'In-time Support', 'responsive-add-ons' ),
				__( 'Customizable Settings', 'responsive-add-ons' ),
				__( '1-Click Import', 'responsive-add-ons' ),
			);

			if ( ! is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) ) {
				?>
		<div id="responsive-welcome_banner-section" class="responsive-notice notice">
			<div class="reponsive-welcome_banner-welcome-section">
				<div class="rst-banner-art" aria-hidden="true">
					<div class="rst-banner-gradient rst-banner-gradient--3"></div>
					<div class="rst-banner-gradient rst-banner-gradient--1"></div>
					<div class="rst-banner-gradient rst-banner-gradient--2"></div>
					<img class="rst-banner-frame-9" src="<?php echo esc_attr( $image_path_frame_9 ); ?>" alt="">
				</div>
				<div class="reponsive-welcome_banner-welcome-section-content">
					<div class="rst-banner-logo-section">
						<img class="rst-banner-logo" src="<?php echo esc_attr( $image_path_logo ); ?>" alt="<?php echo esc_attr__( 'Responsive Plus', 'responsive-add-ons' ); ?>">
					</div>

					<div class="rst-banner-text-section">
						<div class="rst-banner-top-text-section">
							<div class="rst-banner-heading-section">
								<h1 class="reponsive-welcome_banner-welcome-section-text"><?php echo esc_html__( 'Welcome To Responsive Plus - Starter Templates', 'responsive-add-ons' ); ?></h1>
								<p class="reponsive-welcome_banner-welcome-section-tag"><?php echo esc_html__( 'Create professionally designed pixel-perfect websites in minutes.', 'responsive-add-ons' ); ?></p>
							</div>

							<div class="rst-banner-features-grid" role="list">
								<?php foreach ( $features as $feature_text ) : ?>
									<div class="rst-banner-feature" role="listitem">
										<span class="rst-banner-feature-icon" aria-hidden="true">
											<svg width="19" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
												<path d="M0 9C0 4.02944 4.02944 0 9 0C13.9706 0 18 4.02944 18 9C18 13.9706 13.9706 18 9 18C4.02944 18 0 13.9706 0 9Z" fill="url(#pattern0_45_477)"/>
												<path d="M13.2073 6L6.87727 12.33L4 9.45273" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<defs>
													<pattern id="pattern0_45_477" patternContentUnits="objectBoundingBox" width="1" height="1">
														<use xlink:href="#image0_45_477" transform="scale(0.0030303 0.00301205)"/>
													</pattern>
													<image id="image0_45_477" width="330" height="332" preserveAspectRatio="none" xlink:href="data:image/png;base64,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"/>
												</defs>
											</svg>
										</span>
										<span class="rst-banner-feature-text"><?php echo esc_html( $feature_text ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="rst-banner-cta-section">
							<a class="rst-banner-cta-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=responsive_add_ons' ) ); ?>">
								<?php echo esc_html__( "Let's Get Started", 'responsive-add-ons' ); ?>
								<svg width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none">
									<path d="M5 12 H17 M12 6 L18 12 L12 18"
										stroke="#FFFFFF"
										stroke-width="1.5"
										stroke-linecap="round"
										stroke-linejoin="round"/>
								</svg>
							</a>

							<a class="rst-banner-cta-secondary" href="<?php echo esc_url( 'https://cyberchimps.com/pricing/?utm_source=wpdash&utm_medium=RST_plugin&utm_campaign=intro_banner&utm_content=upgrade-to-pro' ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html__( 'Unlock Premium Templates', 'responsive-add-ons' ); ?>
							</a>
						</div>
					</div>
				</div>
				<a class="responsive-welcome_banner-close-icon" id="rst_welcome_banner_close_icon" href="#">
					<span class="dashicons dashicons-no" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php echo esc_html__( 'Dismiss notice', 'responsive-add-ons' ); ?></span>
				</a>
			</div>
		</div>
				<?php
			}
		}
	}

	/**
	 * Dismiss Dashboard Welcome Banner.
	 */
	public function responsive_ready_sites_welcome_banner_dismiss_notice() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );
		if ( isset( $_POST['action'] ) && 'responsive_ready_sites_welcome_banner_dismiss_notice' === $_POST['action'] ) {
			set_transient( 'responsive_ready_sites_welcome_banner_dismissed_notice', true, 10 * YEAR_IN_SECONDS );
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
		wp_die();
	}

	/**
	 * Is notice expired?
	 *
	 * @since 2.0.3
	 *
	 * @return boolean
	 */
	public static function is_activation_theme_notice_expired() {

		// Check the user meta status if current notice is dismissed.
		$meta_status = get_user_meta( get_current_user_id(), 'responsive-theme-activation', true );

		if ( empty( $meta_status ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Dismiss Notice.
	 *
	 * @since 2.0.3
	 * @return void
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'You are not allowed to activate the Theme', 'responsive-add-ons' ) );
		}

		$notice_id = ( isset( $_POST['notice_id'] ) ) ? sanitize_key( $_POST['notice_id'] ) : '';

		// check for Valid input.
		if ( ! empty( $notice_id ) ) {
			update_user_meta( get_current_user_id(), $notice_id, 'notice-dismissed' );
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Activate theme
	 *
	 * @since 2.0.3
	 * @return void
	 */
	public function activate_theme() {

		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_send_json_error( __( 'You are not allowed to activate the Theme', 'responsive-add-ons' ) );
		}

		switch_theme( 'responsive' );

		wp_send_json_success(
			array(
				'success' => true,
				'message' => __( 'Theme Activated', 'responsive-add-ons' ),
			)
		);
	}

	/**
	 * Get theme install, active or inactive status.
	 *
	 * @since 1.3.2
	 *
	 * @return string Theme status
	 */
	public function get_theme_status() {

		$theme = wp_get_theme();

		// Theme installed and activate.
		if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
			return 'installed-and-active';
		}

		// Theme installed but not activate.
		foreach ( (array) wp_get_themes() as $theme_dir => $theme ) {
			if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
				return 'installed-but-inactive';
			}
		}

		return 'not-installed';
	}

	/**
	 * Stuff to do when you activate
	 */
	public static function activate() {
	}

	/**
	 * Clean up after Deactivation
	 */
	public static function deactivate() {
	}

	/**
	 * Setter for $api_url
	 *
	 * @since  1.0.0
	 */
	public static function set_api_url() {
		self::$api_url = apply_filters( 'responsive_ready_sites_api_url', CCRS_URL . '/wp-json/wp/v2/' );
	}

	/**
	 * Setter for rst blocks $rst_blocks_api_url
	 *
	 * @since  2.9.1
	 */
	public static function set_rst_blocks_api_url() {
		self::$rst_blocks_api_url = apply_filters( 'rst_blocks_api_url', CCRS_URL . '/ccblocks/wp-json/wp/v2/' );
	}

	/**
	 * Hook into WP admin_init
	 * Responsive 1.x settings
	 *
	 * @param array $options Options.
	 */
	public function admin_init( $options ) {
		$this->init_settings();

		// Check for plugin update and schedule cron.
		$stored_version  = get_option( 'responsive_ready_sites_version' );
		$current_version = RESPONSIVE_ADDONS_VER;

		if ( version_compare( $stored_version, $current_version, '<' ) ) {
			if ( class_exists( 'Responsive_Ready_Sites_Batch_Processing' ) ) {
				Responsive_Ready_Sites_Batch_Processing::get_instance()->schedule_library_sync();
			}
			update_option( 'responsive_ready_sites_version', $current_version );
		}
	}

	/**
	 * Create plugin translations
	 */
	public function responsive_addons_translations() {
		// Load the text domain for translations.
	}

	/**
	 * Settings
	 */
	public function init_settings() {
		register_setting(
			'responsive_addons',
			'responsive_addons_options',
			array( $this, 'responsive_addons_sanitize' )
		);
	}

	/**
	 * Test to see if the current theme is Responsive
	 *
	 * @return bool
	 */
	public static function is_responsive() {
		$theme = wp_get_theme();
		$name     = $theme->get( 'Name' );
		$template = $theme->get( 'Template' );
		if ( 'Responsive' === $name || 'responsive' === $template || 'Responsive Pro' === $name || 'responsivepro' === $template ) {
			return true;
		}
		return false;
	}
	/**
	 * Add to wp head
	 */
	public function responsive_head() {

		// Test if using Responsive theme. If yes load from responsive options else load from plugin options.
		$responsive_options = ( $this->is_responsive() ) ? $this->options : $this->plugin_options;

		if ( ! empty( $responsive_options['google_site_verification'] ) ) {
			echo '<meta name="google-site-verification" content="' . esc_attr( $responsive_options['google_site_verification'] ) . '" />' . "\n";
		}

		if ( ! empty( $responsive_options['bing_site_verification'] ) ) {
			echo '<meta name="msvalidate.01" content="' . esc_attr( $responsive_options['bing_site_verification'] ) . '" />' . "\n";
		}

		if ( ! empty( $responsive_options['yahoo_site_verification'] ) ) {
			echo '<meta name="y_key" content="' . esc_attr( $responsive_options['yahoo_site_verification'] ) . '" />' . "\n";
		}

		if ( ! empty( $responsive_options['site_statistics_tracker'] ) ) {
			echo wp_kses_post( $responsive_options['site_statistics_tracker'] );
		}
	}

	/**
	 * Responsive Addons Sanitize
	 *
	 * @since 2.0.3
	 *
	 * @param string $input Input.
	 *
	 * @return string
	 */
	public function responsive_addons_sanitize( $input ) {

		$output = array();

		foreach ( $input as $key => $test ) {
			switch ( $key ) {
				case 'google_site_verification':
					$output[ $key ] = wp_filter_post_kses( $test );
					break;
				case 'yahoo_site_verification':
					$output[ $key ] = wp_filter_post_kses( $test );
					break;
				case 'bing_site_verification':
					$output[ $key ] = wp_filter_post_kses( $test );
					break;
				case 'site_statistics_tracker':
					$output[ $key ] = wp_kses_stripslashes( $test );
					break;

			}
		}

		return $output;
	}

	/**
	 * Add settings link to plugin activate page
	 *
	 * @param array $links Links.
	 *
	 * @return mixed
	 */
	public function plugin_settings_link( $links ) {
		$settings_link = '<a href="themes.php?page=responsive-add-ons">' . __( 'Settings', 'responsive-add-ons' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Menu callback
	 *
	 * @since 2.0.0
	 */
	public function menu_callback() {
		?>
			<div class="responsive-sites-menu-page-wrapper">
			<?php require_once RESPONSIVE_ADDONS_DIR . 'admin/partials/responsive-ready-sites-admin-display.php'; ?>
			</div>
			<?php
	}

	/**
	 * Load Responsive Ready Sites Importer
	 *
	 * @since 2.0.0
	 */
	public function load_responsive_sites_importer() {
		require_once RESPONSIVE_ADDONS_DIR . 'includes/importers/class-responsive-ready-sites-importer.php';
	}

	/**
	 * Load Responsive Addons Cyberchimps App Auth
	 *
	 * @since 2.9.2
	 */
	public function load_responsive_addons_cc_app_auth() {
		require_once RESPONSIVE_ADDONS_DIR . 'includes/class-responsive-add-ons-app-auth.php';
		$this->cc_app_auth = new Responsive_Add_Ons_App_Auth();

		require_once RESPONSIVE_ADDONS_DIR . 'includes/settings/class-responsive-add-ons-api.php';
		$respaddons_api = new Responsive_Add_Ons_Api();
	}

	/**
	 * Include Admin JS
	 *
	 * @param string $hook Hook.
	 *
	 * @since 2.0.0
	 */
	public function responsive_ready_sites_admin_enqueue_scripts( $hook = '' ) {

		$settings   = get_option( 'rpro_elementor_settings' );
		$theme_name = ! empty( $settings['theme_name'] ) ? mb_strtolower( $settings['theme_name'], 'UTF-8' ) : 'responsive';
		$theme_name = str_replace( [ ' ', '/' ], '-', $theme_name );

		$characters_to_remove = [ "'", '\\', '?', '|', '*', '"', '`' ];
		$theme_name           = str_replace( $characters_to_remove, '', $theme_name );
		$theme_name           = preg_replace( '/[^\p{L}\p{N}_-]/u', '', $theme_name );

		$pro_plugin_active_status = is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) ? true : false;
		
		$parts = explode('_page_responsive_add_ons', $hook);
		$encoded_part = isset($parts[0]) ? $parts[0] : '';
		$decoded_theme_name = urldecode($encoded_part);

		if ( ( 'toplevel_page_responsive_add_ons' === $hook || $theme_name . '_page_responsive_add_ons' === $hook  || $theme_name . '_page_responsive_add_ons' === $decoded_theme_name . '_page_responsive_add_ons') && empty( $_GET['action'] ) ) {
			wp_enqueue_media();
			wp_enqueue_style( 'imgareaselect' );
			wp_enqueue_script( 'imgareaselect' );
			wp_enqueue_script( 'responsive-ready-sites-admin-import-react-js', RESPONSIVE_ADDONS_URI . 'admin/template-import/react/build/index.js', array( 'react', 'react-dom', 'updates' ), RESPONSIVE_ADDONS_VER, true );
			wp_enqueue_style( 'toastr-css', RESPONSIVE_ADDONS_URI .'/admin/css/toastr.min.css', array(), RESPONSIVE_ADDONS_VER );
			wp_enqueue_script( 'toastr-js', RESPONSIVE_ADDONS_URI . '/admin/js/toastr.min.js', array( 'jquery' ), RESPONSIVE_ADDONS_VER, true );

			global $wcam_lib_responsive_addons;
			$instance_key = '';
			if ( ! empty( $wcam_lib_responsive_addons ) && isset( $wcam_lib_responsive_addons->wc_am_instance_id ) ) {
				$instance_key = $wcam_lib_responsive_addons->wc_am_instance_id;
			}

			$data = apply_filters(
				'responsive_sites_localize_vars',
				array(
					'debug'                           => ((defined('WP_DEBUG') && WP_DEBUG) || isset($_GET['debug'])) ? true : false, //phpcs:ignore
					'ajaxurl'                         => esc_url( admin_url( 'admin-ajax.php' ) ),
					'siteURL'                         => site_url(),
					'_ajax_nonce'                     => wp_create_nonce( 'responsive-addons' ),
					'XMLReaderDisabled'               => ! class_exists( 'XMLReader' ) ? true : false,
					'required_plugins'                => array(),
					'ApiURL'                          => self::$api_url,
					/* translators: %s is a template name */
					'importSingleTemplateButtonTitle' => __( 'Import "%s" Template', 'responsive-add-ons' ),
					'default_page_builder_sites'      => $this->get_sites_by_page_builder(),
					'strings'                         => array(
						'syncCompleteMessage'  => $this->get_sync_complete_message(),
						/* translators: %s is a template name */
						'importSingleTemplate' => __( 'Import "%s" Template', 'responsive-add-ons' ),
					),
					'dismiss'                         => __( 'Dismiss this notice.', 'responsive-add-ons' ),
					'syncTemplatesLibraryStart'       => '<span class="message">' . esc_html__( 'Syncing Responsive Starter Templates in the background. The process will complete in just a few seconds. We will notify you once done.', 'responsive-add-ons' ) . '</span>',
					'activated_first_time'            => get_option( 'ra_first_time_activation' ),
					'hasAppAuth'                      => $this->cc_app_auth->has_auth(),
					'isResponsiveProActive'           => $pro_plugin_active_status,
					'userGivenConsent'                => 'yes' === get_option( 'responsive_addons_contribution_consent', 'yes' ),
					'pluginVersion'                   => RESPONSIVE_ADDONS_VER,
					'admin_url'                       => admin_url(),
					'res_dash_url'                    => admin_url().'admin.php?page=responsive',
					'cc_app_url' 					  => CC_APP_URL,
					'_nonce'                          => wp_create_nonce( 'wp_rest' ),
					'site_url'                        => rawurlencode( get_site_url() ),
					'cookies'                         => $_COOKIE,
					'imageDir'                        => esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/' ),
					'pageBuilders'                    => $this->get_default_page_builders(),
					'favoriteSites'                   => get_option( 'responsive-sites-favorites', array() ),
					'elementorContainerSetting'       => get_option( 'elementor_experiment-container' ),
					'elementorActive'                 => is_plugin_active('elementor/elementor.php'),
					'elementorSettingsURL'            => esc_url( admin_url( 'admin.php?page=elementor-settings#tab-experiments' ) ),
					'themeStatus'                     => $this->get_theme_status(),
					'instance'                        => $instance_key,
					'version'						  => RESPONSIVE_ADDONS_VER,
					// 'responsiveTemplatesURL'          => esc_url( admin_url( 'admin.php?page=responsive_add_ons' ) ),
				)
			);

			wp_localize_script( 'responsive-ready-sites-admin-import-react-js', 'responsiveSitesAdmin', $data );
		} else {
			wp_enqueue_script( 'install-responsive-theme', RESPONSIVE_ADDONS_URI . 'admin/js/install-responsive-theme.js', array( 'jquery', 'updates' ), RESPONSIVE_ADDONS_VER, true );
			wp_enqueue_style( 'install-responsive-theme', RESPONSIVE_ADDONS_URI . 'admin/css/install-responsive-theme.css', null, RESPONSIVE_ADDONS_VER, 'all' );
			$data = apply_filters(
				'responsive_sites_install_theme_localize_vars',
				array(
					'installed'   => __( 'Installed! Activating..', 'responsive-add-ons' ),
					'activating'  => __( 'Activating..', 'responsive-add-ons' ),
					'activated'   => __( 'Activated! Reloading..', 'responsive-add-ons' ),
					'installing'  => __( 'Installing..', 'responsive-add-ons' ),
					'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) ),
					'_ajax_nonce' => wp_create_nonce( 'responsive-addons' ),
				)
			);
			wp_localize_script( 'install-responsive-theme', 'ResponsiveInstallThemeVars', $data );
		}
	}

	/**
	 * Get Sync Complete Message
	 *
	 * @since 2.0.0
	 * @param  boolean $echo Echo the message.
	 * @return mixed
	 */
	public function get_sync_complete_message( $echo = false ) {

		$message = __( 'Responsive Templates data refreshed!', 'responsive-add-ons' );
		if ( $echo ) {
			echo esc_html( $message );
		} else {
			return esc_html( $message );
		}
	}

	/**
	 * Include Admin css
	 *
	 * @since 2.0.0
	 * @param string $hook Hook.
	 */
	public function responsive_ready_sites_admin_enqueue_styles( $hook = '' ) {
		$settings   = get_option( 'rpro_elementor_settings' );
		$theme_name = ! empty( $settings['theme_name'] ) ? mb_strtolower( $settings['theme_name'], 'UTF-8' ) : 'responsive';
		$theme_name = str_replace( [ ' ', '/' ], '-', $theme_name );
		$characters_to_remove = [ "'", '\\', '?', '|', '*', '"', '`' ];
		$theme_name = str_replace( $characters_to_remove, '', $theme_name );
		$theme_name = preg_replace( '/[^\p{L}\p{N}_-]/u', '', $theme_name );
		
		$parts = explode('_page_responsive_add_ons', $hook);
		$encoded_part = isset($parts[0]) ? $parts[0] : '';
		$decoded_theme_name = urldecode($encoded_part);

		if ( 'toplevel_page_responsive_add_ons' === $hook || $theme_name . '_page_responsive_add_ons_go_pro' === $hook || $theme_name . '_page_responsive_add_ons' === $hook || $theme_name . '_page_responsive_add_ons' === $decoded_theme_name . '_page_responsive_add_ons') {
			// Responsive Ready Sites admin styles.
			wp_enqueue_style( 'responsive-addons-import-css', RESPONSIVE_ADDONS_URI . 'admin/template-import/react/build/output.css', false, RESPONSIVE_ADDONS_VER );
			wp_enqueue_style( 'responsive-ready-sites-admin' );
		}
	}

	/**
	 * Include Elementor Templates.
	 *
	 * @since 2.7.3
	 */
	public function responsive_ready_sites_insert_templates() {
		ob_start();
		require_once RESPONSIVE_ADDONS_DIR . 'admin/partials/responsive-elementor-templates.php';
		ob_end_flush();
	}

	/**
	 * Include Elementor Admin JS.
	 *
	 * @since 2.7.3
	 */
	public function responsive_ready_sites_register_widget_scripts() {
		wp_enqueue_script( 'responsive-elementor-admin', RESPONSIVE_ADDONS_URI . 'admin/js/responsive-elementor-admin.js', array( 'jquery', 'wp-util', 'updates', 'jquery-ui-autocomplete', 'masonry', 'imagesloaded' ), RESPONSIVE_ADDONS_VER, true );

		wp_add_inline_script( 'responsive-elementor-admin', sprintf( 'var pagenow = "%s";', 'Responsive Starter Templates' ), 'after' );
		$license_status           = $this->responsive_pro_license_is_active() ? true : false;
		$pro_plugin_active_status = is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) ? true : false;
		$cc_app_auth              = $this->cc_app_auth->has_auth() ? true : false;
		$settings                 = get_option( 'reads_app_settings' );
		$user_plan                = $settings['account']['plan'] ?? 'free';

		$pro_purchase_url = 'https://cyberchimps.com/responsive-go-pro/?utm_source=free-to-pro&utm_medium=responsive-add-ons&utm_campaign=responsive-pro&utm_content=preview-ready-site';

		/* translators: %s are link. */
		$license_msg = sprintf( __( 'This is a Pro Template available with Responsive Pro. You can purchase it from <a href="%s" target="_blank">here</a>.', 'responsive-add-ons' ), esc_url( $pro_purchase_url ) );
		/* translators: %s are link. */
		$license_block_msg = sprintf( __( 'This is a Pro Block available with Responsive Pro. You can purchase it from <a href="%s" target="_blank">here</a>.', 'responsive-add-ons' ), esc_url( $pro_purchase_url ) );

		$data = apply_filters(
			'responsive_sites_render_localize_vars',
			array(
				'plugin_name'                 => 'Responsive Starter Templates',
				'version'                     => RESPONSIVE_ADDONS_VER,
				'default_page_builder'        => 'elementor',
				'license_status'              => $license_status,
				'proActivated'                => $pro_plugin_active_status,
				'ccAppAuth'                   => $cc_app_auth,
				'addonsPlan'                  => $user_plan,
				'ajaxurl'                     => esc_url( admin_url( 'admin-ajax.php' ) ),
				'default_page_builder_sites'  => $this->get_sites_by_elementor(),
				'default_page_builder_blocks' => $this->get_rst_blocks_by_elementor(),
				'ApiURL'                      => self::$api_url,
				'_ajax_nonce'                 => wp_create_nonce( 'responsive-addons' ),
				'isPro'                       => defined( 'RESPONSIVE_ADDONS_PRO_VERSION' ) ? true : false,
				'license_msg'                 => $license_msg,
				'license_block_msg'           => $license_block_msg,
				'dismiss_text'                => esc_html__( 'Dismiss', 'responsive-add-ons' ),
				'noPlugins'                   => __( 'No Plugins Required', 'responsive-add-ons' ),
				'syncCompleteMessage'         => __( 'Template library refreshed!', 'responsive-add-ons' ),
				'getProText'                  => __( 'Upgrade to Pro!', 'responsive-add-ons' ),
				'getProURL'                   => esc_url( 'https://cyberchimps.com/responsive-go-pro/?utm_source=free-to-pro&utm_medium=responsive-add-ons&utm_campaign=responsive-pro&utm_content=preview-ready-site' ),
				'getREAURL'                   => esc_url( 'https://cyberchimps.com/elementor-widgets/docs/how-to-install-activate-the-responsive-elementor-addons/' ),
				'siteURL'                     => site_url(),
				'template'                    => esc_html__( 'Template', 'responsive-add-ons' ),
				'install_plugin_text'         => esc_html__( 'Install Required Plugins', 'responsive-add-ons' ),
				'isREAActivated'              => $this->is_rea_activated(),
				'blockSiteURL'                => self::$rst_blocks_api_url,
				'blockCategories'             => $this->block_categories(),
				'rstHasBlocksCount'           => $this->rst_add_blocks_data(),				  
			)
		);

		wp_localize_script( 'responsive-elementor-admin', 'responsiveElementorSites', $data );

		wp_enqueue_script(
			'responsive-add-ons-getting-started-jsfile',
			RESPONSIVE_ADDONS_URI . 'admin/js/responsive-add-ons-getting-started.js',
			array( 'jquery' ),
			RESPONSIVE_ADDONS_VER,
			true
		);

		$data = array(
			'ajaxurl'     => admin_url( 'admin-ajax.php' ),
			'ccAppURL'    => CC_APP_URL,
			'_ajax_nonce' => wp_create_nonce( 'responsive-addons' ),
			'_nonce'      => wp_create_nonce( 'wp_rest' ),
			'site_url'    => rawurlencode( get_site_url() ),
			'cookies'     => $_COOKIE,
		);

		wp_localize_script( 'responsive-add-ons-getting-started-jsfile', 'responsiveAddonsGettingStarted', $data );
	}

	/**
	 * RST Block Categories.
	 *
	 * @since 2.9.1
	 */
	public function block_categories() {
		return array(
			__( 'About', 'responsive-add-ons' ),
			__( 'Team', 'responsive-add-ons' ),
			__( 'Testimonial', 'responsive-add-ons' ),
			__( 'Hero', 'responsive-add-ons' ),
			__( 'Call to Action', 'responsive-add-ons' ),
		);
	}

	/**
	 * Check if REA is activated.
	 *
	 * @since 2.7.3
	 */
	public function is_rea_activated() {
		$rea_slug = 'responsive-elementor-addons/responsive-elementor-addons.php';
		if ( is_plugin_active( $rea_slug ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get API params
	 *
	 * @since 2.7.3
	 * @return array
	 */
	public function responsive_sites_get_api_params() {
		return apply_filters(
			'responsive_sites_api_params',
			array(
				'purchase_key'    => '',
				'site_url'        => get_site_url(),
				'per-page'        => 20,
				'template_status' => '',
				'version'         => RESPONSIVE_ADDONS_VER,
			)
		);
	}

	/**
	 * Get Elementor Sites
	 *
	 * @since 2.7.3
	 *
	 * @return array page builder sites.
	 */
	public function get_sites_by_elementor() {
		$sites_and_pages = $this->get_all_sites();
		$elementor_sites = array();
		if ( ! empty( $sites_and_pages ) ) {
			$page_builder_keys = wp_list_pluck( $sites_and_pages, 'page_builder' );
			foreach ( $page_builder_keys as $site_id => $page_builder ) {
				if ( 'elementor' === $page_builder ) {
					$elementor_sites[ $site_id ] = $sites_and_pages[ $site_id ];
				}
			}
		}

		return $elementor_sites;
	}

	/**
	 * Get Elementor Based RST Blocks.
	 *
	 * @since 2.9.1
	 *
	 * @return array page builder rst blocks.
	 */
	public function get_rst_blocks_by_elementor() {
		$blocks_and_pages = $this->get_all_rst_blocks();
		$elementor_sites  = array();
		if ( ! empty( $blocks_and_pages ) ) {
			$page_builder_keys = wp_list_pluck( $blocks_and_pages, 'page_builder' );
			foreach ( $page_builder_keys as $site_id => $page_builder ) {
				if ( 'elementor' === $page_builder ) {
					$elementor_sites[ $site_id ] = $blocks_and_pages[ $site_id ];
				}
			}
		}

		return $elementor_sites;
	}

	/**
	 * Elementor Templates Request
	 *
	 * @since 2.7.3
	 */
	public function remote_request() {

		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-add-ons' ) );
		}

		$api_url = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';

		$api_url = add_query_arg( $this->responsive_sites_get_api_params(), $api_url );

		$response = wp_safe_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( wp_remote_retrieve_body( $response ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		wp_send_json_success( $data );
	}

	/**
	 * Elementor Batch Process via AJAX
	 *
	 * @since 2.7.3
	 */
	public function elementor_page_import_process() {

		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-add-ons' ) );
		}

		$api_url = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';

		$response = wp_safe_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( wp_remote_retrieve_body( $response ) );
		}

		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body, true );

		if ( ! isset( $data['post-meta']['_elementor_data'] ) ) {
			wp_send_json_error( __( 'Invalid Post Meta', 'responsive-add-ons' ) );
		}

		$meta = json_decode( $data['post-meta']['_elementor_data'], true );

		$meta = json_decode( $meta, true );

		$post_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';

		if ( empty( $post_id ) || empty( $meta ) ) {
			wp_send_json_error( __( 'Invalid Post ID or Elementor Meta', 'responsive-add-ons' ) );
		}

		$import      = new \Elementor\TemplateLibrary\Responsive_Ready_Sites_Batch_Processing_Elementor();
		$import_data = $import->responsive_import_post_meta( $post_id, $meta );

		wp_send_json_success( $import_data );
	}

	/**
	 * Import Post Meta
	 *
	 * @since 2.7.3
	 *
	 * @param  integer $post_id  Post ID.
	 * @param  array   $metadata  Post meta.
	 * @return void
	 */
	public function import_template_meta( $post_id, $metadata ) {

		$metadata = (array) $metadata;

		foreach ( $metadata as $meta_key => $meta_value ) {

			if ( $meta_value ) {

				if ( '_elementor_data' === $meta_key ) {

					$raw_data = json_decode( $meta_value, true );
					$raw_data = json_decode( $raw_data, true );

					if ( is_array( $raw_data ) ) {
						$raw_data = wp_slash( wp_json_encode( $raw_data ) );
					} else {
						$raw_data = wp_slash( $raw_data );
					}
				} elseif ( is_serialized( $meta_value, true ) ) {

						$raw_data = maybe_unserialize( stripslashes( $meta_value ) );
				} elseif ( is_array( $meta_value ) ) {
					$raw_data = json_decode( stripslashes( $meta_value ), true );
				} else {
					$raw_data = $meta_value;
				}

				update_post_meta( $post_id, $meta_key, $raw_data );
			}
		}
	}

	/**
	 * Include Elementor Admin CSS.
	 *
	 * @since 2.7.3
	 */
	public function responsive_ready_sites_elementor_styles() {
		wp_enqueue_style( 'responsive-elementor-admin', RESPONSIVE_ADDONS_URI . 'admin/css/responsive-elementor-admin.css', RESPONSIVE_ADDONS_VER, true );
	}

	/**
	 * Backup existing settings.
	 */
	public function backup_settings() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'User does not have permission!', 'responsive-add-ons' ) );
		}

		$file_name    = 'responsive-ready-sites-backup-' . date( 'd-M-Y-h-i-s' ) . '.json'; // phpcs:ignore
		$old_settings = get_option( 'responsive_theme_options', array() );

		$upload_dir  = Responsive_Ready_Sites_Importer_Log::get_instance()->log_dir();
		$upload_path = trailingslashit( $upload_dir['path'] );
		$log_file    = $upload_path . $file_name;
		$file_system = Responsive_Ready_Sites_Importer_Log::get_instance()->get_filesystem();

		// If file Write fails.
		if ( false === $file_system->put_contents( $log_file, wp_json_encode( $old_settings ), FS_CHMOD_FILE ) ) {
			update_option( 'responsive_ready_sites_' . $file_name, $old_settings );
		}

		wp_send_json_success();
	}

	/**
	 * Get Active site data
	 */
	public function get_active_site_data() {
		$current_active_site = get_option( 'responsive_current_active_site' );
		return $current_active_site;
	}


	/**
	 * Required Plugin
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function required_plugin() {

		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		$response = array(
			'active'       => array(),
			'inactive'     => array(),
			'notinstalled' => array(),
			'proplugins'   => array(),
		);

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( $response );
		}

		$required_plugins_count = ( isset( $_POST['required_plugins'] ) ) ? count( $_POST['required_plugins'] ) : array();
		$required_pro_plugins   = array();
		if ( isset( $_POST['required_pro_plugins'] ) && is_array( $_POST['required_pro_plugins'] ) ) {
			// Recursively sanitize all values in the array.
			$required_pro_plugins = map_deep( $required_pro_plugins, 'sanitize_text_field' );
		}

		if ( $required_plugins_count > 0 ) {

			for ( $i = 0; $i < $required_plugins_count; $i++ ) {
				$name = isset( $_POST['required_plugins'][ $i ]['name'] ) ? sanitize_text_field( wp_unslash( $_POST['required_plugins'][ $i ]['name'] ) ) : '';
				$slug = isset( $_POST['required_plugins'][ $i ]['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['required_plugins'][ $i ]['slug'] ) ) : '';
				$init = isset( $_POST['required_plugins'][ $i ]['init'] ) ? sanitize_text_field( wp_unslash( $_POST['required_plugins'][ $i ]['init'] ) ) : '';

				$plugin = array(
					'name' => $name,
					'slug' => $slug,
					'init' => $init,
				);

				if ( file_exists( WP_PLUGIN_DIR . '/' . $init ) && is_plugin_inactive( $init ) ) {

					$response['inactive'][] = $plugin;

				} elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $init ) ) {

					$response['notinstalled'][] = $plugin;

				} else {
					$response['active'][] = $plugin;
				}
			}
		}

		if ( is_array( $required_pro_plugins ) && count( $required_pro_plugins ) > 0 ) {
			foreach ( $required_pro_plugins as $key => $plugin ) {
				$response['proplugins'][] = $plugin;
			}
		}

		// Send response.
		wp_send_json_success(
			array(
				'required_plugins' => $response,
			)
		);
	}

	/**
	 * Install Pro plugins.
	 *
	 * @since     1.0.0
	 */
	public function install_pro_plugin() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'Error: You don\'t have the required permissions to install plugins.', 'responsive-add-ons' ),
				)
			);
		}
		$pro_plugins = array();
		if ( isset( $_POST['pro_plugin'] ) && is_array( $_POST['pro_plugin'] ) ) {
			// Recursively sanitize all values in the array.
			$pro_plugins = map_deep( $pro_plugins, 'sanitize_text_field' );
		}

		foreach ( $pro_plugins as $plugin ) {
			$plugin_slug = $plugin['slug'];
			$plugin_init = $plugin['init'];
			if ( self::is_plugin_installed( $plugin_init ) ) {
				if ( ! is_plugin_active( $plugin_init ) ) {
					if ( 'responsive-elementor-addons' === $plugin_slug ) {
						$activate = activate_plugin( $plugin_init, '', false, false );
					} else {
						$activate = activate_plugin( $plugin_init, '', false, true );
					}
				}
			} elseif ( 'responsive-elementor-addons' === $plugin_slug ) {
					$plugin_zip = 'https://cyberchimps.com/wp-content/downloads_cc/' . $plugin_slug . '.zip';
					$installed  = self::install_plugin( $plugin_zip );
				if ( $installed ) {
					if ( ! function_exists( 'activate_plugin' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					$activate = activate_plugin( $plugin_init, '', false, false );
				}
			}
		}
		wp_send_json_success(
			array(
				'pro_plugins_install' => true,
			)
		);
	}

	/**
	 * Check is plugin is installed.
	 *
	 * @param (String) $plugin_init Plugin Init.
	 * @since     1.0.0
	 */
	public function is_plugin_installed( $plugin_init ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		if ( ! empty( $all_plugins[ $plugin_init ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Install Plugin.
	 *
	 * @param (String) $plugin_zip Plugin zip.
	 * @since     1.0.0
	 */
	public function install_plugin( $plugin_zip ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		wp_cache_flush();

		$upgrader  = new Plugin_Upgrader();
		$installed = $upgrader->install( $plugin_zip );

		return $installed;
	}

	/**
	 * Required Plugin Activate
	 *
	 * @since 1.0.0
	 */
	public function required_plugin_activate() {

		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {

			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'Error: You don\'t have the required permissions to install plugins.', 'responsive-add-ons' ),
				)
			);
		}

		if ( ! isset( $_POST['init'] ) || empty( $_POST['init'] ) ) {

			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'Plugins data is missing.', 'responsive-add-ons' ),
				)
			);
		}

		$plugin_init = ( isset( $_POST['init'] ) ) ? wp_kses_post( wp_unslash( $_POST['init'] ) ) : '';
		$silent      = ( strpos( $plugin_init, 'give' ) !== false ) ? false : true;

		$activate = activate_plugin( $plugin_init, '', false, $silent );

		if ( is_wp_error( $activate ) ) {

			wp_send_json_error(
				array(
					'success' => false,
					'message' => $activate->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'success' => true,
				'message' => __( 'Plugin Activated', 'responsive-add-ons' ),
			)
		);
	}


	/**
	 * Check if Responsive Addons Pro is installed.
	 */
	public function is_responsive_pro_is_installed() {
		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		$responsive_pro_slug = 'responsive-addons-pro/responsive-addons-pro.php';
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		if ( ! empty( $all_plugins[ $responsive_pro_slug ] ) ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Check if Responsive Addons Pro is installed.
	 */
	public function responsive_pro_is_installed() {
		$responsive_pro_slug = 'responsive-addons-pro/responsive-addons-pro.php';
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();

		if ( ! empty( $all_plugins[ $responsive_pro_slug ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if Responsive Addons Pro License is Active.
	 */
	public function is_responsive_pro_license_is_active() {
		global $wcam_lib_responsive_pro;
		if ( is_null( $wcam_lib_responsive_pro ) ) {
			wp_send_json_error();
		}
		$license_status = $wcam_lib_responsive_pro->license_key_status();

		if ( ! empty( $license_status['data']['activated'] ) && $license_status['data']['activated'] ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Check if Responsive Addons Pro License is Active.
	 */
	public function responsive_pro_license_is_active() {
		global $wcam_lib_responsive_pro;
		if ( is_null( $wcam_lib_responsive_pro ) ) {
			return false;
		}
		$license_status = $wcam_lib_responsive_pro->license_key_status();

		if ( ! empty( $license_status['data']['activated'] ) && $license_status['data']['activated'] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adding the theme menu page
	 */
	public function responsive_addons_admin_page() {

		if ( $this->is_responsive() ) {
			$menu_title = 'Responsive Templates';
		} else {
			$menu_title = 'Responsive Starter Templates';
		}

		add_theme_page(
			'Responsive Website Templates',
			$menu_title,
			'manage_options',
			'responsive-add-ons',
			array( $this, 'responsive_add_ons' )
		);
	}

	/**
	 * Responsive Addons Admin Page
	 */
	public function responsive_add_ons_templates() {

		if ( $this->is_responsive_addons_pro_is_active() && ! $this->responsive_pro_license_is_active() ) {
			$theme = wp_get_theme();
			if ( 'Responsive' === $theme->get( 'Name' ) && version_compare( $theme->get( 'Version' ), '4.8.8', '>=' ) ) {
				wp_redirect( admin_url( 'themes.php?page=responsive#settings' ) );
			} else {
				wp_redirect( admin_url( '/options-general.php?page=wc_am_client_responsive_addons_pro_dashboard' ) );
			}
			exit();
		}
		?>
			<!-- <div class="wrap"> -->
				<div id="responsive-ready-sites-menu-page"></div>
					<?php
						// $this->init_nav_menu( 'general' );
						// do_action( 'responsive_addons_importer_page' );
					?>
			<!-- </div> -->

			<?php
	}
	/**
	 * Init Nav Menu
	 *
	 * @param mixed $action Action name.
	 * @since 2.5.0
	 */
	public function init_nav_menu( $action = '' ) {

		if ( '' !== $action ) {
			$this->render_tab_menu( $action );
		}
	}

	/**
	 * Render tab menu
	 *
	 * @param mixed $action Action name.
	 * @since 2.5.0
	 */
	public function render_tab_menu( $action = '' ) {
		?>
		<div id="responsive-sites-menu-page">
			<?php $this->render( $action ); ?>
		</div>
		<?php
	}

	/**
	 * Prints HTML content for tabs
	 *
	 * @param mixed $action Action name.
	 * @since 2.5.0
	 */
	public function render( $action ) {
		?>
			<div class="nav-tab-wrapper">
				<div class="logo">
					<div class="responsive-sites-logo-wrap">
							<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/responsive-plus-logo.svg' ); ?>">
							<div class="responsive-sites-version">
								<?php echo esc_html( RESPONSIVE_ADDONS_VER ); ?>
							</div>

					</div>
				</div>
				<div id="responsive-sites-filters" class="hide-on-mobile">
					<?php $this->site_filters(); ?>
					<div id="responsive-sites-analytics-wrap" class="responsive-sites-analytics">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 4C16.4183 4 20 7.58172 20 12C20 16.4183 16.4183 20 12 20C8.82996 20 6.09073 18.1561 4.7959 15.4824L11.4219 13.708C11.6085 13.658 11.8954 13.5841 12.1367 13.4834C12.4184 13.3658 12.8385 13.1343 13.1201 12.6465C13.4015 12.1587 13.3925 11.6796 13.3535 11.377C13.32 11.1176 13.2404 10.8321 13.1904 10.6455L11.415 4.02344C11.6083 4.00947 11.8032 4 12 4ZM10.1621 10.9385L4.02344 12.583C4.00956 12.3904 4 12.1961 4 12C4 8.83059 5.84279 6.09104 8.51562 4.7959L10.1621 10.9385Z" fill="#9CA3AF"/>
						<path d="M9.92945 4.27259C9.67849 3.33602 9.55302 2.86773 9.12083 2.67286C8.68865 2.47799 8.30723 2.66782 7.54439 3.04748C6.97028 3.33321 6.42361 3.67419 5.91239 4.06647C4.87054 4.8659 3.99636 5.86272 3.33975 7C2.68314 8.13728 2.25696 9.39275 2.08555 10.6947C2.00144 11.3336 1.97948 11.9775 2.01909 12.6176C2.07171 13.4681 2.09803 13.8933 2.48288 14.1701C2.86773 14.447 3.33602 14.3215 4.27259 14.0706L10.0681 12.5176C10.9788 12.2736 11.4342 12.1516 11.6413 11.7929C11.8484 11.4342 11.7264 10.9788 11.4824 10.0681L9.92945 4.27259Z" fill="#9CA3AF"/>
						</svg>
						<span class="tooltip-text"><?php esc_html_e( 'Analytics', 'responsive-add-ons' ); ?></span>
					</div>
					<div class="rst-my-favourite">
						<div id="rst-my-favorite-btn" class="rst-my-favourite-tooltip rst-nav-tab-wrapper-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path d="M7.49395 3.006C5.96995 2.925 4.44145 3.435 3.31645 4.56C1.06495 6.816 1.30045 10.602 3.70945 13.014L4.47895 13.7835L11.4719 20.7825C11.6125 20.9226 11.8029 21.0013 12.0014 21.0013C12.2 21.0013 12.3904 20.9226 12.5309 20.7825L19.5209 13.7835L20.2904 13.014C22.6994 10.602 22.9334 6.816 20.6804 4.5615C18.4289 2.307 14.6504 2.547 12.2429 4.9575L11.9999 5.2005L11.7569 4.9575C10.5524 3.75 9.01945 3.087 7.49395 3.006Z" fill="#9CA3AF"/>
							</svg>
							<span class="tooltip-text"><?php esc_html_e( 'Favourites', 'responsive-add-ons' ); ?></span>
						</div>
					</div>
					<div class="sync-ready-sites-templates-wrap header-actions">
						<div class="filters-slug">
							<a href="#" class="responsive-ready-sites-sync-templates-button">
								<span class="dashicons">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
									<g clip-path="url(#clip0_28_460)">
									<path d="M19 8L15 12H18C18 15.315 15.315 18 12 18C10.985 18 10.035 17.745 9.195 17.305L7.735 18.765C8.975 19.54 10.43 20 12 20C16.42 20 20 16.42 20 12H23L19 8ZM6 12C6 8.685 8.685 6 12 6C13.015 6 13.965 6.255 14.805 6.695L16.265 5.235C15.025 4.46 13.57 4 12 4C7.58 4 4 7.58 4 12H1L5 16L9 12H6Z" fill="#9CA3AF"/>
									</g>
									<defs>
									<clipPath id="clip0_28_460">
										<rect width="24" height="24" fill="white"/>
									</clipPath>
									</defs>
								</svg>
								</span>
								<span class="tooltip-text"><?php esc_html_e( 'Sync Library', 'responsive-add-ons' ); ?></span>
							</a>
						</div>
					</div>
					<span class="page-builder-icon">
						<div class="selected-page-builder">
							<?php
							$page_builder = array(
								'name' => 'Elementor',
								'slug' => 'elementor',
							);
							if ( $page_builder ) {
								?>
								<div class="page-builder-title-icon-parent">
								<span class="page-builder-title-icon"><img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/' . esc_html( $page_builder['slug'] ) . '.svg' ); ?>"></span>
								<span class="page-builder-title"><?php echo esc_html( $page_builder['name'] ); ?></span>
								</div>
								<span class="dashicons">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="24" viewBox="0 0 16 24" fill="none">
											<path d="M14.59 8.59L10 13.17L5.41 8.59L4 10L10 16L16 10L14.59 8.59Z" fill="#4B5563"/>
									</svg>
								</span>
							<?php } ?>
						</div>
						<ul class="page-builders">
							<?php
							$default_page_builder = 'elementor';
							$page_builders        = $this->get_default_page_builders();
							foreach ( $page_builders as $key => $page_builder ) {
								$class = '';
								if ( $default_page_builder === $page_builder['slug'] ) {
									$class = 'active';
								}
								?>
								<li data-page-builder="<?php echo esc_html( $page_builder['slug'] ); ?>" class="<?php echo esc_html( $class ); ?>">
										<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/' . esc_html( $page_builder['slug'] ) . '.svg' ); ?>">
									<div class="title"><?php echo esc_html( $page_builder['name'] ); ?></div>
								</li>
								<?php
							}
							?>
						</ul>
						<form id="responsive-sites-welcome-form-inline" enctype="multipart/form-data" method="post" style="display: none;">
							<div class="fields">
								<input type="hidden" name="page_builder" class="page-builder-input" required="required" />
							</div>
							<input type="hidden" name="message" value="saved" />
							<?php wp_nonce_field( 'responsive-sites-welcome-screen', 'responsive-sites-page-builder' ); ?>
						</form>
					</span>
					<div class="guided-overlay step-one" id="step-one">
						<p class="guide-text">Select your desired page builder.</p>
						<div class="guided-overlay-buttons">
							<button class="skip-tour" id="skip-tour">Skip tour</button>
							<button id="step-one-next">Next</button>
						</div>
					</div>
					<div class="rst-admin-overlay">
						<span id="rst-admin-overlay" class="rst-admin-overlay-icon rst-nav-tab-wrapper-icon rst-go-pro-icon" >
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M10 0C8.02219 0 6.08879 0.58649 4.4443 1.6853C2.79981 2.78412 1.51809 4.3459 0.761209 6.17317C0.00433284 8.00043 -0.193701 10.0111 0.192152 11.9509C0.578004 13.8907 1.53041 15.6725 2.92894 17.0711C4.32746 18.4696 6.10929 19.422 8.0491 19.8079C9.98891 20.1937 11.9996 19.9957 13.8268 19.2388C15.6541 18.4819 17.2159 17.2002 18.3147 15.5557C19.4135 13.9112 20 11.9778 20 10C20 8.68678 19.7413 7.38642 19.2388 6.17317C18.7363 4.95991 17.9997 3.85752 17.0711 2.92893C16.1425 2.00035 15.0401 1.26375 13.8268 0.761205C12.6136 0.258658 11.3132 0 10 0ZM10 16C9.80222 16 9.60888 15.9414 9.44443 15.8315C9.27999 15.7216 9.15181 15.5654 9.07613 15.3827C9.00044 15.2 8.98063 14.9989 9.01922 14.8049C9.05781 14.6109 9.15305 14.4327 9.2929 14.2929C9.43275 14.153 9.61093 14.0578 9.80491 14.0192C9.9989 13.9806 10.2 14.0004 10.3827 14.0761C10.5654 14.1518 10.7216 14.28 10.8315 14.4444C10.9414 14.6089 11 14.8022 11 15C11 15.2652 10.8946 15.5196 10.7071 15.7071C10.5196 15.8946 10.2652 16 10 16ZM11 10.84V12C11 12.2652 10.8946 12.5196 10.7071 12.7071C10.5196 12.8946 10.2652 13 10 13C9.73479 13 9.48043 12.8946 9.2929 12.7071C9.10536 12.5196 9 12.2652 9 12V10C9 9.73478 9.10536 9.48043 9.2929 9.29289C9.48043 9.10536 9.73479 9 10 9C10.2967 9 10.5867 8.91203 10.8334 8.7472C11.08 8.58238 11.2723 8.34811 11.3858 8.07403C11.4994 7.79994 11.5291 7.49834 11.4712 7.20736C11.4133 6.91639 11.2704 6.64912 11.0607 6.43934C10.8509 6.22956 10.5836 6.0867 10.2926 6.02882C10.0017 5.97094 9.70007 6.00065 9.42598 6.11418C9.15189 6.22771 8.91762 6.41997 8.7528 6.66665C8.58798 6.91332 8.5 7.20333 8.5 7.5C8.5 7.76522 8.39465 8.01957 8.20711 8.20711C8.01958 8.39464 7.76522 8.5 7.5 8.5C7.23479 8.5 6.98043 8.39464 6.7929 8.20711C6.60536 8.01957 6.5 7.76522 6.5 7.5C6.49739 6.8503 6.67566 6.2127 7.01487 5.65857C7.35408 5.10445 7.84083 4.65568 8.42062 4.3625C9.00042 4.06933 9.65037 3.94332 10.2977 3.99859C10.9451 4.05386 11.5643 4.28823 12.086 4.67545C12.6077 5.06267 13.0113 5.58746 13.2517 6.19107C13.492 6.79467 13.5596 7.45327 13.4469 8.09312C13.3342 8.73297 13.0456 9.32882 12.6134 9.81396C12.1813 10.2991 11.6226 10.6544 11 10.84Z" fill="#A6A6A7"/>
							</svg>

						</span>
					</div>
					<?php $this->responsive_sites_admin_overlay(); ?>
				</div>
			</div><!-- .nav-tab-wrapper -->
			<div id="responsive-sites-filters" class="hide-on-desktop">
			<?php $this->site_filters(); ?>
		</div>
			<?php
	}

	/**
	 * Site Filters
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function site_filters() {
		?>
		<div class="wp-filter hide-if-no-js">
			<div class="section-left">
				<div class="search-form">
					<div class="guided-overlay step-two" id="step-two">
						<p class="guide-text">Choose the category and type of the template from the dropdown.</p>
						<div class="guided-overlay-buttons">
							<button class="skip-tour"id="skip-tour-two">Skip tour</button>
							<button id="step-two-previous">Previous</button>
							<button id="step-two-next">Next</button>
						</div>
					</div>
					<div class="search-container">
						<!-- Search Icon -->
						<div class="search-icon">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M2.41599 10C3.33915 10.9245 4.5685 11.4794 5.87246 11.5603C7.17641 11.6413 8.46495 11.2426 9.49532 10.4393L13.0407 13.9847C13.1664 14.1061 13.3348 14.1733 13.5096 14.1718C13.6844 14.1703 13.8516 14.1001 13.9752 13.9765C14.0988 13.8529 14.1689 13.6857 14.1704 13.5109C14.172 13.3361 14.1048 13.1677 13.9833 13.042L10.438 9.49666C11.2769 8.42011 11.6735 7.06409 11.5469 5.70518C11.4204 4.34626 10.7802 3.08679 9.75698 2.18364C8.73376 1.28048 7.40455 0.801672 6.04043 0.844849C4.67632 0.888026 3.38005 1.44994 2.41599 2.416C1.91785 2.91388 1.5227 3.50503 1.25309 4.15567C0.983492 4.80632 0.844727 5.50371 0.844727 6.208C0.844727 6.91229 0.983492 7.60968 1.25309 8.26032C1.5227 8.91096 1.91785 9.50212 2.41599 10ZM3.35866 3.36C4.01771 2.70096 4.88487 2.29082 5.81241 2.19946C6.73994 2.1081 7.67047 2.34116 8.44543 2.85895C9.2204 3.37673 9.79186 4.14719 10.0625 5.03907C10.3331 5.93095 10.286 6.88907 9.92943 7.75017C9.57282 8.61127 8.92868 9.32209 8.10674 9.76152C7.28481 10.2009 6.33594 10.3418 5.4218 10.1601C4.50767 9.97834 3.68482 9.48528 3.09345 8.76489C2.50209 8.0445 2.1788 7.14136 2.17866 6.20933C2.17683 5.67971 2.28019 5.155 2.48275 4.66565C2.68532 4.1763 2.98304 3.73204 3.35866 3.35866V3.36Z" fill="#9CA3AF"/>
							</svg>
						</div>

						<!-- Search Input -->
						<input autocomplete="off" placeholder="<?php esc_html_e( 'Search', 'responsive-add-ons' ); ?>" 
								type="textarea" aria-describedby="live-search-desc" 
								id="wp-filter-search-input" class="wp-filter-search">

						<!-- Clear Button -->
						<div class="clear-button" id="clear-search">
							<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3.39752 2.11867L7.00018 5.72134L10.5842 2.13734C10.6634 2.05307 10.7587 1.98566 10.8646 1.93915C10.9704 1.89264 11.0846 1.86799 11.2002 1.86667C11.4477 1.86667 11.6851 1.965 11.8602 2.14004C12.0352 2.31507 12.1335 2.55247 12.1335 2.8C12.1357 2.91443 12.1145 3.0281 12.0711 3.13402C12.0278 3.23995 11.9633 3.33591 11.8815 3.416L8.25085 7L11.8815 10.6307C12.0353 10.7812 12.1255 10.985 12.1335 11.2C12.1335 11.4475 12.0352 11.6849 11.8602 11.86C11.6851 12.035 11.4477 12.1333 11.2002 12.1333C11.0812 12.1383 10.9626 12.1184 10.8517 12.075C10.7408 12.0317 10.6402 11.9657 10.5562 11.8813L7.00018 8.27867L3.40685 11.872C3.32799 11.9535 3.23378 12.0185 3.12965 12.0633C3.02553 12.1082 2.91355 12.132 2.80018 12.1333C2.55265 12.1333 2.31525 12.035 2.14022 11.86C1.96518 11.6849 1.86685 11.4475 1.86685 11.2C1.86468 11.0856 1.88591 10.9719 1.92924 10.866C1.97257 10.7601 2.0371 10.6641 2.11885 10.584L5.74952 7L2.11885 3.36934C1.96502 3.21884 1.87482 3.01505 1.86685 2.8C1.86685 2.55247 1.96518 2.31507 2.14022 2.14004C2.31525 1.965 2.55265 1.86667 2.80018 1.86667C3.02418 1.86947 3.23885 1.96 3.39752 2.11867Z" fill="#A6A6A7"/>
							</svg>
						</div>
					</div>

					<div class="responsive-sites-autocomplete-result"></div>
				</div>
			</div>
		</div>
		<?php
	}


	/**
	 * Get Default Page Builders
	 *
	 * @since 2.5.0
	 * @return array
	 */
	public function get_default_page_builders() {
		return array(
			array(
				'id'    => 1,
				'value' => '',
				'label' => 'All',
				'icon'  => esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/all.svg' ),
			),
			array(
				'id'    => 2,
				'value' => 'elementor',
				'label' => 'Elementor',
				'icon'  => esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/elementor.svg' ),
			),
			array(
				'id'    => 3,
				'value' => 'gutenberg',
				'label' => 'Gutenberg',
				'icon'  => esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/gutenberg.svg' ),
			),
		);
	}

	/**
	 * Check if Responsive Addons Pro is installed.
	 */
	public function is_responsive_addons_pro_is_active() {
		$responsive_pro_slug = 'responsive-addons-pro/responsive-addons-pro.php';
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( $responsive_pro_slug ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add links to plugin's description in plugins table.
	 *
	 * @param array  $links Initial list of links.
	 * @param string $file  Basename of current plugin.
	 *
	 * @return array
	 */
	public function responsive_addons_rate_plugin_link( $links, $file ) {
		if ( plugin_basename( RESPONSIVE_ADDONS_FILE ) !== $file ) {
			return $links;
		}
		$rate_url  = 'https://wordpress.org/support/plugin/responsive-add-ons/reviews/#new-post';
		$rate_link = '<a target="_blank" href="' . esc_url( $rate_url ) . '" title="' . esc_attr__( 'Rate the plugin', 'responsive-add-ons' ) . '">' . esc_html__( 'Rate the plugin ★★★★★', 'responsive-add-ons' ) . '</a>';
		$links[]   = $rate_link;
		return $links;
	}

	/**
	 * Add rating links to the Responsive Addons Admin Page
	 *
	 * @param string $footer_text The existing footer text.
	 *
	 * @return string
	 * @since 2.0.6
	 * @global string $typenow
	 */
	public function responsive_addons_admin_rate_us( $footer_text ) {
		$page        = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		$show_footer = array( 'responsive-add-ons' );

		if ( in_array( $page, $show_footer ) ) {
			$rate_text = '<div class="rst-branding-footer">
							<div class="rst-branding-footer-text">
							    If you like <strong>Responsive Starter Templates</strong>, please leave us a "<a href="https://wordpress.org/support/view/plugin-reviews/responsive-add-ons?filter=5#postform" target="_blank" class="responsive-rating-link" style="text-decoration:none;" data-rated="' . esc_attr__( 'Thanks :)', 'responsive-add-ons' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>" rating. Thank you!
							</div>
							<img class="rst-footer-branding-img" src="' . esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/responsive-addons-footer-thumbnail.png' ) . '">
						</div>';

			return $rate_text;
		} else {
			return $footer_text;
		}
	}

	/**
	 * Output buffer
	 */
	public function app_output_buffer() {
		ob_start();
	}

	/**
	 * Check if Responsive theme or Child theme of Responsive is Active
	 *
	 * @since 2.1.1
	 */
	public function check_responsive_theme_active() {

		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_send_json_error( __( 'User does not have permission!', 'responsive-add-ons' ) );
		}

		$current_theme = wp_get_theme();
		if ( ( 'Responsive' === $current_theme->get( 'Name' ) ) || ( is_child_theme() && 'Responsive' === $current_theme->parent()->get( 'Name' ) ) ) {
			wp_send_json_success(
				array( 'success' => true )
			);
		} else {
			wp_send_json_error(
				array( 'success' => false )
			);
		}
	}

	/**
	 * Create Elementor Template.
	 *
	 * @since  2.9.1
	 */
	public function create_elementor_template() {

		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'customize' ) ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-add-ons' ) );
		}

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';
		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$url  = self::$rst_blocks_api_url . 'pages/' . $id;

		$api_url = add_query_arg(
			array(
				'site_url' => site_url(),
				'version'  => RESPONSIVE_ADDONS_VER,
			),
			$url
		);

		$response = wp_safe_remote_get( $api_url );

		if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
			wp_send_json_error( wp_remote_retrieve_body( $response ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data ) ) {
			wp_send_json_error( 'Empty page data.' );
		}

		$content = isset( $data['content']['rendered'] ) ? $data['content']['rendered'] : '';

		$page_id = isset( $data['id'] ) ? sanitize_text_field( $data['id'] ) : '';

		$title          = '';
		$rendered_title = isset( $data['title']['rendered'] ) ? sanitize_text_field( $data['title']['rendered'] ) : '';
		if ( isset( $rendered_title ) ) {
			$title = ( isset( $_POST['title'] ) && '' !== $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) . ' - ' . $rendered_title : $rendered_title;
		}

		$excerpt = isset( $data['excerpt']['rendered'] ) ? sanitize_text_field( $data['excerpt']['rendered'] ) : '';

		$post_args = array(
			'post_type'    => 'elementor_library',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
		);

		$new_page_id = wp_insert_post( $post_args );
		update_post_meta( $new_page_id, '_responsive_sites_enable_for_batch', true );
		$post_meta = isset( $data['post-meta'] ) ? $data['post-meta'] : array();

		if ( ! empty( $post_meta ) ) {
			$this->import_template_meta( $new_page_id, $post_meta );
		}

		$term_value = ( 'pages' === $type ) ? 'page' : 'container';
		update_post_meta( $new_page_id, '_elementor_template_type', $term_value );
		wp_set_object_terms( $new_page_id, $term_value, 'elementor_library_type' );

		update_post_meta( $new_page_id, '_wp_page_template', 'elementor_header_footer' );

		do_action( 'responsive_sites_process_single', $new_page_id );

		wp_send_json_success(
			array(
				'remove-page-id' => $page_id,
				'id'             => $new_page_id,
				'link'           => get_permalink( $new_page_id ),
			)
		);
	}

	/**
	 * Check if Responsive theme or Child theme of Responsive is Active
	 *
	 * @since 2.1.1
	 */
	public function get_responsive_theme() {
		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'install_themes' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to install themes on this site.', 'responsive-add-ons' ) );
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // For themes_api().

		$theme = 'responsive';

		$api = themes_api(
			'theme_information',
			array(
				'slug' => $theme,
			)
		); // Save on a bit of bandwidth.

		if ( is_wp_error( $api ) ) {
			wp_die( esc_html( $api ) );
		}

		/* translators: %s: Theme name and version. */
		$upgrader = new Theme_Upgrader( new Theme_Installer_Skin() );
		$res      = $upgrader->install( $api->download_link );
		switch_theme( 'responsive' );
		if ( $res ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Register the menu for the plugin.
	 *
	 * @since 2.2.8
	 */
	public function responsive_add_ons_admin_menu() {
		$theme = wp_get_theme();

		if ( ( ( 'Responsive' !== $theme->name && 'Responsive' !== $theme->parent_theme ) ) && is_plugin_inactive( 'responsive-block-editor-addons/responsive-block-editor-addons.php' ) && is_plugin_inactive( 'responsive-elementor-addons/responsive-elementor-addons.php' ) && is_plugin_inactive( 'responsive-addons-for-elementor/responsive-addons-for-elementor.php' ) ) {
			add_menu_page( 'Responsive', 'Responsive', 'manage_options', 'responsive_add_ons', array( $this, 'responsive_add_ons_templates' ), esc_url( RESPONSIVE_ADDONS_DIR_URL ) . 'admin/images/responsive-add-ons-menu-icon.png', 59 );
			add_submenu_page(
				'responsive_add_ons',
				__( 'Templates', 'responsive-add-ons' ),
				__( 'Templates', 'responsive-add-ons' ),
				'manage_options',
				'responsive_add_ons',
				array( $this, 'responsive_add_ons_templates' ),
			);
		}

		if ( ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) && version_compare( RESPONSIVE_THEME_VERSION, '4.9.7.1', '<=' ) ) {

			add_menu_page(
				__( 'Responsive Starter Templates', 'responsive-add-ons' ),
				__( 'Responsive', 'responsive-add-ons' ),
				'manage_options',
				'responsive_add_ons',
				array( $this, 'responsive_add_ons_templates' ),
				RESPONSIVE_ADDONS_URI . '/admin/images/responsive-add-ons-menu-icon.png',
				59.5
			);

			add_submenu_page(
				'responsive_add_ons',
				'Responsive Starter Templates',
				__( 'Responsive Templates', 'responsive-add-ons' ),
				'manage_options',
				'responsive_add_ons',
				array( $this, 'responsive_add_ons_templates' ),
				20
			);
		}
	}

	/**
	 * Go to Responsive Pro support.
	 *
	 * Fired by `admin_init` action.
	 *
	 * @since 2.2.8
	 * @access public
	 */
	public function responsive_add_ons_community_support() {
		if ( empty( $_GET['page'] ) ) {
			return;
		}
		wp_redirect( 'https://www.facebook.com/groups/responsive.theme' );
		die;
	}

	/**
	 * Free vs Pro features list.
	 *
	 * @since 2.2.8
	 * @access public
	 */
	public function responsive_add_ons_go_pro() {
		require_once RESPONSIVE_ADDONS_DIR . 'admin/templates/free-vs-pro.php';
	}

	/**
	 * On admin init.
	 *
	 * Preform actions on WordPress admin initialization.
	 *
	 * Fired by `admin_init` action.
	 *
	 * @since 2.2.8
	 * @access public
	 */
	public function responsive_add_ons_on_admin_init() {

		// Check if we should redirect after activation.
		if ( get_transient( 'responsive_add_ons_activation_redirect' ) ) {
			delete_transient( 'responsive_add_ons_activation_redirect' );
			
			// Don't redirect if we're already on the templates page or doing AJAX.
			if ( ! wp_doing_ajax() && ( empty( $_GET['page'] ) || 'responsive_add_ons' !== $_GET['page'] ) ) {
				
				// Ensure templates are loaded from JSON files before redirect
				$sites = $this->get_sites_by_page_builder();
				$total_requests = (int) get_site_option( 'responsive-ready-sites-requests', 0 );
				
				// If templates aren't loaded yet, load them from JSON files now
				if ( empty( $sites ) && 0 === $total_requests ) {
					$dir = RESPONSIVE_ADDONS_DIR . 'includes/json/';
					
					// Load sites from JSON (same logic as process_batch())
					if ( file_exists( $dir . 'responsive-sites-request.json' ) ) {
						$responsive_sites_request = (int) trim( file_get_contents( $dir . 'responsive-sites-request.json' ) );
						
						// Same validation as process_batch() - only load if <= 16 pages
						if ( $responsive_sites_request && $responsive_sites_request <= 16 ) {
							update_site_option( 'responsive-ready-sites-requests', $responsive_sites_request );
							
							for ( $page = 1; $page <= $responsive_sites_request; $page++ ) {
								$json_file = $dir . 'responsive-ready-sites-and-pages-page-' . $page . '.json';
								if ( file_exists( $json_file ) ) {
									$file_contents   = file_get_contents( $json_file );
									$sites_and_pages = json_decode( $file_contents, true );
									if ( ! empty( $sites_and_pages ) ) {
										update_site_option( 'responsive-ready-sites-and-pages-page-' . $page, $sites_and_pages );
									}
								}
							}
						}
					}
					
					// Also load blocks if they exist (for completeness)
					if ( file_exists( $dir . 'rst-blocks-requests.json' ) ) {
						$responsive_blocks_request = (int) trim( file_get_contents( $dir . 'rst-blocks-requests.json' ) );
						if ( $responsive_blocks_request ) {
							update_site_option( 'rst-blocks-requests', $responsive_blocks_request );
							for ( $page = 1; $page <= $responsive_blocks_request; $page++ ) {
								$json_file = $dir . 'rst-blocks-page-' . $page . '.json';
								if ( file_exists( $json_file ) ) {
									$file_contents = file_get_contents( $json_file );
									$blocks_pages  = json_decode( $file_contents, true );
									if ( ! empty( $blocks_pages ) ) {
										update_site_option( 'rst-blocks-page-' . $page, $blocks_pages );
									}
								}
							}
						}
					}
				}
				
				wp_safe_redirect( admin_url( 'admin.php?page=responsive_add_ons' ) );
				exit;
			}
		}

		$this->responsive_add_ons_remove_all_admin_notices();
	}

	/**
	 * Removes all the admin notices.
	 *
	 * @since 2.2.8
	 * @access private
	 */
	private function responsive_add_ons_remove_all_admin_notices() {
		$responsive_add_ons_pages = array(
			'responsive_add_ons',
			'responsive-add-ons',
			'responsive_addons_pro_system_info',
		);

		if ( empty( $_GET['page'] ) || ! in_array( $_GET['page'], $responsive_add_ons_pages, true ) ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
	}

	/**
	 * Get all sites
	 *
	 * @since 2.5.0
	 * @return array All sites.
	 */
	public function get_all_sites() {
		$sites_and_pages = array();

		$total_requests = (int) get_site_option( 'responsive-ready-sites-requests', 0 );

		$favorite_settings = get_option( 'responsive-sites-favorites', array() );

		for ( $page = 1; $page <= $total_requests; $page++ ) {
			$current_page_data = get_site_option( 'responsive-ready-sites-and-pages-page-' . $page, array() );
			if ( ! empty( $current_page_data ) ) {
				foreach ( $current_page_data as $page_id => $page_data ) {
					if ( in_array( $page_data['id'], $favorite_settings ) ) {
						// If it exists in the favorites array, add favorite status.
						$page_data['favorite_status'] = true;
					} else {
						$page_data['favorite_status'] = false;
					}
					$page_data['site_file_location'] = 'responsive-ready-sites-and-pages-page-' . $page;
					$sites_and_pages[] = $page_data;
				}
			}
		}

		return $sites_and_pages;
	}

	/**
	 * Get all RST Blocks
	 *
	 * @since 2.9.1
	 * @return array All RST Blocks.
	 */
	public function get_all_rst_blocks() {
		$blocks = array();

		$total_requests = (int) get_site_option( 'rst-blocks-requests', 0 );

		for ( $page = 1; $page <= $total_requests; $page++ ) {
			$current_page_data = get_site_option( 'rst-blocks-page-' . $page, array() );
			if ( ! empty( $current_page_data ) ) {
				foreach ( $current_page_data as $page_id => $page_data ) {
					$blocks[] = $page_data;
				}
			}
		}
		return $blocks;
	}

	/**
	 * Get Page Builder Sites
	 *
	 * @since 2.5.0
	 *
	 * @return array page builder sites.
	 */
	public function get_sites_by_page_builder() {
		$sites_and_pages            = $this->get_all_sites();
		$current_page_builder_sites = array();
		if ( ! empty( $sites_and_pages ) ) {
			foreach ( $sites_and_pages as $site_id => $site_details ) {
					$current_page_builder_sites[] = $site_details;
			}
		}

		return $current_page_builder_sites;
	}

	/**
	 * Retrieves details of favorite template sites.
	 *
	 * Gets the current favorite site IDs from options, checks them against the
	 * available page builder sites, and returns the matching ones.
	 *
	 * @return void Sends a JSON success response with the favorite site details.
	 */
	public function get_favorite_template_site_details() {

		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		$favorite_sites = array();

		$current_page_builder_sites = $this->get_sites_by_page_builder();

		$favorite_settings = get_option( 'responsive-sites-favorites', array() );

		if ( ! empty( $favorite_settings ) && ! empty( $current_page_builder_sites ) ) {
			foreach ( $current_page_builder_sites as $site ) {
				if ( in_array( $site['id'], $favorite_settings ) ) {
					// If it exists in the favorites array, add favorite status.
					$favorite_sites[] = $site;
				}
			}
		}

		wp_send_json_success( $favorite_sites );
	}

	/**
	 * Get Total Requests
	 *
	 * @since 2.5.0
	 * @return integer
	 */
	public function get_total_requests() {

		$api_args = array(
			'timeout' => 60,
		);

		$api_url = self::$api_url . 'get-ready-sites-requests-count/?per_page=15';

		$response = wp_safe_remote_get( $api_url, $api_args );

		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {

			$total_requests = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( isset( $total_requests ) ) {

				update_site_option( 'responsive-ready-sites-requests', $total_requests );

				return $total_requests;
			}
		}

		$this->get_total_requests();
	}

	/**
	 * Add settings link
	 *
	 * @param array $links holds plugin links.
	 */
	public function responsive_add_view_library_btn( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=responsive_add_ons' ) . '">' . __( 'View Library', 'responsive-add-ons' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
	/**
	 * Add settings link for theme
	 *
	 * @param array $links holds plugin links.
	 */
	public function responsive_add_view_settings_btn( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=responsive#home' ) . '">' . __( 'Settings', 'responsive-add-ons' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Displays the admin overlay for the Responsive Sites plugin.
	 *
	 * This overlay includes sections for Go Pro promotion, rating prompt,
	 * help center, and video guides. It is hidden by default and displayed via JS.
	 *
	 * @return void
	 */
	public function responsive_sites_admin_overlay() {
		?>
			<div style="display: none;" class="responsive-sites-overlay-reveal">
				<div class="responsive-sites-overlay-container">
					<button id="close-admin-overlay" title="close">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M17.9401 7.752L13.6921 12L17.9401 16.248L16.2481 17.94L12.0001 13.704L7.76406 17.94L6.06006 16.236L10.2961 12L6.06006 7.764L7.76406 6.06L12.0001 10.296L16.2481 6.06L17.9401 7.752Z" fill="#9CA3AF"/>
						</svg>
					</button>
					<div class="responsive-sites-go-pro">
						<h3 class="responsive-sites-overlay-heading"><?php esc_html_e( 'Go Pro', 'responsive-add-ons' ); ?></h3>
						<p class="responsive-sites-overlay-content"><?php esc_html_e( 'Get access to all the pro templates and unlock more theme customizer settings using Responsive Pro.', 'responsive-add-ons' ); ?></p>
						<a href="https://cyberchimps.com/responsive-go-pro/?utm_source=RST_plugin&utm_medium=intro_screen_slidein_btn&utm_campaign=free-to-pro&utm_term=Go_Pro_btn" target="_blank" class="button button-primary responsive-sites-go-pro-btn"><?php esc_html_e( 'Go Pro', 'responsive-add-ons' ); ?></a>
					</div>
					<div class="responsive-sites-rate-us">
						<h3 class="responsive-sites-overlay-heading"><?php esc_html_e( 'Rate Us', 'responsive-add-ons' ); ?></h3>
						<p class="responsive-sites-rate-us-stars">
							<?php
							for ( $i = 0; $i < 5; $i++ ) {
								?>
									<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/star-rating.svg' ); ?>">
									<?php
							}
							?>
						</p>
						<p class="responsive-sites-overlay-content"><?php esc_html_e( 'Please let us know what you think, we would appreciate every single review.', 'responsive-add-ons' ); ?></p>
						<a href="https://wordpress.org/support/plugin/responsive-add-ons/reviews/" target="_blank" class="responsive-sites-rate-us-btn"><?php esc_html_e( 'Submit Review', 'responsive-add-ons' ); ?></a>
					</div>
					<div class="responsive-sites-help-center">
						<h3 class="responsive-sites-overlay-heading"><?php esc_html_e( 'Help Center', 'responsive-add-ons' ); ?></h3>
						<p class="responsive-sites-overlay-content"><?php esc_html_e( 'Read the documentation to find answers to your questions.', 'responsive-add-ons' ); ?></p>
						<a href="https://cyberchimps.com/docs/responsive-starter-templates/" target="_blank" class="responsive-sites-help-center-btn"><?php esc_html_e( 'Docs', 'responsive-add-ons' ); ?></a>
						<?php esc_html_e( 'or', 'responsive-add-ons' ); ?>
						<a href="https://www.facebook.com/groups/responsive.theme/" target="_blank" class="responsive-sites-community-support-btn"><?php esc_html_e( 'Visit Facebook Group', 'responsive-add-ons' ); ?></a>
					</div>
					<div class="responsive-sites-video-guides">
						<h3 class="responsive-sites-overlay-heading"><?php esc_html_e( 'Video Guides', 'responsive-add-ons' ); ?></h3>
						<p class="responsive-sites-overlay-content"><?php esc_html_e( 'Browse through these video tutorials to learn more about how the plugin functions.', 'responsive-add-ons' ); ?></p>
						<a href="https://www.youtube.com/playlist?list=PLXTwxw3ZJwPSpE3RYanAdYgnDptbSvjXl" target="_blank" class="responsive-sites-video-guides-btn"><?php esc_html_e( 'Watch Now', 'responsive-add-ons' ); ?></a>
					</div>
					<!-- Remaining content -->
					<!-- ... -->
				</div>
			</div>
		<?php
	}

	/**
	 * Add/Remove Favorite.
	 *
	 * @since  2.8.6
	 */
	public function add_to_favorite() {
		// Permission check.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized action.', 'responsive-add-ons' ) ] );
		}

		// Nonce verification.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		// Sanitize and validate inputs.
		$site_id     = isset( $_POST['site_id'] ) ? sanitize_text_field( wp_unslash( $_POST['site_id'] ) ) : '';
		$is_favorite = isset( $_POST['is_favorite'] ) ? sanitize_text_field( wp_unslash( $_POST['is_favorite'] ) ) : '';

		if ( empty( $site_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid site ID.', 'responsive-add-ons' ) ] );
		}

		// Retrieve existing favorites.
		$favorites = get_option( 'responsive-sites-favorites', array() );
		if ( ! is_array( $favorites ) ) {
			$favorites = array();
		}

		// Add or remove favorite efficiently.
		$key = array_search( $site_id, $favorites, true );

		if ( 'false' === $is_favorite && false !== $key ) {
			unset( $favorites[ $key ] );
		} elseif ( 'true' === $is_favorite && false === $key ) {
			$favorites[] = $site_id;
		}

		$favorites = array_values( $favorites );

		update_option( 'responsive-sites-favorites', $favorites, false );
		// Return response.
		wp_send_json_success( [
			'favorites' => $favorites,
			'count'     => count( $favorites ),
			'action'    => ( 'true' === $is_favorite ) ? 'added' : 'removed',
		] );
	}

	/**
	 * Handles AJAX request to update and return the favorite status of all sites.
	 *
	 * Verifies the AJAX nonce 'responsive-addons' for security.
	 * Responds with a JSON success containing all site data.
	 *
	 * @return void Outputs JSON response and exits.
	 */
	public function update_all_sites_fav_status() {

		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		wp_send_json_success( $this->get_all_sites() );
	}

	
	/**
	 * If RST Blocks Empty, then Insert Data.
	 *
	 * @since 1.9.1
	 */
	public function rst_add_blocks_data() {
		return get_site_option( 'rst-blocks-page-1' );
	}

	/**
	 * RST Register Admin Menu.
	 *
	 * @param string $slug parent slug of submenu.
	 * @since 2.9.3
	 */
	public function rst_register_admin_menu( $slug ) {
		add_submenu_page(
			$slug,
			__( 'Templates', 'responsive-add-ons' ),
			__( 'Templates', 'responsive-add-ons' ),
			'manage_options',
			'responsive_add_ons',
			array( $this, 'responsive_add_ons_templates' ),
		);
		$theme = wp_get_theme();
		if ( $this->responsive_addons_is_theme_site_builder_compatible() && 'on' === get_option( 'rplus_site_builder_enable' ) && ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) ) {
			add_submenu_page(
				$slug,
				__( 'Site Builder', 'responsive-add-ons' ),
				__( 'Site Builder', 'responsive-add-ons' ),
				'manage_options',
				'responsive-site-builder',
				array( $this, 'responsive_site_builder' ),
			);
		}
	}

	/**
	 * Adding stylesheet of responsive pro plugin using handle of responsive theme stylesheet.
	 */
	public function responsive_pro_css() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'responsive-pro-style', plugin_dir_url( __FILE__ ) . "css/style{$suffix}.css", array( 'responsive-style' ), RESPONSIVE_ADDONS_VER );
	}

	/**
	 * Load woocommerce files.
	 */
	public function load_woocommerce() {
		if ( ! class_exists( 'Responsive_Addons_Woocommerce_Ext' ) ) {
			require_once plugin_dir_path( __DIR__ ) . 'includes/compatibility/woocommerce/customizer/class-responsive-addons-woocommerce-ext.php';
		}
	}

	/**
	 * Get plugin settings.
	 *
	 * @since 2.9.2
	 * @return array
	 */
	public static function raddons_get_white_label_settings() {
		$default_settings = array(
			'plugin_name'          => '',
			'plugin_short_name'    => '',
			'plugin_desc'          => '',
			'plugin_author'        => '',
			'plugin_uri'           => '',
			'admin_label'          => '',
			'support_link'         => '',
			'hide_support'         => 'off',
			'hide_wl_settings'     => 'off',
			'theme_name'           => '',
			'theme_desc'           => '',
			'theme_screenshot_url' => '',
			'theme_icon_url'       => '',
		);

		$settings = get_option( 'rpro_elementor_settings' );

		if ( ! is_array( $settings ) || empty( $settings ) ) {
			$settings = $default_settings;
		}

		if ( is_array( $settings ) && ! empty( $settings ) ) {
			$settings = array_merge( $default_settings, $settings );
		}

		return apply_filters( 'rpro_elements_admin_settings', $settings );
	}

	/**
	 * Set the White Label branding data to theme.
	 *
	 * @param array $all_themes Contains Theme Attributes.
	 * @since 2.9.2
	 * @return array
	 */
	public static function responsive_theme_white_label_update_branding( $all_themes ) {

		$settings = self::raddons_get_white_label_settings();

		$theme_slug = 'responsive';
		// Check if the theme exists.
		if ( isset( $all_themes[ $theme_slug ] ) ) {

			// Update theme details.
			if ( ! empty( $settings['theme_name'] ) ) {

				$all_themes['responsive']['name'] = $settings['theme_name'];

				foreach ( $all_themes as $key => $theme ) {
					if ( isset( $theme['parent'] ) && 'Responsive' === $theme['parent'] ) {
						$all_themes[ $key ]['parent'] = $settings['theme_name'];
					}
				}
			}

			$all_themes['responsive']['description'] = ! empty( $settings['theme_desc'] ) ? $settings['theme_desc'] : $all_themes['responsive']['description'];

			if ( ! empty( $settings['plugin_author'] ) ) {
				$all_themes['responsive']['author']       = $settings['plugin_author'];
				$author_url                               = ( ! empty( $settings['plugin_website_uri'] ) ? $settings['plugin_website_uri'] : '#' );
				$all_themes['responsive']['authorAndUri'] = '<a href="' . esc_url( $author_url ) . '">' . $all_themes['responsive']['author'] . '</a>';
			}
			$all_themes['responsive']['screenshot'] = ! empty( $settings['theme_screenshot_url'] ) ? array( $settings['theme_screenshot_url'] ) : $all_themes['responsive']['screenshot'];

		}

		return $all_themes;
	}

	/**
	 * White labels the theme on the dashboard 'At a Glance' metabox
	 *
	 * @param mixed $content Content.
	 * @return array
	 */
	public function admin_dashboard_page( $content ) {
		$settings = self::raddons_get_white_label_settings();
		if ( is_admin() && 'Responsive' === wp_get_theme() && ! empty( $settings['theme_name'] ) ) {
			return sprintf( $content, get_bloginfo( 'version', 'display' ), '<a href="themes.php">' . $settings['theme_name'] . '</a>' );
		}
		return $content;
	}

	/**
	 * White labels the theme using the gettext filter
	 * to cover areas that we can't access like the Customizer.
	 *
	 * @param string $text  Translated text.
	 * @param string $original         Text to translate.
	 * @param string $domain       Text domain. Unique identifier for retrieving translated strings.
	 * @return string
	 */
	public function theme_gettext( $text, $original, $domain ) {
		$settings = self::raddons_get_white_label_settings();
		if ( ! empty( $settings['theme_name'] ) ) {
			if ( 'Responsive' === $original && 'responsive' === $domain ) {
				$text = $settings['theme_name'];
			}
		}
		return $text;
	}

	/**
	 * Get whitelabelled icon for admin dashboard.
	 *
	 * @since 2.9.2
	 * @param string $logo Default icon.
	 * @return string URL for updated whitelabelled icon.
	 */
	public function update_admin_brand_logo( $logo ) {

		$settings = self::raddons_get_white_label_settings();

		$logo = $settings['theme_icon_url'];

		return esc_url( $logo );
	}

	/**
	 * Renders the Settings tab.
	 *
	 * @since 2.9.3
	 * @access public
	 */
	public function responsive_addons_getting_started_settings_tab() {
		if ( ! $this->is_responsive_addons_pro_is_active() ) {
			echo wp_kses_post( '<div class="responsive-theme-tab responsive-theme-raddons-settings-tab" data-tab="raddons-settings"><p class="responsive-theme-tab-name">Settings</p></div>' );
		}
	}

	/**
	 * Renders the Settings tab Content.
	 *
	 * @since 2.9.3
	 * @access public
	 */
	public function responsive_addons_getting_started_settings_tab_content() {
		if ( ! $this->is_responsive_addons_pro_is_active() ) {
			?>
		<div class="responsive-theme-settings-content responsive-theme-tab-content" id="responsive_raddons-settings">
			<?php require_once RESPONSIVE_ADDONS_DIR . '/admin/partials/getting-started/responsive-getting-started.php'; ?>
		</div>
			<?php
		}
	}

	/**
	 * Enqueue js file responsible to handle events on getting started page.
	 *
	 * @since 2.9.3
	 * @access public
	 */
	public function responsive_addons_admin_enqueue_getting_started_scripts_styles() {

		if ( isset( $_GET['page'] ) && 'responsive' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_script(
				'responsive-add-ons-getting-started-jsfile',
				RESPONSIVE_ADDONS_URI . 'admin/js/responsive-add-ons-getting-started.js',
				array( 'jquery' ),
				RESPONSIVE_ADDONS_VER,
				true
			);

			global $wcam_lib_responsive_addons;
			$instance_key = '';
			if ( ! empty( $wcam_lib_responsive_addons ) && isset( $wcam_lib_responsive_addons->wc_am_instance_id ) ) {
				$instance_key = $wcam_lib_responsive_addons->wc_am_instance_id;
			}

			
			$data = array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'ccAppURL'    => CC_APP_URL,
				'_ajax_nonce' => wp_create_nonce( 'responsive-addons' ),
				'_nonce'      => wp_create_nonce( 'wp_rest' ),
				'site_url'    => rawurlencode( get_site_url() ),
				'cookies'     => $_COOKIE,
				'instance'    => $instance_key,
				'version'	  => RESPONSIVE_ADDONS_VER,
			);


			wp_localize_script( 'responsive-add-ons-getting-started-jsfile', 'responsiveAddonsGettingStarted', $data );

			// Responsive Getting Started admin styles.
			wp_register_style( 'responsive-add-ons-getting-started-csfile', RESPONSIVE_ADDONS_URI . 'admin/css/responsive-add-ons-getting-started.css', false, RESPONSIVE_ADDONS_VER );
			wp_enqueue_style( 'responsive-add-ons-getting-started-csfile' );
		}
	}

	/**
	 * Adds API Connection Tab inside themes settings tab.
	 */
	public function responsive_theme_app_connection_setting_item() {
		echo wp_kses_post(
			'
			<div tabindex="3" class="responsive-theme-setting-item d-flex" id="responsive-setting-item-app-connection-tab" role="button">
				<span class="responsive-theme-setting-item-icon dashicons dashicons-admin-users responsive-theme-setting-active-tab"></span>
				<p class="responsive-theme-setting-item-title responsive-theme-setting-active-tab">Connect Account</p>
			</div>
		'
		);
	}

	/**
	 * Render App Connection Settings tab content.
	 */
	public function responsive_add_ons_app_connection_setting_content() {
		require_once RESPONSIVE_ADDONS_DIR . 'admin/partials/getting-started/responsive-app-connection-setting.php';
	}

	/**
	 * Save White Label Settings.
	 *
	 * @since 2.9.3
	 * @access public
	 */
	public function responsive_pro_white_label_settings() {

		check_ajax_referer( 'white_label_settings', '_nonce' );

		$settings = self::raddons_get_white_label_settings();

		$settings['plugin_author']        = isset( $_POST['authorName'] ) ? sanitize_text_field( wp_unslash( $_POST['authorName'] ) ) : '';
		$settings['plugin_name']          = isset( $_POST['pluginName'] ) ? sanitize_text_field( wp_unslash( $_POST['pluginName'] ) ) : '';
		$settings['plugin_desc']          = isset( $_POST['pluginDesc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pluginDesc'] ) ) : '';
		$settings['plugin_uri']           = isset( $_POST['pluginURL'] ) ? sanitize_text_field( wp_unslash( $_POST['pluginURL'] ) ) : '';
		$settings['plugin_website_uri']   = isset( $_POST['websiteURL'] ) ? sanitize_text_field( wp_unslash( $_POST['websiteURL'] ) ) : '';
		$settings['hide_wl_settings']     = isset( $_POST['hideSettings'] ) ? sanitize_text_field( wp_unslash( $_POST['hideSettings'] ) ) : '';
		$settings['theme_name']           = isset( $_POST['themeName'] ) ? sanitize_text_field( wp_unslash( $_POST['themeName'] ) ) : '';
		$settings['theme_desc']           = isset( $_POST['themeDesc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['themeDesc'] ) ) : '';
		$settings['theme_screenshot_url'] = isset( $_POST['themeScreenshotURL'] ) ? sanitize_textarea_field( wp_unslash( $_POST['themeScreenshotURL'] ) ) : '';
		$settings['theme_icon_url']       = isset( $_POST['themeIconURL'] ) ? sanitize_textarea_field( wp_unslash( $_POST['themeIconURL'] ) ) : '';

		update_option( 'rpro_elementor_settings', $settings );

		wp_send_json_success( array( 'msg' => 'Settings Saved' ) );
	}

	/**
	 * Get whitelabelled website url for footer.
	 *
	 * @since 3.0.0
	 * @param string $link Default url.
	 * @return string URL for updated whitelabelled icon.
	 */
	public function white_label_theme_powered_by_link( $link ) {
		$settings = self::raddons_get_white_label_settings();
		$link     = $settings['plugin_website_uri'];
		return esc_url( $link );
	}

	/**
	 * Get whitelabelled theme name for footer.
	 *
	 * @since 3.0.0
	 * @param string $text Default text.
	 * @return string text for updated whitelabelled theme name.
	 */
	public function white_label_theme_powered_by_text( $text ) {
		$settings = self::raddons_get_white_label_settings();
		$text     = $settings['theme_name'];
		return $text;
	}

	/**
	 * Enable/Disables the MegaMenu Feature on switch toggle.
	 *
	 * @since 3.0.0
	 * @access public
	 */
	public function responsive_pro_enable_megamenu() {

		check_ajax_referer( 'rpro_toggle_megamenu', '_nonce' );

		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

		update_option( 'rpo_megamenu_enable', $value );

		wp_send_json_success();
	}

	/**
	 * Enable/Disables the Woocommerce customizer settings on switch toggle.
	 *
	 * @since 3.0.0
	 * @access public
	 */
	public function responsive_pro_enable_woocommerce() {

		check_ajax_referer( 'rpro_toggle_woocommerce', '_nonce' );

		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

		update_option( 'rpro_woocommerce_enable', $value );

		wp_send_json_success();
	}

	/**
	 * Enable/Disables the Custom Fonts Feature on switch toggle.
	 *
	 * @since 3.2.1
	 * @access public
	 */
	public function responsive_plus_enable_custom_fonts() {

		check_ajax_referer( 'rplus_toggle_custom_fonts', '_nonce' );

		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

		update_option( 'rplus_custom_fonts_enable', $value );

		wp_send_json_success();
	}

	/**
	 * Enable/Disables the Site Builder Feature on Switch toggle.
	 *
	 * @since 3.3.0
	 * @access public
	 */
	public function responsive_plus_enable_site_builder() {

		check_ajax_referer( 'rplus_toggle_site_builder', '_nonce' );

		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

		update_option( 'rplus_site_builder_enable', $value );

		wp_send_json_success();
		
	}

	/**
	 * Enqueue Custom Fonts.
	 *
	 * @since 3.0.2
	 * @access public
	 */
	public function responsive_addons_enqueue_custom_fonts() {
		wp_enqueue_script( 'responsive-add-ons-custom-fonts-js', RESPONSIVE_ADDONS_URI . 'includes/custom-fonts/assets/js/responsive-add-ons-custom-fonts.js', array(), RESPONSIVE_ADDONS_VER, true );
		wp_enqueue_style( 'responsive-add-ons-custom-fonts-css', RESPONSIVE_ADDONS_URI . 'includes/custom-fonts/assets/css/responsive-add-ons-custom-fonts.css', array(), RESPONSIVE_ADDONS_VER );
	}

	/**
	 * Register custom font menu.
	 *
	 * @since 3.0.2
	 */
	public function responsive_addons_register_custom_fonts_menu() {

		$title = apply_filters( 'responsive_custom_fonts_menu_title', __( 'Custom Fonts', 'responsive-add-ons' ) );
		add_submenu_page(
			'themes.php',
			$title,
			$title,
			Responsive_Add_Ons_Custom_Fonts_Taxonomy::$capability,
			'edit-tags.php?taxonomy=' . Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug
		);
	}

	/**
	 * Highlight custom font menu.
	 *
	 * @since 3.0.2
	 */
	public function responsive_addons_custom_fonts_menu_highlight() {
		global $parent_file, $submenu_file;

		if ( 'edit-tags.php?taxonomy=' . Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug === $submenu_file ) {
			$parent_file = 'themes.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		if ( get_current_screen()->id != 'edit-' . Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ) {
			return;
		}

		?>
		<style>#addtag div.form-field.term-slug-wrap, #edittag tr.form-field.term-slug-wrap { display: none; }
			#addtag div.form-field.term-description-wrap, #edittag tr.form-field.term-description-wrap { display: none; }</style><script>jQuery( document ).ready( function( $ ) {
				var $wrapper = $( '#addtag, #edittag' );
				$wrapper.find( 'tr.form-field.term-name-wrap p, div.form-field.term-name-wrap > p' ).text( '<?php esc_html_e( 'The name of the font as it appears in the customizer options.', 'responsive-add-ons' ); ?>' );
			} );</script>
			<?php
	}

	/**
	 * Manage Columns
	 *
	 * @since 3.0.2
	 * @param array $columns default columns.
	 * @return array $columns updated columns.
	 */
	public function responsive_addons_manage_columns( $columns ) {

		$screen = get_current_screen();
		// If current screen is add new custom fonts screen.
		if ( isset( $screen->base ) && 'edit-tags' == $screen->base ) {

			$old_columns = $columns;
			$columns     = array(
				'cb'   => $old_columns['cb'],
				'name' => $old_columns['name'],
			);

		}
		return $columns;
	}

	/**
	 * Add new Taxonomy data
	 *
	 * @since 3.0.2
	 */
	public function responsive_addons_add_new_taxonomy_data() {
		$this->responsive_addons_font_file_new_field( 'font_woff_2', __( 'Upload Font', 'responsive-add-ons' ), __( 'Allowed Font types are .woff2, .woff, .ttf, .eot, .svg, .otf', 'responsive-add-ons' ) );

		$this->responsive_addons_select_new_field(
			'font-display',
			__( 'Font Display', 'responsive-add-ons' ),
			__( 'Select font-display property for this font', 'responsive-add-ons' ),
			array(
				'auto'     => 'auto',
				'block'    => 'block',
				'swap'     => 'swap',
				'fallback' => 'fallback',
				'optional' => 'optional',
			)
		);
	}

	/**
	 * Edit Taxonomy data
	 *
	 * @since 3.0.2
	 * @param object $term taxonomy terms.
	 */
	public function responsive_addons_edit_taxonomy_data( $term ) {

		$data = Responsive_Add_Ons_Custom_Fonts_Taxonomy::get_font_links( $term->term_id );
		$this->responsive_addons_font_file_edit_field( 'font_woff_2', __( 'Upload Font', 'responsive-add-ons' ), $data['font_woff_2'], __( 'Allowed Font types are .woff2, .woff, .ttf, .eot, .svg, .otf', 'responsive-add-ons' ) );

		$this->responsive_addons_select_edit_field(
			'font-display',
			__( 'Font Display', 'responsive-add-ons' ),
			$data['font-display'],
			__( 'Select font-display property for this font', 'responsive-add-ons' ),
			array(
				'auto'     => 'Auto',
				'block'    => 'Block',
				'swap'     => 'Swap',
				'fallback' => 'Fallback',
				'optional' => 'Optional',
			)
		);
	}

	/**
	 * Save Taxonomy meta data value
	 *
	 * @since 3.0.2
	 * @param int $term_id current term id.
	 */
	public function responsive_addons_save_metadata( $term_id ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if custom fonts taxonomy POST data is set.
		// Check for nonce to verify the request's authenticity.
		if (
			! isset( $_POST['_custom_fonts_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_custom_fonts_nonce'] ) ), 'save_custom_fonts' )
		) {
			wp_die( esc_html__( 'Security check failed. Please try again.', 'responsive-add-ons' ) );
		}

		// Check if custom fonts taxonomy POST data is set.
		if ( isset( $_POST[ Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ] ) ) {
			// Sanitize and process input values.
			$value = array_map( 'sanitize_text_field', wp_unslash( $_POST[ Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ] ) );
			Responsive_Add_Ons_Custom_Fonts_Taxonomy::update_font_links( $value, $term_id );
		}
	}

	/**
	 * Allowed mime types and file extensions
	 *
	 * @since 3.0.2
	 * @param array $mimes Current array of mime types.
	 * @return array $mimes Updated array of mime types.
	 */
	public function responsive_addons_add_fonts_to_allowed_mimes( $mimes ) {
		$mimes['woff']  = 'application/x-font-woff';
		$mimes['woff2'] = 'application/x-font-woff2';
		$mimes['ttf']   = 'application/x-font-ttf';
		$mimes['eot']   = 'application/vnd.ms-fontobject';
		$mimes['otf']   = 'font/otf';

		return $mimes;
	}

	/**
	 * Correct the mome types and extension for the font types.
	 *
	 * @param array  $defaults File data array containing 'ext', 'type', and
	 *                                          'proper_filename' keys.
	 * @param string $file                      Full path to the file.
	 * @param string $filename                  The name of the file (may differ from $file due to
	 *                                          $file being in a tmp directory).
	 * @return Array File data array containing 'ext', 'type', and
	 */
	public function responsive_addons_update_mime_types( $defaults, $file, $filename ) {
		if ( 'ttf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
			$defaults['type'] = 'application/x-font-ttf';
			$defaults['ext']  = 'ttf';
		}

		if ( 'otf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
			$defaults['type'] = 'application/x-font-otf';
			$defaults['ext']  = 'otf';
		}

		return $defaults;
	}

	/**
	 * Enqueue Render Fonts
	 *
	 * @since 3.0.2
	 * @param array $load_fonts fonts.
	 */
	public function responsive_addons_render_fonts( $load_fonts ) {

		$fonts = Responsive_Add_Ons_Custom_Fonts_Taxonomy::get_fonts();

		foreach ( $load_fonts  as $load_font_name => $load_font ) {
			if ( array_key_exists( $load_font_name, $fonts ) ) {
				unset( $load_fonts[ $load_font_name ] );
			}
		}
		return $load_fonts;
	}

	/**
	 * Add Custom Font list into customizer.
	 *
	 * @since  3.0.2
	 * @param string $value selected font family.
	 */
	public function responsive_addons_add_customizer_font_list( $value ) {

		$fonts = Responsive_Add_Ons_Custom_Fonts_Taxonomy::get_fonts();

		echo '<optgroup label="' . esc_attr( 'Custom Fonts' ) . '">';

		foreach ( $fonts as $font => $links ) {
			echo '<option value="' . esc_attr( $font ) . '" ' . selected( $font, $value, false ) . '>' . esc_attr( $font ) . '</option>';
		}
	}

	/**
	 * Enqueue Scripts
	 *
	 * @since 3.0.2
	 */
	public function responsive_addons_add_style() {
		$fonts = Responsive_Add_Ons_Custom_Fonts_Taxonomy::get_fonts();
		if ( ! empty( $fonts ) ) {
			foreach ( $fonts  as $load_font_name => $load_font ) {
				$this->render_font_css( $load_font_name );
			}
			?>
			<style type="text/css">
				<?php echo wp_strip_all_tags( $this->font_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</style>
			<?php
		}
	}

	/**
	 * Create css for font-face
	 *
	 * @since 3.0.2
	 * @param array $font selected font from custom font list.
	 */
	private function render_font_css( $font ) {
		$fonts = Responsive_Add_Ons_Custom_Fonts_Taxonomy::get_links_by_name( $font );

		foreach ( $fonts as $font => $links ) :
			$css  = '@font-face { font-family:' . esc_attr( $font ) . ';';
			$css .= 'src:';
			$arr  = array();
			if ( $links['font_woff_2'] ) {
				$arr[] = 'url(' . esc_url( $links['font_woff_2'] ) . ") format('woff2')";
			}
			if ( $links['font_woff'] ) {
				$arr[] = 'url(' . esc_url( $links['font_woff'] ) . ") format('woff')";
			}
			if ( $links['font_ttf'] ) {
				$arr[] = 'url(' . esc_url( $links['font_ttf'] ) . ") format('truetype')";
			}
			if ( $links['font_otf'] ) {
				$arr[] = 'url(' . esc_url( $links['font_otf'] ) . ") format('opentype')";
			}
			if ( $links['font_svg'] ) {
				$arr[] = 'url(' . esc_url( $links['font_svg'] ) . '#' . esc_attr( strtolower( str_replace( ' ', '_', $font ) ) ) . ") format('svg')";
			}
			$css .= join( ', ', $arr );
			$css .= ';';
			$css .= 'font-display: ' . esc_attr( $links['font-display'] ) . ';';
			$css .= '}';
		endforeach;

		$this->font_css .= $css;
	}

	/**
	 * Add Taxonomy data field
	 *
	 * @since 3.0.2
	 * @param int    $id current term id.
	 * @param string $title font type title.
	 * @param string $description title font type description.
	 * @param string $value title font type meta values.
	 */
	protected function responsive_addons_font_file_new_field( $id, $title, $description, $value = '' ) {
		?>
		<div class="responsive-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap" >

			<label for="font-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></label>
			<input type="text" id="font-<?php echo esc_attr( $id ); ?>" class="responsive-custom-fonts-link <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
			<a href="#" class="responsive-custom-fonts-upload button" data-upload-type="<?php echo esc_attr( $id ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
				<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
				<path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
			</svg>
			</a>
			<p><?php echo esc_html( $description ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render select field for the new font screen.
	 *
	 * @param String $id Field ID.
	 * @param String $title Field Title.
	 * @param String $description Field Description.
	 * @param Array  $select_fields Select fields as Array.
	 * @return void
	 */
	protected function responsive_addons_select_new_field( $id, $title, $description, $select_fields ) {
		?>
		<div class="responsive-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap" >
			<label for="font-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></label>
			<select type="select" id="font-<?php echo esc_attr( $id ); ?>" class="responsive-custom-font-select-field <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" />
				<?php
				foreach ( $select_fields as $key => $value ) {
					?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>;
				<?php } ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Add Taxonomy data field
	 *
	 * @since 3.0.2
	 * @param int    $id current term id.
	 * @param string $title font type title.
	 * @param string $value title font type meta values.
	 * @param string $description title font type description.
	 */
	protected function responsive_addons_font_file_edit_field( $id, $title, $value, $description ) {
		?>
		<tr class="responsive-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap ">
			<th scope="row">
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
			</th>
			<td>
				<input id="metadata-<?php echo esc_attr( $id ); ?>" type="text" class="responsive-custom-fonts-link <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
				<a href="#" class="responsive-custom-fonts-upload button" data-upload-type="<?php echo esc_attr( $id ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
						<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
						<path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
					</svg>
				</a>
				<p><?php echo esc_html( $description ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render select field for the edit font screen.
	 *
	 * @param String $id Field ID.
	 * @param String $title Field Title.
	 * @param String $saved_val Field Value.
	 * @param String $description Field Description.
	 * @param Array  $select_fields Select fields as Array.
	 * @return void
	 */
	private function responsive_addons_select_edit_field( $id, $title, $saved_val, $description, $select_fields ) {
		?>
		<tr class="responsive-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap ">
			<th scope="row">
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
			</th>
			<td>
			<select type="select" id="font-<?php echo esc_attr( $id ); ?>" class="responsive-custom-font-select-field <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" />
				<?php
				foreach ( $select_fields as $key => $value ) {
					?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $saved_val ); ?>><?php echo esc_html( $value ); ?></option>;
				<?php } ?>
			</select>
				<p><?php echo esc_html( $description ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get current installation import capabilities
	 *
	 * @since 3.0.3
	 */
	public function responsive_addons_get_user_import_capabilities() {

		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-add-ons' ) );
		}

		require_once RESPONSIVE_ADDONS_DIR . 'includes/settings/class-responsive-add-ons-settings.php';
		$settings = new Responsive_Add_Ons_Settings();

		global $wcam_lib_responsive_addons;

		$api_key    = $settings->get( 'api', 'token' );
		$product_id = $settings->get( 'account', 'product_id' );

		if ( empty( $api_key ) || '' === $api_key || empty( $product_id ) || '' === $product_id ) {
			$err_msg = __( 'Connection details are missing. Please reconnect to Cyberchimps Responsive Domain to continue.', 'responsive-add-ons' );
			wp_send_json_error(
				array(
					'message' => $err_msg,
					'error'   => true,
				),
			);
		}

		$args = array(
			'api_key' => $api_key,
		);

		update_option( $wcam_lib_responsive_addons->wc_am_product_id, $product_id );
		update_option(
			$wcam_lib_responsive_addons->data_key,
			array(
				$wcam_lib_responsive_addons->data_key . '_api_key' => $api_key,
			),
		);

		$activate_args = $wcam_lib_responsive_addons->activate( $args, $product_id );
		$status_args   = $wcam_lib_responsive_addons->status( $args, $product_id );
		$ready_site_subscribe_checkbox = isset( $_POST['ready_sites_subscripiton_checkbox'] ) ? sanitize_key( wp_unslash( $_POST['ready_sites_subscripiton_checkbox'] ) ) : '';
		$userEmail = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';

		$response      = $this->cc_app_auth->post(
			'plugin/importcaps',
			wp_json_encode(
				array(
					'id'                  => $settings->get_user_id(),
					'platform'            => 'wordpress',
					'demo_type'           => isset( $_POST['demo_type'] ) ? sanitize_text_field( wp_unslash( $_POST['demo_type'] ) ) : '',
					'status_args'         => $status_args,
					'activate_args'       => $activate_args,
					'wc_am_activated_key' => $wcam_lib_responsive_addons->data,
					'ready_site_subscribe_checkbox' => $ready_site_subscribe_checkbox,
					'user_email'          => $userEmail,
				)
			)
		);
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$message 	   = isset( $response_body->message ) ? $response_body->message : '';

		if ( empty( $message ) ) {
			if ( $response_code >= 500 ) {
				$message = __( 'Server is temporarily unavailable', 'responsive-add-ons' );
			} elseif ( $response_code >= 400 ) {
				$message = __( 'Request failed due to an authentication/authorization issue', 'responsive-add-ons' );
			}
		}

		if ( 200 !== $response_code ) {
			$formatted_message = sprintf(
				/* translators: %1$s is the error message returned by the API. */
				__( '%1$s', 'responsive-add-ons' ),
				$message
			);

			wp_send_json_error(
				array(
					'message'    => $formatted_message,
					'error'      => true,
					'error_code' => $response_code,
				)
			);
		}

		if ( isset( $response_body->allow_import ) && ! $response_body->allow_import ) {
			wp_send_json_error(
				array(
					'message' => "You don't have an active membership of Cyberchimps Responsive Domain.",
					'error'   => true,
				),
			);
		}
		if ( isset( $response_body->update_options ) ) {
			if ( 'success' === $response_body->update_options ) {
				update_option( $wcam_lib_responsive_addons->wc_am_activated_key, $response_body->activated_key );
				update_option( $wcam_lib_responsive_addons->wc_am_deactivate_checkbox_key, $response_body->deactivate_checkbox_key );
			} elseif ( 'fail_1' === $response_body->update_options ) {
				if ( isset( $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_activated_key ] ) ) {
					update_option( $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_activated_key ], $response_body->activated_key );
				}
			} elseif ( 'fail_2' === $response_body->update_options ) {
				if ( isset( $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_activated_key ] ) ) {
					update_option( $wcam_lib_responsive_addons->data[ $wcam_lib_responsive_addons->wc_am_activated_key ], $response_body->activated_key );
				}
			}
		}

		if ( isset( $response_body->connection_status ) ) {
			wp_send_json_success(
				array(
					'connection_status' => $response_body->connection_status,
					'error'             => false,
				),
			);
		} else if ( isset($response_body->error_code) ) {
			wp_send_json_error(
				array(
					'message'    => $response_body->message,
					'error_code' => $response_body->error_code,
					'error'      => true,
				)
			);
		} else {
			wp_send_json_success(
				array(
					'activate_results' => $response_body->activate_results ?? null,
					'message'          => $response_body->message ?? "Failed to verify the connection. Try import again.",
				)
			);
		}
	}

	public function responsive_site_builder() {

		?>
		 <div class="responsive-sb-menu-page-wrapper">
			<div id="responsive-sb-menu-page">
				<div class="responsive-sb-menu-page-content">
					<div id="responsive-sb-app-root" class="responsive-sb-app-root"></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Verify if the version of responsive theme is compatible with site builder or not.
	 *
	 * @since 3.3.0
	 */
	public function responsive_addons_is_theme_site_builder_compatible() {
		$theme = wp_get_theme();
		if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
			if ( 'Responsive' === $theme->parent_theme ) {
				$theme = wp_get_theme( 'responsive' );
			}
		} else {
			return false;
		}

		if ( version_compare( $theme['Version'], '6.2.1', '>' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Update user consent for Mix Panel track.
	 *
	 * @since 3.3.3
	 */
	public function responsive_addons_update_user_consent() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-add-ons' ) );
		}

		$consent = isset( $_POST['consent'] ) ? sanitize_text_field( wp_unslash( $_POST['consent'] ) ) : 'no';

		update_option( 'responsive_addons_contribution_consent', $consent );

		wp_send_json_success();
	}

	/**
	 * Admin Body Classes
	 *
	 * @since 3.4.0
	 * @param string $classes Space separated class string.
	 * @return void
	 */
	public function admin_body_class( $classes = '' ) {
		$theme_builder_class = isset( $_GET['page'] ) && 'responsive_add_ons' === $_GET['page'] ? 'responsive-add-ons-import-sites' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Fetching a $_GET value, no nonce available to validate.
		$classes            .= ' ' . $theme_builder_class . ' ';

		return $classes;
	}

}
