<?php
/**
 * WooCommerce - Customizer
 *
 * @package Responsive Addons
 * @since 1.1.0
 */

if ( ! class_exists( 'Responsive_Addons_Woocommerce_Ext' ) ) {
	/**
	 * Responsive_Customizer_WooCommerce_Add_Ons initial setup.
	 *
	 * @since 1.1.0
	 */
	class Responsive_Addons_Woocommerce_Ext {
		/**
		 * Member Varible
		 *
		 * @var object instance
		 */
		private static $instance;
		/**
		 *  Initiator
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
			if ( class_exists( 'WooCommerce' ) ) {

				add_action( 'woocommerce_shop_loop', array( $this, 'init_quick_view' ), 999 );
				add_action( 'responsive_shop_pagination_infinite', array( $this, 'responsive_shop_pagination' ), 1 );

				add_action( 'wp', array( $this, 'common_actions' ), 999 );
				add_action( 'responsive_shop_pagination_infinite', array( $this, 'common_actions' ), 999 );
				// quick view ajax.
				add_action( 'wp_ajax_responsive_load_product_quick_view', array( $this, 'responsive_load_product_quick_view_ajax' ) );
				add_action( 'wp_ajax_nopriv_responsive_load_product_quick_view', array( $this, 'responsive_load_product_quick_view_ajax' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
				add_action( 'responsive_theme_js_localize', array( $this, 'qv_js_localize' ) );
				add_action( 'responsive_pagination_infinite_enqueue_script', array( $this, 'responsive_shop_js_localize' ) );
				add_action( 'wp_ajax_responsive_shop_pagination_infinite', array( $this, 'responsive_shop_pagination_infinite' ) );
				add_action( 'wp_ajax_nopriv_responsive_shop_pagination_infinite', array( $this, 'responsive_shop_pagination_infinite' ) );

				// Woo Native Cart Popup.
				add_action( 'wp_footer', array( $this, 'popup_template' ) );
				// Ensure cart contents update when products are added to the cart via AJAX.
				add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'woocommerce_header_add_to_cart_fragment' ) );
				// Custom Template Quick View.
				$this->quick_view_content_actions();
			}
		}

		/**
		 * For adding the Infinite scroll functionality in the theme.
		 */
		public function responsive_shop_pagination_infinite() {

			check_ajax_referer( 'responsive-shop-load-more-nonce', 'nonce' );

			do_action( 'responsive_shop_pagination_infinite' );

			$query_vars = array(); // Default value.
			if ( isset( $_POST['query_vars'] ) ) {
				// Pre-sanitizing the response using a custom function to ensure all values are cleaned.
				// PHPCS incorrectly flags this as unsanitized, so the warning is suppressed.
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$query_vars = json_decode( wp_unslash( $_POST['query_vars'] ), true );
				if ( is_array( $query_vars ) ) {
					$query_vars = map_deep( $query_vars, 'sanitize_text_field' );
				} else {
					$query_vars = array(); // Reset to an empty array if decoding fails.
				}
			}
			$query_vars['paged']       = isset( $_POST['page_no'] ) ? absint( $_POST['page_no'] ) : 1;
			$query_vars['post_status'] = 'publish';
			$query_vars                = array_merge( $query_vars, wc()->query->get_catalog_ordering_args() );

			$posts = new WP_Query( $query_vars );

			if ( $posts->have_posts() ) {
				while ( $posts->have_posts() ) {
					$posts->the_post();

					/**
					 * Woocommerce: woocommerce_shop_loop hook.
					 *
					 * @hooked WC_Structured_Data::generate_product_data() - 10
					 */

					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}
			}

			wp_reset_query();

			wp_die();
		}

		/**
		 * Common Actions.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function common_actions() {
			// Quick View.
			$this->init_quick_view();
			$this->shop_pagination();
		}
		/**
		 * Initializes the Quick View functionality for WooCommerce shop listings.
		 *
		 * Adds actions for loading scripts, CSS, buttons, and modal templates based on the
		 * selected Quick View display option.
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function init_quick_view() {
			$qv_enable = get_theme_mod( 'shop_pagination_quick_view' );
			if ( 'disabled' !== $qv_enable ) {
				do_action( 'responsive_theme_js_localize' );
				do_action( 'responsive_get_css_files' );
				// add button.
				if ( 'after-summary' === $qv_enable ) {
					add_action( 'responsive_woo_shop_summary_wrap_bottom', array( $this, 'add_quick_view_button' ) );
				} elseif ( 'on-image' === $qv_enable ) {
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_quick_view_on_img' ), 7 );
				} elseif ( 'on-image-click' === $qv_enable ) {
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_quick_view_on_img_click' ), 7 );
				}
				// load modal template.
				add_action( 'wp_footer', array( $this, 'quick_view_html' ) );

			}
		}

		/**
		 * Enqueues and localizes Quick View JavaScript variables.
		 *
		 * @since 1.0
		 * @return void
		 */
		function qv_js_localize() {
			global $wp_query;

			$suffix    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$directory = 'assets/js/';

			wp_enqueue_script(
				'responsive-shop-quick-view',
				plugin_dir_url( __FILE__ ) . $directory . 'quick-view' . $suffix . '.js',
				array( 'jquery', 'wp-util' ),
				'3.17.2',
				true
			);
			wp_enqueue_script(
				'images-loaded',
				plugin_dir_url( __FILE__ ) . $directory . 'imagesloaded.pkgd' . $suffix . '.js',
				array(),
				'3.17.2',
				true
			);

			$localize = array(
				'query_vars'                  => wp_json_encode( $wp_query->query_vars ),
				'edit_post_url'               => admin_url( 'post.php?post={{id}}&action=edit' ),
				'ajax_url'                    => admin_url( 'admin-ajax.php' ),
				'shop_quick_view_enable'      => get_theme_mod( 'shop_pagination_quick_view' ),
				'shop_quick_view_auto_height' => true,
				'is_cart'                     => is_cart(),
				'is_single_product'           => is_product(),
				'view_cart'                   => esc_attr__( 'View cart', 'responsive-addons-pro' ),
				'nonce'                       => wp_create_nonce( 'responsive_quick_view_nonce' ),
			);

			wp_localize_script( 'responsive-shop-quick-view', 'responsiveShopQuickView', $localize );
		}

		/**
		 * Quick view ajax
		 */
		function responsive_load_product_quick_view_ajax() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'responsive_quick_view_nonce' ) ) {
				wp_send_json_error( 'Invalid nonce', 403 );
			}
			if ( ! isset( $_REQUEST['product_id'] ) ) {
				die();
			}
			
			$product_id = intval( $_REQUEST['product_id'] );
			
			// set the main wp query for the product.
			wp( 'p=' . $product_id . '&post_type=product' );
			
			// remove product thumbnails gallery.
			remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			ob_start();

			// load content template.
			load_template( __DIR__ . '/template-parts/quick-view-product.php' );
			echo ob_get_clean(); 
			die();
		}

		/**
		 * Quick view actions
		 */
		public function quick_view_content_actions() {
			// Image.
			add_action( 'responsive_woo_qv_product_image', 'woocommerce_show_product_sale_flash', 10 );
			add_action( 'responsive_woo_qv_product_image', array( $this, 'qv_product_images_markup' ), 20 );

			// Summary.
			add_action( 'responsive_woo_quick_view_product_summary', array( $this, 'single_product_content_structure' ), 10, 1 );
		}

		/**
		 * Outputs the single product content structure as per configured element order.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function single_product_content_structure() {
		
			$single_product_structure = Responsive\WooCommerce\responsive_woocommerce_product_elements_positioning();
		
			if ( ! is_array( $single_product_structure ) ) {
				return;
			}
		
			if ( empty( $single_product_structure ) ) {
				return;
			}
		
			foreach ( $single_product_structure as $value ) {
				
				if ($value == 'add_cart'){
					continue;
				}
				switch ( $value ) {
					case 'title':
						woocommerce_template_single_title();
						break;
		
					case 'price':
						woocommerce_template_single_price();
						break;
		
					case 'ratings':
						woocommerce_template_single_rating();
						break;
		
					case 'short_desc':
						woocommerce_template_single_excerpt();
						break;
		
					case 'add_cart':
						if ( method_exists( $this, 'add_to_cart_quick_view_button' ) ) {
							$this->add_to_cart_quick_view_button();
						}
						break;

					case 'meta':
						woocommerce_template_single_meta();
						break;
		
					default:
						break;
				}
			}
		
		}
		
		/**
		 * Footer markup.
		 */
		function qv_product_images_markup() {
			load_template( __DIR__ . '/template-parts/quick-view-product-image.php' );
		}

		/**
		 * Quick view button
		 */
		function add_quick_view_button() {
			global $product;
			$allowed_html = wp_kses_allowed_html( 'post' );

			$product_id = $product->get_id();
			// Get label.
			$label = __( 'Quick View', 'responsive-addons-pro' );

			$button  = '<div class="responsive-qv-button-wrap">';
			$button .= '<a href="#" class="button responsive-quick-view-button" data-product_id="' . $product_id . '">' . $label . '</a>';
			$button .= '</div>';
			$button  = apply_filters( 'responsive_woo_add_quick_view_button_html', $button, $label, $product );

			echo wp_kses_post( $button, $allowed_html );
		}

		/**
		 * Quick view on image
		 */
		function add_quick_view_on_img() {

			global $product;
			$allowed_html = wp_kses_allowed_html( 'post' );

			$product_id = $product->get_id();

			// Get label.
			$label = __( 'Quick View', 'responsive-addons-pro' );

			$button = '<div class="responsive-shop-thumbnail-wrap"><a href="#" class="responsive-quick-view-text" data-product_id="' . $product_id . '">' . $label . '</a></div>';
			$button = apply_filters( 'responsive_woo_add_quick_view_text_html', $button, $label, $product );

			echo wp_kses_post( $button, $allowed_html );
		}

		/**
		 * Quick view on image
		 */
		function add_quick_view_on_img_click() {

			global $product;
			$allowed_html = wp_kses_allowed_html( 'post' );

			$product_id = $product->get_id();

			$button = '<div class="responsive-quick-view-data" data-product_id="' . $product_id . '"></div>';
			$button = apply_filters( 'responsive_woo_add_quick_view_data_html', $button, $product );

			echo wp_kses_post( $button, $allowed_html );
		}

		/**
		 * Quick view html
		 */
		function quick_view_html() {
			$this->quick_view_dependent_data();
			load_template( __DIR__ . '/template-parts/quick-view-modal.php' );
		}

		/**
		 * Frontend scripts.
		 *
		 * @since 1.0
		 *
		 * @return void.
		 */
		function enqueue_frontend_scripts() {

			/* Directory and Extension */
			$file_prefix = '.min';

			if ( SCRIPT_DEBUG ) {
				$file_prefix = '';
			}

			$js_gen_path     = plugin_dir_url( __FILE__ ) . 'assets/js/';
			$shop_quick_view = get_theme_mod( 'shop_pagination_quick_view' );
			if ( 'disabled' !== $shop_quick_view ) {
				wp_enqueue_script( 'responsive-single-product-ajax-cart', $js_gen_path . 'product-add-to-cart-ajax' . $file_prefix . '.js', array( 'jquery', 'responsive-addons-pro' ), '3.17.2', true );
			}

			if ( is_shop() || is_product_taxonomy() ) {

				if ( is_shop() ) {
					$shop_page_display = get_option( 'woocommerce_shop_page_display', false );

					if ( 'subcategories' !== $shop_page_display || is_search() ) {
						wp_enqueue_script( 'responsive-shop-pagination-infinite', $js_gen_path . 'product-pagination-infinite' . $file_prefix . '.js', array( 'jquery', 'responsive-addons-pro' ), '3.17.2', true );
					}
				} elseif ( is_product_taxonomy() ) {
					wp_enqueue_script( 'responsive-shop-pagination-infinite', $js_gen_path . 'product-pagination-infinite' . $file_prefix . '.js', array( 'jquery', 'responsive-addons-pro' ), '3.17.2', true );
				}
				if ( 0 !== get_theme_mod( 'responsive_enable_native_cart_popup', 0 ) ) {
					wp_enqueue_script( 'wc-cart-fragments' );
					wp_enqueue_script( 'responsive-magnific-popup', $js_gen_path . 'min/magnific-popup.min.js', array( 'jquery' ), '1.1.0', true );
					wp_enqueue_script( 'responsive-woo-popup', $js_gen_path . 'woo-popup.js', array( 'jquery' ), null, true );
				}
			}
		}

		/**
		 * Quick view dependent data
		 */
		function quick_view_dependent_data() {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'flexslider' );
		}

		/**
		 *  For enqueue the java scripts.
		 */
		public function responsive_shop_js_localize() {

			$suffix    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$directory = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'assets/js' : 'assets/js';
			wp_enqueue_script(
				'responsive-shop-pagination-infinite',
				plugin_dir_url( __FILE__ ) . $directory . '/product-pagination-infinite' . $suffix . '.js',
				array(
					'jquery',
					'wp-util',
				),
				'3.17.2',
				true
			);
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wp-util' );

			global $wp_query;

			$shop_pagination            = get_theme_mod( 'shop_pagination', 'default' );
			$shop_infinite_scroll_event = get_theme_mod( 'shop-infinite-scroll-event', 'scroll' );

			$localize['query_vars']                   = wp_json_encode( $wp_query->query_vars );
			$localize['edit_post_url']                = admin_url( 'post.php?post={{id}}&action=edit' );
			$localize['ajax_url']                     = admin_url( 'admin-ajax.php' );
			$localize['shop_infinite_count']          = 2;
			$localize['shop_infinite_total']          = $wp_query->max_num_pages;
			$localize['shop_pagination']              = $shop_pagination;
			$localize['shop_infinite_scroll_event']   = $shop_infinite_scroll_event;
			$localize['shop_infinite_nonce']          = wp_create_nonce( 'responsive-shop-load-more-nonce' );
			$localize['shop_no_more_product_message'] = apply_filters( 'responsive_shop_no_more_product', __( 'No more products to show.', 'responsive' ) );
			$data['site_url']                         = get_site_url();

			$localize['show_comments'] = __( 'Show Comments', 'responsive' );

			wp_localize_script( 'responsive-shop-pagination-infinite', 'responsiveShopPaginationInfinite', $localize );
		}

		/**
		 * Shop Pagination.
		 *
		 * @return void
		 * @since 4.0.0
		 */
		public function shop_pagination() {

			$pagination = get_theme_mod( 'shop_pagination' );
			do_action( 'responsive_pagination_infinite_enqueue_script' );

			if ( 'infinite' === $pagination ) {
				remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
				add_action( 'woocommerce_after_shop_loop', array( $this, 'responsive_shop_pagination' ), 10 );
			}
		}

		/**
		 * Responsive Shop Pagination
		 *
		 * @param html $output Pagination markup.
		 *
		 * @return void
		 * @since 4.0.0
		 */
		public function responsive_shop_pagination( $output ) {

			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

			global $wp_query;

			$infinite_event = get_theme_mod( 'shop-infinite-scroll-event', 'scroll' );
			$load_more_text = get_theme_mod( 'shop-load-more-text', 'Load More' );
			
			if ( '' === $load_more_text ) {
				$load_more_text = __( 'Load More', 'responsive-addons-pro' );
			}

			if ( $wp_query->max_num_pages > 1 ) {
				?>
				<nav class="responsive-shop-pagination-infinite">
					<div class="responsive-loader">
						<div class="responsive-loader-1"></div>
						<div class="responsive-loader-2"></div>
						<div class="responsive-loader-3"></div>
					</div>
					<?php if ( 'click' === $infinite_event ) { ?>
						<span class="responsive-load-more active">
							<?php echo esc_html( $load_more_text ); ?>
						</span>
					<?php } ?>
				</nav>
				<?php
			}
		}
		/**
		 * Gets the popup template part.
		 */
		public function popup_template() {

			if ( ! class_exists( 'WooCommerce' )
				|| is_cart()
				|| is_checkout() ) {
				return;
			}
			load_template( __DIR__ . '/template-parts/woo-popup.php' );
		}

		/**
		 * Add to cart button
		 *
		 * @return void
		 */
		public function add_to_cart_quick_view_button() {
			check_ajax_referer( 'responsive-addons' );
			$enable_popup = get_theme_mod( 'responsive_enable_native_cart_popup', 0 );
			if ( 1 === $enable_popup ) {
				$product = wc_get_product( get_the_ID() );
				add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'quantity_inputs_for_loop_ajax_add_to_cart' ) );
				$add_to_cart = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
				if ( 'Read more' !== $product->add_to_cart_text() ) {
					$add_to_cart .= woocommerce_quantity_input(
						array(
							'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
							'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
							// WooCommerce does not provide nonce for the for hence the error is being suppressed.
							// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(),
							// phpcs:enable
						),
						$product,
						false
					);
				}

				$add_to_cart .= '<a type="submit" name="add_to_cart" href="?add-to-cart=' . get_the_ID() . '"  class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="' . get_the_ID() . '"  rel="nofollow">' . esc_html( $product->add_to_cart_text() ) . '</a>';
				$add_to_cart .= '</form>';
			} else {
				$add_to_cart = woocommerce_template_single_add_to_cart();
			}

			echo $add_to_cart; 
		}
		/**
		 * Update the cart fragments on AJAX.
		 *
		 * @since 1.0.0
		 * @param array $fragments Array of cart fragments.
		 * @return array Modified cart fragments.
		 */
		public function woocommerce_header_add_to_cart_fragment( $fragments ) {
			$fragments['.responsive-woo-total']      = '<span class="responsive-woo-total">' . WC()->cart->get_total() . '</span>';
			$fragments['.responsive-woo-cart-count'] = '<span class="responsive-woo-cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';

			return $fragments;
		}
		/**
		 * Add quantity input to loop add-to-cart button for Ajax behavior.
		 *
		 * @since 1.0.0
		 * @param string     $html    Original HTML output.
		 * @param WC_Product $product Product object.
		 * @return string Modified HTML with quantity input.
		 */
		public function quantity_inputs_for_loop_ajax_add_to_cart( $html, $product ) {
			if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
				// Get the necessary classes.
				$class = implode(
					' ',
					array_filter(
						array(
							'button',
							'product_type_' . $product->get_type(),
							$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
							$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
						)
					)
				);
				// Embedding the quantity field to Ajax add to cart button.
				$html = sprintf(
					'%s<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
					woocommerce_quantity_input( array(), $product, false ),
					esc_url( $product->add_to_cart_url() ),
					esc_attr( isset( $quantity ) ? $quantity : 1 ),
					esc_attr( $product->get_id() ),
					esc_attr( $product->get_sku() ),
					esc_attr( isset( $class ) ? $class : 'button' ),
					esc_html( $product->add_to_cart_text() )
				);
			}
			return $html;
		}
	}
}
Responsive_Addons_Woocommerce_Ext::get_instance();
