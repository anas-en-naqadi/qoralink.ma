<?php
/**
 * WooCommerce Compatibility & Live AJAX Cart Fragments
 *
 * Ensures reactive live badge counter updates for qora/site-header and AJAX
 * add-to-cart operations across both native WooCommerce and WoodMart scripts.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'qora_is_woocommerce_active' ) ) {
	/**
	 * Check if WooCommerce is active and its core cart class is available.
	 *
	 * @return bool
	 */
	function qora_is_woocommerce_active() {
		return class_exists( 'WooCommerce' ) && function_exists( 'WC' );
	}
}

if ( ! function_exists( 'qora_add_to_cart_fragments' ) ) {
	/**
	 * Filter WooCommerce AJAX fragments to update Qoralink cart badge counters.
	 *
	 * Returns replacement markup for both .qora-cart-badge and .qora-cart-count selectors.
	 * Integrates with WoodMart's updateCartFragmentsFix.js by retaining .wd-cart-number.
	 *
	 * @param array $fragments Existing array of fragments.
	 * @return array Modified fragments.
	 */
	function qora_add_to_cart_fragments( $fragments ) {
		if ( ! is_array( $fragments ) ) {
			$fragments = array();
		}

		$count = (int) qora_get_cart_count();

		// Target: span.qora-cart-badge (used in qora/site-header)
		ob_start();
		?>
		<span class="qora-cart-badge qora-cart-count"><?php echo esc_html( $count ); ?></span>
		<?php
		$fragments['span.qora-cart-badge'] = ob_get_clean();

		// Target: span.qora-cart-count
		ob_start();
		?>
		<span class="qora-cart-badge qora-cart-count"><?php echo esc_html( $count ); ?></span>
		<?php
		$fragments['span.qora-cart-count'] = ob_get_clean();

		return $fragments;
	}
	add_filter( 'woocommerce_add_to_cart_fragments', 'qora_add_to_cart_fragments', 50 );
}

if ( ! function_exists( 'qora_ensure_wc_frontend_scripts' ) ) {
	/**
	 * Ensure WooCommerce AJAX cart fragments and add-to-cart scripts are loaded
	 * on all frontend pages including custom Gutenberg block templates.
	 *
	 * @return void
	 */
	function qora_ensure_wc_frontend_scripts() {
		if ( ! qora_is_woocommerce_active() || is_admin() ) {
			return;
		}

		// Ensure native cart fragments and add-to-cart handlers are enqueued
		wp_enqueue_script( 'wc-cart-fragments' );
		wp_enqueue_script( 'wc-add-to-cart' );
	}
	add_action( 'wp_enqueue_scripts', 'qora_ensure_wc_frontend_scripts', 25 );
}

if ( ! function_exists( 'qora_get_cart_count' ) ) {
	/**
	 * Safe helper to return the current quantity of items in the WooCommerce cart.
	 *
	 * @return int Total item count or 0 if cart is unavailable.
	 */
	function qora_get_cart_count() {
		if ( qora_is_woocommerce_active() && is_object( WC()->cart ) && method_exists( WC()->cart, 'get_cart_contents_count' ) ) {
			return (int) WC()->cart->get_cart_contents_count();
		}
		return 0;
	}
}

if ( ! function_exists( 'qora_get_cart_subtotal' ) ) {
	/**
	 * Safe helper to return the formatted cart subtotal.
	 *
	 * @return string Formatted subtotal HTML or empty string.
	 */
	function qora_get_cart_subtotal() {
		if ( qora_is_woocommerce_active() && is_object( WC()->cart ) && method_exists( WC()->cart, 'get_cart_subtotal' ) ) {
			return WC()->cart->get_cart_subtotal();
		}
		return '';
	}
}

if ( ! function_exists( 'qora_get_cart_url' ) ) {
	/**
	 * Safe helper to get the WooCommerce Cart page URL with fallback.
	 *
	 * @return string Cart page URL.
	 */
	function qora_get_cart_url() {
		if ( function_exists( 'wc_get_cart_url' ) ) {
			return wc_get_cart_url();
		}
		return home_url( '/cart/' );
	}
}

if ( ! function_exists( 'qora_get_account_url' ) ) {
	/**
	 * Safe helper to get the WooCommerce My Account page URL with fallback.
	 *
	 * @return string My Account page URL.
	 */
	function qora_get_account_url() {
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			return wc_get_page_permalink( 'myaccount' );
		}
		return home_url( '/my-account/' );
	}
}

