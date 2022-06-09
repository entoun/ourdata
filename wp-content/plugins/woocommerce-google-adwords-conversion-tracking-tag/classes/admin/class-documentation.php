<?php

namespace WCPM\Classes\Admin;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Documentation {
	protected $documentation_host;

	public function __construct() {
		$this->documentation_host = 'sweetcode.com';
	}

	public function get_link( $key = 'default' ) {
		$documentation_links = [
			'default'                                                            => [
				'default' => '/docs/wpm/',
				'wcm'     => '/'],
			'script_blockers'                                                    => [
				'default' => '/docs/wpm/setup/script-blockers/?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=script-blocker-error',
				'wcm'     => '/'],
			'google_analytics_universal_property'                                => [
				'default' => '/docs/wpm/plugin-configuration/google-analytics?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-analytics-property-id',
				'wcm'     => '/'],
			'google_analytics_4_id'                                              => [
				'default' => '/docs/wpm/plugin-configuration/google-analytics#connect-an-existing-google-analytics-4-property?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-analytics-4-id',
				'wcm'     => '/'],
			'google_ads_conversion_id'                                           => [
				'default' => '/docs/wpm/plugin-configuration/google-ads#configure-the-plugin?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-ads-configure-the-plugin',
				'wcm'     => '/'],
			'google_ads_conversion_label'                                        => [
				'default' => '/docs/wpm/plugin-configuration/google-ads#configure-the-plugin?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-ads-configure-the-plugin',
				'wcm'     => '/'],
			'google_optimize_container_id'                                       => [
				'default' => '/docs/wpm/plugin-configuration/google-optimize?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-optimize',
				'wcm'     => '/'],
			'facebook_pixel_id'                                                  => [
				'default' => '/docs/wpm/plugin-configuration/meta#find-the-pixel-id?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=facebook-pixel-id',
				'wcm'     => '/'],
			'bing_uet_tag_id'                                                    => [
				'default' => '/docs/wpm/plugin-configuration/microsoft-advertising#setting-up-the-uet-tag?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=microsoft-advertising-uet-tag-id',
				'wcm'     => '/'],
			'twitter_pixel_id'                                                   => [
				'default' => '/docs/wpm/plugin-configuration/twitter',
				'wcm'     => '/'],
			'pinterest_pixel_id'                                                 => [
				'default' => '/docs/wpm/plugin-configuration/pinterest',
				'wcm'     => '/'],
			'snapchat_pixel_id'                                                  => [
				'default' => '/docs/wpm/plugin-configuration/snapchat',
				'wcm'     => '/'],
			'tiktok_pixel_id'                                                    => [
				'default' => '/docs/wpm/plugin-configuration/tiktok',
				'wcm'     => '/'],
			'hotjar_site_id'                                                     => [
				'default' => '/docs/wpm/plugin-configuration/hotjar#hotjar-site-id?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=hotjar-site-id',
				'wcm'     => '/'],
			'google_gtag_deactivation'                                           => [
				'default' => '/docs/wpm/faq/#google-tag-assistant-reports-multiple-installations-of-global-site-tag-gtagjs-detected-what-shall-i-do&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=gtag-js',
				'wcm'     => '/'],
			'google_consent_mode'                                                => [
				'default' => '/docs/wpm/consent-management/google-consent-mode?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-consent-mode',
				'wcm'     => '/'],
			'google_consent_regions'                                             => [
				'default' => '/docs/wpm/consent-management/google-consent-mode#regions?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-consent-mode-regions',
				'wcm'     => '/'],
			'google_analytics_eec'                                               => [
				'default' => '/docs/wpm/plugin-configuration/google-analytics#enhanced-e-commerce-funnel-setup',
				'wcm'     => '/'],
			'google_analytics_4_api_secret'                                      => [
				'default' => '/docs/wpm/plugin-configuration/google-analytics#ga4-api-secret?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-analytics-4-api-secret',
				'wcm'     => '/'],
			'google_ads_enhanced_conversions'                                    => [
				'default' => '/docs/wpm/plugin-configuration/google-ads#enhanced-conversions?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-ads-enhanced-conversions',
				'wcm'     => '/'],
			'google_ads_phone_conversion_number'                                 => [
				'default' => '/docs/wpm/plugin-configuration/google-ads#phone-conversion-number?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-ads-phone-conversion-number',
				'wcm'     => '/'],
			'google_ads_phone_conversion_label'                                  => [
				'default' => '/docs/wpm/plugin-configuration/google-ads#phone-conversion-number?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=google-ads-phone-conversion-number',
				'wcm'     => '/'],
			'explicit_consent_mode'                                              => [
				'default' => '/docs/wpm/consent-management/overview/#explicit-consent-mode',
				'wcm'     => '/'],
			'facebook_capi_token'                                                => [
				'default' => '/docs/wpm/plugin-configuration/meta#facebook-conversion-api-capi?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=facebook-capi-token',
				'wcm'     => '/'],
			'facebook_capi_user_transparency_process_anonymous_hits'             => [
				'default' => '/docs/wpm/plugin-configuration/meta#user-transparency-settings?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=facebook-capi-transparency-settings',
				'wcm'     => '/'],
			'facebook_capi_user_transparency_send_additional_client_identifiers' => [
				'default' => '/docs/wpm/plugin-configuration/meta#user-transparency-settings?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=facebook-capi-transparency-settings',
				'wcm'     => '/'],
			'facebook_microdata'                                                 => [
				'default' => '/docs/wpm/plugin-configuration/meta#microdata-tags-for-catalogues?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=facebook-microdata',
				'wcm'     => '/'],
			'maximum_compatibility_mode'                                         => [
				'default' => '/docs/wpm/plugin-configuration/general-settings/#maximum-compatibility-mode?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=maximum-compatibility-mode',
				'wcm'     => '/'],
			'google_ads_dynamic_remarketing'                                     => [
				'default' => '/docs/wpm/plugin-configuration/dynamic-remarketing?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=dynamic-remarketing',
				'wcm'     => '/'],
			'variations_output'                                                  => [
				'default' => '/docs/wpm/plugin-configuration/dynamic-remarketing?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=dynamic-remarketing',
				'wcm'     => '/'],
			'aw_merchant_id'                                                     => [
				'default' => '/docs/wpm/plugin-configuration/google-ads/#conversion-cart-data?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=conversion-cart-data',
				'wcm'     => '/'],
			'custom_thank_you'                                                   => [
				'default' => '/docs/wpm/troubleshooting/#wc-custom-thank-you',
				'wcm'     => '/'],
			'the_dismiss_button_doesnt_work_why'                                 => [
				'default' => '/docs/wpm/faq/#the-dismiss-button-doesnt-work-why?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=wpp-pixel-manager-docs&utm_content=dismiss-button-info',
				'wcm'     => '/'],
			'wp-rocket-javascript-concatenation'                                 => [
				'default' => '/docs/wpm/troubleshooting?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=wp-rocket-javascript-concatenation-error',
				'wcm'     => '/'],
			'litespeed-cache-inline-javascript-after-dom-ready'                  => [
				'default' => '/docs/wpm/troubleshooting?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=litespeed-inline-js-dom-ready-error',
				'wcm'     => '/'],
			'payment-gateways'                                                   => [
				'default' => '/docs/wpm/setup/requirements#payment-gateways?utm_source=woocommerce-plugin&utm_medium=documentation-link&utm_campaign=woocommerce-pixel-manager-docs&utm_content=paypal-standard-warning',
				'wcm'     => '/'],
			'test_order'                                                         => [
				'default' => '/docs/wpm/testing#test-order',
				'wcm'     => '/'],
		];

		if (array_key_exists($key, $documentation_links)) {
			return $this->get_host() . $documentation_links[$key]['default'];
		} else {
			error_log('wpm documentation key "' . $key . '" not available');
			return $this->get_host() . $documentation_links['default'];
		}
	}

	private function get_host() {
		return 'https://' . $this->documentation_host;
	}
}
