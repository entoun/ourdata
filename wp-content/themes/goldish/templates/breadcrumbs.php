<?php
$position = ! empty( $ideapark_var['position'] ) ? trim( $ideapark_var['position'] ) : 'default';
if ( ideapark_mod( 'header_breadcrumbs' ) && ( $breadcrumb_items = ideapark_breadcrumb_list() ) ) { ?>
	<nav class="c-breadcrumbs">
		<ol class="c-breadcrumbs__list c-breadcrumbs__list--<?php echo esc_attr( $position ); ?>" itemscope
			itemtype="http://schema.org/BreadcrumbList">
			<?php
			$i        = 1;
			foreach ( $breadcrumb_items as $item_index => $item ):
				$title = isset( $item['title'] ) ? $item['title'] : '';
				$link = isset( $item['link'] ) ? $item['link'] : '';
				?>
				<li class="c-breadcrumbs__item <?php ideapark_class( ! $item_index, 'c-breadcrumbs__item--first' ); ?> <?php ideapark_class( $item_index == sizeof( $breadcrumb_items ) - 1, 'c-breadcrumbs__item--last' ); ?>"
					itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" title="<?php echo esc_attr( $title ); ?>" href="<?php echo esc_url( $link ); ?>"><span
							itemprop="name"><?php echo esc_html( $title ); ?></span></a><?php if ( $item_index < sizeof( $breadcrumb_items ) - 1 ) { ?><!--
						--><i class="ip-breadcrumb c-breadcrumbs__separator"><!-- --></i><?php } ?>
					<meta itemprop="position" content="<?php echo esc_attr( $i ); ?>">
				</li>
				<?php
				$i ++;
			endforeach;
			?>
		</ol>
	</nav>
<?php } ?>