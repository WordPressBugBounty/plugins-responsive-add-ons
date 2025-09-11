<?php
/**
 * The template for displaying dynamic content.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Responsive_Add_ons
 * @since 3.3.2
 */

if ( isset( $args['layout_id'] ) ) {
	Responsive_Add_Ons_Site_Builder_Markup::get_instance()->render_overridden_template( absint( $args['layout_id'] ) );
}
