<?php
namespace wsModule\Alt\Web;

/**
 * Request class
 * TODO: create Http folder.
 * TOD: move Request, Response, and Router class to Http folder.
 * from Perfect PHP.
 */
class Request
{
    const CHAR_SET = 'UTF-8';

    /** @var array         holds server info. */
    protected $_server  = array();

    /** @var string|bool   path info */
    protected $path_info = null;

    /** @var string|null   base URL */
    protected $base_url  = null;

    /** @var array         post data */
    protected $_post     = array();
    // +-------------------------------------------------------------+
    /**
     * @param array $config    alternative to $_SERVER info.
     */
    function __construct( $config=array() )
    {
        if( !empty( $config ) ) {
            $this->_server = $config;
        }
        else {
            $this->_server = & $_SERVER;
        }
    }

    /**
     * check if request method is POST.
     * @return bool
     */
    function isPost() {
        return ( $this->_server[ 'REQUEST_METHOD' ] === 'POST' ) ? true: false;
    }

    /**
     * @return string
     */
    function getMethod() {
        if( $this->_server[ 'REQUEST_METHOD' ] === 'POST' ) {
            return 'post';
        }
        return 'get';
    }
    /**
     * get host name of server.
     * @return bool
     */
    function getHost() {
        $host = null;
        if( isset( $this->_server[ 'HTTP_HOST' ] ) ) {
            $host = $this->_server[ 'HTTP_HOST' ];
        }
        elseif( isset( $this->_server[ 'SERVER_NAME' ] ) ) {
            $host = $this->_server[ 'SERVER_NAME' ];
        }
        return $this->h( $host );
    }
    /**
     * @return string
     */
    function getRequestUri() {
        return $this->h( urldecode( $this->_server[ 'REQUEST_URI' ] ) );
    }
    /**
     * @return string
     */
    function getScriptName() {
        return urldecode( $this->_server[ 'SCRIPT_NAME' ] );
    }
    /** html special chars wrapper.
     * @param $string
     * @return string
     */
    function h( $string ) {
        return htmlspecialchars( $string, ENT_QUOTES, self::CHAR_SET );
    }
    // +-------------------------------------------------------------+
    /**
     * @param null|string $url
     * @return string
     */
    function getBaseUrl( $url=null )
    {
        if( !isset( $this->base_url ) ) {
            $this->base_url = $this->calBaseUrl();
        }
        if( $url && substr( $url, 0, 1 ) !== '/' ) {
            $url = '/' . $url;
        }
        $base = "{$this->base_url}{$url}";
        $base = $this->truePath( $base );
        return $base;
    }
    /**
     * @param string $url
     */
    function setBaseUrl( $url ) {
        $this->base_url = $url;
    }
    /**
     * get base url of this application.
     * @return string
     */
    function calBaseUrl()
    {
        $script_name = $this->getScriptName();
        $request_uri = $this->getRequestUri();
        $baseUrl = '';
        if( strpos( $request_uri, $script_name ) === 0 ) {
            $baseUrl = $script_name;
        }
        else if( strpos( $request_uri, dirname( $script_name ) ) === 0 ) {
            $baseUrl = rtrim( dirname( $script_name ), '/' );
        }
        if( substr( $baseUrl, -1 ) !== '/' ) {
            $baseUrl .= '/';
        }
        return $baseUrl;
    }
    // +-------------------------------------------------------------+
    /**
     * @param array|null $config
     * @return bool|string
     */
    function getPathInfo( $config=null ) {
        if( isset( $config ) ) $this->_server = $config;
        if( !isset( $this->path_info ) ) {
            $this->path_info = $this->calPathInfo();
        }
        return $this->path_info;
    }
    /**
     * @param $path
     * @return mixed
     */
    function setPathInfo( $path ) {
        $this->path_info = $path;
        return $path;
    }
    /**
     * get path info, url from the base url to the end.
     * path info does NOT starts with '/'.
     * @return string
     */
    function calPathInfo() {
        $base_url    = $this->getBaseUrl();
        $request_uri = $this->getRequestUri();
        if( ( $pos = strpos( $request_uri, '?' ) ) !== false ) {
            $request_uri = substr( $request_uri, 0, $pos );
        }
        $path_info = (string) substr( $request_uri, strlen( $base_url ) );
        if( substr( $path_info, 0, 1 ) === '/' ) {
            $path_info = substr( $path_info, 1 );
        }
        return $path_info;
    }
    // +-------------------------------------------------------------+
    /**
     * This function is to replace PHP's extremely buggy realpath().
     * from http://stackoverflow.com/questions/4049856/replace-phps-realpath
     * @param string $path   The original path, can be relative etc.
     * @return string        The resolved path, it might not exist.
     */
    function truePath($path)
    {
        // whether $path is unix or not
        $unipath=strlen($path)==0 || $path{0}!='/';
        /** @var $lastSlash string */
        $lastSlash = ( substr($path, -1, 1 ) === '/' ) ? '/' : '';
        // attempts to detect if path is relative in which case, add cwd
        if(strpos($path,':')===false && $unipath)
            $path=getcwd().DIRECTORY_SEPARATOR.$path;
        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.'  == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $path=implode(DIRECTORY_SEPARATOR, $absolutes);
        // resolve any symlinks
        //if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
        // put initial separator that could have been lost
        $path=!$unipath ? '/'.$path : $path;
        return $path . $lastSlash;
    }
    // +-------------------------------------------------------------+
    /**
     * @param array $post
     * @return Request
     */
    function setPostData( $post ) {
        $this->_post = $post;
        return $this;
    }

