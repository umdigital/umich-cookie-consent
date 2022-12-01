Cookie Consent Message
======================
Provides easy way to add GDPR compliant message to your site.  This will only show for IP's geolocated to an EU country.
- Integrates cookie usage with [Google Site Kit](https://wordpress.org/plugins/google-site-kit/) plugin


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
