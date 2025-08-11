<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Responsive_Add_ons
 * @since 3.3.0
 */

get_header();

while ( have_posts() ) {
	the_post();
	do_action( 'responsive_site_builder_template' );
}

get_footer();