    /**
     * @param mixed $val
     * @param string $verifyName
     * @return bool|callable|mixed
     */
    function _verify( $val, $verifyName ) {
        /** @var $verify \closure */
        $verify = Utils::$verifyName();
        $result = false;
        if( is_callable( $verify ) ) {
            $result = ( is_array( $val ) ) ?
                array_reduce( $val, $verify, true ):
                $verify( true, $val );
        }
        return $result;
    }

    /**
     * @param string $name
     * @return bool
     */
    function _getPost( $name ) {
        if( !empty( $this->_post ) ) {
            $post = &$this->_post;
        }
        else {
            $post = &$_REQUEST;
        }
        $val = false;
        if( array_key_exists( $name, $post ) ) {
            $val = $post[ $name ];
            $ok  = $this->_verify( $val, 'verifyEncoding' );
            if( !$ok ) { $val = false;}
        }
        return $val;
    }

    /**
     * @param string $name
     * @param null|string $type
     * @return bool
     */
    function getPost( $name, $type=null ) {
        $val = $this->_getPost( $name );
        $ok  = true;
        if( $val === false ) return $val;
        if( $type == 'filename' ) {
            $ok = $this->_verify( $val, 'verifyFile' );
        }
        elseif( $type == 'code' ) {
            $ok = $this->_verify( $val, 'verifyCode' );
        }
        elseif( isset( $type ) ) {
            // this code is not working yet.
            //$verify = $this->getVerifyMatch( $type );
            //$ok = $this->_verify( $val, 'verifyCode' );
        }
        if( !$ok ) $val = false;
        return $val;
    }
    // +-------------------------------------------------------------+
    /**
     * @param bool $langOnly
     * @return array
     */
    function getLanguageList( $langOnly=true ) {
        $languages = array();
        if( isset( $this->_server[ 'HTTP_ACCEPT_LANGUAGE' ] ) ) {
            $languages = $this->parseAcceptLanguage( $this->_server[ 'HTTP_ACCEPT_LANGUAGE' ] );
            foreach( $languages as &$lang ) {
                if( $langOnly && strpos( $lang, '-' ) !== false ) {
                    $lang = substr( $lang, 0, strpos( $lang, '-' ) );
                }
            }
        }
        return $languages;
    }
    /**
     * thanks for
     * http://www.dyeager.org/blog/2008/10/getting-browser-default-language-php.html
     * @param string $accept
     * @return array|bool
     */
    function parseAcceptLanguage( $accept ) {
        if( strlen( $accept ) <= 0 ) return false;
        $list = explode( ',', $accept );
        $unsorted = array();
        foreach( $list as $val ) {
            if( preg_match( "/(.*);q=([0-1]{0,1}\.\d{0,4})/i", $val, $matches ) ) {
                $lang = $matches[1];
                $qVal = (float) $matches[2];
            }
            else {
                $lang = $val;
                $qVal = 1.0;
            }
            $unsorted[ $lang ] = $qVal;
        }
        arsort( $unsorted );
        $langList = array();
        foreach( $unsorted as $lang => $qVal ) {
            $langList[] = $lang;
        }
        return $langList;
    }
    // +-------------------------------------------------------------+
}