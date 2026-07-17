<?php
/**
 * Site Builder — read-only backward compatibility.
 *
 * When Responsive Pro is not active, the `resp-site-builder` post type
 * (registered by the Responsive theme) is registered with show_ui = false,
 * so previously-built templates become invisible in wp-admin even though
 * the posts/postmeta are untouched in the database.
 *
 * This class re-exposes those templates in a read-only admin list so users
 * can see their templates are intact, without granting real edit access.
 *
 * @package Responsive_Add_Ons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Responsive_Site_Builder_Legacy' ) ) {

	class Responsive_Site_Builder_Legacy {

		const POST_TYPE = 'resp-site-builder';
		const PRO_SLUG  = 'responsivepro/responsivepro.php';
		const UPGRADE_URL = 'https://cyberchimps.com/responsive-addons-pro/'; // adjust to your actual upsell URL.

		private static $instance;

		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			// Bail early if Pro is active — Pro owns full read/write access.
			if ( $this->is_pro_active() ) {
				return;
			}

			// Must run before the theme's `init` registration, so hook this early
			// (constructor is invoked during plugin bootstrap, before `init`).
			add_filter( 'register_post_type_args', array( $this, 'expose_post_type_read_only' ), 10, 2 );

			add_action( 'admin_notices', array( $this, 'render_upgrade_notice' ) );
			add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'add_columns' ) );
			add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'filter_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-' . self::POST_TYPE, array( $this, 'strip_bulk_actions' ) );
			add_action( 'load-post.php', array( $this, 'block_direct_edit_access' ) );

			// Keep the live site rendering the same header/footer/404/single/archive
			// layouts as before, read-only. Only makes sense on the Responsive theme,
			// since it relies on that theme's site-builder hooks.
			if ( ! is_admin() && $this->is_theme_site_builder_compatible() ) {
				$this->load_frontend_renderer();
			}
		}

		/**
		 * Mirrors Responsive Pro's theme-compatibility gate: Site Builder
		 * only ever ran on the Responsive theme, version > 6.2.1.
		 */
		private function is_theme_site_builder_compatible() {
			$theme = wp_get_theme();

			if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
				if ( 'Responsive' === $theme->parent_theme ) {
					$theme = wp_get_theme( 'responsive' );
				}
			} else {
				return false;
			}

			return version_compare( $theme['Version'], '6.2.1', '>' );
		}

		/**
		 * Load the read-only front-end renderer: display-rules matcher,
		 * page-builder content renderers, and the markup/hook layer that
		 * echoes previously-saved layouts into the theme's header/footer.
		 * None of this can save, publish, or modify a post.
		 */
		private function load_frontend_renderer() {
			require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/class-rasb-legacy-display-rules.php';
			require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/class-rasb-legacy-compatibility.php';
			require_once RESPONSIVE_ADDONS_DIR . 'admin/site-builder/class-rasb-legacy-markup.php';
		}

		private function is_pro_active() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			return is_plugin_active( self::PRO_SLUG );
		}

		/**
		 * Re-expose the post type in wp-admin, but with editing capabilities
		 * pointed at a capability nobody has, so WP core hides Edit/Add New/
		 * Quick Edit and blocks direct access on its own.
		 */
		public function expose_post_type_read_only( $args, $post_type ) {

			if ( self::POST_TYPE !== $post_type ) {
				return $args;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return $args;
			}

			$args['show_ui']      = true;
			$args['show_in_menu'] = 'responsive_add_ons'; // nests under the existing "Responsive" menu.
			$args['show_in_rest'] = false; // keep the default REST post controller off this type.

			if ( ! empty( $args['labels'] ) && is_array( $args['labels'] ) ) {
				$args['labels']['menu_name'] = __( 'Site Builder Templates', 'responsive-add-ons' );
				$args['labels']['name']      = __( 'Site Builder Templates', 'responsive-add-ons' );
			}

			// Disable meta cap mapping so the literal capability strings below
			// are checked as-is, regardless of post author/ownership.
			$args['map_meta_cap'] = false;
			$args['capabilities']  = array(
				'edit_post'          => 'do_not_allow',
				'edit_posts'         => 'do_not_allow',
				'edit_others_posts'  => 'do_not_allow',
				'publish_posts'      => 'do_not_allow',
				'create_posts'       => 'do_not_allow',
				'delete_post'        => 'do_not_allow',
				'delete_posts'       => 'do_not_allow',
				'delete_others_posts' => 'do_not_allow',
				'read_post'          => 'read',
				'read_private_posts' => 'read',
			);

			return $args;
		}

		/**
		 * Friendly notice on the legacy list screen pointing to the upgrade.
		 */
		public function render_upgrade_notice() {
			$screen = get_current_screen();
			if ( ! $screen || 'edit-' . self::POST_TYPE !== $screen->id ) {
				return;
			}
			?>
			<div class="notice notice-info">
				<p>
					<?php
					printf(
						/* translators: %s: upgrade URL */
						wp_kses_post( __( 'These templates were built with Site Builder and are shown here read-only. <a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to Responsive Pro</a> to edit them again.', 'responsive-add-ons' ) ),
						esc_url( self::UPGRADE_URL )
					);
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Add Type / Status columns so the list is actually useful without an editor.
		 */
		public function add_columns( $columns ) {
			$new = array();
			foreach ( $columns as $key => $label ) {
				$new[ $key ] = $label;
				if ( 'title' === $key ) {
					$new['sb_type']   = __( 'Type', 'responsive-add-ons' );
					$new['sb_status'] = __( 'Status', 'responsive-add-ons' );
				}
			}
			unset( $new['date'] );
			$new['date'] = $columns['date'] ?? __( 'Date', 'responsive-add-ons' );
			return $new;
		}

		public function render_column( $column, $post_id ) {
			if ( 'sb_type' === $column ) {
				$type = get_post_meta( $post_id, 'responsive-site-builder-layout', true );
				echo esc_html( $type ? ucwords( str_replace( '-', ' ', $type ) ) : '—' );
			}

			if ( 'sb_status' === $column ) {
				$status = get_post_meta( $post_id, 'responsive-site-builder-layout-status', true );
				$active = ( 'true' === $status || '1' === $status || true === $status );
				printf(
					'<span style="color:%s;font-weight:600;">%s</span>',
					$active ? '#2242A3' : '#999',
					$active ? esc_html__( 'Active', 'responsive-add-ons' ) : esc_html__( 'Inactive', 'responsive-add-ons' )
				);
			}
		}

		/**
		 * Row actions are already stripped of Edit/Quick Edit/Trash by the
		 * capability lock above; swap in an upgrade CTA and keep "View" if present.
		 */
		public function filter_row_actions( $actions, $post ) {
			if ( self::POST_TYPE !== $post->post_type ) {
				return $actions;
			}
			$actions = array(); // core already omits edit/inline/trash; clear any stragglers.
			$actions['upgrade'] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( self::UPGRADE_URL ),
				esc_html__( 'Unlock editing', 'responsive-add-ons' )
			);
			return $actions;
		}

		public function strip_bulk_actions( $actions ) {
			return array(); // nothing bulk-editable in read-only mode.
		}

		/**
		 * Belt-and-braces redirect if someone lands on post.php?action=edit directly.
		 * WP core already blocks the save/render with a capability check; this just
		 * makes the bounce friendlier and points at the upgrade page.
		 */
		public function block_direct_edit_access() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect, no state change.
			$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
			if ( ! $post_id || self::POST_TYPE !== get_post_type( $post_id ) ) {
				return;
			}
			if ( current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type' => self::POST_TYPE,
						'sb_locked' => '1',
					),
					admin_url( 'edit.php' )
				)
			);
			exit;
		}
	}
}

Responsive_Site_Builder_Legacy::get_instance();
