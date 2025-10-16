<?php
/**
 * Plugin Name: Time.now()
 * Plugin URI: https://github.com/your-username/time-now
 * Description: Beautiful Google Calendar display with multiple views (month, week, day, agenda) using a modern flat design aesthetic.
 * Version: 1.0.0
 * Author: iconick
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: time-now
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 *
 * @package TimeNow
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'TIME_NOW_VERSION', '1.0.0' );
define( 'TIME_NOW_PLUGIN_FILE', __FILE__ );
define( 'TIME_NOW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TIME_NOW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TIME_NOW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 * 
 * Features:
 * - Conditional loading: Only loads Tailwind CSS and JavaScript when calendar block is present
 * - Performance optimized: Caches block detection results
 * - Comprehensive detection: Checks posts, widgets, and template parts
 */
class Time_Now {

	/**
	 * Single instance of the plugin
	 *
	 * @var Time_Now
	 */
	private static $instance = null;

	/**
	 * Get single instance of the plugin
	 *
	 * @return Time_Now
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {
		// Include required classes
		require_once TIME_NOW_PLUGIN_DIR . 'includes/class-google-calendar-api.php';
		require_once TIME_NOW_PLUGIN_DIR . 'includes/class-calendar-cache.php';
		require_once TIME_NOW_PLUGIN_DIR . 'includes/class-block-renderer.php';
		require_once TIME_NOW_PLUGIN_DIR . 'admin/class-settings-page.php';

		// Initialize the settings page
		Time_Now_Settings_Page::get_instance();

		// Register the block
		$this->register_block();
	}

	/**
	 * Register the calendar block
	 */
	private function register_block() {
		register_block_type( TIME_NOW_PLUGIN_DIR . 'block.json', array(
			'render_callback' => array( 'Time_Now_Renderer', 'render_callback' ),
		) );
	}

	/**
	 * Enqueue frontend scripts conditionally
	 */
	public function enqueue_frontend_scripts() {
		// Check if calendar block is present on the page
		$has_block = $this->has_calendar_block();
		
		// Safety net: if we can't determine the page content properly, load scripts anyway
		// This ensures the calendar works even if detection fails
		$should_load_anyway = $this->should_load_scripts_anyway();
		
		$should_load = $has_block || $should_load_anyway;
		
		if ( ! $should_load ) {
			return;
		}

		$asset_file = include TIME_NOW_PLUGIN_DIR . 'build/view.asset.php';
		
		// Force cache busting with timestamp
		$cache_buster = time();
		
		// Enqueue CSS with cache busting
		wp_enqueue_style(
			'time-now-frontend-style',
			TIME_NOW_PLUGIN_URL . 'build/style-index.css?v=' . $cache_buster,
			array(),
			$cache_buster
		);
		
		wp_enqueue_script(
			'time-now-frontend',
			TIME_NOW_PLUGIN_URL . 'build/view.js?v=' . $cache_buster,
			$asset_file['dependencies'],
			$cache_buster,
			true
		);
	}

	/**
	 * Check if calendar block is present on the current page
	 *
	 * @return bool True if calendar block is present
	 */
	private function has_calendar_block() {
		global $post;

		// Check if we're in the admin or if there's no post
		if ( is_admin() || ! $post ) {
			return false;
		}

		// Cache the result for this request to avoid multiple checks
		static $cache = null;
		if ( $cache !== null ) {
			return $cache;
		}

		// Method 1: Use WordPress has_block function (most reliable)
		if ( has_block( 'time-now/google-calendar', $post ) ) {
			$cache = true;
			return true;
		}

		// Method 2: Check post content for block comments
		$content = $post->post_content;
		if ( strpos( $content, '<!-- wp:time-now/google-calendar' ) !== false ) {
			$cache = true;
			return true;
		}

		// Method 3: Check for the wrapper class in raw content
		if ( strpos( $content, 'time-now-calendar-wrapper' ) !== false || strpos( $content, 'calendar-block-wrapper' ) !== false ) {
			$cache = true;
			return true;
		}

		// Method 4: Check for the block name in content
		if ( strpos( $content, 'time-now/google-calendar' ) !== false ) {
			$cache = true;
			return true;
		}

		// Method 5: Check rendered content (this might be expensive, so do it last)
		$rendered_content = apply_filters( 'the_content', $content );
		if ( strpos( $rendered_content, 'time-now-calendar-wrapper' ) !== false || strpos( $rendered_content, 'calendar-block-wrapper' ) !== false ) {
			$cache = true;
			return true;
		}

		// Method 7: Check the actual page output for calendar elements
		// This is the most reliable method - check what's actually being rendered
		ob_start();
		// Capture any output that might contain calendar elements
		$output = ob_get_clean();
		if ( strpos( $output, 'time-now-calendar-wrapper' ) !== false || strpos( $output, 'calendar-block-wrapper' ) !== false ) {
			$cache = true;
			return true;
		}

		// Method 8: Check if the page has been processed and contains calendar elements
		// This checks the final HTML that would be sent to the browser
		if ( is_singular() ) {
			// Force a more thorough check by looking at the post content more carefully
			$blocks = parse_blocks( $content );
			foreach ( $blocks as $block ) {
				if ( isset( $block['blockName'] ) && $block['blockName'] === 'time-now/google-calendar' ) {
					$cache = true;
					return true;
				}
			}
		}

		$cache = false;
		return false;
	}

	/**
	 * Safety net: determine if we should load scripts anyway
	 * This helps when block detection fails but we still need the scripts
	 *
	 * @return bool True if we should load scripts as a safety measure
	 */
	private function should_load_scripts_anyway() {
		// DISABLED: Don't load scripts unless we're absolutely sure there's a calendar block
		// This prevents CSS from loading on pages without the calendar
		return false;
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		// Set default options
		$default_options = array(
			'version' => TIME_NOW_VERSION,
			'activated' => current_time( 'mysql' ),
		);

		add_option( 'time_now_options', $default_options );

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		// Clear calendar cache
		$cache = new Time_Now_Cache();
		$cache->clear_all();

		// Flush rewrite rules
		flush_rewrite_rules();
	}
}

// Initialize the plugin
Time_Now::get_instance();
