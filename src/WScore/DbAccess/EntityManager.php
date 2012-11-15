<?php
namespace WScore\DbAccess;

class EntityManager
{
    /** @var \WScore\DbAccess\Model[] */
    protected $models = array();

    /** @var Entity_Interface[] */
    protected $entities = array();

    /** @var \ReflectionProperty[][] */
    protected $reflections = array();

    /** @var \WScore\DiContainer\Dimplet */
    protected $container;

    /**
     * @param \WScore\DiContainer\Dimplet $container
     * @DimInjection Fresh Container
     */
    public function __construct( $container ) {
        $this->container = $container;
    }

    public function container() {
        return $this->container;
    }
    // +----------------------------------------------------------------------+
    //  Managing Model/Model.
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\Model $model
     * @return EntityManager
     */
    public function registerModel( $model ) {
        $modelName = $this->getModelName( $model );
        $this->models[ $modelName ] = $model;
        $this->setupReflection( $model->recordClassName );
        return $this;
    }

    /**
     * @param Entity_Interface|string $entity
     * @return \WScore\DbAccess\Model
     */
    public function getModel( $entity ) {
        $model = ( $entity instanceof Entity_Interface ) ? $entity->_get_Model(): $entity;
        return $this->models[ $model ];
    }

    /**
     * @param Entity_Interface|string $entity
     * @return string
     */
    public function getModelName( $entity ) {
        $model = ( is_object( $entity ) ) ? get_class( $entity ) : $entity;
        if( strpos( $model, '\\' ) !== FALSE ) {
            $model = substr( $model, strrpos( $model, '\\' )+1 );
        }
        return $model;
    }

    /**
     * @param Entity_Interface|string $entity
     * @return EntityManager
     */
    public function setupReflection( $entity )
    {
        $class = is_object( $entity ) ? get_class( $entity ) : $entity;
        $reflect = function( $class, $prop ) {
            $reflect = new \ReflectionProperty( $class, $prop );
            $reflect->setAccessible( TRUE );
            return $reflect;
        };
        if( !isset( $this->reflections[ $class ] ) ) {
            $reflections = new \ReflectionMethod( $class, '_set_protected_vars' );
            $reflections->setAccessible( true );
            $this->reflections[ $class ] = $reflections;
        }
        return $this;
    }

    /**
     * @param Entity_Interface $entity
     * @param string $prop
     * @param string $value
     * @return \WScore\DbAccess\EntityManager
     */
    public  function setEntityProperty( $entity, $prop, $value ) {
        /** @var $ref \ReflectionMethod */
        $class = get_class( $entity );
        $ref = $this->reflections[ $class ];
        $ref->invoke( $entity, $prop, $value );
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  Managing Entities
    // +----------------------------------------------------------------------+
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
        if( array_key_exists( $cenaId, $this->entities ) ) {
            $entity = & $this->entities[ $cenaId ];
        }
        else {
            $this->entities[ $cenaId ] = $entity;
        }
        return $entity;
    }

    /**
     * @param Entity_Interface $entity
     * @param string $type
     * @param string $identifier
     * @throws \RuntimeException
     * @return \WScore\DbAccess\EntityManager
     */
    public function setupEntity( $entity, $type=NULL, $identifier=NULL )
    {
        if( $type ) {
            $this->setEntityProperty( $entity, 'type', $type );
        }
        elseif( !$entity->_get_Type() ) {
            $type = Entity_Interface::TYPE_NEW;
            $this->setEntityProperty( $entity, 'type', $type );
        }
        if( $identifier ) {
            $this->setEntityProperty( $entity, 'identifier', $identifier );
        }
        elseif( !$entity->_get_Id() ) {
            throw new \RuntimeException( 'identifier not set. ' );
        }
        return $this;
    }
    /**
     * @param string $modelName
     * @param string $id
     * @return Entity_Interface
     */
    public function getEntity( $modelName, $id )
    {
        $model = $this->getModel( $modelName );
        /** @var $entity Entity_Interface */
        $entity = $model->find( $id );
        $this->setupEntity( $entity, Entity_Interface::TYPE_GET, $id );
        $entity = $this->register( $entity );
        return $entity;
    }

    /**
     * @param string        $modelName
     * @param array|string  $data
     * @param null|string   $id
     * @return Entity_Interface
     */
    public function newEntity( $modelName, $data=array(), $id=NULL )
    {
        if( !is_array( $data ) ) {
            $id = $data;
            $data = array();
        }
        $model = $this->getModel( $modelName );
        /** @var $entity Entity_Interface */
        $entity = $model->getRecord( $data );
        $this->setupEntity( $entity, Entity_Interface::TYPE_NEW, $id );
        $entity = $this->register( $entity );
        return $entity;
    }

    /**
     * @param Entity_Interface $entity
     * @throws \RuntimeException
     * @return string
     */
    public function getCenaId( $entity )
    {
        $this->setupReflection( $entity );
        $model  = $entity->_get_Model();
        $type   = $entity->_get_Type();
        $id     = $entity->_get_Id();
        if( !$id ) {
            throw new \RuntimeException( 'entity without id' );
        }
        if( !$type ) {
            throw new \RuntimeException( 'entity without type' );
        }
        $cenaId = "$model.$type.$id";
        return $cenaId;
    }

    /**
     * @return EntityManager
     * @throws \RuntimeException
     */
    public function save()
    {
        if( empty( $this->entities ) ) return $this;
        foreach( $this->entities as $entity ) {
            $this->saveEntity( $entity );
        }
        return $this;
    }

    /**
     * @param Entity_Interface $entity
     * @return EntityManager
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
            $this->setupEntity( $entity, Entity_Interface::TYPE_GET , $id );
        }
        else {
            $id = $entity->_get_Id();
            $model->update( $id, (array) $entity );
        }
        return $this;
    }

    /**
     * @param Entity_Interface $entity
     * @param bool $delete
     */
    public function delete( $entity, $delete=true )
    {
        $this->setEntityProperty( $entity, 'del', $delete );
    }

    /**
     * @param Entity_Interface $entity
     * @param string           $name
     * @return \WScore\DbAccess\Relation_Interface
     */
    public function relation( $entity, $name )
    {
        $model = $this->getModel( $entity->_get_Model() );
        $relation = $model->relation( $entity, $name );
        return $relation;
    }
}