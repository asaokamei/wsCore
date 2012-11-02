<?php
namespace wsCore\DbAccess;

abstract class Entity_Abstract implements Entity_Interface
{
    /** @var null|string  */
    protected $_model = null;

    /** @var null|string  */
    protected $_type = null;

    /** @var null|string */
    protected $_identifier = null;

    /** @var bool */
    protected $_toDelete = false;

    /** @var \wsCore\DbAccess\Relation_Interface[] */
    protected $_relations = array();

    /**
     * TODO: think if this is the right place to set _type and _identifier.
     * @param null|\wsCore\DbAccess\Dao $model
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
        return $this->_type == EntityManager::TYPE_GET;
    }
    /**
     * @return null|string
     */
    public function _get_Id() {
        return $this->_identifier;
    }

    /**
     * @param $name
     * @return \wsCore\DbAccess\Relation_Interface
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

}
