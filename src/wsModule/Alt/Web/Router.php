<?php
namespace wsModule\Alt\Web;

class Router
{
    /** @var array */
    protected $routes;

    public function __construct() {}

    /**
     * @param array $routes
     */
    public function set( $routes ) {
        $this->routes = $this->compile( $routes );
    }
    // +-------------------------------------------------------------+
    /**
     * prepare routes for preg_match method.
     * from Perfect PHP book.
     * @param $routes
     * @return array
     */
    public function compile( $routes ) {
        $_routes = array();
        foreach( $routes as $url => $match ) {
            $tokens = explode( '/', ltrim( $url, '/' ) );
            foreach( $tokens as $i => $token ) {
                if( strpos( $token, ':' ) === 0 ) {
                    $name  = substr( $token, 1 );
                    $token = "(?P<{$name}>[^/]+)";
                }
                $tokens[$i] = $token;
            }
            $pattern = '/' . implode( '/', $tokens );
            $_routes[ $pattern ] = $match;
        }
        return $_routes;
    }
    // +-------------------------------------------------------------+
    /**
     * matches $path against route patterns.
     * from Perfect PHP book.
     * @param string $path    path to match.
     * @return array|bool     returns matched result, or FALSE if not found.
     */
    public function match( $path ) {
        if( substr( $path, 0, 1 ) !== '/' ) {
            $path = '/' . $path;
        }
        foreach( $this->routes as $pattern => $match ) {
            if( preg_match( "#^{$pattern}$#", $path, $matches ) ) {
                $match = array_merge( $match, $matches );
                return $match;
            }
        }
        return FALSE;
    }
}