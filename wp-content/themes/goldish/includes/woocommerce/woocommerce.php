<?php

if ( ! function_exists( 'ideapark_setup_woocommerce' ) ) {
	function ideapark_setup_woocommerce() {
		if ( ( ideapark_is_requset( 'frontend' ) || ideapark_is_elementor_preview() ) && ideapark_woocommerce_on() ) {

			if ( ideapark_is_elementor_preview() ) {
				WC()->frontend_includes();
			}

			/* All WC pages */

			if ( ideapark_mod( 'disable_purchase' ) ) {
				add_filter( 'woocommerce_is_purchasable', '__return_false' );
			}

			if ( ideapark_mod( 'hidden_product_category' ) && ( ! is_admin() || IDEAPARK_IS_AJAX_SEARCH ) ) {

				$terms = get_terms( [
					'taxonomy' => 'product_cat',
					'parent'   => ideapark_mod( 'hidden_product_category' )
				] );

				$category_ids = [ ideapark_mod( 'hidden_product_category' ) ];
				foreach ( $terms as $term ) {
					$category_ids[] = $term->term_id;
				}

				add_filter( 'woocommerce_product_related_posts_query', function ( $query ) use ( $category_ids ) {
					global $wpdb;
					$query['join']  .= " LEFT JOIN (SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (" . implode( ',', $category_ids ) . ") ) AS exclude_hidden_join ON exclude_hidden_join.object_id = p.ID ";
					$query['where'] .= " AND exclude_hidden_join.object_id IS NULL";

					return $query;
				} );

				add_action( 'woocommerce_product_query', function ( $q ) use ( $category_ids ) {
					$tax_query   = (array) $q->get( 'tax_query' );
					$tax_query[] = [
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $category_ids,
						'operator' => 'NOT IN'
					];
					$q->set( 'tax_query', $tax_query );

					return $q;
				} );

				add_action( 'woocommerce_shortcode_products_query', function ( $q ) use ( $category_ids ) {
					if ( ! isset( $q['tax_query'] ) || ! is_array( $q['tax_query'] ) ) {
						$q['tax_query'] = [];
					}
					$q['tax_query'][] = [
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $category_ids,
						'operator' => 'NOT IN'
					];

					return $q;
				} );

				add_filter( 'get_terms_args', function ( $params ) use ( $category_ids ) {
					if ( ! is_admin() && $params['taxonomy'] == [ 'product_cat' ] ) {
						$params['exclude'] = implode( ',', $category_ids );
					}

					return $params;
				} );

				add_filter( 'get_the_terms', function ( $terms, $post_ID, $taxonomy ) use ( $category_ids ) {
					if ( is_product() && $taxonomy == "product_cat" ) {
						foreach ( $terms as $key => $term ) {
							if ( in_array( $term->term_id, $category_ids ) ) {
								unset( $terms[ $key ] );
							}
						}
					}

					return $terms;
				}, 11, 3 );
			}

			ideapark_ra( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
			ideapark_rf( 'woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories' );
			ideapark_ra( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
			ideapark_ra( 'woocommerce_before_lost_password_form', 'woocommerce_output_all_notices', 10 );
			ideapark_ra( 'woocommerce_before_reset_password_form', 'woocommerce_output_all_notices', 10 );
			ideapark_ra( 'woocommerce_before_customer_login_form', 'woocommerce_output_all_notices', 10 );
			ideapark_ra( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

			add_action( 'woocommerce_after_page_header', 'woocommerce_output_all_notices', 10 );

			/* Products loop */

			ideapark_ra( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
			ideapark_ra( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
			ideapark_ra( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
			ideapark_ra( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
			ideapark_ra( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			ideapark_ra( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
			ideapark_ra( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			ideapark_ra( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

			add_action( 'woocommerce_before_main_content', function () {
				if ( is_archive() ) {
					wc_get_template( 'global/ordering.php' );
				}
			}, 5 );

			add_action( 'woocommerce_before_shop_loop', 'ideapark_woocommerce_search_form', 30 );
			add_action( 'woocommerce_no_products_found', 'ideapark_woocommerce_search_form', 9 );

			add_action( 'woocommerce_before_shop_loop_item_title', function () { ?><div class="c-product-grid__thumb-wrap c-product-grid__thumb-wrap--<?php if ( ideapark_mod( 'shop_modal' ) || ideapark_mod( 'wishlist_page' ) && ideapark_mod( 'wishlist_grid_button' ) ) { ?>buttons<?php } else { ?>no-buttons<?php } ?>"><?php }, 6 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'ideapark_loop_product_thumbnail', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11 );

		add_action( 'woocommerce_before_shop_loop_item_title', function () { ?>
			<div class="c-product-grid__badges c-badge__list"><?php }, 15 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'ideapark_woocommerce_show_product_loop_badges', 15 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 15 );
			if ( ideapark_mod( 'outofstock_badge_text' ) ) {
				add_action( 'woocommerce_before_shop_loop_item_title', 'ideapark_stock_badge', 15 );
			}
		add_action( 'woocommerce_before_shop_loop_item_title', function () { ?></div><!-- .c-product-grid__badges --><?php }, 15 );

			add_action( 'woocommerce_before_shop_loop_item_title', 'ideapark_template_product_buttons', 20 );
			add_action( 'woocommerce_before_shop_loop_item_title', function () { ?></div><!-- .c-product-grid__thumb-wrap --><?php }, 50 );
			if ( ideapark_mod( 'show_add_to_cart' ) ) {
				add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 55 );
			}
			add_action( 'woocommerce_before_shop_loop_item_title', function () { ?><div class="c-product-grid__details <?php echo ideapark_grid_text_class( 'c-product-grid__details' ); ?>"><div class="c-product-grid__title-wrap"><?php }, 100 );
			add_action( 'woocommerce_shop_loop_item_title', 'ideapark_cut_product_categories', 8 );
			add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9 );
			add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11 );
			add_action( 'woocommerce_after_shop_loop_item_title', 'ideapark_template_short_description', 3 );
			if ( ideapark_mod( 'short_description_link' ) ) {
				add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 2 );
				add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 4 );
			}

		add_action( 'woocommerce_after_shop_loop_item_title', function () { ?></div>
			<!-- .c-product-grid__title-wrap -->
			<div class="c-product-grid__price-wrap"><?php }, 50 );
			if ( ideapark_mod( 'product_brand_attribute' ) && taxonomy_exists( ideapark_mod( 'product_brand_attribute' ) ) ) {
				if ( ideapark_mod( 'show_product_page_brand' ) ) {
					add_action( 'woocommerce_product_meta_end', 'ideapark_template_brand_meta' );
				}

				if ( ideapark_mod( 'show_cart_page_brand' ) ) {
					add_filter( 'woocommerce_widget_cart_item_quantity', 'ideapark_cart_mini_brand', 1, 3 );
					add_action( 'woocommerce_after_cart_item_name', 'ideapark_cart_brand', 10, 2 );
				}
			}
			add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 55 );
		add_action( 'woocommerce_after_shop_loop_item_title', function () { ?></div>
			<!-- .c-product-grid__price-wrap --><?php }, 60 );

			if ( ideapark_mod( 'show_color_variations' ) && ideapark_mod( 'product_color_attribute' ) && function_exists( 'wvs_wc_product_has_attribute_type' ) && taxonomy_exists( ideapark_mod( 'product_color_attribute' ) ) && ( wvs_wc_product_has_attribute_type( 'color', ideapark_mod( 'product_color_attribute' ) ) || wvs_wc_product_has_attribute_type( 'image', ideapark_mod( 'product_color_attribute' ) ) ) ) {
				add_action( 'woocommerce_after_shop_loop_item_title', 'ideapark_grid_color_attributes', 63 );
			}

			if ( ideapark_mod( 'product_preview_rating' ) ) {
				add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 65 );
			}
			add_action( 'woocommerce_after_shop_loop_item_title', function () { ?></div><!-- .c-product-grid__details --><?php }, 70 );

			add_action( 'woocommerce_archive_description', function () { ?><div class="<?php if ( ideapark_mod( 'category_description_position' ) == 'below' ) { ?>l-section <?php } ?>entry-content c-product-grid__cat-desc c-product-grid__cat-desc--<?php echo esc_attr( ideapark_mod( 'category_description_position' ) ); ?>"><?php }, 9 );
			add_action( 'woocommerce_archive_description', function () { ?></div><?php }, 11 );


			add_action( 'woocommerce_before_subcategory_title', function () { ?><span class="c-sub-categories__thumb-wrap <?php ideapark_class( ideapark_mod( 'show_subcat_in_header' ), 'c-sub-categories__thumb-wrap--header', 'c-sub-categories__thumb-wrap--content' ); ?>"><?php }, 9 );
			add_action( 'woocommerce_before_subcategory_title', function () { ?></span><?php }, 11 );

			if ( ! ideapark_mod( 'show_subcat_in_header' ) ) {
				add_action( 'woocommerce_before_subcategory', function () { ?><span class="c-sub-categories__item-wrap"><?php }, 15 );
				add_action( 'woocommerce_after_subcategory', function () { ?></span><?php }, 5 );
			}

			/* Product page */

			add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
				$args['posts_per_page'] = (int) ideapark_mod( 'related_product_number' );

				return $args;
			}, 100 );

			if ( ideapark_mod( 'related_product_header' ) ) {
				add_filter( 'woocommerce_product_related_products_heading', function ( $header ) {
					return esc_html( ideapark_mod( 'related_product_header' ) );
				}, 100 );
			}

			if ( ideapark_mod( 'upsells_product_header' ) ) {
				add_filter( 'woocommerce_product_upsells_products_heading', function ( $header ) {
					return esc_html( ideapark_mod( 'upsells_product_header' ) );
				}, 100 );
			}

			if ( ideapark_mod( 'cross_sells_product_header' ) ) {
				add_filter( 'woocommerce_product_cross_sells_products_heading', function ( $header ) {
					return esc_html( ideapark_mod( 'cross_sells_product_header' ) );
				}, 100 );
			}

			add_action( 'woocommerce_before_single_product', function () {
				global $product;
				if ( isset( $product ) && $product->is_type( 'variable' ) && is_product() && ideapark_mod( 'hide_variable_price_range' ) ) {

				} else {
					ideapark_ra( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
					add_filter( 'woocommerce_get_price_html', function ( $html ) {
						if ( ideapark_mod( '_is_product_loop' ) ) {
							return $html;
						} else {
							ob_start();
							woocommerce_show_product_loop_sale_flash();
							$sale_badge = trim( ob_get_clean() );

							return $sale_badge . $html;
						}
					} );
				}
			} );

			add_filter( 'woocommerce_post_class', function ( $classes ) {
				if ( is_product() && ! ideapark_mod( '_is_product_loop' ) && ! ideapark_mod( '_is_product_set' ) ) {
					ideapark_mod_set_temp( '_is_product_set', true );
					$ip_classes = [ 'c-product', 'c-product--' . ideapark_mod( 'product_page_layout' ), 'l-section' ];
					if ( ideapark_mod( 'product_bottom_page' ) ) {
						$ip_classes[] = 'c-product--bottom-block';
					}
					switch ( ideapark_mod( 'product_page_layout' ) ) {
						case 'layout-1':
							$ip_classes[] = 'l-section--container';
							break;

						case 'layout-2':
//							$ip_classes[] = 'l-section--container-wide';
							break;
					}

					return array_merge( $ip_classes, $classes );
				} else {
					return $classes;
				}
			}, 100 );
			if ( ideapark_mod( 'hide_variable_price_range' ) ) {
				add_filter( 'woocommerce_get_price_html', function ( $price, $product ) {
					if ( $product->is_type( 'variable' ) && is_product() ) {
						return '';
					}

					return $price;
				}, 99, 2 );

				add_filter( 'woocommerce_show_variation_price', '__return_true' );
			}

			ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 7 );

			add_action( 'woocommerce_before_single_product_summary', function () { ?><div class="c-product__gallery"><?php }, 5 );
			add_action( 'woocommerce_before_single_product_summary', function () { ?></div><!-- .c-product__gallery --><?php }, 50 );
			add_action( 'woocommerce_before_single_product_summary', function () { ?><div class="c-badge__list c-product__badges"><?php }, 8 );
			add_action( 'woocommerce_before_single_product_summary', 'ideapark_woocommerce_show_product_loop_badges', 9 );
			add_action( 'woocommerce_before_single_product_summary', function () { ?></div><!-- .c-product__badges --><?php }, 12 );
			ideapark_ra( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
			ideapark_ra( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
			add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 19 );

			add_filter( 'wc_get_template', function ( $template, $template_name, $args ) {
				if ( $template_name == 'single-product/related.php' ) {
					ideapark_mod_set_temp( '_products_count', sizeof( $args['related_products'] ) );
					ideapark_mod_set_temp( '_product_carousel_class', 'c-product-grid__list--carousel js-product-grid-carousel h-carousel h-carousel--border h-carousel--round h-carousel--hover h-carousel--default-dots h-carousel--flex' );
					ideapark_mod_set_temp( '_product_carousel_data', '' );
				} elseif ( $template_name == 'single-product/up-sells.php' ) {
					ideapark_mod_set_temp( '_products_count', sizeof( $args['upsells'] ) );
					ideapark_mod_set_temp( '_product_carousel_class', 'c-product-grid__list--carousel js-product-grid-carousel h-carousel h-carousel--border h-carousel--round h-carousel--hover h-carousel--default-dots h-carousel--flex' );
					ideapark_mod_set_temp( '_product_carousel_data', '' );
				} elseif ( $template_name == 'cart/cross-sells.php' ) {
					ideapark_mod_set_temp( '_products_count', sizeof( $args['cross_sells'] ) );
					ideapark_mod_set_temp( '_product_carousel_class', 'c-product-grid__list--carousel js-product-grid-carousel h-carousel h-carousel--border h-carousel--round h-carousel--hover h-carousel--default-dots h-carousel--flex' );
					ideapark_mod_set_temp( '_product_carousel_data', '' );
				}

				return $template;
			}, 10, 3 );


			if ( ! IDEAPARK_IS_AJAX_QUICKVIEW ) {
				switch ( ideapark_mod( 'product_page_layout' ) ) {
					case 'layout-1':
						add_action( 'woocommerce_before_single_product_summary', function () {
							get_template_part( 'templates/breadcrumbs' );
						}, 2 );
						add_action( 'woocommerce_before_single_product_summary', function () { ?><div class="c-product__wrap c-product__wrap--layout-1"><?php }, 2 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-1"><?php }, 2 );
					add_action( 'woocommerce_single_product_summary', function () { ?>
						<div class="c-product__sub-wrap">
						<div class="c-product__sub-col-1"><?php }, 9 );
						if ( ideapark_mod( 'product_brand_attribute' ) && ideapark_mod( 'show_product_page_brand' ) && ideapark_mod( 'show_product_page_brand_logo' ) ) {
							add_action( 'woocommerce_single_product_summary', 'ideapark_product_brand_logo', 9 );
						}
					add_action( 'woocommerce_single_product_summary', function () { ?></div>
						<!-- .c-product__sub-col-1 -->
						<div class="c-product__sub-col-2"><?php }, 39 );
					add_action( 'woocommerce_single_product_summary', function () { ?></div>
						<!-- .c-product__sub-col-2 --></div><!-- .c-product__sub-wrap --><?php }, 45 );
						add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 16 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						</div><!-- .c-product__col-1 --><?php }, 99 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-2"><?php }, 100 );
					add_action( 'woocommerce_after_single_product_summary', function () { ?></div>
						<!-- .c-product__col-2 --><?php }, 10 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__wrap --><?php }, 15 );
						add_action( 'woocommerce_single_product_summary', function () { ?><div class="c-product__atc-wrap<?php if ( ideapark_mod( 'hide_stock_info' ) ) { ?> c-product__atc-wrap--hide-stock<?php } ?>"><?php }, 29 );
						add_action( 'woocommerce_single_product_summary', function () { ?></div><!-- .c-product__atc-wrap --><?php }, 31 );

						break;


					case 'layout-2':
						add_action( 'woocommerce_single_product_summary', function () {
							get_template_part( 'templates/breadcrumbs' );
						}, 4 );
						add_action( 'woocommerce_before_single_product_summary', function () { ?><div class="c-product__wrap c-product__wrap--layout-2"><?php }, 1 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-1">
						<div class="js-sticky-sidebar-nearby"><?php }, 2 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?></div>
						<!-- .c-product__col-1 --></div><!-- .js-sticky-sidebar-nearby --><?php }, 99 );
					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-2">
						<div data-no-offset="yes" class="js-sticky-sidebar"><?php }, 100 );
						if ( ideapark_mod( 'product_brand_attribute' ) && ideapark_mod( 'show_product_page_brand' ) ) {
							add_action( 'woocommerce_single_product_summary', 'ideapark_product_brand_logo', 9 );
						}
						add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 55 );
					add_action( 'woocommerce_after_single_product_summary', function () { ?></div>
						<!-- .c-product__col-2 --></div><!-- .js-sticky-sidebar --><?php }, 10 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__wrap --><?php }, 15 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?><div class="l-section__container-wide"><?php }, 16 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .l-section__container-wide --><?php }, 99 );
						add_action( 'woocommerce_single_product_summary', function () { ?><div class="c-product__atc-wrap<?php if ( ideapark_mod( 'hide_stock_info' ) ) { ?> c-product__atc-wrap--hide-stock<?php } ?>"><?php }, 29 );
						add_action( 'woocommerce_single_product_summary', function () { ?></div><!-- .c-product__atc-wrap --><?php }, 31 );
						break;


					case 'layout-3':
						add_action( 'woocommerce_before_single_product_summary', function () { ?>
							<div class="c-product__wrap c-product__wrap--layout-3"><?php }, 2 );

					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-2"><?php }, 3 );

					add_action( 'woocommerce_before_single_product_summary', function () { ?></div><!-- .c-product__col-2 --><?php }, 99 );

					add_action( 'woocommerce_before_single_product_summary', function () { ?>
						<div class="c-product__col-1"><?php }, 100 );

						add_action( 'woocommerce_before_single_product_summary', function () {
							get_template_part( 'templates/breadcrumbs' );
						}, 101 );

					add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__col-1 --><?php }, 1 );
					add_action( 'woocommerce_after_single_product_summary', function () { ?>
						<div class="c-product__col-3"><?php }, 2 );

						if ( ideapark_mod( 'product_brand_attribute' ) && ideapark_mod( 'show_product_page_brand' ) ) {
							add_action( 'woocommerce_after_single_product_summary', 'ideapark_product_brand_logo', 4 );
						}

						ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
						add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_rating', 5 );

					add_action( 'woocommerce_after_single_product_summary', function () { ?>
						<div
							class="c-product__atc-wrap<?php if ( ideapark_mod( 'hide_stock_info' ) ) { ?> c-product__atc-wrap--hide-stock<?php } ?>"><?php }, 6 );
						ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
						add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_add_to_cart', 7 );
					add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__atc-wrap --><?php }, 8 );

					add_action( 'woocommerce_after_single_product_summary', function () { ?>
						</div><!-- .c-product__col-3 --><?php }, 10 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__wrap --><?php }, 11 );

						ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
						add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_meta', 12 );

						add_action( 'woocommerce_after_single_product_summary', function () { ?><div class="c-product__tabs-row"><div class="c-product__tabs-col-1"><?php }, 13 );
						add_action( 'woocommerce_after_single_product_summary', 'ideapark_tabs_list', 14 );
					add_action( 'woocommerce_after_single_product_summary', function () { ?></div>
						<!-- .c-product__tabs-col-1 -->
						<div class="c-product__tabs-col-2"><?php }, 15 );
						add_action( 'woocommerce_after_single_product_summary', 'ideapark_tab_reviews', 16 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .c-product__tabs-col-2 --></div><!-- .c-product__tabs-row --><?php }, 17 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?><div class="l-section__container-wide"><?php }, 18 );
						add_action( 'woocommerce_after_single_product_summary', function () { ?></div><!-- .l-section__container-wide --><?php }, 99 );
						break;
				}
			} else {
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_loop_product_link_open', 4 );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_loop_product_link_close', 6 );
				add_action( 'woocommerce_single_product_summary', function () { ?><div class="c-product__atc-wrap<?php if ( ideapark_mod( 'hide_stock_info' ) ) { ?> c-product__atc-wrap--hide-stock<?php } ?>"><?php }, 29 );
				add_action( 'woocommerce_single_product_summary', function () { ?></div><!-- .c-product__atc-wrap --><?php }, 31 );
				ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );


			}


			ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 8 );

			ideapark_ra( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
			add_action( 'woocommerce_single_product_summary', function () { ?><div class="c-product__buttons-wrap"><?php }, 34 );
			add_action( 'woocommerce_single_product_summary', 'ideapark_product_wishlist', 35 );
			add_action( 'woocommerce_share', 'ideapark_product_share' );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 36 );
			add_action( 'woocommerce_single_product_summary', function () { ?></div><!-- .c-product__buttons-wrap --><?php }, 37 );

			if ( ideapark_mod( 'hide_sku' ) ) {
				add_filter( 'wc_product_sku_enabled', '__return_false' );
			}

			if ( ideapark_mod( 'product_bottom_page' ) ) {
				add_action( 'woocommerce_after_single_product', function () {
					if ( ( $page_id = apply_filters( 'wpml_object_id', ideapark_mod( 'product_bottom_page' ), 'any' ) ) && 'publish' == ideapark_post_status( $page_id ) ) {
						global $post;
						if ( ideapark_is_elementor_page( $page_id ) ) {
							$page_content = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $page_id );
						} elseif ( $post = get_post( $page_id ) ) {
							$page_content = apply_filters( 'the_content', $post->post_content );
							$page_content = str_replace( ']]>', ']]&gt;', $page_content );
							$page_content = ideapark_wrap( $page_content, '<div class="entry-content">', '</div>' );
							wp_reset_postdata();
						} else {
							$page_content = '';
						}
						echo ideapark_wrap( $page_content, '<div class="l-section">', '</div>' );
					}

				}, 90 );
			}

			/* Cart page */
			ideapark_ra( 'woocommerce_before_cart', 'woocommerce_output_all_notices', 10 );
			add_action( 'woocommerce_before_cart_totals', 'woocommerce_checkout_coupon_form', 10 );

			/* Checkout page */
			ideapark_ra( 'woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices', 10 );
			ideapark_ra( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );
			ideapark_ra( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
			add_action( 'woocommerce_checkout_before_order_review', 'woocommerce_checkout_coupon_form', 10 );

			/* All Account pages */
			ideapark_ra( 'woocommerce_account_content', 'woocommerce_output_all_notices', 5 );

			/* Snippets */

			add_filter( 'product_cat_class', 'ideapark_subcat_class', 10, 3 );

			if ( ideapark_mod( 'store_notice_button_text' ) ) {
				add_filter( "woocommerce_demo_store", function ( $notice ) {
					return preg_replace( "~(dismiss-link\">)([^>]+)(<)~", "\\1" . esc_html( ideapark_mod( 'store_notice_button_text' ) ) . "\\3", $notice );
				} );
			}

			add_filter( 'woocommerce_breadcrumb_home_url', function ( $url ) {
				return home_url( '/' );
			} );

			add_filter( 'woocommerce_layered_nav_count', function ( $html, $count ) {
				return '<span class="count">' . absint( $count ) . '</span>';
			}, 10, 2 );

			add_filter( 'woocommerce_subcategory_count_html', function ( $html, $category ) {
				return ' <mark class="count">' . esc_html( $category->count ) . '</mark>';
			}, 10, 2 );

		}
	}
}


