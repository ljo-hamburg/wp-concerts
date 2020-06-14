<?php
/**
 * The `Concert` class. This file is guaranteed to be loaded by the concerts plugin.
 * Themes and other plugins may use this class to interact with the concert post type.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

use DateInterval;
use DateTime;
use Exception;
use Jsvrcek\ICS\Exception\CalendarEventException;
use Jsvrcek\ICS\Model\CalendarEvent;
use Jsvrcek\ICS\Model\Description\Location;
use LJO\WPConcerts\Blocks\CastMemberBlock;
use LJO\WPConcerts\Blocks\ProgramItemBlock;
use LJO\WPConcerts\Blocks\TicketItemBlock;
use LJO\WPConcerts\Blocks\TicketsBlock;
use Locale;
use NumberFormatter;

/**
 * The Concert class is the main API of the wp-concerts plugin. The class provides an
 * easy way to access both the special meta values for concerts and the information
 * provided in the post's content.
 *
 * For performance reasons instances of this class are cached. In order to use a Concert
 * instance you can use the {@link Concert::get()} function.
 *
 * @package LJO\WPConcerts
 */
class Concert {

	/**
	 * The existing concert instances. This is a cache that is used in order to avoid
	 * parsing post contents multiple times.
	 *
	 * @var array
	 */
	private static array $instances = array();

	/**
	 * Lazily gets or creates a `Concert` instance for the post with the specified ID. If
	 * the `$id` is unspecified the current post's ID is used.
	 *
	 * You need to make sure that the `$id` passed to this function actually belongs to a
	 * concert post. It is a programmer error to pass an `$id` belonging to a different
	 * entity.
	 *
	 * @param int  $id The ID of the concert.
	 * @param bool $parse_content A boolean value indicating whether to parse the
	 *                            concert's contents. If you only need to access the
	 *                            concert's meta fields it is a good idea to pass `false`
	 *                            here.
	 *
	 * @return Concert
	 *
	 * @throws Exception If the concert's date cannot be parsed.
	 */
	public static function get( int $id = null, bool $parse_content = true ): Concert {
		if ( ! $id ) {
			$id = get_the_ID();
		}
		if ( ! array_key_exists( $id, self::$instances ) ) {
			$instance         = new Concert( $id );
			$instances[ $id ] = $instance;
		} else {
			$instance = self::$instances[ $id ];
		}
		if ( $parse_content ) {
			$instance->parse_content();
		}
		return $instance;
	}

	/**
	 * The ID of the concert post that is represented by this concert.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * The date of the concert or `null` if the date is invalid or not present. This is a
	 * meta value.
	 *
	 * @var DateTime
	 */
	public DateTime $date;

	/**
	 * The duration of the concert in minutes. A duration of 0 is considered unset. This
	 * is a meta value.
	 *
	 * @var int
	 */
	public int $duration;

	/**
	 * The location where the concert is taking place. Usually this is the name of the
	 * venue. This is a meta value.
	 *
	 * @var string
	 */
	public string $location;

	/**
	 * Additional information about the concert location such as the floor, room or stage.
	 * This is a meta value.
	 *
	 * @var string
	 */
	public string $location_extra;

	/**
	 * The address of the concert location. This is a meta value.
	 *
	 * @var string
	 */
	public string $location_address;

	/**
	 * An URL of the concert location (e.g. a website of the venue). This is a meta value.
	 *
	 * @var string
	 */
	public string $location_url;

	/**
	 * The name of the organizer of the concert. This is a meta value.
	 *
	 * @var string
	 */
	public string $organizer;

	/**
	 * URL of the organizer of the concert. This is a meta value.
	 *
	 * @var string
	 */
	public string $organizer_url;

	/**
	 * A boolean value indicating whether the concert was marked as cancelled. This is a
	 * meta value.
	 *
	 * @var bool
	 */
	public bool $cancelled;

	/**
	 * A message giving the reason why a concert was cancelled. This is a meta value.
	 *
	 * @var string
	 */
	public string $cancelled_message;

