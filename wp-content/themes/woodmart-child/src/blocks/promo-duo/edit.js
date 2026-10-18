import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { cards = [] } = attributes;
	const blockProps = useBlockProps( { className: 'qora-promo-duo-editor' } );

	const updateCard = ( idx, key, value ) => {
		const next = cards.map( ( c, i ) => ( i === idx ? { ...c, [ key ]: value } : c ) );
		setAttributes( { cards: next } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				{ cards.map( ( card, idx ) => (
					<PanelBody
						key={ idx }
						title={ `${ __( 'Promo Card', 'woodmart-child' ) } ${ idx + 1 }: ${ card.eyebrow || '' }` }
						initialOpen={ idx === 0 }
					>
						<TextControl
							label={ __( 'Eyebrow', 'woodmart-child' ) }
							value={ card.eyebrow || '' }
							onChange={ ( val ) => updateCard( idx, 'eyebrow', val ) }
						/>
						<TextareaControl
							label={ __( 'Heading', 'woodmart-child' ) }
							value={ card.heading || '' }
							onChange={ ( val ) => updateCard( idx, 'heading', val ) }
						/>
						<TextareaControl
							label={ __( 'Subtitle', 'woodmart-child' ) }
							value={ card.subtitle || '' }
							onChange={ ( val ) => updateCard( idx, 'subtitle', val ) }
						/>
						<TextControl
							label={ __( 'CTA Button Text', 'woodmart-child' ) }
							value={ card.ctaText || '' }
							onChange={ ( val ) => updateCard( idx, 'ctaText', val ) }
						/>
						<TextControl
							label={ __( 'CTA Button URL', 'woodmart-child' ) }
							value={ card.ctaUrl || '' }
							onChange={ ( val ) => updateCard( idx, 'ctaUrl', val ) }
						/>
						<TextControl
							label={ __( 'Background Image URL', 'woodmart-child' ) }
							value={ card.bgImage || '' }
							onChange={ ( val ) => updateCard( idx, 'bgImage', val ) }
						/>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => media?.url && updateCard( idx, 'bgImage', media.url ) }
								allowedTypes={ [ 'image' ] }
								render={ ( { open } ) => (
									<Button variant="secondary" onClick={ open }>
										{ __( 'Choose Card Image', 'woodmart-child' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
					</PanelBody>
				) ) }
			</InspectorControls>

			<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '18px' } }>
				{ cards.map( ( card, idx ) => (
					<div
						key={ idx }
						style={ {
							backgroundImage: card.bgImage ? `url(${ card.bgImage })` : 'none',
							backgroundSize: 'cover',
							backgroundPosition: 'center',
							backgroundColor: '#0A162C',
							borderRadius: '14px',
							padding: '32px',
							color: '#fff',
							minHeight: '240px',
						} }
					>
						<span style={ { fontSize: '11px', letterSpacing: '2px', textTransform: 'uppercase', opacity: 0.85 } }>
							{ card.eyebrow }
						</span>
						<h3 style={ { fontSize: '28px', fontWeight: 800, color: '#fff', margin: '8px 0', whiteSpace: 'pre-line' } }>
							{ card.heading }
						</h3>
						<p style={ { fontSize: '14px', opacity: 0.9, whiteSpace: 'pre-line', marginBottom: '18px' } }>
							{ card.subtitle }
						</p>
						<span style={ { display: 'inline-block', background: 'var(--wd-primary-color)', color: '#fff', padding: '10px 20px', borderRadius: '999px', fontWeight: 600 } }>
							{ card.ctaText } &rarr;
						</span>
					</div>
				) ) }
			</div>
		</div>
	);
}