if ( ! function_exists( 'ideapark_cut_product_categories' ) ) {
	function ideapark_cut_product_categories() { ?>
		<?php
		/**
		 * @var $product WC_Product
		 **/
		global $product;

		$separator  = '<span class="h-bullet"></span>';
		$categories = [];
		$brands     = [];

		if ( ideapark_mod( 'shop_category' ) ) {
			$term_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
			foreach ( $term_ids as $term_id ) {
				$categories[] = get_term_by( 'id', $term_id, 'product_cat' );
			}
		}

		if ( ideapark_mod( 'show_product_grid_brand' ) ) {
			if ( $terms = ideapark_brands() ) {
				foreach ( $terms as $term ) {
					$brands[] = '<a class="c-product-grid__category-item c-product-grid__category-item--brand" href="' . esc_url( get_term_link( $term->term_id, ideapark_mod( 'product_brand_attribute' ) ) ) . '">' . esc_html( $term->name ) . '</a>';
				}
				$brands = array_filter( $brands );
			}
		}

		if ( $categories || $brands ) { ?>
			<div class="c-product-grid__category-list">
				<?php
				if ( $categories ) {
					ideapark_category( $separator, $categories, 'c-product-grid__category-item' );
				}
				if ( $brands ) {
					echo ideapark_wrap( $categories ? ideapark_wrap( $separator ) : '' ) . implode( $separator, $brands );
				}
				?>
			</div>
		<?php }
	}
}

