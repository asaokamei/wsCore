<?php
namespace WScore\Validator;

class Filter
{
    public static $charCode = 'UTF-8';

    public function arrGet( $arr, $key, $default=null ) {
        if( !is_array( $arr ) ) return $default;
        return array_key_exists( $key, $arr ) ? $arr[$key] : $default;
    }
    // +----------------------------------------------------------------------+
    //  filter definitions (filters that alters the value).
    // +----------------------------------------------------------------------+

    public function filter_noNull( &$v ) {
        $v = str_replace( "\0", '', $v );
        return true;
    }

    public function filter_trim( &$v ) {
        $v = trim( $v );
        return true;
    }

    /**
     * options for sanitize.
     * @var array
     */
    public $sanitizes = array(
        'mail'   => FILTER_SANITIZE_EMAIL,
        'float'  => FILTER_SANITIZE_NUMBER_FLOAT,
        'int'    => FILTER_SANITIZE_NUMBER_INT,
        'url'    => FILTER_SANITIZE_URL,
        'string' => FILTER_SANITIZE_STRING,
    );
    public function sanitize( &$v, $p ) {
        $p = $this->arrGet( $this->patterns, $p, $p );
        $v = filter_var( $v, $p );
        return !!$v;
    }

    public function filter_encoding( &$v, $p ) {
        $code = ( empty( $p ) ) ? static::$charCode: $p;
        if( mb_check_encoding( $v, $code ) ) {
            return true;
        }
        $v = ''; // overwrite invalid encode string.
        return false;
    }

    public function filter_mbConvert( &$v, $p ) {
        static $option = null;
        if( !isset( $option ) ) $option = array(
            'hankaku' => 'aks',
            'han_kana' => 'kh',
            'zen_hira' => 'HVc',
            'zen_kana' => 'KVC',
        );
        $convert = isset( $option[$p] ) ? $option[$p] : 'KV';
        $v = mb_convert_kana( $v, $convert, static::$charCode );
        return true;
    }

    public function filter_sanitize( &$v, $p ) {
        static $option = null;
        if( !isset( $option ) ) $option = array(
            'mail' => FILTER_SANITIZE_EMAIL,
        );
        return true;
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
        return true;
    }

    public function filter_default( &$v, $p, &$loop=null ) {
        if( !$v && "" == "$v" ) { // no value. set default...
            $v = $p;
        }
        return true; // but it is not an error.
    }
    // +----------------------------------------------------------------------+
    //  filter definitions (filters for validation).
    // +----------------------------------------------------------------------+

    public function filter_required( $v, $p, &$loop=null ) {
        if( "$v" != '' ) { // it has some value in it. OK.
            return true;
        }
        // now, the value is empty. check if it is "required".
        return false;
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
        return true;
    }

    /**
     * options for patterns.
     * @var array
     */
    public $patterns = array(
        'number' => '[0-9]+',
        'int'    => '[-0-9]+',
        'float'  => '[-.0-9]+',
        'code'   => '[-_0-9a-zA-Z]+',
        'mail'   => '[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+\.[a-zA-Z]+',
    );

    /**
     * @param $v
     * @param $p
     * @return bool
     */
    public function filter_pattern( $v, $p ) {
        $p  = $this->arrGet( $this->patterns, $p, $p );
        $ok = preg_match( "/^{$p}\$/", $v );
        return !!$ok;
    }

    public function filter_sameAs( $v, $p ) {
        return $v===$p;
    }
    // +----------------------------------------------------------------------+
}
