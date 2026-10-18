<?php
/**
 * Dynamic Server-Side Render Template: qora/site-header
 *
 * Fetches dynamic WooCommerce product_cat parent/child hierarchy with hover dropdowns,
 * live cart counter badge stuck to top-right of the cart icon (numeric only),
 * and hides mobile hamburger toggle on desktop.
 *
 * @package woodmart-child
 *
 * @var array    $attributes Block attributes passed from WordPress core block renderer.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance object.
 */

defined( 'ABSPATH' ) || exit;

$attributes = isset( $attributes ) && is_array( $attributes ) ? $attributes : array();

$logo_url            = ! empty( $attributes['logoUrl'] ) ? esc_url( $attributes['logoUrl'] ) : '';
$logo_alt            = ! empty( $attributes['logoAlt'] ) ? sanitize_text_field( $attributes['logoAlt'] ) : get_bloginfo( 'name' );
$search_placeholder  = ! empty( $attributes['searchPlaceholder'] ) ? sanitize_text_field( $attributes['searchPlaceholder'] ) : esc_attr__( 'Search for laptops, accessories, audio, and more...', 'woodmart-child' );
$morocco_notice_text = isset( $attributes['moroccoNoticeText'] ) ? sanitize_text_field( $attributes['moroccoNoticeText'] ) : esc_html__( 'Fast delivery across Morocco', 'woodmart-child' );
$show_deals_red      = ! isset( $attributes['showDealsRed'] ) || ! empty( $attributes['showDealsRed'] );
$show_morocco_notice = ! isset( $attributes['showMoroccoNotice'] ) || ! empty( $attributes['showMoroccoNotice'] );
$show_wishlist       = ! isset( $attributes['showWishlist'] ) || ! empty( $attributes['showWishlist'] );
$show_account        = ! isset( $attributes['showAccount'] ) || ! empty( $attributes['showAccount'] );
$show_cart           = ! isset( $attributes['showCart'] ) || ! empty( $attributes['showCart'] );

// Dynamically fetch parent/child WooCommerce categories from DB
$category_tree = function_exists( 'qora_get_header_category_tree' ) ? qora_get_header_category_tree() : array();

// Ensure cart scripts are available for AJAX reactivity
if ( function_exists( 'wp_enqueue_script' ) && ! is_admin() ) {
	wp_enqueue_script( 'wc-cart-fragments' );
}

// Live Cart contents count (numeric only)
$cart_count = 0;
if ( function_exists( 'WC' ) && is_object( WC()->cart ) && method_exists( WC()->cart, 'get_cart_contents_count' ) ) {
	$cart_count = (int) WC()->cart->get_cart_contents_count();
}

$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$wishlist_url = function_exists( 'woodmart_get_wishlist_page_url' ) ? woodmart_get_wishlist_page_url() : home_url( '/wishlist/' );
$deals_url    = home_url( '/shop/?on_sale=1' );

$tracking_url = home_url( '/my-account/orders/' );
$tracking_slugs = array( 'order-tracking', 'track-order', 'tracking', 'suivi-de-commande', 'suivi-commande' );
foreach ( $tracking_slugs as $slug ) {
	$tracking_page = get_page_by_path( $slug );
	if ( $tracking_page && 'publish' === $tracking_page->post_status ) {
		$tracking_url = get_permalink( $tracking_page->ID );
		break;
	}
}
if ( function_exists( 'wc_get_account_endpoint_url' ) && $tracking_url === home_url( '/my-account/orders/' ) ) {
	$tracking_url = wc_get_account_endpoint_url( 'orders' );
}

// Website Links: About Us, Contact Us, Shop (for mobile drawer)
$about_url   = home_url( '/about-us/' );
$about_slugs = array( 'about-us', 'about', 'a-propos', 'qui-sommes-nous' );
foreach ( $about_slugs as $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		$about_url = get_permalink( $page->ID );
		break;
	}
}