if ( ! function_exists( 'ideapark_product_brand_logo' ) ) {
	function ideapark_product_brand_logo() {
		if ( $terms = ideapark_brands() ) {
			$brands = [];
			foreach ( $terms as $term ) {
				if ( ( $image_id = get_term_meta( $term->term_id, 'brand_logo', true ) ) && ( $type = get_post_mime_type( $image_id ) ) ) {
					$brand = '<a class="c-product__brand-logo" href="' . esc_url( get_term_link( $term->term_id, ideapark_mod( 'product_brand_attribute' ) ) ) . '">';
					if ( $type == 'image/svg+xml' ) {
						$brand .= ideapark_get_inline_svg( $image_id, 'c-product__brand-logo-svg' );
					} else {
						$brand .= ideapark_img( ideapark_image_meta( $image_id ), 'c-product__brand-logo-image' );
					}
					$brand    .= '</a>';
					$brands[] = $brand;
				}
			}
			$brands = array_filter( $brands );
			echo ideapark_wrap( implode( '', $brands ), '<div class="c-product__brand-logo-list">', '</div>' );
		}
	}
}

if ( ! function_exists( 'ideapark_tabs_list' ) ) {
	function ideapark_tabs_list() {
		$product_tabs = apply_filters( 'woocommerce_product_tabs', [] );
		if ( ! empty( $product_tabs ) ) {
			foreach ( $product_tabs as $key => $product_tab ) {
				if ( $key != 'reviews' ) { ?>
					<div
						class="c-product__tabs-header"><?php echo wp_kses( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ), [ 'sup' => [ 'class' => true ] ] ); ?></div>
					<div
						class="c-product__tabs-panel woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel <?php if ( $key == 'description' ) { ?>entry-content<?php } ?> wc-tab visible"
						id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel"
						aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
						<?php
						if ( isset( $product_tab['callback'] ) ) {
							call_user_func( $product_tab['callback'], $key, $product_tab );
						}
						?>
					</div>
				<?php }
			}
			do_action( 'woocommerce_product_after_tabs' );
		}
	}
}

