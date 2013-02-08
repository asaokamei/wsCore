<?php
namespace wsModule\Alt\Web;

class Utils
{
    /**
     * @return callable
     */
    public static function verifyEncoding() {
        return function( $result, $val ) {
            $ok = mb_check_encoding( $val, Request::CHAR_SET );
            return $result && $ok;
        };
    }

    /**
     * @return callable
     */
    public static function verifyFile()
    {
        return function( $result, $val ) {
            $ok = preg_match( '/^[-\._a-zA-Z0-9]+$/', $val );
            return $result && $ok;
        };
    }

    /**
     * @return callable
     */
    public static function verifyCode()
    {
        return function( $result, $val ) {
            $ok = preg_match( '/^[_a-zA-Z0-9]+$/', $val );
            return $result && $ok;
        };
    }

    /**
     * @param $match
     * @return callable
     */
    public static function getVerifyMatch( $match )
    {
        return function( $result, $val ) use( $match ) {
            $match = preg_quote( $match );
            $ok = preg_match( "/^{$match}+$/", $val );
            return $result && $ok;
        };
    }
}