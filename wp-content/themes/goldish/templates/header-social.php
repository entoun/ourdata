<?php ob_start();
ideapark_get_template_part( 'templates/soc', [ 'set' => 'o-' ] );
$content = trim( ob_get_clean() ); ?>
<?php if ( $content ) { ?>
	<div class="c-header__top-row-item c-header__top-row-item--social">
		<?php echo ideapark_wrap( $content ); ?>
	</div>
<?php } ?>