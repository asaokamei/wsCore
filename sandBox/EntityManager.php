<?php

// testing ReflectionProperty

$entity = new EntityBase();
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
    protected $_mapper = NULL;
    
    /** @var null|string  */
    private $_type = NULL;

    /** @var null|string */
    private $_identifier = NULL;

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
    protected $mapper = array();

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
        $mapName = $this->getModelName( $dao );
        $this->mapper[ $mapName ] = $dao;
        $entityClass = $dao->getEntityClass();
        $this->setupReflection( $entityClass );
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
            $refDao  = new ReflectionProperty( $class, '_mapper' );
            $refDao->setAccessible( TRUE );
            $reflections = array(
                'dao'  => $refDao,
                'type' => $refType,
                'id'   => $refId,
            );
            $this->reflections[ $class ] = $reflections;
        }
        return $this;
    }
    /**
     * @param $entity
     * @return wsCore\DbAccess\Dao
     */
    public function getMapper( $entity ) {
        $mapper = $this->getEntityProperty( $entity, '_mapper' );
        return $this->mapper[ $mapper ];
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
        $dao = $this->getMapper( $model );
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
        $dao = $this->getMapper( $model );
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
            $dao   = $this->getMapper( $entity );
            if( $type == 'new' ) {
                $id = $dao->insert( $entity );
                $this->setEntityProperty( $entity, 'id'  , $id );
                $this->setEntityProperty( $entity, 'type', 'get' );
            }
            elseif( $type == 'get' ) {
                // TODO: remove id from update.
                $id     = $this->getEntityProperty( $entity, 'id' );
                $dao->update( $id, $entity );
            }
            else {
                throw new RuntimeException( "Bad entity type: $type" );
            }
        }
        return $this;
    }
}