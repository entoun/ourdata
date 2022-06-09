<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
	<meta name="format-detection" content="telephone=no"/>
	<link rel="profile" href="//gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/header-search' ); ?>

<?php if ( ideapark_mod( 'store_notice' ) == 'top' && function_exists( 'woocommerce_demo_store' ) ) {
	woocommerce_demo_store();
	ideapark_ra( 'wp_footer', 'woocommerce_demo_store' );
}

$header_type        = ideapark_mod( 'header_type' );
$header_type_mobile = ideapark_mod( 'header_type_mobile' );

$mobile_header_buttons_cnt = 0;

if ( $header_type_mobile == 'header-type-mobile-2' ) {
	$header_blocks = ideapark_parse_checklist( ideapark_mod( 'header_buttons_mobile' ) );
	foreach ( $header_blocks as $block_index => $enabled ) {
		if ( $enabled ) {
			$mobile_header_buttons_cnt ++;
		}
	}
}

global $ideapark_advert_bar;
$is_advert_bar_above = ideapark_mod( 'header_advert_bar_placement' ) == 'above';
$is_advert_bar_below = ideapark_mod( 'header_advert_bar_placement' ) == 'below';

?>
<div class="l-wrap">
	<?php if ( $is_advert_bar_above ) {
		echo ideapark_wrap( $ideapark_advert_bar, '<div class="l-section"><div class="c-header__advert_bar c-header__advert_bar--above">', '</div></div>' );
	} ?>
	<header class="l-section l-header" id="main-header">
		<?php
		// ========================================================= MOBILE =========================================================
		?>
		<div
			class="c-header__outer c-header__outer--mobile <?php if ( ideapark_is_transparent_header() ) { ?> c-header__outer--tr<?php } ?> c-header__outer--<?php echo esc_attr( $header_type ); ?> c-header__outer--<?php echo esc_attr( $header_type_mobile ); ?>">
			<div
				class="c-header <?php if ( ideapark_is_transparent_header() ) { ?> c-header--tr<?php } ?> c-header--<?php echo esc_attr( $header_type ); ?> c-header--<?php echo esc_attr( $header_type_mobile ); ?> c-header--buttons-<?php echo esc_attr( $mobile_header_buttons_cnt ); ?> c-header--mobile js-header-mobile">
				<div class="c-header__row">
					<?php if ( $header_type_mobile == 'header-type-mobile-1' ) { ?>
						<?php get_template_part( 'templates/header-logo-mobile' ); ?>
						<?php get_template_part( 'templates/header-mobile-menu-button' ); ?>
					<?php } else { ?>
						<?php get_template_part( 'templates/header-mobile-menu-button' ); ?>
						<?php get_template_part( 'templates/header-logo-mobile' ); ?>
						<?php get_template_part( 'templates/header-buttons-mobile' ); ?>
					<?php } ?>
				</div>
			</div>
		</div>

		<div
			class="c-header__menu c-header--mobile c-header__menu--<?php echo esc_attr( $header_type_mobile ); ?> js-mobile-menu">
			<div class="c-header__menu-shadow"></div>
			<div class="c-header__menu-buttons">
				<button type="button" class="h-cb h-cb--svg c-header__menu-back js-mobile-menu-back"><i
						class="ip-menu-left c-header__menu-back-svg"></i>
				</button>
				<button type="button" class="h-cb h-cb--svg c-header__menu-close js-mobile-menu-close"><i
						class="ip-close-rect c-header__menu-close-svg"></i></button>
			</div>
			<div class="c-header__menu-content">
				<div class="c-header__menu-wrap js-mobile-menu-wrap"></div>
				<?php get_template_part( 'templates/header-mobile-top-menu' ); ?>
				<?php get_template_part( 'templates/header-mobile-blocks' ); ?>
			</div>
			<?php get_template_part( 'templates/header-buttons-mobile-bottom' ); ?>
		</div>
		<?php if ( $is_advert_bar_below ) {
			echo ideapark_wrap( $ideapark_advert_bar, '<div class="c-header__advert_bar c-header__advert_bar--below c-header--mobile">', '</div>' );
		} ?>

		<?php
		// ========================================================= DESKTOP =========================================================
		?>
		<?php get_template_part( 'templates/header-desktop' ); ?>

		<?php if ( $is_advert_bar_below ) {
			echo ideapark_wrap( $ideapark_advert_bar, '<div class="c-header__advert_bar c-header__advert_bar--below c-header--desktop">', '</div>' );
		} ?>


		<div class="c-header--desktop l-section__container js-simple-container"></div>

		<?php if ( ideapark_mod( 'shop_modal' ) ) { ?>
			<div
				class="c-header__callback-popup c-header__callback-popup--disabled js-callback-popup js-quickview-popup">
				<div class="c-header__callback-bg js-callback-close"></div>
				<div class="c-header__callback-wrap c-header__callback-wrap--quickview">
					<div class="js-quickview-container"></div>
					<button type="button" class="h-cb h-cb--svg c-header__callback-close js-callback-close"><i
							class="ip-close-rect"></i></button>
				</div>
			</div>
		<?php } ?>
		<div
			class="c-header__callback-popup c-header__callback-popup--disabled js-callback-popup js-attribute-hint-popup">
			<div class="c-header__callback-wrap c-header__callback-wrap--hint">
				<div
					class="c-header__callback-content c-header__callback-content--hint js-attribute-hint-container"></div>
				<button type="button" class="h-cb h-cb--svg c-header__callback-close js-callback-close"><i
						class="ip-close-rect"></i></button>
			</div>
		</div>
	</header>

	<div class="l-inner">
