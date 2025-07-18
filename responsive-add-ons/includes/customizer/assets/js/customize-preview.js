/**
 * Customizer options
 *
 * @package Responsive WordPress theme
 */

( function ( $ ) {

	function responsive_dynamic_radius(control, selector) {
		var mobile_menu_breakpoint = api( 'responsive_mobile_menu_breakpoint' ).get();
		if (0 == api( 'responsive_disable_mobile_menu' ).get()) {
			mobile_menu_breakpoint = 0;
		}

		jQuery( 'style#responsive-' + control + '-radius' ).remove();
		var desktopRadius = 'border-top-left-radius:' + api( 'responsive_' + control + '_radius_top_left_radius' ).get() + 'px; ' +
							'border-top-right-radius:' + api( 'responsive_' + control + '_radius_top_right_radius' ).get() + 'px; ' +
							'border-bottom-left-radius:' + api( 'responsive_' + control + '_radius_bottom_left_radius' ).get() + 'px; ' +
							'border-bottom-right-radius:' + api( 'responsive_' + control + '_radius_bottom_right_radius' ).get() + 'px;';
		var tabletRadius  = 'border-top-left-radius:' + api( 'responsive_' + control + '_radius_tablet_top_left_radius' ).get() + 'px; ' +
							'border-top-right-radius:' + api( 'responsive_' + control + '_radius_tablet_top_right_radius' ).get() + 'px; ' +
							'border-bottom-left-radius:' + api( 'responsive_' + control + '_radius_tablet_bottom_left_radius' ).get() + 'px; ' +
							'border-bottom-right-radius:' + api( 'responsive_' + control + '_radius_tablet_bottom_right_radius' ).get() + 'px;';
		var mobileRadius  = 'border-top-left-radius:' + api( 'responsive_' + control + '_radius_mobile_top_left_radius' ).get() + 'px; ' +
							'border-top-right-radius:' + api( 'responsive_' + control + '_radius_mobile_top_right_radius' ).get() + 'px; ' +
							'border-bottom-left-radius:' + api( 'responsive_' + control + '_radius_mobile_bottom_left_radius' ).get() + 'px; ' +
							'border-bottom-right-radius:' + api( 'responsive_' + control + '_radius_mobile_bottom_right_radius' ).get() + 'px;';

		jQuery( 'head' ).append(
			'<style id="responsive-' + control + '-radius">' +
			selector + ' { ' + desktopRadius + ' }' +
			'@media (max-width: ' + mobile_menu_breakpoint + 'px) {' + selector + ' { ' + tabletRadius + ' } }' +
			'@media (max-width: 544px) {' + selector + ' { ' + mobileRadius + ' } }' +
			'</style>'
		);
	}
	// Declare vars.
	var api = wp.customize;
	api(
		"responsive_theme_options[sticky-header]",
		function ( $swipe ) {

			$swipe.bind(
				function ( pair ) {
					if ( pair === true ) {
						jQuery( window ).scroll(
							function () {
								if (jQuery( this ).scrollTop() > 0) {
									if ( pair === true ) {
										jQuery( '#masthead' ).addClass( "sticky-header" );
										var floatingBarCheck    = document.getElementById( 'floating-bar' );
										var heightOfHeaderTaken = jQuery( '#masthead' ).outerHeight();
										if ( floatingBarCheck && jQuery( window ).width() > 768) {
											jQuery( '.responsive-floating-bar' ).css( { top: heightOfHeaderTaken + 'px', bottom: 'auto' } );
										} else if ( jQuery( '#masthead' ).hasClass( 'sticky-header' ) && jQuery( window ).width() <= 768 ) {
											jQuery( '.responsive-floating-bar' ).css( { bottom: 0, top: 'auto' } );
										}
									}
								} else {
									jQuery( '#masthead' ).removeClass( "sticky-header" );
								}

							}
						);
					} else {
						jQuery( window ).scroll(
							function () {
								jQuery( '#masthead' ).removeClass( "sticky-header" );
								var floatingBarCheck = document.getElementById( 'floating-bar' );
								if ( floatingBarCheck && jQuery( window ).width() > 768 ) {
									jQuery( '.responsive-floating-bar' ).css( { top: 0, bottom: 'auto' } );
								} else if ( jQuery( window ).width() <= 768 ) {
									jQuery( '.responsive-floating-bar' ).css( { bottom: 0, top: 'auto' } );
								}
							}
						);
					}
				}
			);
		}
	);
	api(
		"responsive_shrink_sticky_header",
		function ( $swipe ) {

			$swipe.bind(
				function ( pair ) {
					if ( pair === true ) {
						jQuery( '#masthead' ).addClass( "shrink" );
					} else {
						jQuery( '#masthead' ).removeClass( "shrink" );
					}
				}
			)
		}
	);

	api(
		"responsive_disable_author_meta",
		function ( $swipe ) {

			$swipe.bind(
				function ( pair ) {
					if ( pair === true ) {
						jQuery( '#author-meta' ).css( "display", "none" );
					} else {
						jQuery( '#author-meta' ).css( "display", "block" );
					}
				}
			)
		}
	);

	// Site Colors
	// Box Background Color
	if ( responsive_pro.isProGreater && ! responsive_pro.isThemeGreater ) {
		api(
			'responsive_box_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( '.page.front-page.responsive-site-style-content-boxed .custom-home-widget-section.home-widgets,.blog.front-page.responsive-site-style-content-boxed .custom-home-widget-section.home-widgets,.responsive-site-style-content-boxed .custom-home-about-section,.responsive-site-style-content-boxed .custom-home-feature-section,.responsive-site-style-content-boxed .custom-home-team-section,.responsive-site-style-content-boxed .custom-home-testimonial-section,.responsive-site-style-content-boxed .custom-home-contact-section,.responsive-site-style-content-boxed .custom-home-widget-section,.responsive-site-style-content-boxed .custom-home-featured-area,.responsive-site-style-content-boxed .site-content-header,.responsive-site-style-content-boxed .content-area-wrapper,.responsive-site-style-content-boxed .site-content .hentry,.responsive-site-style-content-boxed .navigation,.responsive-site-style-content-boxed .comments-area,.responsive-site-style-content-boxed .comment-respond,.responsive-site-style-boxed .custom-home-about-section,.responsive-site-style-boxed .custom-home-feature-section,.responsive-site-style-boxed .custom-home-team-section,.responsive-site-style-boxed .custom-home-testimonial-section,.responsive-site-style-boxed .custom-home-contact-section,.responsive-site-style-boxed .custom-home-widget-section,.responsive-site-style-boxed .custom-home-featured-area,.responsive-site-style-boxed .site-content-header,.responsive-site-style-boxed .site-content .hentry,.responsive-site-style-boxed .navigation,.responsive-site-style-boxed .comments-area,.responsive-site-style-boxed .comment-respond,.responsive-site-style-boxed .comment-respond,.responsive-site-style-boxed .site-content article.product,.woocommerce.responsive-site-style-content-boxed .related-product-wrapper,.woocommerce-page.responsive-site-style-content-boxed .related-product-wrapper,.woocommerce-page.responsive-site-style-content-boxed .products-wrapper,.woocommerce.responsive-site-style-content-boxed .products-wrapper,.woocommerce-page:not(.responsive-site-style-flat) .woocommerce-pagination,.woocommerce-page.responsive-site-style-boxed ul.products li.product,.woocommerce.responsive-site-style-boxed ul.products li.product,.woocommerce-page.single-product:not(.responsive-site-style-flat) div.product,.woocommerce.single-product:not(.responsive-site-style-flat) div.product' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_box_background_color' ).get() + ',' + api( 'responsive_box_background_color' ).get() + '),url(' + newval + ')' );

						if ( ! api( 'responsive_sidebar_background_image' ).get() ) {
								$( '.responsive-site-style-boxed aside#secondary .widget-wrapper' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_box_background_color' ).get() + ',' + api( 'responsive_box_background_color' ).get() + '),url(' + newval + ')' );
						}
					}
				);
			}
		);
	} else {
		api(
			'responsive_box_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( '.page.front-page.responsive-site-style-content-boxed .custom-home-widget-section.home-widgets,.blog.front-page.responsive-site-style-content-boxed .custom-home-widget-section.home-widgets,.responsive-site-style-content-boxed .custom-home-about-section,.responsive-site-style-content-boxed .custom-home-feature-section,.responsive-site-style-content-boxed .custom-home-team-section,.responsive-site-style-content-boxed .custom-home-testimonial-section,.responsive-site-style-content-boxed .custom-home-contact-section,.responsive-site-style-content-boxed .custom-home-widget-section,.responsive-site-style-content-boxed .custom-home-featured-area,.responsive-site-style-content-boxed .site-content-header,.responsive-site-style-content-boxed .content-area-wrapper,.responsive-site-style-content-boxed .site-content .hentry,.responsive-site-style-content-boxed .navigation,.responsive-site-style-content-boxed .comments-area,.responsive-site-style-content-boxed .comment-respond,.responsive-site-style-boxed .custom-home-about-section,.responsive-site-style-boxed .custom-home-feature-section,.responsive-site-style-boxed .custom-home-team-section,.responsive-site-style-boxed .custom-home-testimonial-section,.responsive-site-style-boxed .custom-home-contact-section,.responsive-site-style-boxed .custom-home-widget-section,.responsive-site-style-boxed .custom-home-featured-area,.responsive-site-style-boxed .site-content-header,.responsive-site-style-boxed .site-content .hentry,.responsive-site-style-boxed .navigation,.responsive-site-style-boxed .comments-area,.responsive-site-style-boxed .comment-respond,.responsive-site-style-boxed .comment-respond' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_box_background_color' ).get() + ',' + api( 'responsive_box_background_color' ).get() + '),url(' + newval + ')' );

						if ( ! api( 'responsive_sidebar_background_image' ).get() ) {
								$( '.responsive-site-style-boxed aside#secondary .widget-wrapper' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_box_background_color' ).get() + ',' + api( 'responsive_box_background_color' ).get() + '),url(' + newval + ')' );
						}
					}
				);
			}
		);
	}
	if ( responsive_pro.isProGreater && ! responsive_pro.isThemeGreater ) {
		api(
			'responsive_box_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_box_background_image' ).get() ) {
								$( '.page.front-page.responsive-site-style-content-boxed .custom-home-widget-section.home-widgets,.blog.front-page.responsive-site-style-content-boxed .custom-home-widget-section.home-widgets,.responsive-site-style-content-boxed .custom-home-about-section,.responsive-site-style-content-boxed .custom-home-feature-section,.responsive-site-style-content-boxed .custom-home-team-section,.responsive-site-style-content-boxed .custom-home-testimonial-section,.responsive-site-style-content-boxed .custom-home-contact-section,.responsive-site-style-content-boxed .custom-home-widget-section,.responsive-site-style-content-boxed .custom-home-featured-area,.responsive-site-style-content-boxed .site-content-header,.responsive-site-style-content-boxed .content-area-wrapper,.responsive-site-style-content-boxed .site-content .hentry,.responsive-site-style-content-boxed .navigation,.responsive-site-style-content-boxed .comments-area,.responsive-site-style-content-boxed .comment-respond,.responsive-site-style-boxed .custom-home-about-section,.responsive-site-style-boxed .custom-home-feature-section,.responsive-site-style-boxed .custom-home-team-section,.responsive-site-style-boxed .custom-home-testimonial-section,.responsive-site-style-boxed .custom-home-contact-section,.responsive-site-style-boxed .custom-home-widget-section,.responsive-site-style-boxed .custom-home-featured-area,.responsive-site-style-boxed .site-content-header,.responsive-site-style-boxed .site-content .hentry,.responsive-site-style-boxed .navigation,.responsive-site-style-boxed .comments-area,.responsive-site-style-boxed .comment-respond,.responsive-site-style-boxed .comment-respond' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_box_background_image' ).get() + ')' );
							if ( ! api( 'responsive_sidebar_background_image' ).get() && ! ! api( 'responsive_sidebar_background_color' ).get() ) {
								$( '.responsive-site-style-boxed aside#secondary .widget-wrapper' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_box_background_image' ).get() + ')' );
							}
						}

					}
				);
			}
		);
	} else {
		api(
			'responsive_box_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_box_background_image' ).get() ) {
								$( '.responsive-site-style-boxed .site-content article.product,.woocommerce.responsive-site-style-content-boxed .related-product-wrapper,.woocommerce-page.responsive-site-style-content-boxed .related-product-wrapper,.woocommerce-page.responsive-site-style-content-boxed .products-wrapper,.woocommerce.responsive-site-style-content-boxed .products-wrapper,.woocommerce-page:not(.responsive-site-style-flat) .woocommerce-pagination,.woocommerce-page.responsive-site-style-boxed ul.products li.product,.woocommerce.responsive-site-style-boxed ul.products li.product,.woocommerce-page.single-product:not(.responsive-site-style-flat) div.product,.woocommerce.single-product:not(.responsive-site-style-flat) div.product' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_box_background_image' ).get() + ')' );
							if ( ! api( 'responsive_sidebar_background_image' ).get() && ! ! api( 'responsive_sidebar_background_color' ).get() ) {
								$( '.responsive-site-style-boxed aside#secondary .widget-wrapper' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_box_background_image' ).get() + ')' );
							}
						}

					}
				);
			}
		);
	}

	if ( responsive_pro.isProGreater && ! responsive_pro.isThemeGreater ) {
		api(
			'responsive_button_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( '.page.front-page .button,.blog.front-page .button,.read-more-button .hentry .read-more .more-link,input[type=button],input[type=submit],button,.button,.wp-block-button__link,div.wpforms-container-full .wpforms-form input[type=submit],body div.wpforms-container-full .wpforms-form button[type=submit],div.wpforms-container-full .wpforms-form .wpforms-page-button ' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_button_color' ).get() + ',' + api( 'responsive_button_color' ).get() + '),url(' + newval + ')' );
					}
				);
			}
		);

		api(
			'responsive_button_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_button_background_image' ).get() ) {
								$( '.page.front-page .button,.blog.front-page .button,.read-more-button .hentry .read-more .more-link,input[type=button],input[type=submit],button,.button,.wp-block-button__link,div.wpforms-container-full .wpforms-form input[type=submit],body div.wpforms-container-full .wpforms-form button[type=submit],div.wpforms-container-full .wpforms-form .wpforms-page-button ' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_button_background_image' ).get() + ')' );
						}
					}
				);
			}
		);
	}
	if ( responsive_pro.isProGreater && ! responsive_pro.isThemeGreater ) {
		// Inputs color
		api(
			'responsive_inputs_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( 'select,textarea,input[type=tel],input[type=email],input[type=number],input[type=search],input[type=text],input[type=date],input[type=datetime],input[type=datetime-local],input[type=month],input[type=password],input[type=range],input[type=time],input[type=url],input[type=week],div.wpforms-container-full .wpforms-form input[type=date],div.wpforms-container-full .wpforms-form input[type=datetime],div.wpforms-container-full .wpforms-form input[type=datetime-local],body div.wpforms-container-full .wpforms-form input[type=email],div.wpforms-container-full .wpforms-form input[type=month],div.wpforms-container-full .wpforms-form input[type=number],div.wpforms-container-full .wpforms-form input[type=password],div.wpforms-container-full .wpforms-form input[type=range],div.wpforms-container-full .wpforms-form input[type=search],div.wpforms-container-full .wpforms-form input[type=tel],div.wpforms-container-full .wpforms-form input[type=text],div.wpforms-container-full .wpforms-form input[type=time],div.wpforms-container-full .wpforms-form input[type=url],div.wpforms-container-full .wpforms-form input[type=week],div.wpforms-container-full .wpforms-form select,div.wpforms-container-full .wpforms-form textarea,#add_payment_method table.cart td.actions .coupon .input-text,.woocommerce-cart table.cart td.actions .coupon .input-text,.woocommerce-checkout table.cart td.actions .coupon .input-text,.woocommerce form .form-row input.input-text,.woocommerce form .form-row textarea' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_inputs_background_color' ).get() + ',' + api( 'responsive_inputs_background_color' ).get() + '),url(' + newval + ')' );
					}
				);
			}
		);
	} else {
		api(
			'responsive_inputs_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( '#add_payment_method table.cart td.actions .coupon .input-text,.woocommerce-cart table.cart td.actions .coupon .input-text,.woocommerce-checkout table.cart td.actions .coupon .input-text,.woocommerce form .form-row input.input-text,.woocommerce form .form-row textarea' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_inputs_background_color' ).get() + ',' + api( 'responsive_inputs_background_color' ).get() + '),url(' + newval + ')' );
					}
				);
			}
		);
	}
	if ( responsive_pro.isProGreater && ! responsive_pro.isThemeGreater ) {
		// Inputs color
		api(
			'responsive_inputs_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_inputs_background_image' ).get() ) {
								$( 'select,textarea,input[type=tel],input[type=email],input[type=number],input[type=search],input[type=text],input[type=date],input[type=datetime],input[type=datetime-local],input[type=month],input[type=password],input[type=range],input[type=time],input[type=url],input[type=week],div.wpforms-container-full .wpforms-form input[type=date],div.wpforms-container-full .wpforms-form input[type=datetime],div.wpforms-container-full .wpforms-form input[type=datetime-local],body div.wpforms-container-full .wpforms-form input[type=email],div.wpforms-container-full .wpforms-form input[type=month],div.wpforms-container-full .wpforms-form input[type=number],div.wpforms-container-full .wpforms-form input[type=password],div.wpforms-container-full .wpforms-form input[type=range],div.wpforms-container-full .wpforms-form input[type=search],div.wpforms-container-full .wpforms-form input[type=tel],div.wpforms-container-full .wpforms-form input[type=text],div.wpforms-container-full .wpforms-form input[type=time],div.wpforms-container-full .wpforms-form input[type=url],div.wpforms-container-full .wpforms-form input[type=week],div.wpforms-container-full .wpforms-form select,div.wpforms-container-full .wpforms-form textarea,#add_payment_method table.cart td.actions .coupon .input-text,.woocommerce-cart table.cart td.actions .coupon .input-text,.woocommerce-checkout table.cart td.actions .coupon .input-text,.woocommerce form .form-row input.input-text,.woocommerce form .form-row textarea' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_inputs_background_image' ).get() + ')' );
						}
					}
				);
			}
		);
	} else {
		// Inputs color
		api(
			'responsive_inputs_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_inputs_background_image' ).get() ) {
								$( '#add_payment_method table.cart td.actions .coupon .input-text,.woocommerce-cart table.cart td.actions .coupon .input-text,.woocommerce-checkout table.cart td.actions .coupon .input-text,.woocommerce form .form-row input.input-text,.woocommerce form .form-row textarea' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_inputs_background_image' ).get() + ')' );
						}
					}
				);
			}
		);
	}

	if ( responsive_pro.isProGreater && ! responsive_pro.isThemeGreater ) {
		// sidebar color
		api(
			'responsive_sidebar_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( '.responsive-site-style-boxed aside#secondary .widget-wrapper' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_sidebar_background_color' ).get() + ',' + api( 'responsive_sidebar_background_color' ).get() + '),url(' + newval + ')' );
					}
				);
			}
		);
		// sidebar color
		api(
			'responsive_sidebar_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_sidebar_background_image' ).get() ) {
								$( '.responsive-site-style-boxed aside#secondary .widget-wrapper' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_sidebar_background_image' ).get() + ')' );
						}
					}
				);
			}
		);

		// header color
		api(
			'responsive_header_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( 'body:not(.res-transparent-header) .site-header' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_header_background_color' ).get() + ',' + api( 'responsive_header_background_color' ).get() + '),url(' + newval + ')' );
					}
				);
			}
		);
		// header color
		api(
			'responsive_header_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_header_background_image' ).get() ) {
								$( 'body:not(.res-transparent-header) .site-header' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_header_background_image' ).get() + ')' );
						}
					}
				);
			}
		);

		// footer color
		api(
			'responsive_footer_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( 'body:not(.res-transparent-footer) .site-footer' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_footer_background_color' ).get() + ',' + api( 'responsive_footer_background_color' ).get() + '),url(' + newval + ')' );
					}
				);
			}
		);
		// footer color
		api(
			'responsive_footer_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_footer_background_image' ).get() ) {
								$( 'body:not(.res-transparent-footer) .site-footer' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_footer_background_image' ).get() + ')' );
						}
					}
				);
			}
		);
		// header_widget color
		api(
			'responsive_header_widget_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( 'body:not(.res-transparent-header) .header-widgets' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_header_widget_background_color' ).get() + ',' + api( 'responsive_header_widget_background_color' ).get() + '),url(' + newval + ')' );
					}
				);
			}
		);
		// header_widget color
		api(
			'responsive_header_widget_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_header_widget_background_image' ).get() ) {
								$( 'body:not(.res-transparent-header) .header-widgets' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_header_widget_background_image' ).get() + ')' );
						}
					}
				);
			}
		);
		// transparent_header_widget color
		api(
			'responsive_transparent_header_widget_background_image',
			function ( value ) {
				value.bind(
					function ( newval ) {
						$( '.res-transparent-header .header-widgets' ).css( 'background-image', 'linear-gradient(to right,' + api( 'responsive_transparent_header_widget_background_color' ).get() + ',' + api( 'responsive_transparent_header_widget_background_color' ).get() + '),url(' + newval + ')' );
					}
				);
			}
		);
		// transparent_header_widget color
		api(
			'responsive_transparent_header_widget_background_color',
			function ( value ) {
				value.bind(
					function ( newval ) {
						if ( api( 'responsive_transparent_header_widget_background_image' ).get() ) {
								$( '.res-transparent-header .header-widgets' ).css( 'background-image', 'linear-gradient(to right,' + newval + ',' + newval + '),url(' + api( 'responsive_transparent_header_widget_background_image' ).get() + ')' );
						}
					}
				);
			}
		);
	}

	// Native POPUP
	api( 'responsive_native_cart_popup_display', function( value ) {
		value.bind( function( newval ) {
			if ( newval === true || newval === '1' || newval === 1 ) {
				// Show popup container if hidden
				$( '#woo-popup-wrap' ).css( { display: 'block' } );

				// Open magnific popup modal
				$.magnificPopup.open({
					items: {
						src: '#woo-popup-wrap',
					},
					modal: true,
					closeOnBgClick: true,
				});
			} else {
				// Close popup if open
				$.magnificPopup.close();
			}
		});
	});


	// Title text
	api(
		'responsive_popup_title_text',
		function ( value ) {
			value.bind(
				function ( newval ) {
					$( '#woo-popup-wrap .popup-title' ).html( newval );
				}
			);
		}
	);
	// Content
	api(
		'responsive_popup_content',
		function ( value ) {
			value.bind(
				function ( newval ) {
					$( '#woo-popup-wrap .popup-content' ).html( newval );
				}
			);
		}
	);

	// Continue button text
	api(
		'responsive_popup_continue_btn_text',
		function ( value ) {
			value.bind(
				function ( newval ) {
					$( '#woo-popup-wrap .buttons-wrap a.continue-btn' ).html( newval );
				}
			);
		}
	);

	// Cart button text
	api(
		'responsive_popup_cart_btn_text',
		function ( value ) {
			value.bind(
				function ( newval ) {
					$( '#woo-popup-wrap .buttons-wrap a.cart-btn' ).html( newval );
				}
			);
		}
	);

	// Bottom text
	api(
		'responsive_popup_bottom_text',
		function ( value ) {
			value.bind(
				function ( newval ) {
					$( '#woo-popup-wrap .popup-text' ).html( newval );
				}
			);
		}
	);

	// Popup width
	api(
		'responsive_popup_width',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_width' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_width">#woo-popup-wrap #woo-popup-inner { width: ' + to + 'px; }</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup width tablet
	api(
		'responsive_popup_width_tablet',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_width_tablet' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_width_tablet">@media (max-width: 786px){#woo-popup-wrap #woo-popup-inner{width: ' + to + 'px; }}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup width mobile
	api(
		'responsive_popup_width_mobile',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_width_mobilee' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_width_mobile">@media (max-width: 480px){#woo-popup-wrap #woo-popup-inner{width: ' + to + 'px; }}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup height
	api(
		'responsive_popup_height',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_height' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_height">#woo-popup-wrap #woo-popup-inner { height: ' + to + 'px; }</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup height tablet
	api(
		'responsive_popup_height_tablet',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_height_tablet' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_height_tablet">@media (max-height: 786px){#woo-popup-wrap #woo-popup-inner{height: ' + to + 'px; }}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup height mobile
	api(
		'responsive_popup_height_mobile',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_height_mobile' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_height_mobile">@media (max-height: 480px){#woo-popup-wrap #woo-popup-inner{height: ' + to + 'px; }}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup Radius
	function responsive_popup_radius(control, to, selector) {
		var $child = $( '.customizer-' + control );
		if ( to ) {
			var style = '<style class="customizer-' + control + '">#woo-popup-wrap #woo-popup-inner{' + selector + ': ' + to + 'px; }</style>';
			if ( $child.length ) {
				$child.replaceWith( style );
			} else {
				$( 'head' ).append( style );
			}
		} else {
			$child.remove();
		}
	}
	var topRight    = 'border-top-right-radius';
	var bottomRight = 'border-bottom-right-radius';
	var bottomLeft  = 'border-bottom-left-radius';
	var topLeft     = 'border-top-left-radius';
	api(
		'responsive_popup_radius_top_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_top_padding', to, topRight );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_right_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_right_padding', to, bottomRight );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_bottom_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_bottom_padding', to, bottomLeft );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_left_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_left_padding', to, topLeft );
				}
			);
		}
	);

	api(
		'responsive_popup_radius_tablet_top_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_tablet_top_padding', to, topRight );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_tablet_right_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_tablet_right_padding', to, bottomRight );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_tablet_bottom_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_tablet_bottom_padding', to, bottomLeft );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_tablet_left_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_tablet_left_padding', to, topLeft );
				}
			);
		}
	);

	api(
		'responsive_popup_radius_mobile_top_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_mobile_top_padding', to, topRight );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_mobile_right_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_mobile_right_padding', to, bottomRight );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_mobile_bottom_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_mobile_bottom_padding', to, bottomLeft );
				}
			);
		}
	);
	api(
		'responsive_popup_radius_mobile_left_padding',
		function ( value ) {
			value.bind(
				function ( to ) {
					responsive_popup_radius( 'responsive_popup_radius_mobile_left_padding', to, topLeft );
				}
			);
		}
	);

	// Popup background color
	api(
		'responsive_popup_bg_color',
		function ( value ) {
			value.bind(
				function ( newval ) {
					if ( newval ) {
							$( '#woo-popup-wrap #woo-popup-inner' ).css( 'background-color', newval );
					}
				}
			);
		}
	);
	// Popup overlay color
	api(
		'responsive_popup_overlay_color',
		function ( value ) {
			value.bind(
				function ( newval ) {
					if ( newval ) {
							$( '.mfp-container' ).css( 'background', newval );
					}
				}
			);
		}
	);

	// Popup check mark background
	api(
		'responsive_popup_checkmark_bg_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_checkmark_bg_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_checkmark_bg_color">#woo-popup-wrap .checkmark{box-shadow: inset 0 0 0 ' + to + '; }#woo-popup-wrap .checkmark-circle{stroke: ' + to + ';}@keyframes fill {100% { box-shadow: inset 0 0 0 100px ' + to + '; }}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup check mark color
	api(
		'responsive_popup_checkmark_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_checkmark_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_checkmark_color">#woo-popup-wrap .checkmark-check{stroke: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup title color
	api(
		'responsive_popup_title_color',
		function ( value ) {
			value.bind(
				function ( newval ) {
					if ( newval ) {
							$( '#woo-popup-wrap .popup-title' ).css( 'color', newval );
					}
				}
			);
		}
	);

	// Popup content color
	api(
		'responsive_popup_content_color',
		function ( value ) {
			value.bind(
				function ( newval ) {
					if ( newval ) {
							$( '#woo-popup-wrap .popup-content' ).css( 'color', newval );
					}
				}
			);
		}
	);

	// Popup continue button background color
	api(
		'responsive_popup_continue_btn_bg_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_continue_btn_bg_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_continue_btn_bg_color">#woo-popup-wrap .buttons-wrap a.continue-btn{background-color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup continue button color
	api(
		'responsive_popup_continue_btn_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_continue_btn_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_continue_btn_color">#woo-popup-wrap .buttons-wrap a.continue-btn{color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup continue button border color
	api(
		'responsive_popup_continue_btn_border_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_continue_btn_border_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_continue_btn_border_color">#woo-popup-wrap .buttons-wrap a.continue-btn{border-color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup continue button hover background color
	api(
		'responsive_popup_continue_btn_hover_bg_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_continue_btn_hover_bg_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_continue_btn_hover_bg_color">#woo-popup-wrap .buttons-wrap a.continue-btn:hover{background-color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup continue button hover color
	api(
		'responsive_popup_continue_btn_hover_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_continue_btn_hover_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_continue_btn_hover_color">#woo-popup-wrap .buttons-wrap a.continue-btn:hover{color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup continue button hover border color
	api(
		'responsive_popup_continue_btn_hover_border_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_continue_btn_hover_border_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_continue_btn_hover_border_color">#woo-popup-wrap .buttons-wrap a.continue-btn:hover{border-color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup cart button background color
	api(
		'responsive_popup_cart_btn_bg_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_cart_btn_bg_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_cart_btn_bg_color">#woo-popup-wrap .buttons-wrap a.cart-btn{background-color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup cart button color
	api(
		'responsive_popup_cart_btn_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.responsive_popup_cart_btn_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_cart_btn_color">#woo-popup-wrap .buttons-wrap a.cart-btn{color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup cart button border color
	api(
		'responsive_popup_cart_btn_border_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_cart_btn_border_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_cart_btn_border_color">#woo-popup-wrap .buttons-wrap a.cart-btn{border-color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup cart button hover background color
	api(
		'responsive_popup_cart_btn_hover_bg_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_cart_btn_hover_bg_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_cart_btn_hover_bg_color">#woo-popup-wrap .buttons-wrap a.cart-btn:hover{background-color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup cart button hover color
	api(
		'responsive_popup_cart_btn_hover_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_cart_btn_hover_color' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_cart_btn_hover_color">#woo-popup-wrap .buttons-wrap a.cart-btn:hover{color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup cart button hover border color
	api(
		'responsive_popup_cart_btn_hover_border_color',
		function ( value ) {
			value.bind(
				function ( to ) {
					var $child = $( '.customizer-responsive_popup_cart_btn_hover_border_colorr' );
					if ( to ) {
							var style = '<style class="customizer-responsive_popup_cart_btn_hover_border_color">#woo-popup-wrap .buttons-wrap a.cart-btn:hover{border-color: ' + to + ';}</style>';
						if ( $child.length ) {
							$child.replaceWith( style );
						} else {
							$( 'head' ).append( style );
						}
					} else {
						$child.remove();
					}
				}
			);
		}
	);

	// Popup bottom text color
	api(
		'responsive_popup_text_color',
		function ( value ) {
			value.bind(
				function ( newval ) {
					if ( newval ) {
							$( '#woo-popup-wrap .popup-text' ).css( 'color', newval );
					}
				}
			);
		}
	);
	api(
		'responsive_cart_icon_size',
		function ( value ) {
			value.bind(
				function ( newval ) {
					if ( newval ) {
							$( '.responsive-shopping-cart-svg svg' ).css( 'height', newval + 'px' );
							$( '.responsive-shopping-cart-svg svg' ).css( 'width', newval + 'px' );
							$( '.responsive-shopping-cart-svg' ).css( 'height', newval + 'px' );
							$( '.responsive-shopping-cart-svg' ).css( 'width', newval + 'px' );
					}
				}
			);
		}
	);
	api(
		'responsive_cart_color',
		function (setting) {
			setting.bind(
				function (color) {
					var cartIconStyle = api( 'responsive_cart_style' ).get();
					if (cartIconStyle === 'outline') {
						$( '.res-addon-cart-wrap' ).css( 'border', api( 'responsive_cart_border_width' ).get() + 'px solid ' + color );
						$( '.res-addon-cart-wrap' ).css( 'color', color );
						$( '.res-addon-cart-wrap svg path' ).css( 'fill', color );
					} else if (cartIconStyle === 'fill') {
						$( '.res-addon-cart-wrap' ).css( 'background-color', color );
					}
				}
			);
		}
	);
	api(
		'responsive_cart_hover_color',
		function (setting) {
			setting.bind(
				function (color) {
					jQuery( 'style#responsive-cart-hover-color' ).remove();
					var cartIconStyle = api( 'responsive_cart_style' ).get();
					if (cartIconStyle === 'outline') {
						jQuery( 'head' ).append(
							'<style id="responsive-cart-hover-color">'
							+ '.res-addon-cart-wrap:hover { border: ' + api( 'responsive_cart_border_width' ).get() + 'px solid ' + color + '!important; }'
							+ '.res-addon-cart-wrap:hover { color: ' + color + '!important; }'
							+ '.res-addon-cart-wrap:hover svg path { fill: ' + color + '!important; }'
							+ '</style>'
						);
					} else if (cartIconStyle === 'fill') {
						jQuery( 'head' ).append(
							'<style id="responsive-cart-hover-color">'
							+ '.res-addon-cart-wrap:hover { background-color: ' + color + '!important; }'
							+ '</style>'
						);
					}
				}
			);
		}
	);
	api(
		'responsive_cart_border_width',
		function (setting) {
			setting.bind(
				function (width) {
					jQuery( 'style#responsive-cart-border-width' ).remove();
					jQuery( 'head' ).append(
						'<style id="responsive-cart-border-width">'
						+ '.res-addon-cart-wrap, .res-addon-cart-wrap:hover { border-width: ' + width + 'px !important; }'
						+ '</style>'
					);
				}
			);
		}
	);

	api('box_shadow_options', function(value) {
		value.bind(function(newval) {
			// Define shadow levels (1–5)
			const shadowPresets = {
				1: '0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.1)',
				2: '0 3px 6px rgba(0, 0, 0, 0.15), 0 3px 6px rgba(0, 0, 0, 0.1)',
				3: '0 10px 20px rgba(0, 0, 0, 0.15), 0 6px 6px rgba(0, 0, 0, 0.1)',
				4: '0 14px 28px rgba(0, 0, 0, 0.16), 0 10px 10px rgba(0, 0, 0, 0.12)',
				5: '0 20px 30px rgba(0, 0, 0, 0.2), 0 15px 12px rgba(0, 0, 0, 0.15)'
			};
	
			const boxShadow = shadowPresets[newval] || 'none';
	
			// Apply shadow on desktop screens
			function applyShadowIfDesktop(x) {
				if (x.matches) {
					jQuery('.woocommerce ul.products .product.type-product').css('box-shadow', boxShadow);
				}
			}
	
			const mq = window.matchMedia("(min-width: 992px)");
			applyShadowIfDesktop(mq);
			mq.addEventListener('change', applyShadowIfDesktop);
		});
	});
	
	// Hover box shadow
	api('box_shadow_hover_options', function(value) {
		value.bind(function(newval) {
			var shadowPresets = {
				1: '0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.1)',
				2: '0 3px 6px rgba(0, 0, 0, 0.15), 0 3px 6px rgba(0, 0, 0, 0.1)',
				3: '0 10px 20px rgba(0, 0, 0, 0.15), 0 6px 6px rgba(0, 0, 0, 0.1)',
				4: '0 14px 28px rgba(0, 0, 0, 0.16), 0 10px 10px rgba(0, 0, 0, 0.12)',
				5: '0 20px 30px rgba(0, 0, 0, 0.2), 0 15px 12px rgba(0, 0, 0, 0.15)'
			};

			var hoverShadow = shadowPresets[newval] || 'none';
			
			var styleTag = document.getElementById('dynamic-hover-shadow-style');
			if (!styleTag) {
				styleTag = document.createElement('style');
				styleTag.id = 'dynamic-hover-shadow-style';
				document.head.appendChild(styleTag);
			}

			styleTag.textContent = [
				'@media (min-width: 992px) {',
				'  .woocommerce ul.products .product.type-product:hover {',
				'    box-shadow: ' + hoverShadow + ' !important;',
				'  }',
				'}'
			].join('\n');
		});
	});


	// header woo cart border radius.
	function applyCartRadius(controlName) {
		api(
			controlName,
			function (value) {
				value.bind(
					function ( newval ) {
						var selector = '.res-addon-cart-wrap';
						responsive_dynamic_radius( 'cart', selector );
					}
				);
			}
		);
	}

	// Call the function for each cart border radius
	applyCartRadius( 'responsive_cart_radius_top_left_radius' );
	applyCartRadius( 'responsive_cart_radius_top_right_radius' );
	applyCartRadius( 'responsive_cart_radius_bottom_left_radius' );
	applyCartRadius( 'responsive_cart_radius_bottom_right_radius' );

	applyCartRadius( 'responsive_cart_radius_tablet_top_left_radius' );
	applyCartRadius( 'responsive_cart_radius_tablet_top_right_radius' );
	applyCartRadius( 'responsive_cart_radius_tablet_bottom_left_radius' );
	applyCartRadius( 'responsive_cart_radius_tablet_bottom_right_radius' );

	applyCartRadius( 'responsive_cart_radius_mobile_top_left_radius' );
	applyCartRadius( 'responsive_cart_radius_mobile_top_right_radius' );
	applyCartRadius( 'responsive_cart_radius_mobile_bottom_left_radius' );
	applyCartRadius( 'responsive_cart_radius_mobile_bottom_right_radius' );

} )( jQuery );
