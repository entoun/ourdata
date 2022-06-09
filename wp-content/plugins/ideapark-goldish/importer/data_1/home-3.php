<?php
defined( 'ABSPATH' ) || exit;

global $theme_home;

$footer_page_id = ( $page = get_page_by_title( 'Footer (white)', OBJECT, 'html_block' ) ) ? $page->ID : 0;
$home_page_id   = ( $page = get_page_by_title( 'Home 3' ) ) ? $page->ID : 0;

$mods                                   = [];
$mods['header_top_color']               = '#FFFFFF';
$mods['header_top_accent_color']        = '#181818';
$mods['header_top_background_color']    = '#C6AD8A';
$mods['mobile_header_color']            = '#FFFFFF';
$mods['mobile_header_background_color'] = '#C6AD8A';
$mods['logo']                           = '';
$mods['header_blocks_1']                = 'social=1(top-left)|logo=1(top-center)|menu=1(bottom-center)|buttons=1(top-right)|other=0|address=0|phone=1(bottom-left)|email=0|hours=1(bottom-left)|lang=1(bottom-right)';

if ( $footer_page_id ) {
	$mods['footer_page'] = $footer_page_id;
}

$options = [];
if ( $home_page_id ) {
	$options['page_on_front'] = $home_page_id;
}

$theme_home = [
	'title'      => __( 'Home 3', 'ideapark-goldish' ),
	'screenshot' => 'home-3.jpg',
	'url'        => 'https://parkofideas.com/goldish/demo1/home-3/',
	'mods'       => $mods,
	'options'    => $options,
];