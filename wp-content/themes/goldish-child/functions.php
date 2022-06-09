<?php
/**
 * Goldish-Child functions and definitions
 *
 * @package goldish-child
 */

/** Enqueue the child theme stylesheet **/
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'goldish-child-style', get_stylesheet_directory_uri() . '/style.css', PHP_INT_MAX );
}, 100 );