$contact_url   = home_url( '/contact-us/' );
$contact_slugs = array( 'contact-us', 'contact', 'contactez-nous' );
foreach ( $contact_slugs as $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		$contact_url = get_permalink( $page->ID );
		break;
	}
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

$search_query = esc_attr( get_search_query() );

$wrapper_attributes = function_exists( 'get_block_wrapper_attributes' )
	? get_block_wrapper_attributes( array( 'class' => 'qora-site-header-block' ) )
	: 'class="qora-site-header-block"';
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore ?>>
	<header class="qora-site-header" role="banner">

		<!-- =================================================================== -->
		<!-- 1. Header Top Row: Brand Logo, Search Bar, Account/Wishlist/Cart    -->
		<!-- =================================================================== -->
		<div class="qora-site-header__top qora-header-top-row">
			<div class="qora-site-header__top-inner qora-header-container">

				<!-- Mobile Hamburger Toggle (Tablet/Mobile only, Hidden on Desktop) -->
				<button
					type="button"
					class="qora-site-header__mobile-toggle qora-mobile-toggle"
					aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'woodmart-child' ); ?>"
					aria-controls="qora-mobile-drawer"
					aria-expanded="false"
					data-toggle="qora-mobile-drawer"
				>
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<line x1="3" y1="6" x2="21" y2="6"></line>
						<line x1="3" y1="12" x2="21" y2="12"></line>
						<line x1="3" y1="18" x2="21" y2="18"></line>
					</svg>
				</button>

				<!-- Brand Logo (Centered on X for tablet/mobile, left on desktop) -->
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="qora-site-header__logo qora-logo" aria-label="<?php esc_attr_e( 'Qoralink Home', 'woodmart-child' ); ?>">
					<?php if ( ! empty( $logo_url ) ) : ?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $logo_alt ); ?>" class="qora-logo-img" />
					<?php else : ?>
						<span class="qora-logo-wrap">
							<svg class="qora-logo-emblem" width="32" height="32" viewBox="0 0 233 239" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M113.413 0H117.393L123.364 0.56865L129.051 1.70595L134.737 3.4119L141.277 6.25515L145.826 8.81408L150.375 11.9417L154.071 15.0692L156.63 17.3438L158.62 19.6184L161.748 23.3147L165.16 28.4325L168.003 33.5504L171.415 42.0801L179.661 43.7861L186.484 46.0607L192.171 48.6196L197.004 51.4628L200.985 54.3061L204.965 57.718L208.093 60.8456L211.221 64.8261L214.064 69.091L216.338 73.6402L218.044 77.9051L219.466 83.3073L220.035 86.7192L220.319 89.8467V94.6803L219.75 99.7981L218.613 104.916L216.907 109.749L214.348 115.152L211.505 119.417L208.377 123.397L202.975 128.799L198.426 132.211L193.877 135.054L189.328 137.329L185.347 139.035L179.092 141.025L176.533 141.594H174.543L172.268 139.604L151.797 119.132V118.564L150.659 117.995L148.385 115.436L148.953 114.014L151.512 110.034L153.787 105.485L155.209 101.22L156.062 97.8078L156.63 93.8273V85.0132L155.493 78.758L153.787 73.6402L151.512 69.091L149.806 66.2477L147.247 62.8358L144.12 59.4239L140.139 56.012L135.59 53.1688L131.61 51.1785L125.639 49.1882L121.374 48.3353L119.384 48.0509H110.854L105.736 48.9039L100.049 50.6099L94.0787 53.4531L90.6668 55.7277L87.8235 58.0023L84.6959 60.8456L82.137 63.9731L79.5781 67.6694L76.7348 73.0715L74.7445 79.0424L73.8916 83.3073L73.6072 85.5819V92.9743L74.4602 98.6608L75.8818 103.779L78.1564 108.897L80.7154 113.161L83.8429 117.142L88.3922 121.691L93.2257 125.103L97.4906 127.378L101.755 129.084L105.167 130.221L107.442 132.211L144.12 168.889L143.836 169.742L138.433 172.017L133.316 173.723L126.776 175.144L119.384 175.997H110.57L103.461 175.144L96.0689 173.438L90.9511 171.732L85.8332 169.458L80.9997 166.899L76.7348 164.056L73.6072 161.497L70.764 158.938L67.9207 156.094L64.7932 152.114L61.3813 146.712L59.6753 143.3V142.447L53.9888 141.594L47.4493 139.888L41.7628 137.613L37.2136 135.339L32.0958 132.211L28.3996 129.368L24.9876 126.24L21.8601 123.113L18.1638 118.279L14.7519 112.308L12.4773 106.622L11.0557 101.22L10.2027 95.2489V89.2781L11.34 82.17L13.046 76.4835L15.3206 71.3656L17.5952 67.385L20.4384 63.4045L22.9974 60.5612L24.9876 58.2866L28.1152 55.4434L32.3801 52.3158L36.9293 49.4726L41.4785 47.198L48.018 44.639L53.1358 43.2174L58.2537 42.3644L59.1067 41.5115L61.9499 34.4033L65.0775 28.4325L68.4894 23.599L70.764 20.7557L73.3229 17.9125L76.4505 14.7849L80.431 11.6573L84.4116 8.81408L90.0981 5.6865L97.2062 2.84325L103.746 1.1373L109.432 0.284325L113.413 0Z" fill="#87BFD1"/>
								<path d="M111.991 95.8176H117.393L121.374 97.2392L123.933 99.2295L125.923 101.504L127.629 104.916L128.482 106.338L129.051 107.475L130.188 108.044L132.178 110.318L138.149 116.289L140.139 117.995L142.13 120.27L143.835 121.691L145.826 123.966L148.385 126.24L149.522 127.662L150.091 127.946V128.515H150.659L152.081 130.221L153.503 131.643L155.208 133.064L156.63 134.77L158.052 136.192L159.758 137.613L162.317 140.172V140.741L163.17 141.025V141.594H163.738L165.16 143.016L167.719 146.143L169.14 149.271L169.425 150.408V155.242L168.287 158.653L166.013 161.781L162.885 164.056L159.758 165.193L158.336 165.477H155.777L152.365 164.624L150.091 163.487L147.247 160.928L104.03 117.711L102.324 114.867L101.471 112.024V107.191L102.324 103.779L104.314 100.367L106.873 98.0922L109.148 96.6705L111.991 95.8176Z" fill="#87BFD1"/>
							</svg>
							<span class="qora-logo-wordmark">
								<span class="qora-logo-mark">qora</span><span class="qora-logo-text">link</span>
							</span>
						</span>
					<?php endif; ?>
				</a>

				<!-- WooCommerce Product Search Bar -->
				<div class="qora-site-header__search qora-header-search" data-qora-live-search>
					<form role="search" method="get" class="qora-header-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="hidden" name="post_type" value="product" />
						<div class="qora-search-input-wrapper">
							<input
								type="search"
								class="qora-site-header__search-input qora-search-field"
								name="s"
								value="<?php echo $search_query; // phpcs:ignore ?>"
								placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
								autocomplete="off"
								role="combobox"
								aria-autocomplete="list"
								aria-expanded="false"
								aria-haspopup="listbox"
								aria-controls="qora-search-listbox-desktop"
								aria-label="<?php esc_attr_e( 'Search products', 'woodmart-child' ); ?>"
							/>
							<span class="qora-search-spinner" aria-hidden="true"></span>
							<button type="button" class="qora-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'woodmart-child' ); ?>" hidden>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<line x1="18" y1="6" x2="6" y2="18"></line>
									<line x1="6" y1="6" x2="18" y2="18"></line>
								</svg>
							</button>
							<button type="submit" class="qora-site-header__search-btn qora-search-submit" aria-label="<?php esc_attr_e( 'Search', 'woodmart-child' ); ?>">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<circle cx="11" cy="11" r="8"></circle>
									<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
								</svg>
							</button>
						</div>
						<div
							id="qora-search-listbox-desktop"
							class="qora-search-dropdown"
							role="listbox"
							aria-label="<?php esc_attr_e( 'Suggested Products', 'woodmart-child' ); ?>"
							hidden
						></div>
					</form>
				</div>

				<!-- Header Action Tools: Account, Wishlist, Cart -->
				<ul class="qora-site-header__actions qora-header-actions">
					<?php if ( $show_account ) : ?>
						<li class="qora-site-header__action-item qora-site-header__action-item--account">
							<a href="<?php echo esc_url( $account_url ); ?>" class="qora-site-header__action-link qora-header-action qora-account-link" aria-label="<?php esc_attr_e( 'My Account', 'woodmart-child' ); ?>">
								<span class="qora-site-header__action-icon">
									<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
										<circle cx="12" cy="7" r="4"></circle>
									</svg>
								</span>
								<span class="qora-site-header__action-label qora-action-label"><?php esc_html_e( 'Account', 'woodmart-child' ); ?></span>
							</a>
						</li>
					<?php endif; ?>

					<?php if ( $show_wishlist ) : ?>
						<li class="qora-site-header__action-item qora-site-header__action-item--wishlist">
							<a href="<?php echo esc_url( $wishlist_url ); ?>" class="qora-site-header__action-link qora-header-action qora-wishlist-link" aria-label="<?php esc_attr_e( 'Wishlist', 'woodmart-child' ); ?>">
								<span class="qora-site-header__action-icon">
									<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
									</svg>
								</span>
								<span class="qora-site-header__action-label qora-action-label"><?php esc_html_e( 'Wishlist', 'woodmart-child' ); ?></span>
							</a>
						</li>
					<?php endif; ?>

					<?php if ( $show_cart ) : ?>
						<li class="qora-site-header__action-item qora-site-header__action-item--cart">
							<a href="<?php echo esc_url( $cart_url ); ?>" class="qora-site-header__action-link qora-site-header__cart-link qora-header-action qora-cart-link cart-widget-opener" aria-label="<?php esc_attr_e( 'View Cart', 'woodmart-child' ); ?>">
								<span class="qora-site-header__action-icon qora-site-header__cart-icon-wrap qora-cart-icon-wrapper">
									<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<circle cx="9" cy="21" r="1"></circle>
										<circle cx="20" cy="21" r="1"></circle>
										<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
									</svg>
									<span class="qora-cart-badge qora-cart-count"><?php echo esc_html( (int) $cart_count ); ?></span>
								</span>
								<span class="qora-site-header__action-label qora-action-label"><?php esc_html_e( 'Cart', 'woodmart-child' ); ?></span>
							</a>
						</li>
					<?php endif; ?>

					<!-- Mobile/Tablet Only: Single High-Utility Order Tracking Icon Link -->
					<li class="qora-site-header__action-item qora-site-header__action-item--tracking">
						<a href="<?php echo esc_url( $tracking_url ); ?>" class="qora-site-header__action-link qora-header-action qora-tracking-link" aria-label="<?php esc_attr_e( 'Track Order', 'woodmart-child' ); ?>" title="<?php esc_attr_e( 'Track Order', 'woodmart-child' ); ?>">
							<span class="qora-site-header__action-icon qora-tracking-icon-wrap">
								<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<rect x="1" y="3" width="15" height="13"></rect>
									<polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
									<circle cx="5.5" cy="18.5" r="2.5"></circle>
									<circle cx="18.5" cy="18.5" r="2.5"></circle>
								</svg>
							</span>
							<span class="qora-site-header__action-label qora-action-label"><?php esc_html_e( 'Track Order', 'woodmart-child' ); ?></span>
						</a>
					</li>
				</ul>

			</div>
		</div>

		<!-- =================================================================== -->
		<!-- 2. Header Navigation Row: Dynamic Categories + Hover Subcategories  -->
		<!-- =================================================================== -->
		<div class="qora-site-header__nav-row qora-header-nav-row">
			<div class="qora-site-header__nav-inner qora-header-container qora-nav-container">

				<nav class="qora-site-header__nav qora-site-nav" aria-label="<?php esc_attr_e( 'Main Product Categories', 'woodmart-child' ); ?>">
					<ul class="qora-site-header__menu qora-nav-list">
						<?php foreach ( $category_tree as $cat ) :
							$has_children = ! empty( $cat['children'] ) && is_array( $cat['children'] );
							$li_classes   = 'qora-site-header__menu-item qora-nav-item' . ( $has_children ? ' has-children' : '' );
							?>
							<li class="<?php echo esc_attr( $li_classes ); ?>" data-cat-id="<?php echo esc_attr( $cat['id'] ); ?>">
								<a href="<?php echo esc_url( $cat['url'] ); ?>" class="qora-site-header__menu-link qora-nav-link"<?php echo $has_children ? ' aria-haspopup="true" aria-expanded="false"' : ''; ?>>
									<span class="qora-nav-link__text"><?php echo esc_html( $cat['name'] ); ?></span>
									<?php if ( $has_children ) : ?>
										<svg class="qora-nav-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
											<polyline points="6 9 12 15 18 9"></polyline>
										</svg>
									<?php endif; ?>
								</a>

								<?php if ( $has_children ) : ?>
									<ul class="qora-nav-dropdown" aria-label="<?php echo esc_attr( $cat['name'] ); ?>">
										<?php foreach ( $cat['children'] as $child ) :
											$has_subchildren = ! empty( $child['children'] ) && is_array( $child['children'] );
											?>
											<li class="qora-nav-dropdown__item<?php echo $has_subchildren ? ' has-subchildren' : ''; ?>">
												<a href="<?php echo esc_url( $child['url'] ); ?>" class="qora-nav-dropdown__link">
													<span><?php echo esc_html( $child['name'] ); ?></span>
													<?php if ( $has_subchildren ) : ?>
														<svg class="qora-nav-subchevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
															<polyline points="9 18 15 12 9 6"></polyline>
														</svg>
													<?php endif; ?>
												</a>
												<?php if ( $has_subchildren ) : ?>
													<ul class="qora-nav-subdropdown">
														<?php foreach ( $child['children'] as $subchild ) : ?>
															<li class="qora-nav-subdropdown__item">
																<a href="<?php echo esc_url( $subchild['url'] ); ?>" class="qora-nav-subdropdown__link">
																	<span><?php echo esc_html( $subchild['name'] ); ?></span>
																</a>
															</li>
														<?php endforeach; ?>
													</ul>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>

						<?php if ( $show_deals_red ) : ?>
							<li class="qora-site-header__menu-item qora-nav-item qora-site-header__menu-item--deals qora-nav-deals deals">
								<a href="<?php echo esc_url( $deals_url ); ?>" class="qora-site-header__menu-link qora-nav-link qora-menu-link--deals">
									<span class="qora-nav-link__text"><?php esc_html_e( 'Deals', 'woodmart-child' ); ?></span>
								</a>
							</li>
						<?php endif; ?>
					</ul>
				</nav>

				<!-- Morocco Shipping Proposition Notice -->
				<?php if ( $show_morocco_notice && ! empty( $morocco_notice_text ) ) : ?>
					<div class="qora-site-header__notice qora-header-notice">
						<svg class="qora-notice-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<rect x="1" y="3" width="15" height="13"></rect>
							<polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
							<circle cx="5.5" cy="18.5" r="2.5"></circle>
							<circle cx="18.5" cy="18.5" r="2.5"></circle>
						</svg>
						<span class="qora-notice-text"><?php echo esc_html( $morocco_notice_text ); ?></span>
					</div>
				<?php endif; ?>

			</div>
		</div>

		<!-- =================================================================== -->
		<!-- 3. Mobile Off-Canvas Navigation Drawer                              -->
		<!-- =================================================================== -->
		<div id="qora-drawer-backdrop" class="qora-drawer-backdrop" aria-hidden="true"></div>

		<div
			id="qora-mobile-drawer"
			class="qora-mobile-drawer"
			role="dialog"
			aria-modal="true"
			aria-label="<?php esc_attr_e( 'Mobile Navigation', 'woodmart-child' ); ?>"
			tabindex="-1"
		>
			<div class="qora-drawer-content">

				<div class="qora-mobile-drawer__header qora-drawer-header">
					<span class="qora-drawer-title"><?php esc_html_e( 'Categories', 'woodmart-child' ); ?></span>
					<button
						type="button"
						class="qora-mobile-drawer__close qora-drawer-close"
						aria-label="<?php esc_attr_e( 'Close navigation drawer', 'woodmart-child' ); ?>"
						data-close-drawer
					>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>

				<div class="qora-mobile-drawer__search qora-drawer-search" data-qora-live-search>
					<form role="search" method="get" class="qora-mobile-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="hidden" name="post_type" value="product" />
						<div class="qora-search-input-wrapper">
							<input
								type="search"
								class="qora-site-header__search-input qora-mobile-search-field qora-search-field"
								name="s"
								value="<?php echo $search_query; // phpcs:ignore ?>"
								placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
								autocomplete="off"
								role="combobox"
								aria-autocomplete="list"
								aria-expanded="false"
								aria-haspopup="listbox"
								aria-controls="qora-search-listbox-mobile"
								aria-label="<?php esc_attr_e( 'Search products', 'woodmart-child' ); ?>"
							/>
							<span class="qora-search-spinner" aria-hidden="true"></span>
							<button type="button" class="qora-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'woodmart-child' ); ?>" hidden>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<line x1="18" y1="6" x2="6" y2="18"></line>
									<line x1="6" y1="6" x2="18" y2="18"></line>
								</svg>
							</button>
							<button type="submit" class="qora-site-header__search-btn qora-mobile-search-submit qora-search-submit" aria-label="<?php esc_attr_e( 'Search', 'woodmart-child' ); ?>">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<circle cx="11" cy="11" r="8"></circle>
									<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
								</svg>
							</button>
						</div>
						<div
							id="qora-search-listbox-mobile"
							class="qora-search-dropdown qora-search-dropdown--mobile"
							role="listbox"
							aria-label="<?php esc_attr_e( 'Suggested Products', 'woodmart-child' ); ?>"
							hidden
						></div>
					</form>
				</div>

				<nav class="qora-drawer-nav" aria-label="<?php esc_attr_e( 'Mobile Category Menu', 'woodmart-child' ); ?>">
					<ul class="qora-mobile-menu">
						<?php foreach ( $category_tree as $cat ) :
							$has_children = ! empty( $cat['children'] ) && is_array( $cat['children'] );
							?>
							<li class="qora-mobile-menu__item qora-mobile-nav-item<?php echo $has_children ? ' has-children' : ''; ?>">
								<div class="qora-mobile-menu__item-row">
									<a href="<?php echo esc_url( $cat['url'] ); ?>" class="qora-mobile-menu__link qora-mobile-nav-link">
										<span><?php echo esc_html( $cat['name'] ); ?></span>
									</a>
									<?php if ( $has_children ) : ?>
										<button
											type="button"
											class="qora-mobile-accordion-toggle"
											aria-expanded="false"
											aria-label="<?php echo esc_attr( sprintf( __( 'Toggle %s subcategories', 'woodmart-child' ), $cat['name'] ) ); ?>"
										>
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
												<polyline points="6 9 12 15 18 9"></polyline>
											</svg>
										</button>
									<?php endif; ?>
								</div>
								<?php if ( $has_children ) : ?>
									<ul class="qora-mobile-submenu" hidden>
										<?php foreach ( $cat['children'] as $child ) :
											$child_has_children = ! empty( $child['children'] ) && is_array( $child['children'] );
											?>
											<li class="qora-mobile-submenu__item<?php echo $child_has_children ? ' has-subchildren' : ''; ?>">
												<div class="qora-mobile-submenu__item-row">
													<a href="<?php echo esc_url( $child['url'] ); ?>" class="qora-mobile-submenu__link">
														<span><?php echo esc_html( $child['name'] ); ?></span>
													</a>
													<?php if ( $child_has_children ) : ?>
														<button
															type="button"
															class="qora-mobile-accordion-toggle qora-mobile-accordion-toggle--sub"
															aria-expanded="false"
															aria-label="<?php echo esc_attr( sprintf( __( 'Toggle %s subcategories', 'woodmart-child' ), $child['name'] ) ); ?>"
														>
															<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
																<polyline points="6 9 12 15 18 9"></polyline>
															</svg>
														</button>
													<?php endif; ?>
												</div>
												<?php if ( $child_has_children ) : ?>
													<ul class="qora-mobile-subsubmenu" hidden>
														<?php foreach ( $child['children'] as $subchild ) : ?>
															<li class="qora-mobile-subsubmenu__item">
																<a href="<?php echo esc_url( $subchild['url'] ); ?>" class="qora-mobile-submenu__link qora-mobile-submenu__link--deep">
																	<span><?php echo esc_html( $subchild['name'] ); ?></span>
																</a>
															</li>
														<?php endforeach; ?>
													</ul>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>

						<?php if ( $show_deals_red ) : ?>
							<li class="qora-mobile-menu__item qora-mobile-nav-item qora-mobile-menu__item--deals qora-nav-deals deals">
								<a href="<?php echo esc_url( $deals_url ); ?>" class="qora-mobile-menu__link qora-mobile-nav-link qora-menu-link--deals">
									<?php esc_html_e( 'Deals', 'woodmart-child' ); ?>
								</a>
							</li>
						<?php endif; ?>
					</ul>
				</nav>

				<div class="qora-mobile-drawer__actions qora-drawer-actions qora-drawer-site-links">
					<a href="<?php echo esc_url( $about_url ); ?>" class="qora-drawer-site-link">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" y1="16" x2="12" y2="12"></line>
							<line x1="12" y1="8" x2="12.01" y2="8"></line>
						</svg>
						<span><?php esc_html_e( 'About Us', 'woodmart-child' ); ?></span>
					</a>

					<a href="<?php echo esc_url( $contact_url ); ?>" class="qora-drawer-site-link">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
						</svg>
						<span><?php esc_html_e( 'Contact Us', 'woodmart-child' ); ?></span>
					</a>

					<a href="<?php echo esc_url( $shop_url ); ?>" class="qora-drawer-site-link">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
							<line x1="3" y1="6" x2="21" y2="6"></line>
							<path d="M16 10a4 4 0 0 1-8 0"></path>
						</svg>
						<span><?php esc_html_e( 'Shop', 'woodmart-child' ); ?></span>
					</a>
				</div>

				<?php if ( $show_morocco_notice && ! empty( $morocco_notice_text ) ) : ?>
					<div class="qora-mobile-drawer__notice qora-drawer-footer-notice">
						<svg class="qora-notice-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<rect x="1" y="3" width="15" height="13"></rect>
							<polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
							<circle cx="5.5" cy="18.5" r="2.5"></circle>
							<circle cx="18.5" cy="18.5" r="2.5"></circle>
						</svg>
						<span class="qora-notice-text"><?php echo esc_html( $morocco_notice_text ); ?></span>
					</div>
				<?php endif; ?>

			</div>
		</div>

	</header>
</div>
