/**
 * React Editor Interface: qora/site-header
 *
 * Provides a pixel-faithful WYSIWYG preview in the Gutenberg block editor canvas
 * along with granular controls in the WordPress Block Inspector sidebar.
 *
 * @package woodmart-child
 */

import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	Button,
	Card,
	CardBody,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { useState } from '@wordpress/element';

const DEFAULT_NAV_ITEMS = [
	{ title: 'Laptops', url: '/product-category/computers/laptops/', isDeals: false },
	{ title: 'Phone Accessories', url: '/product-category/accessories/', isDeals: false },
	{ title: 'Power & Charging', url: '/product-category/accessories/', isDeals: false },
	{ title: 'Workspace', url: '/product-category/computers/peripherals/', isDeals: false },
	{ title: 'Audio & Creator', url: '/product-category/audio/', isDeals: false },
	{ title: 'Bags & Travel', url: '/product-category/accessories/', isDeals: false },
	{ title: 'Smart Living', url: '/product-category/computers/', isDeals: false },
	{ title: 'Deals', url: '/shop/?on_sale=1', isDeals: true },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		logoUrl,
		logoAlt,
		searchPlaceholder,
		moroccoNoticeText,
		showDealsRed,
		showMoroccoNotice,
		showWishlist,
		showAccount,
		showCart,
		navItems,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'qora-site-header-block qora-header-edit-mode',
	} );

	// Local state for active editing tab or item expansion
	const [ editingIndex, setEditingIndex ] = useState( null );

	const currentNavItems = Array.isArray( navItems ) && navItems.length > 0
		? navItems
		: DEFAULT_NAV_ITEMS;

	const handleNavItemChange = ( index, field, value ) => {
		const updated = [ ...currentNavItems ];
		updated[ index ] = {
			...updated[ index ],
			[ field ]: value,
		};
		setAttributes( { navItems: updated } );
	};

	const handleAddNavItem = () => {
		const updated = [
			...currentNavItems,
			{ title: __( 'New Item', 'woodmart-child' ), url: '#', isDeals: false },
		];
		setAttributes( { navItems: updated } );
		setEditingIndex( updated.length - 1 );
	};

	const handleRemoveNavItem = ( index ) => {
		const updated = currentNavItems.filter( ( _, i ) => i !== index );
		setAttributes( { navItems: updated } );
		if ( editingIndex === index ) {
			setEditingIndex( null );
		}
	};

	const handleResetNavItems = () => {
		setAttributes( { navItems: DEFAULT_NAV_ITEMS } );
		setEditingIndex( null );
	};

	return (
		<>
			{ /* ============================================================= */ }
			{ /* Block Inspector Controls (Sidebar)                           */ }
			{ /* ============================================================= */ }
			<InspectorControls>
				{ /* Notice Panel */ }
				<PanelBody title={ __( 'Header Announcement & Notice', 'woodmart-child' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Display Morocco Delivery Notice', 'woodmart-child' ) }
						checked={ showMoroccoNotice }
						onChange={ ( val ) => setAttributes( { showMoroccoNotice: val } ) }
						help={ __( 'Shows "Fast delivery across Morocco" on the right side of the navigation row.', 'woodmart-child' ) }
					/>
					{ showMoroccoNotice && (
						<TextControl
							label={ __( 'Notice Text', 'woodmart-child' ) }
							value={ moroccoNoticeText }
							onChange={ ( val ) => setAttributes( { moroccoNoticeText: val } ) }
						/>
					) }
					<ToggleControl
						label={ __( 'Highlight Deals Link in Red', 'woodmart-child' ) }
						checked={ showDealsRed }
						onChange={ ( val ) => setAttributes( { showDealsRed: val } ) }
						help={ __( 'Applies brand accent #E53935 to the Deals link.', 'woodmart-child' ) }
					/>
				</PanelBody>

				{ /* Search Bar Settings */ }
				<PanelBody title={ __( 'Search Bar Settings', 'woodmart-child' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Search Input Placeholder', 'woodmart-child' ) }
						value={ searchPlaceholder }
						onChange={ ( val ) => setAttributes( { searchPlaceholder: val } ) }
						help={ __( 'Default: "Search for laptops, accessories, audio, and more..."', 'woodmart-child' ) }
					/>
				</PanelBody>

				{ /* Header Action Icons Visibility */ }
				<PanelBody title={ __( 'Header Action Icons', 'woodmart-child' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show Account Link', 'woodmart-child' ) }
						checked={ showAccount }
						onChange={ ( val ) => setAttributes( { showAccount: val } ) }
					/>
					<ToggleControl
						label={ __( 'Show Wishlist Link', 'woodmart-child' ) }
						checked={ showWishlist }
						onChange={ ( val ) => setAttributes( { showWishlist: val } ) }
					/>
					<ToggleControl
						label={ __( 'Show Cart Link & Live Badge', 'woodmart-child' ) }
						checked={ showCart }
						onChange={ ( val ) => setAttributes( { showCart: val } ) }
					/>
				</PanelBody>

				{ /* Brand Logo Settings */ }
				<PanelBody title={ __( 'Brand Logo Override', 'woodmart-child' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Custom Logo URL (Optional)', 'woodmart-child' ) }
						value={ logoUrl }
						onChange={ ( val ) => setAttributes( { logoUrl: val } ) }
						help={ __( 'Leave blank to use the official Qoralink vector brand logo (uploads/2026/10/logo.svg).', 'woodmart-child' ) }
					/>
					<TextControl
						label={ __( 'Logo Alt Text', 'woodmart-child' ) }
						value={ logoAlt }
						onChange={ ( val ) => setAttributes( { logoAlt: val } ) }
					/>
				</PanelBody>

				{ /* Navigation Items Customizer */ }
				<PanelBody title={ __( 'Navigation Menu Items (8 Items)', 'woodmart-child' ) } initialOpen={ false }>
					<p style={ { fontSize: '13px', color: '#666', marginBottom: '12px' } }>
						{ __( 'Configure the categories and quick links displayed in the secondary navigation bar.', 'woodmart-child' ) }
					</p>

					{ currentNavItems.map( ( item, index ) => (
						<Card key={ index } style={ { marginBottom: '10px', border: '1px solid #E1E8EC' } }>
							<CardBody style={ { padding: '10px' } }>
								<Flex justify="space-between" align="center">
									<FlexItem>
										<strong>{ item.title || __( '(Untitled)', 'woodmart-child' ) }</strong>
										{ item.isDeals && <span style={ { color: '#E53935', marginLeft: '6px', fontSize: '11px' } }>[Deals]</span> }
									</FlexItem>
									<FlexItem>
										<Button
											isSmall
											variant="tertiary"
											onClick={ () => setEditingIndex( editingIndex === index ? null : index ) }
										>
											{ editingIndex === index ? __( 'Close', 'woodmart-child' ) : __( 'Edit', 'woodmart-child' ) }
										</Button>
									</FlexItem>
								</Flex>

								{ editingIndex === index && (
									<div style={ { marginTop: '10px' } }>
										<TextControl
											label={ __( 'Label', 'woodmart-child' ) }
											value={ item.title }
											onChange={ ( val ) => handleNavItemChange( index, 'title', val ) }
										/>
										<TextControl
											label={ __( 'URL', 'woodmart-child' ) }
											value={ item.url }
											onChange={ ( val ) => handleNavItemChange( index, 'url', val ) }
										/>
										<ToggleControl
											label={ __( 'Style as Red Deals Accent', 'woodmart-child' ) }
											checked={ !! item.isDeals }
											onChange={ ( val ) => handleNavItemChange( index, 'isDeals', val ) }
										/>
										<Button
											isDestructive
											isSmall
											variant="secondary"
											onClick={ () => handleRemoveNavItem( index ) }
										>
											{ __( 'Delete Item', 'woodmart-child' ) }
										</Button>
									</div>
								) }
							</CardBody>
						</Card>
					) ) }

					<Flex justify="space-between" style={ { marginTop: '12px' } }>
						<Button variant="secondary" onClick={ handleAddNavItem }>
							{ __( '+ Add Nav Item', 'woodmart-child' ) }
						</Button>
						<Button variant="tertiary" isDestructive onClick={ handleResetNavItems }>
							{ __( 'Reset to Defaults', 'woodmart-child' ) }
						</Button>
					</Flex>
				</PanelBody>
			</InspectorControls>

			{ /* ============================================================= */ }
			{ /* Gutenberg Canvas Visual Preview                               */ }
			{ /* ============================================================= */ }
			<div { ...blockProps }>
				{ /* Top Editor Notice Banner */ }
				<div
					style={ {
						background: '#EBF4F7',
						borderBottom: '1px solid #87BFD1',
						padding: '6px 16px',
						fontSize: '12px',
						color: '#07132B',
						display: 'flex',
						justifyContent: 'space-between',
						alignItems: 'center',
					} }
				>
					<span>
						<strong>{ __( 'Qoralink Global Header Preview', 'woodmart-child' ) }</strong> — { __( 'Live WooCommerce cart fragments, mobile drawer & dynamic queries are active on the frontend.', 'woodmart-child' ) }
					</span>
					<span style={ { fontSize: '11px', color: 'var(--wd-primary-color)', fontWeight: '600' } }>
						{ __( 'Global Header Block', 'woodmart-child' ) }
					</span>
				</div>

				{ /* Header Root Wrapper */ }
				<header className="qora-site-header">
					{ /* TOP ROW */ }
					<div className="qora-header-top-row">
						<div className="qora-header-container">
							{ /* Mobile Hamburger Mockup */ }
							<button type="button" className="qora-mobile-toggle" aria-label="Toggle navigation">
								<span className="qora-hamburger-box" aria-hidden="true">
									<span className="qora-hamburger-inner"></span>
								</span>
							</button>

							{ /* Logo */ }
							<a href="#preview" className="qora-logo" onClick={ ( e ) => e.preventDefault() }>
								{ logoUrl ? (
									<img
										src={ logoUrl }
										alt={ logoAlt || 'Qoralink' }
										style={ { maxHeight: '42px', width: 'auto' } }
									/>
								) : (
									<div style={ { display: 'flex', alignItems: 'center', gap: '8px' } }>
										<svg width="36" height="36" viewBox="0 0 233 239" fill="none">
											<path d="M113.413 0H117.393L123.364 0.56865L129.051 1.70595L134.737 3.4119L141.277 6.25515L145.826 8.81408L150.375 11.9417L154.071 15.0692L156.63 17.3438L158.62 19.6184L161.748 23.3147L165.16 28.4325L168.003 33.5504L171.415 42.0801L179.661 43.7861L186.484 46.0607L192.171 48.6196L197.004 51.4628L200.985 54.3061L204.965 57.718L208.093 60.8456L211.221 64.8261L214.064 69.091L216.338 73.6402L218.044 77.9051L219.466 83.3073L220.035 86.7192L220.319 89.8467V94.6803L219.75 99.7981L218.613 104.916L216.907 109.749L214.348 115.152L211.505 119.417L208.377 123.397L202.975 128.799L198.426 132.211L193.877 135.054L189.328 137.329L185.347 139.035L179.092 141.025L176.533 141.594H174.543L172.268 139.604L151.797 119.132V118.564L150.659 117.995L148.385 115.436L148.953 114.014L151.512 110.034L153.787 105.485L155.209 101.22L156.062 97.8078L156.63 93.8273V85.0132L155.493 78.758L153.787 73.6402L151.512 69.091L149.806 66.2477L147.247 62.8358L144.12 59.4239L140.139 56.012L135.59 53.1688L131.61 51.1785L125.639 49.1882L121.374 48.3353L119.384 48.0509H110.854L105.736 48.9039L100.049 50.6099L94.0787 53.4531L90.6668 55.7277L87.8235 58.0023L84.6959 60.8456L82.137 63.9731L79.5781 67.6694L76.7348 73.0715L74.7445 79.0424L73.8916 83.3073L73.6072 85.5819V92.9743L74.4602 98.6608L75.8818 103.779L78.1564 108.897L80.7154 113.161L83.8429 117.142L88.3922 121.691L93.2257 125.103L97.4906 127.378L101.755 129.084L105.167 130.221L107.442 132.211L144.12 168.889L143.836 169.742L138.433 172.017L133.316 173.723L126.776 175.144L119.384 175.997H110.57L103.461 175.144L96.0689 173.438L90.9511 171.732L85.8332 169.458L80.9997 166.899L76.7348 164.056L73.6072 161.497L70.764 158.938L67.9207 156.094L64.7932 152.114L61.3813 146.712L59.6753 143.3V142.447L53.9888 141.594L47.4493 139.888L41.7628 137.613L37.2136 135.339L32.0958 132.211L28.3996 129.368L24.9876 126.24L21.8601 123.113L18.1638 118.279L14.7519 112.308L12.4773 106.622L11.0557 101.22L10.2027 95.2489V89.2781L11.34 82.17L13.046 76.4835L15.3206 71.3656L17.5952 67.385L20.4384 63.4045L22.9974 60.5612L24.9876 58.2866L28.1152 55.4434L32.3801 52.3158L36.9293 49.4726L41.4785 47.198L48.018 44.639L53.1358 43.2174L58.2537 42.3644L59.1067 41.5115L61.9499 34.4033L65.0775 28.4325L68.4894 23.599L70.764 20.7557L73.3229 17.9125L76.4505 14.7849L80.431 11.6573L84.4116 8.81408L90.0981 5.6865L97.2062 2.84325L103.746 1.1373L109.432 0.284325L113.413 0Z" fill="#87BFD1" />
										</svg>
										<span style={ { fontFamily: 'Space Grotesk, sans-serif', fontSize: '22px', fontWeight: '700', color: '#07132B', letterSpacing: '-0.5px' } }>
											qora<span style={ { color: 'var(--wd-primary-color)' } }>link</span>
										</span>
									</div>
								) }
							</a>

							{ /* Search Form Mockup */ }
							<div className="qora-header-search-form" style={ { flex: '1', maxWidth: '640px', margin: '0 24px' } }>
								<div className="qora-search-input-wrapper">
									<input
										type="search"
										className="qora-search-field"
										placeholder={ searchPlaceholder }
										readOnly
										tabIndex="-1"
									/>
									<button type="button" className="qora-search-submit" tabIndex="-1">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
											<circle cx="11" cy="11" r="8"></circle>
											<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
										</svg>
									</button>
								</div>
							</div>

							{ /* Action Tools */ }
							<div className="qora-header-actions">
								{ showAccount && (
									<a href="#preview" className="qora-header-action qora-account-link" onClick={ ( e ) => e.preventDefault() }>
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
											<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
											<circle cx="12" cy="7" r="4"></circle>
										</svg>
										<span className="qora-action-label">{ __( 'Account', 'woodmart-child' ) }</span>
									</a>
								) }

								{ showWishlist && (
									<a href="#preview" className="qora-header-action qora-wishlist-link" onClick={ ( e ) => e.preventDefault() }>
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
											<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
										</svg>
										<span className="qora-action-label">{ __( 'Wishlist', 'woodmart-child' ) }</span>
									</a>
								) }

								{ showCart && (
									<a href="#preview" className="qora-header-action qora-cart-link" onClick={ ( e ) => e.preventDefault() }>
										<div className="qora-cart-icon-wrapper">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
												<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
												<line x1="3" y1="6" x2="21" y2="6"></line>
												<path d="M16 10a4 4 0 0 1-8 0"></path>
											</svg>
											<span className="qora-cart-badge qora-cart-count wd-cart-number">0</span>
										</div>
										<span className="qora-action-label">{ __( 'Cart', 'woodmart-child' ) }</span>
									</a>
								) }
							</div>
						</div>
					</div>

					{ /* NAVIGATION ROW */ }
					<div className="qora-header-nav-row">
						<div className="qora-header-container qora-nav-container">
							<nav className="qora-site-nav" aria-label={ __( 'Main Navigation Preview', 'woodmart-child' ) }>
								<ul className="qora-nav-list">
									{ currentNavItems.map( ( item, index ) => {
										const isDeals = item.isDeals && showDealsRed;
										return (
											<li
												key={ index }
												className={ `qora-nav-item ${ isDeals ? 'qora-nav-deals deals' : '' }` }
											>
												<a
													href="#preview"
													className="qora-nav-link"
													style={ isDeals ? { color: '#E53935', fontWeight: '600' } : {} }
													onClick={ ( e ) => e.preventDefault() }
												>
													{ item.title }
												</a>
											</li>
										);
									} ) }
								</ul>
							</nav>

							{ showMoroccoNotice && moroccoNoticeText && (
								<div className="qora-header-notice">
									<svg className="qora-notice-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
										<rect x="1" y="3" width="15" height="13"></rect>
										<polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
										<circle cx="5.5" cy="18.5" r="2.5"></circle>
										<circle cx="18.5" cy="18.5" r="2.5"></circle>
									</svg>
									<span className="qora-notice-text">{ moroccoNoticeText }</span>
								</div>
							) }
						</div>
					</div>
				</header>
			</div>
		</>
	);
}
