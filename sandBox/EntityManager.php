<?php

// testing ReflectionProperty

class SomEntity extends EntityBase {}

$entity = new SomEntity();
$prop = new ReflectionProperty( $entity, '_type' );
$prop->setAccessible( TRUE );
$prop->setValue( $entity, 'test' );
echo $prop->getValue( $entity );
// echo $prop->_type; // error!

/*
 * Dao and Model has the same "name", that is 'yourModel' are common like:
 * \App\Dao\yourModel and \App\Model\yourModel.
 *
 * Dao will generate entity (not DataRecord).
 * when retrieved entities from db, register the entities to EntityManager:
 * $em->register( $entity );
 *
 * What to do with DataRecord, an ActiveRecord implementation???
 */

// TODO: EntityBase, yet another inheritance? Or, plain object? Or abstract/trait???
// no problem.

class EntityBase
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
     * @param $name
     * @return wsCore\DbAccess\Relation_Interface
     */
    public function getRelation( $name ) {
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

class EntityManager
{
    /** @var \wsCore\DbAccess\Dao[] */
    protected $models = array();

    /** @var EntityBase[] */
    protected $entities = array();

    /** @var ReflectionProperty[][] */
    protected $reflections = array();

    /** @var int */
    protected $newId = 1;

    // +----------------------------------------------------------------------+
    //  Managing Dao.
    // +----------------------------------------------------------------------+
    /**
     * @param $dao
     * @return EntityManager
     */
    public function registerDao( $dao ) {
        $model = $this->getModelName( $dao );
        $this->models[ $model ] = $dao;
        return $this;
    }

    /**
     * @param $entity
     * @return EntityManager
     */
    public function setupReflection( $entity )
    {
        $class = is_object( $entity ) ? get_class( $entity ) : $entity;
        if( !isset( $this->reflections[ $class ] ) )
        {
            $refType = new ReflectionProperty( $class, '_type' );
            $refType->setAccessible( TRUE );
            $refId   = new ReflectionProperty( $class, '_identifier' );
            $refId->setAccessible( TRUE );
            $refModel  = new ReflectionProperty( $class, '_model' );
            $refModel->setAccessible( TRUE );
            $reflections = array(
                'model' => $refModel,
                'type'  => $refType,
                'id'    => $refId,
            );
            $this->reflections[ $class ] = $reflections;
        }
        return $this;
    }
    
    /**
     * @param $entity
     * @return wsCore\DbAccess\Dao
     */
    public function getModel( $entity ) {
        $model = $this->getEntityProperty( $entity, 'model' );
        return $this->models[ $model ];
    }

    /**
     * TODO: return without namespace part.
     * @param $entity
     * @return string
     */
    public function getModelName( $entity ) {
        if( is_object( $entity ) ) return get_class( $entity );
        return $entity;
    }

    // +----------------------------------------------------------------------+
    //  Managing Entities
    // +----------------------------------------------------------------------+
    /**
     * @param EntityBase $entity
     * @return \EntityManager
     */
    public function register( &$entity )
    {
        $cenaId = $this->getCenaId( $entity );
        if( array_key_exists( $cenaId, $this->entities ) ) {
            $entity = $this->entities[ $cenaId ];
        }
        else {
            $this->entities[ $cenaId ] = $entity;
        }
        return $this;
    }

    /**
     * @param string $model
     * @param string $type
     * @param null|string $id
     * @return \EntityBase
     */
    public function entity( $model, $type, $id=NULL )
    {
        $method = strtolower( $type ) . 'Entity';
        return $this->$method( $model, $id );
    }

    protected function setEntityProperty( $entity, $prop, $value ) {
        /** @var $ref ReflectionProperty */
        $class = get_class( $entity );
        $ref = $this->reflections[ $class ][ $prop ];
        $ref->setValue( $entity, $value );
    }

    protected function getEntityProperty( $entity, $prop ) {
        /** @var $ref ReflectionProperty */
        $class = get_class( $entity );
        $ref = $this->reflections[ $class ][ $prop ];
        return $ref->getValue( $entity );
    }

    /**
     * TODO: think about getting DataRecord or EntityBase...
     * @param string $model
     * @param string $id
     * @return \EntityBase
     */
    public function getEntity( $model, $id )
    {
        $dao = $this->getModel( $model );
        /** @var $entity EntityBase */
        $entity = $dao->find( $id );
        $this->setEntityProperty( $entity, 'id'  , $id );
        $this->setEntityProperty( $entity, 'type', 'get' );
        $this->register( $entity );
        return $entity;
    }

    /**
     * @param string      $model
     * @param null|string $id
     * @return \EntityBase
     */
    public function newEntity( $model, $id=NULL )
    {
        $dao = $this->getModel( $model );
        /** @var $entity EntityBase */
        $entity = $dao->getRecord();
        if( !$id ) $id = $this->newId++;
        $this->setEntityProperty( $entity, 'id'  , $id );
        $this->setEntityProperty( $entity, 'type', 'new' );
        return $entity;
    }

    /**
     * @param EntityBase $entity
     * @return string
     */
    public function getCenaId( $entity )
    {
        $model  = $this->getModelName( $entity );
        $id     = $this->getEntityProperty( $entity, 'id' );
        $type   = $this->getEntityProperty( $entity, 'type' );
        $cenaId = "$model.$type.$id";
        return $cenaId;
    }

    /**
     * @return EntityManager
     * @throws RuntimeException
     */
    public function save()
    {
        if( empty( $this->entities ) ) return $this;
        foreach( $this->entities as $entity )
        {
            $type  = $this->getEntityProperty( $entity, 'type' );
            $dao   = $this->getModel( $entity );
            if( $type == 'new' ) {
                $id = $dao->insert( $entity );
                $this->setEntityProperty( $entity, 'id'  , $id );
                $this->setEntityProperty( $entity, 'type', 'get' );
            }
            else {
                // TODO: remove id from update.
                $id     = $this->getEntityProperty( $entity, 'id' );
                $dao->update( $id, $entity );
            }
        }
        return $this;
    }
}