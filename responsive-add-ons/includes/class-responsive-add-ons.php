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
			add_option( 'rplus_custom_fonts_enable', 'on' );
		}

		// Display Custom Fonts only when Responsive theme is active.
		$theme = wp_get_theme();
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
			add_action( 'wp_ajax_responsive-ready-sites-remote-request', array( $this, 'remote_request' ) );
			add_action( 'wp_ajax_responsive-ready-sites-elementor_page_import_process', array( $this, 'elementor_page_import_process' ) );
			add_action( 'wp_ajax_responsive-ready-sites-set-reset-data', array( $this, 'set_reset_data' ) );
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

		// Add rating links to plugin's description in plugins table
		add_filter('plugin_row_meta', array($this, 'responsive_addons_rate_plugin_link'), 10, 2);

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

		// Ask for review notice
		add_action( 'admin_notices', array( $this, 'responsive_addons_ask_for_review_notice' ) );
		add_action( 'admin_init', array( $this, 'responsive_addons_notice_dismissed' ) );
		add_action( 'admin_init', array( $this, 'responsive_addons_notice_change_timeout' ) );

		self::set_api_url();
		self::set_rst_blocks_api_url();
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
				esc_html__( 'Hello! Seems like you have used Responsive Starter Templates plugin to build this website — Thanks a ton!', 'responsive-addons' ),
				esc_html__( 'Could you please do us a BIG favor and give it a 5-star rating on WordPress? This would boost our motivation and help other users make a comfortable decision while choosing the Responsive Starter Templates plugin.', 'responsive-addons' ),
				esc_html__( 'Ok, you deserve it', 'responsive-addons' ),
				esc_html__( 'Nope, maybe later', 'responsive-addons' ),
				esc_html__( 'I already did', 'responsive-addons' )
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

		if ( 'on' === get_option( 'rpro_woocommerce_enable' ) && 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
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

		$image_path = RESPONSIVE_ADDONS_URI . 'admin/images/responsive-starter-templates-thumbnail.jpg';
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
			$image_path_underline = RESPONSIVE_ADDONS_URI . 'admin/images/underline.png';
			$image_path_close     = RESPONSIVE_ADDONS_URI . 'admin/images/close_icon.png';
			if ( ! is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) ) {
				?>
		<div id="responsive-welcome_banner-section" class="responsive-notice notice">
			<div class="reponsive-welcome_banner-welcome-section">
				<div class="reponsive-welcome_banner-welcome-section-content">
					<h1 class="reponsive-welcome_banner-welcome-section-text"><?php echo esc_html__( 'Welcome To Responsive Starter Templates', 'responsive-addons' ); ?></h1>
					<img src="<?php echo esc_attr( $image_path_underline ); ?>" alt="underline" class="underline_image">
					<p class="reponsive-welcome_banner-welcome-section-tag"><?php echo esc_html__( 'Create professionally designed pixel-perfect websites in minutes.' ); ?></p>
					<a class="responsive-welcome_banner-explore-button" href="<?php echo esc_url( admin_url( 'admin.php?page=responsive_add_ons' ) ); ?>"><?php echo esc_html__( 'Explore Templates', 'responsive-addons' ); ?></a>
				</div>
				<a class="responsive-welcome_banner-close-icon" id="rst_welcome_banner_close_icon" href="#">
					<img src="<?php echo esc_attr( $image_path_close ); ?>" alt="close">
				</a>
			</div>
			<div class="responsive-welcome_banner-features-section">
				<div class="responsive-welcome_banner-features">
					<h4>Features</h4>
					<div class="responsive-welcome_banner-feature-rows-section">
						<div class="responsive-welcome_banner-features-row">
							<div class="feature-row">
								<span class="dashicons dashicons-saved"></span>
								<p class="feature-row-text"><?php echo esc_html__( 'Loads Blazing Fast', 'responsive-addons' ); ?></p>
							</div>
							<div class="feature-row">
								<span class="dashicons dashicons-saved"></span>
								<p class="feature-row-text"><?php echo esc_html__( 'Customizable Settings', 'responsive-addons' ); ?></p>
							</div>
						</div>
						<div class="responsive-welcome_banner-features-row">
							<div class="feature-row">
								<span class="dashicons dashicons-saved"></span>
								<p class="feature-row-text"><?php echo esc_html__( 'Pre-designed pages', 'responsive-addons' ); ?></p>
							</div>
							<div class="feature-row">
								<span class="dashicons dashicons-saved"></span>
								<p class="feature-row-text"><?php echo esc_html__( 'Contact Form', 'responsive-addons' ); ?></p>
							</div>
						</div>
						<div class="responsive-welcome_banner-features-row">
							<div class="feature-row">
								<span class="dashicons dashicons-saved"></span>
								<p class="feature-row-text"><?php echo esc_html__( 'In-time Support', 'responsive-addons' ); ?></p>
							</div>
							<div class="feature-row">
								<span class="dashicons dashicons-saved"></span>
								<p class="feature-row-text"><?php echo esc_html__( '1- Click Import', 'responsive-addons' ); ?></p>
							</div>
						</div>
					</div>
				</div>
				
				<?php
				$user_details = get_option( 'reads_app_settings' );
				if ( empty( $user_details ) || ( ! empty( $user_details ) && 'free' === $user_details['account']['plan'] ) ) {
					?>
					<a href="<?php echo esc_url( 'https://cyberchimps.com/pricing/?utm_source=wpdash&utm_medium=RST_plugin&utm_campaign=intro_banner&utm_content=upgrade-to-pro' ); ?>" target="_blank" class="responsive-welcome_banner-upgrade-button">
					<p class="upgrade-button-text"><?php echo esc_html__( 'Upgrade To Pro', 'responsive-addons' ); ?> </p>
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</a>
				<?php } ?>
				
			</div>
			<div class="responsive-welcome_banner-images_collection">
				<div class="rst_welcomeBanner_image1"></div>
				<div class="rst_welcomeBanner_image2"></div>
				<div class="rst_welcomeBanner_image3"></div>
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
			wp_send_json_error( __( 'You are not allowed to activate the Theme', 'responsive-addons' ) );
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
			wp_send_json_error( __( 'You are not allowed to activate the Theme', 'responsive-addons' ) );
		}

		switch_theme( 'responsive' );

		wp_send_json_success(
			array(
				'success' => true,
				'message' => __( 'Theme Activated', 'responsive-addons' ),
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
		self::$api_url = apply_filters( 'responsive_ready_sites_api_url', 'https://ccreadysites.cyberchimps.com/wp-json/wp/v2/' );
	}

	/**
	 * Setter for rst blocks $rst_blocks_api_url
	 *
	 * @since  2.9.1
	 */
	public static function set_rst_blocks_api_url() {
		self::$rst_blocks_api_url = apply_filters( 'rst_blocks_api_url', 'https://ccreadysites.cyberchimps.com/ccblocks/wp-json/wp/v2/' );
	}

	/**
	 * Hook into WP admin_init
	 * Responsive 1.x settings
	 *
	 * @param array $options Options.
	 */
	public function admin_init( $options ) {
		$this->init_settings();
	}

	/**
	 * Create plugin translations
	 */
	public function responsive_addons_translations() {
		// Load the text domain for translations.
		load_plugin_textdomain( 'responsive-addons', false, basename( __DIR__ ) . '/languages' );
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

		if ( 'Responsive' === $theme->Name || 'responsive' === $theme->Template || 'Responsive Pro' === $theme->Name || 'responsivepro' === $theme->Template ) {
			return true;
		} else {
			return false;
		}
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
		$settings_link = '<a href="themes.php?page=responsive-add-ons">' . __( 'Settings', 'responsive-addons' ) . '</a>';
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

		wp_enqueue_script( 'install-responsive-theme', RESPONSIVE_ADDONS_URI . 'admin/js/install-responsive-theme.js', array( 'jquery', 'updates' ), RESPONSIVE_ADDONS_VER, true );
		wp_enqueue_style( 'install-responsive-theme', RESPONSIVE_ADDONS_URI . 'admin/css/install-responsive-theme.css', null, RESPONSIVE_ADDONS_VER, 'all' );
		$data = apply_filters(
			'responsive_sites_install_theme_localize_vars',
			array(
				'installed'   => __( 'Installed! Activating..', 'responsive-addons' ),
				'activating'  => __( 'Activating..', 'responsive-addons' ),
				'activated'   => __( 'Activated! Reloading..', 'responsive-addons' ),
				'installing'  => __( 'Installing..', 'responsive-addons' ),
				'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) ),
				'_ajax_nonce' => wp_create_nonce( 'responsive-addons' ),
			)
		);
		wp_localize_script( 'install-responsive-theme', 'ResponsiveInstallThemeVars', $data );
		$settings   = get_option( 'rpro_elementor_settings' );
		$theme_name = ! empty( $settings['theme_name'] ) ? mb_strtolower( $settings['theme_name'], 'UTF-8' ) : 'responsive';
		$theme_name = str_replace( [ ' ', '/' ], '-', $theme_name );

		$characters_to_remove = [ "'", '\\', '?', '|', '*', '"', '`' ];
		$theme_name = str_replace( $characters_to_remove, '', $theme_name );

		$theme_name = preg_replace( '/[^\p{L}\p{N}-]/u', '', $theme_name );


		$pro_plugin_active_status = is_plugin_active( 'responsive-addons-pro/responsive-addons-pro.php' ) ? true : false;
		
		$parts = explode('_page_responsive_add_ons', $hook);
		$encoded_part = isset($parts[0]) ? $parts[0] : '';
		$decoded_theme_name = urldecode($encoded_part);

		if ( ( 'toplevel_page_responsive_add_ons' === $hook || $theme_name . '_page_responsive_add_ons' === $hook  || $theme_name . '_page_responsive_add_ons' === $decoded_theme_name . '_page_responsive_add_ons') && empty( $_GET['action'] ) ) {
			wp_enqueue_script( 'responsive-ready-sites-admin-js', RESPONSIVE_ADDONS_URI . 'admin/js/responsive-ready-sites-admin.js', array( 'jquery', 'wp-util', 'updates', 'jquery-ui-autocomplete', 'canvas-confetti' ), RESPONSIVE_ADDONS_VER, true );
			wp_enqueue_script( 'canvas-confetti', 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js', array(), RESPONSIVE_ADDONS_VER, true );

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
					'importSingleTemplateButtonTitle' => __( 'Import "%s" Template', 'responsive-addons' ),
					'default_page_builder_sites'      => $this->get_sites_by_page_builder(),
					'strings'                         => array(
						'syncCompleteMessage'  => $this->get_sync_complete_message(),
						/* translators: %s is a template name */
						'importSingleTemplate' => __( 'Import "%s" Template', 'responsive-addons' ),
					),
					'dismiss'                         => __( 'Dismiss this notice.', 'responsive-addons' ),
					'syncTemplatesLibraryStart'       => '<span class="message">' . esc_html__( 'Syncing Responsive Starter Templates in the background. The process will complete in just a few seconds. We will notify you once done.', 'responsive-addons' ) . '</span>',
					'activated_first_time'            => get_option( 'ra_first_time_activation' ),
					'hasAppAuth'                      => $this->cc_app_auth->has_auth(),
					'isResponsiveProActive'           => $pro_plugin_active_status,
				)
			);

			wp_localize_script( 'responsive-ready-sites-admin-js', 'responsiveSitesAdmin', $data );

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
				'site_url '   => rawurlencode( get_site_url() ),
				'cookies'     => $_COOKIE,
			);

			wp_localize_script( 'responsive-add-ons-getting-started-jsfile', 'responsiveAddonsGettingStarted', $data );
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

		$message = __( 'Responsive Templates data refreshed!', 'responsive-addons' );
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
		$theme_name = preg_replace( '/[^\p{L}\p{N}-]/u', '', $theme_name );
		
		$parts = explode('_page_responsive_add_ons', $hook);
		$encoded_part = isset($parts[0]) ? $parts[0] : '';
		$decoded_theme_name = urldecode($encoded_part);

		if ( 'toplevel_page_responsive_add_ons' === $hook || $theme_name . '_page_responsive_add_ons_go_pro' === $hook || $theme_name . '_page_responsive_add_ons' === $hook || $theme_name . '_page_responsive_add_ons' === $decoded_theme_name . '_page_responsive_add_ons') {
			// Responsive Ready Sites admin styles.
			wp_register_style( 'responsive-ready-sites-admin', RESPONSIVE_ADDONS_URI . 'admin/css/responsive-ready-sites-admin.css', false, RESPONSIVE_ADDONS_VER );
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
		$user_plan                = $settings['account']['plan'];

		$pro_purchase_url = 'https://cyberchimps.com/responsive-go-pro/?utm_source=free-to-pro&utm_medium=responsive-add-ons&utm_campaign=responsive-pro&utm_content=preview-ready-site';

		/* translators: %s are link. */
		$license_msg = sprintf( __( 'This is a Pro Template available with Responsive Pro. You can purchase it from <a href="%s" target="_blank">here</a>.', 'responsive-addons' ), esc_url( $pro_purchase_url ) );
		/* translators: %s are link. */
		$license_block_msg = sprintf( __( 'This is a Pro Block available with Responsive Pro. You can purchase it from <a href="%s" target="_blank">here</a>.', 'responsive-addons' ), esc_url( $pro_purchase_url ) );

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
				'dismiss_text'                => esc_html__( 'Dismiss', 'responsive-addons' ),
				'noPlugins'                   => __( 'No Plugins Required' ),
				'syncCompleteMessage'         => __( 'Template library refreshed!', 'responsive-addons' ),
				'getProText'                  => __( 'Upgrade to Pro!', 'responsive-addons' ),
				'getProURL'                   => esc_url( 'https://cyberchimps.com/responsive-go-pro/?utm_source=free-to-pro&utm_medium=responsive-add-ons&utm_campaign=responsive-pro&utm_content=preview-ready-site' ),
				'getREAURL'                   => esc_url( 'https://cyberchimps.com/elementor-widgets/docs/how-to-install-activate-the-responsive-elementor-addons/' ),
				'siteURL'                     => site_url(),
				'template'                    => esc_html__( 'Template', 'responsive-addons' ),
				'install_plugin_text'         => esc_html__( 'Install Required Plugins', 'responsive-addons' ),
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
			'site_url '   => rawurlencode( get_site_url() ),
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
			__( 'About', 'responsive-addons' ),
			__( 'Team', 'responsive-addons' ),
			__( 'Testimonial', 'responsive-addons' ),
			__( 'Hero', 'responsive-addons' ),
			__( 'Call to Action', 'responsive-addons' ),
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
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
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
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
		}

		$api_url = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';

		$response = wp_safe_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( wp_remote_retrieve_body( $response ) );
		}

		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body, true );

		if ( ! isset( $data['post-meta']['_elementor_data'] ) ) {
			wp_send_json_error( __( 'Invalid Post Meta', 'responsive-addons' ) );
		}

		$meta = json_decode( $data['post-meta']['_elementor_data'], true );

		$meta = json_decode( $meta, true );

		$post_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';

		if ( empty( $post_id ) || empty( $meta ) ) {
			wp_send_json_error( __( 'Invalid Post ID or Elementor Meta', 'responsive-addons' ) );
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
			wp_send_json_error( __( 'User does not have permission!', 'responsive-addons' ) );
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
	 * Set reset data
	 */
	public function set_reset_data() {
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		global $wpdb;

		$post_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_responsive_ready_sites_imported_post'" );
		$form_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_responsive_ready_sites_imported_wp_forms'" );
		$term_ids = $wpdb->get_col( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key='_responsive_ready_sites_imported_term'" );

		wp_send_json_success(
			array(
				'reset_posts'    => $post_ids,
				'reset_wp_forms' => $form_ids,
				'reset_terms'    => $term_ids,
			)
		);
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
			// Recursively sanitize all values in the array
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
					'message' => __( 'Error: You don\'t have the required permissions to install plugins.', 'responsive-addons' ),
				)
			);
		}
		$pro_plugins = array();
		if ( isset( $_POST['pro_plugin'] ) && is_array( $_POST['pro_plugin'] ) ) {
			// Recursively sanitize all values in the array
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
					'message' => __( 'Error: You don\'t have the required permissions to install plugins.', 'responsive-addons' ),
				)
			);
		}

		if ( ! isset( $_POST['init'] ) || empty( $_POST['init'] ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'Plugins data is missing.', 'responsive-addons' ),
				)
			);
		}

		$data        = array();
		$plugin_init = ( isset( $_POST['init'] ) ) ? wp_kses_post( wp_unslash( $_POST['init'] ) ) : '';
		if ( strpos( $plugin_init, 'give' ) !== false ) {
			$silent = false;
		} else {
			$silent = true;
		}
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
				'message' => __( 'Plugin Activated', 'responsive-addons' ),
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
			<div class="wrap">
					<?php
						$this->init_nav_menu( 'general' );
						do_action( 'responsive_addons_importer_page' );
					?>
			</div>

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
							<div class="responsive-sites-version"><?php esc_html_e( RESPONSIVE_ADDONS_VER, 'responsive-add-ons' ); ?></div>
					</div>
				</div>
				<div id="responsive-sites-filters" class="hide-on-mobile">
					<?php $this->site_filters(); ?>
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
					    <input autocomplete="off" placeholder="<?php esc_html_e( 'Search', 'responsive-addons' ); ?>" 
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
				'id'   => 1,
				'slug' => 'all',
				'name' => 'All',
			),
			array(
				'id'   => 2,
				'slug' => 'elementor',
				'name' => 'Elementor',
			),
			array(
				'id'   => 3,
				'slug' => 'gutenberg',
				'name' => 'WP Editor',
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
     * Add links to plugin's description in plugins table
     *
     * @param array  $links  Initial list of links.
     * @param string $file   Basename of current plugin.
     *
     * @return array
     */
    function responsive_addons_rate_plugin_link( $links, $file ) {
		if ( $file !== plugin_basename( RESPONSIVE_ADDONS_FILE ) ) {
			return $links;
		}
		
		$rate_url = 'https://wordpress.org/support/plugin/responsive-add-ons/reviews/#new-post';
		$rate_link = '<a target="_blank" href="' . esc_url( $rate_url ) . '" title="' . esc_attr__( 'Rate the plugin', 'responsive-addons' ) . '">' . esc_html__( 'Rate the plugin ★★★★★', 'responsive-addons' ) . '</a>';
		$links[] = $rate_link;

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
							    If you like <strong>Responsive Starter Templates</strong>, please leave us a "<a href="https://wordpress.org/support/view/plugin-reviews/responsive-add-ons?filter=5#postform" target="_blank" class="responsive-rating-link" style="text-decoration:none;" data-rated="' . esc_attr__( 'Thanks :)', 'responsive-addons' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>" rating. Thank you!
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
			wp_send_json_error( __( 'User does not have permission!', 'responsive-addons' ) );
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
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
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
			wp_die( esc_html__( 'Sorry, you are not allowed to install themes on this site.' ) );
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
				__( 'Templates', 'responsive-addons' ),
				__( 'Templates', 'responsive-addons' ),
				'manage_options',
				'responsive_add_ons',
				array( $this, 'responsive_add_ons_templates' ),
			);
		}

		if ( ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) && version_compare( RESPONSIVE_THEME_VERSION, '4.9.7.1', '<=' ) ) {

			add_menu_page(
				__( 'Responsive Starter Templates', 'responsive-addons' ),
				__( 'Responsive', 'responsive-addons' ),
				'manage_options',
				'responsive_add_ons',
				array( $this, 'responsive_add_ons_templates' ),
				RESPONSIVE_ADDONS_URI . '/admin/images/responsive-add-ons-menu-icon.png',
				59.5
			);

			add_submenu_page(
				'responsive_add_ons',
				'Responsive Starter Templates',
				__( 'Responsive Templates', 'responsive-addons' ),
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
						// If it exists in the favorites array, add favorite status
						$page_data['favorite_status'] = 'active';
					}
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

	public function get_favorite_template_site_details() {

		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		$favorite_sites = array();

		$current_page_builder_sites = $this->get_sites_by_page_builder();

		$favorite_settings = get_option( 'responsive-sites-favorites', array() );

		if ( ! empty( $favorite_settings ) && ! empty( $current_page_builder_sites ) ) {
			foreach ( $current_page_builder_sites as $site ) {
				if ( in_array( $site['id'], $favorite_settings ) ) {
					// If it exists in the favorites array, add favorite status
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
		$settings_link = '<a href="' . admin_url( 'admin.php?page=responsive_add_ons' ) . '">' . __( 'View Library', 'responsive-addons' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
	/**
	 * Add settings link for theme
	 *
	 * @param array $links holds plugin links.
	 */
	public function responsive_add_view_settings_btn( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=responsive#home' ) . '">' . __( 'Settings', 'responsive-addons' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

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
						<h3 class="responsive-sites-overlay-heading"><?php esc_html_e( 'Go Pro', 'responsive-addons' ); ?></h3>
						<p class="responsive-sites-overlay-content"><?php esc_html_e( 'Get access to all the pro templates and unlock more theme customizer settings using Responsive Pro.', 'responsive-addons' ); ?></p>
						<a href="https://cyberchimps.com/responsive-go-pro/?utm_source=RST_plugin&utm_medium=intro_screen_slidein_btn&utm_campaign=free-to-pro&utm_term=Go_Pro_btn" target="_blank" class="button button-primary responsive-sites-go-pro-btn"><?php esc_html_e( 'Go Pro', 'responsive-addons' ); ?></a>
					</div>
					<div class="responsive-sites-rate-us">
						<h3 class="responsive-sites-overlay-heading"><?php esc_html_e( 'Rate Us', 'responsive-addons' ); ?></h3>
						<p class="responsive-sites-rate-us-stars">
							<?php
							for ( $i = 0; $i < 5; $i++ ) {
								?>
									<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/star-rating.svg' ); ?>">
									<?php
							}
							?>
						</p>
						<p class="responsive-sites-overlay-content"><?php esc_html_e( 'Please let us know what you think, we would appreciate every single review.', 'responsive-addons' ); ?></p>
						<a href="https://wordpress.org/support/plugin/responsive-add-ons/reviews/" target="_blank" class="responsive-sites-rate-us-btn"><?php esc_html_e( 'Submit Review', 'responsive-addons' ); ?></a>
					</div>
					<div class="responsive-sites-help-center">
						<h3 class="responsive-sites-overlay-heading"><?php esc_html_e( 'Help Center', 'responsive-addons' ); ?></h3>
						<p class="responsive-sites-overlay-content"><?php esc_html_e( 'Read the documentation to find answers to your questions.', 'responsive-addons' ); ?></p>
						<a href="https://cyberchimps.com/docs/responsive-plus/responsive-starter-templates-plugin/" target="_blank" class="responsive-sites-help-center-btn"><?php esc_html_e( 'Docs', 'responsive-addons' ); ?></a>
						<?php esc_html_e( 'or', 'responsive-addons' ); ?>
						<a href="https://www.facebook.com/groups/responsive.theme/" target="_blank" class="responsive-sites-community-support-btn"><?php esc_html_e( 'Visit Facebook Group', 'responsive-addons' ); ?></a>
					</div>
					<div class="responsive-sites-video-guides">
						<h3 class="responsive-sites-overlay-heading"><?php esc_html_e( 'Video Guides', 'responsive-addons' ); ?></h3>
						<p class="responsive-sites-overlay-content"><?php esc_html_e( 'Browse through these video tutorials to learn more about how the plugin functions.', 'responsive-addons' ); ?></p>
						<a href="https://www.youtube.com/playlist?list=PLXTwxw3ZJwPSpE3RYanAdYgnDptbSvjXl" target="_blank" class="responsive-sites-video-guides-btn"><?php esc_html_e( 'Watch Now', 'responsive-addons' ); ?></a>
					</div>
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

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You are not allowed to perform this action', 'responsive-addons' );
		}
		// Verify Nonce.
		check_ajax_referer( 'responsive-addons', '_ajax_nonce' );

		$site_id = isset( $_POST['site_id'] ) ? sanitize_key( $_POST['site_id'] ) : '';

		if ( empty( $site_id ) ) {
			wp_send_json_error();
		}

		$favorite_settings = get_option( 'responsive-sites-favorites', array() );

		if ( false !== $favorite_settings && is_array( $favorite_settings ) ) {
			self::$new_favorites = $favorite_settings;
		}

		$is_favorite = isset( $_POST['is_favorite'] ) ? sanitize_key( $_POST['is_favorite'] ) : '';

		if ( 'false' === $is_favorite ) {
			if ( in_array( $site_id, self::$new_favorites, true ) ) {
				$key = array_search( $site_id, self::$new_favorites, true );
				unset( self::$new_favorites[ $key ] );
			}
		} elseif ( ! in_array( $site_id, self::$new_favorites, true ) ) {
				array_push( self::$new_favorites, $site_id );
		}

		update_option( 'responsive-sites-favorites', self::$new_favorites, 'no' );

		wp_send_json_success( self::$new_favorites );
	}

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
			__( 'Templates', 'responsive-elementor-addons' ),
			__( 'Templates', 'responsive-elementor-addons' ),
			'manage_options',
			'responsive_add_ons',
			array( $this, 'responsive_add_ons_templates' ),
		);
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

			$data = array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'ccAppURL'    => CC_APP_URL,
				'_ajax_nonce' => wp_create_nonce( 'responsive-addons' ),
				'_nonce'      => wp_create_nonce( 'wp_rest' ),
				'site_url '   => rawurlencode( get_site_url() ),
				'cookies'     => $_COOKIE,
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

		$title = apply_filters( 'responsive_custom_fonts_menu_title', __( 'Custom Fonts', 'responsive-addons' ) );
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
				$wrapper.find( 'tr.form-field.term-name-wrap p, div.form-field.term-name-wrap > p' ).text( '<?php esc_html_e( 'The name of the font as it appears in the customizer options.', 'responsive-addons' ); ?>' );
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
		$this->responsive_addons_font_file_new_field( 'font_woff_2', __( 'Upload Font', 'responsive-addons' ), __( 'Allowed Font types are .woff2, .woff, .ttf, .eot, .svg, .otf', 'responsive-addons' ) );

		$this->responsive_addons_select_new_field(
			'font-display',
			__( 'Font Display', 'responsive-addons' ),
			__( 'Select font-display property for this font', 'responsive-addons' ),
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
		$this->responsive_addons_font_file_edit_field( 'font_woff_2', __( 'Upload Font', 'responsive-addons' ), $data['font_woff_2'], __( 'Allowed Font types are .woff2, .woff, .ttf, .eot, .svg, .otf', 'responsive-addons' ) );

		$this->responsive_addons_select_edit_field(
			'font-display',
			__( 'Font Display', 'responsive-addons' ),
			$data['font-display'],
			__( 'Select font-display property for this font', 'responsive-addons' ),
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

		if ( isset( $_POST[ Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ] ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Missing
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$value = array_map( 'esc_attr', wp_unslash( $_POST[ Responsive_Add_Ons_Custom_Fonts_Taxonomy::$register_taxonomy_slug ] ) );
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
			wp_send_json_error( __( 'You are not allowed to perform this action', 'responsive-addons' ) );
		}

		require_once RESPONSIVE_ADDONS_DIR . 'includes/settings/class-responsive-add-ons-settings.php';
		$settings = new Responsive_Add_Ons_Settings();

		global $wcam_lib_responsive_addons;

		$api_key    = $settings->get( 'api', 'token' );
		$product_id = $settings->get( 'account', 'product_id' );

		if ( empty( $api_key ) || '' === $api_key || empty( $product_id ) || '' === $product_id ) {
			$err_msg = __( 'Connection details are missing. Please reconnect to Cyberchimps to continue.', 'responsive-addons' );
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
		$userEmail = isset( $_POST['user_email'] ) ? sanitize_key( wp_unslash( $_POST['user_email'] ) ) : '';


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

		if ( 403 === $response_code ) {
			wp_send_json_error(
				array(
					'message' => $response_body->message,
					'error'   => true,
					'error_code' => $response_code,
				),
			);
		}
		if ( 200 !== $response_code ) {
			$err_msg = sprintf(
				__( '%1$s Please reconnect to Cyberchimps to continue.', 'responsive-addons' ), $response_body->message );

			wp_send_json_error(
				array(
					'message' => $err_msg,
					'error'   => true,
					'error_code' => $response_code,
				),
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
}
