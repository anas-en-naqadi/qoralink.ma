<?php
/**
 * Data Query Helpers & Template Utilities
 *
 * Provides query builders, category image resolution, 12-slot catalog padding,
 * and card rendering helpers for Qoralink blocks.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

// -----------------------------------------------------------------------------
// 1. Category Image Resolution (Requirement R4)
// -----------------------------------------------------------------------------

if ( ! function_exists( 'qora_get_category_image_url' ) ) {
	/**
	 * Resolves category image URL using a 4-tier fallback cascade:
	 * 1. Category thumbnail_id in term meta
	 * 2. Featured image of the first published product in that category
	 * 3. Custom inspector upload / attribute override
	 * 4. WooCommerce placeholder or generic SVG fallback
	 *
	 * @param WP_Term|int $term             Term object or Term ID.
	 * @param string      $custom_image_url Optional custom URL from block attributes.
	 * @param string      $size             Image size (default: 'medium_large').
	 * @return string Sanitized image URL.
	 */
	function qora_get_category_image_url( $term, $custom_image_url = '', $size = 'medium_large' ) {
		// Tier 3: Custom inspector override has priority if explicitly provided
		if ( ! empty( $custom_image_url ) ) {
			return esc_url( $custom_image_url );
		}

		$term_id = is_a( $term, 'WP_Term' ) ? (int) $term->term_id : ( ( is_object( $term ) && isset( $term->term_id ) ) ? (int) $term->term_id : ( is_numeric( $term ) ? (int) $term : 0 ) );
		if ( ! $term_id ) {
			return function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( $size ) : '';
		}

		// Tier 1: Category thumbnail_id from wp_termmeta
		$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );
		if ( ! empty( $thumbnail_id ) ) {
			$image_url = wp_get_attachment_image_url( (int) $thumbnail_id, $size );
			if ( $image_url ) {
				return esc_url( $image_url );
			}
		}

		// Tier 2: First published product in this category
		$product_posts = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
			'fields'         => 'ids',
		) );

		if ( ! empty( $product_posts ) ) {
			$product_thumb_id = get_post_thumbnail_id( $product_posts[0] );
			if ( $product_thumb_id ) {
				$image_url = wp_get_attachment_image_url( $product_thumb_id, $size );
				if ( $image_url ) {
					return esc_url( $image_url );
				}
			}
		}

		// Tier 4: WooCommerce placeholder
		if ( function_exists( 'wc_placeholder_img_src' ) ) {
			return esc_url( wc_placeholder_img_src( $size ) );
		}

		return '';
	}
}

if ( ! function_exists( 'qora_get_header_category_tree' ) ) {
	/**
	 * Dynamically fetches WooCommerce product_cat categories from the database
	 * and builds a parent -> children hierarchy for the global site header.
	 *
	 * @return array Array of category nodes with 'id', 'name', 'slug', 'url', 'children'.
	 */
	function qora_get_header_category_tree() {
		$raw_terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'orderby'    => 'menu_order',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $raw_terms ) || empty( $raw_terms ) || ! is_array( $raw_terms ) ) {
			return array();
		}

		$terms_by_id  = array();
		$children_map = array();

		foreach ( $raw_terms as $term ) {
			if ( 'uncategorized' === $term->slug || 'non-classe' === $term->slug ) {
				continue;
			}
			$terms_by_id[ (int) $term->term_id ] = $term;
			$parent_id                           = (int) $term->parent;
			if ( ! isset( $children_map[ $parent_id ] ) ) {
				$children_map[ $parent_id ] = array();
			}
			$children_map[ $parent_id ][] = $term;
		}

		$build_node = null;
		$build_node = function( $term ) use ( &$build_node, &$children_map ) {
			$term_id   = (int) $term->term_id;
			$term_link = get_term_link( $term );
			if ( is_wp_error( $term_link ) ) {
				$term_link = home_url( '/shop/' );
			}

			$kids = array();
			if ( ! empty( $children_map[ $term_id ] ) ) {
				foreach ( $children_map[ $term_id ] as $child_term ) {
					$kids[] = $build_node( $child_term );
				}
			}

			return array(
				'id'       => $term_id,
				'name'     => wp_specialchars_decode( $term->name, ENT_QUOTES ),
				'slug'     => $term->slug,
				'url'      => $term_link,
				'count'    => (int) $term->count,
				'parent'   => (int) $term->parent,
				'children' => $kids,
			);
		};

		$top_terms = isset( $children_map[0] ) ? $children_map[0] : array();
		$nav_tree  = array();
		$added_ids = array();

		foreach ( $top_terms as $root_term ) {
			$node                     = $build_node( $root_term );
			$nav_tree[]               = $node;
			$added_ids[ $node['id'] ] = true;

			// Surface any child category that is ITSELF a parent (has children of its own, e.g. Laptops, Desktops)
			if ( ! empty( $children_map[ (int) $root_term->term_id ] ) ) {
				foreach ( $children_map[ (int) $root_term->term_id ] as $lvl1_term ) {
					$lvl1_id = (int) $lvl1_term->term_id;
					if ( ! empty( $children_map[ $lvl1_id ] ) && ! isset( $added_ids[ $lvl1_id ] ) ) {
						$nav_tree[]            = $build_node( $lvl1_term );
						$added_ids[ $lvl1_id ] = true;
					}
				}
			}
		}

		return $nav_tree;
	}
}


