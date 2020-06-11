<?php
/**
 * Basic template to show multiple concerts. This template can be overridden by themes
 * that provide their own page template for concert archives.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts;

get_header();
while ( have_posts() ) :
	the_post();
	try {
		$concert = Concert::get( get_the_ID() );
		$concert->html();
	} catch ( \Exception $exception ) { // @codingStandardsIgnoreLine
		// Skip concerts with errors.
	}
endwhile;
get_footer();
