<?php
/**
 * Outputs the customizer styles.
 *
 * @package Responsive Addons Pro Plugin Woocommerce
 * @since 0.2
 */

if ( ! function_exists( 'check_is_pro_version_greater' ) ) {
	/**
	 * Verify if the version of responsive pro is greater or not.
	 *
	 * @since 2.6.4
	 */
	function check_is_pro_version_greater() {
		$is_pro_version_greater = false;
		if ( class_exists( 'Responsive_Addons_Pro' ) ) {
			if ( version_compare( RESPONSIVE_ADDONS_PRO_VERSION, '2.6.3', '>' ) ) {
				$is_pro_version_greater = true;
			}
		}
		return $is_pro_version_greater;
	}
}

if ( ! function_exists( 'is_responsive_version_greater' ) ) {
	/**
	 * Verify if the version of responsive theme is greater or not.
	 *
	 * @since 2.6.4
	 */
	function is_responsive_version_greater() {
		$theme = wp_get_theme();
		if ( 'Responsive' === $theme->name || 'Responsive' === $theme->parent_theme ) {
			if ( 'Responsive' === $theme->parent_theme ) {
				$theme = wp_get_theme( 'responsive' );
			}
		}
		$is_theme_version_greater = false;
		if ( version_compare( $theme['Version'], '4.9.6', '>' ) ) {
			$is_theme_version_greater = true;
		}
		return $is_theme_version_greater;
	}
}

/* To convert font size units */
if ( ! function_exists( 'responsive_typography_unit_conversion' ) ) {
	/**
	 * Converts a given font size from various CSS units (px, rem, em, %) to a pixel value.
	 *
	 * @param string    $font_size        The font size value including unit (e.g., '16px', '1.2em', '100%').
	 * @param int|float $parent_font_size Optional. The parent font size in pixels, used for relative units. Default 0.
	 * @param int|float $root_font_size   Optional. The root font size in pixels, used for rem conversion. Default 0.
	 *
	 * @return float|int The font size converted to pixels.
	 */
	function responsive_typography_unit_conversion( $font_size, $parent_font_size = 0, $root_font_size = 0 ) {
		if ( false !== strpos( $font_size, 'px' ) ) {
			$font_size = str_replace( 'px', '', $font_size );
		} elseif ( false !== strpos( $font_size, 'rem' ) ) {
			$font_size = str_replace( 'rem', '', $font_size );
			$font_size = $font_size * $root_font_size;

		} elseif ( false !== strpos( $font_size, 'em' ) ) {
			$font_size = str_replace( 'em', '', $font_size );
			$font_size = $font_size * $parent_font_size;

		} elseif ( false !== strpos( $font_size, '%' ) ) {
			$font_size = str_replace( '%', '', $font_size );
			$font_size = ( $font_size * $parent_font_size ) / 100;
		}

		return $font_size;
	}
}


if ( ! function_exists( 'responsive_addons_custom_theme_styles' ) ) {
	/**
	 * Outputs the custom styles for the woocommerce plugin.
	 *
	 * @return void
	 */
	function responsive_addons_custom_theme_styles() {
		$custom_css = "";
		$custom_css .= '@media (min-width: ' . get_theme_mod( 'responive_mobile_breakpoint', 992 ) . 'px) {';

		for ( $i = 10; $i <= 100; $i++ ) {
			$custom_css .= '.main-navigation li.megamenu-parent ul.megamenu.tab_width-' . $i . ' > li { width: ' . $i . '%; }';
		}

		$custom_css .= '.main-navigation li.megamenu-parent .children,
			//.main-navigation li.megamenu-parent .sub-menu {
			// height: 500px;
				//padding: 50px 10px;
			//}
			.main-navigation li.megamenu-parent .children > li,
			.main-navigation li.megamenu-parent .sub-menu > li {
				border-top: none;
			}
			.main-navigation li.megamenu-parent .children .children,
			.main-navigation li.megamenu-parent .children .sub-menu,
			.main-navigation li.megamenu-parent .sub-menu .children,
			.main-navigation li.megamenu-parent .sub-menu .sub-menu {
				border: none;
				border-right: 1px solid rgba(170, 170, 170, 0.2);
				border-left: 1px solid rgba(170, 170, 170, 0.2);
				box-shadow: none;
			}
		}';

		$layout = responsive_addons_get_content_layout();
		if( 'site-builder' === $layout ) {
			$custom_css .= "
				.responsive-site-builder-layout .site-content > .container {
					max-width: 100%;
					padding: 0;
				}
			";
		}

		$custom_css .= apply_filters( 'responsive_site_builder_sticky_header_css', $custom_css );
		$custom_css .= apply_filters( 'responsive_site_builder_sticky_footer_css', $custom_css );

		wp_add_inline_style( 'responsive-pro-style', apply_filters( 'responsive_add_ons_dynamic_css', $custom_css ) );
	}
}
add_action( 'wp_enqueue_scripts', 'responsive_addons_custom_theme_styles', 99 );
