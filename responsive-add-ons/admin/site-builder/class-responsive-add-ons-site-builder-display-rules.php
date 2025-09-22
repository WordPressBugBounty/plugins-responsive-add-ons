<?php

/**
 * Responsive Add-ons Site Builder Display Rules
 * 
 * @package Responsive_Add_Ons
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Responsive_Add_Ons_Site_Builder_Display_Rules' ) ) {

    /**
     * Class Responsive_Add_Ons_Site_Builder_Display_Rules
     */
    class Responsive_Add_Ons_Site_Builder_Display_Rules {

        /**
		 * Member Variable
		 *
		 * @var object instance
		 */
		private static $instance;

        /**
		 * Location Selection Option
		 *
		 * @since  3.3.0
		 *
		 * @var $location_selection
		 */
		public static $location_selection;

        /**
		 * User Selection Option
		 *
		 * @since  3.3.0
		 *
		 * @var $user_selection
		 */
		public static $user_selection;

		/**
		 * Current page type
		 *
		 * @access private
		 * @static
		 *
		 * @since  1.3.0
		 *
		 * @var $current_page_type
		 */
		private static $current_page_type = null;

		/**
		 * Current page data
		 *
		 * @access private
		 * @static
		 *
		 * @since  3.3.0
		 *
		 * @var $current_page_data
		 */
		private static $current_page_data = array();

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
            add_action( 'admin_action_edit', array( $this, 'initialize_options' ) );
			add_action( 'wp_ajax_responsive_sb_get_posts_by_query', array( $this, 'responsive_sb_get_posts_by_query' ) );
        }

        /**
		 * Initialize member variables.
		 *
		 * @return void
		 */
		public function initialize_options() {
			self::$location_selection = self::get_location_selections();
			self::$user_selection     = self::get_user_selections();
		}

        /**
		 * Get location selection options.
		 *
		 * @param bool $consider_type Consider type for dealing with ruleset options.
		 * @return array
		 */
		public static function get_location_selections( $consider_type = false ) {

			$args = array(
				'public'   => true,
				'_builtin' => true,
			);

			$post_types = get_post_types( $args, 'objects' );
			unset( $post_types['attachment'] );

			$args['_builtin'] = false;
			$custom_post_type = get_post_types( $args, 'objects' );

			$post_types = apply_filters( 'responsive_site_builder_location_rule_post_types', array_merge( $post_types, $custom_post_type ) );

			$special_pages = array(
				'special-404'    => __( '404 Page', 'responsive-add-ons' ),
				'special-search' => __( 'Search Page', 'responsive-add-ons' ),
				'special-blog'   => __( 'Blog / Posts Page', 'responsive-add-ons' ),
				'special-front'  => __( 'Front Page', 'responsive-add-ons' ),
				'special-date'   => __( 'Date Archive', 'responsive-add-ons' ),
				'special-author' => __( 'Author Archive', 'responsive-add-ons' ),
			);

			if ( class_exists( 'WooCommerce' ) ) {
				$special_pages['special-woo-shop'] = __( 'WooCommerce Shop Page', 'responsive-add-ons' );
			}

			if ( 'single' === $consider_type ) {
				$global_val = array(
					'basic-global'    => __( 'Entire Website', 'responsive-add-ons' ),
					'basic-singulars' => __( 'All Singulars', 'responsive-add-ons' ),
				);
			} elseif ( 'archive' === $consider_type ) {
				$global_val = array(
					'basic-global'   => __( 'Entire Website', 'responsive-add-ons' ),
					'basic-archives' => __( 'All Archives', 'responsive-add-ons' ),
				);
			} else {
				$global_val = array(
					'basic-global'    => __( 'Entire Website', 'responsive-add-ons' ),
					'basic-singulars' => __( 'All Singulars', 'responsive-add-ons' ),
					'basic-archives'  => __( 'All Archives', 'responsive-add-ons' ),
				);
			}

			if ( 'single' === $consider_type ) {
				$selection_options = array(
					'basic' => array(
						'label' => __( 'Basic', 'responsive-add-ons' ),
						'value' => $global_val,
					),
				);
			} else {
				$selection_options = array(
					'basic'         => array(
						'label' => __( 'Basic', 'responsive-add-ons' ),
						'value' => $global_val,
					),
					'special-pages' => array(
						'label' => __( 'Special Pages', 'responsive-add-ons' ),
						'value' => $special_pages,
					),
				);
			}

			$args = array(
				'public' => true,
			);

			$taxonomies = get_taxonomies( $args, 'objects' );

			if ( ! empty( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {

					// skip post format taxonomy.
					if ( 'post_format' === $taxonomy->name ) {
						continue;
					}

					foreach ( $post_types as $post_type ) {

						$post_opt = self::get_post_target_rule_options( $post_type, $taxonomy, $consider_type );

						if ( isset( $selection_options[ $post_opt['post_key'] ] ) ) {

							if ( ! empty( $post_opt['value'] ) && is_array( $post_opt['value'] ) ) {

								foreach ( $post_opt['value'] as $key => $value ) {

									if ( ! in_array( $value, $selection_options[ $post_opt['post_key'] ]['value'] ) ) {
										$selection_options[ $post_opt['post_key'] ]['value'][ $key ] = $value;
									}
								}
							}
						} else {
							$selection_options[ $post_opt['post_key'] ] = array(
								'label' => $post_opt['label'],
								'value' => $post_opt['value'],
							);
						}
					}
				}
			}

			$selection_options['specific-target'] = array(
				'label' => __( 'Specific Target', 'responsive-add-ons' ),
				'value' => array(
					'specifics' => __( 'Specific Pages / Posts / Taxonomies, etc.', 'responsive-add-ons' ),
				),
			);

			/**
			 * Filter options displayed in the display conditions select field of Display conditions.
			 *
			 * @since 3.3.0
			 */
			return apply_filters( 'responsive_site_builder_display_on_list', $selection_options );
		}

        /**
		 * Get target rules for generating the markup for rule selector.
		 *
		 * @since  3.3.0
		 *
		 * @param object $post_type post type parameter.
		 * @param object $taxonomy taxonomy for creating the target rule markup.
		 * @param mixed  $consider_type consider type for dealing with rule options.
		 */
		public static function get_post_target_rule_options( $post_type, $taxonomy, $consider_type = false ) {

			$post_key    = str_replace( ' ', '-', strtolower( $post_type->label ) );
			$post_label  = ucwords( $post_type->label );
			$post_name   = $post_type->name;
			$post_option = array();

			if ( 'archive' !== $consider_type ) {
				/* translators: %s post label */
				$all_posts                          = sprintf( __( 'All %s', 'responsive-add-ons' ), $post_label );
				$post_option[ $post_name . '|all' ] = $all_posts;
			}

			if ( 'pages' !== $post_key && 'single' !== $consider_type ) {
				/* translators: %s post label */
				$all_archive                                = sprintf( __( 'All %s Archive', 'responsive-add-ons' ), $post_label );
				$post_option[ $post_name . '|all|archive' ] = $all_archive;
			}

			if ( 'single' !== $consider_type ) {
				if ( in_array( $post_type->name, $taxonomy->object_type ) ) {
					$tax_label = ucwords( $taxonomy->label );
					$tax_name  = $taxonomy->name;

					/* translators: %s taxonomy label */
					$tax_archive = sprintf( __( 'All %s Archive', 'responsive-add-ons' ), $tax_label );

					$post_option[ $post_name . '|all|taxarchive|' . $tax_name ] = $tax_archive;
				}
			}

			$post_output             = array();
			$post_output['post_key'] = $post_key;
			$post_output['label']    = $post_label;
			$post_output['value']    = $post_option;

			return $post_output;
		}

		/**
		 * Ajax handeler to return the posts based on the search query.
		 * When searching for the post/pages only titles are searched for.
		 *
		 * @since 3.3.0
		 */
		public function responsive_sb_get_posts_by_query() {

			check_ajax_referer( 'responsive-sb-get-posts-by-query', 'nonce' );

			$search_string = isset( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data          = array();
			$result        = array();

			$args = array(
				'public'   => true,
				'_builtin' => false,
			);

			$output     = 'names'; // names or objects, note names is the default.
			$operator   = 'and'; // also supports 'or'.
			$post_types = get_post_types( $args, $output, $operator );

			$post_types['Posts'] = 'post';
			$post_types['Pages'] = 'page';

			foreach ( $post_types as $key => $post_type ) {

				$data = array();

				add_filter( 'posts_search', array( $this, 'search_only_titles' ), 10, 2 );

				$query = new WP_Query(
					array(
						's'              => $search_string,
						'post_type'      => $post_type,
						'posts_per_page' => - 1,
					)
				);

				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						$title  = get_the_title();
						$title .= 0 != $query->post->post_parent ? ' (' . get_the_title( $query->post->post_parent ) . ')' : '';
						$id     = get_the_id();
						$data[] = array(
							'id'   => 'post-' . $id,
							'text' => $title,
						);
					}
				}

				if ( is_array( $data ) && ! empty( $data ) ) {
					$result[] = array(
						'text'     => $key,
						'children' => $data,
					);
				}
			}

			$data = array();

			wp_reset_postdata();

			$args = array(
				'public' => true,
			);

			$output     = 'objects'; // names or objects, note names is the default.
			$operator   = 'and'; // also supports 'or'.
			$taxonomies = get_taxonomies( $args, $output, $operator );

			foreach ( $taxonomies as $taxonomy ) {
				$terms = get_terms(
					$taxonomy->name,
					array(
						'orderby'    => 'count',
						'hide_empty' => 0,
						'name__like' => $search_string,
					)
				);

				$data = array();

				$label = ucwords( $taxonomy->label );

				if ( ! empty( $terms ) ) {

					foreach ( $terms as $term ) {
						$data[] = array(
							'id'   => 'tax-' . $term->term_id,
							'text' => $term->name . ' archive page',
						);

						$data[] = array(
							'id'   => 'tax-' . $term->term_id . '-single-' . $taxonomy->name,
							'text' => 'All singulars from ' . $term->name,
						);
					}
				}

				if ( is_array( $data ) && ! empty( $data ) ) {
					$result[] = array(
						'text'     => $label,
						'children' => $data,
					);
				}
			}

			// return the result in json.
			wp_send_json( $result );
		}

		/**
		 * Return search results only by post title.
		 * This is only run from responsive_sb_get_posts_by_query()
		 *
		 * @param  (string)   $search   Search SQL for WHERE clause.
		 * @param  (WP_Query) $wp_query The current WP_Query object.
		 *
		 * @return (string) The Modified Search SQL for WHERE clause.
		 */
		public function search_only_titles( $search, $wp_query ) {
			if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
				global $wpdb;

				$q = $wp_query->query_vars;
				$n = ! empty( $q['exact'] ) ? '' : '%';

				$search = array();

				foreach ( (array) $q['search_terms'] as $term ) {
					$search[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
				}

				if ( ! is_user_logged_in() ) {
					$search[] = "{$wpdb->posts}.post_password = ''";
				}

				$search = ' AND ' . implode( ' AND ', $search );
			}

			return $search;
		}

		/**
		 * Get user selection options.
		 *
		 * @access public
		 * @static
		 *
		 * @since 3.3.0
		 *
		 * @return array Array user roles list.
		 */
		public static function get_user_selections() {
			$selection_options = array(
				'basic'    => array(
					'label' => __( 'Basic', 'responsive-add-ons' ),
					'value' => array(
						'all'        => __( 'All', 'responsive-add-ons' ),
						'logged-in'  => __( 'Logged In', 'responsive-add-ons' ),
						'logged-out' => __( 'Logged Out', 'responsive-add-ons' ),
					),
				),

				'advanced' => array(
					'label' => __( 'Advanced', 'responsive-add-ons' ),
					'value' => array(),
				),
			);

			/* User roles */
			$roles = get_editable_roles();

			foreach ( $roles as $slug => $data ) {
				$selection_options['advanced']['value'][ $slug ] = $data['name'];
			}

			// Filter options displayed in the user select field of Display conditions.
			return apply_filters( 'responsive_sb_display_user_roles_list', $selection_options );
		}

		/**
		 * Get posts by conditions
		 *
		 * @since  3.3.0
		 * @param  string $post_type Post Type.
		 * @param  array  $option meta option name.
		 *
		 * @return object|array  Posts.
		 */
		public function get_posts_by_conditions( $post_type, $option ) {

			global $wpdb;
			global $post;

			$post_type = $post_type ? esc_sql( $post_type ) : esc_sql( $post->post_type );

			if ( is_array( self::$current_page_data ) && isset( self::$current_page_data[ $post_type ] ) ) {
				return apply_filters( 'responsive_addons_get_display_posts_by_conditions', self::$current_page_data[ $post_type ], $post_type );
			}

			$current_page_type                     = $this->get_current_page_type();
			self::$current_page_data[ $post_type ] = array();
			$option['current_post_id']             = self::$current_page_data['ID'];
			$current_post_type                     = esc_sql( get_post_type() );
			$current_post_id                       = false;
			$q_obj                                 = get_queried_object();

			$location = isset( $option['location'] ) ? esc_sql( $option['location'] ) : '';

			$query = "SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} as pm
					INNER JOIN {$wpdb->posts} as p ON pm.post_id = p.ID
					WHERE pm.meta_key = '{$location}'
					AND p.post_type = '{$post_type}'
					AND p.post_status = 'publish'";

			$orderby = ' ORDER BY p.post_date DESC';

			/* Entire Website */
			$meta_args = "pm.meta_value LIKE '%\"basic-global\"%'";

			$meta_args = apply_filters( 'responsive_addons_meta_args_post_by_condition', $meta_args, $q_obj, $current_post_id );

			switch ( $current_page_type ) {
				case 'is_404':
					$meta_args .= " OR pm.meta_value LIKE '%\"special-404\"%'";
					break;
				case 'is_search':
					$meta_args .= " OR pm.meta_value LIKE '%\"special-search\"%'";
					break;
				case 'is_archive':
				case 'is_tax':
				case 'is_date':
				case 'is_author':
					$meta_args .= " OR pm.meta_value LIKE '%\"basic-archives\"%'";
					$meta_args .= " OR pm.meta_value LIKE '%\"{$current_post_type}|all|archive\"%'";

					if ( 'is_tax' === $current_page_type && ( is_category() || is_tag() || is_tax() ) ) {

						if ( is_object( $q_obj ) ) {
							$meta_args .= " OR pm.meta_value LIKE '%\"{$current_post_type}|all|taxarchive|{$q_obj->taxonomy}\"%'";
							$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$q_obj->term_id}\"%'";
						}
					} elseif ( 'is_date' === $current_page_type ) {
						$meta_args .= " OR pm.meta_value LIKE '%\"special-date\"%'";
					} elseif ( 'is_author' === $current_page_type ) {
						$meta_args .= " OR pm.meta_value LIKE '%\"special-author\"%'";
					}
					break;
				case 'is_home':
					$meta_args .= " OR pm.meta_value LIKE '%\"special-blog\"%'";
					break;
				case 'is_front_page':
					$current_id      = esc_sql( get_the_id() );
					$current_post_id = $current_id;
					$meta_args      .= " OR pm.meta_value LIKE '%\"special-front\"%'";
					$meta_args      .= " OR pm.meta_value LIKE '%\"{$current_post_type}|all\"%'";
					$meta_args      .= " OR pm.meta_value LIKE '%\"post-{$current_id}\"%'";
					break;
				case 'is_singular':
					$current_id      = esc_sql( get_the_id() );
					$current_post_id = $current_id;
					$meta_args      .= " OR pm.meta_value LIKE '%\"basic-singulars\"%'";
					$meta_args      .= " OR pm.meta_value LIKE '%\"{$current_post_type}|all\"%'";
					$meta_args      .= " OR pm.meta_value LIKE '%\"post-{$current_id}\"%'";
					if ( is_object( $q_obj ) ) {
						$taxonomies = get_object_taxonomies( $q_obj->post_type );
						$terms      = wp_get_post_terms( $q_obj->ID, $taxonomies );

						foreach ( $terms as $term ) {
							$meta_args .= " OR pm.meta_value LIKE '%\"tax-{$term->term_id}-single-{$term->taxonomy}\"%'";
						}
					}

					break;
				case 'is_woo_shop_page':
					$meta_args .= " OR pm.meta_value LIKE '%\"special-woo-shop\"%'";
					break;
				case '':
					$current_post_id = get_the_id();
					break;
			}

			// Ignore the PHPCS warning about constant declaration.
			// @codingStandardsIgnoreStart
			$posts  = $wpdb->get_results( $query . ' AND (' . $meta_args . ')' . $orderby );
			// @codingStandardsIgnoreEnd

			$enabled_key = isset( $option['enabled'] ) ? $option['enabled'] : '';

			foreach ( $posts as $local_post ) {
				// ignore disabled layouts.
				if ( $enabled_key && 'disabled' === get_post_meta( $local_post->ID, $enabled_key, true ) ) {
					continue;
				}

				self::$current_page_data[ $post_type ][ $local_post->ID ] = array(
					'id'       => $local_post->ID,
					'location' => maybe_unserialize( $local_post->meta_value ),
				);
			}

			$option['current_post_id'] = $current_post_id;

			$this->remove_exclusion_rule_posts( $post_type, $option );
			$this->remove_user_rule_posts( $post_type, $option );

			return apply_filters( 'responsive_addons_get_display_posts_by_conditions', self::$current_page_data[ $post_type ], $post_type );
		}

		/**
		 * Get current page type
		 *
		 * @access public
		 *
		 * @since  3.3.0
		 *
		 * @return string Page Type.
		 */
		public function get_current_page_type() {

			if ( null === self::$current_page_type ) {

				$page_type  = '';
				$current_id = false;

				if ( is_404() ) {
					$page_type = 'is_404';
				} elseif ( is_search() ) {
					$page_type = 'is_search';
				} elseif ( is_archive() ) {
					$page_type = 'is_archive';

					if ( is_category() || is_tag() || is_tax() ) {
						$page_type = 'is_tax';
					} elseif ( is_date() ) {
						$page_type = 'is_date';
					} elseif ( is_author() ) {
						$page_type = 'is_author';
					} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
						$page_type = 'is_woo_shop_page';
					}
				} elseif ( is_home() ) {
					$page_type = 'is_home';
				} elseif ( is_front_page() ) {
					$page_type  = 'is_front_page';
					$current_id = get_the_id();
				} elseif ( is_singular() ) {
					$page_type  = 'is_singular';
					$current_id = get_the_id();
				} else {
					$current_id = get_the_id();
				}

				self::$current_page_data['ID'] = $current_id;
				self::$current_page_type       = $page_type;
			}

			return self::$current_page_type;
		}

		/**
		 * Remove exclusion rule posts.
		 *
		 * @since  3.3.0
		 * @param  string $post_type Post Type.
		 * @param  array  $option meta option name.
		 */
		public function remove_exclusion_rule_posts( $post_type, $option ) {

			$exclusion       = isset( $option['exclusion'] ) ? $option['exclusion'] : '';
			$current_post_id = isset( $option['current_post_id'] ) ? $option['current_post_id'] : false;

			foreach ( self::$current_page_data[ $post_type ] as $c_post_id => $c_data ) {

				$exclusion_rules = get_post_meta( $c_post_id, $exclusion, true );
				$is_exclude      = $this->parse_layout_display_condition( $current_post_id, $exclusion_rules );

				if ( $is_exclude ) {
					unset( self::$current_page_data[ $post_type ][ $c_post_id ] );
				}
			}
		}

		/**
		 * Checks for the display condition for the current page/
		 *
		 * @param  int   $post_id Current post ID.
		 * @param  array $rules   Array of rules Display on | Exclude on.
		 *
		 * @return bool  Returns true or false depending on if the $rules match for the current page and the layout is to be displayed.
		 */
		public function parse_layout_display_condition( $post_id, $rules ) {

			$display           = false;
			$current_post_type = get_post_type( $post_id );

			if ( isset( $rules['rule'] ) && is_array( $rules['rule'] ) && ! empty( $rules['rule'] ) ) {
				foreach ( $rules['rule'] as $rule ) {

					if ( ! empty( $rule ) ) {

						if ( strrpos( $rule, 'all' ) !== false ) {
							$rule_case = 'all';
						} else {
							$rule_case = $rule;
						}

						switch ( $rule_case ) {
							case 'basic-global':
								$display = true;
								break;

							case 'basic-singulars':
								if ( is_singular() ) {
									$display = true;
								}
								break;

							case 'basic-archives':
								if ( is_archive() ) {
									$display = true;
								}
								break;

							case 'special-404':
								if ( is_404() ) {
									$display = true;
								}
								break;

							case 'special-search':
								if ( is_search() ) {
									$display = true;
								}
								break;

							case 'special-blog':
								if ( is_home() ) {
									$display = true;
								}
								break;

							case 'special-front':
								if ( is_front_page() ) {
									$display = true;
								}
								break;

							case 'special-date':
								if ( is_date() ) {
									$display = true;
								}
								break;

							case 'special-author':
								if ( is_author() ) {
									$display = true;
								}
								break;

							case 'special-woo-shop':
								if ( function_exists( 'is_shop' ) && is_shop() ) {
									$display = true;
								}
								break;

							case 'all':
								$rule_data = explode( '|', $rule );

								$post_type     = isset( $rule_data[0] ) ? $rule_data[0] : false;
								$archieve_type = isset( $rule_data[2] ) ? $rule_data[2] : false;
								$taxonomy      = isset( $rule_data[3] ) ? $rule_data[3] : false;
								if ( false === $archieve_type ) {

									$current_post_type = get_post_type( $post_id );

									if ( false !== $post_id && $current_post_type == $post_type ) {

										$display = true;
									}
								} else {

									if ( is_archive() ) {

										$current_post_type = get_post_type();
										if ( $current_post_type == $post_type ) {
											if ( 'archive' === $archieve_type ) {
												$display = true;
											} elseif ( 'taxarchive' === $archieve_type ) {

												$obj              = get_queried_object();
												$current_taxonomy = '';
												if ( '' !== $obj && null !== $obj && isset( $obj->taxonomy ) ) {
													$current_taxonomy = $obj->taxonomy;
												}

												if ( $current_taxonomy == $taxonomy ) {
													$display = true;
												}
											}
										}
									}
								}
								break;

							case 'specifics':
								if ( isset( $rules['specific'] ) && is_array( $rules['specific'] ) ) {
									foreach ( $rules['specific'] as $specific_page ) {

										$specific_data      = explode( '-', $specific_page );
										$specific_post_type = isset( $specific_data[0] ) ? $specific_data[0] : false;
										$specific_post_id   = isset( $specific_data[1] ) ? $specific_data[1] : false;
										if ( 'post' === $specific_post_type ) {
											if ( $specific_post_id == $post_id ) {
												$display = true;
											}
										} elseif ( isset( $specific_data[2] ) && ( 'single' === $specific_data[2] ) && 'tax' === $specific_post_type ) {

											if ( is_singular() ) {
												$term_details = get_term( $specific_post_id );

												if ( isset( $term_details->taxonomy ) ) {
													$has_term = has_term( (int) $specific_post_id, $term_details->taxonomy, $post_id );

													if ( $has_term ) {
														$display = true;
													}
												}
											}
										} elseif ( 'tax' === $specific_post_type ) {
											$tax_id = get_queried_object_id();
											if ( $specific_post_id == $tax_id ) {
												$display = true;
											}
										}
									}
								}
								break;

							default:
								break;
						}
					}

					if ( $display ) {
						break;
					}
				}
			}

			return $display;
		}

		/**
		 * Remove user rule posts.
		 *
		 * @since  3.3.0
		 * @param  int   $post_type Post Type.
		 * @param  array $option meta option name.
		 */
		public function remove_user_rule_posts( $post_type, $option ) {

			$users           = isset( $option['users'] ) ? $option['users'] : '';
			$current_post_id = isset( $option['current_post_id'] ) ? $option['current_post_id'] : false;

			foreach ( self::$current_page_data[ $post_type ] as $c_post_id => $c_data ) {

				$user_rules = get_post_meta( $c_post_id, $users, true );
				$is_user    = $this->parse_user_role_condition( $current_post_id, $user_rules );

				if ( ! $is_user ) {
					unset( self::$current_page_data[ $post_type ][ $c_post_id ] );
				}
			}
		}

		/**
		 * Parse user role condition.
		 *
		 * @since  3.3.0
		 * @param  int   $post_id Post ID.
		 * @param  Array $rules   Current user rules.
		 *
		 * @return bool  True = user condition passes. False = User condition does not pass.
		 */
		public function parse_user_role_condition( $post_id, $rules ) {

			$show_popup = true;

			if ( is_array( $rules ) && ! empty( $rules ) ) {
				$show_popup = false;

				foreach ( $rules as $rule ) {

					switch ( $rule ) {
						case '':
						case 'all':
							$show_popup = true;
							break;

						case 'logged-in':
							if ( is_user_logged_in() ) {
								$show_popup = true;
							}
							break;

						case 'logged-out':
							if ( ! is_user_logged_in() ) {
								$show_popup = true;
							}
							break;

						default:
							if ( is_user_logged_in() ) {

								$current_user = wp_get_current_user();

								if ( isset( $current_user->roles )
										&& is_array( $current_user->roles )
										&& in_array( $rule, $current_user->roles )
									) {

									$show_popup = true;
								}
							}
							break;
					}

					if ( $show_popup ) {
						break;
					}
				}
			}

			return $show_popup;
		}

    }
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Responsive_Add_Ons_Site_Builder_Display_Rules::get_instance();