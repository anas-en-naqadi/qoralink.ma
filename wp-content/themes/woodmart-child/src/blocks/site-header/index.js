/**
 * Block Entry Point: qora/site-header
 *
 * @package woodmart-child
 */

import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';

registerBlockType( metadata.name, {
	...metadata,
	icon: (
		<svg
			width="24"
			height="24"
			viewBox="0 0 24 24"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
			aria-hidden="true"
		>
			<rect x="2" y="4" width="20" height="16" rx="2" stroke="#07132B" strokeWidth="1.5" />
			<line x1="2" y1="10" x2="22" y2="10" stroke="#07132B" strokeWidth="1.5" />
			<rect x="5" y="6" width="3" height="2" rx="0.5" fill="var(--wd-primary-color)" />
			<rect x="10" y="6" width="6" height="2" rx="0.5" fill="#E1E8EC" />
			<circle cx="18" cy="7" r="1.2" fill="var(--wd-primary-color)" />
			<line x1="5" y1="13" x2="8" y2="13" stroke="#07132B" strokeWidth="1.5" strokeLinecap="round" />
			<line x1="10" y1="13" x2="13" y2="13" stroke="#07132B" strokeWidth="1.5" strokeLinecap="round" />
			<line x1="15" y1="13" x2="19" y2="13" stroke="#E53935" strokeWidth="1.5" strokeLinecap="round" />
		</svg>
	),
	edit: Edit,
	// Dynamic server-side block renders via render.php; save must return null.
	save: () => null,
} );
