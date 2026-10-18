#!/usr/bin/env node
/* Audit Gutenberg blocks in the woodmart-child theme for admin-editability.
   Usage: node audit-blocks.js <path-to-woodmart-child>
   Exit code 1 if any ERROR is found. No dependencies. */
const fs = require( 'fs' );
const path = require( 'path' );

const root = process.argv[ 2 ];
if ( ! root || ! fs.existsSync( path.join( root, 'src', 'blocks' ) ) ) {
	console.error( 'Usage: node audit-blocks.js <path-to-woodmart-child> (needs src/blocks)' );
	process.exit( 2 );
}
const srcDir = path.join( root, 'src', 'blocks' );
const buildDir = path.join( root, 'build', 'blocks' );
const read = ( f ) => ( fs.existsSync( f ) ? fs.readFileSync( f, 'utf8' ) : null );
const mtime = ( f ) => ( fs.existsSync( f ) ? fs.statSync( f ).mtimeMs : 0 );

let errors = 0, warnings = 0;
const out = [];
const err = ( b, m ) => { errors++; out.push( `  ERROR  ${ m }` ); };
const warn = ( b, m ) => { warnings++; out.push( `  warn   ${ m }` ); };

const blocks = fs.readdirSync( srcDir, { withFileTypes: true } ).filter( ( d ) => d.isDirectory() ).map( ( d ) => d.name );