if ( ! function_exists( 'qora_get_wishlist_url' ) ) {
	/**
	 * Safe helper to get the Wishlist page URL using WoodMart native function or fallback.
	 *
	 * @return string Wishlist page URL.
	 */
	function qora_get_wishlist_url() {
		if ( function_exists( 'woodmart_get_wishlist_page_url' ) ) {
			return woodmart_get_wishlist_page_url();
		}
		return home_url( '/wishlist/' );
	}
}

if ( ! function_exists( 'qora_cart_side_widget' ) ) {
	/**
	 * Outputs WoodMart shopping cart sidebar widget and enqueues required scripts/styles
	 * so clicking the cart icon opens the side drawer without a page refresh.
	 *
	 * @return void
	 */
	function qora_cart_side_widget() {
		static $printed = false;
		if ( $printed || ! qora_is_woocommerce_active() ) {
			return;
		}
		$printed = true;

		if ( function_exists( 'woodmart_enqueue_js_script' ) ) {
			woodmart_enqueue_js_script( 'cart-widget' );
		}
		if ( function_exists( 'woodmart_enqueue_inline_style' ) ) {
			woodmart_enqueue_inline_style( 'header-cart-side' );
			woodmart_enqueue_inline_style( 'widget-general' );
			woodmart_enqueue_inline_style( 'widget-shopping-cart' );
			woodmart_enqueue_inline_style( 'widget-product-list', true );
		}
		?>
		<div class="cart-widget-side wd-side-hidden wd-right" role="complementary" aria-label="<?php esc_attr_e( 'Shopping cart sidebar', 'woodmart' ); ?>">
			<div class="wd-heading">
				<span class="title"><?php esc_html_e( 'Shopping cart', 'woodmart' ); ?></span>
				<div class="close-side-widget wd-action-btn wd-style-text wd-cross-icon">
					<a href="#" rel="nofollow">
						<span class="wd-action-icon"></span>
						<span class="wd-action-text"><?php esc_html_e( 'Close', 'woodmart' ); ?></span>
					</a>
				</div>
			</div>
			<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
		</div>
		<div class="wd-close-side wd-fill"></div>
		<?php
	}
	add_action( 'woodmart_before_wp_footer', 'qora_cart_side_widget', 145 );
	add_action( 'wp_footer', 'qora_cart_side_widget', 10 );
}

// -----------------------------------------------------------------------------
// 3. Qoralink Product Loop Layout Registration (Grid & List)
// -----------------------------------------------------------------------------

