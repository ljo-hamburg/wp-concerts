<?php
/**
 * Sanitization utilities.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

use DateTime;
use Exception;

/**
 * Sanitization utilities.
 *
 * @package LJO\WPConcerts
 */
final class Sanitize {

	/**
	 * Sanitize constructor. All functions are static so the constructor is private.
	 */
	private function __construct() {
	}

	/**
	 * Sanitizes a date value, replacing it with the current date if it cannot be
	 * parsed. The date is then returned as a RFC 3339 string.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string
	 */
	public static function sanitize_date( $value ): string {
		if ( $value instanceof DateTime ) {
			$date = $value;
		} else {
			try {
				$date = new DateTime( $value );
			} catch ( Exception $exception ) {
				$date = new DateTime();
			}
		}
		return $date->format( DATE_RFC3339 );
	}

	/**
	 * Sanitizes an integer value using `absint`.
	 *
	 * @param mixed $value A potential integer.
	 *
	 * @return int An actual integer.
	 */
	public static function sanitize_int( $value ): int {
		return absint( $value );
	}

	/**
	 * Escapes a value for inclusion in ICS data.
	 *
	 * @param string $value A string.
	 *
	 * @return string An escaped string.
	 */
	public static function escape_ics( string $value ): string {
		return preg_replace( '/([,;])/', '\\1', $value );
	}
}
