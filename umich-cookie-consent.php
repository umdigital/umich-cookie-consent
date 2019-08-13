<?php
/**
 * Plugin Name: U-M Cookie Consent
 * Plugin URI: https://github.com/umdigital/umich-cookie-consent/
 * Description: Show GDPR compliant cookie consent message to EU gelocated users.
 * Version: 1.1.3
 * Author: U-M: Digital
 * Author URI: http://vpcomm.umich.edu
 */

define( 'UMCOOKIECONSENT_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

class UMichCookieConsent
{
    static public function init()
    {
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

        add_action( 'wp_enqueue_scripts', function(){
            wp_enqueue_script( 'insites-cookieconsent', plugins_url( 'vendor/cookieconsent-3.0.6/src/cookieconsent.js', __FILE__ ) );
            wp_enqueue_script( 'umich-cookie-consent', plugins_url( 'assets/umich-cookie-consent.js', __FILE__ ) );

            wp_enqueue_style( 'umich-cookie-consent', plugins_url( 'assets/umich-cookie-consent.css', __FILE__ ) );
        });


        // check for cookie consent cookie and execute appropriate action
        $cookieStatus = isset( $_COOKIE['um_cookie_consent'] ) ? $_COOKIE['um_cookie_consent'] : '';

        if( in_array( $cookieStatus, array( 'allow', 'na' ) ) ) {
            do_action( 'umich_cookie_consent_allowed', $cookieStatus );
        }
        else {
            // support the mc-google-analytics plugin
            add_filter( 'mc_ga_create_options', function( $options ){
                // disable cookies for GA
                $options['storage'] = 'none';
                return $options;
            });

            do_action( 'umich_cookie_consent_denied', $cookieStatus );
        }
    }
}
UMichCookieConsent::init();
