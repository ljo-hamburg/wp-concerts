<?php
/**
 * Basic template to show a single concert. This template can be overridden by themes
 * that provide their own page template for concerts.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

get_header();
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
get_footer();
