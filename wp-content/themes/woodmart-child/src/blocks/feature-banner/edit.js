import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, heading, subtitle, ctaText, ctaUrl, bgImage, checklist = [] } = attributes;
	const blockProps = useBlockProps( { className: 'qora-feature-banner-editor' } );

	const updateCheckItem = ( idx, label ) => {
		const next = checklist.map( ( c, i ) => ( i === idx ? { ...c, label } : c ) );
		setAttributes( { checklist: next } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Banner Content', 'woodmart-child' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Eyebrow', 'woodmart-child' ) }
						value={ eyebrow || '' }
						onChange={ ( val ) => setAttributes( { eyebrow: val } ) }
					/>
					<TextareaControl
						label={ __( 'Heading', 'woodmart-child' ) }
						value={ heading || '' }
						onChange={ ( val ) => setAttributes( { heading: val } ) }
					/>
					<TextareaControl
						label={ __( 'Subtitle', 'woodmart-child' ) }
						value={ subtitle || '' }
						onChange={ ( val ) => setAttributes( { subtitle: val } ) }
					/>
					<TextControl
						label={ __( 'CTA Button Text', 'woodmart-child' ) }
						value={ ctaText || '' }
						onChange={ ( val ) => setAttributes( { ctaText: val } ) }
					/>
					<TextControl
						label={ __( 'CTA Button URL', 'woodmart-child' ) }
						value={ ctaUrl || '' }
						onChange={ ( val ) => setAttributes( { ctaUrl: val } ) }
					/>
					<TextControl
						label={ __( 'Background Image URL', 'woodmart-child' ) }
						value={ bgImage || '' }
						onChange={ ( val ) => setAttributes( { bgImage: val } ) }
					/>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => media?.url && setAttributes( { bgImage: media.url } ) }
							allowedTypes={ [ 'image' ] }
							render={ ( { open } ) => (
								<Button variant="secondary" onClick={ open }>
									{ __( 'Choose Banner Image', 'woodmart-child' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>
				<PanelBody title={ __( 'Checklist Items (4 Items)', 'woodmart-child' ) } initialOpen={ false }>
					{ checklist.map( ( item, idx ) => (
						<TextControl
							key={ idx }
							label={ `${ __( 'Item', 'woodmart-child' ) } ${ idx + 1 }` }
							value={ item.label || '' }
							onChange={ ( val ) => updateCheckItem( idx, val ) }
						/>
					) ) }
				</PanelBody>
			</InspectorControls>

			<div
				style={ {
					backgroundImage: bgImage ? `url(${ bgImage })` : 'none',
					backgroundSize: 'cover',
					backgroundPosition: 'center',
					backgroundColor: '#EAECEF',
					borderRadius: '14px',
					padding: '36px',
					display: 'flex',
					justifyContent: 'space-between',
					alignItems: 'center',
					gap: '24px',
				} }
			>
				<div style={ { maxWidth: '360px', background: 'rgba(255,255,255,0.85)', padding: '20px', borderRadius: '10px' } }>
					<span style={ { fontSize: '11px', letterSpacing: '2px', textTransform: 'uppercase', color: '#5A6577' } }>
						{ eyebrow }
					</span>
					<h3 style={ { fontSize: '30px', fontWeight: 800, color: '#07132B', margin: '6px 0 10px', whiteSpace: 'pre-line' } }>
						{ heading }
					</h3>
					<p style={ { fontSize: '14px', color: '#4A5568', whiteSpace: 'pre-line', marginBottom: '16px' } }>
						{ subtitle }
					</p>
					<span style={ { display: 'inline-block', background: 'var(--wd-primary-color)', color: '#fff', padding: '10px 20px', borderRadius: '999px', fontWeight: 600 } }>
						{ ctaText } &rarr;
					</span>
				</div>
				<div style={ { background: 'rgba(255,255,255,0.9)', padding: '20px 26px', borderRadius: '12px', minWidth: '210px' } }>
					{ checklist.map( ( c, i ) => (
						<div key={ i } style={ { padding: '6px 0', color: '#07132B', fontWeight: 600, fontSize: '14px' } }>
							✓ { c.label }
						</div>
					) ) }
				</div>
			</div>
		</div>
	);
}
