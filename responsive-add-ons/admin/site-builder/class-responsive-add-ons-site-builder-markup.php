<?php
/**
 * Site Builder Markup
 * 
 * @package Responsive_Add_Ons
 */

if( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Responsive_Add_Ons_Site_Builder_Markup' ) ) {

    class Responsive_Add_Ons_Site_Builder_Markup {

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

		private $add_site_builder_body_class = false;
		private $add_footer_preview_active_class = false;

        public function __construct()
        {
            add_action( 'wp', array( $this, 'responsive_addons_load_markup' ), 1 );
			add_filter( 'body_class', array( $this, 'responsive_addons_custom_body_classes' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
			add_filter( 'responsive_addons_js_localize', array( $this, 'localize_variables' ) );
            add_action( 'wp', array( $this, 'responsive_addons_load_site_builder_templates' ), 1 );
			add_filter( 'single_template', array( $this, 'get_custom_post_type_template' ) );
			add_action( 'responsive_site_builder_template', array( $this, 'template_empty_content' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_responsive_block_editor_addons_blocks_assets' ) );

			add_action( 'template_include', array( $this, 'override_template_include' ), 999 );
        }

		public function enqueue_frontend_scripts() {
			wp_enqueue_script( 'responsive-site-builder-sticky', RESPONSIVE_ADDONS_SITE_BUILDER_URI . 'assets/js/sticky-layout.js', array(), RESPONSIVE_ADDONS_VER, true );
			wp_localize_script( 'responsive-site-builder-sticky', 'resposiveAddonsSB', apply_filters( 'responsive_addons_js_localize', array() ) );
		}

        public function responsive_addons_load_markup() {

            $option = array(
				'location'  => 'responsive-site-builder-layout-location',
				'exclusion' => 'responsive-site-builder-layout-exclusion',
				'users'     => 'responsive-site-builder-layout-users',
				'enabled'   => 'responsive-site-builder-layout-status',
			);

			$result             = Responsive_Add_Ons_Site_Builder_Display_Rules::get_instance()->get_posts_by_conditions( RESPONSIVE_BUILDER_POST_TYPE, $option );
			$header_counter     = 0;
			$footer_counter     = 0;
			$layout_404_counter = 0;

			foreach ( $result as $post_id => $post_data ) {
				$post_type = get_post_type();

				if ( RESPONSIVE_BUILDER_POST_TYPE !== $post_type ) {

					$layout = get_post_meta( $post_id, 'responsive-site-builder-layout', false );

					if ( isset( $layout[0] ) && 'header' === $layout[0] && 0 == $header_counter ) {

						$hide_classes = explode( ' ', $this->get_display_device( $post_id, false ) );

						if ( ! static::get_time_duration_eligibility( $post_id ) ) {
							continue;
						}

						// If SB Layout is displayed on desktop, tablet & mobile then do not render theme's layout.
						if ( 3 === count( $hide_classes ) ) {
							$layout_status = get_post_meta( $post_id, 'responsive-site-builder-layout-status', true );
							if ( 'disabled' !== $layout_status ) {
								remove_action( 'responsive_header', 'header_markup' );
							}
						}

						// Add hide classes on theme's header.
						add_filter(
							'responsive_header_class',
							static function( $classes ) use ( $hide_classes ) {
								foreach( $hide_classes as $hide_class ) {
									$classes[] = $hide_class;
								}
								return $classes;
						});

						$meta           = get_post_meta( $post_id, 'responsive-site-builder-layout-header', true );
						$sticky_enabled = isset( $meta['sticky'] ) && 'enabled' === $meta['sticky'];
						$device_type    = $meta['sticky-header-on-devices'] ?? 'desktop';

						$sticky_class = '';
						if ( $sticky_enabled ) {
							if ( 'both' === $device_type ) {
								$sticky_class = 'sticky-header sticky-header-both';
							} elseif ( 'desktop' === $device_type ) {
								$sticky_class = 'sticky-header sticky-header-desktop';
							} elseif ( 'mobile' === $device_type ) {
								$sticky_class = 'sticky-header sticky-header-mobile';
							}
						}

						// Add body class for sticky header based on device type.
						add_filter( 'body_class', static function( $classes ) use ( $sticky_enabled, $device_type ) {

							if( ! $sticky_enabled ) return $classes;

							if ( 'both' === $device_type ) {
								$classes[] = 'responsive-sb-sticky-header-both';
							} elseif ( 'desktop' === $device_type ) {
								$classes[] = 'responsive-sb-sticky-header-desktop';
							} elseif ( 'mobile' === $device_type ) {
								$classes[] = 'responsive-sb-sticky-header-mobile';
							}
							return $classes;
						}, 20 );

						if( $sticky_enabled ) {
							add_filter( 'responsive_site_builder_header_sticky_device', fn() => $device_type );
						}

						$action = 'responsive_site_builder_header';
						if ( ! has_action( 'responsive_site_builder_header' ) ) {
							$action = 'responsive_header';
						}
						add_action(
							$action,
							static function() use ( $post_id, $sticky_class ) {
								echo '<header class="responsive-site-builder-header ' . esc_attr( $sticky_class ) . '" itemscope="itemscope" itemtype="https://schema.org/WPHeader">';
									Responsive_Add_Ons_Site_Builder_Markup::get_instance()->get_action_content( $post_id );
								echo '</header>';
							},
							10
						);

						$header_counter++;
					} elseif ( isset( $layout[0] ) && 'footer' === $layout[0] && 0 == $footer_counter ) {

						$hide_classes = explode( ' ', $this->get_display_device( $post_id, false ) );

						if ( ! static::get_time_duration_eligibility( $post_id ) ) {
							continue;
						}

						// If SB Layout is displayed on desktop, tablet & mobile then do not render theme's layout.
						if ( 3 === count( $hide_classes ) ) {
							$layout_status = get_post_meta( $post_id, 'responsive-site-builder-layout-status', true );
							if ( 'disabled' !== $layout_status ) {
								// remove_action( 'responsive_footer', 'footer_markup' );
								remove_action( 'responsive_footer', array( Responsive_Builder_Footer::get_instance(), 'footer_markup' ) );
							}
						}

						$meta           = get_post_meta( $post_id, 'responsive-site-builder-layout-footer', true );
						$sticky_enabled = isset( $meta['sticky'] ) && 'enabled' === $meta['sticky'];
						$device_type    = $meta['sticky-footer-on-devices'] ?? 'desktop';

						$sticky_class = '';
						if ( $sticky_enabled ) {
							if ( 'both' === $device_type ) {
								$sticky_class = 'sticky-footer sticky-footer-both';
							} elseif ( 'desktop' === $device_type ) {
								$sticky_class = 'sticky-footer-desktop';
							} elseif ( 'mobile' === $device_type ) {
								$sticky_class = 'sticky-footer-mobile';
							}
						}

						// Add body class for sticky footer based on device type.
						add_filter( 'body_class', static function( $classes ) use ( $sticky_enabled, $device_type ) {

							if( ! $sticky_enabled ) return $classes;

							if ( 'both' === $device_type ) {
								$classes[] = 'responsive-sb-sticky-footer-both';
							} elseif ( 'desktop' === $device_type ) {
								$classes[] = 'responsive-sb-sticky-footer-desktop';
							} elseif ( 'mobile' === $device_type ) {
								$classes[] = 'responsive-sb-sticky-footer-mobile';
							}
							return $classes;
						}, 20 );

						if( $sticky_enabled ) {
							add_filter( 'responsive_site_builder_footer_sticky_device', fn() => $device_type );
						}

						// Add hide classes on theme's footer.
						add_filter(
							'responsive_footer_class',
							static function( $classes ) use ( $hide_classes ) {
								foreach( $hide_classes as $hide_class ) {
									$classes[] = $hide_class;
								}
								return $classes;
						});

						$action = 'responsive_site_builder_footer';
						// if responsive_site_builder_footer not exist then call responsive_footer_after.
						if ( ! has_action( 'responsive_site_builder_footer' ) ) {
							$action = 'responsive_footer_after';
						}
						// Add Action for site builder footer.
						add_action(
							$action,
							static function() use ( $post_id, $sticky_class ) {
								echo '<footer class="responsive-site-builder-footer '. esc_attr( $sticky_class ) .' " itemscope="itemscope" itemtype="https://schema.org/WPFooter">';
									Responsive_Add_Ons_Site_Builder_Markup::get_instance()->get_action_content( $post_id );
								echo '</footer>';
							},
							10
						);
						$footer_counter++;
					} elseif ( isset( $layout[0] ) && '404-page' === $layout[0] && 0 === $layout_404_counter ) {

						if ( ! static::get_time_duration_eligibility( $post_id ) ) {
							continue;
						}

						$hide_classes = explode( ' ', $this->get_display_device( $post_id, false ) );

						// If SB Layout is displayed on desktop, tablet & mobile then do not render theme's layout.
						if ( 3 === count( $hide_classes ) ) {
							$layout_status = get_post_meta( $post_id, 'responsive-site-builder-layout-status', true );
							if ( 'disabled' !== $layout_status ) {
								remove_action( 'resposive_entry_content_404_page', 'resposive_entry_content_404_page_template' );
							}
						}

						// Add hide classes on theme's 404 page.
						add_filter(
							'responsive_404_class',
							static function( $classes ) use ( $hide_classes ) {
								foreach( $hide_classes as $hide_class ) {
									$classes[] = $hide_class;
								}
								return $classes;
						});

						$this->add_site_builder_body_class = true;
						add_action( 'responsive_addons_get_content_layout', 'responsive_addons_return_content_layout_site_builder' );

						$layout_404_settings = get_post_meta( $post_id, 'responsive-site-builder-layout-404', true );

						if ( isset( $layout_404_settings['disable_header'] ) && 'enabled' === $layout_404_settings['disable_header'] ) {
							remove_action( 'responsive_header', 'header_markup' );
						}

						if ( isset( $layout_404_settings['disable_footer'] ) && 'enabled' === $layout_404_settings['disable_footer'] ) {
							remove_action( 'responsive_footer', array( Responsive_Builder_Footer::get_instance(), 'footer_markup' ) );
						}

						add_action(
							'resposive_entry_content_404_page',
							static function() use ( $post_id ) {
								Responsive_Add_Ons_Site_Builder_Markup::get_instance()->get_action_content( $post_id );
							},
							10
						);

						$layout_404_counter++;
					}
                }
            }
        }

        /**
		 * Site Builder Layout get content
		 *
		 * Loads content
		 *
		 * @since 3.3.0
		 * @param int $post_id post id.
		 */
		public function get_action_content( $post_id ) {

			$enabled = get_post_meta( $post_id, 'responsive-site-builder-layout-status', true );
			if ( false === apply_filters( 'responsive_addons_render_site_builder_layout_content', true, $post_id ) || 'disabled' === $enabled ) {
				return;
			}
			if ( ! static::get_time_duration_eligibility( $post_id ) ) {
				return;
			}

			$display_device_classes = $this->get_display_device( $post_id );
            ?>
                <div class="responsive-site-builder-layout-<?php echo esc_attr( $post_id ); ?> <?php echo esc_attr( $display_device_classes ); ?>">
            <?php

            if ( class_exists( 'Responsive_Add_Ons_Site_Builder_compatibility' ) ) {

                $site_builder_compatibility_base_instance = Responsive_Add_Ons_Site_Builder_compatibility::get_instance();

                $site_builder_builder_instance = $site_builder_compatibility_base_instance->get_active_page_builder( $post_id );

                $site_builder_builder_instance->render_content( $post_id );

            }
            ?>
                </div>
            <?php
        }

        /**
		 * Prepare a class to hide custom layout as per selected device.
		 *
		 * @param int  $post_id post id.
		 * @param bool $hide_classes get the hide/show classes.
		 * @return string
		 */
		public function get_display_device( $post_id, $hide_classes = true ) {
			// bail early if the custom layout or hook is disabled.
			if ( 'disabled' === get_post_meta( $post_id, 'responsive-site-builder-layout-status', true ) ) {
				return '';
			}

			$classes        = '';
			$display_device = get_post_meta( $post_id, 'responsive-site-builder-layout-display-device', true );
			$devices        = array( 'desktop', 'tablet', 'mobile' );

			if ( '' === $display_device ) {
				$display_device = $devices; // Managing backward compatibility.
			}

			if ( ! is_array( $display_device ) ) {
				return $classes;
			}

			if ( $hide_classes ) {
				$devices        = array( 'desktop', 'tablet', 'mobile' );
				$display_device = array_diff( $devices, $display_device );
			}

			if ( ! empty( $display_device ) ) {
				$classes = implode( ' ', preg_filter( '/^/', 'responsive-hide-display-device-', $display_device ) );
			}
			return $classes;
		}

		/**
		 * Check if post eligible to show on time duration
		 *
		 *  @param int $post_id post id.
		 */
		public static function get_time_duration_eligibility( $post_id ) {

			$time_duration = get_post_meta( $post_id, 'responsive-site-builder-layout-time-duration', 'true' );

			if ( isset( $time_duration['enabled'] ) && 'enabled' !== $time_duration['enabled'] ) {
				return true; // Eligible to display as not enabled time duration.
			}

			$start_dt   = isset( $time_duration['start-dt'] ) ? strtotime( $time_duration['start-dt'] ) : false;
			$end_dt     = isset( $time_duration['end-dt'] ) ? strtotime( $time_duration['end-dt'] ) : false;
			$current_dt = strtotime( current_time( 'mysql' ) );

			if ( $start_dt && $start_dt > $current_dt ) {
				return false; // Not eligible if not started yet.
			}

			if ( $end_dt && $end_dt < $current_dt ) {
				return false; // Not eligible if already time passed.
			}

			return true; // Fallback true.
		}

		public function responsive_addons_custom_body_classes( $classes ) {
			if ( $this->add_site_builder_body_class ) {
				$classes[] = 'responsive-site-builder-layout';
			}
			if( $this->add_footer_preview_active_class ) {
				$classes[] = 'responsive-site-builder-footer-layout-preview';
			}

			return apply_filters( 'responsive_addons_site_builder_body_classes', $classes );
		}

		/**
		 * Add Localize variables
		 *
		 * @param  array $localize_vars Localize variables array.
		 * @return array
		 */
		public function localize_variables( $localize_vars ) {

			$option = array(
				'location'  => 'responsive-site-builder-layout-location',
				'exclusion' => 'responsive-site-builder-layout-exclusion',
				'users'     => 'responsive-site-builder-layout-users',
			);

			$result         = Responsive_Add_Ons_Site_Builder_Display_Rules::get_instance()->get_posts_by_conditions( RESPONSIVE_BUILDER_POST_TYPE, $option );
			$header_counter = 0;
			$footer_counter = 0;

			foreach ( $result as $post_id => $post_data ) {
				$post_type = get_post_type();

				if ( RESPONSIVE_BUILDER_POST_TYPE !== $post_type ) {
					$header = get_post_meta( $post_id, 'responsive-site-builder-layout-header', true );
					$footer = get_post_meta( $post_id, 'responsive-site-builder-layout-footer', true );
					$layout = get_post_meta( $post_id, 'responsive-site-builder-layout', false );

					if ( 0 == $header_counter && isset( $layout[0] ) && 'header' === $layout[0] ) {
						$localize_vars['sticky_header']            = isset( $header['sticky'] ) ? $header['sticky'] : '';
						$localize_vars['sticky_header_on_devices'] = isset( $header['sticky-header-on-devices'] ) ? $header['sticky-header-on-devices'] : '';

						$header_counter++;
					}

					if ( 0 == $footer_counter && isset( $layout[0] ) && 'footer' === $layout[0] ) {
						$localize_vars['sticky_footer']             = isset( $footer['sticky'] ) ? $footer['sticky'] : '';
						$localize_vars['sticky_footer_on_devices']  = isset( $footer['sticky-footer-on-devices'] ) ? $footer['sticky-footer-on-devices'] : '';

						$footer_counter++;
					}
				}
			}

			return $localize_vars;
		}

		/**
		 * Load Site Builder Layouts preview markup.
		 *
		 * @return void
		 */
		public function responsive_addons_load_site_builder_templates() {

			if ( is_singular( RESPONSIVE_BUILDER_POST_TYPE ) ) {
				$post_id  = get_the_id();
				$layout   = get_post_meta( $post_id, 'responsive-site-builder-layout', true );

				if ( 'header' === $layout ) {

					remove_action( 'responsive_header', 'header_markup' );

					$action = 'responsive_site_builder_header';
					if ( ! has_action( 'responsive_site_builder_header' ) ) {
						$action = 'responsive_header';
					}
					add_action(
						$action,
						static function() {
							echo '<header class="responsive-site-builder-header" itemscope="itemscope" itemtype="https://schema.org/WPHeader">';
								Responsive_Add_Ons_Site_Builder_Markup::get_instance()->get_the_hook_content();
							echo '</header>';
						},
						10
					);
					remove_action( 'responsive_footer', array( Responsive_Builder_Footer::get_instance(), 'footer_markup' ) );
				} elseif ( 'footer' === $layout ) {

					remove_action( 'responsive_footer', array( Responsive_Builder_Footer::get_instance(), 'footer_markup' ) );

					$action = 'responsive_site_builder_footer';
					// if responsive_site_builder_footer not exist then call responsive_footer_after.
					if ( ! has_action( 'responsive_site_builder_footer' ) ) {
						$action = 'responsive_footer_after';
					}
					// Add Action for site builder footer.
					add_action(
						$action,
						static function() {
							echo '<footer class="responsive-site-builder-footer" itemscope="itemscope" itemtype="https://schema.org/WPFooter">';
								Responsive_Add_Ons_Site_Builder_Markup::get_instance()->get_the_hook_content();
							echo '</footer>';
						},
						10
					);
					remove_action( 'responsive_header', 'header_markup' );
				}
			}
		}

		/**
		 * Get the content of the hook
		 */
		public function get_the_hook_content() {
			while ( have_posts() ) {
				the_post();
				the_content();
			}
		}

		/**
		 * Custom template for Site Builder post type.
		 *
		 * @param  string $template Single Post template path.
		 * @return string
		 */
		public function get_custom_post_type_template( $template ) {
			global $post;

			$post_id = get_the_id();
			$layout  = get_post_meta( $post_id, 'responsive-site-builder-layout', true );

			if ( RESPONSIVE_BUILDER_POST_TYPE === $post->post_type ) {
				'footer' === $layout ? $this->add_footer_preview_active_class = true : $this->add_footer_preview_active_class = false;
				if ( 'header' === $layout || 'footer' === $layout ) {
					$template = RESPONSIVE_ADDONS_SITE_BUILDER_DIR . 'template/template.php';
				}
				if ( '404-page' === $layout ) {
					$classes = array(
						'responsive-hide-display-device-desktop',
						'responsive-hide-display-device-tablet',
						'responsive-hide-display-device-mobile',
					);

					$append_classes = static function( $apply_classes ) use ( $classes ) {
						return array_merge( $apply_classes, $classes );
					};

					foreach ( array( 'responsive_header_class', 'responsive_footer_class' ) as $filter ) {
						add_filter( $filter, $append_classes );
					}

					$template = RESPONSIVE_ADDONS_SITE_BUILDER_DIR . 'template/404-template.php';
				}
			}

			return $template;
		}

		/**
		 * Empty Content area for Site Builder Layouts.
		 *
		 * @return void
		 */
		public function template_empty_content() {
			$post_id = get_the_id();
			$layout  = get_post_meta( $post_id, 'responsive-site-builder-layout', true );
			if ( empty( $layout ) ) {
				the_content();
			}
		}

		/**
		 * Load all Guttenberg compatibility styles & scripts.
		 *
		 * @since 3.3.0
		 */
		public function load_responsive_block_editor_addons_blocks_assets() {

			$option = array(
				'location'  => 'responsive-site-builder-layout-location',
				'exclusion' => 'responsive-site-builder-layout-exclusion',
				'users'     => 'responsive-site-builder-layout-users',
			);

			$result         = Responsive_Add_Ons_Site_Builder_Display_Rules::get_instance()->get_posts_by_conditions( RESPONSIVE_BUILDER_POST_TYPE, $option );

			foreach ( $result as $post_id => $post_data ) {

				if ( class_exists( 'Responsive_Add_Ons_SB_Gutenberg_Compatibility' ) ) {

					$responsive_addons_sb_gutenberg_instance = new Responsive_Add_Ons_SB_Gutenberg_Compatibility();

					if ( is_callable( array( $responsive_addons_sb_gutenberg_instance, 'enqueue_blocks_assets' ) ) ) {
						$responsive_addons_sb_gutenberg_instance->enqueue_blocks_assets( $post_id );
					}
				}
			}
		}

		/**
		 * Overriding default template.
		 *
		 * @since 3.3.2
		 * @param mixed $template template.
		 * @return string
		 */
		public function override_template_include( $template ) {

			$post_type = get_post_type();

			if ( RESPONSIVE_BUILDER_POST_TYPE !== $post_type ) {
				$option = array(
					'location'  => 'responsive-site-builder-layout-location',
					'exclusion' => 'responsive-site-builder-layout-exclusion',
					'users'     => 'responsive-site-builder-layout-users',
				);

				$posts   = Responsive_Add_Ons_Site_Builder_Display_Rules::get_instance()->get_posts_by_conditions( RESPONSIVE_BUILDER_POST_TYPE, $option );
				$posts   = array_keys( $posts );
				$layouts = array();

				foreach( $posts as $post_id ) {
					$layout = get_post_meta( $post_id, 'responsive-site-builder-layout', true );
					if ( ! empty( $layout ) && in_array( $layout, array( 'single', 'archive' ), true ) ) {
						$layouts[] = $post_id;
					}
				}

				if ( empty( $layouts ) ) {
					return $template;
				}

				if ( is_singular() ) {
					$page_template = get_page_template_slug();
					if( $page_template ) {
						return $template;
					}
				}

				$post_id = isset( $layouts[0] ) ? $layouts[0] : 0;
				if( ! $post_id ) {
					return $template;
				}
				$layout     = get_post_meta( $post_id, 'responsive-site-builder-layout', true );
				$is_enabled = get_post_meta( $post_id, 'responsive-site-builder-layout-status', true );

				if ( false === apply_filters( 'responsive_addons_render_single_archive_content', true, $post_id ) || 'disabled' === $is_enabled || ! in_array( $layout, array( 'single', 'archive' ) ) ) {
					return $template;
				}

				$args = array(
					'layout_id' => $post_id,
				);

				include RESPONSIVE_ADDONS_SITE_BUILDER_DIR . 'template/dynamic-content.php';
				return;
			}

			return $template;
		}

		public function render_overridden_template( $post_id ) {

			$post_content = get_post( $post_id );
			if ( empty( $post_content ) ) {
				return;
			}

			get_header();
			?>
			<div id="wrapper" class="site-content clearfix">
				<div class="content-outer container">
			<?php
			ob_start();
			self::get_instance()->get_action_content( $post_id );
			echo do_shortcode( ob_get_clean() );
			?>
				</div><!-- .content-outer -->
			</div><!-- #wrapper -->
			<?php
			get_footer();
		}
    }
}

Responsive_Add_Ons_Site_Builder_Markup::get_instance();