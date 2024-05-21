<?php
/**
 * Plugin Name: University of Michigan: Cookie Consent
 * Plugin URI: https://github.com/umdigital/umich-cookie-consent/
 * Description: Show GDPR compliant cookie consent message to EU gelocated users.
 * Version: 2.0.6
 * Author: U-M: Digital
 * Author URI: http://vpcomm.umich.edu
 * Update URI: https://github.com/umdigital/umich-cookie-consent/releases/latest
 */

define( 'UMCOOKIECONSENT_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

class UMichCookieConsent
{
    static private $_domains = [
        'umich.edu'      => [
            'code'    => '03e0096b-3569-4b70-8a31-918e55aa20da',
            'privacy' => 'https://umich.edu/about/privacy-statement/',
        ],
        'umflint.edu'    => [
            'code'    => '018f776d-a3b7-74f3-8fec-065496d9a96f',
            'privacy' => 'https://umflint.edu/about/privacy/',
        ],
        'umdearborn.edu' => [
            'code'    => '018f779a-dc12-7048-a377-bb381a930bf2',
            'privacy' => 'https://umdearborn.edu/privacy-policy',
        ]
    ];
    static private $_options = [];

    static public function init()
    {
        if( !class_exists( 'UMOneTrust' ) ) {
            include_once UMCOOKIECONSENT_PATH .'includes'. DIRECTORY_SEPARATOR .'umonetrust.php';
        }

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
                'mode'   => 'prod',
                'domain' => ''
            ],
            get_option( 'umich_cc_options' ) ?: []
        );

        // force script(s) as high up as possible
        add_action( 'wp_head', function(){
            $topDomain = self::_detectDomain( true );

            $otCode = false;

            if( isset( self::$_domains[ $topDomain ]['code'] ) ) {
                $otCode = self::$_domains[ $topDomain ]['code'];
            }

            $otCode = apply_filters( 'umich_cc_onetrust_code', $otCode, $topDomain );

            if( self::$_options['mode'] != 'prod' ) {
                if( !$otCode ) {
                    $otCode = self::$_domains['umich.edu']['code'];
                }

                $otCode .= '-test';
            }

            if( !$otCode ) {
                return;
            }

            echo "\n";
            echo '<script src="https://cdn.cookielaw.org/consent/'. $otCode .'/otSDKStub.js"  type="text/javascript" charset="UTF-8" data-domain-script="'. $otCode .'" ></script>';
            echo "\n";
            echo '<script type="text/javascript">
            function OptanonWrapper(){
                // performance
                if( OnetrustActiveGroups.includes("C0002") ) {
                    gtag( "consent", "update", {
                        analytics_storage: "granted"
                    });
                }
                // functional
                if( OnetrustActiveGroups.includes("C0003") ) {
                    gtag( "consent", "update", {
                        functional_storage: "granted"
                    });
                }
                // targeting
                if( OnetrustActiveGroups.includes("C0004") ) {
                    gtag( "consent", "update", {
                        ad_storage             : "granted",
                        ad_user_data           : "granted",
                        ad_personalization     : "granted",
                        personalization_storage: "granted"
                    });
                }
                else {
                    document.cookie.split(";").forEach( (cookie) => {
                        const [ name ] = cookie.split("=");
                        if( name.trim().match( /^_ga(_.+)?$/ ) ) {
                            document.cookie = name + "=;path=/;domain=.'. $topDomain .';expires=Thu, 01 Jan 1970 00:00:01 GMT";
                        }
                    });
                }

                // trigger event for use in Tag Manager
                window.dataLayer.push({ event: "um_consent_updated" });
            };
            </script>';
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

        // check for cookie consent cookie and execute appropriate action
        if( UMOneTrust::get('targeting') ) {
            do_action( 'umich_cookie_consent_allowed', UMOneTrust::get() );
        }
        else {
            do_action( 'umich_cookie_consent_denied', UMOneTrust::get() );
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
                    $umCCOptions    = self::$_options;
                    $umCCDomains    = array_keys( self::$_domains );
                    $umCCAutodetect = 'None';

                    if( $topDomain = self::_detectDomain() ) {
                        $umCCAutodetect = $topDomain;
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
