<?php
namespace WScore\DataMapper;

abstract class Entity_Abstract implements Entity_Interface, \ArrayAccess
{
    // ----------------------------
    // about models.

    /** @var null|string  */
    protected $_model = null;

    // ----------------------------
    // about entity's state.

    /** @var null|string    Entity_Interface::_ENTITY_TYPE_{NEW|GET}_ */
    private $_type = null;

    /** @var null|string */
    private $_identifier = null;
    
    /** @var null|string */
    private $_id_name = null;

    /** @var bool */
    private $_toDelete = false;

    /** @var \WScore\DataMapper\Relation_Interface[] */
    private $_relations = array();

    /** @var int */
    protected static $_id_for_new = 1;

    // ----------------------------
    // about validation result

    /** @var bool */
    private $_isValid = true;

    /** @var array */
    private $_errors = array();

    /** @var array */
    private $_orig_data = array();
    // +----------------------------------------------------------------------+
    //  construction and modifying protected properties.
    // +----------------------------------------------------------------------+
    /**
     * @param null|\WScore\DataMapper\Model $model
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
        $this->_id_name    = $model->getIdName();
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
     * returns model name (except namespace part of the model class).
     *
     * @return null|string
     */
    public function _get_Model() {
        return $this->_model;
    }

    /**
     * returns model class name.
     *
     * @return null|string
     */
    public function _get_ShortModel() {
        $model = $this->_model;
        if( strpos( $model, '\\' ) !== false ) {
            $model = substr( $model, strrpos( $model, '\\' )+1 );
        }
        return $model;
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
     * returns if the id value is permanent (i.e. id from database).
     *
     * @return bool
     */
    public function isIdPermanent() {
        return $this->_type == Entity_Interface::_ENTITY_TYPE_GET_;
    }
    /**
     * returns identifier value.
     * note: id and identifier are different. this method returns identifier, which maybe set
     * for newly created entity. to get the id in the database, use EM's getId() method.
     *
     * @return null|string
     */
    public function _get_Id() {
        return $this->_identifier;
    }

    /**
     * returns id (primary key) name. 
     * 
     * @return null|string
     */
    public function _get_id_name() {
        return $this->_id_name;
    }

    /**
     * @throws \RuntimeException
     * @return string
     */
    public function _get_cenaId( )
    {
        $model  = $this->_get_ShortModel();
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
     * @return \WScore\DataMapper\Entity_Interface[]
     */
    public function relation( $name ) {
        if( isset( $this->_relations[ $name ] ) ) return $this->_relations[ $name ];
        return null;
    }

    /**
     * @param $name
     * @param \WScore\DataMapper\Entity_Interface[] $relation
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
