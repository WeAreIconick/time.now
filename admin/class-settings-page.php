<?php
/**
 * Admin settings page class
 *
 * @package CalendarBlock
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings page class
 */
class Calendar_Block_Settings_Page {

	/**
	 * Single instance of the class
	 *
	 * @var Calendar_Block_Settings_Page
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class
	 *
	 * @return Calendar_Block_Settings_Page
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
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add settings page to WordPress admin menu
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Google Calendar Block Settings', 'calendar-block' ),
			__( 'Google Calendar', 'calendar-block' ),
			'manage_options',
			'calendar-block-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		register_setting(
			'calendar_block_settings',
			'calendar_block_api_key',
			array(
				'type' => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default' => '',
			)
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_calendar-block-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'calendar-block-admin',
			CALENDAR_BLOCK_PLUGIN_URL . 'admin/settings.css',
			array(),
			CALENDAR_BLOCK_VERSION
		);
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'calendar-block' ) );
		}

		// Handle form submissions
		$this->handle_form_submissions();

		// Get current settings
		$api_key = get_option( 'calendar_block_api_key', '' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Google Calendar Block Settings', 'calendar-block' ); ?></h1>

			<form method="post" action="">
				<?php wp_nonce_field( 'calendar_block_settings_save' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="calendar_block_api_key"><?php esc_html_e( 'Google API Key', 'calendar-block' ); ?></label>
						</th>
						<td>
							<input type="text" 
								   id="calendar_block_api_key" 
								   name="calendar_block_api_key" 
								   value="<?php echo esc_attr( $api_key ); ?>" 
								   class="regular-text"
								   required
							/>
							<p class="description">
								<?php
								printf(
									/* translators: %s: Google Cloud Console URL */
									esc_html__( 'Required for public calendars. Get from %s', 'calendar-block' ),
									'<a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>'
								);
								?>
							</p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" 
						   name="calendar_block_save_settings" 
						   class="button button-primary" 
						   value="<?php esc_attr_e( 'Save Settings', 'calendar-block' ); ?>"
					/>
				</p>
			</form>

			<hr>

			<div class="calendar-block-cache-section">
				<h3><?php esc_html_e( 'Cache Management', 'calendar-block' ); ?></h3>
				<p><?php esc_html_e( 'Calendar events are cached for 30 minutes to improve performance.', 'calendar-block' ); ?></p>
				<form method="post" action="">
					<?php wp_nonce_field( 'calendar_block_clear_cache' ); ?>
					<input type="submit" 
						   name="calendar_block_clear_cache" 
						   class="button" 
						   value="<?php esc_attr_e( 'Clear Cache', 'calendar-block' ); ?>"
						   onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to clear the calendar cache?', 'calendar-block' ); ?>')"
					/>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle form submissions
	 */
	private function handle_form_submissions() {
		if ( isset( $_POST['calendar_block_save_settings'] ) ) {
			check_admin_referer( 'calendar_block_settings_save' );

			$api_key = sanitize_text_field( wp_unslash( $_POST['calendar_block_api_key'] ?? '' ) );
			update_option( 'calendar_block_api_key', $api_key );

			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved successfully!', 'calendar-block' ) . '</p></div>';
		}

		if ( isset( $_POST['calendar_block_clear_cache'] ) ) {
			check_admin_referer( 'calendar_block_clear_cache' );

			$cache = new Calendar_Block_Cache();
			$deleted = $cache->clear_all();

			echo '<div class="notice notice-success"><p>' . 
				sprintf( 
					/* translators: %d: Number of cached items deleted */
					esc_html__( 'Cache cleared successfully! %d cached items deleted.', 'calendar-block' ), 
					esc_html( $deleted ) 
				) . 
				'</p></div>';
		}
	}
}
