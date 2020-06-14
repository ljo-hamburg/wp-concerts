<?php
/**
 * The `ConcertCalendar` makes concert calendars available.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

use Exception;
use Jsvrcek\ICS\CalendarExport;
use Jsvrcek\ICS\CalendarStream;
use Jsvrcek\ICS\Model\Calendar;
use Jsvrcek\ICS\Utility\Formatter;
use Locale;
use WP_Post;
use WP_Query;

/**
 * The `ConcertCalendar` does three things:
 * - It provides a means to generate valid ICS calendars from concerts.
 * - It adds a query parameters that lets users download an ICS representation for a
 *   concert.
 * - It adds the option to download an ICS calendar for all concerts.
 *
 * @package LJO\WPConcerts
 */
class ConcertCalendar {

	/**
	 * This query parameter is used to determine whether to download the ICS file for a
	 * concert or forward to the usual WordPress templating.
	 *
	 * @var string
	 */
	public const QUERY_PARAMETER = 'ics';

	/**
	 * ConcertCalendar constructor. Registers the appropriate hooks to make calendars
	 * available to the site.
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'parse_query', array( $this, 'parse_query' ) );
		add_filter( 'template_redirect', array( $this, 'redirect_calendar' ) );
	}

	/**
	 * Filters the `WP_Query` vars by adding the {@link ConcertCalendar::QUERY_PARAMETER}.
	 *
	 * @param string[] $vars The exiting query vars.
	 * @return string[] The new query vars.
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = self::QUERY_PARAMETER;
		return $vars;
	}

	/**
	 * This function is hooked into `parse_query`. It makes sure that the query includes
	 * all concerts
	 *
	 * @param WP_Query $query The main WP_Query instance.
	 */
	public function parse_query( WP_Query $query ): void {
		if ( $query->is_post_type_archive( ConcertPostType::SLUG ) ) {
			if ( boolval( $query->get( self::QUERY_PARAMETER, false ) ) ) {
				$query->set( 'all', true );
				$query->set( 'nopaging', true );
			}
		}
	}

	/**
	 * This function is hooked into `template_redirect`. It determines whether the ICS
	 * version of a concert should be downloaded or the user should be forwarded to the
	 * usual WordPress templating mechanism.
	 *
	 * If this function determines that the calendar should be shown it will echo the
	 * data directly (including custom headers) and abort the WordPress templating
	 * mechanism.
	 */
	public function redirect_calendar(): void {
		if ( boolval( get_query_var( self::QUERY_PARAMETER, false ) ) ) {
			if ( is_post_type_archive( ConcertPostType::SLUG ) ) {
				$this->print_all();
				die;
			} elseif ( is_singular( ConcertPostType::SLUG ) ) {
				$this->print_single();
				die;
			}
		}
	}

	/**
	 * Returns the URL for the calendar entry for the specified concert.
	 *
	 * @param int|WP_Post|null $id ID of a concert. If unspecified or not the ID of a
	 *                             concert the URL to the full calendar will be
	 *                             returned.
	 *
	 * @return string The URL to a single concert event or to the concert calendar.
	 */
	public static function ics_url( $id ): string {
		if ( $id && ConcertPostType::SLUG === get_post_type( $id ) ) {
			$url = get_the_permalink( $id );
		} else {
			$url = get_post_type_archive_link( ConcertPostType::SLUG );
		}
		$param = self::QUERY_PARAMETER;
		$url  .= ( wp_parse_url( $url, PHP_URL_QUERY ) ? '&' : '?' ) . "$param=1";
		return $url;
	}

	/**
	 * Returns the product ID for the concert calendar. By default the ID is constructed
	 * from the site's name and language but it is possible to override the ID using the
	 * `wp_concerts/calendar_prod_id` hook.
	 *
	 * @return string The product ID for the concert calendar.
	 */
	public function get_product_id(): string {
		$name     = get_bloginfo( 'name' );
		$language = strtoupper( Locale::getPrimaryLanguage( get_locale() ) );
		$prod_id  = "-//$name//Concerts//$language";

		/**
		 * Filters the product ID for the concert calendar.
		 *
		 * @param string $prod_id The generated product ID.
		 * @return string The actual product ID.
		 */
		return apply_filters( 'wp_concerts/calendar_prod_id', $prod_id );
	}

	/**
	 * Prints the ICS data for a single concert (a single event). The ICS is echo'd and
	 * not returned.
	 */
	public function print_single(): void {
		try {
			$concert  = Concert::get( get_the_ID() );
			$calendar = new Calendar();
			$calendar->setProdId( $this->get_product_id() );
			$calendar->setTimezone( wp_timezone() );
			$calendar->addEvent( $concert->get_ical_event() );
			$calendar = apply_filters( 'wp_concerts/calendar', $calendar );

			$calendar_export = new CalendarExport( new CalendarStream(), new Formatter() );
			$calendar_export->addCalendar( $calendar );

			header( 'Content-type: text/calendar; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . get_the_title() . '.ics' );
			echo $calendar_export->getStream(); // @codingStandardsIgnoreLine
		} catch ( Exception $e ) {
			header( 'HTTP/1.1 500 Internal Server Error' );
			exit( 'The ICS Event could not be generated.' );
		}
	}

	/**
	 * Prints the ICS data for the full concert calendar. The ICS is echo'd and not
	 * returned.
	 */
	public function print_all(): void {
		try {
			$calendar = new Calendar();
			$calendar->setProdId( $this->get_product_id() );
			$calendar->setTimezone( wp_timezone() );

			$url  = get_post_type_archive_link( ConcertPostType::SLUG );
			$url .= ( wp_parse_url( $url, PHP_URL_QUERY ) ? '&' : '?' ) . 'ics=1';
			$name = apply_filters( 'wp_concerts/calendar_name', __( 'Concerts', 'wp-concerts' ) );

			$description = apply_filters( 'wp_concerts/calendar_description', '' );

			$calendar->setCustomHeaders(
				array(
					'URL'           => $url,
					'NAME'          => $name,
					'X-WR-CALNAME'  => $name,
					'DESCRIPTION'   => $description,
					'X-WR-CALDESC'  => $description,
					'TIMEZONE-ID'   => wp_timezone_string(),
					'X-WR-TIMEZONE' => wp_timezone_string(),
				)
			);
			while ( have_posts() ) {
				the_post();
				$concert = Concert::get( get_the_ID() );
				$calendar->addEvent( $concert->get_ical_event() );
			}

			$calendar_export = new CalendarExport( new CalendarStream(), new Formatter() );
			$calendar_export->addCalendar( $calendar );

			header( 'Content-type: text/calendar; charset=utf-8' );
			echo $calendar_export->getStream(); // @codingStandardsIgnoreLine
		} catch ( Exception $e ) {
			header( 'HTTP/1.1 500 Internal Server Error' );
			exit( 'The ICS Calendar could not be generated.' );
		}
	}
}
