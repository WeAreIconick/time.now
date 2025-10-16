<?php
/**
 * Block renderer class for server-side rendering
 *
 * @package TimeNow
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block renderer class
 */
class Time_Now_Renderer {

	/**
	 * Render callback for the calendar block
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content Block content.
	 * @param object $block Block object.
	 * @return string Rendered block HTML.
	 */
	public static function render_callback( $attributes, $content, $block ) {
		// Extract and sanitize block attributes with defaults
		$calendar_id = isset( $attributes['calendarId'] ) ? sanitize_text_field( $attributes['calendarId'] ) : '';
		$default_view = isset( $attributes['defaultView'] ) ? sanitize_text_field( $attributes['defaultView'] ) : 'dayGridMonth';
		$show_weekends = isset( $attributes['showWeekends'] ) ? (bool) $attributes['showWeekends'] : true;
		$event_limit = isset( $attributes['eventLimit'] ) ? absint( $attributes['eventLimit'] ) : 3;
		$accent_color = '#3b82f6'; // Fixed accent color

		// Validate default view
		$allowed_views = array( 'dayGridMonth', 'timeGridWeek', 'timeGridDay', 'listWeek' );
		if ( ! in_array( $default_view, $allowed_views, true ) ) {
			$default_view = 'dayGridMonth';
		}

		// Validate event limit
		if ( $event_limit < 1 || $event_limit > 10 ) {
			$event_limit = 3;
		}


		// Generate unique ID for this calendar instance
		$block_id = 'calendar-' . uniqid();

		// Fetch events if calendar ID is provided
		$events = array();
		if ( ! empty( $calendar_id ) ) {
			$api = new Time_Now_Google_Calendar_API();
			$start_date = gmdate( 'Y-m-d', strtotime( '-1 month' ) );
			$end_date = gmdate( 'Y-m-d', strtotime( '+3 months' ) );
			$events = $api->get_events( $calendar_id, $start_date, $end_date );
			
			// Extract the actual Calendar ID for frontend use
			$calendar_id = $api->extract_calendar_id( $calendar_id );
		}

		// Get wrapper attributes
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'calendar-block-wrapper',
				'data-block-id' => $block_id,
			)
		);

		// Start output buffering
		ob_start();

		echo '<div ' . wp_kses_post( $wrapper_attributes ) . '>';

		// Handle empty calendar ID
		if ( empty( $calendar_id ) ) {
			echo '<div class="calendar-placeholder">';
			echo '<p>' . esc_html__( 'Please configure your Google Calendar ID in the block settings.', 'time-now' ) . '</p>';
			echo '</div>';
		} elseif ( isset( $events['error'] ) ) {
			// Handle API errors
			echo '<div class="calendar-error">';
			echo '<p><strong>' . esc_html__( 'Error:', 'time-now' ) . '</strong> ' . esc_html( $events['error'] ) . '</p>';
			echo '</div>';
		} else {
			// Debug: Log events count
		// Debug logging removed for production
			
			// Render calendar container with data attributes for JavaScript to use
			$events_json = wp_json_encode( $events );
			
			
			echo '<div id="' . esc_attr( $block_id ) . '" class="calendar-block-wrapper" ';
			echo 'data-calendar-id="' . esc_attr( $calendar_id ) . '" ';
			echo 'data-events=\'' . esc_attr( $events_json ) . '\' ';
			echo 'data-default-view="' . esc_attr( $default_view ) . '" ';
			echo 'data-accent-color="' . esc_attr( $accent_color ) . '">';
			echo '<div class="calendar-loading">';
			echo '<div class="loading-spinner"></div>';
			echo '<p>Loading calendar...</p>';
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';

		return ob_get_clean();
	}
}
