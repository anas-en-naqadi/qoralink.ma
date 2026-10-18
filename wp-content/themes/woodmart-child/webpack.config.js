/**
 * Custom Webpack Configuration for WoodMart Child Theme Multi-Block Architecture
 * Compiles: src/blocks/<slug>/ -> build/blocks/<slug>/
 */

const path = require('path');
const fs = require('fs');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
let getWebpackEntryPoints = null;
try {
	const configUtils = require('@wordpress/scripts/utils/config');
	getWebpackEntryPoints = configUtils.getWebpackEntryPoints;
} catch (e) {
	getWebpackEntryPoints = null;
}
const CopyWebpackPlugin = require('copy-webpack-plugin');

// 1. Collect standard entry points from @wordpress/scripts
let autoEntries = {};
try {
	if (typeof getWebpackEntryPoints === 'function') {
		autoEntries = getWebpackEntryPoints();
	}
} catch (error) {
	autoEntries = {};
}

// 2. Scan src/blocks for complete multi-block entry coverage
const blocksDir = path.resolve(__dirname, 'src/blocks');
const scannedEntries = {};

if (fs.existsSync(blocksDir)) {
	const blockFolders = fs.readdirSync(blocksDir, { withFileTypes: true });
	blockFolders.forEach((dirent) => {
		if (dirent.isDirectory()) {
			const blockSlug = dirent.name;
			const blockPath = path.resolve(blocksDir, blockSlug);

			// Editor script entry (src/blocks/<slug>/index.js -> build/blocks/<slug>/index.js)
			const indexPath = path.resolve(blockPath, 'index.js');
			if (fs.existsSync(indexPath)) {
				scannedEntries[`blocks/${blockSlug}/index`] = indexPath;
			}

			// Frontend interactive script entry (src/blocks/<slug>/view.js -> build/blocks/<slug>/view.js)
			const viewPath = path.resolve(blockPath, 'view.js');
			if (fs.existsSync(viewPath)) {
				scannedEntries[`blocks/${blockSlug}/view`] = viewPath;
			}

			// Component-scoped stylesheet (src/blocks/<slug>/style.scss -> build/blocks/<slug>/style-index.css)
			const stylePath = path.resolve(blockPath, 'style.scss');
			if (fs.existsSync(stylePath)) {
				scannedEntries[`blocks/${blockSlug}/style-index`] = stylePath;
			}

			// Editor-only stylesheet (src/blocks/<slug>/editor.scss -> build/blocks/<slug>/editor.css)
			const editorStylePath = path.resolve(blockPath, 'editor.scss');
			if (fs.existsSync(editorStylePath)) {
				scannedEntries[`blocks/${blockSlug}/editor`] = editorStylePath;
			}
		}
	});
}

// Merge entries (scannedEntries take precedence to guarantee naming contract)
const finalEntries = {
	...autoEntries,
	...scannedEntries,
};

// Resilient fallback: ensure entry is never empty (avoids Webpack crash before blocks are created)
if (Object.keys(finalEntries).length === 0) {
	const tokensScss = path.resolve(__dirname, 'src/styles/_tokens.scss');
	if (fs.existsSync(tokensScss)) {
		finalEntries['tokens-init'] = tokensScss;
	}
}

// Ensure all entry keys use forward slashes (Windows path normalization)
const normalizedEntries = {};
Object.keys(finalEntries).forEach((key) => {
	const normalizedKey = key.replace(/\\/g, '/');
	normalizedEntries[normalizedKey] = finalEntries[key];
});

module.exports = {
	...defaultConfig,
	entry: normalizedEntries,
	output: {
		...defaultConfig.output,
		path: path.resolve(__dirname, 'build'),
	},
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...((defaultConfig.resolve && defaultConfig.resolve.alias) || {}),
			'@styles': path.resolve(__dirname, 'src/styles'),
		},
	},
	plugins: [
		...defaultConfig.plugins,
		// Guarantee copying of block.json and render.php into build/blocks/<slug>/
		new CopyWebpackPlugin({
			patterns: [
				{
					from: 'blocks/*/{block.json,render.php}',
					context: path.resolve(__dirname, 'src'),
					to: path.resolve(__dirname, 'build'),
					noErrorOnMissing: true,
				},
			],
		}),
	],
};