if ( ! function_exists( 'ideapark_tab_reviews' ) ) {
	function ideapark_tab_reviews() {
		$product_tabs = apply_filters( 'woocommerce_product_tabs', [] );
		if ( ! empty( $product_tabs ) ) {
			foreach ( $product_tabs as $key => $product_tab ) {
				if ( $key == 'reviews' ) { ?>
					<div class="c-product__col-2-center">
						<div
							class="c-product__tabs-panel woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel <?php if ( $key == 'description' ) { ?>entry-content<?php } ?> wc-tab visible"
							id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel"
							aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
							<?php
							if ( isset( $product_tab['callback'] ) ) {
								call_user_func( $product_tab['callback'], $key, $product_tab );
							}
							?>
						</div>
					</div>
				<?php }
			}
			do_action( 'woocommerce_product_after_tabs' );
		}
	}
}

if ( ! function_exists( 'ideapark_loop_product_thumbnail' ) ) {
	function ideapark_loop_product_thumbnail( $is_hover_image = false ) {
		global $product;
		$switch_image_on_hover = ideapark_mod( 'switch_image_on_hover' ) && ideapark_mod( 'product_grid_layout' ) != 'compact' && ideapark_mod( 'product_buttons_layout' ) == 'buttons-2';
		if ( $product ) {
			$attr       = [ 'class' => 'c-product-grid__thumb c-product-grid__thumb--' . ideapark_mod( 'grid_image_fit' ) . ( $switch_image_on_hover && $product->get_gallery_image_ids() ? ( $is_hover_image ? ' c-product-grid__thumb--hover' : ' c-product-grid__thumb--base' ) : '' ) ];
			$image_size = apply_filters( 'single_product_archive_thumbnail_size', 'large' );
			if ( $is_hover_image ) {
				$ids           = $product->get_gallery_image_ids();
				$attachment_id = ( ! empty( $ids[0] ) ) ? $ids[0] : 0;
			} else {
				$attachment_id = $product->get_image_id();
			}
			if ( $attachment_id && ( $image = wp_get_attachment_image_src( $attachment_id, $image_size ) ) ) {
				$image_meta = wp_get_attachment_metadata( $attachment_id );
				if ( is_array( $image_meta ) ) {
					[ $src, $width, $height ] = $image;
					$size_array     = [ absint( $width ), absint( $height ) ];
					$fn             = 'wp_calculate' . '_image_srcset';
					$attr['srcset'] = $fn( $size_array, $src, $image_meta, $attachment_id );
					$attr['sizes']  = '';// wp_calculate_image_sizes( $size_array, $src, $image_meta, $attachment_id );
					$layout         = ideapark_mod( '_product_layout' );
					$layout_mobile  = ideapark_mod( '_product_layout_mobile' );
					$layout_width   = ideapark_mod( '_product_layout_width' );
					$with_sidebar   = ideapark_mod( '_with_sidebar' );
					switch ( $layout ) {
						case '2-per-row':
							if ( $layout_width == 'boxed' ) {
								if ( $with_sidebar ) {
									$attr['sizes'] .= "(min-width: 1190px) 445px, (min-width: 1024px) 50vw, (min-width: 768px) 100vw";
								} else {
									$attr['sizes'] .= "(min-width: 1190px) 580px, (min-width: 1024px) 50vw, (min-width: 768px) 100vw";
								}
							} else {
								$attr['sizes'] .= "(min-width: 1190px) 50vw, (min-width: 768px) 100vw";
							}
							break;
						case '3-per-row':
							if ( $layout_width == 'boxed' ) {
								if ( $with_sidebar ) {
									$attr['sizes'] .= "(min-width: 768px) 294px";
								} else {
									$attr['sizes'] .= "(min-width: 768px) 384px";
								}
							} else {
								$attr['sizes'] .= "(min-width: 768px) 33vw";
							}
							break;
						case '4-per-row':
							if ( $layout_width == 'boxed' ) {
								if ( $with_sidebar ) {
									$attr['sizes'] .= "(min-width: 1190px) 218px, (min-width: 1024px) 25vw, (min-width: 768px) 33vw";
								} else {
									$attr['sizes'] .= "(min-width: 1024px) 285px, (min-width: 768px) 33vw";
								}
							} else {
								if ( $with_sidebar ) {
									$attr['sizes'] .= "(min-width: 1190px) 20vw, (min-width: 1024px) 25vw, (min-width: 768px) 33vw";
								} else {
									$attr['sizes'] .= "(min-width: 1024px) 25vw, (min-width: 768px) 33vw";
								}
							}

							break;
						case 'compact':
							$attr['sizes'] = "(min-width: 768px) 145px";
							break;
					}
					switch ( $layout_mobile ) {
						case '1-per-row-mobile':
							$attr['sizes'] .= ", 100vw";
							break;
						case '2-per-row-mobile':
							$attr['sizes'] .= ", 50vw";
							break;
						case 'compact-mobile':
							$attr['sizes'] .= ", 90px";
							break;
					}
				}
			}
			echo ideapark_wrap( $product->get_image( $image_size, $attr ) );
			if ( $switch_image_on_hover && ! $is_hover_image ) {
				ideapark_loop_product_thumbnail( true );
			}
		}
	}
}

if ( ! function_exists( 'ideapark_template_product_buttons' ) ) {
	function ideapark_template_product_buttons() {
		wc_get_template( 'global/product-buttons.php' );
	}
}

if ( ! function_exists( 'ideapark_template_short_description' ) ) {
	function ideapark_template_short_description() {
		wc_get_template( 'loop/short-description.php' );
	}
}

if ( ! function_exists( 'ideapark_template_brand_meta' ) ) {
	function ideapark_template_brand_meta() {
		wc_get_template( 'loop/brand_meta.php' );
	}
}

if ( ! function_exists( 'ideapark_cart_info' ) ) {
	function ideapark_cart_info() {
		global $woocommerce;

		if ( isset( $woocommerce->cart ) ) {
			$cart_total = $woocommerce->cart->get_cart_total();
			$cart_count = $woocommerce->cart->get_cart_contents_count();

			return '<span class="js-cart-info">'
			       . ( ! $woocommerce->cart->is_empty() ? ideapark_wrap( esc_html( $cart_count ), '<span class="c-header__cart-count js-cart-count">', '</span>' ) : '' )
			       . ( ! $woocommerce->cart->is_empty() ? ideapark_wrap( $cart_total, '<span class="c-header__cart-sum">', '</span>' ) : '' ) .
			       '</span>';
		}
	}
}

if ( ! function_exists( 'ideapark_wishlist_info' ) ) {
	function ideapark_wishlist_info() {

		if ( ideapark_mod( 'wishlist_page' ) ) {
			$count = sizeof( ideapark_wishlist()->ids() );
		} else {
			$count = 0;
		}

		return '<span class="js-wishlist-info">'
		       . ( $count ? ideapark_wrap( $count, '<span class="c-header__cart-count">', '</span>' ) : '' ) .
		       '</span>';
	}
}

if ( ! function_exists( 'ideapark_header_add_to_cart_fragment' ) ) {
	function ideapark_header_add_to_cart_fragment( $fragments ) {
		$fragments['.js-cart-info']     = ideapark_cart_info();
		$fragments['.js-wishlist-info'] = ideapark_wishlist_info();
		ob_start();
		wc_print_notices();
		$fragments['ideapark_notice'] = ob_get_clean();

		return $fragments;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_show_product_loop_badges' ) ) {
	function ideapark_woocommerce_show_product_loop_badges() {
		/**
		 * @var $product WC_Product
		 **/
		global $product;

		if ( ideapark_mod( 'featured_badge_text' ) && $product->is_featured() ) {
			echo '<span class="c-badge c-badge--featured">' . esc_html( ideapark_mod( 'featured_badge_text' ) ) . '</span>';
		}

		$newness = (int) ideapark_mod( 'product_newness' );

		if ( ideapark_mod( 'new_badge_text' ) && $newness > 0 ) {
			$postdate      = get_the_time( 'Y-m-d' );
			$postdatestamp = strtotime( $postdate );
			if ( ( time() - ( 60 * 60 * 24 * $newness ) ) < $postdatestamp ) {
				echo '<span class="c-badge c-badge--new">' . esc_html( ideapark_mod( 'new_badge_text' ) ) . '</span>';
			}
		}
	}
}

if ( ! function_exists( 'ideapark_woocommerce_breadcrumbs' ) ) {
	function ideapark_woocommerce_breadcrumbs() {
		return [
			'delimiter'   => '',
			'wrap_before' => '<nav class="c-breadcrumbs"><ol class="c-breadcrumbs__list">',
			'wrap_after'  => '</ol></nav>',
			'before'      => '<li class="c-breadcrumbs__item">',
			'after'       => '</li>',
			'home'        => esc_html_x( 'Home', 'breadcrumb', 'woocommerce' ),
		];
	}
}

if ( ! function_exists( 'ideapark_woocommerce_account_menu_items' ) ) {
	function ideapark_woocommerce_account_menu_items( $items ) {
		unset( $items['customer-logout'] );

		return $items;
	}
}

if ( ! function_exists( 'ideapark_product_availability' ) ) {
	function ideapark_product_availability() {
		global $product;

		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
			$availability = $product->get_availability();
			if ( $product->is_in_stock() ) {
				$availability_html = '<span class="c-stock c-stock--in-stock ' . esc_attr( $availability['class'] ) . '">' . ( $availability['availability'] ? esc_html( $availability['availability'] ) : esc_html__( 'In stock', 'goldish' ) ) . '</span>';
			} else {
				$availability_html = '<span class="c-stock c-stock--out-of-stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</span>';
			}
		} else {
			$availability_html = '';
		}

		echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
	}
}

