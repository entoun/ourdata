<?php

/**
 * Plugin Name:          WooCommerce Pixel Manager
 * Description:          Visitor and conversion value tracking for WooCommerce. Highly optimized for data accuracy.
 * Author:               SweetCode
 * Plugin URI:           https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/
 * Author URI:           https://sweetcode.com
 * Developer:            SweetCode
 * Developer URI:        https://sweetcode.com
 * Text Domain:          woocommerce-google-adwords-conversion-tracking-tag
 * Domain path:          /languages
 * * Version:              1.16.14
 *
 * WC requires at least: 3.7
 * WC tested up to:      6.4
 *
 * License:              GNU General Public License v3.0
 * License URI:          http://www.gnu.org/licenses/gpl-3.0.html
 *
 *
 **/
const  WPM_CURRENT_VERSION = '1.16.14' ;
// TODO export settings function
// TODO add option checkbox on uninstall and ask if user wants to delete options from db

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

use  WCPM\Classes\Admin\Admin ;
use  WCPM\Classes\Admin\Environment_Check ;
use  WCPM\Classes\Db_Upgrade ;
use  WCPM\Classes\Default_Options ;
use  WCPM\Classes\Pixels\Pixel_Manager ;
use  WCPM\Classes\Admin\Ask_For_Rating ;