	/**
	 * A boolean value indicating whether this concert's body has been parsed. This value
	 * is for internal use only.
	 *
	 * @var bool
	 */
	protected bool $parsed;

	/**
	 * The description of the concert. This value is obtained by concatenating all
	 * paragraphs in the concert's body.
	 *
	 * @var string
	 */
	public string $description;

	/**
	 * An array of cast members that are participating in this concert- This value is
	 * obtained by collecting all cast member blocks in the concert's body.
	 *
	 * @var array
	 */
	public array $cast;

	/**
	 * An array of program items that make up the concert schedule. This array is obtained
	 * by collecting all program items in the concert's body.
	 *
	 * @var array
	 */
	public array $program;

	/**
	 * The main ticket availability. This value is obtained from one of the ticket blocks
	 * in the concert's body. If multiple ticket blocks exist the last one will take
	 * precedence.
	 *
	 * @var string
	 */
	public string $ticket_availability;

	/**
	 * The main ticket URL (if any). This value is obtained from one of the ticket blocks
	 * in the concert's body. If multiple ticket blocks exist the last one will take
	 * precedence.
	 *
	 * @var string|null
	 */
	public ?string $ticket_url;

	/**
	 * The main ticket notes (if any). This value is obtained from one of the ticket
	 * blocks in the concert's body. If multiple ticket blocks exist the last one will
	 * take precedence.
	 *
	 * @var string|null
	 */
	public ?string $ticket_notes;

	/**
	 * The available ticket tiers. This value is obtained by collecting all ticket items
	 * in the concert's body. In addition to the ticket item's attributes each price also
	 * contains the availability, URL and notes from the surrounding ticket block.
	 *
	 * @var array
	 */
	public array $ticket_prices;

	/**
	 * An array of URLs corresponding to images associated with this concert.
	 *
	 * @var string[]
	 */
	public array $image_urls;

	/**
	 * Concert constructor. Instead of calling the constructor you should use
	 * {@link Concert::get()} instead.
	 *
	 * @param int $id The concert's post ID. If unspecified the current post's ID is used.
	 *
	 * @throws Exception If the concert's date cannot be parsed.
	 */
	protected function __construct( int $id ) {
		if ( ! $id ) {
			$id = get_the_ID();
		}
		assert( ConcertPostType::SLUG === get_post_type( $id ) );
		$this->id = $id;
		$this->init();
		$this->load_meta();
		$this->parsed = false;
	}

	/**
	 * Initializes the concert with default values.
	 */
	protected function init(): void {
		$this->date              = new DateTime();
		$this->duration          = 0;
		$this->location          = '';
		$this->location_extra    = '';
		$this->location_address  = '';
		$this->location_url      = '';
		$this->cancelled         = false;
		$this->cancelled_message = '';

		$this->description         = '';
		$this->cast                = array();
		$this->program             = array();
		$this->ticket_availability = TicketsBlock::AVAILABILITY_NONE;
		$this->ticket_notes        = null;
		$this->ticket_url          = null;
		$this->ticket_prices       = array();
		$this->image_urls          = array();

		/**
		 * This action is fired after a `Concert` has been initialized with default values.
		 * This hook gives themes and plugins the chance to provide custom default values or
		 * add custom attributes to a concert.
		 *
		 * @param Concert $concert The concert instance being initialized.
		 */
		do_action( 'wp_concerts/init_concert', $this );
	}