if ( ! function_exists( 'qora_product_loop_card_shortcode' ) ) {
	/**
	 * Shortcode [qora_product_loop_card view="grid|list"] to render the Qoralink
	 * Product Card inside WoodMart Custom Product Loop layouts.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered product card HTML.
	 */
	function qora_product_loop_card_shortcode( $atts = array() ) {
		global $product;

		if ( ( ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		if ( ! is_object( $product ) || ! function_exists( 'qora_render_product_card' ) ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'view' => 'grid',
			),
			$atts,
			'qora_product_loop_card'
		);

		$loop_idx = function_exists( 'woodmart_loop_prop' ) ? (int) woodmart_loop_prop( 'woocommerce_loop' ) : 0;

		ob_start();
		qora_render_product_card( $product, $loop_idx, $atts['view'] );
		return ob_get_clean();
	}
	add_shortcode( 'qora_product_loop_card', 'qora_product_loop_card_shortcode' );
}

if ( ! function_exists( 'qora_ensure_product_loop_layouts' ) ) {
	/**
	 * Automatically creates the "Qoralink Product Card (Grid)" and
	 * "Qoralink Product Card (List)" layouts in WoodMart's `woodmart_layout`
	 * post type (`wd_layout_type = product_loop_item`) so they appear in
	 * Theme Settings -> Product Archive -> Products loop dropdowns and are
	 * active across the entire website.
	 *
	 * @return void
	 */
	function qora_ensure_product_loop_layouts() {
		if ( ! qora_is_woocommerce_active() || ! post_type_exists( 'woodmart_layout' ) ) {
			return;
		}

		$grid_id = (int) get_option( 'qora_loop_layout_grid_id', 0 );
		if ( ! $grid_id || 'publish' !== get_post_status( $grid_id ) ) {
			$existing_grid = get_posts(
				array(
					'post_type'      => 'woodmart_layout',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'title'          => 'Qoralink Product Card (Grid)',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $existing_grid ) ) {
				$grid_id = (int) $existing_grid[0];
			} else {
				$grid_id = (int) wp_insert_post(
					array(
						'post_title'   => 'Qoralink Product Card (Grid)',
						'post_name'    => 'qoralink-product-card-grid',
						'post_status'  => 'publish',
						'post_type'    => 'woodmart_layout',
						'post_content' => '[qora_product_loop_card view="grid"]',
						'meta_input'   => array(
							'wd_layout_type' => 'product_loop_item',
						),
					)
				);
			}

			if ( $grid_id > 0 ) {
				update_post_meta( $grid_id, 'wd_layout_type', 'product_loop_item' );
				update_option( 'qora_loop_layout_grid_id', $grid_id );
			}
		}

		$list_id = (int) get_option( 'qora_loop_layout_list_id', 0 );
		if ( ! $list_id || 'publish' !== get_post_status( $list_id ) ) {
			$existing_list = get_posts(
				array(
					'post_type'      => 'woodmart_layout',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'title'          => 'Qoralink Product Card (List)',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $existing_list ) ) {
				$list_id = (int) $existing_list[0];
			} else {
				$list_id = (int) wp_insert_post(
					array(
						'post_title'   => 'Qoralink Product Card (List)',
						'post_name'    => 'qoralink-product-card-list',
						'post_status'  => 'publish',
						'post_type'    => 'woodmart_layout',
						'post_content' => '[qora_product_loop_card view="list"]',
						'meta_input'   => array(
							'wd_layout_type' => 'product_loop_item',
						),
					)
				);
			}

			if ( $list_id > 0 ) {
				update_post_meta( $list_id, 'wd_layout_type', 'product_loop_item' );
				update_option( 'qora_loop_layout_list_id', $list_id );
			}
		}

		// Sync into WoodMart saved options if not yet configured so the Theme Settings dropdowns show them selected
		if ( $grid_id > 0 && ! get_option( 'qora_loop_layout_configured_v2' ) ) {
			$wd_opts = get_option( 'xts-woodmart-options' );
			if ( is_array( $wd_opts ) ) {
				$wd_opts['products_hover_type']  = 'custom';
				$wd_opts['product_custom_hover'] = (string) $grid_id;
				if ( $list_id > 0 ) {
					$wd_opts['product_custom_list'] = (string) $list_id;
				}
				update_option( 'xts-woodmart-options', $wd_opts );
			}
			if ( isset( $GLOBALS['xts_woodmart_options'] ) && is_array( $GLOBALS['xts_woodmart_options'] ) ) {
				$GLOBALS['xts_woodmart_options']['products_hover_type']  = 'custom';
				$GLOBALS['xts_woodmart_options']['product_custom_hover'] = (string) $grid_id;
				if ( $list_id > 0 ) {
					$GLOBALS['xts_woodmart_options']['product_custom_list'] = (string) $list_id;
				}
			}
			if ( isset( $GLOBALS['woodmart_options'] ) && is_array( $GLOBALS['woodmart_options'] ) ) {
				$GLOBALS['woodmart_options']['products_hover_type']  = 'custom';
				$GLOBALS['woodmart_options']['product_custom_hover'] = (string) $grid_id;
				if ( $list_id > 0 ) {
					$GLOBALS['woodmart_options']['product_custom_list'] = (string) $list_id;
				}
			}
			update_option( 'qora_loop_layout_configured_v2', 1 );
		}
	}
	add_action( 'init', 'qora_ensure_product_loop_layouts', 20 );
}

if ( ! function_exists( 'qora_filter_woodmart_product_loop_options' ) ) {
	/**
	 * Ensures WoodMart product loops default to the custom Qoralink Product Card
	 * layout when Custom mode is active or no other custom layout is selected.
	 *
	 * @param mixed  $opt  Current option value.
	 * @param string $slug Option slug.
	 * @return mixed Filtered option value.
	 */
	function qora_filter_woodmart_product_loop_options( $opt, $slug ) {
		if ( 'products_hover_type' === $slug ) {
			$grid_id = (int) get_option( 'qora_loop_layout_grid_id', 0 );
			if ( $grid_id > 0 ) {
				return 'custom';
			}
		}

		if ( 'product_custom_hover' === $slug && empty( $opt ) ) {
			$grid_id = (int) get_option( 'qora_loop_layout_grid_id', 0 );
			if ( $grid_id > 0 ) {
				return (string) $grid_id;
			}
		}

		if ( 'product_custom_list' === $slug && empty( $opt ) ) {
			$list_id = (int) get_option( 'qora_loop_layout_list_id', 0 );
			if ( $list_id > 0 ) {
				return (string) $list_id;
			}
		}

		return $opt;
	}
	add_filter( 'woodmart_option', 'qora_filter_woodmart_product_loop_options', 10, 2 );
}

// -----------------------------------------------------------------------------
// 4. Live Product Search Autocomplete (REST API + WC AJAX + Admin AJAX)
// -----------------------------------------------------------------------------

if ( ! function_exists( 'qora_get_live_search_products_data' ) ) {
	/**
	 * Queries WooCommerce products matching a search term across title/content,
	 * SKU, product_brand, and product_cat taxonomies.
	 *
	 * @param string $raw_term Search keyword typed by the user.
	 * @param int    $limit    Maximum number of product suggestions to return (default 5).
	 * @return array Structured payload with items, total count, and full search URL.
	 */
	function qora_get_live_search_products_data( $raw_term, $limit = 5 ) {
		$term  = sanitize_text_field( wp_unslash( (string) $raw_term ) );
		$limit = max( 1, min( 10, (int) $limit ) );

		$search_url = add_query_arg(
			array(
				's'         => $term,
				'post_type' => 'product',
			),
			home_url( '/' )
		);

		if ( ! qora_is_woocommerce_active() || mb_strlen( $term ) < 1 ) {
			return array(
				'query'      => $term,
				'total'      => 0,
				'search_url' => $search_url,
				'items'      => array(),
			);
		}

		$visibility_terms = wc_get_product_visibility_term_ids();
		$tax_query        = array( 'relation' => 'AND' );

		if ( ! empty( $visibility_terms['exclude-from-search'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => array( (int) $visibility_terms['exclude-from-search'] ),
				'operator' => 'NOT IN',
			);
		}

		// 1. Primary WP_Query by keyword (title, excerpt, content)
		$keyword_query = new WP_Query(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				's'                   => $term,
				'posts_per_page'      => $limit,
				'fields'              => 'ids',
				'ignore_sticky_posts' => true,
				'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			)
		);

		$matched_ids = is_array( $keyword_query->posts ) ? array_map( 'intval', $keyword_query->posts ) : array();
		$total_found = (int) $keyword_query->found_posts;

		// 2. Supplement with SKU or Brand/Category taxonomy matches if fewer than $limit found
		if ( count( $matched_ids ) < $limit ) {
			global $wpdb;
			$needed   = $limit - count( $matched_ids );
			$like_val = '%' . $wpdb->esc_like( $term ) . '%';

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$sku_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT p.ID
					 FROM {$wpdb->posts} p
					 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
					 WHERE p.post_type = 'product'
					   AND p.post_status = 'publish'
					   AND pm.meta_key = '_sku'
					   AND pm.meta_value != ''
					   AND pm.meta_value LIKE %s
					 LIMIT %d",
					$like_val,
					$limit
				)
			);

			if ( ! empty( $sku_ids ) ) {
				foreach ( $sku_ids as $sku_id ) {
					$sku_id = (int) $sku_id;
					if ( ! in_array( $sku_id, $matched_ids, true ) && count( $matched_ids ) < $limit ) {
						$matched_ids[] = $sku_id;
						$total_found++;
					}
				}
			}
		}

		if ( count( $matched_ids ) < $limit ) {
			$needed         = $limit - count( $matched_ids );
			$or_tax_clauses = array( 'relation' => 'OR' );

			if ( taxonomy_exists( 'product_brand' ) ) {
				$brand_ids = get_terms(
					array(
						'taxonomy'   => 'product_brand',
						'name__like' => $term,
						'hide_empty' => true,
						'fields'     => 'ids',
					)
				);
				if ( ! is_wp_error( $brand_ids ) && ! empty( $brand_ids ) ) {
					$or_tax_clauses[] = array(
						'taxonomy' => 'product_brand',
						'field'    => 'term_id',
						'terms'    => array_map( 'intval', $brand_ids ),
						'operator' => 'IN',
					);
				}
			}

			if ( taxonomy_exists( 'product_cat' ) ) {
				$cat_ids = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'name__like' => $term,
						'hide_empty' => true,
						'fields'     => 'ids',
					)
				);
				if ( ! is_wp_error( $cat_ids ) && ! empty( $cat_ids ) ) {
					$or_tax_clauses[] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => array_map( 'intval', $cat_ids ),
						'operator' => 'IN',
					);
				}
			}

			if ( count( $or_tax_clauses ) > 1 ) {
				$term_tax_query   = $tax_query;
				$term_tax_query[] = $or_tax_clauses;

				$tax_prod_query = new WP_Query(
					array(
						'post_type'           => 'product',
						'post_status'         => 'publish',
						'posts_per_page'      => $needed,
						'post__not_in'        => $matched_ids,
						'fields'              => 'ids',
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
						'tax_query'           => $term_tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					)
				);

				if ( ! empty( $tax_prod_query->posts ) ) {
					foreach ( $tax_prod_query->posts as $tp_id ) {
						$matched_ids[] = (int) $tp_id;
						$total_found++;
					}
				}
			}
		}

		$matched_ids = array_slice( array_values( array_unique( $matched_ids ) ), 0, $limit );
		$items       = array();

		foreach ( $matched_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
				continue;
			}

			$img_id  = (int) $product->get_image_id();
			$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'woocommerce_gallery_thumbnail' ) : '';
			if ( ! $img_url && $img_id ) {
				$img_url = wp_get_attachment_image_url( $img_id, 'woocommerce_thumbnail' );
			}
			if ( ! $img_url ) {
				$img_url = function_exists( 'wc_placeholder_img_src' )
					? wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' )
					: QORA_THEME_URI . '/assets/images/placeholder-cat.svg';
			}

			$brand = '';
			if ( taxonomy_exists( 'product_brand' ) ) {
				$brands = wp_get_post_terms( $product_id, 'product_brand', array( 'fields' => 'names' ) );
				if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
					$brand = $brands[0];
				}
			}
			if ( '' === $brand ) {
				$cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'names' ) );
				if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
					$brand = $cats[0];
				}
			}

			$raw_specs = function_exists( 'qora_get_product_specs_line' )
				? qora_get_product_specs_line( $product )
				: '';
			$specs     = html_entity_decode( wp_strip_all_tags( $raw_specs ), ENT_QUOTES, 'UTF-8' );

			$items[] = array(
				'id'         => (int) $product->get_id(),
				'title'      => wp_strip_all_tags( $product->get_name() ),
				'url'        => esc_url_raw( $product->get_permalink() ),
				'image'      => esc_url_raw( $img_url ),
				'brand'      => sanitize_text_field( $brand ),
				'specs'      => sanitize_text_field( $specs ),
				'price_html' => wp_kses_post( $product->get_price_html() ),
				'in_stock'   => (bool) $product->is_in_stock(),
			);
		}

		return array(
			'query'      => $term,
			'total'      => max( count( $items ), $total_found ),
			'search_url' => esc_url_raw( $search_url ),
			'items'      => $items,
		);
	}
}

