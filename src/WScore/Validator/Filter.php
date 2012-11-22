<?php
namespace WScore\Validator;

class Filter
{
    // +----------------------------------------------------------------------+
    //  filter definitions (filters that alters the value).
    // +----------------------------------------------------------------------+

    public function filter_noNull( &$v ) {
        $v = str_replace( "\0", '', $v );
        return TRUE;
    }

    public function filter_myTrim( &$v ) {
        $v = trim( $v );
        return TRUE;
    }

    public function sanitize( &$v, $p ) {
        $v = filter_var( $v, $p );
        return !!$v;
    }

    public function filter_encoding( &$v, $p ) {
        $code = ( empty( $p ) ) ? static::$charCode: $p;
        if( mb_check_encoding( $v, $code ) ) {
            return TRUE;
        }
        $v = ''; // overwrite invalid encode string.
        return FALSE;
    }

    public function filter_mbConvert( &$v, $p ) {
        static $option = NULL;
        if( !isset( $option ) ) $option = array(
            'hankaku' => 'aks',
            'han_kana' => 'kh',
            'zen_hira' => 'HVc',
            'zen_kana' => 'KVC',
        );
        $convert = isset( $option[$p] ) ? $option[$p] : 'KV';
        $v = mb_convert_kana( $v, $convert, static::$charCode );
        return TRUE;
    }

    public function filter_sanitize( &$v, $p ) {
        static $option = NULL;
        if( !isset( $option ) ) $option = array(
            'mail' => FILTER_SANITIZE_EMAIL,
        );
        return TRUE;
    }

    public function filter_string( &$v, $p ) {
        if( $p == 'lower' ) {
            $v = strtolower( $v );
        }
        elseif( $p == 'upper' ) {
            $v = strtoupper( $v );
        }
        elseif( $p == 'capital' ) {
            $v = ucwords( $v );
        }
        return TRUE;
    }

    public function filter_default( &$v, $p, &$loop=NULL ) {
        if( !$v && "" == "$v" ) { // no value. set default...
            $v = $p;
        }
        return TRUE; // but it is not an error.
    }
    // +----------------------------------------------------------------------+
    //  filter definitions (filters for validation).
    // +----------------------------------------------------------------------+

    public function filter_required( $v, $p, &$loop=NULL ) {
        if( "$v" != '' ) { // it has some value in it. OK.
            return TRUE;
        }
        // now, the value is empty. check if it is "required".
        return FALSE;
    }

    /**
     * breaks loop if value is empty by returning $loop='break'.
     * validation is not necessary for empty value.
     *
     * @param $v
     * @param $p
     * @param $loop
     * @return bool
     */
    public function filter_loopBreak( $v, $p, &$loop ) {
        if( "$v" == '' ) { // value is really empty. break the loop.
            $loop = 'break'; // skip subsequent validations for empty values.
        }
        return TRUE;
    }

    public function filter_pattern( $v, $p ) {
        $ok = preg_match( "/^{$p}\$/", $v );
        return !!$ok;
    }
    // +----------------------------------------------------------------------+
}
