<?php
namespace AmidaMVC\Tools;
/**
 * Session wrapper.
 */

class Session
{
    /**  name of session variable */
    const SESSION_ID  = '_session_id_';

    /**  name of tokens stored in Session.     */
    const  TOKEN_ID   = 'session..token.ids';

    /**  name of token send via post */
    const  TOKEN_NAME = 'sessionTokenValue_';

    /** @var bool  flag to check if session started. */
    protected $session_start = FALSE;

    /** @var array|bool   where session data is */
    protected $_session = NULL;

    /** @var null   temporary saves generated token. */
    protected $session_token = NULL;

    // +-------------------------------------------------------------+
    function __construct( $config=NULL )
    {
        $this->start();
        if( !empty( $config ) && is_array( $config ) ) {
            $this->_session = $config;
        }
        else {
            if( !isset( $_SESSION[ self::SESSION_ID ] ) ) $_SESSION[ self::SESSION_ID ] = array();
            $this->_session = &$_SESSION[ self::SESSION_ID ];
        }
    }

    /**
     * @return bool
     */
    function start()
    {
        if( !$this->session_start ) {
            session_start();
            $this->session_start = TRUE;
        }
        return TRUE;
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    function set( $name, $value )
    {
        $this->_session[ $name ] = $value;
        return $value;
    }

    /**
     * @param $name
     * @return bool
     */
    function del( $name )
    {
        if( array_key_exists( $name,  $this->_session ) ) {
            unset( $this->_session[ $name ] );
        }
        return TRUE;
    }

    /**
     * @param $name
     * @return bool
     */
    function get( $name )
    {
        if( array_key_exists( $name,  $this->_session ) ) {
            return $this->_session[ $name ];
        }
        return FALSE;
    }
    // +-------------------------------------------------------------+
    //  managing token for CSRF.
    // +-------------------------------------------------------------+
    /**
     * @return string
     */
    function pushToken()
    {
        $token = md5( 'session.dumb' . time() . mt_rand(1,100*100) . __DIR__ );
        $this->_pushToken( $token );
        $this->session_token = $token;
        return $token;
    }

    /**
     * @param $token
     */
    function _pushToken( $token )
    {
        static::start();
        if( !isset( $this->_session[ static::TOKEN_ID ] ) ) {
            $this->_session[ static::TOKEN_ID ] = array();
        }
        $max_token = 20;
        $this->_session[ static::TOKEN_ID ][] = $token;
        if( count( $this->_session[ static::TOKEN_ID ] ) > $max_token ) {
            $num_remove = count( $this->_session[ static::TOKEN_ID ] ) - $max_token;
            $this->_session[ static::TOKEN_ID ] =
                array_slice( $this->_session[ static::TOKEN_ID ], $num_remove );
        }
    }

    /**
     * @return string
     */
    function popToken()
    {
        $name  = static::TOKEN_NAME;
        $value = $this->session_token;
        return "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\">";
    }

    /**
     * @return bool
     */
    function verifyToken()
    {
        $token = $_POST[ static::TOKEN_NAME ];
        if( $token && !empty( $this->_session[ static::TOKEN_ID ] ) ) {
            if( in_array( $token, $this->_session[ static::TOKEN_ID ] ) ) {
                foreach( $this->_session[ static::TOKEN_ID ] as $k=>$v ) {
                    if( $v === $token ) {
                        unset( $this->_session[ static::TOKEN_ID ][$k] );
                    }
                }
                $this->_session[ static::TOKEN_ID ] = array_values( $this->_session[ static::TOKEN_ID ] );
                return TRUE;
            }
        }
        return FALSE;
    }
    // +-------------------------------------------------------------+
}