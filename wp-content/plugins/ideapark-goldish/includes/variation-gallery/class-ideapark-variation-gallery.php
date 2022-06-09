<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ideapark_Variation_Gallery {

	private $dir;
	private $assets_dir;
	private $assets_url;

	public function __construct() {
		$this->dir        = dirname( __FILE__ );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( rtrim( plugins_url( '/assets/', __FILE__ ), '/' ) );

		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'output' ], 10, 3 );
		add_action( 'dokan_product_after_variable_attributes', [ $this, 'output' ], 10, 3 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 10, 1 );
		add_action( 'admin_footer', [ $this, 'admin_template' ] );
		add_action( 'woocommerce_save_product_variation', [ $this, 'save' ], 10, 2 );
		add_filter( 'woocommerce_available_variation', [ $this, 'available_variation_gallery' ], 110, 3 );

		add_action( 'wp_ajax_ideapark_variation_images', [ $this, 'variation_images' ] );
		add_action( 'wp_ajax_nopriv_ideapark_variation_images', [ $this, 'variation_images' ] );
	}

	public function admin_template() { ?>
		<script type="text/html" id="tmpl-ideapark-variation-gallery-image">
			<li class="ideapark-variation-gallery-image">
				<input class="wvg_variation_id_input" type="hidden"
					   name="ideapark_variation_gallery[{{data.product_variation_id}}][]" value="{{data.id}}">
				<img src="{{data.url}}" alt="">
				<a onclick="return false" class="ideapark-variation-remove"><span
						class="dashicons dashicons-dismiss"></span></a>
			</li>
		</script>
	<?php }

	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'ideapark-variation-gallery-admin', $this->assets_url . '/variation-gallery.css', [], ideapark_mtime( $this->assets_dir . '/variation-gallery.css' ) );
		wp_enqueue_script( 'ideapark-variation-gallery-admin', $this->assets_url . '/variation-gallery.js', [
			'jquery',
			'jquery-ui-sortable',
			'wp-util'
		], ideapark_mtime( $this->assets_dir . '/variation-gallery.js' ), true );
		wp_localize_script( 'ideapark-variation-gallery-admin', 'ideapark_variation_vars', array(
			'choose_image' => esc_html__( 'Choose Image', 'ideapark-goldish' ),
			'add_image'    => esc_html__( 'Add Images', 'ideapark-goldish' )
		) );
	}

	public function output( $loop, $variation_data, $variation ) {
		$variation_id   = absint( $variation->ID );
		$gallery_images = get_post_meta( $variation_id, 'ideapark_variation_images', true );
		?>
		<div data-product_variation_id="<?php echo esc_attr( $variation_id ) ?>"
			 class="form-row form-row-full ideapark-variation-gallery-wrapper">
			<div><?php esc_html_e( 'Variation Image Gallery', 'ideapark-goldish' ) ?></div>
			<div class="ideapark-variation-gallery-image-container">
				<ul class="ideapark-variation-gallery-images">
					<?php
					if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
						foreach ( $gallery_images as $image_id ) {
							$image = wp_get_attachment_image_src( $image_id );
							?>
							<li class="ideapark-variation-gallery-image">
								<input type="hidden"
									   name="ideapark_variation_gallery[<?php echo esc_attr( $variation_id ) ?>][]"
									   value="<?php echo $image_id ?>">
								<img src="<?php echo esc_url( $image[0] ) ?>" alt="">
								<a onclick="return false" class="ideapark-variation-remove"><span
										class="dashicons dashicons-dismiss"></span></a>
							</li>
						<?php }
					}
					?>
				</ul>
			</div>
			<p class="ideapark-variation-button-wrap hide-if-no-js">
				<a onclick="return false" data-product_variation_loop="<?php echo absint( $loop ) ?>"
				   data-product_variation_id="<?php echo esc_attr( $variation_id ) ?>"
				   class="button ideapark-variation-add"><?php esc_html_e( 'Add Gallery Images', 'ideapark-goldish' ) ?></a>
			</p>
		</div>
		<?php
	}

	public function save( $variation_id, $loop ) {
		if ( isset( $_POST['ideapark_variation_gallery'] ) ) {

			if ( isset( $_POST['ideapark_variation_gallery'][ $variation_id ] ) ) {

				$gallery_image_ids = (array) array_map( 'absint', $_POST['ideapark_variation_gallery'][ $variation_id ] );
				update_post_meta( $variation_id, 'ideapark_variation_images', $gallery_image_ids );
			} else {
				delete_post_meta( $variation_id, 'ideapark_variation_images' );
			}
		} else {
			delete_post_meta( $variation_id, 'ideapark_variation_images' );
		}
	}

	public function variation_images() {
		if ( isset( $_POST['variation_id'] ) && ( $variation_id = absint( $_POST['variation_id'] ) ) && get_post_meta( $variation_id, 'ideapark_variation_images', true ) ) {
			$product_id   = wp_get_post_parent_id( $variation_id );
			$is_quickview = ! empty( $_POST['is_quickview'] );
			if ( $is_quickview ) {
				ideapark_mod_set_temp( '_is_quickview', true );
				ideapark_mod_set_temp( 'shop_product_modal', false );
			}
			wc_get_template( 'single-product/product-image.php', [
				'has_variation_gallery_images' => true,
				'product_id'                   => $product_id,
				'images'                       => ideapark_product_images( $product_id, $variation_id ),
			] );
		}
		die();
	}

	public function available_variation_gallery( $available_variation, $variationProductObject, $variation ) {
		$variation_id = absint( $variation->get_id() );

		$available_variation['has_variation_gallery_images'] = (bool) get_post_meta( $variation_id, 'ideapark_variation_images', true );

		return $available_variation;
	}
}

new Ideapark_Variation_Gallery();