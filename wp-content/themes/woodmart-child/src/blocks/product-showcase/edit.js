import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, RangeControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const {
		brandPrefix = '',
		heading = 'SELECTED',
		subtitle = 'Curated technology for work, study and everyday life.',
		viewAllText = 'View all products',
		viewAllUrl = '/shop/',
		querySource = 'new_arrivals',
		categorySlug = '',
		productCount = 12,
		columns = 6,
	} = attributes;

	const blockProps = useBlockProps( { className: 'qora-product-showcase-editor' } );

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Product Query Customizer', 'woodmart-child' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Products Source', 'woodmart-child' ) }
						value={ querySource }
						options={ [
							{ label: __( 'New Arrivals (Latest)', 'woodmart-child' ), value: 'new_arrivals' },
							{ label: __( 'Best Sellers (High Sellers)', 'woodmart-child' ), value: 'best_sellers' },
							{ label: __( 'Deals (On Sale)', 'woodmart-child' ), value: 'on_sale' },
							{ label: __( 'Specific Category', 'woodmart-child' ), value: 'category' },
						] }
						onChange={ ( val ) => setAttributes( { querySource: val } ) }
					/>
					{ querySource === 'category' && (
						<TextControl
							label={ __( 'Category Slug', 'woodmart-child' ) }
							help={ __( 'e.g. laptops, audio, desktops, peripherals', 'woodmart-child' ) }
							value={ categorySlug }
							onChange={ ( val ) => setAttributes( { categorySlug: val } ) }
						/>
					) }
					<RangeControl
						label={ __( 'Total Products', 'woodmart-child' ) }
						value={ productCount }
						onChange={ ( val ) => setAttributes( { productCount: val } ) }
						min={ 6 }
						max={ 18 }
						step={ 6 }
					/>
					<RangeControl
						label={ __( 'Columns per Row (Desktop)', 'woodmart-child' ) }
						value={ columns }
						onChange={ ( val ) => setAttributes( { columns: val } ) }
						min={ 3 }
						max={ 6 }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Section Header', 'woodmart-child' ) } initialOpen={ false }>
					<TextControl
						label={ __( 'Brand Prefix (var(--wd-primary-color))', 'woodmart-child' ) }
						value={ brandPrefix }
						onChange={ ( val ) => setAttributes( { brandPrefix: val } ) }
					/>
					<TextControl
						label={ __( 'Main Heading', 'woodmart-child' ) }
						value={ heading }
						onChange={ ( val ) => setAttributes( { heading: val } ) }
					/>
					<TextControl
						label={ __( 'Subtitle', 'woodmart-child' ) }
						value={ subtitle }
						onChange={ ( val ) => setAttributes( { subtitle: val } ) }
					/>
					<TextControl
						label={ __( 'View All Link Text', 'woodmart-child' ) }
						value={ viewAllText }
						onChange={ ( val ) => setAttributes( { viewAllText: val } ) }
					/>
					<TextControl
						label={ __( 'View All Link URL', 'woodmart-child' ) }
						value={ viewAllUrl }
						onChange={ ( val ) => setAttributes( { viewAllUrl: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div style={ { padding: '20px 0' } }>
				<div style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: '20px' } }>
					<div style={ { display: 'flex', flexDirection: 'column', gap: '6px' } }>
						<div style={ { fontSize: '28px', fontWeight: 800, color: '#07132B' } }>
							{ heading }
						</div>
						<div style={ { fontSize: '14px', color: '#5A6577' } }>{ subtitle }</div>
					</div>
					<span style={ { fontSize: '13px', fontWeight: 600, color: 'var(--wd-primary-color)' } }>
						{ viewAllText } &rarr;
					</span>
				</div>
				<div
					style={ {
						display: 'grid',
						gridTemplateColumns: `repeat(${ columns }, 1fr)`,
						gap: '14px',
					} }
				>
					{ Array.from( { length: productCount } ).map( ( _, idx ) => (
						<div
							key={ idx }
							style={ {
								border: '1px solid #EAECEF',
								borderRadius: '10px',
								padding: '14px',
								background: '#fff',
							} }
						>
							<div style={ { height: '100px', background: '#F4F6F8', borderRadius: '6px', marginBottom: '10px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '11px', color: '#8A94A6' } }>
								{ `${ querySource } #${ idx + 1 }` }
							</div>
							<span style={ { color: 'var(--wd-primary-color)', fontWeight: 700, fontSize: '12px' } }>Q/</span>
							<div style={ { fontWeight: 700, fontSize: '13px', color: '#07132B', margin: '4px 0' } }>
								{ __( 'Dynamic Product', 'woodmart-child' ) } #{ idx + 1 }
							</div>
							<div style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '8px' } }>
								<strong style={ { color: 'var(--wd-primary-color)', fontSize: '13px' } }>MAD</strong>
								<span style={ { background: 'var(--wd-primary-color)', color: '#fff', padding: '4px 8px', borderRadius: '5px', fontSize: '11px' } }>+</span>
							</div>
						</div>
					) ) }
				</div>
			</div>
		</div>
	);
}
