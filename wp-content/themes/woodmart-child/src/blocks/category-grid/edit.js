import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, RangeControl, Button } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { columns = 4, items = [] } = attributes;
	const blockProps = useBlockProps( { className: 'qora-category-grid-editor' } );

	const updateItem = ( idx, key, value ) => {
		const next = items.map( ( it, i ) => ( i === idx ? { ...it, [ key ]: value } : it ) );
		setAttributes( { items: next } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Grid Layout Settings', 'woodmart-child' ) } initialOpen={ true }>
					<RangeControl
						label={ __( 'Desktop Columns', 'woodmart-child' ) }
						value={ columns }
						onChange={ ( val ) => setAttributes( { columns: val } ) }
						min={ 2 }
						max={ 4 }
					/>
				</PanelBody>
				{ items.map( ( item, idx ) => (
					<PanelBody
						key={ idx }
						title={ `${ __( 'Tile', 'woodmart-child' ) } ${ idx + 1 }: ${ item.customTitle || item.slug }` }
						initialOpen={ false }
					>
						<TextControl
							label={ __( 'WooCommerce Category Slug (from DB)', 'woodmart-child' ) }
							help={ __( 'e.g. laptops, audio, desktops, peripherals, monitors, telephonie', 'woodmart-child' ) }
							value={ item.slug || '' }
							onChange={ ( val ) => updateItem( idx, 'slug', val ) }
						/>
						<TextControl
							label={ __( 'Display Title Override (leave empty to use DB category name)', 'woodmart-child' ) }
							value={ item.customTitle || '' }
							onChange={ ( val ) => updateItem( idx, 'customTitle', val ) }
						/>
						<TextareaControl
							label={ __( 'Subtitle Text', 'woodmart-child' ) }
							value={ item.subtitle || '' }
							onChange={ ( val ) => updateItem( idx, 'subtitle', val ) }
						/>
						<TextControl
							label={ __( 'Image Override URL (leave empty to use DB category/product image)', 'woodmart-child' ) }
							value={ item.customImage || '' }
							onChange={ ( val ) => updateItem( idx, 'customImage', val ) }
						/>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => media?.url && updateItem( idx, 'customImage', media.url ) }
								allowedTypes={ [ 'image' ] }
								render={ ( { open } ) => (
									<Button variant="secondary" onClick={ open }>
										{ __( 'Select Custom Image', 'woodmart-child' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
					</PanelBody>
				) ) }
			</InspectorControls>

			<div
				style={ {
					display: 'grid',
					gridTemplateColumns: `repeat(${ columns }, 1fr)`,
					gap: '16px',
					padding: '12px 0',
				} }
			>
				{ items.map( ( item, idx ) => (
					<div
						key={ idx }
						style={ {
							background: '#F4F6F8',
							borderRadius: '10px',
							padding: '20px',
							minHeight: '150px',
							display: 'flex',
							justifyContent: 'space-between',
							alignItems: 'center',
							border: '1px solid #E6E9EE',
						} }
					>
						<div>
							<strong style={ { display: 'block', fontSize: '16px', color: '#07132B', marginBottom: '6px' } }>
								{ item.customTitle || item.slug }
							</strong>
							<span style={ { display: 'block', fontSize: '13px', color: '#5A6577', whiteSpace: 'pre-line', marginBottom: '12px' } }>
								{ item.subtitle }
							</span>
							<span style={ { color: 'var(--wd-primary-color)', fontWeight: 700 } }>&rarr;</span>
						</div>
						<div style={ { fontSize: '11px', color: 'var(--wd-primary-color)', textAlign: 'right' } }>
							[DB: { item.slug }]
						</div>
					</div>
				) ) }
			</div>
		</div>
	);
}
