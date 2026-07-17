<?php
/**
 * The template for displaying dynamic content (Site Builder single/archive layouts).
 *
 * Read-only backward-compat port of Responsive Pro's dynamic-content.php.
 *
 * @package Responsive_Add_Ons
 * @since   3.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $args['layout_id'] ) ) {
	RASB_Legacy_Markup::get_instance()->render_overridden_template( absint( $args['layout_id'] ) );
}
