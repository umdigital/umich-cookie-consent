(function($){
    $(document).ready(function(){
        // cookie consent
        if( window.cookieconsent ) {
            // add no notice required status (e.g. non EU request)
            window.cookieconsent.status.na = 'na';

            window.cookieconsent.umconfig = {
                cookie: {
                    name: 'um_cookie_consent',
                    domain: (window.location.hostname.match( /(^|.)umich.edu$/ ) ? 'umich.edu' : '')
                },
                cookiesOkStatus: [ 'allow', 'na' ]
            };

            // Add Purge Cookies Routine (must be defined before initialise call)
            window.cookieconsent.utils.purgeCookies = function( cookieConfig ) {
                var cookies = document.cookie.split(';');

                // since we don't know the domain of each cookie lets try them all
                var thisDomains = [];
                thisDomains.push('');

                var thisDomain = window.location.hostname.split('.');
                var tmpCount   = thisDomain.length;
                for( var i = 0; i < tmpCount; i++ ) {
                    if( thisDomain.length > 1 ) { // min 2 parts (e.g. foo.com)
                        thisDomains.push( thisDomain.join('.') );
                        thisDomain.shift();
                    }
                }

                // delete all but the consent cookie
                var tmpCount = cookies.length;
                for( var i = 0; i < tmpCount; i++ ) {
                    var thisName = (cookies[ i ].split('=')[0]).trim();

                    // skip this status cookie
                    if( thisName != cookieConfig.name ) {
                        var prevCookies = document.cookies;
                        for( var x = 0; x < thisDomains.length; x++ ) {
                            // only attempt while there is no change
                            if( prevCookies == document.cookies ) {
                                // attempt to delete cookie
                                window.cookieconsent.utils.setCookie(
                                    thisName, '', -1, thisDomains[ x ], cookieConfig.path
                                );
                            }
                        }
                    }                                                                                                       
                }                                                                                                           
            }

            // PREPARE CC OPTIONS
            var thisCCOptions = window.cookieconsent.utils.deepExtend({
                palette: {
                    popup: {
                        background: "#00274c",
                        text      : "#ffffff"
                    },
                    button: {
                        background: "#ffcb05",
                        text      : "#00274c"
                    }
                },
                theme: "classic",
                type : "opt-in",
                content: {
                    dismiss: "Decline",
                    link   : "View our Privacy Notice",
                    href   : "http://umich.edu/about/privacy/"
                },
                law: {
                    regionalLaw: false
                },
                revokeBtn: '<div/>',
            }, (window.umcookieconsent || {}) );

            // add non overridable options
            thisCCOptions = window.cookieconsent.utils.deepExtend( thisCCOptions, {
                cookie: window.cookieconsent.umconfig.cookie,
                location: {
                    services: ['umich'],
                    serviceDefinitions: {
                        umich: function( options ){
                            var hasCookieStatus = window.cookieconsent.utils.getCookie(
                                window.cookieconsent.umconfig.cookie.name
                            );

                            // decision has already been made, don't need to do another country lookup
                            if( hasCookieStatus ) {
                                return false;
                            }

                            return {
                                url: 'https://umich.edu/apis/country/',
                                callback: function( done, response ) {
                                    if( response.length == 2 ) {
                                        return { code: response };
                                    }

                                    return new Error( 'Invalid country lookup response ('+ response +')' );
                                }
                            };
                        }                                                                                                   
                    }
                },
                onInitialise: function( status ) {
                    if( window.cookieconsent.umconfig.cookiesOkStatus.indexOf( status ) == -1 ) {
                        window.cookieconsent.utils.purgeCookies( this.options.cookie );
                    }
                },
                onStatusChange: function( status, previous ) {
                    if( window.cookieconsent.umconfig.cookiesOkStatus.indexOf( status ) != -1 ) {
                        var hasCookieStatus = window.cookieconsent.utils.getCookie(
                            window.cookieconsent.umconfig.cookie.name
                        );

                        // make sure the cookie was set before attempting to redirect
                        if( hasCookieStatus ) {
                            window.location = window.location;
                        }
                    }
                    else {
                        window.cookieconsent.utils.purgeCookies( this.options.cookie );
                    }
                }
            });

            // load the plugin
            window.cookieconsent.initialise( thisCCOptions, function( result ){
                var hasCookieStatus = window.cookieconsent.utils.getCookie(
                    window.cookieconsent.umconfig.cookie.name
                );

                // they don't need to agree, auto set status cookie to 'na'
                if( !result.options.enabled && !hasCookieStatus ) {
                    result.setStatus( 'na' );
                }
                // they need to agree and have not... purge cookies
                else if( result.options.enabled && !hasCookieStatus ) {
                    window.cookieconsent.utils.purgeCookies( result.options.cookie );
                }
            });
        }
    });
}(jQuery));
