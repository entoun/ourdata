<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$home_page_id   = ( $page = get_page_by_title( 'Home 2' ) ) ? $page->ID : 0;

$mods                       = [];
$mods['to_top_button']      = true;
$mods['switch_image_on_hover']  = true;
$mods['header_blocks_1']    = 'logo=1(top-left)|menu=1(center-center)|buttons=1(top-right)|social=0|other=1(bottom-left)|address=1(bottom-right)|phone=1(bottom-right)|email=0|hours=0|lang=0';
$mods['transparent_header'] = true;

$options = [];
if ( $home_page_id ) {
	$options['page_on_front'] = $home_page_id;
}

$theme_home = [
	'title'      => __( 'Home 2', 'ideapark-goldish' ),
	'screenshot' => 'home-2.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo1/home-2/',
	'mods'       => $mods,
	'options'    => $options,
];