/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';


// import { dispatch } from '@wordpress/data';

import { store as editPostStore } from '@wordpress/edit-post';
// import { store as editPostStore } from '@wordpress/preferences';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
// import './style.scss';

/**
 * Internal dependencies
 */
import json from './block.json';
import edit from './edit';
import save from './save';

const {name, ...settings} = json;

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType(name, {
	...settings,

	/**
	 * This is the display title for your block, which can be translated with `i18n` functions.
	 * The block inserter will show this name.
	 */
	title: __( 'Social Icons', 'ft-network-sourcelinks' ),

	/**
	 * This is a short description for your block, can be translated with `i18n` functions.
	 * It will be shown in the Block Tab in the Settings Sidebar.
	 */
	description: __(
		'Shows a better version of the core social-links block - prefilled with the URLs you entered as separate Links',
		'ft-network-sourcelinks'
	),

	example:{},
	/**
	 * @see ./edit.js
	 */
	edit,

	/**
	 * @see ./save.js
	 */
	save,
});

// hide the core block from the inserter
// kinda house-keeping
// https://github.com/WordPress/gutenberg/issues/14139#issuecomment-480261866
wp.data.dispatch( editPostStore ).hideBlockTypes( [ 'core/social-links' ] ); 
