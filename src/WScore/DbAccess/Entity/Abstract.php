<?php
namespace WScore\DbAccess;

abstract class Entity_Abstract implements Entity_Interface, \ArrayAccess
{
    /** @var null|string  */
    protected $_model = null;

    /** @var null|string  */
    protected $_type = null;

    /** @var null|string */
    protected $_identifier = null;

    /** @var bool */
    protected $_toDelete = false;

    /** @var \WScore\DbAccess\Relation_Interface[] */
    protected $_relations = array();

    /**
     * TODO: think if this is the right place to set _type and _identifier.
     * @param null|\WScore\DbAccess\Dao $model
     * @param null|string               $type
     * @throws \RuntimeException
     */
    public function __construct( $model=null, $type=null )
    {
        if( $model ) {
            $this->_identifier = $model->getId( $this );
            if( !isset( $this->_model ) ) $this->_model = $model->getModelName();
        }
        if( $type ) $this->_type = $type;
        if( !isset( $this->_model ) ) {
            throw new \RuntimeException( 'model must be defined in Entity' );
        }
    }

    /**
     * @return null|string
     */
    public function _get_Model() {
        return $this->_model;
    }

    /**
     * @return null|string
     */
    public function _get_ModelClass() {
        return substr( $this->_model, strrpos( $this->_model, '\\' ) );
    }

    /**
     * @return null|string
     */
    public function _get_Type() {
        $type = $this->_type;
        return $type;
    }

    /**
     * @return bool
     */
    public function toDelete() {
        return $this->_toDelete;
    }

    /**
     * @return bool
     */
    public function isIdPermanent() {
        return $this->_type == Entity_Interface::TYPE_GET;
    }
    /**
     * @return null|string
     */
    public function _get_Id() {
        return $this->_identifier;
    }

    /**
     * @throws \RuntimeException
     * @return string
     */
    public function _get_cenaId( )
    {
        $model  = $this->_get_Model();
        $type   = $this->_get_Type();
        $id     = $this->_get_Id();
        if( !$id ) {
            throw new \RuntimeException( 'entity without id' );
        }
        if( !$type ) {
            throw new \RuntimeException( 'entity without type' );
        }
        $cenaId = "$model.$type.$id";
        return $cenaId;
    }

    /**
     * @param $name
     * @return \WScore\DbAccess\Relation_Interface
     */
    public function relation( $name ) {
        if( isset( $this->_relations[ $name ] ) ) return $this->_relations[ $name ];
        return null;
    }

    /**
     * @param $name
     * @param $relation
     * @return Entity_Interface
     */
    public function setRelation( $name, $relation ) {
        $this->_relations[ $name ] = $relation;
        return $this;
    }

    // +-----------------------------------------------------------+
    /**
     */
    public function offsetExists( $offset )
    {
        if( substr( $offset, 0, 1 ) != '_' && isset( $this->$offset ) ) return true;
        return false;
    }

    /**
     */
    public function offsetGet( $offset )
    {
        if( substr( $offset, 0, 1 ) != '_' && isset( $this->$offset ) ) return $this->$offset;
        return null;
    }

    /**
     */
    public function offsetSet( $offset, $value )
    {
        if( is_null( $offset ) ) {
            foreach( $value as $key => $val ) {
                if( substr( $offset, 0, 1 ) != '_' ) $this->$offset = $value;
            }
        }
        else {
            if( substr( $offset, 0, 1 ) != '_' ) $this->$offset = $value;
        }
    }

    /**
     */
    public function offsetUnset( $offset )
    {
        if( substr( $offset, 0, 1 ) != '_' && isset( $this->$offset ) ) unset( $this->$offset );
    }
    // +-----------------------------------------------------------+
}
