<?php

function ideapark_theme_colors() {
	return [
		'background_color'        => $bg_color = ideapark_mod_hex_color_norm( 'background_color', '#ffffff' ),
		'text_color'              => $text_color = ideapark_mod_hex_color_norm( 'text_color', '#000000' ),
		'text_color_body'         => ideapark_hex_to_rgb_overlay( $bg_color, $text_color, 0.8 ),
		'text_color_light'        => ideapark_hex_to_rgb_overlay( $bg_color, $text_color, 0.646 ),
		'text_color_med_light'    => ideapark_hex_to_rgb_overlay( $bg_color, $text_color, 0.38 ),
		'text_color_extra_light'  => ideapark_hex_to_rgb_overlay( $bg_color, $text_color, 0.13 ),
		'accent_color'            => ideapark_mod_hex_color_norm( 'accent_color', '#C6AD8A' ),
		'accent_background_color' => ideapark_mod_hex_color_norm( 'accent_background_color', '#FDF9F2' ),
	];
}

function ideapark_customize_css( $is_return_value = false ) {

	$custom_css = '';

	/**
	 * @var $text_color                       string
	 * @var $text_color_body                  string
	 * @var $text_color_light                 string
	 * @var $text_color_med_light             string
	 * @var $text_color_extra_light           string
	 * @var $background_color                 string
	 * @var $accent_color                     string
	 * @var $accent_background_color          string
	 */
	extract( ideapark_theme_colors() );

	$lang_postfix        = ideapark_get_lang_postfix();
	$text_color_lighting = ideapark_hex_lighting( $text_color );

	$custom_css .= '
	<style> 
		:root {
			--text-color: ' . esc_attr( $text_color ) . ';
			--text-color-body: ' . esc_attr( $text_color_body ) . ';
			--text-color-light: ' . esc_attr( $text_color_light ) . ';
			--text-color-med-light: ' . esc_attr( $text_color_med_light ) . ';
			--text-color-extra-light: ' . esc_attr( $text_color_extra_light ) . ';
			--text-color-tr: ' . ideapark_hex_to_rgba( $text_color, 0.15 ) . ';
			--background-color: ' . esc_attr( $background_color ) . ';
			--background-color-light: ' . ideapark_hex_to_rgb_overlay( '#FFFFFF', $background_color, 0.5 ) . ';
			--background-color-dark: ' . ideapark_hex_to_rgb_overlay( $background_color, '#000000', 0.03 ) . ';
			--accent-color: ' . esc_attr( $accent_color ) . ';
			--accent-color-dark: ' . ideapark_hex_to_rgb_overlay( $accent_color, '#000000', 0.1 ) . ';
			--accent-background-color: ' . esc_attr( $accent_background_color ) . ';
			--white-color: ' . esc_attr( $text_color_lighting > 128 ? '#000000' : '#FFFFFF' ) . ';
			--smart-color: ' . esc_attr( $text_color_lighting > 128 ? $background_color : $text_color ) . ';
			--star-rating-color: ' . ideapark_mod_hex_color_norm( 'star_rating_color', $text_color ) . ';
			--font-text: "' . esc_attr( str_replace( 'custom-', '', ideapark_mod( 'theme_font_text' . $lang_postfix ) ) ) . '", sans-serif;
			--text-transform: ' . ( ideapark_mod( 'capitalize_headers' ) ? 'capitalize' : 'none' ) . ';
			--logo-size: ' . esc_attr( round( ideapark_mod( 'logo_size' ) ) ) . 'px;
			--logo-size-mobile: ' . esc_attr( round( ideapark_mod( 'logo_size_mobile' ) ) ) . 'px;
			--shadow-color-desktop: ' . ideapark_hex_to_rgba( ideapark_mod_hex_color_norm( 'shadow_color_desktop', '#FFFFFF' ), 0.95 ) . ';
			--search-color-desktop: ' . esc_attr( ideapark_hex_lighting( ideapark_mod_hex_color_norm( 'shadow_color_desktop', '#FFFFFF' ) ) > 128 ? ( $text_color_lighting > 128 ? $background_color : $text_color ) : ( $text_color_lighting > 128 ? $text_color : $background_color ) ) . ';
			--text-align-left: ' . ( ideapark_is_rtl() ? 'right' : 'left' ) . ';
			--text-align-right: ' . ( ideapark_is_rtl() ? 'left' : 'right' ) . ';
			--custom-transform-transition: visibility 0.5s cubic-bezier(0.86, 0, 0.07, 1), opacity 0.5s cubic-bezier(0.86, 0, 0.07, 1), transform 0.5s cubic-bezier(0.86, 0, 0.07, 1), box-shadow 0.5s cubic-bezier(0.86, 0, 0.07, 1);
			--opacity-transition: opacity 0.3s linear, visibility 0.3s linear;
			--opacity-transform-transition: opacity 0.3s linear, visibility 0.3s linear, transform 0.3s ease-out, box-shadow 0.3s ease-out;
			--hover-transition: opacity 0.15s linear, visibility 0.15s linear, color 0.15s linear, border-color 0.15s linear, background-color 0.15s linear, box-shadow 0.15s linear;
			--star-rating-image: url("data:image/svg+xml;base64,' . ideapark_b64enc( '<svg width="14" height="10" fill="' . ideapark_mod_hex_color_norm( 'star_rating_color', $text_color ) . '" xmlns="http://www.w3.org/2000/svg"><path d="M8.956 9.782c.05.153-.132.28-.27.186L5.5 7.798l-3.19 2.168c-.137.093-.32-.033-.269-.187l1.178-3.563L.07 3.99c-.135-.095-.065-.3.103-.302l3.916-.032L5.335.114c.053-.152.28-.152.333 0L6.91 3.658l3.916.035c.168.001.238.206.103.302L7.78 6.217l1.175 3.565z"/></svg>' ) . '");
			--select-image: url("data:image/svg+xml;base64,' . ideapark_b64enc( '<svg width="10" height="7" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M.47 1.53 1.53.47 5 3.94 8.47.47l1.06 1.06L5 6.06.47 1.53z" fill="' . $text_color . '"/></svg>' ) . '");
			--select-ordering-image: url("data:image/svg+xml;base64,' . ideapark_b64enc( '<svg width="6" height="4" viewBox="0 0 6 4" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3 2.551.633.184 0 .816l3 3 3-3-.633-.632L3 2.55Z" fill="' . $text_color_light . '"/></svg>' ) . '");
			
			--icon-divider: "\f10f" /* ip-divider */;
			--icon-zoom: "\f140" /* ip-plus-zoom */;
			--icon-user: "\f152" /* ip-user */;
			--icon-close-small: "\f10d" /* ip-close-small */;
			--icon-check: "\f10b" /* ip-check */;
			--icon-select: "\f111" /* ip-down_arrow */;
			--icon-select-bold: "\f112" /* ip-down */;
			--icon-romb: "\f147" /* ip-romb */;
			--icon-calendar: "\f106" /* ip-calendar */;
			--icon-li: "\f110" /* ip-dot */;
			--icon-submenu: "\f129" /* ip-menu-right */;
			--icon-depth: "\f15c" /* ip-z-depth */;
			--icon-eye-back: "\f114" /* ip-eye-back */;
			--icon-heart-back: "\f11b" /* ip-heart-active */;
			
			--image-background-color: ' . esc_attr( ideapark_mod_hex_color_norm( 'product_image_background_color', 'transparent' ) ) . ';
		}
		
		.owl-nav {
			--disable-color: ' . ideapark_hex_to_rgba( $text_color, 0.2 ) . ';
		}
		
		.c-badge {
			--badge-bgcolor-featured: ' . ideapark_mod_hex_color_norm( 'featured_badge_color', $text_color ) . ';
			--badge-bgcolor-new: ' . ideapark_mod_hex_color_norm( 'new_badge_color', $text_color ) . ';
			--badge-bgcolor-sale: ' . ideapark_mod_hex_color_norm( 'sale_badge_color', $text_color ) . ';
			--badge-bgcolor-outofstock: ' . ideapark_mod_hex_color_norm( 'outofstock_badge_color', $text_color ) . ';
		}
		
		.c-to-top-button {
			--to-top-button-color: ' . ideapark_mod_hex_color_norm( 'to_top_button_color' ) . ';
		}
		
		.c-top-menu__list {
			--top-menu-submenu-color: ' . ideapark_mod_hex_color_norm( 'top_menu_submenu_color', $text_color ) . ';
			--top-menu-submenu-bg-color: ' . ideapark_mod_hex_color_norm( 'top_menu_submenu_bg_color', '#FFFFFF' ) . ';
			--top_menu_submenu_accent_color: ' . ideapark_mod_hex_color_norm( 'top_menu_submenu_accent_color', $accent_color ) . ';
		}
		
		.c-product-grid__item {
			--font-size: ' . esc_attr( ideapark_mod( 'product_font_size' ) ) . 'px;
			--font-size-mobile: ' . esc_attr( round( ideapark_mod( 'product_font_size' ) * 19 / 20 ) ) . 'px;
			--font-size-mobile-2-rows: ' . esc_attr( round( ideapark_mod( 'product_font_size' ) * 17 / 20 ) ) . 'px;
			--font-size-compact: ' . esc_attr( ideapark_mod( 'product_font_size_compact' ) ) . 'px;
			--font-size-compact-mobile: ' . esc_attr( round( ideapark_mod( 'product_font_size_compact' ) * 17 / 18 ) ) . 'px;
		}
		
		.c-product {
			--summary-bg-color: ' . ideapark_mod_hex_color_norm( 'product_summary_background_color', '#FFFFFF' ) . ';
		}
		
		.l-header {
			--top-color: ' . esc_attr( ideapark_mod_hex_color_norm( 'header_top_color', $text_color ) ) . ';
			--top-accent-color: ' . esc_attr( ideapark_mod_hex_color_norm( 'header_top_accent_color', $accent_color ) ) . ';
			--top-background-color: ' . esc_attr( $bg = ideapark_mod_hex_color_norm( 'header_top_background_color', $background_color ) ) . ';
			--top-border-color: ' . ( ideapark_hex_lighting( $bg ) == 255 ? 'currentColor' : 'transparent' ) . ';
			
			--header-color-mobile: ' . ideapark_mod_hex_color_norm( 'mobile_header_color', $text_color ) . ';
			--header-color-bg-mobile: ' . ( $bg = ideapark_mod_hex_color_norm( 'mobile_header_background_color', '#FFFFFF' ) ) . ';
			
			--transparent-header-color: ' . ideapark_mod_hex_color_norm( 'transparent_header_color', '#FFFFFF' ) . ';
			--transparent-header-accent: ' . ideapark_mod_hex_color_norm( 'transparent_header_accent_color', $text_color ) . ';
		}
		
		.woocommerce-store-notice {
			--store-notice-color: ' . ideapark_mod_hex_color_norm( 'store_notice_color' ) . ';
			--store-notice-background-color: ' . ideapark_mod_hex_color_norm( 'store_notice_background_color' ) . ';
		}
		
		input[type=radio],
		input[type=checkbox],
		.woocommerce-widget-layered-nav-list__item,
		.c-ip-attribute-filter__list {
			--border-color: ' . ideapark_hex_to_rgb_overlay( '#FFFFFF', $text_color, 0.2 ) . ';
		}
			
	</style>';

	$custom_css = preg_replace( '~[\r\n]~', '', preg_replace( '~[\t\s]+~', ' ', str_replace( [
		'<style>',
		'</style>'
	], [ '', '' ], $custom_css ) ) );

	if ( $custom_css ) {
		if ( $is_return_value ) {
			return $custom_css;
		} else {
			wp_add_inline_style( 'ideapark-core', $custom_css );
		}
	}

	return '';
}

function ideapark_uniord( $u ) {
	$k  = mb_convert_encoding( $u, 'UCS-2LE', 'UTF-8' );
	$k1 = ord( substr( $k, 0, 1 ) );
	$k2 = ord( substr( $k, 1, 1 ) );

	return $k2 * 256 + $k1;
}

function ideapark_b64enc( $input ) {

	$keyStr = "ABCDEFGHIJKLMNOP" .
	          "QRSTUVWXYZabcdef" .
	          "ghijklmnopqrstuv" .
	          "wxyz0123456789+/" .
	          "=";

	$output = "";
	$i      = 0;

	do {
		$chr1 = ord( substr( $input, $i ++, 1 ) );
		$chr2 = $i < strlen( $input ) ? ord( substr( $input, $i ++, 1 ) ) : null;
		$chr3 = $i < strlen( $input ) ? ord( substr( $input, $i ++, 1 ) ) : null;

		$enc1 = $chr1 >> 2;
		$enc2 = ( ( $chr1 & 3 ) << 4 ) | ( $chr2 >> 4 );
		$enc3 = ( ( $chr2 & 15 ) << 2 ) | ( $chr3 >> 6 );
		$enc4 = $chr3 & 63;

		if ( $chr2 === null ) {
			$enc3 = $enc4 = 64;
		} else if ( $chr3 === null ) {
			$enc4 = 64;
		}

		$output = $output .
		          substr( $keyStr, $enc1, 1 ) .
		          substr( $keyStr, $enc2, 1 ) .
		          substr( $keyStr, $enc3, 1 ) .
		          substr( $keyStr, $enc4, 1 );
		$chr1   = $chr2 = $chr3 = "";
		$enc1   = $enc2 = $enc3 = $enc4 = "";
	} while ( $i < strlen( $input ) );

	return $output;
}