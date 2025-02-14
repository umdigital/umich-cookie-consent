<?php
/**
 * Plugin Name: University of Michigan: Cookie Consent
 * Plugin URI: https://github.com/umdigital/umich-cookie-consent/
 * Description: Show GDPR compliant cookie consent message to EU gelocated users.
 * Version: 3.0.0
 * Author: U-M: Digital Strategy
 * Author URI: http://vpcomm.umich.edu
 * Update URI: https://github.com/umdigital/umich-cookie-consent/releases/latest
 */

define( 'UMCOOKIECONSENT_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

class UMichCookieConsent
{
    static private $_domains = [
        'umich.edu'      => [
            'privacy' => 'https://umich.edu/about/privacy/',
        ],
        'umflint.edu'    => [
            'privacy' => 'https://umflint.edu/about/privacy/',
        ],
        'umdearborn.edu' => [
            'privacy' => 'https://umdearborn.edu/privacy-policy',
        ]
    ];
    static private $_options = [];
    static private $_cookieName = 'um_cookie_consent';

    static public function init()
    {
        // load updater library
        if( file_exists( UMCOOKIECONSENT_PATH . implode( DIRECTORY_SEPARATOR, [ 'vendor', 'umdigital', 'wordpress-github-updater', 'github-updater.php' ] ) ) ) {
            include UMCOOKIECONSENT_PATH . implode( DIRECTORY_SEPARATOR, [ 'vendor', 'umdigital', 'wordpress-github-updater', 'github-updater.php' ] );
        }
        else if( file_exists( UMCOOKIECONSENT_PATH .'includes'. DIRECTORY_SEPARATOR .'github-updater.php' ) ) {
            include UMCOOKIECONSENT_PATH .'includes'. DIRECTORY_SEPARATOR .'github-updater.php';
        }

        // Initialize Github Updater
        if( class_exists( '\Umich\GithubUpdater\Init' ) ) {
            new \Umich\GithubUpdater\Init([
                'repo' => 'umdigital/umich-cookie-consent',
                'slug' => plugin_basename( __FILE__ ),
            ]);
        }
        // Show error upon failure
        else {
            add_action( 'admin_notices', function(){
                echo '<div class="error notice"><h3>WARNING</h3><p>U-M: Cookie Consent plugin is currently unable to check for updates due to a missing dependency.  Please <a href="https://github.com/umdigital/umich-cookie-consent">reinstall the plugin</a>.</p></div>';
            });
        }

        self::$_options = array_replace_recursive([
                'mode'                => 'prod',
                'privacy_url'         => '',
                'google_analytics_id' => '',
                'custom'              => '',
                'always_show'         => '',
                'domain'              => ''
            ],
            get_option( 'umich_cc_options' ) ?: []
        );

        if( self::$_options['custom'] ) {
            $domain = self::$_options['domain'] ?: $_SERVER['HTTP_HOST'];

            if( function_exists( 'hash' ) && in_array( 'sha1', hash_algos() ) ) {
                self::$_cookieName .= '_'. hash( 'sha1', $domain );
            }
            else if( function_exists( 'sha1' ) ) {
                self::$_cookieName .= '_'. sha1( $domain );
            }
        }

        // force script(s) as high up as possible
        add_action( 'wp_head', function(){
            $params = [];

            if( self::$_options['mode'] == 'dev' ) {
                if( !is_user_logged_in() ) {
                    return;
                }

                $params['mode'] = self::$_options['mode'];
            }

            if( self::$_options['privacy_url'] ) {
                $params['privacyUrl'] = self::$_options['privacy_url'];
            }

            if( self::$_options['google_analytics_id'] ) {
                $params['googleAnalyticsID'] = self::$_options['google_analytics_id'];
            }

            if( self::$_options['custom'] ) {
                $params['customManager'] = [];

                $params['customManager']['enabled'] = true;

                if( self::$_options['always_show'] ) {
                    $params['customManager']['alwaysShow'] = true;
                }

                if( self::$_options['domain'] && (count(explode('.', self::$_options['domain'])) != 2) ) {
                    $params['customManager']['rootDomain'] = self::$_options['domain'];
                }
            }

            $params = apply_filters( 'umich_cc_js_params', $params, self::$_options );

            if( $params ) {
                echo "\n";
                echo '<script>window.umConsentManager = '. json_encode( $params ) .';</script>';
            }

            echo "\n";
            echo '<script async src="https://umich.edu/apis/umconsentmanager/consentmanager.js"></script>';
            echo "\n";
        });

        // support Google Site Kit plugin (https://wordpress.org/plugins/google-site-kit/)
        add_action( 'wp_enqueue_scripts', function(){
            wp_add_inline_script( 'google_gtagjs', '
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            // Default ad_storage to "denied". 
            gtag("consent", "default", { 
                ad_storage             : "denied", 
                analytics_storage      : "denied", 
                functionality_storage  : "denied", 
                personalization_storage: "denied", 
                security_storage       : "denied",  
                ad_user_data           : "denied",
                ad_personalization     : "denied", 
                wait_for_update        : 500 
            });', 'before' );
        }, 30 );

        // get cookie data
        $cookieData = isset( $_COOKIE[ self::$_cookieName ] ) ? json_decode( $_COOKIE[ self::$_cookieName ] ) : false;

        // check for cookie consent cookie and execute appropriate action
        if( $cookieData && is_array( $cookieData->categories ) && in_array( 'analytics', $cookieData->categories ) ) {
            do_action( 'umich_cookie_consent_allowed', $cookieData );
        }
        else {
            do_action( 'umich_cookie_consent_denied', $cookieData );
        }

        // default the privacy url
        add_action( 'init', function(){
            if( isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] ) ) {
                if( preg_match( '#^/privacy/?$#', parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) ) && !url_to_postid( '/privacy/' ) ) {
                    $topDomain = self::_detectDomain( true );

                    if( isset( self::$_domains[ $topDomain ]['privacy'] ) ) {
                        wp_redirect( self::$_domains[ $topDomain ]['privacy'] );
                        exit;
                    }
                }
            }
        }, 99);


        /** ADMIN **/
        add_action( 'admin_notices', function(){
            if( self::$_options['mode'] != 'prod' ) {
                echo '<div class="error notice"><h3>NOTICE</h3><p>U-M: Cookie Consent plugin is currently in development mode. <a href="'. admin_url( 'options-general.php?page=umich-cc' ) .'">Manage Settings</a></p></div>';
            }

            if( !preg_match( '/'. preg_quote( self::$_options['domain'] ) .'$/', $_SERVER['HTTP_HOST'] ) ) {
                echo '<div class="error notice"><h3>NOTICE</h3><p>U-M: Cookie Consent plugin is currently configured with the domain "'. self::$_options['domain'] .'" however the current domain is "'. $_SERVER['HTTP_HOST'] .'" the plugin will not work correctly with this setting. The selected parent domain must be the current domain or a parent of the current domain.</p></div>';
            }
        });

        add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), function( $links ){
            return array_merge(
                $links,
                array(
                    '<a href="'. admin_url( 'options-general.php?page=umich-cc' ) .'">Settings</a>'
                )
            );
        });

        add_action( 'admin_init', function(){
            register_setting(
                'umich-cc',
                'umich_cc_options'
            );
        });

        add_action( 'admin_menu', function(){
            add_options_page(
                'U-M: Cookie Consent',
                'U-M: Cookie Consent',
                'administrator',
                'umich-cc',
                function(){
                    $umCCOptions           = self::$_options;
                    $umCCDefaultPrivacyUrl = false;
                    $umCCDomains           = [];

                    // determine selectable domains
                    $domain = parse_url( get_site_url(), PHP_URL_HOST );
                    $max    = count( explode( '.', $domain ) ) - 1;

                    for( $i = $max; $i >= 1; $i-- ) {
                        $umCCDomains[] = array_pop( explode( '.', $domain, $i ) );
                    }

                    if( self::$_options['domain'] && !in_array( self::$_options['domain'], $umCCDomains ) ) {
                        $umCCDomains[] = self::$_options['domain'];
                    }

                    if( $topDomain = self::_detectDomain() ) {
                        $umCCDefaultPrivacyUrl = self::$_domains[ $topDomain ]['privacy'];
                    }

                    include UMCOOKIECONSENT_PATH .'templates'. DIRECTORY_SEPARATOR .'admin.tpl';
                }
            );
        });
    }

    static private function _detectDomain( $useConfig = false )
    {
        $topDomain = preg_replace( '/^(.*\.)?(.+\..+)$/', '$2', parse_url( get_site_url(), PHP_URL_HOST ) );

        if( $useConfig && self::$_options['domain'] ) {
            $topDomain = self::$_options['domain'];
        }

        if( isset( self::$_domains[ $topDomain ] ) ) {
            return $topDomain;
        }

        return false;
    }
}
UMichCookieConsent::init();