if ( ! function_exists( 'ideapark_remove_product_description_heading' ) ) {
	function ideapark_remove_product_description_heading() {
		return '';
	}
}

if ( ! function_exists( 'ideapark_woocommerce_search_form' ) ) {
	function ideapark_woocommerce_search_form() {
		if ( is_search() ) {
			echo '<div class="c-product-search-form">';
			get_search_form();
			echo '</div>';
		}
	}
}

if ( ! function_exists( 'ideapark_woocommerce_max_srcset_image_width_768' ) ) {
	function ideapark_woocommerce_max_srcset_image_width_768( $max_width, $size_array ) {
		return 768;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_max_srcset_image_width_360' ) ) {
	function ideapark_woocommerce_max_srcset_image_width_360( $max_width, $size_array ) {
		return 360;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_hide_uncategorized' ) ) {
	function ideapark_woocommerce_hide_uncategorized( $args ) {
		if ( ideapark_mod( 'hide_uncategorized' ) ) {
			$args['exclude'] = get_option( 'default_product_cat' );
			if ( ! empty( $args['include'] ) ) {
				$args['include'] = implode( ',', array_filter( explode( ',', $args['include'] ), function ( $var ) {
					return $var != get_option( 'default_product_cat' );
				} ) );
			}
		}

		return $args;
	}
}

if ( ! function_exists( 'ideapark_subcategory_archive_thumbnail_size' ) ) {
	function ideapark_subcategory_archive_thumbnail_size( $thumbnail_size ) {
		return 'woocommerce_gallery_thumbnail';
	}
}

if ( ! function_exists( 'ideapark_loop_add_to_cart_link' ) ) {
	function ideapark_loop_add_to_cart_link( $text, $product, $args ) {
		$text = preg_replace( '~(<a[^>]+>)~ui', '\\1<span class="c-product-grid__atc-text">', $text );
		$text = preg_replace( '~(</a>)~ui', '</span>' . '\\1', $text );
		if ( $product->get_type() == 'simple' ) {
			return preg_replace( '~(<a[^>]+>)~ui', '\\1<i class="ip-plus c-product-grid__atc-icon"></i>', $text );
		} else {
			return preg_replace( '~(</a>)~ui', '<i class="ip-button-more c-product-grid__atc-icon"></i>' . '\\1', $text );
		}
	}
}

if ( ! function_exists( 'ideapark_woocommerce_gallery_image_size' ) ) {
	function ideapark_woocommerce_gallery_image_size( $size ) {
		return 'full';
	}
}

if ( ! function_exists( 'ideapark_get_filtered_term_product_counts' ) ) {
	function ideapark_get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type, $tax_query = null, $meta_query = null ) {
		global $wpdb;

		if ( $tax_query === null ) {
			$tax_query = WC_Query::get_main_tax_query();
		}

		if ( $meta_query === null ) {
			$meta_query = WC_Query::get_main_meta_query();
		}

		if ( 'or' === $query_type ) {
			foreach ( $tax_query as $key => $query ) {
				if ( is_array( $query ) && $taxonomy === $query['taxonomy'] ) {
					unset( $tax_query[ $key ] );
				}
			}
		}

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// Generate query.
		$query           = [];
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];

		$query['where'] = "
			WHERE {$wpdb->posts}.post_type IN ( 'product' )
			AND {$wpdb->posts}.post_status = 'publish'"
		                  . $tax_query_sql['where'] . $meta_query_sql['where'] .
		                  'AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';

		if ( ! empty( WC_Query::$query_vars ) ) {
			$search = WC_Query::get_main_search_query_sql();
			if ( $search ) {
				$query['where'] .= ' AND ' . $search;
			}
		}

		$query['group_by'] = 'GROUP BY terms.term_id';
		$query             = implode( ' ', $query );

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = md5( $query );

		// Maybe store a transient of the count values.
		$cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', true );
		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ) );
		} else {
			$cached_counts = [];
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {
			$results                      = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
			$counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
			$cached_counts[ $query_hash ] = $counts;
			if ( true === $cache ) {
				set_transient( 'wc_layered_nav_counts_' . sanitize_title( $taxonomy ), $cached_counts, DAY_IN_SECONDS );
			}
		}

		return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	}
}

