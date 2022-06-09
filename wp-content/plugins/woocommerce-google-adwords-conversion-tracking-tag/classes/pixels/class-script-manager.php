<?php

namespace WCPM\Classes\Pixels;

use  stdClass ;
use  WCPM\Classes\Admin\Documentation ;
use  WC_Product ;
use  WC_Product_Data_Store_CPT ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Script_Manager
{
    use  Trait_Product ;
    use  Trait_Shop ;
    protected  $transaction_deduper_timeout = 2000 ;
    protected  $options ;
    protected  $options_obj ;
    public function __construct( $options )
    {
        /*
         * Initialize options
         */
        //        $this->options = get_option(WPM_DB_OPTIONS_NAME);
        $this->options = $options;
        $this->options_obj = json_decode( wp_json_encode( $this->options ) );
        //		$this->options_obj->shop->currency = new stdClass();
        $this->options_obj->shop->currency = get_woocommerce_currency();
        /*
         * Inject pixel snippets in head
         */
        add_action( 'wp_head', function () {
            $this->inject_head_pixels();
        } );
    }
    
    public function inject_head_pixels()
    {
        global  $woocommerce ;
        $_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $this->inject_opening_script_tag();
        $this->inject_everywhere();
        
        if ( is_product_category() ) {
            $this->inject_product_category();
        } elseif ( is_product_tag() ) {
            $this->inject_product_tag();
        } elseif ( is_search() ) {
            $this->inject_search();
        } elseif ( is_product() && !isset( $_post['add-to-cart'] ) ) {
            $product = wc_get_product();
            $product_id = $product->get_id();
            $product_attributes = [
                'brand' => $this->get_brand_name( $product_id ),
            ];
            if ( $product->is_type( 'variable' ) ) {
                // find out if attributes have been set in the URL
                // if not, continue
                // if yes get the variation id and variation SKU
                
                if ( $this->query_string_contains_all_variation_attributes( $product ) ) {
                    // get variation product
                    $product_id = $this->get_variation_from_query_string( $product_id, $product );
                    // In case a variable product is misconfigured, wc_get_product($product_id) will not
                    // get a product but a bool. So we need to test it and only run it if
                    // we actually get a product. Basically we fall back to the parent product.
                    if ( is_object( wc_get_product( $product_id ) ) ) {
                        $product = wc_get_product( $product_id );
                    }
                }
            
            }
            
            if ( is_object( $product ) ) {
                $product_attributes['product_id_compiled'] = $this->get_compiled_product_id(
                    $product_id,
                    $product->get_sku(),
                    $this->options,
                    ''
                );
                $product_attributes['dyn_r_ids'] = $this->get_dyn_r_ids( $product );
                $this->inject_product( $product, $product_attributes );
            } else {
                $this->log_problematic_product_id( $product_id );
            }
        
        } elseif ( $this->is_shop_top_page() ) {
            $this->inject_shop_top_page();
        } elseif ( is_cart() && !empty($woocommerce->cart->get_cart()) ) {
            $cart = $woocommerce->cart->get_cart();
            $cart_total = WC()->cart->get_cart_contents_total();
            $this->inject_cart( $cart, $cart_total );
        } elseif ( is_order_received_page() ) {
            //            $this->is_nodedupe_parameter_set();
            // get order from URL and evaluate order total
            
            if ( $this->get_order_from_order_received_page() ) {
                $order = $this->get_order_from_order_received_page();
                
                if ( is_user_logged_in() ) {
                    $user = get_current_user_id();
                } else {
                    $user = $order->get_billing_email();
                }
                
                $is_new_customer = !$this->is_existing_customer( $order, $user );
                $order_total = $this->wpm_get_order_total( $order );
                // dedupe code
                if ( $this->can_order_confirmation_be_processed( $order ) ) {
                    $this->inject_order_received_page_dedupe( $order, $order_total, $is_new_customer );
                }
                $this->inject_order_received_page_no_dedupe( $order, $order_total, $is_new_customer );
            }
        
        }
        
        $this->inject_closing_script_tag();
    }
    
    protected function can_order_confirmation_be_processed( $order )
    {
        $conversion_prevention = false;
        $conversion_prevention = apply_filters_deprecated(
            'wgact_conversion_prevention',
            [ $conversion_prevention, $order ],
            '1.10.2',
            'wooptpm_conversion_prevention'
        );
        $conversion_prevention = apply_filters_deprecated(
            'wooptpm_conversion_prevention',
            [ $conversion_prevention, $order ],
            '1.13.0',
            'wpm_conversion_prevention'
        );
        $conversion_prevention = apply_filters( 'wpm_conversion_prevention', $conversion_prevention, $order );
        //        error_log('conversion_prevention: ' . $conversion_prevention);
        //        error_log('$this->is_nodedupe_parameter_set(): ' . $this->is_nodedupe_parameter_set());
        //        error_log('$order->has_status(\'failed\'): ' . $order->has_status('failed'));
        //        error_log('current_user_can(\'edit_others_pages\'): ' . current_user_can('edit_others_pages'));
        //        error_log("this->options['shop']['order_deduplication']: " . $this->options['shop']['order_deduplication']);
        //        error_log('order id: ' . $order->get_id());
        //        error_log('order number: ' . $order->get_order_number());
        //        error_log('get_post_meta($order->get_order_number(), \'_wpm_conversion_pixel_fired\', true): ' . get_post_meta($order->get_order_number(), '_wpm_conversion_pixel_fired', true));
        
        if ( $this->is_nodedupe_parameter_set() || !$order->has_status( 'failed' ) && $this->track_user() && false == $conversion_prevention && (!$this->options['shop']['order_deduplication'] || $this->has_conversion_pixel_already_fired( $order ) != true) ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    private function has_conversion_pixel_already_fired( $order )
    {
        return false;
    }
    
    public function inject_everywhere()
    {
    }
    
    public function inject_product_category()
    {
    }
    
    public function inject_product_tag()
    {
    }
    
    public function inject_search()
    {
    }
    
    public function inject_product( $product, $product_attributes )
    {
    }
    
    public function inject_shop_top_page()
    {
    }
    
    public function inject_cart( $cart, $cart_total )
    {
    }
    
    public function inject_order_received_page_dedupe( $order, $order_total, $is_new_customer )
    {
    }
    
    protected function is_nodedupe_parameter_set()
    {
        $_get = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        
        if ( isset( $_get['nodedupe'] ) ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    protected function query_string_contains_all_variation_attributes( $product )
    {
        $_server = filter_input_array( INPUT_SERVER, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        
        if ( !empty($_server['QUERY_STRING']) ) {
            parse_str( $_server['QUERY_STRING'], $query_string_attributes );
            foreach ( array_keys( $product->get_attributes() ) as $variation_attribute => $value ) {
                if ( !array_key_exists( 'attribute_' . $value, $query_string_attributes ) ) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    
    }
    
    protected function get_variation_from_query_string( $product_id, $product )
    {
        $_server = filter_input_array( INPUT_SERVER, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        parse_str( $_server['QUERY_STRING'], $query_string_attributes );
        $search_variation_attributes = [];
        foreach ( array_keys( $product->get_attributes() ) as $variation_attribute => $value ) {
            $search_variation_attributes['attribute_' . $value] = $query_string_attributes['attribute_' . $value];
        }
        return $this->find_matching_product_variation_id( $product_id, $search_variation_attributes );
    }
    
    protected function find_matching_product_variation_id( $product_id, $attributes )
    {
        return ( new WC_Product_Data_Store_CPT() )->find_matching_product_variation( new WC_Product( $product_id ), $attributes );
    }
    
    protected function conversion_pixels_already_fired_html()
    {
        ?>

		<!--	----------------------------------------------------------------------------------------------------
				The conversion pixels have not been fired. Possible reasons:
					- The user role has been disabled for tracking.
					- The order payment has failed.
					- The pixels have already been fired. To prevent double counting the pixels are only fired once.

				If you want to test the order you have two options:
					- Turn off order duplication prevention in the advanced settings
					- Add the '&nodedupe' parameter to the order confirmation URL like this:
					  https://example.test/checkout/order-received/123/?key=wc_order_123abc&nodedupe

				More info on testing: <?php 
        esc_html_e( ( new Documentation() )->get_link( 'test_order' ) );
        ?>

				----------------------------------------------------------------------------------------------------
		-->
		<?php 
    }
    
    private function is_shop_top_page()
    {
        
        if ( !is_product() && !is_product_category() && !is_order_received_page() && !is_cart() && !is_search() && is_shop() ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    protected function inject_opening_script_tag()
    {
        echo  '   <script>' ;
    }
    
    protected function inject_closing_script_tag()
    {
        echo  '   </script>' ;
    }

}