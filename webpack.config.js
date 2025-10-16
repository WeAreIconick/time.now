const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		index: './src/index.js',
		view: './src/view.js',
	},
	output: {
		...defaultConfig.output,
		filename: '[name].js',
	},
};
