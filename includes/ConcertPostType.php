<?php
/**
 * This file manages the custom post type concert.
 *
 * @package LJO\WPCocnerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

use Exception;
use WP_Query;

/**
 * The class `ConcertPostType` manages the custom post type for concerts. It registers
 * the appropriate hooks to make the post type and its taxonomies available.
 *
 * @package LJO\WPConcerts
 */
class ConcertPostType {

	/**
	 * The identifier of the post type. Concerning WordPress the name of this constant
	 * IS the custom post type.
	 *
	 * @var string
	 */
	public const SLUG = 'concert';

	/**
	 * The name of the tags taxonomy for concerts.
	 *
	 * @var string
	 */
	public const TAGS_KEY = 'concert_tags';

	/**
	 * The name of the categories taxonomy for concerts.
	 *
	 * @var string
	 */
	public const CATEGORIES_KEY = 'concert-category';

	/**
	 * The script handle for the script that registers the editor sidebar for concert
	 * metadata.
	 *
	 * @var string
	 */
	public const SCRIPT_HANDLE = 'concert-meta';

	/**
	 * The post meta key for the date of a concert.
	 *
	 * @var string
	 */
	public const DATE_META_KEY = 'concert-date';

	/**
	 * The post meta key for the duration of a concert.
	 *
	 * @var string
	 */
	public const DURATION_META_KEY = 'concert-duration';

	/**
	 * The post meta key for the location of a concert.
	 *
	 * @var string
	 */
	public const LOCATION_META_KEY = 'concert-location';

	/**
	 * The post meta key for the location extra of a concert.
	 *
	 * @var string
	 */
	public const LOCATION_EXTRA_META_KEY = 'concert-location-extra';

	/**
	 * The post meta key for the location address of a concert.
	 *
	 * @var string
	 */
	public const LOCATION_ADDRESS_META_KEY = 'concert-location-address';

	/**
	 * The post meta key for the location URL of a concert.
	 *
	 * @var string
	 */
	public const LOCATION_URL_META_KEY = 'concert-location-url';

	/**
	 * The post meta key for the organizer of a concert.
	 *
	 * @var string
	 */
	public const ORGANIZER_META_KEY = 'concert-organizer';

	/**
	 * The post meta key for the organizer URL of a concert.
	 *
	 * @var string
	 */
	public const ORGANIZER_URL_META_KEY = 'concert-organizer-url';

	/**
	 * The post meta key for the cancelled flag of a concert.
	 *
	 * @var string
	 */
	public const CANCELLED_META_KEY = 'concert-cancelled';

	/**
	 * The post meta key for the cancelled message of a concert.
	 *
	 * @var string
	 */
	public const CANCELLED_MESSAGE_META_KEY = 'concert-cancelled-message';

