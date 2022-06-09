<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$footer_page_id = ( $page = get_page_by_title( 'Footer (green)', OBJECT, 'html_block' ) ) ? $page->ID : 0;
$home_page_id   = ( $page = get_page_by_title( 'Home 4' ) ) ? $page->ID : 0;

$mods                            = [];
$mods['transparent_header']      = true;
$mods['switch_image_on_hover']   = true;
$mods['product_buttons_layout']  = 'buttons-2';
$mods['accent_color']            = $mods['featured_badge_text'] = $mods['new_badge_color'] = '#C08467';
$mods['hidden_product_category'] = 0;
$mods['header_blocks_1']         = 'logo=1(center-left)|menu=1(center-center)|buttons=1(center-right)|social=0|other=0|address=0|phone=0|email=0|hours=0|lang=0';

if ( $footer_page_id ) {
	$mods['footer_page'] = $footer_page_id;
}

$options = [];
if ( $home_page_id ) {
	$options['page_on_front'] = $home_page_id;
}

$theme_home = [
	'title'      => __( 'Home 4', 'ideapark-goldish' ),
	'screenshot' => 'home-4.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo1/home-4/',
	'mods'       => $mods,
	'options'    => $options,
];