	/**
	 * Loads the concert's post meta.
	 *
	 * @throws Exception If the concert's date cannot be parsed.
	 */
	protected function load_meta(): void {
		$id             = $this->id;
		$this->date     = new DateTime(
			get_post_meta( $id, ConcertPostType::DATE_META_KEY, true )
		);
		$this->duration = intval(
			get_post_meta( $id, ConcertPostType::DURATION_META_KEY, true )
		);

		$this->location         = get_post_meta(
			$id,
			ConcertPostType::LOCATION_META_KEY,
			true
		);
		$this->location_extra   = get_post_meta(
			$id,
			ConcertPostType::LOCATION_EXTRA_META_KEY,
			true
		);
		$this->location_address = get_post_meta(
			$id,
			ConcertPostType::LOCATION_ADDRESS_META_KEY,
			true
		);
		$this->location_url     = get_post_meta(
			$id,
			ConcertPostType::LOCATION_URL_META_KEY,
			true
		);

		$this->organizer     = get_post_meta( $id, ConcertPostType::ORGANIZER_META_KEY, true );
		$this->organizer_url = get_post_meta( $id, ConcertPostType::ORGANIZER_URL_META_KEY, true );

		$this->cancelled         = boolval(
			get_post_meta( $id, ConcertPostType::CANCELLED_META_KEY, true )
		);
		$this->cancelled_message = get_post_meta(
			$this->id,
			ConcertPostType::CANCELLED_MESSAGE_META_KEY,
			true
		);

		$thumbnail = get_the_post_thumbnail_url( $id, 'full' );
		if ( $thumbnail ) {
			$this->image_urls[] = $thumbnail;
		}

		/**
		 * This action is fired after a `Concert` has loaded its metadata. This gives themes
		 * and plugins the chance to provide their own metadata (such as a custom header
		 * image).
		 *
		 * @param Concert $concert The concert instance being initialized.
		 */
		do_action( 'wp_concerts/load_meta', $this );
	}

	/**
	 * Parses the concert's post content thereby loading data from certain blocks. This
	 * method should usually not be called directly (although it is save to do so). It is
	 * called automatically by {@link Concert::get()}.
	 */
	public function parse_content(): void {
		if ( $this->parsed ) {
			return;
		}
		$blocks = parse_blocks( get_post( $this->id )->post_content );
		foreach ( $blocks as $block ) {
			/**
			 * This filter is applied to each block before it is parsed by a concert. This
			 * gives themes and plugins the chance to override the default behavior and to
			 * support custom blocks.
			 *
			 * @param array $block The block that is about to be parsed.
			 * @param Concert $concert The concert instance being initialized.
			 *
			 * @return array|null|false If this filter returns a falsy value the default
			 * parsing mechanism will be skipped. Otherwise the filter must return a block
			 * that is then parsed by the concert.
			 */
			$block = apply_filters( 'wp_concerts/load_block', $block, $this );
			if ( $block ) {
				$this->load_block( $block );
			}
		}
		$this->parsed = true;
	}

	/**
	 * Loads data from a single block. This method iterates over all blocks in this post's
	 * content and extracts relevant data for the concert.
	 *
	 * @param array $block The array representation of a block.
	 */
	protected function load_block( array $block ): void {
		switch ( $block['blockName'] ) {
			case 'core/paragraph':
				if ( $this->description ) {
					$this->description .= "\n\n";
				}
				$this->description .= wp_strip_all_tags( render_block( $block ), true );
				break;
			case 'core/image':
				$id    = $block['attrs']['id'];
				$image = wp_get_attachment_image_src( $id, 'full' );
				if ( $image ) {
					$this->image_urls[] = $image[0];
				}
				break;
			case 'wp-concerts/cast-member':
				$this->cast[] = $block['attrs'];
				break;
			case 'wp-concerts/program-item':
				$this->program[] = $block['attrs'];
				break;
			case 'wp-concerts/tickets':
				$attrs                     = $block['attrs'];
				$this->ticket_availability =
						$attrs[ TicketsBlock::AVAILABILITY_KEY ] ?? TicketsBlock::AVAILABILITY_NONE;
				$this->ticket_notes        = $attrs[ TicketsBlock::NOTES_KEY ] ?? null;
				$this->ticket_url          = $attrs[ TicketsBlock::LINK_KEY ] ?? null;
				foreach ( $block['innerBlocks'] as $inner_block ) {
					assert( 'wp-concerts/ticket-item' === $inner_block['blockName'] );
					$price                                   = $inner_block['attrs'];
					$price[ TicketsBlock::AVAILABILITY_KEY ] = $this->ticket_availability;
					$price[ TicketsBlock::NOTES_KEY ]        = $this->ticket_notes;
					$price[ TicketsBlock::LINK_KEY ]         = $this->ticket_url;
					$this->ticket_prices[]                   = $price;
				}
				break;
			default:
				if ( array_key_exists( 'innerBlocks', $block ) ) {
					foreach ( $block['innerBlocks'] as $inner_block ) {
						$this->load_block( $inner_block );
					}
				}
				break;
		}
	}

