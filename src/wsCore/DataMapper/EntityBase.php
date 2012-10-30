<?php
namespace wsCore\DataMapper;

abstract class EntityBase implements EntityInterface
{
    /** @var null|string  */
    protected $_model = NULL;

    /** @var null|string  */
    protected $_type = NULL;

    /** @var null|string */
    protected $_identifier = NULL;

    /** @var \wsCore\DbAccess\Relation_Interface[] */
    protected $_relations = array();

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