<?php

// testing ReflectionProperty

$entity = new EntityBase();
$prop = new ReflectionProperty( $entity, '_type' );
$prop->setAccessible( true );
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

class EntityBase
{
    /** @var null|string  */
    protected $_type = null;

    /** @var null|string */
    protected $_identifier = null;

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
    protected $dao = array();

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
        $this->dao[ $model ] = $dao;
        $entityClass = $dao->getEntityClass();
        if( !isset( $this->reflections[ $model ] ) )
        {
            $refType = new ReflectionProperty( $entityClass, '_type' );
            $refType->setAccessible( true );
            $refId   = new ReflectionProperty( $entityClass, '_identifier' );
            $refId->setAccessible( true );
            $reflections = array(
                'type' => $refType,
                'id'   => $refId,
            );
            $this->reflections[ $model ] = $reflections;
        }
        return $this;
    }

    /**
     * @param $entity
     * @return wsCore\DbAccess\Dao
     */
    public function getDao( $entity ) {
        $model = $this->getModelName( $entity );
        return $this->dao[ $model ];
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
    public function entity( $model, $type, $id=null )
    {
        $method = strtolower( $type ) . 'Entity';
        return $this->$method( $model, $id );
    }

    protected function setEntityProperty( $model, $prop, $entity, $value ) {
        /** @var $ref ReflectionProperty */
        $ref = $this->reflections[ $model ][ $prop ];
        $ref->setValue( $entity, $value );
    }

    protected function getEntityProperty( $model, $prop, $entity ) {
        /** @var $ref ReflectionProperty */
        $ref = $this->reflections[ $model ][ $prop ];
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
        $dao = $this->getDao( $model );
        /** @var $entity EntityBase */
        $entity = $dao->find( $id );
        $this->setEntityProperty( $model, 'id',   $entity, $id );
        $this->setEntityProperty( $model, 'type', $entity, 'get' );
        $this->register( $entity );
        return $entity;
    }

    /**
     * @param string      $model
     * @param null|string $id
     * @return \EntityBase
     */
    public function newEntity( $model, $id=null )
    {
        $dao = $this->getDao( $model );
        /** @var $entity EntityBase */
        $entity = $dao->getRecord();
        if( !$id ) $id = $this->newId++;
        $this->setEntityProperty( $model, 'id',   $entity, $id );
        $this->setEntityProperty( $model, 'type', $entity, 'new' );
        return $entity;
    }

    /**
     * @param EntityBase $entity
     * @return string
     */
    public function getCenaId( $entity )
    {
        $model  = $this->getModelName( $entity );
        $id     = $this->getEntityProperty( $model, 'id',   $entity );
        $type   = $this->getEntityProperty( $model, 'type', $entity );
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
            $model = $this->getModelName( $entity );
            $type  = $this->getEntityProperty( $model, 'type', $entity );
            $dao   = $this->getDao( $entity );
            if( $type == 'new' ) {
                $id = $dao->insert( $entity );
                $this->setEntityProperty( $model, 'id',   $entity, $id );
                $this->setEntityProperty( $model, 'type', $entity, 'get' );
            }
            elseif( $type == 'get' ) {
                // TODO: remove id from update.
                $id     = $this->getEntityProperty( $model, 'id',   $entity );
                $dao->update( $id, $entity );
            }
            else {
                throw new RuntimeException( "Bad entity type: $type" );
            }
        }
        return $this;
    }
}