// -----------------------------------------------------------------------------
// 2. Product Showcase Query & 12-Slot Padding Engine (Requirement R6)
// -----------------------------------------------------------------------------

if ( ! function_exists( 'qora_get_showcase_products' ) ) {
	/**
	 * Queries products based on querySource and guarantees an exact 12-item array.
	 *
	 * Solves the critical edge case where the database contains fewer than 12 products
	 * (e.g. 11 catalog products) by padding or cycling available products so the 2x6
	 * grid never breaks layout.
	 *
	 * @param string $query_source   Source type: 'best_sellers', 'new_arrivals', 'on_sale', or 'category'.
	 * @param string $category_slug  Category slug if query_source is 'category'.
	 * @param int    $target_count   Target number of product slots (default: 12).
	 * @return WC_Product[] Array of WC_Product instances (length guaranteed = $target_count if catalog has >=1 product).
	 */
	function qora_get_showcase_products( $query_source = 'new_arrivals', $category_slug = '', $target_count = 12 ) {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$query_args = array(
			'status'     => 'publish',
			'limit'      => $target_count,
			'visibility' => 'catalog',
		);

		switch ( $query_source ) {
			case 'best_sellers':
				$query_args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$query_args['orderby']  = 'meta_value_num';
				$query_args['order']    = 'DESC';
				break;

			case 'on_sale':
				$on_sale_ids = function_exists( 'wc_get_product_ids_on_sale' ) ? wc_get_product_ids_on_sale() : array();
				if ( ! empty( $on_sale_ids ) ) {
					$query_args['include'] = $on_sale_ids;
				} else {
					$query_args['include'] = array( 0 );
				}
				break;

			case 'category':
				if ( ! empty( $category_slug ) ) {
					$query_args['category'] = array( sanitize_title( $category_slug ) );
				}
				break;

			case 'new_arrivals':
			default:
				$query_args['orderby'] = 'date';
				$query_args['order']   = 'DESC';
				break;
		}

		$products = wc_get_products( $query_args );

		// Step A: If fewer than target_count, query additional published products excluding retrieved IDs
		if ( count( $products ) < $target_count ) {
			$existing_ids = array();
			foreach ( $products as $prod ) {
				$existing_ids[] = $prod->get_id();
			}

			$needed = $target_count - count( $products );
			$extra_products = wc_get_products( array(
				'status'     => 'publish',
				'limit'      => $needed,
				'exclude'    => $existing_ids,
				'visibility' => 'catalog',
				'orderby'    => 'date',
				'order'      => 'DESC',
			) );

			if ( ! empty( $extra_products ) ) {
				$products = array_merge( $products, $extra_products );
			}
		}

		// Step B: If catalog total is still less than target_count (e.g. 11 products in entire store),
		// cycle through available products to guarantee exactly 12 items render in the 2x6 grid
		if ( ! empty( $products ) && count( $products ) < $target_count ) {
			$base_products = $products;
			$index = 0;
			$total_base = count( $base_products );
			while ( count( $products ) < $target_count ) {
				$products[] = $base_products[ $index % $total_base ];
				$index++;
			}
		}

		return array_slice( $products, 0, $target_count );
	}
}

// -----------------------------------------------------------------------------
// 3. Product Specification Extractor (Requirement R6)
// -----------------------------------------------------------------------------