if ( ! function_exists( 'ideapark_woocommerce_loop_add_to_cart_args' ) ) {
	function ideapark_woocommerce_loop_add_to_cart_args( $args ) {

		$args['class'] = 'h-cb c-product-grid__atc ' . $args['class'];

		return $args;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_available_variation' ) ) {
	function ideapark_woocommerce_available_variation( $params, $instance, $variation ) {

		$image = wp_get_attachment_image_src( $params['image_id'], 'woocommerce_single' );
		if ( ! empty( $image ) ) {
			$params['image']['gallery_thumbnail_src'] = $image[0];
		}

		return $params;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_pagination_args' ) ) {
	function ideapark_woocommerce_pagination_args( $args ) {
		$args['prev_text'] = ideapark_pagination_prev();
		$args['next_text'] = ideapark_pagination_next();
		$args['end_size']  = 1;
		$args['mid_size']  = 1;

		return $args;
	}
}

if ( ! function_exists( 'ideapark_ajax_product_images' ) ) {
	function ideapark_ajax_product_images() {
		ob_start();

		if ( isset( $_REQUEST['product_id'] ) && ( $product_id = absint( $_REQUEST['product_id'] ) ) ) {
			$variation_id   = isset( $_REQUEST['variation_id'] ) ? absint( $_REQUEST['variation_id'] ) : 0;
			$index          = isset( $_REQUEST['index'] ) ? absint( $_REQUEST['index'] ) : 0;
			$product_images = ideapark_product_images( $product_id, $variation_id );
			$images         = [];
			foreach ( $product_images as $_index => $image ) {
				if ( ! empty( $image['video_url'] ) ) {
					if ( preg_match( '~\.(mp4|m4v|webm|ogv|wmv|flv)$~i', trim( $image['video_url'] ) ) ) {
						$images[] = [
							'html' => ideapark_wrap( do_shortcode( '[video ' . ( $_index == $index ? 'autoplay="on"' : '' ) . ' src="' . esc_url( trim( $image['video_url'] ) ) . '"]' ), '<div class="pswp__video-wrap">', '</div>' )
						];
					} else {
						if ( $_index == $index ) {
							add_filter( 'oembed_result', function ( $html ) {
								return str_replace( "?feature=oembed", "?feature=oembed&autoplay=1", $html );
							} );
						}
						$images[] = [
							'html' => ideapark_wrap( wp_oembed_get( $image['video_url'] ), '<div class="pswp__video-wrap">', '</div>' )
						];
					}
				} else {
					$images[] = [
						'src' => $image['full'][0],
						'w'   => $image['full'][1],
						'h'   => $image['full'][2],
					];
				}
			}

			ob_end_clean();
			wp_send_json( [ 'images' => $images ] );
		}
		ob_end_clean();
	}
}

if ( ! function_exists( 'ideapark_ajax_product' ) ) {
	function ideapark_ajax_product() {
		global $woocommerce, $product, $post;
		if ( isset( $_REQUEST['lang'] ) ) {
			do_action( 'wpml_switch_language', $_REQUEST['lang'] );
		}
		if (
			ideapark_woocommerce_on() &&
			ideapark_mod( 'shop_modal' ) &&
			! empty( $_POST['product_id'] ) &&
			( $product_id = (int) $_POST['product_id'] ) &&
			( $product = wc_get_product( $_POST['product_id'] ) ) &&
			( $post = get_post( $_POST['product_id'] ) )
		) {
			setup_postdata( $post );
			wc_get_template_part( 'content', 'quickview' );
			wp_reset_postdata();
		}
		die();
	}
}

if ( ! function_exists( 'ideapark_ajax_attribute_hint' ) ) {
	function ideapark_ajax_attribute_hint() {
		if ( isset( $_REQUEST['lang'] ) ) {
			do_action( 'wpml_switch_language', $_REQUEST['lang'] );
		}
		if (
			ideapark_woocommerce_on() &&
			! empty( $_POST['attribute_id'] ) &&
			( $attr_id = (int) $_POST['attribute_id'] ) &&
			( $hint = get_option( "wc_attribute_hint-$attr_id" ) ) &&
			( $html_block_id = get_option( "wc_attribute_html_block_id-$attr_id" ) ) &&
			( $page_id = apply_filters( 'wpml_object_id', $html_block_id, 'any' ) ) &&
			( 'publish' == ideapark_post_status( $page_id ) )
		) {
			global $post;
			if ( ideapark_is_elementor_page( $page_id ) ) {
				$page_content = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $page_id );
			} elseif ( $post = get_post( $page_id ) ) {
				$page_content = apply_filters( 'the_content', $post->post_content );
				$page_content = str_replace( ']]>', ']]&gt;', $page_content );
				$page_content = ideapark_wrap( $page_content, '<div class="entry-content">', '</div>' );
				wp_reset_postdata();
			} else {
				$page_content = '';
			}
			echo ideapark_wrap( $page_content, '<div class="l-section">', '</div>' );
		}
		die();
	}
}

if ( ! function_exists( 'ideapark_woocommerce_before_widget_product_list' ) ) {
	function ideapark_woocommerce_before_widget_product_list( $content ) {
		return str_replace( 'product_list_widget', 'c-product-list-widget', $content );
	}
}

if ( ! function_exists( 'ideapark_wp_scrset_on' ) ) {
	function ideapark_wp_scrset_on( $name = '' ) {
		$f = 'add_filter';
		$n = 'wp_calculate_image_' . 'srcset';
		call_user_func( $f, $n, 'ideapark_woocommerce_srcset' . ( $name ? '_' : '' ) . $name, 10, 5 );
	}
}

if ( ! function_exists( 'ideapark_wp_scrset_off' ) ) {
	function ideapark_wp_scrset_off( $name = '' ) {
		$f = 'remove_filter';
		$n = 'wp_calculate_image_' . 'srcset';
		call_user_func( $f, $n, 'ideapark_woocommerce_srcset' . ( $name ? '_' : '' ) . $name, 10 );
	}
}

if ( ! function_exists( 'ideapark_wp_max_scrset_on' ) ) {
	function ideapark_wp_max_scrset_on( $name = '' ) {
		$f = 'add_filter';
		$n = 'max_srcset_image_' . 'width';
		call_user_func( $f, $n, 'ideapark_woocommerce_max_srcset_image_width' . ( $name ? '_' : '' ) . $name, 10, 2 );
	}
}

if ( ! function_exists( 'ideapark_wp_max_scrset_off' ) ) {
	function ideapark_wp_max_scrset_off( $name = '' ) {
		$f = 'remove_filter';
		$n = 'max_srcset_image_' . 'width';
		call_user_func( $f, $n, 'ideapark_woocommerce_max_srcset_image_width' . ( $name ? '_' : '' ) . $name, 10 );
	}
}

if ( ! function_exists( 'ideapark_woocommerce_srcset_grid' ) ) {
	function ideapark_woocommerce_srcset_grid( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		foreach ( $sources as $width => $data ) {
			if ( $width != $size_array[0] && $width != $size_array[0] * 2 ) {
				unset( $sources[ $width ] );
			}
		}

		return $sources;
	}
}

if ( ! function_exists( 'ideapark_woocommerce_srcset_retina' ) ) {
	function ideapark_woocommerce_srcset_retina( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		foreach ( $sources as $width => $data ) {
			if ( $width != $size_array[0] && $width != $size_array[0] * 2 ) {
				unset( $sources[ $width ] );
			}
		}

		return $sources;
	}
}

if ( ! function_exists( 'ideapark_product_images' ) ) {
	function ideapark_product_images( $product_id = 0, $variation_id = 0 ) {
		global $product;

		if ( ! $product_id ) {
			$product_id = $product->get_id();
		} else {
			$product = wc_get_product( $product_id );
		}
		$image_size = 'full'; //todo-me image size
		$images     = [];
		if ( ! ( $variation_id && ( $attachment_ids = get_post_meta( $variation_id, 'ideapark_variation_images', true ) ) ) ) {
			$attachment_ids = $product->get_gallery_image_ids();
		}
		if ( ! is_array( $attachment_ids ) ) {
			$attachment_ids = [];
		}
		if ( get_post_meta( $product_id, '_thumbnail_id', true ) ) {
			if ( $variation_id && ( $attachment_id = get_post_thumbnail_id( $variation_id ) ) ) {
				array_unshift( $attachment_ids, $attachment_id );
			} else {
				array_unshift( $attachment_ids, get_post_thumbnail_id( $product_id ) );
			}
		}

		if ( $attachment_ids ) {

			add_filter( 'wp_lazy_loading_enabled', '__return_false', 100 );
			foreach ( $attachment_ids as $attachment_id ) {
				if ( ! wp_get_attachment_url( $attachment_id ) ) {
					continue;
				}

				$image = wp_get_attachment_image( $attachment_id, $image_size, false, [
					'alt'   => get_the_title( $attachment_id ),
					'class' => 'c-product__slider-img c-product__slider-img--' . ideapark_mod( 'product_image_fit' )
				] );

				$full = ideapark_mod( 'shop_product_modal' ) || ideapark_mod( 'quickview_product_zoom' ) ? wp_get_attachment_image_src( $attachment_id, 'full' ) : false;

				$thumb = wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, [
					'alt'   => get_the_title( $product_id ),
					'class' => 'c-product__thumbs-img'
				] );

				$images[] = [
					'attachment_id' => $attachment_id,
					'image'         => $image,
					'full'          => $full,
					'thumb'         => $thumb
				];
			}
			ideapark_rf( 'wp_lazy_loading_enabled', '__return_false', 100 );
		}

		if ( $video_url = get_post_meta( $product_id, '_ip_product_video_url', true ) ) {

			$is_youtube_preview = false;
			if ( $video_thumb_id = get_post_meta( $product_id, '_ip_product_video_thumb', true ) ) {
				$thumb_url = ( $image = wp_get_attachment_image_src( $video_thumb_id, 'woocommerce_gallery_thumbnail' ) ) ? $image[0] : '';
				$image_url = ( $image = wp_get_attachment_image_src( $video_thumb_id, $image_size ) ) ? $image[0] : '';
			} else {
				$pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
				if ( preg_match( $pattern, $video_url, $match ) ) {
					$image_url          = 'https://img.youtube.com/vi/' . $match[1] . '/maxresdefault.jpg';
					$thumb_url          = 'https://img.youtube.com/vi/' . $match[1] . '/default.jpg';
					$is_youtube_preview = true;
				} else {
					$image_url = '';
					$thumb_url = '';
				}
			}
			$video = [
				'thumb_url'          => $thumb_url,
				'image_url'          => $image_url,
				'video_url'          => $video_url,
				'is_youtube_preview' => $is_youtube_preview,
			];

			if ( sizeof( $images ) >= 4 ) {
				array_splice( $images, 3, 0, [ $video ] );
			} else {
				$images[] = $video;
			}
		}

		return $images;
	}
}

if ( ! function_exists( 'ideapark_product_wishlist' ) ) {
	function ideapark_product_wishlist() {
		if ( ideapark_mod( 'wishlist_page' ) ) { ?>
			<div
				class="c-product__wishlist"><?php Ideapark_Wishlist()->ideapark__button( 'h-cb c-product__wishlist-button', 'c-product__wishlist-icon', 'c-product__wishlist-text', __( 'Add to Wishlist', 'goldish' ), __( 'Remove from Wishlist', 'goldish' ), 'ip-heart-sm', 'ip-heart-sm-active', '12px' ) ?></div>
		<?php }
	}
}

if ( ! function_exists( 'ideapark_product_share' ) ) {
	function ideapark_product_share() {
		if ( ideapark_mod( 'product_share' ) && shortcode_exists( 'ip-post-share' ) ) { ?>
			<div class="c-product__share">
				<i class="ip-share c-product__share-icon"></i>
				<div class="c-product__share-title"><?php esc_html_e( 'Share', 'goldish' ); ?></div>
				<?php echo ideapark_shortcode( '[ip-post-share]' ); ?>
			</div>
		<?php }
	}
}

if ( ! function_exists( 'ideapark_add_to_cart_ajax_notice' ) ) {
	function ideapark_add_to_cart_ajax_notice( $product_id ) {
		wc_add_to_cart_message( $product_id );
	}
}

if ( ! function_exists( 'ideapark_woocommerce_demo_store' ) ) {
	function ideapark_woocommerce_demo_store( $notice ) {
		return str_replace( 'woocommerce-store-notice ', 'woocommerce-store-notice woocommerce-store-notice--' . ideapark_mod( 'store_notice' ) . ' ', $notice );
	}
}

if ( ! function_exists( 'ideapark_woocommerce_product_tabs' ) ) {
	function ideapark_woocommerce_product_tabs( $tabs ) {
		$theme_tabs = ideapark_parse_checklist( ideapark_mod( 'product_tabs' ) );
		$priority   = 10;
		foreach ( $theme_tabs as $theme_tab_index => $enabled ) {
			if ( array_key_exists( $theme_tab_index, $tabs ) ) {
				if ( $enabled ) {
					$tabs[ $theme_tab_index ]['priority'] = $priority;
				} else {
					unset( $tabs[ $theme_tab_index ] );
				}
			}
			$priority += 10;
		}

		return $tabs;
	}
}

if ( ! function_exists( 'ideapark_stock_badge' ) ) {
	function ideapark_stock_badge() {
		global $product;
		/**
		 * @var $product WC_Product
		 */

		$availability = $product->get_availability();
		if ( ! ( $product->is_in_stock() || $product->is_on_backorder() ) ) {
			$availability_html = '<span class="c-badge c-badge--out-of-stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( ideapark_mod( 'outofstock_badge_text' ) ) . '</span>';
			echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
		}
	}
}

