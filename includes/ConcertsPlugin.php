<?php
/**
 * The main entry point of the wp-concerts plugin.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

use LJO\WPConcerts\Blocks\Block;
use LJO\WPConcerts\Blocks\CastMemberBlock;
use LJO\WPConcerts\Blocks\FeaturedConcertBlock;
use LJO\WPConcerts\Blocks\NextConcertsBlock;
use LJO\WPConcerts\Blocks\ProgramItemBlock;
use LJO\WPConcerts\Blocks\TicketItemBlock;
use LJO\WPConcerts\Blocks\TicketsBlock;

/**
 * The `ConcertsPlugin` is the main plugin class of wp-concerts. It manages a singleton
 * instance that in turn registers hooks in the WordPress system to make the concert
 * functionality available.
 *
 * @package LJO\WPConcerts
 */
class ConcertsPlugin {

	/**
	 * The singleton instance of the concerts plugin.
	 *
	 * @var ConcertsPlugin|null
	 */
	private static ?ConcertsPlugin $instance = null;

	/**
	 * Initializes the concerts plugin. This function must not be called more than once.
	 * You probably want to use {@link ConcertsPlugin::get_instance()} instead.
	 *
	 * @param string $file The plugin file, used for locating files.
	 * @param string $slug The slug of the plugin.
	 */
	public static function initialize( string $file, string $slug ): void {
		self::$instance = new self( $file, $slug );
	}

	/**
	 * Returns the singleton instance of `ConcertsPlugin`.
	 *
	 * @return ConcertsPlugin The concerts plugin instance.
	 */
	public static function get_instance(): ConcertsPlugin {
		return self::$instance;
	}

	/**
	 * The main plugin file. Used for locating files relative to the plugin root.
	 *
	 * @var string
	 */
	public string $file;

	/**
	 * The slug of the plugin.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	public string $version;

	/**
	 * The concert post type registrar.
	 *
	 * @var ConcertPostType
	 */
	private ConcertPostType $post_type;

	/**
	 * The concert calendar registrar.
	 *
	 * @var ConcertCalendar
	 */
	private ConcertCalendar $calendar;

	/**
	 * The cast block registrar.
	 *
	 * @var Block
	 */
	private Block $cast_block;

	/**
	 * The cast member block registrar.
	 *
	 * @var CastMemberBlock
	 */
	private CastMemberBlock $cast_member_block;

	/**
	 * The program block registrar.
	 *
	 * @var Block
	 */
	private Block $program_block;

	/**
	 * The program item block registrar.
	 *
	 * @var ProgramItemBlock
	 */
	private ProgramItemBlock $program_item_block;

	/**
	 * The tickets block registrar.
	 *
	 * @var TicketsBlock
	 */
	private TicketsBlock $tickets_block;

	/**
	 * The ticket item block registrar.
	 *
	 * @var TicketItemBlock
	 */
	private TicketItemBlock $ticket_item_block;

	/**
	 * The featured concert block registrar.
	 *
	 * @var FeaturedConcertBlock
	 */
	private FeaturedConcertBlock $featured_concert;

	/**
	 * The next concerts block registrar.
	 *
	 * @var NextConcertsBlock
	 */
	private NextConcertsBlock $next_concerts;

	/**
	 * ConcertsPlugin constructor. Initializes the plugin by registering the appropriate
	 * hooks in the WordPress system. Aside from registering hooks this function does
	 * nothing.
	 *
	 * @param string $file The main plugin file.
	 * @param string $slug The plugin's slug.
	 */
	public function __construct( string $file, string $slug ) {
		$this->file    = $file;
		$this->slug    = $slug;
		$this->version = WP_CONCERTS_VERSION;

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		// We enqueue our styles relatively late to give themes a chance to provide
		// their own frontend scripts (e.g. another version of font awesome).
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_styles' ), 20 );

		$this->post_type          = new ConcertPostType();
		$this->calendar           = new ConcertCalendar();
		$this->cast_block         = new Block( 'cast' );
		$this->cast_member_block  = new CastMemberBlock();
		$this->program_block      = new Block( 'program' );
		$this->program_item_block = new ProgramItemBlock();
		$this->tickets_block      = new TicketsBlock();
		$this->ticket_item_block  = new TicketItemBlock();
		$this->featured_concert   = new FeaturedConcertBlock();
		$this->next_concerts      = new NextConcertsBlock();
	}

	/**
	 * Loads the plugin's text domain.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'wp-concerts',
			false,
			dirname( plugin_basename( $this->file ) ) . '/languages/'
		);
	}

	/**
	 * Adds the countdown script to the page.
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_script(
			'wp-concerts/countdown',
			plugin_dir_url( __DIR__ ) . '/scripts/countdown.js',
			array( 'jquery' ),
			filemtime( dirname( __DIR__ ) . '/scripts/countdown.js' ),
			true
		);
	}

	/**
	 * Enqueues plugin styles in the front- and backend.
	 */
	public function enqueue_styles(): void {
		wp_register_style(
			'font-awesome',
			'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
			array(),
			'4.7.0'
		);
		wp_enqueue_style(
			'wp-concerts',
			plugin_dir_url( __DIR__ ) . '/style.css',
			array( 'font-awesome' ),
			filemtime( dirname( __DIR__ ) . '/style.css' )
		);
	}
}
