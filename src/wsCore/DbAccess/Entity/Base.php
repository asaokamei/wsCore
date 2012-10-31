<?php
namespace wsCore\DataMapper;

abstract class Entity_Base implements EntityInterface
{
    /** @var null|string  */
    protected $_model = null;

    /** @var null|string  */
    protected $_type = null;

    /** @var null|string */
    protected $_identifier = null;

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
        if( !isset( $this->_model ) ) {
            throw new \RuntimeException( 'model must be defined in Entity' );
        }
        if( $model ) {
            $this->_identifier = $model->getId( $this );
        }
        if( $type ) $this->_type = $type;
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
        return $this->_type;
    }

    /**
     * @return bool
     */
    public function isIdPermanent() {
        return $this->_type == 'get';
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
        return $this->_relations[ $name ];
    }

    /**
     * @param $name
     * @param $relation
     * @return EntityBase
     */
    public function setRelation( $name, $relation ) {
        $this->_relations[ $name ] = $relation;
        return $this;
    }

}
