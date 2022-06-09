<?php
/**
 * Single Product Sale Flash
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/sale-flash.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see           https://docs.woocommerce.com/document/template-structure/
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       1.6.4
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;

if ( ideapark_mod( 'sale_badge_text' ) && $product->is_on_sale() ) : ?>
	<?php
	$regular_price = $product->get_regular_price();
	$sale_price = $product->get_sale_price();
	?>
	<?php if ( $regular_price > 0 ) { ?>
		<?php $percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 ); ?>
		<?php echo apply_filters( 'woocommerce_sale_flash', '<span class="c-badge c-badge--sale">-' . esc_html( $percentage ) . '%</span>', $post, $product ); ?>
	<?php } else { ?>
		<?php echo apply_filters( 'woocommerce_sale_flash', '<span class="c-badge c-badge--sale">' . esc_html( ideapark_mod( 'sale_badge_text' ) ) . '</span>', $post, $product ); ?>
	<?php } ?>

<?php endif; ?>
