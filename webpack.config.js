const defaultConfig = require('@wordpress/scripts/config/webpack.config');
module.exports = {
	...defaultConfig,
	entry: {
		'simple-links/simple-links':
			'./src/block-editor/blocks/simple-links',
	
		'filtered-links/filtered-links':
			'./src/block-editor/blocks/filtered-links',
	
	
		'social-links/social-links':
			'./src/block-editor/blocks/social-links',


	},
};
