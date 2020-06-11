<?php
/**
 * The `NextConcertsBlock` class.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts\Blocks;

use LJO\WPConcerts\Concert;
use LJO\WPConcerts\ConcertPostType;

/**
 * Class NextConcertsBlock. Creates and registers the next concerts block.
 *
 * @package LJO\WPConcerts\Blocks
 */
class NextConcertsBlock extends Block {

	/**
	 * The name of the block.
	 *
	 * @var string
	 */
	public const NAME = 'next-concerts';

	/**
	 * NextConcertsBlock constructor. Creates and registers the block.
	 */
	public function __construct() {
		parent::__construct( self::NAME, true );
	}

	/**
	 * Returns the block's attributes to be registered on the server side. This is
	 * required for server side rendering.
	 *
	 * @return array
	 */
	public function get_extra_args(): array {
		return array(
			'attributes' => array(
				'count' => array(
					'type'    => 'integer',
					'default' => 3,
				),
			),
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array  $attributes The block's attributes.
	 * @param string $content The block's content (ignored).
	 *
	 * @return string
	 */
	public function render( array $attributes, string $content ): string {
		$count = $attributes['count'];
		$args  = array(
			'post_type'   => ConcertPostType::SLUG,
			'numberposts' => $count,
			'order'       => 'ASC',
			'orderby'     => 'meta_value',
			'meta_key'    => ConcertPostType::DATE_META_KEY, // @codingStandardsIgnoreLine
			'meta_query'  => array( // @codingStandardsIgnoreLine
				array(
					'key'     => ConcertPostType::DATE_META_KEY,
					'value'   => gmdate( DATE_RFC3339 ),
					'compare' => '>=',
				),
			),
		);
		$posts = get_posts( $args );
		$block = '<div class="wp-block-wp-concerts-next-concerts">';
		foreach ( $posts as $post ) {
			$concert = Concert::get( $post->ID );
			$block  .= $concert->html( true, false );
		}
		wp_reset_postdata();
		$block .= '</div>';
		return $block;
	}
}
