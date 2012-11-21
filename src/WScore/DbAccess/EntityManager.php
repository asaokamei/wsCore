<?php
namespace WScore\DbAccess;

/**
 * todo: entity maybe not registered automatically at generation.
 */
class EntityManager
{
    /** @var \WScore\DbAccess\Model[] */
    protected $models = array();

    /** @var Entity_Interface[] */
    protected $entities = array();

    /** @var \ReflectionMethod[] */
    protected $reflections = array();

    /** @var array    array( entityClass => modelClass ) */
    protected $entityToModel = array();
    
    /** @var \WScore\DiContainer\Dimplet */
    protected $container;

    /**
     * @param \WScore\DiContainer\Dimplet $container
     * @DimInjection Fresh Container
     */
    public function __construct( $container ) {
        $this->container = $container;
    }

    /**
     * @return \WScore\DiContainer\Dimplet
     */
    public function container() {
        return $this->container;
    }
    // +----------------------------------------------------------------------+
    //  Managing Model.
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\Model $model
     */
    public function registerModel( $model )
    {
        $modelName = get_class( $model );
        if( substr( $modelName, 0, 1 ) == '\\' ) $modelName = substr( $modelName, 1 );
        $this->models[ $modelName ] = $model;
        $this->setupReflection( $model->recordClassName );
    }

    /**
     * get model object from an entity object, entity class name, or model name.
     *
     * @param Entity_Interface|string $entity
     * @return \WScore\DbAccess\Model
     */
    public function getModel( $entity )
    {
        // get model class name.
        $model = $this->getModelNameFromEntity( $entity );
        if( substr( $model, 0, 1 ) == '\\' ) $model = substr( $model, 1 );
        if( !isset( $this->models[ $model ] ) ) {
            $this->models[ $model ] = $this->container->get( $model );
        }
        return $this->models[ $model ];
    }

    /**
     * gets model class name from entity class name or entity object.
     *
     * @param string $entity    entity class name.
     * @throws \RuntimeException
     * @return string
     */
    public function getModelNameFromEntity( $entity )
    {
        // $entity is an object.
        if( is_object( $entity ) ) {
            if( $entity instanceof Entity_Interface ) { // $entity is an *entity* object.
                return $entity->_get_Model();
            }
            throw new \RuntimeException( "cannot get model name from unknown object." );
        }
        // a string. must be a class name.
        if( isset( $this->entityToModel[ $entity ] ) ) { // found it in the table.
            return $this->entityToModel[ $entity ];
        }
        if( !in_array( 'WScore\DbAccess\Entity_Interface', class_implements( $entity ) ) ) {
            return $entity; // not an entity class.
        }
        // get model name from entity class by getting $_model default value using reflection.
        $refClass = new \ReflectionClass( $entity );
        $propList = $refClass->getDefaultProperties();
        $this->entityToModel[ $entity ] = $propList[ '_model' ];
        return $this->entityToModel[ $entity ];
    }

    // +----------------------------------------------------------------------+
    //  Managing entities
    // +----------------------------------------------------------------------+
    /**
     * @param Entity_Interface|string $entity
     */
    public function setupReflection( $entity )
    {
        // get class name of entity if it is an object.
        $class = is_object( $entity ) ? get_class( $entity ) : $entity;
        // get that magic method to setup private properties.   
        if( !isset( $this->reflections[ $class ] ) ) {
            $reflections = new \ReflectionMethod( $class, '_set_protected_vars' );
            $reflections->setAccessible( true );
            $this->reflections[ $class ] = $reflections;
        }
    }

    /**
     * @param Entity_Interface $entity
     * @param string           $prop
     * @param string           $value
     */
    public  function setEntityProperty( $entity, $prop, $value )
    {
        $class = get_class( $entity );
        if( !isset( $this->reflections[ $class ] ) ) {
            $this->setupReflection( $entity );
        }
        $ref = $this->reflections[ $class ];
        $ref->invoke( $entity, $prop, $value );
    }
    /**
     * @param Entity_Interface|Entity_Interface[] $entity
     * @return Entity_Interface|Entity_Interface[]
     */
    public function register( $entity )
    {
        if( is_array( $entity ) ) {
            foreach( $entity as $key => $ent ) {
                $entity[ $key ] = $this->register( $ent );
            }
            return $entity;
        }
        $this->setupEntity( $entity );
        $cenaId = $entity->_get_cenaId();
        if( !array_key_exists( $cenaId, $this->entities ) ) {
            $this->entities[ $cenaId ] = $entity;
        }
        return $entity;
    }

