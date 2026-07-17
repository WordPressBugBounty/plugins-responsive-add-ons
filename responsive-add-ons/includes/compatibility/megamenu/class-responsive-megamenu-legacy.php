<?php
/**
 * Mega Menu — read-only backward compatibility.
 *
 * When ResponsiveX Pro is not active, menu items that already have
 * `_menu_item_megamenu_resp_*` postmeta saved on them (from a previous
 * Pro session) would otherwise fall back to a plain WordPress menu and
 * lose all of their configured layout/columns/icons/highlights.
 *
 * This class keeps those previously-configured menus rendering exactly
 * as before on the front end, using the same nav walker markup and the
 * same front-end-only CSS/JS. It intentionally does NOT port:
 *  - the "Mega Menu Settings" admin UI / modal,
 *  - the nav-menus.php edit walker,
 *  - the REST routes that read/write menu-item megamenu options,
 *  - the admin bundle (mega-menu-admin.js).
 * So nothing new can be configured or changed without Pro — this only
 * keeps already-saved settings visible on the live site.
 *
 * The enable flag itself is only trusted if it's backed by a signed
 * `_menu_item_megamenu_resp_legacy_marker` value that Pro writes at save
 * time (see class-rao-legacy-megamenu-nav-walker.php::verify_legacy_marker()).
 * This prevents someone from unlocking Mega Menu for free by simply
 * adding the plain `enable_megamenu` custom field by hand, without ever
 * having a genuine Pro-saved configuration.
 *
 * @package Responsive_Add_Ons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Responsive_MegaMenu_Legacy' ) ) {

	class Responsive_MegaMenu_Legacy {

		const PRO_SLUG     = 'responsivepro/responsivepro.php';
		const META_PREFIX  = '_menu_item_megamenu_resp_';
		const UPGRADE_URL  = 'https://cyberchimps.com/responsive-addons-pro/'; // adjust to your actual upsell URL.

		private static $instance;

		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			// Bail early if Pro is active — Pro owns the full configuration UI.
			if ( $this->is_pro_active() ) {
				return;
			}

			// Nothing to do if no menu item was ever configured with megamenu settings.
			if ( ! $this->has_saved_megamenu_meta() ) {
				return;
			}

			add_action( 'admin_notices', array( $this, 'render_upgrade_notice' ) );

			// Read-only front-end rendering only. No admin editing hooks, no REST routes.
			if ( ! is_admin() ) {
				$this->load_frontend_renderer();
			}
		}

		/**
		 * Load the read-only nav walker + its front-end assets. Nothing here
		 * can save or modify menu-item postmeta.
		 */
		private function load_frontend_renderer() {
			require_once RESPONSIVE_ADDONS_DIR . 'includes/compatibility/megamenu/class-responsive-legacy-megamenu-nav-walker.php';

			add_filter( 'responsive_nav_menu_arg', array( $this, 'use_legacy_megamenu_walker' ), 11, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_theme_script' ), 999 );
		}

		/**
		 * Swap in the ported walker so previously-built mega menus keep
		 * rendering with their saved layout, same as when Pro was active.
		 */
		public function use_legacy_megamenu_walker( $args ) {
			$args['walker'] = new Responsive_Legacy_MegaMenu_Nav_Walker();
			return $args;
		}

		/**
		 * Enqueue only the front-end display assets (menu interaction JS +
		 * megamenu CSS). The admin editor bundle is never loaded here.
		 */
		public function enqueue_frontend_assets() {
			$base_url = RESPONSIVE_ADDONS_DIR_URL . 'includes/compatibility/megamenu/assets/';

			wp_enqueue_script( 'responsive-legacy-navigation-pro', $base_url . 'navigation-pro.js', array( 'jquery' ), RESPONSIVE_ADDONS_VER, true );
			$mobile_menu_breakpoint = array( 'mobileBreakpoint' => get_theme_mod( 'responsive_mobile_menu_breakpoint', 767 ) );
			wp_localize_script( 'responsive-legacy-navigation-pro', 'responsive_breakpoint', $mobile_menu_breakpoint );

			wp_enqueue_script( 'responsive-legacy-mega-menu', $base_url . 'mega-menu.js', array( 'jquery' ), RESPONSIVE_ADDONS_VER, true );

			wp_enqueue_style( 'responsive-legacy-mega-menu-frontend', $base_url . 'megamenu-frontend.css', array(), RESPONSIVE_ADDONS_VER );
			wp_enqueue_style( 'responsive-legacy-mega-menu', $base_url . 'megamenu.css', array(), RESPONSIVE_ADDONS_VER );
		}

		/**
		 * The theme's own nav script conflicts with the megamenu interaction
		 * script, same as Pro dequeues it.
		 */
		public function dequeue_theme_script() {
			wp_dequeue_script( 'navigation-scripts' );
		}

		/**
		 * Checks whether any menu item still has a genuine, Pro-signed
		 * megamenu marker saved, so this whole compatibility layer is a
		 * no-op both on sites that never used the feature AND on sites
		 * where someone merely added the plain `enable_megamenu` custom
		 * field by hand without ever having a real Pro-saved config.
		 */
		private function has_saved_megamenu_meta() {
			global $wpdb;
			$meta_key = self::META_PREFIX . 'legacy_marker';
			$result   = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = %s", $meta_key ) );
			return (bool) $result;
		}

		private function is_pro_active() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			return is_plugin_active( self::PRO_SLUG );
		}

		/**
		 * Friendly notice pointing to the upgrade, shown on any wp-admin
		 * screen since (unlike Site Builder) there's no dedicated list
		 * screen for menu items.
		 */
		public function render_upgrade_notice() {
			$screen = get_current_screen();
			if ( ! $screen || 'nav-menus' !== $screen->id ) {
				return;
			}
			?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
						/* translators: %s: upgrade URL */
						wp_kses_post( __( 'One or more of your menus use Mega Menu settings. They keep displaying on your site as configured, but editing them requires ResponsiveX Pro. <a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to ResponsiveX Pro</a> to manage them again.', 'responsive-add-ons' ) ),
						esc_url( self::UPGRADE_URL )
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}

Responsive_MegaMenu_Legacy::get_instance();
