<?php
namespace WScore\DiContainer;

/**
 * a utility class for DiContainer to provide useful static methods. 
 * maybe used with hard dependencies. 
 * 
 * @author Asao Kamei
 */
class Utils
{
    // +----------------------------------------------------------------------+
    //  utilities.
    // +----------------------------------------------------------------------+

    /**
     * test if a string maybe a class name, which contains backslash and a-zA-Z0-9.
     * @param mixed $name
     * @return bool
     */
    public static function isClassName( $name ) {
        return is_string( $name ) && preg_match( "/^[_a-zA-Z0-9\\\\]*$/", $name ) && class_exists( $name );
    }

    // +----------------------------------------------------------------------+
    //  managing dependency options.
    // +----------------------------------------------------------------------+

    /**
     * normalize dependency option. 
     * option can be set for construct, property, or setter. 
     * 
     * @param $option
     * @return array
     */
    public static function normalizeOption( $option )
    {
        $normalized = array();
        if( empty( $option ) ) return $normalized;
        if( !is_array( $option ) ) $option = array( $option );
        if( isset( $option[ 'construct' ] ) ) {
            $normalized[ 'construct' ] = Utils::normalizeInjection( $option[ 'construct' ] );
        }
        if( isset( $option[ 'property' ] ) ) {
            $normalized[ 'property' ] = Utils::normalizeInjection( $option[ 'property' ] );
        }
        if( isset( $option[ 'setter' ] ) ) {
            $normalized[ 'setter' ] = Utils::normalizeInjection( $option[ 'setter' ] );
        }
        if( empty( $normalized ) ) {
            $normalized[ 'construct' ] = Utils::normalizeInjection( $option );
        }
        return $normalized;
    }

    /**
     * normalize dependency information.
     * 
     * @param $option
     * @return array
     */
    public static function normalizeInjection( $option )
    {
        if( empty( $option ) ) return $option;
        if( !is_array( $option ) ) $option = array( $option );
        // check injection info for each key... 
        foreach( $option as $key => $info ) 
        {
            // info must be an array having 'id' as object id to inject. 
            if( !is_array( $info ) ) $info = array( 'id' => $info );
            // convert numeric key to 'id', i.e. consider it as an object id. 
            foreach( $info as $k => $v ) {
                if( is_numeric( $k ) ) {
                    unset( $info[$k] );
                    $info[ 'id' ] = $v;
                }
            }
            $option[ $key ] = $info;
        }
        return $option;
    }
    /**
     * FROM:
     * http://www.php.net/manual/ja/function.array-merge-recursive.php#92195
     *
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public static function mergeOption ( array &$array1, array &$array2 )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = self::mergeOption ( $merged [$key], $value );
            }
            else
            {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }
    // +----------------------------------------------------------------------+
    //  parsing @DimInjection in PHPDoc.
    // +----------------------------------------------------------------------+

    /**
     * parse phpDoc comments for DimInjection.
     *
     * @param string $comments
     * @return array
     */
    public static function parseDimDoc( $comments )
    {
        if( !preg_match_all( "/(@.*)$/mU", $comments, $matches ) ) return array();
        $injectList = array();
        foreach( $matches[1] as $comment ) {
            if( !preg_match( '/@DimInjection[ \t]+(.*)$/', $comment, $comMatch ) ) continue;
            $dimInfo = preg_split( '/[ \t]+/', trim( $comMatch[1] ) );
            $injectList[] = self::parseDimInjection( $dimInfo );
        }
        return $injectList;
    }

    /**
     * parse @DimInjection comment into injection information.
     *
     * @param array $dimInfo
     * @return array
     */
    private static function parseDimInjection( $dimInfo )
    {
        $injectInfo = array(
            'by' => 'fresh',
            'ob' => 'obj',
            'id' => null,
        );
        foreach( $dimInfo as $info ) {
            switch( strtolower( $info ) ) {
                case 'none':   $injectInfo[ 'by' ] = null;      break;
                case 'get':    $injectInfo[ 'by' ] = 'get';     break;
                case 'fresh':  $injectInfo[ 'by' ] = 'fresh';   break;
                case 'raw':    $injectInfo[ 'ob' ] = 'raw';     break;
                case 'obj':    $injectInfo[ 'ob' ] = 'obj';     break;
                default:       $injectInfo[ 'id' ] = $info;     break;
            }
        }
        return $injectInfo;
    }

    /**
     * @param Dimplet  $container
     * @param array    $injectInfo
     * @return mixed|null
     */
    public static function constructByInfo( $container, $injectInfo )
    {
        extract( $injectInfo ); // gets $by, $ob, and $id.
        /** @var $by string   type of object fresh/get   */
        /** @var $ob string   type of construct obj/raw  */
        /** @var $id string   look for id to generate    */
        $object = null;
        if( $by && $ob && $id ) {
            if( $ob == 'raw' ) {
                $object = $container->raw( $id, $by );
            }
            else {
                $object = $container->$by( $id );
            }
        }
        return $object;
    }
    // +----------------------------------------------------------------------+
}