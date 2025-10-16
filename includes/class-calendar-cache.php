<?php
/**
 * Calendar cache management class
 *
 * @package CalendarBlock
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calendar cache wrapper class
 */
class Calendar_Block_Cache {

	/**
	 * Cache prefix for all transient keys
	 *
	 * @var string
	 */
	private $prefix = 'gcal_cache_';

	/**
	 * Get cached value
	 *
	 * @param string $key Cache key.
	 * @return mixed Cached value or false if not found.
	 */
	public function get( $key ) {
		return get_transient( $this->prefix . $key );
	}

	/**
	 * Set cached value
	 *
	 * @param string $key Cache key.
	 * @param mixed  $value Value to cache.
	 * @param int    $expiration Expiration time in seconds.
	 * @return bool True on success, false on failure.
	 */
	public function set( $key, $value, $expiration = 1800 ) {
		return set_transient( $this->prefix . $key, $value, $expiration );
	}

	/**
	 * Delete cached value
	 *
	 * @param string $key Cache key.
	 * @return bool True on success, false on failure.
	 */
	public function delete( $key ) {
		return delete_transient( $this->prefix . $key );
	}

	/**
	 * Clear all calendar cache
	 *
	 * @return int Number of deleted transients.
	 */
	public function clear_all() {
		global $wpdb;

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . $this->prefix ) . '%'
			)
		);

		// Also delete timeout transients
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_timeout_' . $this->prefix ) . '%'
			)
		);

		return $deleted;
	}

	/**
	 * Get cache statistics
	 *
	 * @return array Cache statistics.
	 */
	public function get_stats() {
		global $wpdb;

		$total_cached = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . $this->prefix ) . '%'
			)
		);

		return array(
			'total_cached' => (int) $total_cached,
			'prefix' => $this->prefix,
		);
	}
}