	/**
	 * Returns the date when the concert will end (or did end). This is calculated
	 * depending on the concert's start date and duration. If the duration is 0, `null` is
	 * returned.
	 *
	 * @return DateTime|null
	 */
	public function get_end(): ?DateTime {
		$duration = $this->duration;
		if ( $duration > 0 ) {
			$end = clone $this->date;
			$end->add( new DateInterval( "PT{$duration}M" ) );

			return $end;
		} else {
			return null;
		}
	}

	/**
	 * Returns a boolean value indicating whether the concert's end date is in the past.
	 *
	 * @return bool
	 */
	public function is_over(): bool {
		$end = $this->get_end();
		if ( $end ) {
			return strtotime( 'now' ) >= $this->get_end()->getTimestamp();
		} else {
			return strtotime( 'now' ) >= $this->date->getTimestamp();
		}
	}

	/**
	 * Returns an array of class names that should be applied to the root element of this
	 * concert (or the body class of a single concert page).
	 *
	 * @return string[]
	 */
	public function html_classes(): array {
		$class_names = array( 'concert', 'post-type-concert' );
		if ( $this->cancelled ) {
			$class_names[] = 'cancelled';
		}
		if ( $this->is_over() ) {
			$class_names[] = 'over';
		}
		return $class_names;
	}

	/**
	 * Returns a HTML representation of this concert suitable to be displayed in a list of
	 * concerts.
	 *
	 * @param bool $include_schema_data Whether or not to include JSON-LD data in the
	 *                                  generated HTML.
	 * @param bool $display Whether to echo the resulting HTML or not.
	 *
	 * @return string The resulting HTML. If `$display` is set to true the HTML is both
	 *                echo'd and returned.
	 */
	public function html( bool $include_schema_data = true, bool $display = true ): ?string {
		$subtitle = apply_filters( 'wp_concerts/subtitle', null, $this );
		$end      = $this->get_end();
		ob_start(); ?>
			<a href="<?php the_permalink( $this->id ); ?>" class="post post-item post-type-concert
					<?php echo esc_attr( implode( ' ', $this->html_classes() ) ); ?>">
				<?php if ( $include_schema_data ) : ?>
					<?php echo '<script type="application/ld+json">'; ?>
						<?php echo wp_json_encode( $this->get_schema_data() ); ?>
					<?php echo '</script>'; ?>
				<?php endif ?>
				<div class="date">
					<span class="day"><?php echo esc_html( $this->date->format( 'd' ) ); ?></span>
					<span class="month-year">
							<?php echo esc_html( $this->date->format( 'M, Y' ) ); ?>
					</span>
				</div>
				<div class="summary">
					<h3 class="title"><?php echo esc_html( get_the_title( $this->id ) ); ?></h3>
					<?php if ( $subtitle ) : ?>
						<div class="subtitle"><?php echo esc_html( $subtitle ); ?></div>
					<?php endif ?>
					<?php if ( $this->location ) : ?>
						<div class="location">
							<i class="fa fa-map-marker-alt"></i>
							<strong><?php echo esc_html( $this->location ); ?></strong>
							<?php echo esc_html( $this->location_extra ); ?>
						</div>
					<?php endif ?>
					<div class="time">
						<i class="fa fa-clock-o"></i>
						<?php echo esc_html( $this->date->format( 'H:m' ) ); ?>
						<?php if ( $end ) : ?>
						– <?php echo esc_html( $end->format( 'H:m' ) ); ?>
						<?php endif ?>
					</div>
				</div>
				<?php if ( $this->cancelled ) : ?>
					<div class="info"><?php esc_html_e( 'Cancelled', 'wp-concerts' ); ?></div>
				<?php elseif ( $this->is_over() ) : ?>
					<div class="info"><?php esc_html_e( 'Finished', 'wp-concerts' ); ?></div>
				<?php else : ?>
					<?php HTML::countdown( $this->date ); ?>
				<?php endif ?>
			</a>
		<?php
		$markup = ob_get_clean();
		if ( $display ) {
			echo $markup; // @codingStandardsIgnoreLine
		}

		return $markup;
	}

