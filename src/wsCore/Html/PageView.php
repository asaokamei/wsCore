<?php
namespace wsCore\Html;

class PageView implements \ArrayAccess
{
    public $newResource = array(
        '_links' => array(),
    );
    public $contents = array();

    private $pointer = NULL;

    // +-----------------------------------------------------------+
    public function __construct()
    {
        $this->contents = $this->newResource;
        $this->pointer = &$this->contents;
    }

    /**
     * set state of the resource.
     *
     * @param $name
     * @param $value
     * @return PageView
     */
    public function set( $name, $value )
    {
        $this->pointer[ $name ] = $value;
        return $this;
    }

    /**
     * set links for the resource.
     *
     * @param $name
     * @param $href
     * @return PageView
     */
    public function link( $name, $href )
    {
        $this->pointer[ '_links' ][ $name ] = array( 'href' => $href );
        return $this;
    }

    /**
     * create or point to embedded resources of the given type.
     *
     * @param $type
     */
    public function embed( $type )
    {
        if( !isset( $this->contents[ '_embedded' ] ) ) $this->contents[ '_embedded' ] = array();
        $embedded = $this->contents[ '_embedded' ];
        if( !isset( $embedded[ $type ] ) ) {
            $embedded[ $type ] = array();
        }
        $new_resource = $this->newResource;
        $embedded[ $type ][] = &$new_resource;
        $this->pointer = &$new_resource;
    }
    // +-----------------------------------------------------------+
    /**
     * get state of the top resources.
     *
     * @param $name
     * @return null
     */
    public function get( $name ) {
        return ( isset( $this->contents[ $name ] ) ) ? $this->contents[ $name ]: NULL;
    }

    /**
     * return embedded resource(s).
     *
     * @param $type
     * @return array
     */
    public function getEmbedded( $type )
    {
        if( isset( $this->contents[ '_embedded' ][ $type ] ) ) {
            if( count( $this->contents[ '_embedded' ][ $type ] ) == 1 ) {
                return $this->contents[ '_embedded' ][ $type ][0];
            }
            return $this->contents[ '_embedded' ][ $type ];
        }
        return array();
    }
    /**
     * create hidden tag for the name with the value.
     *
     * @param $name
     * @return string
     */
    public function getHiddenTag( $name )
    {
        $tags = '';
        if( isset( $this->contents[ $name ] ) ) {
            $value = $this->contents[ $name ];
            $tags  = "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\" />\n";
        }
        return $tags;
    }

    /**
     * create buttons based on $name's value.
     *   value: back, reset, or other for submit button with value.
     *
     * @param $name
     * @return string
     */
    public function getButton( $name )
    {
        if( !isset( $this->contents[ $name ] ) || empty( $this->contents[ $name ] ) ) return '';
        $type = $this->contents[ $name ];
        switch( $type ) {
            case 'back':
                $button = '<input type="button" name="Submit" value="前の画面に戻る" onClick="history.back();">';
                break;
            case 'reset':
                $button = '<input type="reset" name="Submit" value="リセット">';
                break;
            default:
                $button = "<input type=\"submit\" name=\"Submit\" value=\"{$type}\">";
                break;
        }
        return $button;
    }
    // +-----------------------------------------------------------+
    /**
     */
    public function offsetExists( $offset ) {
        return array_key_exists( $offset, $this->contents );
    }

    /**
     */
    public function offsetGet( $offset ) {
        return array_key_exists( $offset, $this->contents ) ? $this->contents[ $offset ]: null;
    }

    /**
     */
    public function offsetSet( $offset, $value ) {
        if( is_null( $offset ) ) {
            $this->contents = $value;
        }
        else {
            $this->contents[ $offset ] = $value;
        }
    }

    /**
     */
    public function offsetUnset( $offset ) {
        unset( $this->contents[ $offset ] );
    }
    // +-----------------------------------------------------------+
    /**
     * @param null|string $type
     * @return null|string
     */
    public function getMessage( &$type=null )
    {
        $list = array( 'error', 'info', 'success' );
        foreach( $list as $name ) {
            if( $message = $this->get( 'alert-' . $name ) ) {
                $type = $name;
                return $message;
            }
        }
        return null;
    }

    /**
     * @param string $message
     */
    public function success( $message ) {
        $this->set( 'alert-success', $message );
    }

    /**
     * @param string $message
     */
    public function notice( $message ) {
        $this->set( 'alert-info', $message );
    }

    /**
     * @param string $message
     */
    public function error( $message ) {
        $this->set( 'alert-error', $message );
    }
    // +-----------------------------------------------------------+
}