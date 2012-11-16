<?php
namespace WScore\DbAccess;

abstract class Entity_Abstract implements Entity_Interface, \ArrayAccess
{
    // ----------------------------
    // about models.

    /** @var null|string  */
    protected $_model = null;

    // ----------------------------
    // about entity's state.

    /** @var null|string  */
    protected $_type = null;

    /** @var null|string */
    protected $_identifier = null;

    /** @var bool */
    protected $_toDelete = false;

    /** @var \WScore\DbAccess\Relation_Interface[] */
    protected $_relations = array();

    /** @var int */
    protected static $_id_for_new = 1;

    // ----------------------------
    // about validation result

    /** @var bool */
    protected $_isValid = true;

    /** @var array */
    protected $_errors = array();

    /** @var array */
    protected $_orig_data = array();
    // +----------------------------------------------------------------------+
    //  construction and modifying protected properties.
    // +----------------------------------------------------------------------+
    /**
     * @param null|\WScore\DbAccess\Model $model
     * @param null|string               $type
     * @throws \RuntimeException
     */
    public function __construct( $model=null, $type=null )
    {
        if( !isset( $model ) ) {
            throw new \RuntimeException( 'model must be defined in Entity' );
        }
        // setting up identifier.
        $this->_identifier = $model->getId( $this );
        if( $type == static::_ENTITY_TYPE_NEW_ && !$this->_identifier ) {
            $this->_identifier = static::$_id_for_new++;
        }
        // setting original data
        if( $type == static::_ENTITY_TYPE_GET_ ) {
            $data = get_object_vars( $this );
            foreach( $data as $prop => $val ) {
                if( substr( $prop, 0, 1 ) != '_' ) {
                    $this->_orig_data[ $prop ] = $val;
                }
            }
        }
        $this->_model = $model->getModelName();
        $this->_type = $type;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function _set_protected_vars( $name, $value ) {
        $varName = '_' . $name;
        $this->$varName = $value;
    }
    // +----------------------------------------------------------------------+
    //  state of the entity.
    // +----------------------------------------------------------------------+
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
        return $this->_type == Entity_Interface::_ENTITY_TYPE_GET_;
    }
    /**
     * note: id and identifier are different. this method returns identifier, which maybe set
     * for newly created entity. to get the id in the database, use EM's getId() method.
     *
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
        $type   = $this->_type;
        $id     = $this->_identifier;
        if( !$id ) {
            throw new \RuntimeException( 'entity without id' );
        }
        if( !$type ) {
            throw new \RuntimeException( 'entity without type' );
        }
        $cenaId = "$model.$type.$id";
        return $cenaId;
    }
    // +----------------------------------------------------------------------+
    //  for relations. they are meant to be really public.
    // +----------------------------------------------------------------------+
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
    // +----------------------------------------------------------------------+
    //  for validation result
    // +----------------------------------------------------------------------+
    /**
     * @return bool
     */
    public function _is_valid() {
        return $this->_isValid;
    }

    /**
     * @param null|string $name
     * @return mixed
     */
    public function _pop_error( $name=null ) {
        if( !$name ) return $this->_errors;
        return array_key_exists( $name, $this->_errors ) ? $this->_errors[ $name ] : null;
    }
    // +----------------------------------------------------------------------+
    //  for ArrayAccess
    // +----------------------------------------------------------------------+
    /**
     */
    public function offsetExists( $offset ) {
        if( substr( $offset, 0, 1 ) != '_' && isset( $this->$offset ) ) return true;
        return false;
    }

    /**
     */
    public function offsetGet( $offset ) {
        if( substr( $offset, 0, 1 ) != '_' && isset( $this->$offset ) ) return $this->$offset;
        return null;
    }

    /**
     */
    public function offsetSet( $offset, $value )
    {
        if( is_null( $offset ) ) {
            foreach( $value as $key => $val ) {    $this->offsetSet( $key, $value ); }
        }
        elseif( substr( $offset, 0, 1 ) != '_' ) { $this->$offset = $value; }
    }

    /**
     */
    public function offsetUnset( $offset ) {
        if( substr( $offset, 0, 1 ) != '_' && isset( $this->$offset ) ) unset( $this->$offset );
    }
    // +----------------------------------------------------------------------+
}