    /**
     * @param Entity_Interface $entity
     * @param string           $type
     * @param null|string           $identifier
     * @throws \RuntimeException
     */
    public function setupEntity( $entity, $type=null, $identifier=null )
    {
        if( !$entity ) return;
        if( $type ) {
            $this->setEntityProperty( $entity, 'type', $type );
        }
        elseif( !$entity->_get_Type() ) {
            $type = Entity_Interface::_ENTITY_TYPE_NEW_;
            $this->setEntityProperty( $entity, 'type', $type );
        }
        if( $identifier ) {
            $this->setEntityProperty( $entity, 'identifier', $identifier );
        }
        elseif( !$entity->_get_Id() ) {
            throw new \RuntimeException( 'identifier not set. ' );
        }
    }
    // +----------------------------------------------------------------------+
    //  Generating and saving Entities to database
    // +----------------------------------------------------------------------+
    /**
     * returns an entity object for a given id value.
     *
     * @param string $modelName   entity or model class name, or entity object.
     * @param string $id
     * @return Entity_Interface
     */
    public function getEntity( $modelName, $id )
    {
        $model = $this->getModel( $modelName );
        $entity = $model->find( $id );
        $this->setupEntity( $entity, Entity_Interface::_ENTITY_TYPE_GET_, $id );
        $entity = $this->register( $entity );
        return $entity;
    }

    /**
     * returns a *new* entity object.
     *
     * @param string        $modelName   entity or model class name, or entity object.
     * @param array|string  $data
     * @param null|string   $id
     * @return Entity_Interface
     */
    public function newEntity( $modelName, $data=array(), $id=null )
    {
        if( !is_array( $data ) ) {
            $id = $data;
            $data = array();
        }
        $model = $this->getModel( $modelName );
        /** @var $entity Entity_Interface */
        $entity = $model->getRecord( $data );
        $this->setupEntity( $entity, Entity_Interface::_ENTITY_TYPE_NEW_, $id );
        $entity = $this->register( $entity );
        return $entity;
    }

    /**
     * saves or delete registered entities to/from database.
     */
    public function save()
    {
        if( empty( $this->entities ) ) return;
        foreach( $this->entities as $entity ) {
            $this->saveEntity( $entity );
        }
    }

    /**
     * saves or delete an entity to/from database.
     *
     * @param Entity_Interface $entity
     */
    public function saveEntity( $entity )
    {
        $model  = $this->getModel( $entity );
        $delete = $entity->toDelete();
        if( $delete ) {
            if( $entity->isIdPermanent() ) { // i.e. entity is from db.
                $model->delete( $entity->_get_Id() );
            }
            // ignore if type is new; just not saving the entity.
        }
        elseif( !$entity->isIdPermanent() ) { // i.e. entity is new. insert this.
            $id = $model->insert( $entity );
            $this->setupEntity( $entity, Entity_Interface::_ENTITY_TYPE_GET_ , $id );
        }
        else {
            $id = $entity->_get_Id();
            $model->update( $id, (array) $entity );
        }
    }

    /**
     * deletes an entity by setting toDelete attribute of entity to true.
     * delete is done when em's save or saveEntity method is called.
     *
     * @param Entity_Interface $entity
     * @param bool $delete
     */
    public function delete( $entity, $delete=true ) {
        $this->setEntityProperty( $entity, 'toDelete', $delete );
    }

    /**
     * get relation.
     *
     * @param Entity_Interface $entity
     * @param string           $name
     * @return \WScore\DbAccess\Relation_Interface
     */
    public function relation( $entity, $name )
    {
        $model = $this->getModel( $entity );
        $relation = $model->relation( $entity, $name );
        return $relation;
    }

    /**
     * fetch entities for a simple condition;
     *
     * @param Entity_Interface|string  $name       entity or model class name, or entity object.
     * @param string|array             $value      pass array to fetch multiple entities.
     * @param null|string              $column     set to null to fetch by id.
     * @param bool                     $select     to get only the column value.
     * @return \WScore\DbAccess\Entity_Interface|\WScore\DbAccess\Entity_Interface[]
     */
    public function fetch( $name, $value, $column=null, $select=false )
    {
        $model = $this->getModel( $name );
        $entities = $model->fetch( $value, $column, $select );
        $entities = $this->register( $entities );
        return $entities;
    }
    // +----------------------------------------------------------------------+
}