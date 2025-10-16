<?php
/**
 * Google Calendar API integration class
 *
 * @package TimeNow
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Calendar API wrapper class
 */
class Time_Now_Google_Calendar_API {

	/**
	 * Google Calendar API key
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Cache instance
	 *
	 * @var Time_Now_Cache
	 */
	private $cache;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->api_key = get_option( 'time_now_api_key', '' );
		$this->cache = new Time_Now_Cache();
	}

	/**
	 * Extract Calendar ID from Google Calendar share URL
	 *
	 * @param string $input Calendar ID or share URL.
	 * @return string Extracted Calendar ID.
	 */
	public function extract_calendar_id( $input ) {
		if ( empty( $input ) ) {
			return '';
		}

		// If it's already a Calendar ID (contains @), return as is
		if ( strpos( $input, '@' ) !== false ) {
			return $input;
		}

		// Handle Google Calendar share URLs
		if ( strpos( $input, 'calendar.google.com' ) !== false ) {
			// Extract cid parameter from URL
			$parsed_url = wp_parse_url( $input );
			if ( isset( $parsed_url['query'] ) ) {
				parse_str( $parsed_url['query'], $query_params );
				if ( isset( $query_params['cid'] ) ) {
					// Decode base64 encoded Calendar ID
					$decoded = base64_decode( $query_params['cid'] );
					if ( $decoded !== false ) {
						return $decoded;
					}
				}
			}
		}

		// Handle direct base64 encoded Calendar ID
		if ( preg_match( '/^[A-Za-z0-9+\/]+=*$/', $input ) && strlen( $input ) > 20 ) {
			$decoded = base64_decode( $input );
			if ( $decoded !== false && strpos( $decoded, '@' ) !== false ) {
				return $decoded;
			}
		}

		// Return original input if no extraction method worked
		return $input;
	}

	/**
	 * Fetch events from Google Calendar
	 *
	 * @param string $calendar_id Calendar ID.
	 * @param string $start_date Start date (Y-m-d format).
	 * @param string $end_date End date (Y-m-d format).
	 * @return array|WP_Error Events array or error.
	 */
	public function get_events( $calendar_id, $start_date, $end_date ) {
		if ( empty( $this->api_key ) ) {
			return array( 'error' => __( 'Google Calendar API key is not configured.', 'time-now' ) );
		}

		if ( empty( $calendar_id ) ) {
			return array( 'error' => __( 'Calendar ID is required.', 'time-now' ) );
		}

		// Extract Calendar ID from share URL if needed
		$calendar_id = $this->extract_calendar_id( $calendar_id );

		// Check cache first
		$cache_key = 'gcal_events_' . md5( $calendar_id . $start_date . $end_date );
		$cached_events = $this->cache->get( $cache_key );
		
		if ( false !== $cached_events ) {
			return $cached_events;
		}

		// Build API request URL - properly encode Calendar ID
		$encoded_calendar_id = rawurlencode( $calendar_id );
		$url = 'https://www.googleapis.com/calendar/v3/calendars/' . $encoded_calendar_id . '/events';

		$params = array(
			'key' => $this->api_key,
			'timeMin' => $this->format_rfc3339( $start_date ),
			'timeMax' => $this->format_rfc3339( $end_date ),
			'singleEvents' => 'true', // Try with singleEvents=true but with different approach
			'orderBy' => 'startTime',
			'maxResults' => 2500,
		);

		$request_url = add_query_arg( $params, $url );

		// Debug: Log API request details
		// Debug logging removed for production

		// Make API request
		$response = wp_remote_get(
			$request_url,
			array(
				'timeout' => 15,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'error' => $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		
		if ( 200 !== $response_code ) {
			$data = json_decode( $response_body, true );
			$error_message = isset( $data['error']['message'] ) ? $data['error']['message'] : __( 'Unknown API error', 'time-now' );
			// translators: %1$d is the HTTP response code, %2$s is the error message
			return array( 'error' => sprintf( __( 'API Error %1$d: %2$s', 'time-now' ), $response_code, $error_message ) );
		}

		$data = json_decode( $response_body, true );
		
		// Debug: Add visible console output for JSON decode

		// Debug: Log basic API response info
		// Debug logging removed for production
		
		// Debug: Add visible console output for API response
		
		// Debug: Check first few events for recurringEventId and recurrence fields
		for ( $i = 0; $i < min( 5, count( $data['items'] ) ); $i++ ) {
			$event = $data['items'][ $i ];
			$has_recurring_event_id = isset( $event['recurringEventId'] ) ? 'YES' : 'NO';
			$has_recurrence = isset( $event['recurrence'] ) ? 'YES' : 'NO';
			$summary = $event['summary'] ?? 'EMPTY';
			$recurrence_rules = isset( $event['recurrence'] ) ? implode( ', ', $event['recurrence'] ) : 'NONE';
		}

		if ( ! isset( $data['items'] ) ) {
			return array( 'error' => __( 'Invalid API response format', 'time-now' ) );
		}

		// Transform events to FullCalendar format
		$events = $this->transform_events( $data['items'], $calendar_id, $start_date, $end_date );

		// Cache for 30 minutes
		$this->cache->set( $cache_key, $events, 1800 );

		return $events;
	}

	/**
	 * Transform Google Calendar events to FullCalendar format
	 *
	 * @param array $google_events Google Calendar events.
	 * @return array Transformed events.
	 */
	private function transform_events( $google_events, $calendar_id, $start_date = null, $end_date = null ) {
		if ( empty( $google_events ) ) {
			return array();
		}

		$events = array();
		
		// NEW APPROACH: Try to fetch master events by base IDs
		// Since we have individual instances, let's try to get the master events by their base IDs
		$base_ids = array();
		$standalone_events = array();
		
		foreach ( $google_events as $event ) {
			$event_id = $event['id'];
			
			// Check if this is a recurring event instance by looking for timestamp pattern in ID
			$is_recurring_instance = preg_match( '/^(.+)_\d{8}T\d{6}Z$/', $event_id, $matches );
			
			if ( $is_recurring_instance ) {
				// This is a recurring instance - extract the base ID
				$base_id = $matches[1];
				if ( ! in_array( $base_id, $base_ids ) ) {
					$base_ids[] = $base_id;
				}
			} else {
				// This is a standalone event
				$standalone_events[] = $event;
			}
		}
		
		
		// Process standalone events first
		foreach ( $standalone_events as $event ) {
			$events[] = $this->transform_single_event( $event );
		}
		
		// Try to fetch master events by their base IDs
		$master_titles = array();
		foreach ( $base_ids as $base_id ) {
			
			$master_event = $this->get_single_event( $calendar_id, $base_id );
			if ( $master_event && ! empty( $master_event['summary'] ) ) {
				$master_titles[ $base_id ] = $master_event['summary'];
			} else {
			}
		}
		
		// Process recurring instances with master titles
		foreach ( $google_events as $event ) {
			$event_id = $event['id'];
			$is_recurring_instance = preg_match( '/^(.+)_\d{8}T\d{6}Z$/', $event_id, $matches );
			
			if ( $is_recurring_instance ) {
				$base_id = $matches[1];
				// Use master title if available
				if ( isset( $master_titles[ $base_id ] ) ) {
					$event['summary'] = $master_titles[ $base_id ];
				}
			}
			
			$events[] = $this->transform_single_event( $event );
		}

		return $events;
	}

	/**
	 * Get a single event by its ID
	 *
	 * @param string $calendar_id The calendar ID
	 * @param string $event_id The event ID
	 * @return array|null Event data or null if not found
	 */
	private function get_single_event( $calendar_id, $event_id ) {
		// Build the single event endpoint URL
		$encoded_calendar_id = rawurlencode( $calendar_id );
		$encoded_event_id = rawurlencode( $event_id );
		$url = "https://www.googleapis.com/calendar/v3/calendars/{$encoded_calendar_id}/events/{$encoded_event_id}";

		$params = array(
			'key' => $this->api_key,
		);

		$request_url = add_query_arg( $params, $url );
		

		$response = wp_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			$error_message = wp_remote_retrieve_response_message( $response );
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( $data ) {
			return $data;
		}

		return null;
	}

	/**
	 * Get all instances of a recurring event using events.instances endpoint
	 *
	 * @param string $calendar_id The calendar ID
	 * @param string $event_id The master event ID
	 * @param string $start_date Start date for the range
	 * @param string $end_date End date for the range
	 * @return array Array of event instances
	 */
	private function get_recurring_event_instances( $calendar_id, $event_id, $start_date, $end_date ) {
		// Build the events.instances endpoint URL
		$encoded_calendar_id = rawurlencode( $calendar_id );
		$encoded_event_id = rawurlencode( $event_id );
		$url = "https://www.googleapis.com/calendar/v3/calendars/{$encoded_calendar_id}/events/{$encoded_event_id}/instances";

		$params = array(
			'key' => $this->api_key,
			'timeMin' => $this->format_rfc3339( $start_date ),
			'timeMax' => $this->format_rfc3339( $end_date ),
			'maxResults' => 2500,
		);

		$request_url = add_query_arg( $params, $url );
		

		$response = wp_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			$error_message = wp_remote_retrieve_response_message( $response );
			return array();
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( isset( $data['items'] ) ) {
			return $data['items'];
		}

		return array();
	}

	/**
	 * Transform a single Google Calendar event to our format
	 *
	 * @param array $event Google Calendar event data
	 * @return array Transformed event data
	 */
	private function transform_single_event( $event ) {
		// Skip events without start time
		if ( ! isset( $event['start'] ) ) {
			return null;
		}

		$start = $event['start']['dateTime'] ?? $event['start']['date'];
		$end = $event['end']['dateTime'] ?? $event['end']['date'];
		$all_day = isset( $event['start']['date'] );

		// Try multiple possible title sources
		$title = 'No Title';
		
		if ( ! empty( $event['summary'] ) ) {
			$title = $event['summary'];
		} elseif ( ! empty( $event['title'] ) ) {
			$title = $event['title'];
		} elseif ( ! empty( $event['description'] ) ) {
			$title = substr( $event['description'], 0, 50 ) . ( strlen( $event['description'] ) > 50 ? '...' : '' );
		} else {
			// Generate a more descriptive default title
			$start_time = new DateTime( $start );
			$event_date = $start_time->format( 'M j' );
			$event_time = $start_time->format( 'g:i A' );
			$title = "Event on {$event_date} at {$event_time}";
		}

		return array(
			'id' => $event['id'],
			'title' => $title,
			'start' => $start,
			'end' => $end,
			'allDay' => $all_day,
			'description' => $event['description'] ?? '',
			'location' => $event['location'] ?? '',
			'url' => $event['htmlLink'] ?? '',
			'backgroundColor' => $event['backgroundColor'] ?? '#3b82f6',
			'borderColor' => $event['backgroundColor'] ?? '#3b82f6',
			'originalSummary' => $event['summary'] ?? '',
			'originalTitle' => $event['title'] ?? '',
		);
	}

	/**
	 * Format date for RFC3339 format required by Google Calendar API
	 *
	 * @param string $date Date in Y-m-d format.
	 * @return string RFC3339 formatted date.
	 */
	private function format_rfc3339( $date ) {
		return gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $date ) );
	}

	/**
	 * Test API connection
	 *
	 * @param string $calendar_id Calendar ID to test.
	 * @return array|WP_Error Test result.
	 */
	public function test_connection( $calendar_id ) {
		$today = gmdate( 'Y-m-d' );
		$tomorrow = gmdate( 'Y-m-d', strtotime( '+1 day' ) );

		return $this->get_events( $calendar_id, $today, $tomorrow );
	}
}