if ( ! function_exists( 'ideapark_brands' ) ) {
	function ideapark_brands() {
		global $product;
		if (
			( $brand_taxonomy = ideapark_mod( 'product_brand_attribute' ) ) &&
			( $attributes = $product->get_attributes() ) &&
			is_array( $attributes ) &&
			array_key_exists( $brand_taxonomy, $attributes ) &&
			is_object( $attributes[ $brand_taxonomy ] )
		) {
			return $attributes[ $brand_taxonomy ]->get_terms();
		}
	}
}

if ( ! function_exists( 'ideapark_grid_text_class' ) ) {
	function ideapark_grid_text_class( $class_base ) {
		ob_start();
		if ( in_array( ideapark_mod( '_product_layout' ), [
			'2-per-row',
			'3-per-row',
			'4-per-row'
		] ) ) { ?><?php echo ' ' . esc_attr( $class_base ); ?>--<?php echo ideapark_mod( 'product_grid_text_desktop' ); ?><?php } ?><?php if ( in_array( ideapark_mod( '_product_layout_mobile' ), [
			'1-per-row-mobile',
			'2-per-row-mobile'
		] ) ) { ?><?php echo ' ' . esc_attr( $class_base ); ?>--<?php echo ideapark_mod( 'product_grid_text_mobile' ); ?><?php }

		return ob_get_clean();
	}
}

if ( ! function_exists( 'ideapark_subcat_class' ) ) {
	function ideapark_subcat_class( $classes = [], $class = '', $category = null ) {
		$classes[] = ideapark_mod( 'show_subcat_in_header' ) || ideapark_mod( '_is_header_subcat' ) ? 'c-page-header__sub-cat-item' : ( 'c-sub-categories__item c-sub-categories__item--' . ( ideapark_mod( '_with_sidebar' ) ? 'sidebar' : 'no-sidebar' ) . ' c-sub-categories__item--' . ideapark_mod( 'product_grid_width' ) );
		if ( is_product_category() && $category && $category->term_id == get_queried_object_id() ) {
			$classes[] = 'c-page-header__sub-cat-item--current';
		}

		return $classes;
	}
}