	/**
	 * Returns a JSON-compatible array containing data representing this concert according
	 * to schema.org. JSON-Encoding the resulting array will give you valid JSON-LD markup
	 * for this concert.
	 *
	 * @return array A JSON-LD compatible array of structured data.
	 */
	public function get_schema_data(): array {
		$id   = $this->id;
		$data = array(
			'@context'            => 'http://schema.org',
			'@type'               => 'MusicEvent',
			'url'                 => get_the_permalink( $id ),
			'name'                => get_the_title( $id ),
			'description'         => $this->description,
			'eventStatus'         => $this->cancelled ? 'EventCancelled' : 'EventScheduled',
			'eventAttendanceMode' => 'OfflineEventAttendanceMode',
		);

		if ( $this->organizer ) {
			$data['organizer'] = array(
				'name'   => $this->organizer,
				'url'    => $this->organizer_url,
				'sameAs' => $this->organizer_url,
			);
		}

		if ( ! empty( $this->image_urls ) ) {
			$data['image'] = $this->image_urls;
		}

		$start = $this->date;
		$end   = $this->get_end();
		if ( $start ) {
			$data['startDate'] = $start->format( DATE_ISO8601 );
			if ( $end ) {
					$data['endDate'] = $end->format( DATE_ISO8601 );
			}
		}

		if ( $this->location ) {
			$data['location'] = array(
				'@type'   => 'MusicVenue',
				'name'    => $this->location,
				'address' => $this->location_address,
				'url'     => $this->location_url,
				'sameAs'  => $this->location_url,
			);
		}

		$price_format = new NumberFormatter( get_locale(), NumberFormatter::CURRENCY );
		$offers_ld    = array();
		foreach ( $this->ticket_prices as $price ) {
			$offer = array(
				'@type'       => 'Offer',
				'description' => $price[ TicketItemBlock::DESCRIPTION_KEY ] ?? null,
				'validFrom'   => get_the_modified_date( 'Y-m-d', $id ),
			);

			$currency       = null;
			$parsable_price = str_replace( ' ', "\xc2\xa0", $price[ TicketItemBlock::AMOUNT_KEY ] );
			$offer['price'] = $price_format->parseCurrency(
				$parsable_price,
				$currency
			);
			if ( ! $currency ) {
				$currency = $price_format->getTextAttribute( NumberFormatter::CURRENCY_CODE );
			}
			$offer['priceCurrency'] = $currency;

			if ( $this->cancelled ) {
				$availability = 'Discontinued';
			} elseif ( $price[ TicketItemBlock::SOLD_OUT_KEY ] ?? false ) {
				$availability = 'SoldOut';
			} else {
				$availability = 'PreOrder';
			}
			$offer['availability'] = $availability;

			if ( TicketsBlock::AVAILABILITY_ONLINE === $price[ TicketsBlock::AVAILABILITY_KEY ]
					&& isset( $price[ TicketsBlock::LINK_KEY ] ) ) {
				$offer['url'] = $price[ TicketsBlock::LINK_KEY ] ?? null;
			}

			$offers_ld[] = $offer;
		}
		if ( empty( $offers_ld ) && $this->ticket_url ) {
			$offers_ld = array(
				'@type'     => 'Offer',
				'url'       => $this->ticket_url,
				'validFrom' => get_the_modified_date( 'Y-m-d' ),
			);
		}
		$data['offers'] = $offers_ld;

		if ( TicketsBlock::AVAILABILITY_FREE === $this->ticket_availability ) {
			$data['isAccessibleForFree'] = true;
		}

		$schedule_ld = array();
		foreach ( $this->program as $program_item ) {
			$schedule_ld[] = array(
				'@type'    => 'MusicComposition',
				'composer' => array(
					'@type' => 'Person',
					'name'  => $program_item[ ProgramItemBlock::COMPOSER_KEY ] ?? null,
				),
				'name'     => $program_item[ ProgramItemBlock::PIECE_KEY ] ?? null,
				'url'      => $program_item[ ProgramItemBlock::URL_KEY ] ?? null,
				'sameAs'   => $program_item[ ProgramItemBlock::URL_KEY ] ?? null,
			);
		}
		$data['workPerformed'] = $schedule_ld ? $schedule_ld : null;

		$performers_ld = array();
		foreach ( $this->cast as $member ) {
			$performers_ld[] = array(
				'name'   => $member[ CastMemberBlock::NAME_KEY ] ?? null,
				'@type'  => empty( $member[ CastMemberBlock::ENSEMBLE_KEY ] )
						? 'Person' : 'MusicGroup',
				'url'    => $member[ CastMemberBlock::URL_KEY ] ?? null,
				'sameAs' => $member[ CastMemberBlock::URL_KEY ] ?? null,
			);
		}
		$data['performer'] = $performers_ld ? $performers_ld : null;

		/**
		 * Filters the structured data for a concert. This gives themes and plugins the
		 * chance to modify the data or to append custom values.
		 *
		 * @param array $data The structured data representing a concert.
		 * @param Concert $concert The concert instance for which the data is generated.
		 * @return array The structured data for the concert.
		 */
		return apply_filters( 'wp_concerts/json_ld', $data, $this );
	}

