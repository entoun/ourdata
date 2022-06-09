<div
	class="c-header__logo<?php if ( ideapark_mod( 'logo_mobile' ) && ( ideapark_mod( 'logo_sticky' ) || ideapark_mod( 'logo_mobile_sticky' ) ) ) { ?> c-header__logo--sticky<?php } ?>">
	<?php if ( ! is_front_page() ): ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php endif ?>

		<?php if ( ideapark_mod( 'logo__width' ) && ideapark_mod( 'logo__height' ) ) {
			$dimension = ' width="' . ideapark_mod( 'logo__width' ) . '" height="' . ideapark_mod( 'logo__height' ) . '" ';
		} else {
			$dimension = '';
		}

		$logo_url = ideapark_mod( 'logo' );
		?>
		<?php if ( ideapark_mod( 'logo_mobile' ) && $logo_url ) { ?>
			<img <?php echo ideapark_wrap( $dimension ); ?>
				src="<?php echo esc_url( ideapark_mod( 'logo_mobile' ) ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="c-header__logo-img c-header__logo-img--mobile"/>
		<?php } elseif ( $logo_url ) { ?>
			<img <?php echo ideapark_wrap( $dimension ); ?>
				src="<?php echo esc_url( $logo_url ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="c-header__logo-img c-header__logo-img--all"/>
		<?php } else { ?>
			<?php $empty_logo = true; ?>
			<span
				class="c-header__logo-empty"><?php echo esc_html( trim( ideapark_truncate( get_bloginfo( 'name', 'display' ), 10, '' ), " -/.,\r\n\t" ) ); ?></span>
		<?php } ?>

		<?php if ( ideapark_mod( 'logo_mobile_sticky' ) && ideapark_mod( 'logo_mobile' ) && $logo_url ) { ?>
			<img <?php echo ideapark_wrap( $dimension ); ?>
				src="<?php echo esc_url( ideapark_mod( 'logo_mobile_sticky' ) ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="c-header__logo-img c-header__logo-img--sticky"/>
		<?php } elseif ( ideapark_mod( 'logo_sticky' ) && $logo_url ) { ?>
			<img <?php echo ideapark_wrap( $dimension ); ?>
				src="<?php echo esc_url( ideapark_mod( 'logo_sticky' ) ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="c-header__logo-img c-header__logo-img--sticky"/>
		<?php } ?>

		<?php if ( ! is_front_page() ): ?></a><?php endif ?>
</div>