if ( ! function_exists( 'ideapark_header_categories' ) ) {
	function ideapark_header_categories( $_parent_id = null ) {
		if ( ideapark_woocommerce_on() && ( $_parent_id !== null || ideapark_mod( 'show_subcat_in_header' ) && ( is_shop() || is_product_category() ) ) ) {

			$parent_id = $_parent_id ?: ( is_product_category() ? get_queried_object_id() : 0 );
			do {
				ob_start();
				ideapark_mod_set_temp( '_is_header_subcat', true );
				woocommerce_output_product_categories(
					[ 'parent_id' => $parent_id ]
				);
				ideapark_mod_set_temp( '_is_header_subcat', false );
				$loop_html = ob_get_clean();
				if ( ! $loop_html ) {
					if ( $parent_id ) {
						$parent_id = get_queried_object()->parent;
					} else {
						break;
					}
				} elseif ( $parent_id && ! $_parent_id ) {
					$term_id = get_queried_object()->parent;
					$title   = '';
					if ( $term_id ) {
						$term = get_term( $term_id );
						if ( $term && ! is_wp_error( $term ) ) {
							$title = $term->name;
							$link  = get_term_link( (int) $term->term_id );
						}
					} elseif ( $shop_page_id = wc_get_page_id( 'shop' ) ) {
						$title = get_the_title( $shop_page_id );
						$link  = get_permalink( $shop_page_id );
					}
					if ( $title ) {
						$loop_html = '<div class="product-category product first c-page-header__sub-cat-item"><a href="' . esc_url( $link ) . '"><span class="c-sub-categories__thumb-wrap c-sub-categories__thumb-wrap--header c-sub-categories__thumb-wrap--back"><i class="ip-arrow-long c-sub-categories__back"></i></span><h2 class="woocommerce-loop-category__title">' . esc_html( $title ) . '</h2></a></div>' . str_replace( 'product first', '', $loop_html );
					}
				}
			} while ( ! $loop_html );

			$subcategories = apply_filters( 'ideapark_page_header_subcat', $loop_html );
			if ( $subcategories ) {
				$count = preg_match_all( '~c-page-header__sub-cat-item~', $subcategories, $matches, PREG_SET_ORDER ) ? sizeof( $matches ) : 0;
				echo ideapark_wrap( $subcategories, '<div class="c-page-header__sub-cat' . ( $_parent_id !== null ? ' c-page-header__sub-cat--widget' : '' ) . '"><div class="c-page-header__sub-cat-list ' . ( $count > 6 ? ' c-page-header__sub-cat-list--carousel ' : '' ) . ' js-header-subcat h-carousel h-carousel--dots-hide h-carousel--flex">', '</div></div>' );
			}

			return ! ! $subcategories;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'ideapark_attribute_hint' ) ) {
	function ideapark_attribute_hint() {
		$id            = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
		$hint          = $id ? get_option( "wc_attribute_hint-$id" ) : '';
		$html_block_id = $id ? get_option( "wc_attribute_html_block_id-$id" ) : 0;
		$dropdown      = wp_dropdown_pages(
			[
				'name'              => 'attribute_html_block_id',
				'echo'              => 0,
				'show_option_none'  => '&mdash; ' . esc_html__( 'Select', 'goldish' ) . ' &mdash;',
				'option_none_value' => '0',
				'selected'          => $html_block_id,
				'post_type'         => 'html_block',
				'post_status'       => [ 'publish' ],
			]
		);
		$add_func      = function () use ( $hint, $dropdown ) {
			?>
			<div class="form-field">
				<label for="attribute_hint"><?php esc_html_e( 'Hint title', 'goldish' ); ?></label>
				<input name="attribute_hint" type="text" id="attribute_hint" value="<?php echo esc_attr( $hint ); ?>"/>
				<p class="description"><?php esc_html_e( 'Fill in these two fields if you need a popup hint for the attribute. For example, "Size guide".', 'goldish' ); ?></p>
			</div>
			<div class="form-field">
				<label
					for="attribute_html_block_id"><?php esc_html_e( 'Hint content (HTML block)', 'goldish' ); ?></label>
				<?php echo ideapark_wrap( $dropdown ) ?>
				<div class="ideapark-manage-blocks"><a
						href="<?php echo esc_url( admin_url( 'edit.php?post_type=html_block' ) ); ?>"><?php echo esc_html__( 'Manage html blocks', 'goldish' ); ?></a>
				</div>
			</div>
			<?php
		};
		$edit_func     = function () use ( $hint, $dropdown ) {
			?>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="attribute_hint"><?php esc_html_e( 'Hint title', 'goldish' ); ?></label>
				</th>
				<td>
					<input name="attribute_hint" type="text" id="attribute_hint"
						   value="<?php echo esc_attr( $hint ); ?>"/>
					<p class="description"><?php esc_html_e( 'Fill in these two fields if you need a popup hint for the attribute. For example, "Size guide".', 'goldish' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label
						for="attribute_html_block_id"><?php esc_html_e( 'Hint content (HTML block)', 'goldish' ); ?></label>
				</th>

				<td>
					<?php echo ideapark_wrap( $dropdown ) ?>
					<div class="ideapark-manage-blocks"><a
							href="<?php echo esc_url( admin_url( 'edit.php?post_type=html_block' ) ); ?>"><?php echo esc_html__( 'Manage html blocks', 'goldish' ); ?></a>
					</div>
				</td>
			</tr>
			<?php
		};
		add_action( 'woocommerce_after_add_attribute_fields', $add_func, 99 );
		add_action( 'woocommerce_after_edit_attribute_fields', $edit_func, 99 );

		$save_func = function ( $id ) {
			$option = "wc_attribute_hint-$id";
			if ( is_admin() && isset( $_POST['attribute_hint'] ) ) {
				update_option( $option, sanitize_text_field( $_POST['attribute_hint'] ) );
			} else {
				delete_option( $option );
			}
			$option = "wc_attribute_html_block_id-$id";
			if ( is_admin() && isset( $_POST['attribute_html_block_id'] ) ) {
				update_option( $option, sanitize_text_field( $_POST['attribute_html_block_id'] ) );
			} else {
				delete_option( $option );
			}
		};
		add_action( 'woocommerce_attribute_added', $save_func );
		add_action( 'woocommerce_attribute_updated', $save_func );

		add_action( 'woocommerce_attribute_deleted', function ( $id ) {
			delete_option( "wc_attribute_hint-$id" );
			delete_option( "wc_attribute_html_block_id-$id" );
		} );
	}
}

if ( ! function_exists( 'ideapark_WPML_attribute_title' ) ) {
	function ideapark_WPML_attribute_title( $id, $data ) {
		if ( is_array( $data ) && isset( $data['attribute_label'] ) ) {
			do_action( 'wpml_register_single_string', IDEAPARK_NAME, "attribute name: " . $data['attribute_label'], $data['attribute_label'] );
		}
	}
}

if ( ! function_exists( 'ideapark_grid_color_attributes' ) ) {
	function ideapark_grid_color_attributes() {
		/**
		 * @var $product   WC_Product
		 * @var $attribute WC_Product_Attribute
		 **/
		global $product;
		static $types = [];
		$taxonomy   = ideapark_mod( 'product_color_attribute' );
		$attributes = $product->get_attributes();

		$items = [];

		if ( array_key_exists( $taxonomy, $attributes ) ) {
			$attribute = $attributes[ $taxonomy ];
			if ( $attribute->get_variation() && $product->is_type( 'variable' ) && ( $variations = $product->get_available_variations() ) ) {
				foreach ( $variations as $variation ) {
					if ( ! empty( $variation['attributes'][ 'attribute_' . $taxonomy ] ) && ! empty( $variation['image'] ) && ! empty( $variation['variation_is_visible'] ) && ! empty( $variation['variation_is_active'] ) ) {
						$value = $variation['attributes'][ 'attribute_' . $taxonomy ];
						if ( ! array_key_exists( $value, $items ) && ( $term = get_term_by( 'slug', $value, $taxonomy ) ) ) {
							$items[ $term->term_id ] = [
								'src'    => $variation['image']['src'],
								'srcset' => $variation['image']['srcset'],
								'term'   => $term,
							];
						}
					}
				}
			} elseif ( $terms = wp_get_post_terms( $product->get_id(), $taxonomy ) ) {
				foreach ( $terms as $term ) {
					$items[ $term->term_id ] = [
						'term' => $term
					];
				}
			}

			if ( $items ) {
				usort( $items, function ( $a, $b ) {
					if ( $a['term']->term_order == $b['term']->term_order ) {
						return 0;
					}

					return ( $a['term']->term_order < $b['term']->term_order ) ? - 1 : 1;
				} );
				$type = '';
				if ( isset( $types[ $taxonomy ] ) ) {
					$type = $types[ $taxonomy ];
				} else {
					if ( wvs_wc_product_has_attribute_type( $_type = 'color', $taxonomy ) ) {
						$type = $_type;
					}
					if ( wvs_wc_product_has_attribute_type( $_type = 'image', $taxonomy ) ) {
						$type = $_type;
					}
					$types[ $taxonomy ] = $type;
				}

				if ( $type ) { ?>
					<ul
						class="c-product-grid__color-list c-product-grid__color-list--<?php echo esc_attr( $type ); ?>">
						<?php foreach ( $items as $term_id => $item ) {
							$data_src    = ! empty( $item['src'] ) ? ' data-src="' . esc_attr( $item['src'] ) . '" ' : '';
							$data_srcset = ! empty( $item['srcset'] ) ? ' data-srcset="' . esc_attr( $item['srcset'] ) . '" ' : '';
							switch ( $type ) {
								case 'color':
									$color = sanitize_hex_color( wvs_get_product_attribute_color( $item['term'] ) );
									$html  = sprintf( '<li %s %s class="c-product-grid__color-item c-product-grid__color-item--color %s" style="background-color:%s;"><span class="c-product-grid__color-title">%s</span></li>', $data_src, $data_srcset, $data_src ? 'c-product-grid__color-item--var js-grid-color-var' : '', esc_attr( $color ), esc_html( $item['term']->name ) );
									break;

								case 'image':
									$attachment_id = absint( wvs_get_product_attribute_image( $item['term'] ) );
									$image_size    = woo_variation_swatches()->get_option( 'attribute_image_size' );
									$image         = wp_get_attachment_image_src( $attachment_id, $image_size );
									$html          = sprintf( '<li %s %s class="c-product-grid__color-item c-product-grid__color-item--image %s"><img class="c-ip-attribute-filter__thumb" aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" /><span class="c-product-grid__color-title">%s</span></li>', $data_src, $data_srcset, $data_src ? 'c-product-grid__color-item--var js-grid-color-var' : '', esc_attr( $item['term']->name ), esc_url( $image[0] ), esc_attr( $image[1] ), esc_attr( $image[2] ), esc_html( $item['term']->name ) );
									break;
							}
							echo ideapark_wrap( $html );
						} ?>
					</ul>
				<?php }
			}
		}
	}
}

if ( ! function_exists( 'ideapark_cart_brand' ) ) {
	function ideapark_cart_brand( $cart_item, $cart_item_mini ) {
		/**
		 * @var $product WC_Product
		 **/
		$attribute = ideapark_mod( 'product_brand_attribute' );
		$product   = $cart_item['data'];
		if ( $parent_id = $product->get_parent_id() ) {
			$product = wc_get_product( $parent_id );
		}
		if ( $name = $product->get_attribute( $attribute ) ) {
			echo ideapark_wrap( $name, '<div class="c-cart__shop-brand">', '</div>' );
		}
	}
}

if ( ! function_exists( 'ideapark_cart_mini_brand' ) ) {
	function ideapark_cart_mini_brand( $html, $cart_item, $cart_item_mini ) {
		/**
		 * @var $product WC_Product
		 **/
		$attribute = ideapark_mod( 'product_brand_attribute' );
		$product   = $cart_item['data'];
		if ( $parent_id = $product->get_parent_id() ) {
			$product = wc_get_product( $parent_id );
		}
		if ( $name = $product->get_attribute( $attribute ) ) {
			$html = ideapark_wrap( $name, '<div class="c-product-list-widget__brand">', '</div>' ) . $html;
		}

		return $html;
	}
}

if ( ! function_exists( 'ideapark_ajax_add_to_cart' ) ) {
	function ideapark_ajax_add_to_cart() {
		WC_AJAX::get_refreshed_fragments();
	}

	add_action( 'wc_ajax_ip_add_to_cart', 'ideapark_ajax_add_to_cart' );
	add_action( 'wc_ajax_nopriv_ip_add_to_cart', 'ideapark_ajax_add_to_cart' );
}

if ( IDEAPARK_IS_AJAX_IMAGES ) {
	add_action( 'wp_ajax_ideapark_product_images', 'ideapark_ajax_product_images' );
	add_action( 'wp_ajax_nopriv_ideapark_product_images', 'ideapark_ajax_product_images' );
} else {
	add_action( 'init', 'ideapark_attribute_hint' );
	add_action( 'wp_loaded', 'ideapark_setup_woocommerce', 99 );

	add_action( 'wc_ajax_ideapark_ajax_product', 'ideapark_ajax_product' );
	add_action( 'wp_ajax_ideapark_ajax_product', 'ideapark_ajax_product' );
	add_action( 'wp_ajax_nopriv_ideapark_ajax_product', 'ideapark_ajax_product' );

	add_action( 'wp_ajax_ideapark_ajax_variable_scripts', 'ideapark_ajax_variable_scripts' );
	add_action( 'wp_ajax_nopriv_ideapark_ajax_variable_scripts', 'ideapark_ajax_variable_scripts' );

	add_action( 'wp_ajax_ideapark_ajax_attribute_hint', 'ideapark_ajax_attribute_hint' );
	add_action( 'wp_ajax_nopriv_ideapark_ajax_attribute_hint', 'ideapark_ajax_attribute_hint' );

	add_action( 'woocommerce_ajax_added_to_cart', 'ideapark_add_to_cart_ajax_notice' );

	add_filter( 'woocommerce_enqueue_styles', '__return_false' );
	add_filter( 'woocommerce_add_to_cart_fragments', 'ideapark_header_add_to_cart_fragment' );
	add_filter( 'woocommerce_breadcrumb_defaults', 'ideapark_woocommerce_breadcrumbs' );
	add_filter( 'woocommerce_account_menu_items', 'ideapark_woocommerce_account_menu_items' );
	add_filter( 'woocommerce_product_description_heading', 'ideapark_remove_product_description_heading' );
	add_filter( 'woocommerce_loop_add_to_cart_link', 'ideapark_loop_add_to_cart_link', 99, 3 );
	add_filter( 'woocommerce_gallery_image_size', 'ideapark_woocommerce_gallery_image_size', 99, 1 );
	add_filter( 'woocommerce_loop_add_to_cart_args', 'ideapark_woocommerce_loop_add_to_cart_args', 99 );
	add_filter( 'woocommerce_available_variation', 'ideapark_woocommerce_available_variation', 100, 3 );
	add_filter( 'woocommerce_pagination_args', 'ideapark_woocommerce_pagination_args' );
	add_filter( 'subcategory_archive_thumbnail_size', 'ideapark_subcategory_archive_thumbnail_size', 99, 1 );
	add_filter( 'woocommerce_before_widget_product_list', 'ideapark_woocommerce_before_widget_product_list' );
	add_filter( 'woocommerce_demo_store', 'ideapark_woocommerce_demo_store' );
	add_filter( 'woocommerce_product_tabs', 'ideapark_woocommerce_product_tabs', 11 );
	add_action( 'woocommerce_attribute_updated', 'ideapark_WPML_attribute_title', 10, 2 );
	add_action( 'woocommerce_attribute_added', 'ideapark_WPML_attribute_title', 10, 2 );
}

add_filter( 'woocommerce_product_subcategories_args', 'ideapark_woocommerce_hide_uncategorized' );
add_filter( 'woocommerce_product_categories_widget_args', 'ideapark_woocommerce_hide_uncategorized' );
add_filter( 'woocommerce_product_categories_widget_dropdown_args', 'ideapark_woocommerce_hide_uncategorized' );