if ( ! function_exists( 'qora_register_live_search_rest_route' ) ) {
	/**
	 * Registers GET /wp-json/qora/v1/search-products endpoint for instant live search.
	 *
	 * @return void
	 */
	function qora_register_live_search_rest_route() {
		register_rest_route(
			'qora/v1',
			'/search-products',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'args'                => array(
					'q'     => array(
						'required'          => false,
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'limit' => array(
						'required'          => false,
						'type'              => 'integer',
						'default'           => 5,
						'sanitize_callback' => 'absint',
					),
				),
				'callback'            => function ( WP_REST_Request $request ) {
					$term  = (string) $request->get_param( 'q' );
					$limit = (int) $request->get_param( 'limit' );
					$data  = qora_get_live_search_products_data( $term, $limit ? $limit : 5 );
					return rest_ensure_response( $data );
				},
			)
		);
	}
	add_action( 'rest_api_init', 'qora_register_live_search_rest_route' );
}

if ( ! function_exists( 'qora_ajax_live_product_search' ) ) {
	/**
	 * Handles wc-ajax=qora_live_product_search and admin-ajax fallback requests.
	 *
	 * @return void
	 */
	function qora_ajax_live_product_search() {
		$term  = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$limit = isset( $_REQUEST['limit'] ) ? absint( $_REQUEST['limit'] ) : 5; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$data = qora_get_live_search_products_data( $term, $limit ? $limit : 5 );
		wp_send_json_success( $data );
	}
	add_action( 'wc_ajax_qora_live_product_search', 'qora_ajax_live_product_search' );
	add_action( 'wp_ajax_qora_live_product_search', 'qora_ajax_live_product_search' );
	add_action( 'wp_ajax_nopriv_qora_live_product_search', 'qora_ajax_live_product_search' );
}


