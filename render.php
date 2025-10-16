<?php
/**
 * Server-side rendering for the calendar block
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the block renderer class
require_once CALENDAR_BLOCK_PLUGIN_DIR . 'includes/class-block-renderer.php';

// Use the static render callback
return Calendar_Block_Renderer::render_callback( $attributes, $content, $block );
