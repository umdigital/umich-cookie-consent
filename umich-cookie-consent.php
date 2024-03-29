<?php
/**
 * Plugin Name: U-M Cookie Consent
 * Plugin URI: https://github.com/umdigital/umich-cookie-consent/
 * Description: Show GDPR compliant cookie consent message to EU gelocated users.
 * Version: 2.0.2
 * Author: U-M: Digital
 * Author URI: http://vpcomm.umich.edu
 */

define( 'UMCOOKIECONSENT_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

class UMichCookieConsent
{
    static public function init()
    {
        if( !class_exists( 'UMOneTrust' ) ) {
            include_once UMCOOKIECONSENT_PATH .'vendor'. DIRECTORY_SEPARATOR .'umonetrust.php';
        }

        // UPDATER SETUP
        if( !class_exists( 'WP_GitHub_Updater' ) ) {
            include_once UMCOOKIECONSENT_PATH .'vendor'. DIRECTORY_SEPARATOR .'updater.php';
        }
        if( isset( $_GET['force-check'] ) && $_GET['force-check'] && !defined( 'WP_GITHUB_FORCE_UPDATE' ) ) {
            define( 'WP_GITHUB_FORCE_UPDATE', true );
        }
        if( is_admin() ) {
            new WP_GitHub_Updater(array(
                // this is the slug of your plugin
                'slug' => plugin_basename(__FILE__),
                // this is the name of the folder your plugin lives in
                'proper_folder_name' => dirname( plugin_basename( __FILE__ ) ),
                // the github API url of your github repo
                'api_url' => 'https://api.github.com/repos/umdigital/umich-cookie-consent',
                // the github raw url of your github repo
                'raw_url' => 'https://raw.githubusercontent.com/umdigital/umich-cookie-consent/master',
                // the github url of your github repo
                'github_url' => 'https://github.com/umdigital/umich-cookie-consent',
                 // the zip url of the github repo
                'zip_url' => 'https://github.com/umdigital/umich-cookie-consent/zipball/master',
                // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
                'sslverify' => true,
                // which version of WordPress does your plugin require?
                'requires' => '3.0',
                // which version of WordPress is your plugin tested up to?
                'tested' => '3.9.1',
                // which file to use as the readme for the version number
                'readme' => 'README.md',
                // Access private repositories by authorizing under Appearance > Github Updates when this example plugin is installed
                'access_token' => '',
            ));
        }

        // force script(s) as high up as possible
        add_action( 'wp_head', function(){
            $topDomain = preg_replace( '/^(.*\.)?(.+\..+)$/', '$2', parse_url( get_site_url(), PHP_URL_HOST ) );

            echo "\n";
            echo '<script src="https://cdn.cookielaw.org/consent/03e0096b-3569-4b70-8a31-918e55aa20da/otSDKStub.js"  type="text/javascript" charset="UTF-8" data-domain-script="03e0096b-3569-4b70-8a31-918e55aa20da" ></script>';
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
            };
            </script>';
            echo "\n";
        });

        // support Google Site Kit plugin (https://wordpress.org/plugins/google-site-kit/)
        add_filter( 'googlesitekit_gtag_opt', function( $opts ){
            wp_add_inline_script( 'google_gtagjs', '
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
            });' );

            $opts['storage'] = 'none';
            return $opts;
        });

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
                if( preg_match( '#/privacy/$#', parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) ) && !url_to_postid( '/privacy/' ) ) {
                    wp_redirect( 'https://umich.edu/about/privacy-statement/' );
                    exit;
                }
            }
        }, 99);
    }
}
UMichCookieConsent::init();
