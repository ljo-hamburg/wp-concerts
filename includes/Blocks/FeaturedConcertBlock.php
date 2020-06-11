<?php
/**
 * The `FeaturedConcertBlock` class.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts\Blocks;

use Exception;
use LJO\WPConcerts\Concert;
use LJO\WPConcerts\HTML;

/**
 * The `FeaturedConcertBlock` class registers the featured concert block. The block is
 * rendered on the server side.
 *
 * @package LJO\WPConcerts\Blocks
 */
class FeaturedConcertBlock extends Block {

	/**
	 * The attribute key for the concert that should be displayed.
	 *
	 * @var string
	 */
	public const CONCERT_ID_KEY = 'concertID';

	/**
	 * The attribute key for the background image that should be used.
	 *
	 * @var string
	 */
	public const BACKGROUND_IMAGE_ID_KEY = 'backgroundImageID';

	/**
	 * FeaturedConcertBlock constructor. Creates and registers the block.
	 */
	public function __construct() {
		parent::__construct( 'featured-concert', true );
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
		try {
			$concert = Concert::get( $attributes[ self::CONCERT_ID_KEY ] );
		} catch ( Exception $exception ) {
			return '';
		}
		$background = wp_get_attachment_image_src( $attributes[ self::BACKGROUND_IMAGE_ID_KEY ] );
		if ( $background ) {
			$style = 'background-image: url("' . $background[0] . '");';
		} else {
			$style = '';
		}

		ob_start(); ?>
			<div class="wp-block-wp-concerts-featured-concert">
				<div class="date" style="<?php echo esc_attr( $style ); ?>">
					<div class="overlay"></div>
					<span class="day">
							<?php echo esc_html( $concert->date->format( 'd' ) ); ?>
					</span>
					<span class="month-year">
							<?php echo esc_html( $concert->date->format( 'M, Y' ) ); ?>
					</span>
				</div>
				<div class="content">
					<h3 class="title"><a href="<?php the_permalink( $concert->id ); ?>">
									<?php echo esc_html( get_the_title( $concert->id ) ); ?>
					</a></h3>
					<?php HTML::countdown( $concert->date ); ?>
				</div>
			</div>
		<?php
		return ob_get_clean();
	}
}
