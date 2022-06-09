<?php if ( ideapark_woocommerce_on() && ideapark_mod( 'wishlist_page' ) ) { ?>
	<div class="c-header__wishlist">
		<a class="c-header__button-link"
		   href="<?php echo esc_url( get_permalink( apply_filters( 'wpml_object_id', ideapark_mod( 'wishlist_page' ), 'any' ) ) ); ?>"><i class="ip-wishlist c-header__wishlist-icon h-hide-mobile"></i><i class="ip-m-wishlist c-header__wishlist-icon h-hide-desktop"></i><?php echo ideapark_wishlist_info(); ?></a>
	</div>
<?php } ?>