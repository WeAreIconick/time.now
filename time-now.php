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
		// Always enqueue the script for now to ensure it loads
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