if ( function_exists( 'wpm_fs' ) ) {
    wpm_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'wpm_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wpm_fs()
        {
            global  $wpm_fs ;
            
            if ( !isset( $wpm_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_7498_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_7498_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
                $wpm_fs = fs_dynamic_init( [
                    'navigation'      => 'tabs',
                    'id'              => '7498',
                    'slug'            => 'woocommerce-google-adwords-conversion-tracking-tag',
                    'premium_slug'    => 'woocommerce-pixel-manager-pro',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_d4182c5e1dc92c6032e59abbfdb91',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Pro',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => [
                    'days'               => 14,
                    'is_require_payment' => true,
                ],
                    'has_affiliation' => 'customers',
                    'menu'            => [
                    'slug'           => 'wpm',
                    'override_exact' => true,
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => [
                    'slug' => 'woocommerce',
                ],
                ],
                    'is_live'         => true,
                ] );
            }
            
            return $wpm_fs;
        }
        
        // Init Freemius.
        wpm_fs();
        // Signal that SDK was initiated.
        do_action( 'wpm_fs_loaded' );
        function wpm_fs_settings_url()
        {
            return admin_url( 'admin.php?page=wpm&section=main&subsection=google-ads' );
        }
        
        wpm_fs()->add_filter( 'connect_url', 'wpm_fs_settings_url' );
        wpm_fs()->add_filter( 'after_skip_url', 'wpm_fs_settings_url' );
        wpm_fs()->add_filter( 'after_connect_url', 'wpm_fs_settings_url' );
        wpm_fs()->add_filter( 'after_pending_connect_url', 'wpm_fs_settings_url' );
    }
    
    // ... Your plugin's main file logic ...
    class WCPM
    {
        protected  $options ;
        protected  $environment_check ;
        public function __construct()
        {
            define( 'WPM_PLUGIN_PREFIX', 'wpm_' );
            define( 'WPM_DB_VERSION', '3' );
            define( 'WPM_DB_OPTIONS_NAME', 'wgact_plugin_options' );
            define( 'WPM_DB_NOTIFICATIONS_NAME', 'wgact_notifications' );
            define( 'WPM_PLUGIN_DIR_PATH', plugin_dir_url( __FILE__ ) );
            define( 'WPM_DB_RATINGS', 'wgact_ratings' );
            // check if WooCommerce is running
            // currently this is the most reliable test for single and multisite setups
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            
            if ( $this->are_requirements_met() ) {
                // autoloader
                require_once 'lib/autoload.php';
                if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
                    require __DIR__ . '/vendor/autoload.php';
                }
                $this->setup_freemius_environment();
                // running the DB updater
                if ( get_option( WPM_DB_OPTIONS_NAME ) ) {
                    ( new Db_Upgrade() )->run_options_db_upgrade();
                }
                // load the options
                $this->wpm_options_init();
                $this->environment_check = new Environment_Check( $this->options );
                if ( isset( $this->options['google']['gads']['dynamic_remarketing'] ) && $this->options['google']['gads']['dynamic_remarketing'] ) {
                    // make sure to disable the WGDR plugin in case we use dynamic remarketing in this plugin
                    add_filter( 'wgdr_third_party_cookie_prevention', '__return_true' );
                }
                // run environment workflows
                add_action( 'admin_notices', [ $this, 'run_admin_compatibility_checks' ] );
                add_action( 'admin_notices', [ $this, 'environment_check_admin_notices' ] );
                add_action( 'admin_notices', function () {
                    $this->environment_check->run_incompatible_plugins_checks();
                } );
                $this->environment_check->disable_third_party_js_optimization();
                if ( $this->options['general']['maximum_compatibility_mode'] ) {
                    $this->environment_check->enable_compatibility_mode();
                }
                $this->environment_check->flush_cache_on_plugin_changes();
                register_activation_hook( __FILE__, [ $this, 'plugin_activated' ] );
                register_deactivation_hook( __FILE__, [ $this, 'plugin_deactivated' ] );
                //				$this->init();
                add_action( 'woocommerce_init', [ $this, 'init' ] );
            } else {
                add_action( 'admin_menu', [ $this, 'add_empty_admin_page' ], 99 );
                add_action( 'admin_notices', [ $this, 'requirements_error' ] );
                //				error_log('The WooCommerce Pixel Manager requires the WooCommerce plugin to be active.');
            }
        
        }
        
        private function are_requirements_met()
        {
            
            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                return true;
            } else {
                return false;
            }
        
        }
        
        public function add_empty_admin_page()
        {
            add_submenu_page(
                'woocommerce',
                esc_html__( 'WooCommerce Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                esc_html__( 'WooCommerce Pixel Manager', 'woocommerce-google-adwords-conversion-tracking-tag' ),
                'manage_options',
                'wpm',
                function () {
            }
            );
        }
        
        // https://github.com/iandunn/WordPress-Plugin-Skeleton/blob/master/views/requirements-error.php
        public function requirements_error()
        {
            ?>

			<div class="error">
				<p>
					<strong>
						<?php 
            esc_html_e( 'WooCommerce Pixel Manager error', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
					</strong>:
					<?php 
            esc_html_e( "Your environment doesn't meet all the system requirements listed below.", 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
				</p>

				<ul class="ul-disc">
					<li><?php 
            esc_html_e( 'The WooCommerce plugin needs to be activated', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?></li>
				</ul>
			</div>
			<style>
				.fs-tab {
					display: none !important;
				}
			</style>

			<?php 
        }
        
        public function plugin_activated()
        {
            $this->environment_check->flush_cache_of_all_cache_plugins();
        }
        
        public function plugin_deactivated()
        {
            $this->environment_check->flush_cache_of_all_cache_plugins();
        }
        
        public function environment_check_admin_notices()
        {
            if ( apply_filters( 'wpm_show_admin_alerts', apply_filters_deprecated(
                'wooptpm_show_admin_alerts',
                [ true ],
                '1.13.0',
                'wpm_show_admin_alerts'
            ) ) ) {
                $this->environment_check->check_active_off_site_payment_gateways();
            }
        }
        
        // startup all functions
        public function init()
        {
            
            if ( is_admin() ) {
                // display admin views
                new Admin( $this->options );
                // ask visitor for rating
                new Ask_For_Rating();
                // add a settings link on the plugins page
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'wpm_settings_link' ] );
            }
            
            // inject pixels into front end
            //			add_action('after_setup_theme', [$this, 'inject_pixels']);
            $this->inject_pixels();
        }
        
        public function inject_pixels()
        {
            // TODO Remove the cookie prevention filters by January 2023
            $cookie_prevention = apply_filters_deprecated(
                'wgact_cookie_prevention',
                [ false ],
                '1.10.4',
                'wooptpm_cookie_prevention'
            );
            $cookie_prevention = apply_filters_deprecated(
                'wooptpm_cookie_prevention',
                [ $cookie_prevention ],
                '1.12.1',
                '',
                'This filter has been replaced by a much more robust cookie consent handing in the plugin. Please read more about it in the documentation.'
            );
            if ( false === $cookie_prevention ) {
                // inject pixels
                new Pixel_Manager( $this->options );
            }
        }
        
        public function run_admin_compatibility_checks()
        {
            $this->environment_check->run_checks();
        }
        
        // initialise the options
        /**
         * Noinspection
         *
         * @noinspection GrazieInspection
         */
        private function wpm_options_init()
        {
            // set options equal to defaults
            $this->options = get_option( WPM_DB_OPTIONS_NAME );
            
            if ( false === $this->options ) {
                // if no options have been set yet, initiate default options
                $this->options = ( new Default_Options() )->get_default_options();
            } else {
                // Check if each single option has been set. If not, set them. That is necessary when new options are introduced.
                // add new default options to the options db array
                $this->options = ( new Default_Options() )->update_with_defaults( $this->options, ( new Default_Options() )->get_default_options() );
            }
            
            update_option( WPM_DB_OPTIONS_NAME, $this->options );
        }
        
        // adds a link on the plugins page for the wgdr settings
        // ! Can't be required. Must be in the main plugin file.
        public function wpm_settings_link( $links )
        {
            $links[] = '<a href="' . admin_url( 'admin.php?page=wpm' ) . '">Settings</a>';
            return $links;
        }
        
        protected function setup_freemius_environment()
        {
            wpm_fs()->add_filter( 'show_trial', function () {
                
                if ( $this->is_development_install() ) {
                    return false;
                } else {
                    return apply_filters( 'wpm_show_admin_trial_promo', apply_filters_deprecated(
                        'wooptpm_show_admin_trial_promo',
                        [ true ],
                        '1.13.0',
                        'wpm_show_admin_trial_promo'
                    ) ) && apply_filters( 'wpm_show_admin_notifications', apply_filters_deprecated(
                        'wooptpm_show_admin_notifications',
                        [ true ],
                        '1.13.0',
                        'wpm_show_admin_notifications'
                    ) );
                }
            
            } );
            // don't reshow trial message for 10 years
            wpm_fs()->add_filter( 'reshow_trial_after_every_n_sec', function () {
                return 60 * 60 * 24 * 7 * 52 * 10;
            } );
        }
        
        protected function is_development_install()
        {
            
            if ( class_exists( 'FS_Site' ) ) {
                return FS_Site::is_localhost_by_address( get_site_url() );
            } else {
                return false;
            }
        
        }
    
    }
    new WCPM();
}
