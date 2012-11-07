<?php
namespace wsCore\Utilities;

class Tools
{

    /**
     * checks for existence of a key in an array/object without E_NOTICE.
     * returns default as 3rd argument if key is not set.
     *
     * @param array|object $data
     * @param string       $key
     * @param mixed        $default
     * @return mixed
     */
    public static function getKey( $data, $key, $default=null ) {
        if( is_array( $data ) && array_key_exists( $key, $data ) ) {
            return $data[ $key ];
        }
        elseif( is_object( $data ) && isset( $data->$key ) ) {
            return $data->$key;
        }
        return $default;
    }

    /**
     * unset a key in an array/object without E_NOTICE.
     *
     * @param array|object $data
     * @param string       $key
     */
    public static function unsetKey( $data, $key ) {
        if( is_array( $data ) && array_key_exists( $key, $data ) ) {
            unset( $data[ $key ] );
        }
        elseif( is_object( $data ) && isset( $data->$key ) ) {
            unset( $data->$key );
        }
    }
    /**
     * generates a random password.
     *
     * @param int  $length
     * @param bool $all
     * @return string
     */
    public static function password( $length=12, $all=false )
    {
        $vows   = 'aiue';
        $number = '23456789';
        $letter = 'bcdfghjkmnpqrstvwxyz';
        $symbol = '-_~!@#$%^&*()_+=';

        $select = function( $list, $max ) {
            $str = '';
            for( $i = 0; $i < $max; $i ++ ) {
                $str .= $list[ mt_rand( 0, strlen( $list ) - 1 ) ];
            }
            return $str;
        };
        // first, make password only with numbers and symbols.
        $password  = '';
        $password .= $select( $number, mt_rand( 2, 3 ) );
        if( $all ) { // use symbols.
            $password .= $select( $symbol, mt_rand( 1, 2 ) );
        }
        // create rest of password with alphabets. only one vows.
        $alphabet  = $select( $vows, 1 );
        $alphabet .= $alphabet[ mt_rand( 0, strlen( $alphabet ) - 1 ) ]; // duplicate one chars.
        $alphabet .= $select( $letter, $length - strlen( $password ) - 1 );
        $alphabet  = str_shuffle( $alphabet );

        // make some of the alphabets to upper case
        $divide = mt_rand( 1, strlen( $alphabet ) - 1 );
        $upper = strtoupper( substr( $alphabet, 0, $divide ) );
        $lower = substr( $alphabet, $divide );

        // put together and shuffle the password.
        $password .= $password . $lower . $upper;
        $password  = str_shuffle( $password );
        return $password;
    }
}