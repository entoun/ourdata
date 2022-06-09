<?php

namespace WCPM\Classes\Pixels\Google;

use  WC_Order_Refund ;
use  WC_Product ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Google_Analytics_Refund extends Google_Analytics
{
    public function __construct( $options )
    {
        parent::__construct( $options );
    }
    
    public function output_refund_to_frontend( $order, $refund, $dataLayer_refund_items = null )
    {
        $data = [
            'send_to'        => $this->options_obj->google->analytics->ga4->measurement_id,
            'affiliation'    => get_bloginfo( 'name' ),
            'currency'       => (string) $this->get_order_currency( $order ),
            'items'          => (array) $dataLayer_refund_items,
            'transaction_id' => (string) $order->get_order_number(),
            'shipping'       => (double) $order->get_shipping_total(),
            'tax'            => (double) $order->get_total_tax(),
            'value'          => (double) $refund->get_amount(),
        ];
        ?>
		<script>

			if (typeof wpmFunctionExists !== 'function') {
				window.wpmFunctionExists = function (functionName) {
					return new Promise(function (resolve) {
						(function waitForVar() {
							if (typeof window[functionName] !== 'undefined') return resolve();
							setTimeout(waitForVar, 1000);
						})();
					});
				}
			}

			wpmFunctionExists('wpm').then(function () {
				if (wpm.googleConfigConditionsMet('analytics')) {
					gtag('event', 'refund', <?php 
        echo  wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ;
        ?>);
				}
			})
		</script>
		<?php 
    }

}