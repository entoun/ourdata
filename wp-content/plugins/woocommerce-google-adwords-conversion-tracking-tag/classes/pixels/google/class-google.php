<?php

namespace WCPM\Classes\Pixels\Google;

use  WCPM\Classes\Admin\Environment_Check ;
use  WCPM\Classes\Pixels\Pixel ;
use  WCPM\Classes\Pixels\Trait_Shop ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Google extends Pixel
{
    use  Trait_Shop ;
    private  $google_ads_conversion_identifiers ;
    public function __construct( $options )
    {
        parent::__construct( $options );
        $this->google_business_vertical = $this->get_google_business_vertical( $this->options['google']['ads']['google_business_vertical'] );
        $this->pixel_name = 'google';
    }
    
    public function get_order_item_data( $order_item )
    {
        $product = $order_item->get_product();
        
        if ( !is_object( $product ) ) {
            wc_get_logger()->debug( 'get_order_item_data received an order item which is not a valid product: ' . $order_item->get_id(), [
                'source' => 'wpm',
            ] );
            return [];
        }
        
        $dyn_r_ids = $this->get_dyn_r_ids( $product );
        
        if ( $product->get_type() === 'variation' ) {
            $parent_product = wc_get_product( $product->get_parent_id() );
            $name = $parent_product->get_name();
        } else {
            $name = $product->get_name();
        }
        
        return [
            'id'             => (string) $dyn_r_ids[$this->get_ga_id_type()],
            'name'           => (string) $name,
            'quantity'       => (int) $order_item['quantity'],
            'affiliation'    => (string) get_bloginfo( 'name' ),
            'brand'          => (string) $this->get_brand_name( $product->get_id() ),
            'category'       => implode( ',', $this->get_product_category( $product->get_id() ) ),
            'category_array' => $this->get_product_category( $product->get_id() ),
            'variant'        => ( (string) ($product->get_type() === 'variation') ? $this->get_formatted_variant_text( $product ) : '' ),
            'price'          => (double) $this->wpm_get_order_item_price( $order_item, $product ),
        ];
    }
    
    public function get_ga_id_type()
    {
        $ga_id_type = 'post_id';
        $ga_id_type = apply_filters_deprecated(
            'wooptpm_product_id_type_for_google_analytics',
            [ $ga_id_type ],
            '1.13.0',
            'wpm_product_id_type_for_google_analytics'
        );
        // Change the output of the product ID type for Google Analytics
        return apply_filters( 'wpm_product_id_type_for_google_analytics', $ga_id_type );
    }
    
    public function google_active()
    {
        
        if ( $this->options_obj->google->analytics->universal->property_id ) {
            return true;
        } elseif ( $this->options_obj->google->analytics->ga4->measurement_id ) {
            return true;
        } elseif ( $this->options_obj->google->ads->conversion_id ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function is_google_ads_active()
    {
        
        if ( $this->options['google']['ads']['conversion_id'] ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function is_google_analytics_active()
    {
        
        if ( $this->is_google_analytics_ua_active() || $this->is_google_analytics_4_active() ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function is_google_analytics_ua_active()
    {
        
        if ( $this->options_obj->google->analytics->universal->property_id ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    protected function is_google_analytics_4_active()
    {
        
        if ( $this->options_obj->google->analytics->ga4->measurement_id ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function is_google_analytics_4_mp_active()
    {
        
        if ( $this->options_obj->google->analytics->ga4->measurement_id && $this->options_obj->google->analytics->ga4->api_secret ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function is_google_optimize_active()
    {
        
        if ( $this->options_obj->google->optimize->container_id ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function is_google_active()
    {
        
        if ( $this->is_google_ads_active() || $this->is_google_analytics_active() || $this->is_google_optimize_active() ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function wpm_get_order_item_price( $order_item, $product )
    {
        
        if ( ( new Environment_Check( $this->options ) )->is_woo_discount_rules_active() ) {
            $item_value = $order_item->get_meta( '_advanced_woo_discount_item_total_discount' );
            
            if ( is_array( $item_value ) && array_key_exists( 'discounted_price', $item_value ) && 0 != $item_value['discounted_price'] ) {
                return (double) $item_value['discounted_price'];
            } elseif ( is_array( $item_value ) && array_key_exists( 'initial_price', $item_value ) && 0 != $item_value['initial_price'] ) {
                return (double) $item_value['initial_price'];
            } else {
                return (double) $product->get_price();
            }
        
        } else {
            return (double) $product->get_price();
        }
    
    }
    
    public function add_categories_to_ga4_product_items( $item_details_array, $categories )
    {
        $categories = array_unique( $categories );
        
        if ( count( $categories ) > 0 ) {
            $max_categories = 5;
            $item_details_array['item_category'] = $categories[0];
            $max = ( count( $categories ) > $max_categories ? $max_categories : count( $categories ) );
            for ( $i = 1 ;  $i < $max ;  $i++ ) {
                $item_details_array['item_category' . ($i + 1)] = $categories[$i];
            }
        }
        
        return $item_details_array;
    }
    
    public function get_google_business_vertical( $id )
    {
        $verticals = [
            0 => 'retail',
            1 => 'education',
            2 => 'flights',
            3 => 'hotel_rental',
            4 => 'jobs',
            5 => 'local',
            6 => 'real_estate',
            7 => 'travel',
            8 => 'custom',
        ];
        return $verticals[$id];
    }
    
    public function get_google_ads_conversion_ids( $purchase = false )
    {
        $this->google_ads_conversion_identifiers[$this->options['google']['ads']['conversion_id']] = $this->options['google']['ads']['conversion_label'];
        $this->google_ads_conversion_identifiers = apply_filters_deprecated(
            'wgact_google_ads_conversion_identifiers',
            [ $this->google_ads_conversion_identifiers ],
            '1.10.2',
            'wooptpm_google_ads_conversion_identifiers'
        );
        $this->google_ads_conversion_identifiers = apply_filters_deprecated(
            'wooptpm_google_ads_conversion_identifiers',
            [ $this->google_ads_conversion_identifiers ],
            '1.13.0',
            'wpm_google_ads_conversion_identifiers'
        );
        $this->google_ads_conversion_identifiers = apply_filters( 'wpm_google_ads_conversion_identifiers', $this->google_ads_conversion_identifiers );
        $formatted_conversion_ids = [];
        
        if ( $purchase ) {
            foreach ( $this->google_ads_conversion_identifiers as $conversion_id => $conversion_label ) {
                $conversion_id = $this->extract_google_ads_id( $conversion_id );
                if ( $conversion_id ) {
                    $formatted_conversion_ids['AW-' . $conversion_id] = $conversion_label;
                }
            }
        } else {
            foreach ( $this->google_ads_conversion_identifiers as $conversion_id => $conversion_label ) {
                $conversion_id = $this->extract_google_ads_id( $conversion_id );
                if ( $conversion_id ) {
                    $formatted_conversion_ids['AW-' . $conversion_id] = '';
                }
            }
        }
        
        return $formatted_conversion_ids;
    }
    
    protected function extract_google_ads_id( $string )
    {
        $re = '/\\d{9,11}/';
        
        if ( $string ) {
            preg_match(
                $re,
                $string,
                $matches,
                PREG_OFFSET_CAPTURE,
                0
            );
            if ( is_array( $matches[0] ) ) {
                return $matches[0][0];
            }
        }
        
        return '';
    }
    
    public function get_google_ads_enhanced_conversion_data( $order )
    {
        $customer_data = [];
        if ( $order->get_billing_email() ) {
            $customer_data['email'] = (string) $order->get_billing_email();
        }
        if ( $order->get_billing_phone() ) {
            $customer_data['phone_number'] = $this->get_e164_formatted_phone_number( (string) $order->get_billing_phone(), (string) $order->get_billing_country() );
        }
        
        if ( $this->is_shipping_address_set( $order ) ) {
            $customer_data['address'][] = $this->get_billing_address_details( $order );
            $customer_data['address'][] = $this->get_shipping_address_details( $order );
        } else {
            $customer_data['address'] = $this->get_billing_address_details( $order );
        }
        
        return $customer_data;
    }
    
    protected function get_billing_address_details( $order )
    {
        $customer_data = [];
        if ( $order->get_billing_first_name() ) {
            $customer_data['first_name'] = (string) $order->get_billing_first_name();
        }
        if ( $order->get_billing_last_name() ) {
            $customer_data['last_name'] = (string) $order->get_billing_last_name();
        }
        if ( $order->get_billing_address_1() ) {
            $customer_data['street'] = (string) $order->get_billing_address_1();
        }
        if ( $order->get_billing_city() ) {
            $customer_data['city'] = (string) $order->get_billing_city();
        }
        if ( $order->get_billing_state() ) {
            $customer_data['region'] = (string) $order->get_billing_state();
        }
        if ( $order->get_billing_postcode() ) {
            $customer_data['postal_code'] = (string) $order->get_billing_postcode();
        }
        if ( $order->get_billing_country() ) {
            $customer_data['country'] = (string) $order->get_billing_country();
        }
        return $customer_data;
    }
    
    protected function get_shipping_address_details( $order )
    {
        $customer_data = [];
        if ( $order->get_shipping_first_name() ) {
            $customer_data['first_name'] = (string) $order->get_shipping_first_name();
        }
        if ( $order->get_shipping_last_name() ) {
            $customer_data['last_name'] = (string) $order->get_shipping_last_name();
        }
        if ( $order->get_shipping_address_1() ) {
            $customer_data['street'] = (string) $order->get_shipping_address_1();
        }
        if ( $order->get_shipping_city() ) {
            $customer_data['city'] = (string) $order->get_shipping_city();
        }
        if ( $order->get_shipping_state() ) {
            $customer_data['region'] = (string) $order->get_shipping_state();
        }
        if ( $order->get_shipping_postcode() ) {
            $customer_data['postal_code'] = (string) $order->get_shipping_postcode();
        }
        if ( $order->get_shipping_country() ) {
            $customer_data['country'] = (string) $order->get_shipping_country();
        }
        return $customer_data;
    }
    
    private function is_shipping_address_set( $order )
    {
        // https://woocommerce.github.io/code-reference/files/woocommerce-includes-admin-meta-boxes-class-wc-meta-box-order-data.html#source-view.446
        
        if ( $order->get_formatted_shipping_address() ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function get_gmc_language()
    {
        return strtoupper( substr( get_locale(), 0, 2 ) );
    }
    
    // https://developers.google.com/gtagjs/devguide/linker
    public function get_google_linker_settings()
    {
        $linker_settings = apply_filters_deprecated(
            'wooptpm_google_cross_domain_linker_settings',
            [ null ],
            '1.13.0',
            'wpm_google_cross_domain_linker_settings'
        );
        return apply_filters( 'wpm_google_cross_domain_linker_settings', $linker_settings );
    }
    
    public function get_ga4_parameters( $id )
    {
        $ga_4_parameters = [];
        if ( $this->options_obj->google->user_id && is_user_logged_in() ) {
            $ga_4_parameters = [
                'user_id' => get_current_user_id(),
            ];
        }
        $ga_4_parameters = apply_filters_deprecated(
            'wooptpm_ga_4_parameters',
            [ $ga_4_parameters, $id ],
            '1.13.0',
            'wpm_ga_4_parameters'
        );
        return apply_filters( 'wpm_ga_4_parameters', $ga_4_parameters, $id );
    }
    
    public function get_ga_ua_parameters( $id )
    {
        $ga_ua_parameters = [
            'anonymize_ip'     => true,
            'link_attribution' => (bool) $this->options_obj->google->analytics->link_attribution,
        ];
        if ( $this->options_obj->google->user_id && is_user_logged_in() ) {
            $ga_ua_parameters['user_id'] = get_current_user_id();
        }
        $ga_ua_parameters = apply_filters_deprecated(
            'woopt_pm_analytics_parameters',
            [ $ga_ua_parameters, $id ],
            '1.10.10',
            'wooptpm_ga_ua_parameters'
        );
        $ga_ua_parameters = apply_filters_deprecated(
            'wooptpm_ga_ua_parameters',
            [ $ga_ua_parameters, $id ],
            '1.13.0',
            'wpm_ga_ua_parameters'
        );
        return apply_filters( 'wpm_ga_ua_parameters', $ga_ua_parameters, $id );
    }
    
    public function get_all_refund_products( $refund )
    {
        $data = [];
        $item_index = 1;
        foreach ( $refund->get_items() as $item_id => $item ) {
            //            $product = new WC_Product($refund_item->get_product_id());
            $order_item_data = $this->get_order_item_data( $item );
            $data['pr' . $item_index . 'id'] = $order_item_data['id'];
            $data['pr' . $item_index . 'qt'] = -1 * $order_item_data['quantity'];
            $data['pr' . $item_index . 'pr'] = $order_item_data['price'];
            $item_index++;
        }
        return $data;
    }

}