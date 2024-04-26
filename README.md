Cookie Consent Manager
======================
[![GitHub release](https://img.shields.io/github/release/umdigital/umich-cookie-consent.svg)](https://github.com/umdigital/umich-cookie-consent/releases/latest)
[![GitHub issues](https://img.shields.io/github/issues/umdigital/umich-cookie-consent.svg)](https://github.com/umdigital/umich-cookie-consent/issues)

Provides easy way to add GDPR compliant message to your site.  This will only show for IP's geolocated to an EU country.
- Integrates cookie usage with [Google Site Kit](https://wordpress.org/plugins/google-site-kit/) plugin


## Install
### WP Admin/Dashboard Method
*This requires that your site has write access to the plugins folder.*
1. Download the [latest package](https://github.com/umdigital/umich-cookie-consent/releases/latest) *(e.g. umich-cookie-consent-x.x.x.zip)*
2. Go to WP Admin/Dashboard -> Plugins -> Add New -> Upload Plugin
3. Select the downloaded zip file and Upload
4. Activate Plugin
5. Configure plugin settings (WP Admin/Dashboard -> Settings -> U-M: Cloudlfare
### Manual Method
1. Download the [latest package](https://github.com/umdigital/umich-cookie-consent/releases/latest) *(e.g. umich-cookie-consent-x.x.x.zip)*
2. Extract zip
3. Upload the *umich-cookie-consent* folder to *wp-content/plugins/* folder in your site
4. Activate Plugin
5. Configure plugin settings (WP Admin/Dashboard -> Settings -> U-M: Cookie Consent 


## Integrations
### Actions
**umich_cookie_consent_allowed**
```
add_action( 'umich_cookie_consent_allowed', function( $status ){
    // your code here to execute when cookies are allowed
});
```


**umich_cookie_consent_denied**
```
add_action( 'umich_cookie_consent_denied', function( $status ){
    // your code here to execute when cookies are denied
});
```

### OneTrust Cookie Library
UMOneTrust

**Get Raw Decoded Cookie Value**
```
UMOneTrust::get();
```

**Get Preference Group Value**
```
/* return preference value (boolean)
 * Where $group is one of:
 * - required
 * - performance
 * - functional
 * - targeting
 */
UMOneTrust::get( $group );
```