if ( ! function_exists( 'qora_get_product_specs_line' ) ) {
	/**
	 * Extracts a concise single-line spec string for a product card.
	 *
	 * Checks:
	 * 1. Product short description (trimmed to 7 words)
	 * 2. Available attributes (e.g., RAM, Storage, CPU)
	 * 3. Primary category name
	 * 4. Default guarantee string
	 *
	 * @param WC_Product $product WooCommerce product object.
	 * @return string Sanitized specs line.
	 */
	function qora_get_product_specs_line( $product ) {
		if ( ! is_object( $product ) ) {
			return esc_html__( 'Refurbished &bull; Official Warranty', 'woodmart-child' );
		}

		// 1. Short description
		$short_desc = wp_strip_all_tags( $product->get_short_description() );
		if ( ! empty( $short_desc ) ) {
			return wp_trim_words( $short_desc, 14, '...' );
		}

		// 2. Attributes (concatenated)
		$attributes = $product->get_attributes();
		if ( ! empty( $attributes ) ) {
			$attr_parts = array();
			foreach ( $attributes as $attribute ) {
				if ( is_object( $attribute ) && method_exists( $attribute, 'get_options' ) ) {
					$options = $attribute->get_options();
					if ( ! empty( $options ) ) {
						if ( method_exists( $attribute, 'is_taxonomy' ) && $attribute->is_taxonomy() ) {
							$term = get_term( (int) $options[0] );
							if ( $term && ! is_wp_error( $term ) ) {
								$attr_parts[] = $term->name;
							}
						} elseif ( is_string( $options[0] ) ) {
							$attr_parts[] = $options[0];
						} elseif ( is_numeric( $options[0] ) ) {
							$term = get_term( (int) $options[0] );
							if ( $term && ! is_wp_error( $term ) ) {
								$attr_parts[] = $term->name;
							}
						}
					}
				}
			}
			if ( ! empty( $attr_parts ) ) {
				return implode( ' &bull; ', array_slice( $attr_parts, 0, 3 ) );
			}
		}

		// 3. Category Fallback
		$cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
			return sprintf( esc_html__( 'Category: %s', 'woodmart-child' ), esc_html( $cats[0] ) );
		}

		return esc_html__( 'Refurbished &bull; Official Warranty', 'woodmart-child' );
	}
}

// -----------------------------------------------------------------------------
// 4. Product Second Image Resolver & Card Renderer (Requirement R6)
// -----------------------------------------------------------------------------

if ( ! function_exists( 'qora_get_product_second_image_url' ) ) {
	/**
	 * Resolves the second (hover) image URL for a WooCommerce product.
	 *
	 * Priority:
	 * 1. First image in WooCommerce Product Gallery ($product->get_gallery_image_ids())
	 * 2. Secondary image attachment uploaded to the product post
	 * 3. Related category product image fallback (ensures consistent hover preview)
	 *
	 * @param WC_Product $product WooCommerce product instance.
	 * @param string     $size    Image size (default: 'woocommerce_thumbnail').
	 * @return string Sanitized second image URL or empty string.
	 */
	function qora_get_product_second_image_url( $product, $size = 'woocommerce_thumbnail' ) {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
			return '';
		}

		$product_id = (int) $product->get_id();
		$primary_id = (int) $product->get_image_id();

		// 1. Official WooCommerce product gallery (first gallery image = 2nd product image)
		if ( method_exists( $product, 'get_gallery_image_ids' ) ) {
			$gallery_ids = $product->get_gallery_image_ids();
			if ( ! empty( $gallery_ids ) && is_array( $gallery_ids ) ) {
				foreach ( $gallery_ids as $gal_id ) {
					$gal_id = (int) $gal_id;
					if ( $gal_id > 0 && $gal_id !== $primary_id ) {
						$url = wp_get_attachment_image_url( $gal_id, $size );
						if ( $url ) {
							return esc_url( $url );
						}
					}
				}
			}
		}

		// 2. Attached child images on this product post
		$child_attachments = get_posts(
			array(
				'post_parent'    => $product_id,
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 2,
				'exclude'        => $primary_id ? array( $primary_id ) : array(),
				'fields'         => 'ids',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);

		if ( ! empty( $child_attachments ) ) {
			$url = wp_get_attachment_image_url( (int) $child_attachments[0], $size );
			if ( $url ) {
				return esc_url( $url );
			}
		}

		// 3. Fallback: another product's thumbnail in the same category or store
		$cat_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		$sibling_args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'post__not_in'   => array( $product_id ),
			'fields'         => 'ids',
		);
		if ( ! empty( $cat_ids ) && ! is_wp_error( $cat_ids ) ) {
			$sibling_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $cat_ids,
				),
			);
		}
		$siblings = get_posts( $sibling_args );
		if ( empty( $siblings ) ) {
			unset( $sibling_args['tax_query'] );
			$siblings = get_posts( $sibling_args );
		}
		if ( ! empty( $siblings ) ) {
			foreach ( $siblings as $sib_id ) {
				$sib_thumb = (int) get_post_thumbnail_id( $sib_id );
				if ( $sib_thumb && $sib_thumb !== $primary_id ) {
					$url = wp_get_attachment_image_url( $sib_thumb, $size );
					if ( $url ) {
						return esc_url( $url );
					}
				}
			}
		}

		return '';
	}
}

