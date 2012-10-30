<?php
namespace wsCore\DataMapper;

class EntityManager
{
    /** @var \wsCore\DbAccess\Dao[] */
    protected $models = array();

    /** @var EntityInterface[] */
    protected $entities = array();

    /** @var \ReflectionProperty[][] */
    protected $reflections = array();

    /** @var int */
    protected $newId = 1;

    // +----------------------------------------------------------------------+
    //  Managing Model/Dao.
    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\DbAccess\Dao $model
     * @return EntityManager
     */
    public function registerModel( $model ) {
        $modelName = $this->getModelName( $model );
        $this->models[ $modelName ] = $model;
        $this->setupReflection( $model->recordClassName );
        return $this;
    }

    /**
     * @param EntityInterface|string $entity
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
            $reflections = array(
                'model' => $reflect( $class, '_model' ),
                'type'  => $reflect( $class, '_type' ),
                'id'    => $reflect( $class, '_identifier' ),
            );
            $this->reflections[ $class ] = $reflections;
        }
        return $this;
    }
    
    /**
     * @param EntityInterface|string $entity
     * @return \wsCore\DbAccess\Dao
     */
    public function getModel( $entity ) {
        $model = ( $entity instanceof EntityInterface ) ? $entity->_get_Model(): $entity;
        return $this->models[ $model ];
    }

    /**
     * @param EntityInterface|string $entity
     * @return string
     */
    public function getModelName( $entity ) {
        $model = ( is_object( $entity ) ) ? get_class( $entity ) : $entity;
        if( strpos( $model, '\\' ) !== FALSE ) {
            $model = substr( $model, strrpos( $model, '\\' )+1 );
        }
        return $model;
    }

    // +----------------------------------------------------------------------+
    //  Managing Entities
    // +----------------------------------------------------------------------+
    /**
     * @param EntityInterface $entity
     * @return EntityManager
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
     * @return EntityInterface
     */
    public function entity( $model, $type, $id=NULL )
    {
        $method = strtolower( $type ) . 'Entity';
        return $this->$method( $model, $id );
    }

    public  function setEntityProperty( $entity, $prop, $value ) {
        /** @var $ref \ReflectionProperty */
        $class = get_class( $entity );
        $ref = $this->reflections[ $class ][ $prop ];
        $ref->setValue( $entity, $value );
    }

    public function getEntityProperty( $entity, $prop ) {
        /** @var $ref \ReflectionProperty */
        $class = get_class( $entity );
        $ref = $this->reflections[ $class ][ $prop ];
        return $ref->getValue( $entity );
    }

    /**
     * TODO: think about getting DataRecord or EntityBase...
     * @param string $modelName
     * @param string $id
     * @return EntityInterface
     */
    public function getEntity( $modelName, $id )
    {
        $model = $this->getModel( $modelName );
        /** @var $entity EntityInterface */
        $entity = $model->find( $id );
        $this->setEntityProperty( $entity, 'id'  , $id );
        $this->setEntityProperty( $entity, 'type', 'get' );
        $this->register( $entity );
        return $entity;
    }

    /**
     * @param string      $modelName
     * @param null|string $id
     * @return EntityInterface
     */
    public function newEntity( $modelName, $id=NULL )
    {
        $model = $this->getModel( $modelName );
        /** @var $entity EntityInterface */
        $entity = $model->getRecord();
        if( !$id ) $id = $this->newId++;
        $this->setEntityProperty( $entity, 'id'  , $id );
        $this->setEntityProperty( $entity, 'type', 'new' );
        $this->register( $entity );
        return $entity;
    }

    /**
     * @param EntityInterface $entity
     * @return string
     */
    public function getCenaId( $entity )
    {
        $this->setupReflection( $entity );
        $model  = $entity->_get_Model();
        $type   = $entity->_get_Type();
        if( !$type ) {
            $type = 'new';
            $this->setEntityProperty( $entity, 'type', $type );
        }
        $id     = $entity->_get_Id();
        if( !$id && $type == 'new' ) {
            $id = $this->newId++;
            $this->setEntityProperty( $entity, 'id', $id );
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
        foreach( $this->entities as $entity )
        {
            $type   = $entity->_get_Type();
            $model  = $this->getModel( $entity );
            if( $type == 'new' ) {
                $id = $model->insert( (array) $entity );
                $this->setEntityProperty( $entity, 'id'  , $id );
                $this->setEntityProperty( $entity, 'type', 'get' );
            }
            else {
                // TODO: remove id from update.
                $id = $entity->_get_Id();
                $model->update( $id, (array) $entity );
            }
        }
        return $this;
    }
}