<?php

class UMOneTrust
{
    static private $_cookie;
    static private $_groups = [
        'C0001' => 'required',
        'C0002' => 'performance',
        'C0003' => 'functional',
        'C0004' => 'targeting',
    ];

    static public function get( $group = false )
    {
        self::_init();

        if( $group ) {
            if( ($gKey = array_search( $group, self::$_groups )) !== false ) {
                $group = $gKey;
            }

            if( array_key_exists( $group, self::$_cookie['groups'] ) ) {
                return self::$_cookie['groups'][ $group ];
            }

            return false;
        }

        return self::$_cookie;
    }

    static private function _init()
    {
        if( is_null( self::$_cookie ) ) {
            $cookie = [];
            if( isset( $_COOKIE['OptanonConsent'] ) ) {
                parse_str( $_COOKIE['OptanonConsent'], $cookie );
            }

            $cookie = array_replace_recursive(
                [ 'groups' => 'C0001:1' ],
                is_array( $cookie ) ? $cookie : [],
            );

            foreach( $cookie as $key => $val ) {
                switch( $key ) {
                    case 'groups':
                        $cookie[ $key ] = [];
                        foreach( explode( ',', $val ) as $group ) {
                            $group = explode( ':', $group );

                            $cookie[ $key ][ $group[0] ] = (bool) $group[1];
                        }
                        ksort( $cookie[ $key ] );
                        break;

                    case 'geolocation':
                        $val = explode( ';', $val );
                        $cookie[ $key ] = [
                            'country' => $val[0],
                            'state'   => $val[1]
                        ];
                        break;
                }
            }

            self::$_cookie = $cookie;
        }
    }
}
