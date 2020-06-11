<?php
/**
 * Standalone HTML components.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

use DateTime;

/**
 * This class includes standalone, reusable HTML components.
 *
 * @package LJO\WPConcerts
 */
final class HTML {

	/**
	 * HTML constructor. All functions are static, so no instances can be created.
	 */
	private function __construct() {
	}

	/**
	 * Creates a countdown component, counting down to the specified date.
	 *
	 * @param DateTime $date The target date to which the countdown should run.
	 * @param bool     $display Whether to echo the resulting HTML.
	 *
	 * @return string The resulting HTML. If `$display` is set to true the HTML will still
	 *                be returned.
	 */
	public static function countdown( DateTime $date, bool $display = true ): string {
		ob_start(); ?>
		<div class="countdown" data-date="<?php echo esc_attr( $date->format( DATE_RFC3339 ) ); ?>">
			<div class="section">
				<div class="label"><?php esc_html_e( 'Days', 'wp-concerts' ); ?></div>
				<div class="amount days">?</div>
			</div>
			<div class="section">
				<div class="label"><?php esc_html_e( 'Hours', 'wp-concerts' ); ?></div>
				<div class="amount hours">?</div>
			</div>
			<div class="section">
				<div class="label"><?php esc_html_e( 'Minutes', 'wp-concerts' ); ?></div>
				<div class="amount minutes">?</div>
			</div>
			<div class="section">
				<div class="label"><?php esc_html_e( 'Seconds', 'wp-concerts' ); ?></div>
				<div class="amount seconds">?</div>
			</div>
		</div>
		<?php
		$markup = ob_get_clean();
		if ( $display ) {
			echo $markup; // @codingStandardsIgnoreLine
		}
		return $markup;
	}
}
