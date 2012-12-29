<?php
namespace WScore\DataMapper;

class EntityManager
{
    /** @var \WScore\DataMapper\Model[] */
    protected $models = array();

    /** @var \WScore\DiContainer\Dimplet */
    protected $container;

    /** @var \WScore\DataMapper\Entity_Property */
    protected $entityProperty;
    
    /** @var \WScore\DataMapper\Entity_Collection */
    protected $collection;

    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DiContainer\Dimplet        $container
     * @param \WScore\DataMapper\Entity_Property $entityProperty
     * @param \WScore\DataMapper\Entity_Collection $collection
     * @DimInjection Fresh Container
     * @DimInjection Fresh \WScore\DataMapper\Entity_Property
     * @DimInjection Fresh \WScore\DataMapper\Entity_Collection
     */
    public function __construct( $container, $entityProperty, $collection ) {
        $this->container = $container;
        $this->entityProperty = $entityProperty;
        $this->collection = $collection;
    }

    /**
     * @return \WScore\DiContainer\Dimplet
     */
    public function container() {
        return $this->container;
    }

    /**
     * @return Entity_Collection
     */
    public function emptyCollection() {
        return $this->collection->collection();
    }
    // +----------------------------------------------------------------------+
    //  Managing Model.
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\Model $model
     */
    public function registerModel( $model )
    {
        $modelName = get_class( $model );
        if( substr( $modelName, 0, 1 ) == '\\' ) $modelName = substr( $modelName, 1 );
        $this->models[ $modelName ] = $model;
        $this->entityProperty->setup( $model->recordClassName );
    }

    /**
     * get model object from an entity object, entity class name, or model name.
     *
     * @param Entity_Interface|string $entity
     * @return \WScore\DataMapper\Model
     */
    public function getModel( $entity )
    {
        // get model class name.
        $model = $this->entityProperty->getModelName( $entity );
        if( substr( $model, 0, 1 ) == '\\' ) $model = substr( $model, 1 );
        if( !isset( $this->models[ $model ] ) ) {
            $this->models[ $model ] = $this->container->get( $model );
        }
        return $this->models[ $model ];
    }

    /**
     * @param string|object $entity
     * @return bool
     */
    public function isEntity( $entity ) {
        return $this->entityProperty->isEntity( $entity );
    }

    /**
     * @param string $modelName
     * @return string
     */
    public function getIdName( $modelName ) {
        $model = $this->getModel( $modelName );
        return $model->getIdName();
    }
    // +----------------------------------------------------------------------+
    //  Managing entities
    // +----------------------------------------------------------------------+
    /**
     * @param Entity_Interface $entity
     * @param string           $prop
     * @param string           $value
     */
    public  function setEntityProperty( $entity, $prop, $value ) {
        $this->entityProperty->set( $entity, $prop, $value );
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
        if( !isset( $this->collection[ $cenaId ] ) ) {
            $this->collection->add( $entity );
        }
        return $entity;
    }

    /**
     * setup entities hidden state properties: _type and _identifier.
     *
     * @param Entity_Interface|Entity_Interface[] $entity
     * @param string           $type
     * @param null|string           $identifier
     * @throws \RuntimeException
     */
    public function setupEntity( $entity, $type=null, $identifier=null ) {
        $this->entityProperty->initialize( $entity, $type, $identifier );
    }
    // +----------------------------------------------------------------------+
    //  Generating and saving Entities to database
    // +----------------------------------------------------------------------+
    /**
     * returns an entity object for a given id value.
     * TODO: merge getEntity and fetch methods. 
     *
     * @param string $modelName   entity or model class name, or entity object.
     * @param string $id
     * @return Entity_Interface
     */
    public function getEntity( $modelName, $id )
    {
        $model = $this->getModel( $modelName );
        if( $this->isEntity( $modelName ) ) $model->setEntityClass( $modelName );
        $entity = $model->find( $id );
        $this->setupEntity( $entity, Entity_Interface::_ENTITY_TYPE_GET_ );
        $entity = $this->register( $entity );
        return $entity;
    }

    /**
     * fetch entities for a simple condition;
     *
     * @param Entity_Interface|string  $name       entity or model class name, or entity object.
     * @param string|array             $value      pass array to fetch multiple entities.
     * @param null|string              $column     set to null to fetch by id.
     * @return \WScore\DataMapper\Entity_Collection
     */
    public function fetch( $name, $value=null, $column=null )
    {
        $model = $this->getModel( $name );
        if( $this->isEntity( $name ) ) $model->setEntityClass( $name );
        $entities = $model->fetch( $value, $column );
        $this->setupEntity( $entities, Entity_Interface::_ENTITY_TYPE_GET_ );
        $entities = $this->register( $entities );
        return $this->collection->collection( $entities );
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
        if( $this->isEntity( $modelName ) ) $model->setEntityClass( $modelName );
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
        if( empty( $this->collection ) ) return;
        foreach( $this->collection as $entity ) {
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
                $model->delete( $entity->_get_Identifier() );
            }
            // ignore if type is new; just not saving the entity.
        }
        elseif( !$entity->isIdPermanent() ) { // i.e. entity is new. insert this.
            $id = $model->insert( $entity );
            $this->setupEntity( $entity, Entity_Interface::_ENTITY_TYPE_GET_ , $id );
        }
        else {
            $id = $entity->_get_Identifier();
            $model->update( $id, $entity );
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
     * @return \WScore\DataMapper\Relation_Interface
     */
    public function relation( $entity, $name )
    {
        $model = $this->getModel( $entity );
        $relation = Relation::getRelation( $this, $entity, $model->getRelationInfo(), $name );
        return $relation;
    }
    // +----------------------------------------------------------------------+
}