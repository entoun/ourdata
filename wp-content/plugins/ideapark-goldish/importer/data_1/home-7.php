<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$footer_page_id = ( $page = get_page_by_title( 'Footer (white-2)', OBJECT, 'html_block' ) ) ? $page->ID : 0;
$home_page_id   = ( $page = get_page_by_title( 'Home 7' ) ) ? $page->ID : 0;

$mods                                   = [];
$mods['to_top_button']                  = true;
$mods['switch_image_on_hover']          = true;
$mods['to_top_button_color']            = '#704D6D';
$mods['accent_color']                   = '#704D6D';
$mods['header_top_color']               = '#FFFFFF';
$mods['header_top_accent_color']        = '#704D6D';
$mods['header_top_background_color']    = '#000000';
$mods['mobile_header_color']            = '#FFFFFF';
$mods['mobile_header_background_color'] = '#000000';
$mods['logo']                           = '';
$mods['header_blocks_1']                = 'logo=1(top-left)|menu=1(center-center)|buttons=1(top-right)|social=0|other=1(bottom-left)|address=1(bottom-right)|phone=1(bottom-right)|email=0|hours=0|lang=0';

if ( $footer_page_id ) {
	$mods['footer_page'] = $footer_page_id;
}

$options = [];
if ( $home_page_id ) {
	$options['page_on_front'] = $home_page_id;
}

$theme_home = [
	'title'      => __( 'Home 7', 'ideapark-goldish' ),
	'screenshot' => 'home-7.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo1/home-7/',
	'mods'       => $mods,
	'options'    => $options,
];