if ( ! function_exists( 'qora_render_product_card' ) ) {
	/**
	 * Renders the unified Qoralink Product Card (used in both the 12-product home showcase
	 * and across all site-wide WooCommerce product loops).
	 *
	 * Includes:
	 * - Primary image + hover second image with tiny zoom
	 * - Brand prefix, product title, concise specs line
	 * - Price & cyan AJAX add-to-cart icon button
	 *
	 * @param WC_Product $product WooCommerce product instance.
	 * @param int        $index   Card loop index.
	 * @param string     $view    Layout view mode ('grid' or 'list').
	 * @return void
	 */
	function qora_render_product_card( $product, $index = 0, $view = 'grid' ) {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
			return;
		}

		$product_id    = $product->get_id();
		$permalink     = $product->get_permalink();
		$title         = $product->get_name();
		$price_html    = $product->get_price_html();
		$specs_line    = function_exists( 'qora_get_product_specs_line' ) ? qora_get_product_specs_line( $product ) : '';
		$purchasable   = $product->is_purchasable() && $product->is_in_stock();
		$img_url       = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
		if ( ! $img_url && function_exists( 'wc_placeholder_img_src' ) ) {
			$img_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
		}
		$hover_img_url     = function_exists( 'qora_get_product_second_image_url' ) ? qora_get_product_second_image_url( $product, 'woocommerce_thumbnail' ) : '';
		$wishlist_url      = function_exists( 'woodmart_get_wishlist_page_url' ) ? woodmart_get_wishlist_page_url() : home_url( '/wishlist/' );
		$wishlist_nonce    = wp_create_nonce( 'woodmart-wishlist-add' );
		$is_wishlist_added = false;

		if ( class_exists( 'XTS\WC_Wishlist\Wishlist' ) ) {
			$wl = new \XTS\WC_Wishlist\Wishlist();
			$all_wl = $wl->get_all();
			if ( ! empty( $all_wl ) && is_array( $all_wl ) ) {
				foreach ( $all_wl as $wl_item ) {
					if ( isset( $wl_item['product_id'] ) && (int) $wl_item['product_id'] === (int) $product_id ) {
						$is_wishlist_added = true;
						break;
					}
				}
			}
		}

		if ( function_exists( 'woodmart_enqueue_js_script' ) ) {
			woodmart_enqueue_js_script( 'wishlist' );
		}

		$card_classes = 'qora-showcase-card qora-loop-product-card';
		if ( 'list' === $view ) {
			$card_classes .= ' qora-showcase-card--list';
		}
		?>
		<article class="<?php echo esc_attr( $card_classes ); ?>" data-slot="<?php echo esc_attr( $index ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>">
			<div class="qora-showcase-card__wishlist-btn wd-wishlist-btn wd-action-btn wd-style-icon<?php echo $is_wishlist_added ? ' added' : ''; ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<a
					href="<?php echo esc_url( $wishlist_url ); ?>"
					class="<?php echo $is_wishlist_added ? 'added' : ''; ?>"
					data-key="<?php echo esc_attr( $wishlist_nonce ); ?>"
					data-product-id="<?php echo esc_attr( $product_id ); ?>"
					rel="nofollow"
					aria-label="<?php echo $is_wishlist_added ? esc_attr__( 'Remove from wishlist', 'woodmart-child' ) : esc_attr__( 'Add to wishlist', 'woodmart-child' ); ?>"
					title="<?php echo $is_wishlist_added ? esc_attr__( 'Remove from wishlist', 'woodmart-child' ) : esc_attr__( 'Add to wishlist', 'woodmart-child' ); ?>"
				>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $is_wishlist_added ? '#E53935' : 'none'; ?>" stroke="<?php echo $is_wishlist_added ? '#E53935' : 'currentColor'; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
					</svg>
					<span class="wd-action-text" style="display:none !important;"><?php echo $is_wishlist_added ? esc_html__( 'Remove from wishlist', 'woodmart-child' ) : esc_html__( 'Add to wishlist', 'woodmart-child' ); ?></span>
				</a>
			</div>

			<a href="<?php echo esc_url( $permalink ); ?>" class="qora-showcase-card__media<?php echo $hover_img_url ? ' has-hover-image' : ''; ?>" tabindex="-1">
				<?php if ( $img_url ) : ?>
					<img class="qora-showcase-card__img-primary" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
				<?php endif; ?>
				<?php if ( $hover_img_url ) : ?>
					<img class="qora-showcase-card__img-secondary" src="<?php echo esc_url( $hover_img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
				<?php endif; ?>
			</a>

			<div class="qora-showcase-card__body">
				<h3 class="qora-showcase-card__title">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
				</h3>
				<?php if ( $specs_line ) : ?>
					<p class="qora-showcase-card__specs"><?php echo wp_kses_post( $specs_line ); ?></p>
				<?php endif; ?>

				<div class="qora-showcase-card__footer">
					<span class="qora-showcase-card__price">
						<?php echo $price_html ? $price_html : esc_html__( '3,490 MAD', 'woodmart-child' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>

					<?php if ( $purchasable && $product->is_type( 'simple' ) ) : ?>
						<a
							href="<?php echo esc_url( '?add-to-cart=' . $product_id ); ?>"
							data-quantity="1"
							class="button product_type_simple add_to_cart_button ajax_add_to_cart qora-showcase-card__cart-btn"
							data-product_id="<?php echo esc_attr( $product_id ); ?>"
							data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Add "%s" to your cart', 'woodmart-child' ), $title ) ); ?>"
							rel="nofollow"
						>
							<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
								<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
							</svg>
						</a>
					<?php else : ?>
						<a
							href="<?php echo esc_url( $permalink ); ?>"
							class="qora-showcase-card__cart-btn"
							aria-label="<?php echo esc_attr( sprintf( __( 'View "%s"', 'woodmart-child' ), $title ) ); ?>"
						>
							<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
								<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
							</svg>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</article>
		<?php
	}
}

