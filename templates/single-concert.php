<?php
/**
 * Basic template to show a single concert. This template can be overridden by themes
 * that provide their own page template for concerts.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

use Exception;

try {
	$concert = Concert::get( get_the_ID(), false );
} catch ( Exception $e ) {
	wp_die(
		esc_html__(
			'Invalid concert date. This issue cannot be fixed automatically.',
			'wp-concerts'
		)
	);
}

get_header();
echo '<script type="application/ld+json">';
echo wp_json_encode( $concert->get_schema_data() );
echo '</script>';
?>
	<?php if ( $concert->cancelled ) : ?>
		<div class="cancelled">
			<h2><?php esc_html_e( 'The concert was cancelled.', 'wp-concerts' ); ?></h2>
			<div><?php echo esc_html( $concert->cancelled_message ); ?></div>
		</div>
	<?php endif ?>
	<div class="concert-meta">
		<div class="date">
			<?php echo esc_html( $concert->date->format( 'l, j.n.Y' ) ); ?>
		</div>
		<div class="time">
			<?php
			if ( is_null( $concert->end_date() ) ) {
				echo esc_html( $concert->date->format( 'H:i' ) );
			} else {
				echo esc_html(
					sprintf(
					// Translators: 1: Start Time, 2: End Time.
						__( '%1$s – %2$s', 'wp-concerts' ),
						$concert->date->format( 'H:i' ),
						$concert->end_date()->format( 'H:i' )
					)
				);
			}
			?>
		</div>
		<?php if ( $concert->organizer ) : ?>
			<div class="organizer">
				<?php if ( $concert->organizer_url ) : ?>
					<a href="<?php echo esc_url( $concert->organizer_url ); ?>">
							<?php echo esc_html( $concert->organizer ); ?>
					</a>
				<?php else : ?>
					<span><?php echo esc_html( $concert->organizer ); ?></span>
				<?php endif ?>
			</div>
		<?php endif ?>
		<?php if ( $concert->location ) : ?>
			<div class="location">
				<?php if ( $concert->location_url ) : ?>
					<a href="<?php echo esc_url( $concert->location_url ); ?>" class="name">
							<?php echo esc_html( $concert->location ); ?>
					</a>
				<?php else : ?>
					<span class="name"><?php echo esc_html( $concert->location ); ?></span>
				<?php endif ?>
				<span class="extra"><?php echo esc_html( $concert->location_extra ); ?></span>
				<span class="address"><?php echo esc_html( $concert->location_address ); ?></span>
			</div>
		<?php endif ?>
	</div>
	<div class="concert-content">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
</div >
<?php
get_footer();
