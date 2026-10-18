import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { items = [] } = attributes;
	const blockProps = useBlockProps( { className: 'qora-trust-bar-editor' } );

	const updateItem = ( idx, key, value ) => {
		const next = items.map( ( it, i ) => ( i === idx ? { ...it, [ key ]: value } : it ) );
		setAttributes( { items: next } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				{ items.map( ( item, idx ) => (
					<PanelBody
						key={ idx }
						title={ `${ __( 'Trust Badge', 'woodmart-child' ) } ${ idx + 1 }: ${ item.title || '' }` }
						initialOpen={ idx === 0 }
					>
						<SelectControl
							label={ __( 'Icon', 'woodmart-child' ) }
							value={ item.icon || 'truck' }
							options={ [
								{ label: __( 'Delivery Truck', 'woodmart-child' ), value: 'truck' },
								{ label: __( 'Payment Card', 'woodmart-child' ), value: 'card' },
								{ label: __( 'Warranty Shield', 'woodmart-child' ), value: 'shield' },
								{ label: __( 'Support Headset', 'woodmart-child' ), value: 'headset' },
							] }
							onChange={ ( val ) => updateItem( idx, 'icon', val ) }
						/>
						<TextControl
							label={ __( 'Title', 'woodmart-child' ) }
							value={ item.title || '' }
							onChange={ ( val ) => updateItem( idx, 'title', val ) }
						/>
						<TextControl
							label={ __( 'Subtitle', 'woodmart-child' ) }
							value={ item.subtitle || '' }
							onChange={ ( val ) => updateItem( idx, 'subtitle', val ) }
						/>
					</PanelBody>
				) ) }
			</InspectorControls>

			<div
				style={ {
					display: 'grid',
					gridTemplateColumns: 'repeat(4, 1fr)',
					gap: '20px',
					padding: '24px 20px',
					borderTop: '1px solid #EAECEF',
					borderBottom: '1px solid #EAECEF',
					background: '#fff',
				} }
			>
				{ items.map( ( item, idx ) => (
					<div key={ idx } style={ { display: 'flex', alignItems: 'center', gap: '12px' } }>
						<span style={ { color: 'var(--wd-primary-color)', fontWeight: 800, fontSize: '20px' } }>●</span>
						<div>
							<strong style={ { display: 'block', fontSize: '14px', color: '#07132B' } }>
								{ item.title }
							</strong>
							<span style={ { fontSize: '12px', color: '#64748B' } }>{ item.subtitle }</span>
						</div>
					</div>
				) ) }
			</div>
		</div>
	);
}