// -----------------------------------------------------------------------------
// 5. Safe SVG Sanitizer Utility
// -----------------------------------------------------------------------------

if ( ! function_exists( 'qora_sanitize_svg' ) ) {
	/**
	 * Sanitizes inline SVG markup using wp_kses with allowed SVG attributes.
	 *
	 * @param string $svg_html Raw SVG string.
	 * @return string Sanitized SVG markup.
	 */
	function qora_sanitize_svg( $svg_html ) {
		$allowed = array(
			'svg'      => array(
				'class'       => true,
				'aria-hidden' => true,
				'aria-label'  => true,
				'role'        => true,
				'viewbox'     => true,
				'width'       => true,
				'height'      => true,
				'fill'        => true,
				'stroke'      => true,
				'stroke-width'=> true,
				'xmlns'       => true,
			),
			'g'        => array(
				'class'           => true,
				'id'              => true,
				'transform'       => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'opacity'         => true,
				'clip-path'       => true,
			),
			'path'     => array(
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			),
			'circle'   => array(
				'cx'     => true,
				'cy'     => true,
				'r'      => true,
				'fill'   => true,
				'stroke' => true,
			),
			'rect'     => array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
				'ry'     => true,
				'fill'   => true,
				'stroke' => true,
			),
			'line'     => array(
				'x1'     => true,
				'y1'     => true,
				'x2'     => true,
				'y2'     => true,
				'stroke' => true,
			),
			'polyline' => array(
				'points' => true,
				'fill'   => true,
				'stroke' => true,
			),
			'polygon'  => array(
				'points' => true,
				'fill'   => true,
				'stroke' => true,
			),
		);

		return wp_kses( $svg_html, $allowed );
	}
}