	/**
	 * Returns the URL to the ICS event for this concert.
	 *
	 * @return string The URL to the ICS for this concert.
	 */
	public function ics_url() {
		return ConcertCalendar::ics_url( $this->id );
	}

	/**
	 * Returns a `CalendarEvent` representing this concert.
	 *
	 * @return CalendarEvent An event representing this concert.
	 */
	public function get_ical_event(): CalendarEvent {
		$id    = $this->id;
		$event = new CalendarEvent();
		$title = get_the_title( $id );
		$event->setUid( $id );
		$event->setCreated( new DateTime( 'now' ) );
		$event->setSummary( Sanitize::escape_ics( $title ) );
		$event->setUrl( get_the_permalink( $id ) );
		$event->setDescription( $this->description );
		$event->setStart( $this->date );

		if ( $this->cancelled ) {
			$event->setStatus( 'CANCELLED' );
		}
		$end = $this->get_end();
		if ( $end ) {
			try {
				$event->setEnd( $end );
			} catch ( CalendarEventException $exception ) {
				// Should never happen.
				wp_die(
					esc_html__(
						'An internal error occurred when generating an event for a concert.',
						'wp-concerts'
					)
				);
			}
		}

		if ( $this->location ) {
			$location_string = $this->location;
			if ( $this->location_extra ) {
				$location_string .= ' – ' . $this->location_extra;
			}
			if ( $this->location_address ) {
				$location_string .= '\n' . str_replace( ',', '\,', $this->location_address );
			}
			$location = new Location();
			$location->setLanguage( Locale::getPrimaryLanguage( get_locale() ) );
			$location->setName( Sanitize::escape_ics( $location_string ) );
			if ( $this->location_url ) {
				$location->setUri( $this->location_url );
			}
			$event->addLocation( $location );
		}

		/**
		 * Filters the `CalendarEvent` representation of a concert. This gives themes and
		 * plugins the chance to modify or add data to the event.
		 *
		 * @param CalendarEvent $event The generated calendar event.
		 * @param Concert $concert The concert instance for which the event is generated.
		 * @return CalendarEvent The final calendar event representing the concert instance.
		 */
		return apply_filters( 'wp_concerts/ics', $event, $this );
	}
}