	/**
	 * ConcertPostType constructor. Registers the appropriate hooks to make the post
	 * type available.
	 */
	public function __construct() {
		add_action( 'init', array( self::class, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxononmies' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'pre_get_posts', array( $this, 'sort_concerts' ) );
		add_filter( 'single_template', array( $this, 'page_template' ) );
		add_filter( 'template_include', array( $this, 'archive_template' ) );
		add_filter( 'body_class', array( $this, 'filter_body_class' ) );
		add_action(
			'admin_print_scripts-post-new.php',
			array(
				$this,
				'enqueue_editor_assets',
			)
		);
		add_action(
			'admin_print_scripts-post.php',
			array(
				$this,
				'enqueue_editor_assets',
			)
		);
	}

	/**
	 * Registers the concert post type in WordPress.
	 *
	 * This function is intentionally static so that it can be called from the plugin
	 * activator.
	 */
	public static function register_post_type(): void {
		$labels = array(
			'name'                  => _x( 'Concerts', 'Post type general name', 'wp-concerts' ),
			'singular_name'         => _x( 'Concert', 'Post type singular name', 'wp-concert' ),
			'menu_name'             => _x( 'Concerts', 'Admin Menu text', 'wp-concerts' ),
			'name_admin_bar'        => _x( 'Concert', 'Add New on Toolbar', 'wp-concerts' ),
			'add_new'               => __( 'Add New', 'wp-concerts' ),
			'add_new_item'          => __( 'Add New Concert', 'wp-concerts' ),
			'new_item'              => __( 'New Concert', 'wp-concerts' ),
			'edit_item'             => __( 'Edit Concert', 'wp-concerts' ),
			'view_item'             => __( 'View Concert', 'wp-concerts' ),
			'all_items'             => __( 'All Concerts', 'wp-concerts' ),
			'search_items'          => __( 'Search Concerts', 'wp-concerts' ),
			'parent_item_colon'     => __( 'Parent Concerts:', 'wp-concerts' ),
			'not_found'             => __( 'No concerts found.', 'wp-concerts' ),
			'not_found_in_trash'    => __( 'No concerts found in Trash.', 'wp-concerts' ),
			'archives'              => __( 'Concert archives', 'wp-concerts' ),
			'insert_into_item'      => __( 'Insert into concert', 'wp-concerts' ),
			'uploaded_to_this_item' => __( 'Uploaded to this concert', 'wp-concerts' ),
			'filter_items_list'     => _x(
				'Filter concert list',
				'Screen reader text',
				'wp-concerts'
			),
			'items_list_navigation' => _x(
				'Concerts list navigation',
				'Screen reader text',
				'wp-concerts'
			),
			'items_list'            => _x( 'Books list', 'Screen reader text', 'wp-concerts' ),
		);
		$args   = array(
			'labels'       => $labels,
			'description'  => __( 'An upcoming concert.', 'wp-concerts' ),
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-calendar-alt',
			'has_archive'  => _x( 'concerts', 'Concert Archive Slug', 'wp-concerts' ),
			'rewrite'      => array( 'slug' => _x( 'concert', 'Concert Slug', 'wp-concerts' ) ),
			'supports'     => array(
				'title',
				'editor',
				'custom-fields',
				'thumbnail',
				'excerpt',
				'revisions',
			),
		);
		register_post_type( self::SLUG, $args );
	}

	/**
	 * Registers the taxonomies for the concert post type.
	 */
	public function register_taxononmies(): void {
		$labels = array(
			'name'                       => __( 'Tags' ),
			'singular_name'              => __( 'Tag' ),
			'search_items'               => __( 'Search Tags' ),
			'popular_items'              => __( 'Popular Tags' ),
			'all_items'                  => __( 'All Tags' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag' ),
			'update_item'                => __( 'Update Tag' ),
			'add_new_item'               => __( 'Add New Tag' ),
			'new_item_name'              => __( 'New Tag Name' ),
			'separate_items_with_commas' => __( 'Separate tags with commas' ),
			'add_or_remove_items'        => __( 'Add or remove tags' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags' ),
			'not_found'                  => __( 'No tags found.' ),
			'menu_name'                  => __( 'Concert Tags', 'wp-concerts' ),
		);
		$args   = array(
			'labels'       => $labels,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => __( 'tag' ) ),
		);
		register_taxonomy( self::TAGS_KEY, self::SLUG, $args );
		$labels = array(
			'name'              => __( 'Categories' ),
			'singular_name'     => __( 'Category' ),
			'search_items'      => __( 'Search Categories' ),
			'all_items'         => __( 'All Categories' ),
			'parent_item'       => __( 'Parent Category' ),
			'parent_item_colon' => __( 'Parent Category:' ),
			'edit_item'         => __( 'Edit Category' ),
			'update_item'       => __( 'Update Category' ),
			'add_new_item'      => __( 'Add New Category' ),
			'new_item_name'     => __( 'New Category Name' ),
			'menu_name'         => __( 'Concert Category', 'wp-concerts' ),
		);

		$args = array(
			'hierarchical' => true,
			'labels'       => $labels,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => __( 'category' ) ),
		);
		register_taxonomy( self::CATEGORIES_KEY, self::SLUG, $args );
	}

	/**
	 * Registers the meta fields for the concert post type so that they appear in the
	 * REST API and can be used in the Gutenberg editor.
	 */
	public function register_meta(): void {
		$meta = array(
			self::DATE_META_KEY              => array(
				'type'     => 'string',
				'sanitize' => array( Sanitize::class, 'sanitize_date' ),
			),
			self::DURATION_META_KEY          => 'integer',
			self::LOCATION_META_KEY          => 'string',
			self::LOCATION_EXTRA_META_KEY    => 'string',
			self::LOCATION_ADDRESS_META_KEY  => 'string',
			self::LOCATION_URL_META_KEY      => 'string',
			self::ORGANIZER_META_KEY         => 'string',
			self::ORGANIZER_URL_META_KEY     => 'string',
			self::CANCELLED_META_KEY         => 'boolean',
			self::CANCELLED_MESSAGE_META_KEY => 'string',
		);
		foreach ( $meta as $key => $value ) {
			$args     = array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => $value['type'] ?? $value,
			);
			$sanitize = $value['sanitize'] ?? null;
			if ( $sanitize ) {
				$args['sanitize_callback'] = $sanitize;
			}
			register_post_meta( self::SLUG, $key, $args );
		}
	}

	/**
	 * Enqueues assets for the block editor when editing a concert. This makes the
	 * concert sidebar available.
	 */
	public function enqueue_editor_assets(): void {
		global $post_type;
		if ( self::SLUG === $post_type ) {
			$dependencies = require dirname( __DIR__ ) . '/blocks/concert-meta.asset.php';
			wp_register_script(
				self::SCRIPT_HANDLE,
				plugin_dir_url( __DIR__ ) . '/blocks/concert-meta.js',
				$dependencies['dependencies'],
				$dependencies['version'],
				true
			);
			wp_set_script_translations(
				self::SCRIPT_HANDLE,
				'wp-concerts',
				dirname( __DIR__ ) . '/languages'
			);
			wp_enqueue_script( self::SCRIPT_HANDLE );
		}
	}

	/**
	 * Adds a query variable that supports listing all or upcoming concerts in the
	 * concert post type archive.
	 *
	 * @param string[] $vars Existing query variables.
	 * @return string[] New query variables.
	 */
	public function add_query_vars( array $vars ) :array {
		$vars[] = 'all';
		return $vars;
	}

	/**
	 * This function is hooked into the main WP_Query. It makes sure that concerts will
	 * be sorted using the concert date instead of the publishing date. Additionally it
	 * will hide past concerts unless the `all` query parameter is specified.
	 *
	 * @param WP_Query $query The main query.
	 */
	public function sort_concerts( WP_Query $query ): void {
		if ( $query->is_post_type_archive( self::SLUG ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', self::DATE_META_KEY );
			$query->set( 'order', 'ASC' );
			if ( empty( $query->get( 'all', false ) ) ) {
				$query->set(
					'meta_query',
					array(
						'key'     => self::DATE_META_KEY,
						'value'   => gmdate( DATE_RFC3339 ),
						'compare' => '>=',
					)
				);
			}
		}
	}

	/**
	 * Adds a concert's extra classes to the post's body class.
	 *
	 * @param string[] $classes The existing body classes.
	 * @return string[] The new body classes.
	 *
	 * @throws Exception If the concert's date cannot be parsed.
	 */
	public function filter_body_class( array $classes ): array {
		if ( is_singular( self::SLUG ) ) {
			$concert = Concert::get( get_the_ID(), false );
			$classes = array_merge( $classes, $concert->html_classes() );
		}
		return $classes;
	}

	/**
	 * This function substitutes the general templates of a theme for a specific
	 * template for the concert post type. Themes may themselves provide a specialized
	 * template for the post type in which case this function chooses that template.
	 *
	 * @param string $template The template initially chosen by the WordPress template
	 *                         hierarchy.
	 *
	 * @return string The actual template.
	 */
	public function archive_template( string $template ): string {
		$slug = self::SLUG;
		if ( is_post_type_archive( $slug ) ) {
			$template        = "archive-$slug.php";
			$exists_in_theme = locate_template( $template, false );
			if ( '' !== $exists_in_theme ) {
				return $exists_in_theme;
			} else {
				return plugin_dir_path( __DIR__ ) . $template;
			}
		}
		return $template;
	}

	/**
	 * This function substitutes the general templates of a theme for a specific
	 * template for the concert post type. Themes may themselves provide a specialized
	 * template for the post type in which case this function chooses that template.
	 *
	 * @param string $template The template initially chosen by the WordPress template
	 *                         hierarchy.
	 *
	 * @return string The actual template.
	 */
	public function page_template( string $template ): string {
		$slug = self::SLUG;
		if (
			self::SLUG === get_post_type( get_queried_object_id() ) &&
			locate_template( "single-$slug.php" ) !== $template
		) {
			$template = dirname( dirname( __FILE__ ) ) . '/single-concert.php';
		}
		return $template;
	}
}
