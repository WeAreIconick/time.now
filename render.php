<?php
/**
 * Server-side rendering for the calendar block
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the block renderer class
require_once TIME_NOW_PLUGIN_DIR . 'includes/class-block-renderer.php';

// Use the static render callback
return Time_Now_Renderer::render_callback( $attributes, $content, $block );
