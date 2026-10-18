import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, ToggleControl, RangeControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const { autoplay = true, interval = 6000, slides = [] } = attributes;
	const [ activeIndex, setActiveIndex ] = useState( 0 );
	const blockProps = useBlockProps( { className: 'qora-hero-slider-editor' } );

	const updateSlide = ( index, key, value ) => {
		const updated = slides.map( ( s, i ) => ( i === index ? { ...s, [ key ]: value } : s ) );
		setAttributes( { slides: updated } );
	};

	const updateFeature = ( slideIndex, featIndex, key, value ) => {
		const updatedSlides = slides.map( ( s, i ) => {
			if ( i !== slideIndex ) return s;
			const feats = ( s.features || [] ).map( ( f, fi ) => ( fi === featIndex ? { ...f, [ key ]: value } : f ) );
			return { ...s, features: feats };
		} );
		setAttributes( { slides: updatedSlides } );
	};

	const currentSlide = slides[ activeIndex ] || slides[ 0 ] || {};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Slider Settings', 'woodmart-child' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Autoplay Slides', 'woodmart-child' ) }
						checked={ !! autoplay }
						onChange={ ( val ) => setAttributes( { autoplay: val } ) }
					/>
					<RangeControl
						label={ __( 'Slide Interval (ms)', 'woodmart-child' ) }
						value={ interval }
						onChange={ ( val ) => setAttributes( { interval: val } ) }
						min={ 2000 }
						max={ 15000 }
						step={ 500 }
					/>
				</PanelBody>
				{ slides.map( ( slide, idx ) => (
					<PanelBody
						key={ idx }
						title={ `${ __( 'Slide', 'woodmart-child' ) } 0${ idx + 1 }: ${ slide.eyebrow || '' }` }
						initialOpen={ idx === activeIndex }
					>
						<TextControl
							label={ __( 'Eyebrow Text', 'woodmart-child' ) }
							value={ slide.eyebrow || '' }
							onChange={ ( val ) => updateSlide( idx, 'eyebrow', val ) }
						/>
						<TextareaControl
							label={ __( 'Primary Heading (White)', 'woodmart-child' ) }
							value={ slide.headingPrimary || '' }
							onChange={ ( val ) => updateSlide( idx, 'headingPrimary', val ) }
						/>
						<TextControl
							label={ __( 'Accent Heading (var(--wd-primary-color))', 'woodmart-child' ) }
							value={ slide.headingAccent || '' }
							onChange={ ( val ) => updateSlide( idx, 'headingAccent', val ) }
						/>
						<TextControl
							label={ __( 'Subtitle', 'woodmart-child' ) }
							value={ slide.subtitle || '' }
							onChange={ ( val ) => updateSlide( idx, 'subtitle', val ) }
						/>
						<TextControl
							label={ __( 'CTA Button Text', 'woodmart-child' ) }
							value={ slide.ctaText || '' }
							onChange={ ( val ) => updateSlide( idx, 'ctaText', val ) }
						/>
						<TextControl
							label={ __( 'CTA Button URL', 'woodmart-child' ) }
							value={ slide.ctaUrl || '' }
							onChange={ ( val ) => updateSlide( idx, 'ctaUrl', val ) }
						/>
						<TextControl
							label={ __( 'Background Image URL', 'woodmart-child' ) }
							value={ slide.bgImage || '' }
							onChange={ ( val ) => updateSlide( idx, 'bgImage', val ) }
						/>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( media ) => media?.url && updateSlide( idx, 'bgImage', media.url ) }
								allowedTypes={ [ 'image' ] }
								render={ ( { open } ) => (
									<Button variant="secondary" onClick={ open } style={ { marginBottom: '12px' } }>
										{ __( 'Choose Background Image', 'woodmart-child' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
						{ ( slide.features || [] ).map( ( feat, fIdx ) => (
							<div key={ fIdx } style={ { borderTop: '1px solid #ddd', paddingTop: '8px', marginTop: '8px' } }>
								<strong>{ `${ __( 'Badge', 'woodmart-child' ) } ${ fIdx + 1 }` }</strong>
								<TextControl
									label={ __( 'Line 1', 'woodmart-child' ) }
									value={ feat.line1 || '' }
									onChange={ ( val ) => updateFeature( idx, fIdx, 'line1', val ) }
								/>
								<TextControl
									label={ __( 'Line 2', 'woodmart-child' ) }
									value={ feat.line2 || '' }
									onChange={ ( val ) => updateFeature( idx, fIdx, 'line2', val ) }
								/>
							</div>
						) ) }
					</PanelBody>
				) ) }
			</InspectorControls>

			<div
				className="qora-hero-slider"
				style={ {
					backgroundImage: currentSlide.bgImage ? `url(${ currentSlide.bgImage })` : 'none',
					backgroundSize: 'cover',
					backgroundPosition: 'center',
					padding: '48px 36px',
					borderRadius: '12px',
					color: '#fff',
					backgroundColor: '#07132B',
					position: 'relative',
				} }
			>
				<div style={ { maxWidth: '560px', position: 'relative', zIndex: 2 } }>
					<h2 style={ { fontSize: '42px', fontWeight: 800, lineHeight: 1.08, color: '#fff', margin: '0 0 4px', whiteSpace: 'pre-line' } }>
						{ currentSlide.headingPrimary }
					</h2>
					<div style={ { fontSize: '42px', fontWeight: 800, lineHeight: 1.12, color: 'var(--wd-primary-color)', margin: '0 0 14px' } }>
						{ currentSlide.headingAccent }
					</div>
					<p style={ { fontSize: '16px', opacity: 0.9, margin: '0 0 24px' } }>{ currentSlide.subtitle }</p>
					<div style={ { display: 'flex', gap: '16px', marginBottom: '26px', flexWrap: 'wrap' } }>
						{ ( currentSlide.features || [] ).map( ( f, i ) => (
							<div key={ i } style={ { fontSize: '13px', lineHeight: 1.3 } }>
								<strong>{ f.line1 }</strong>
								<br />
								<span>{ f.line2 }</span>
							</div>
						) ) }
					</div>
					<span style={ { display: 'inline-block', background: 'var(--wd-primary-color)', color: '#fff', padding: '12px 24px', borderRadius: '999px', fontWeight: 600 } }>
						{ currentSlide.ctaText } &rarr;
					</span>
				</div>
				<div style={ { display: 'flex', gap: '12px', marginTop: '24px' } }>
					{ slides.map( ( _, idx ) => (
						<button
							key={ idx }
							type="button"
							onClick={ () => setActiveIndex( idx ) }
							style={ {
								background: idx === activeIndex ? 'var(--wd-primary-color)' : 'rgba(255,255,255,0.2)',
								color: '#fff',
								border: 'none',
								padding: '6px 12px',
								borderRadius: '4px',
								cursor: 'pointer',
							} }
						>
							{ `0${ idx + 1 }` }
						</button>
					) ) }
				</div>
			</div>
		</div>
	);
}