for ( const name of blocks ) {
	out.push( `\n[${ name }]` );
	const before = errors + warnings;
	const dir = path.join( srcDir, name );
	const bj = read( path.join( dir, 'block.json' ) );
	const edit = read( path.join( dir, 'edit.js' ) );
	const render = read( path.join( dir, 'render.php' ) );
	const index = read( path.join( dir, 'index.js' ) );
	let meta = null;
	if ( ! bj ) { err( name, 'block.json missing' ); continue; }
	try { meta = JSON.parse( bj ); } catch ( e ) { err( name, 'block.json is not valid JSON' ); continue; }

	const attrs = Object.keys( meta.attributes || {} );
	if ( ! meta.name ) err( name, 'block.json has no "name"' );
	if ( attrs.length === 0 ) err( name, 'no attributes: nothing is editable' );
	for ( const a of attrs ) {
		if ( ! ( 'default' in meta.attributes[ a ] ) && ! meta.attributes[ a ].source ) warn( name, `attribute "${ a }" has no default` );
	}
	if ( ! meta.example ) warn( name, 'no "example" (inserter preview will be empty)' );
	if ( ! index ) err( name, 'index.js missing' );
	else if ( ! /registerBlockType/.test( index ) ) warn( name, 'index.js has no registerBlockType call' );

	// edit.js controls
	if ( ! edit ) err( name, 'edit.js missing (block has no editor UI)' );
	else {
		if ( ! /useBlockProps/.test( edit ) ) warn( name, 'edit.js does not use useBlockProps' );
		if ( ! /RichText|TextControl|PlainText/.test( edit ) ) warn( name, 'edit.js has no text control (RichText/TextControl)' );
		for ( const a of attrs ) {
			if ( ! new RegExp( `\\b${ a }\\b` ).test( edit ) && ! ( meta.supports || {} )[ a ] ) err( name, `attribute "${ a }" is never used in edit.js (admin cannot change it)` );
		}
		const hasImageAttr = attrs.some( ( a ) => /image|img|photo|bg|background|logo|icon/i.test( a ) );
		if ( hasImageAttr && ! /MediaUpload|MediaPlaceholder|MediaReplaceFlow/.test( edit ) ) err( name, 'image-like attribute but no media picker in edit.js' );
		const arrayAttrs = attrs.filter( ( a ) => meta.attributes[ a ].type === 'array' );
		if ( arrayAttrs.length && ! /filter\(|splice\(|remove/i.test( edit ) ) warn( name, `array attribute(s) ${ arrayAttrs.join( ', ' ) } but no add/remove logic found in edit.js` );
	}

	// render.php
	if ( ! render ) warn( name, 'render.php missing (static save block? then markup lives in post content)' );
	else {
		if ( ! /\$attributes/.test( render ) ) err( name, 'render.php never reads $attributes: output is fully hard-coded' );
		for ( const m of render.matchAll( /\$attributes\[\s*['"]([\w-]+)['"]\s*\]/g ) ) {
			if ( ! attrs.includes( m[ 1 ] ) ) err( name, `render.php uses $attributes['${ m[ 1 ] }'] but block.json does not declare it` );
		}
		for ( const a of attrs ) {
			if ( ! new RegExp( `['"]${ a }['"]|\\$${ a }\\b` ).test( render ) && ! /\$attributes\s*\)|extract\(|foreach\s*\(\s*\$attributes/.test( render ) ) warn( name, `attribute "${ a }" is not used in render.php` );
		}
		const noPhp = render.replace( /<\?php[\s\S]*?\?>/g, '\u0000' ).replace( /<\?=[\s\S]*?\?>/g, '\u0000' );
		const texts = [ ...noPhp.matchAll( />([^<>\u0000]*[A-Za-zÀ-ɏ؀-ۿ]{3,}[^<>\u0000]*)</g ) ]
			.map( ( m ) => m[ 1 ].trim() ).filter( ( t ) => t && ! /^[\s\W\d]+$/.test( t ) );
		if ( texts.length ) err( name, `${ texts.length } hard-coded text(s) in render.php, e.g. "${ texts.slice( 0, 3 ).join( '" | "' ).slice( 0, 120 ) }"` );
		const urls = [ ...noPhp.matchAll( /(?:src|href)=["'](?!\u0000|#?["'])([^"']+)["']/g ) ].map( ( m ) => m[ 1 ] ).filter( ( u ) => u && ! u.includes( '\u0000' ) );
		if ( urls.length ) err( name, `${ urls.length } hard-coded src/href in render.php, e.g. ${ urls.slice( 0, 2 ).join( ', ' ) }` );
		if ( /href=["']#["']/.test( noPhp ) ) warn( name, 'href="#" placeholder link in render.php' );
		if ( ! /esc_html|esc_attr|esc_url|wp_kses/.test( render ) ) warn( name, 'render.php has no escaping functions' );
		if ( /echo\s+\$attributes\[[^\]]+\]\s*;/.test( render ) ) err( name, 'unescaped echo of $attributes in render.php' );
	}

	// build freshness
	const built = path.join( buildDir, name );
	if ( ! fs.existsSync( built ) ) err( name, 'not built: build/blocks/' + name + ' missing (run npm run build)' );
	else {
		const newestSrc = Math.max( ...fs.readdirSync( dir ).map( ( f ) => mtime( path.join( dir, f ) ) ) );
		const oldestBuilt = Math.min( ...fs.readdirSync( built ).map( ( f ) => mtime( path.join( built, f ) ) ) );
		if ( newestSrc > oldestBuilt + 1000 ) warn( name, 'src is newer than build: rebuild' );
		if ( render && ! fs.existsSync( path.join( built, 'render.php' ) ) ) err( name, 'render.php missing from build output' );
	}
	if ( errors + warnings === before ) out.push( '  ok' );
}

// registration + duplicate assets
const reg = read( path.join( root, 'inc', 'blocks-register.php' ) );
if ( ! reg ) { out.push( '\n[project]' ); err( 'project', 'inc/blocks-register.php missing' ); }
else if ( ! /register_block_type/.test( reg ) ) { out.push( '\n[project]' ); err( 'project', 'blocks-register.php has no register_block_type call' ); }
const t1 = read( path.join( root, 'assets', 'css', 'tokens.css' ) ), t2 = read( path.join( root, 'css', 'tokens.css' ) );
if ( t1 && t2 && t1 !== t2 ) { out.push( '\n[project]' ); warn( 'project', 'assets/css/tokens.css and css/tokens.css differ: keep one source of truth' ); }

console.log( out.join( '\n' ) );
console.log( `\n${ blocks.length } blocks audited: ${ errors } error(s), ${ warnings } warning(s)` );
process.exit( errors ? 1 : 0 );
