<div
	class="c-header__logo<?php if ( ideapark_mod( 'logo' ) && ideapark_mod( 'logo_sticky' ) ) { ?> c-header__logo--sticky<?php } ?> <?php ideapark_class( ideapark_mod( 'sticky_menu_desktop' ) && ideapark_mod( 'sticky_logo_desktop_hide' ), 'c-header__logo--sticky-hide' ); ?>">
	<?php if ( ! is_front_page() ): ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php endif ?>

		<?php if ( ideapark_mod( 'logo__width' ) && ideapark_mod( 'logo__height' ) ) {
			$dimension = ' width="' . ideapark_mod( 'logo__width' ) . '" height="' . ideapark_mod( 'logo__height' ) . '" ';
		} else {
			$dimension = '';
		}

		$logo_url = ideapark_mod( 'logo' );
		?>
		<?php if ( $logo_url ) { ?>
			<img <?php echo ideapark_wrap( $dimension ); ?>
				src="<?php echo esc_url( $logo_url ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="c-header__logo-img c-header__logo-img--desktop"/>
		<?php } else { ?>
			<?php $empty_logo = true; ?>
			<span
				class="c-header__logo-empty"><?php echo esc_html( trim( ideapark_truncate( get_bloginfo( 'name' ), 10, '' ), " -/.,\r\n\t" ) ); ?></span>
		<?php } ?>

		<?php if ( ideapark_mod( 'logo_sticky' ) ) { ?>
			<img <?php echo ideapark_wrap( $dimension ); ?>
				src="<?php echo esc_url( ideapark_mod( 'logo_sticky' ) ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="c-header__logo-img c-header__logo-img--sticky"/>
		<?php } ?>

		<?php if ( empty( $empty_logo ) && ideapark_mod( 'sticky_menu_desktop' ) && ideapark_mod( 'sticky_logo_desktop_hide' ) && ideapark_mod( 'sticky_logo_desktop_hide_text' ) ) { ?>
			<span
				class="c-header__logo-empty c-header__logo-hidden"><?php echo esc_html( trim( ideapark_mod( 'sticky_logo_desktop_hide_text' ) ) ); ?></span>
		<?php } ?>

		<?php if ( ! is_front_page() ): ?></a><?php endif ?>
</div>

