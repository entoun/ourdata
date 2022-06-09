<?php

namespace WCPM\Classes\Pixels;

use  WCPM\Classes\Http\Facebook_CAPI ;
use  WCPM\Classes\Http\Google_MP ;
use  WCPM\Classes\Pixels\Facebook\Facebook_Microdata ;
use  WCPM\Classes\Pixels\Facebook\Facebook_Pixel_Manager ;
use  WCPM\Classes\Pixels\Facebook\Facebook_Pixel_Manager_Microdata ;
use  WCPM\Classes\Pixels\Google\Google ;
use  WCPM\Classes\Pixels\Google\Google_Analytics_Refund ;
use  WCPM\Classes\Pixels\Google\Google_Pixel_Manager ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Pixel_Manager extends Script_Manager
{
    use  Trait_Product ;
    use  Trait_Shop ;
    protected  $options ;
    protected  $options_obj ;
    protected  $cart ;
    protected  $facebook_active ;
    protected  $google_active ;
    protected  $dyn_r_ids ;
    protected  $transaction_deduper_timeout = 1000 ;
    protected  $position = 1 ;
    protected  $google ;
    protected  $microdata_product_id ;
    public function __construct( $options )
    {
        /**
         * Initialize options
         */
        $this->options = $options;
        $this->options_obj = json_decode( wp_json_encode( $this->options ) );
        $this->options_obj->shop->currency = get_woocommerce_currency();
        /**
         * Set a few states
         */
        $this->facebook_active = !empty($this->options_obj->facebook->pixel_id);
        //		$this->google_active   = $this->google_active();
        $this->google = new Google( $this->options );
        $this->google_active = $this->google->google_active();
        /**
         * Inject WPM snippets in head
         */
        add_action( 'wp_head', function () {
            $this->inject_wpm_opening();
            if ( wpm_fs()->is__premium_only() && is_product() ) {
                if ( $this->options_obj->facebook->microdata ) {
                    $this->microdata_product_id = ( new Facebook_Microdata( $this->options ) )->inject_schema( wc_get_product( get_the_ID() ) );
                }
            }
            $this->inject_data_layer();
        } );
        /**
         * Initialize all pixels
         */
        if ( $this->google_active ) {
            new Google_Pixel_Manager( $this->options );
        }
        if ( $this->facebook_active ) {
            new Facebook_Pixel_Manager( $this->options );
        }
        add_action( 'wp_head', function () {
            $this->inject_wpm_closing();
        } );
        /**
         * Front-end script section
         */
        if ( $this->track_user() ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'wpm_front_end_scripts' ] );
        }
        add_action( 'wp_ajax_wpm_get_cart_items', [ $this, 'ajax_wpm_get_cart_items' ] );
        add_action( 'wp_ajax_nopriv_wpm_get_cart_items', [ $this, 'ajax_wpm_get_cart_items' ] );
        add_action( 'wp_ajax_wpm_get_product_ids', [ $this, 'ajax_wpm_get_product_ids' ] );
        add_action( 'wp_ajax_nopriv_wpm_get_product_ids', [ $this, 'ajax_wpm_get_product_ids' ] );
        add_action( 'wp_ajax_wpm_purchase_pixels_fired', [ $this, 'ajax_purchase_pixels_fired_handler' ] );
        add_action( 'wp_ajax_nopriv_wpm_purchase_pixels_fired', [ $this, 'ajax_purchase_pixels_fired_handler' ] );
        // Experimental filter ! Can be removed without further notification
        if ( apply_filters( 'wpm_experimental_defer_scripts', false ) ) {
            add_filter(
                'script_loader_tag',
                [ $this, 'experimental_defer_scripts' ],
                10,
                2
            );
        }
        /**
         * Inject pixel snippets after <body> tag
         */
        if ( did_action( 'wp_body_open' ) ) {
            add_action( 'wp_body_open', function () {
                $this->inject_body_pixels();
            } );
        }
        /**
         * Inject pixel snippets into wp_footer
         */
        add_action( 'wp_footer', [ $this, 'wpm_wp_footer' ] );
        /**
         * Process short codes
         */
        new Shortcodes( $this->options );
        add_action(
            'woocommerce_after_shop_loop_item',
            [ $this, 'action_woocommerce_after_shop_loop_item' ],
            10,
            1
        );
        add_filter(
            'woocommerce_blocks_product_grid_item_html',
            [ $this, 'wc_add_date_to_gutenberg_block' ],
            10,
            3
        );
        add_action( 'wp_head', [ $this, 'woocommerce_inject_product_data_on_product_page' ] );
        // do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
        add_action(
            'woocommerce_after_cart_item_name',
            [ $this, 'woocommerce_after_cart_item_name' ],
            10,
            2
        );
        add_action(
            'woocommerce_after_mini_cart_item_name',
            [ $this, 'woocommerce_after_cart_item_name' ],
            10,
            2
        );
        add_action( 'woocommerce_mini_cart_contents', [ $this, 'woocommerce_mini_cart_contents' ] );
        add_action( 'woocommerce_new_order', [ $this, 'wpm_woocommerce_new_order' ] );
        /**
         * Run background processes
         */
        add_action( 'template_redirect', [ $this, 'run_background_processes' ] );
    }
    
    public function run_background_processes()
    {
    }
    
    public function wpm_woocommerce_new_order( $order_id )
    {
        /**
         * All new orders should be marked as long WPM is active,
         * so that we know we can process them later through WPM,
         * and so that we know we should not touch orders that were
         * placed before WPM was active.
         */
        add_post_meta(
            $order_id,
            '_wpm_process_through_wpm',
            true,
            true
        );
        /**
         * Set a custom user ID on the order
         * because WC sets 0 on all order created
         * manually through the back-end.
         */
        $user_id = 0;
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
        }
        add_post_meta(
            $order_id,
            '_wpm_customer_user',
            $user_id,
            true
        );
    }
    
    // Thanks to: https://gist.github.com/mishterk/6b7a4d6e5a91086a5a9b05ace304b5ce#file-mark-wordpress-scripts-as-async-or-defer-php
    public function experimental_defer_scripts( $tag, $handle )
    {
        if ( 'wpm' !== $handle ) {
            return $tag;
        }
        return str_replace( ' src', ' defer src', $tag );
        // defer the script
    }
    
    public function woocommerce_mini_cart_contents()
    {
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $this->woocommerce_after_cart_item_name( $cart_item, $cart_item_key );
        }
    }
    
    public function woocommerce_after_cart_item_name( $cart_item, $cart_item_key )
    {
        $data = [
            'product_id'   => $cart_item['product_id'],
            'variation_id' => $cart_item['variation_id'],
        ];
        ?>
		<script>
			window.wpmDataLayer.cartItemKeys                                          = window.wpmDataLayer.cartItemKeys || {}
			window.wpmDataLayer.cartItemKeys['<?php 
        echo  esc_js( $cart_item_key ) ;
        ?>'] = <?php 
        echo  wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ;
        ?>;
		</script>

		<?php 
    }
    
    // on product page
    public function woocommerce_inject_product_data_on_product_page()
    {
        
        if ( is_product() ) {
            $product = wc_get_product( get_the_id() );
            
            if ( is_object( $product ) ) {
                $this->get_product_data_layer_script( $product, false, true );
            } else {
                wc_get_logger()->debug( 'woocommerce_inject_product_data_on_product_page provided no product on a product page: .' . get_the_id(), [
                    'source' => 'wpm',
                ] );
            }
            
            if ( $product->is_type( 'grouped' ) ) {
                foreach ( $product->get_children() as $product_id ) {
                    $product = wc_get_product( $product_id );
                    
                    if ( is_object( $product ) ) {
                        $this->get_product_data_layer_script( $product, false, true );
                    } else {
                        $this->log_problematic_product_id( $product_id );
                    }
                
                }
            }
            if ( $product->is_type( 'variable' ) ) {
                /**
                 * Stop inspection
                 *
                 * @noinspection PhpPossiblePolymorphicInvocationInspection
                 */
                foreach ( $product->get_available_variations() as $key => $variation ) {
                    $variable_product = wc_get_product( $variation['variation_id'] );
                    
                    if ( is_object( $variable_product ) ) {
                        $this->get_product_data_layer_script( $variable_product, false, true );
                    } else {
                        $this->log_problematic_product_id( $variation['variation_id'] );
                    }
                
                }
            }
        }
    
    }
    
    // every product that's generated by the shop loop like shop page or a shortcode
    public function action_woocommerce_after_shop_loop_item()
    {
        global  $product ;
        $this->get_product_data_layer_script( $product );
    }
    
    // product views generated by a gutenberg block instead of a shortcode
    public function wc_add_date_to_gutenberg_block( $html, $data, $product )
    {
        return $html . $this->buffer_get_product_data_layer_script( $product );
    }
    
    public function wpm_wp_footer()
    {
        if ( wpm_fs()->is__premium_only() && $this->options_obj->google->analytics->eec ) {
            ( new Google_Analytics_Refund( $this->options ) )->process_refund_to_frontend__premium_only();
        }
    }
    
    // https://support.cloudflare.com/hc/en-us/articles/200169436-How-can-I-have-Rocket-Loader-ignore-specific-JavaScripts-
    private function inject_data_layer()
    {
        ?>

		<script>

			window.wpmDataLayer = window.wpmDataLayer || {}
			window.wpmDataLayer = <?php 
        echo  wp_json_encode( $this->get_data_for_data_layer(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ;
        ?>;

		</script>

		<?php 
    }
    
    /**
     * Set up the wpmDataLayer
     *
     * @return mixed|void
     */
    protected function get_data_for_data_layer()
    {
        /**
         * Load and set some defaults.
         */
        $data = [
            'cart'                => (object) [],
            'cart_item_keys'      => (object) [],
            'orderDeduplication'  => $this->options['shop']['order_deduplication'] && !$this->is_nodedupe_parameter_set(),
            'position'            => 1,
            'viewItemListTrigger' => $this->view_item_list_trigger_settings(),
            'version'             => [
            'number' => WPM_CURRENT_VERSION,
            'pro'    => wpm_fs()->is__premium_only(),
        ],
        ];
        /**
         * Load the pixels
         */
        $data['pixels'] = $this->get_pixel_data();
        /**
         * Load remaining settings
         */
        $data = array_merge( $data, $this->get_order_data() );
        $data['shop'] = $this->get_shop_data();
        $data['general'] = $this->get_general_data();
        $data['user'] = $this->get_user_data();
        // Return and optionally modify the wpm data layer
        return apply_filters( 'wpm_experimental_data_layer', $data );
    }
    
    protected function get_pixel_data()
    {
        $data = [];
        if ( $this->google->is_google_active() ) {
            $data['google'] = $this->get_google_pixel_data();
        }
        if ( $this->options_obj->bing->uet_tag_id ) {
            $data['bing'] = $this->get_bing_pixel_data();
        }
        if ( $this->options_obj->facebook->pixel_id ) {
            $data['facebook'] = $this->get_facebook_pixel_data();
        }
        if ( $this->options_obj->hotjar->site_id ) {
            $data['hotjar'] = $this->get_hotjar_pixel_data();
        }
        if ( $this->options_obj->pinterest->pixel_id ) {
            $data['pinterest'] = $this->get_pinterest_pixel_data();
        }
        if ( $this->options_obj->snapchat->pixel_id ) {
            $data['snapchat'] = $this->get_snapchat_pixel_data();
        }
        if ( $this->options_obj->tiktok->pixel_id ) {
            $data['tiktok'] = $this->get_tiktok_pixel_data();
        }
        if ( $this->options_obj->twitter->pixel_id ) {
            $data['twitter'] = $this->get_twitter_pixel_data();
        }
        return $data;
    }
    
    protected function get_google_pixel_data()
    {
        $data = [
            'linker'  => [
            'settings' => $this->google->get_google_linker_settings(),
        ],
            'user_id' => (bool) $this->options_obj->google->user_id,
        ];
        if ( $this->google->is_google_ads_active() ) {
            $data['ads'] = [
                'conversionIds'            => (object) $this->google->get_google_ads_conversion_ids( is_order_received_page() ),
                'dynamic_remarketing'      => [
                'status'                      => (bool) $this->options_obj->google->ads->dynamic_remarketing,
                'id_type'                     => $this->get_dyn_r_id_type( 'google' ),
                'send_events_with_parent_ids' => apply_filters( 'wpm_send_events_with_parent_ids', apply_filters_deprecated(
                'wooptpm_send_events_with_parent_ids',
                [ true ],
                '1.13.0',
                'wpm_send_events_with_parent_ids'
            ) ),
            ],
                'google_business_vertical' => $this->google->get_google_business_vertical( $this->options['google']['ads']['google_business_vertical'] ),
                'phone_conversion_label'   => $this->options_obj->google->ads->phone_conversion_label,
                'phone_conversion_number'  => $this->options_obj->google->ads->phone_conversion_number,
            ];
        }
        if ( $this->google->is_google_analytics_active() ) {
            $data['analytics'] = [
                'universal' => [
                'property_id' => $this->options_obj->google->analytics->universal->property_id,
                'parameters'  => (object) $this->google->get_ga_ua_parameters( $this->options_obj->google->analytics->universal->property_id ),
                'mp_active'   => wpm_fs()->is__premium_only(),
            ],
                'ga4'       => [
                'measurement_id' => $this->options_obj->google->analytics->ga4->measurement_id,
                'parameters'     => (object) $this->google->get_ga4_parameters( $this->options_obj->google->analytics->ga4->measurement_id ),
                'mp_active'      => $this->options_obj->google->analytics->ga4->api_secret && wpm_fs()->is__premium_only(),
            ],
                'id_type'   => $this->google->get_ga_id_type(),
                'eec'       => (bool) $this->options_obj->google->analytics->eec,
            ];
        }
        if ( $this->google->is_google_optimize_active() ) {
            $data['optimize'] = [
                'container_id' => $this->options_obj->google->optimize->container_id,
            ];
        }
        return $data;
    }
    
    protected function get_bing_pixel_data()
    {
        return [
            'uet_tag_id'          => $this->options_obj->bing->uet_tag_id,
            'dynamic_remarketing' => [
            'id_type' => $this->get_dyn_r_id_type( 'bing' ),
        ],
        ];
    }
    
    protected function get_facebook_pixel_data()
    {
        $data = [
            'pixel_id'            => $this->options_obj->facebook->pixel_id,
            'dynamic_remarketing' => [
            'id_type' => $this->get_dyn_r_id_type( 'facebook' ),
        ],
            'capi'                => (bool) $this->options_obj->facebook->capi->token,
        ];
        if ( wpm_fs()->is__premium_only() && is_product() && $this->options_obj->facebook->microdata ) {
            $data['microdata_product_id'] = $this->microdata_product_id;
        }
        return $data;
    }
    
    protected function get_hotjar_pixel_data()
    {
        return [
            'site_id' => $this->options_obj->hotjar->site_id,
        ];
    }
    
    protected function get_pinterest_pixel_data()
    {
        return [
            'pixel_id'             => $this->options_obj->pinterest->pixel_id,
            'dynamic_remarketing'  => [
            'id_type' => $this->get_dyn_r_id_type( 'pinterest' ),
        ],
            'enhanced_match'       => apply_filters( 'wpm_pinterest_enhanced_match', apply_filters_deprecated(
            'wooptpm_pinterest_enhanced_match',
            [ false ],
            '1.13.0',
            'wpm_pinterest_enhanced_match'
        ) ),
            'enhanced_match_email' => $this->get_user_email(),
        ];
    }
    
    protected function get_snapchat_pixel_data()
    {
        return [
            'pixel_id'            => $this->options_obj->snapchat->pixel_id,
            'dynamic_remarketing' => [
            'id_type' => $this->get_dyn_r_id_type( 'snapchat' ),
        ],
        ];
    }
    
    protected function get_tiktok_pixel_data()
    {
        return [
            'pixel_id'            => $this->options_obj->tiktok->pixel_id,
            'dynamic_remarketing' => [
            'id_type' => $this->get_dyn_r_id_type( 'tiktok' ),
        ],
            'purchase_event_name' => apply_filters( 'wpm_tiktok_purchase_event_name', 'PlaceAnOrder' ),
        ];
    }
    
    protected function get_twitter_pixel_data()
    {
        return [
            'pixel_id'            => $this->options_obj->twitter->pixel_id,
            'dynamic_remarketing' => [
            'id_type' => $this->get_dyn_r_id_type( 'twitter' ),
        ],
        ];
    }
    
    protected function get_order_data()
    {
        if ( !is_order_received_page() ) {
            return [];
        }
        $order = $this->get_order_from_order_received_page();
        if ( $order && !$this->can_order_confirmation_be_processed( $order ) ) {
            return [];
        }
        $data = [];
        
        if ( $order ) {
            $data['order'] = [
                'id'               => (int) $order->get_id(),
                'number'           => (string) $order->get_order_number(),
                'affiliation'      => (string) get_bloginfo( 'name' ),
                'currency'         => (string) $this->get_order_currency( $order ),
                'value_filtered'   => (double) $this->wpm_get_order_total( $order ),
                'value_regular'    => (double) $order->get_total(),
                'discount'         => (double) $order->get_total_discount(),
                'tax'              => (double) $order->get_total_tax(),
                'shipping'         => (double) $order->get_shipping_total(),
                'coupon'           => implode( ',', $order->get_coupon_codes() ),
                'aw_merchant_id'   => ( (int) $this->options['google']['ads']['aw_merchant_id'] ? (int) $this->options['google']['ads']['aw_merchant_id'] : '' ),
                'aw_feed_country'  => (string) $this->get_visitor_country(),
                'aw_feed_language' => (string) $this->google->get_gmc_language(),
                'new_customer'     => !$this->is_existing_customer( $order ),
                'quantity'         => (int) count( $this->wpm_get_order_items( $order ) ),
                'items'            => $this->get_front_end_order_items( $order ),
            ];
            $data['products'] = $this->get_order_products( $order );
        }
        
        return $data;
    }
    
    protected function get_user_data()
    {
        $data = [];
        
        if ( is_user_logged_in() || is_order_received_page() ) {
            $data['id'] = get_current_user_id();
            $data['email'] = $this->get_user_email();
            $data['email_sha256'] = $this->get_user_email( 'sha256' );
        }
        
        return $data;
    }
    
    protected function get_order_products( $order )
    {
        $order_products = [];
        foreach ( (array) $this->wpm_get_order_items( $order ) as $order_item ) {
            $order_item_data = $order_item->get_data();
            if ( 0 !== $order_item_data['variation_id'] ) {
                // add variation
                $order_products[$order_item_data['variation_id']] = $this->get_product_data( $order_item_data['variation_id'] );
            }
            $order_products[$order_item_data['product_id']] = $this->get_product_data( $order_item_data['product_id'] );
        }
        return $order_products;
    }
    
    protected function get_product_data( $product_id )
    {
        $product = wc_get_product( $product_id );
        
        if ( !is_object( $product ) ) {
            $this->log_problematic_product_id( $product_id );
            return [];
        }
        
        return [
            'product_id'   => $product->get_id(),
            'name'         => $product->get_name(),
            'type'         => $product->get_type(),
            'dyn_r_ids'    => $this->get_dyn_r_ids( $product ),
            'brand'        => (string) $this->get_brand_name( $product_id ),
            'category'     => (array) $this->get_product_category( $product_id ),
            'variant_name' => ( (string) ($product->get_type() === 'variation') ? $this->get_formatted_variant_text( $product ) : '' ),
        ];
    }
    
    public function view_item_list_trigger_settings()
    {
        $settings = [
            'testMode'        => false,
            'backgroundColor' => 'green',
            'opacity'         => 0.5,
            'repeat'          => true,
            'timeout'         => 1000,
            'threshold'       => 0.8,
        ];
        $settings = apply_filters_deprecated(
            'wooptpm_view_item_list_trigger_settings',
            [ $settings ],
            '1.13.0',
            'wpm_view_item_list_trigger_settings'
        );
        return apply_filters( 'wpm_view_item_list_trigger_settings', $settings );
    }
    
    public function inject_wpm_opening()
    {
        echo  PHP_EOL . '<!-- START WooCommerce Pixel Manager -->' . PHP_EOL ;
    }
    
    public function inject_wpm_closing()
    {
        if ( is_order_received_page() ) {
            
            if ( $this->get_order_from_order_received_page() ) {
                $order = $this->get_order_from_order_received_page();
                $this->increase_conversion_count_for_ratings( $order );
            }
        
        }
        echo  PHP_EOL . '<!-- END WooCommerce Pixel Manager -->' . PHP_EOL ;
    }
    
    private function increase_conversion_count_for_ratings( $order )
    {
        
        if ( $this->can_order_confirmation_be_processed( $order ) ) {
            $ratings = get_option( WPM_DB_RATINGS );
            $ratings['conversions_count'] = $ratings['conversions_count'] + 1;
            update_option( WPM_DB_RATINGS, $ratings );
        } else {
            $this->conversion_pixels_already_fired_html();
        }
    
    }
    
    public function ajax_wpm_get_cart_items()
    {
        global  $woocommerce ;
        $cart_items = $woocommerce->cart->get_cart();
        $data = [];
        foreach ( $cart_items as $cart_item => $value ) {
            $product = wc_get_product( $value['data']->get_id() );
            
            if ( !is_object( $product ) ) {
                $this->log_problematic_product_id( $value['data']->get_id() );
                continue;
            }
            
            $data['cart_item_keys'][$cart_item] = [
                'id'          => (string) $product->get_id(),
                'isVariation' => false,
            ];
            $data['cart'][$product->get_id()] = [
                'id'          => (string) $product->get_id(),
                'dyn_r_ids'   => $this->get_dyn_r_ids( $product ),
                'name'        => $product->get_name(),
                'brand'       => $this->get_brand_name( $product->get_id() ),
                'quantity'    => (int) $value['quantity'],
                'price'       => (double) $product->get_price(),
                'isVariation' => false,
            ];
            
            if ( 'variation' === $product->get_type() ) {
                $parent_product = wc_get_product( $product->get_parent_id() );
                
                if ( $parent_product ) {
                    $data['cart'][$product->get_id()]['name'] = $parent_product->get_name();
                    $data['cart'][$product->get_id()]['parentId'] = (string) $parent_product->get_id();
                    $data['cart'][$product->get_id()]['parentId_dyn_r_ids'] = $this->get_dyn_r_ids( $parent_product );
                } else {
                    wc_get_logger()->debug( 'Variation ' . $product->get_id() . ' doesn\'t link to a valid parent product.', [
                        'source' => 'wpm',
                    ] );
                }
                
                $data['cart'][$product->get_id()]['isVariation'] = true;
                $data['cart'][$product->get_id()]['category'] = $this->get_product_category( $product->get_parent_id() );
                $variant_text_array = [];
                $attributes = $product->get_attributes();
                if ( $attributes ) {
                    foreach ( $attributes as $key => $value ) {
                        $key_name = str_replace( 'pa_', '', $key );
                        $variant_text_array[] = ucfirst( $key_name ) . ': ' . strtolower( $value );
                    }
                }
                $data['cart'][$product->get_id()]['variant'] = (string) implode( ' | ', $variant_text_array );
                $data['cart_item_keys'][$cart_item]['parentId'] = (string) $product->get_parent_id();
                $data['cart_item_keys'][$cart_item]['isVariation'] = true;
            } else {
                $data['cart'][$product->get_id()]['category'] = $this->get_product_category( $product->get_id() );
            }
        
        }
        wp_send_json( $data );
    }
    
    public function ajax_wpm_get_product_ids()
    {
        $_get = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $product_ids = $_get['productIds'];
        
        if ( !$product_ids ) {
            wp_send_json_error();
            return;
        }
        
        $products = [];
        foreach ( $product_ids as $key => $product_id ) {
            // validate if a valid product ID has been passed in the array
            if ( !ctype_digit( $product_id ) ) {
                continue;
            }
            $product = wc_get_product( $product_id );
            
            if ( !is_object( $product ) ) {
                wc_get_logger()->debug( 'ajax_wpm_get_product_ids received an invalid product', [
                    'source' => 'wpm',
                ] );
                continue;
            }
            
            $products[$product_id] = $this->get_product_details_for_datalayer( $product );
        }
        wp_send_json( $products );
    }
    
    public function ajax_purchase_pixels_fired_handler()
    {
        //        if (!check_ajax_referer('wpm-premium-only-nonce', 'nonce', false)) {
        //            wp_send_json_error('Invalid security token sent.');
        //            error_log('Invalid security token sent.');
        //            wp_die();
        //        }
        $_post = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $order_id = $_post['order_id'];
        update_post_meta( $order_id, '_wpm_conversion_pixel_fired', true );
        wp_send_json_success();
        wp_die();
        // this is required to terminate immediately and return a proper response
    }
    
    public function wpm_front_end_scripts()
    {
        $wpm_dependencies = [ 'jquery' ];
        // enable polyfill.io with filter
        
        if ( wpm_fs()->is__premium_only() && apply_filters( 'wpm_experimental_inject_polyfill_io', false ) ) {
            wp_enqueue_script(
                'polyfill-io',
                'https://cdn.polyfill.io/v2/polyfill.min.js',
                false,
                WPM_CURRENT_VERSION,
                false
            );
            $wpm_dependencies[] = 'polyfill-io';
        }
        
        wp_enqueue_script(
            'wpm',
            WPM_PLUGIN_DIR_PATH . 'js/public/wpm-public.p1.min.js',
            $wpm_dependencies,
            WPM_CURRENT_VERSION,
            $this->move_wpm_script_to_footer()
        );
        wp_localize_script(
            'wpm',
            //            'ajax_object',
            'wpm',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            ]
        );
    }
    
    protected function move_wpm_script_to_footer()
    {
        // this filter moves the wpm script to the footer
        return apply_filters( 'wpm_experimental_move_wpm_script_to_footer', false );
    }
    
    private function get_preset_version()
    {
        return '.p' . apply_filters( 'wpm_script_optimization_preset_version', 1 );
    }
    
    public function inject_order_received_page_dedupe( $order, $order_total, $is_new_customer )
    {
        // nothing to do
    }
    
    private function inject_body_pixels()
    {
        //        $this->google_pixel_manager->inject_google_optimize_anti_flicker_snippet();
    }
    
    private function get_shop_data()
    {
        $data = [];
        
        if ( is_product_category() ) {
            $data['list_name'] = 'Product Category' . $this->get_list_name_suffix();
            $data['list_id'] = 'product_category' . $this->get_list_id_suffix();
            $data['page_type'] = 'product_category';
        } elseif ( is_product_tag() ) {
            $data['list_name'] = 'Product Tag' . $this->get_list_name_suffix();
            $data['list_id'] = 'product_tag' . $this->get_list_id_suffix();
            $data['page_type'] = 'product_tag';
        } elseif ( is_search() ) {
            $data['list_name'] = 'Product Search';
            $data['list_id'] = 'search';
            $data['page_type'] = 'search';
        } elseif ( is_shop() ) {
            $data['list_name'] = 'Shop | page number: ' . $this->get_page_number();
            $data['list_id'] = 'product_shop_page_number_' . $this->get_page_number();
            $data['page_type'] = 'product_shop';
        } elseif ( is_product() ) {
            $data['list_name'] = 'Product | ' . $this->wpm_get_the_title();
            $data['list_id'] = 'product_' . sanitize_title( get_the_title() );
            $data['page_type'] = 'product';
            $product = wc_get_product();
            $data['product_type'] = $product->get_type();
        } elseif ( is_cart() ) {
            $data['list_name'] = 'Cart';
            $data['list_id'] = 'cart';
            $data['page_type'] = 'cart';
        } elseif ( is_front_page() ) {
            $data['list_name'] = 'Front Page';
            $data['list_id'] = 'front_page';
            $data['page_type'] = 'front_page';
        } elseif ( is_order_received_page() ) {
            $data['list_name'] = 'Order Received Page';
            $data['list_id'] = 'order_received_page';
            $data['page_type'] = 'order_received_page';
        } elseif ( is_checkout() ) {
            $data['list_name'] = 'Checkout Page';
            $data['list_id'] = 'checkout';
            $data['page_type'] = 'checkout';
        } elseif ( is_page() ) {
            $data['list_name'] = 'Page | ' . $this->wpm_get_the_title();
            $data['list_id'] = 'page_' . sanitize_title( get_the_title() );
            $data['page_type'] = 'page';
        } elseif ( is_home() ) {
            $data['list_name'] = 'Blog Home';
            $data['list_id'] = 'blog_home';
            $data['page_type'] = 'blog_post';
        } elseif ( 'post' === get_post_type() ) {
            $data['list_name'] = 'Blog Post | ' . $this->wpm_get_the_title();
            $data['list_id'] = 'blog_post_' . sanitize_title( get_the_title() );
            $data['page_type'] = 'blog_post';
        } else {
            $data['list_name'] = '';
            $data['list_id'] = '';
            $data['page_type'] = '';
        }
        
        $data['currency'] = get_woocommerce_currency();
        //		$data['mini_cart']['track'] = apply_filters_deprecated('wooptpm_track_mini_cart', [true], '1.13.0', 'wpm_track_mini_cart');
        //		$data['mini_cart']['track'] = apply_filters('wpm_track_mini_cart', $data['mini_cart']['track']);
        $mini_cart_filter_deprecation_message = 'The filter has become obsolete since WPM now tracks cart item data using the browser cache and doesn\'t rely entirely on the server anymore.';
        apply_filters_deprecated(
            'wooptpm_track_mini_cart',
            [ true ],
            '1.13.0',
            '',
            $mini_cart_filter_deprecation_message
        );
        apply_filters_deprecated(
            'wpm_track_mini_cart',
            [ true ],
            '1.15.5',
            '',
            $mini_cart_filter_deprecation_message
        );
        $data['cookie_consent_mgmt'] = [
            'explicit_consent' => (bool) $this->options_obj->shop->cookie_consent_mgmt->explicit_consent,
        ];
        return $data;
    }
    
    protected function get_page_number()
    {
        return ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
    }
    
    private function get_general_data()
    {
        return [
            'variationsOutput' => (bool) $this->options_obj->general->variations_output,
            'userLoggedIn'     => is_user_logged_in(),
        ];
    }

}