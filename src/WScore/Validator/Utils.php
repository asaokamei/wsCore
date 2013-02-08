<?php
namespace WScore\Validator;

class Utils
{
    /**
     * @param      $arr
     * @param      $key
     * @param null $default
     * @return null
     */
    public static function arrGet( $arr, $key, $default=null ) 
    {
        if( !is_string( $key ) ) return false;
        if( !is_array(  $arr ) ) return false;
        return array_key_exists( $key, $arr ) ? $arr[ $key ] : $default;
    }
    
    // +----------------------------------------------------------------------+
    //  special filters for multiple and sameWith rules.
    // +----------------------------------------------------------------------+
    /**
     * @var array   options for multiple preparation.
     */
    public static $multiples = array(
        'date'     => array( 'suffix' => 'y,m,d', 'connector' => '-', ),
        'YMD'      => array( 'suffix' => 'y,m,d', 'connector' => '-', ),
        'YM'       => array( 'suffix' => 'y,m',   'connector' => '-', ),
        'time'     => array( 'suffix' => 'h,i,s', 'connector' => ':', ),
        'His'      => array( 'suffix' => 'h,i,s', 'connector' => ':', ),
        'Hi'       => array( 'suffix' => 'h,i',   'connector' => ':', ),
        'datetime' => array( 'suffix' => 'y,m,d,h,i,s', 'format' => '%04d-%02d-%02d %02d:%02d:%02d', ),
        'tel'      => array( 'suffix' => '1,2,3',   'connector' => '-', ),
        'credit'   => array( 'suffix' => '1,2,3,4', 'connector' => '', ),
        'amex'     => array( 'suffix' => '1,2,3',   'connector' => '', ),
    );

    /**
     * prepares for validation by creating a value from multiple value.
     *
     * @param string       $name
     * @param array        $source
     * @param string|array $option
     * @return mixed|null|string
     */
    public static function prepare_multiple( $name, $source, $option )
    {
        // get options.
        if( is_string( $option ) ) {
            $option = self::arrGet( self::$multiples, $option, array() );
        }
        $sep = array_key_exists( 'separator', $option ) ? $option[ 'separator' ]: '_';
        $con = array_key_exists( 'connector', $option ) ? $option[ 'connector' ]: '-';
        // find multiples values from suffix list.
        $lists = array();
        $suffix = explode( ',', $option[ 'suffix' ] );
        foreach( $suffix as $sfx ) {
            $name_sfx = $name . $sep . $sfx;
            if( array_key_exists( $name_sfx, $source ) ) {
                $lists[] = $source[ $name_sfx ];
            }
        }
        // merge the found list into one value.
        $found = null; // default is null if list was not found.
        if( !empty( $lists ) )
        {
            // found format using sprintf.
            if( isset( $option[ 'format' ] ) ) {
                $param = array_merge( array( $option[ 'format' ] ), $lists );
                $found = call_user_func_array( 'sprintf', $param );
            }
            else {
                $found = implode( $con, $lists );
            }
        }
        return $found;
    }

    // +----------------------------------------------------------------------+
    /**
     * prepares filter for sameWith rule.
     * get another value to compare in sameWith, and compare it with the value using sameAs rule.
     *
     * @param NewValidator $dio
     * @param array|Rules  $filters
     * @return array
     */
    public static function prepare_sameWith( $dio, $filters )
    {
        if( !self::arrGet( $filters, 'sameWith' ) ) return $filters;
        // find the same with value.
        $sub_name = $filters[ 'sameWith' ];
        if( is_object( $filters ) ) {
            $sub_filter = clone $filters;
        } else {
            $sub_filter = $filters;
        }
        $sub_filter[ 'sameWith' ] = false;
        $sub_filter[ 'required' ] = false;
        $dio->find( $sub_name, $sub_filter );
        $value = $dio->pop( $sub_name );

        // reset sameWith filter, and set same{As|Empty} filter.
        $filters[ 'sameWith' ] = false;
        if( $value ) {
            $filters[ 'sameAs' ] = $value;
        }
        else {
            $filters[ 'sameEmpty' ] = true;
        }
        $filters[ 'sameWith' ] = false;
        return $filters;
    }
    
}