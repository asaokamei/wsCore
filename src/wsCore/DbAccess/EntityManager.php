<?php
namespace wsCore\DbAccess;

class EntityManager
{
    const TYPE_GET = 'get';
    const TYPE_NEW = 'new';

    /** @var \wsCore\DbAccess\Dao[] */
    protected $models = array();

    /** @var Entity_Interface[] */
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
     * @param Entity_Interface|string $entity
     * @return \wsCore\DbAccess\Dao
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
     * @param Entity_Interface $entity
     * @param string $prop
     * @param string $value
     * @return \wsCore\DbAccess\EntityManager
     */
    public  function setEntityProperty( $entity, $prop, $value ) {
        /** @var $ref \ReflectionProperty */
        $class = get_class( $entity );
        $ref = $this->reflections[ $class ][ $prop ];
        $ref->setValue( $entity, $value );
        return $this;
    }

    /**
     * @param Entity_Interface $entity
     * @param string $prop
     * @return mixed
     */
    public function getEntityProperty( $entity, $prop ) {
        /** @var $ref \ReflectionProperty */
        $class = get_class( $entity );
        $ref = $this->reflections[ $class ][ $prop ];
        return $ref->getValue( $entity );
    }

    // +----------------------------------------------------------------------+
    //  Managing Entities
    // +----------------------------------------------------------------------+
    /**
     * @param Entity_Interface|Entity_Interface[] $entity
     * @return EntityManager
     */
    public function register( &$entity )
    {
        if( is_array( $entity ) ) {
            foreach( $entity as &$ent ) {
                $this->register( $ent );
            }
            return $this;
        }
        $this->setupEntity( $entity );
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
     * @param Entity_Interface $entity
     * @param string $type
     * @param string $id
     * @return \wsCore\DbAccess\EntityManager
     */
    public function setupEntity( $entity, $type=NULL, $id=NULL )
    {
        if( $type ) {
            $this->setEntityProperty( $entity, 'type', $type );
        }
        elseif( !$entity->_get_Type() ) {
            if( !$type ) $type = self::TYPE_NEW;
            $this->setEntityProperty( $entity, 'type', $type );
        }
        $type = $entity->_get_Type();
        if( $id ) {
            $this->setEntityProperty( $entity, 'id', $id );
        }
        elseif( !$entity->_get_Id() ) {
            if( !$id && $type == self::TYPE_NEW ) {
                $id = $this->newId++;
            }
            elseif( !$id && $type == self::TYPE_GET ) {
                $model = $this->getModel( $entity->_get_Model() );
                $id = $model->getId( $entity );
            }
            $this->setEntityProperty( $entity, 'id', $id );
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
        $this->setupEntity( $entity, self::TYPE_GET, $id );
        $this->register( $entity );
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
        $this->setupEntity( $entity, self::TYPE_NEW, $id );
        $this->register( $entity );
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
        foreach( $this->entities as $entity )
        {
            $type   = $entity->_get_Type();
            $model  = $this->getModel( $entity );
            if( $type == self::TYPE_NEW ) {
                $id = $model->insert( (array) $entity );
                $this->setupEntity( $entity, self::TYPE_GET , $id );
            }
            else {
                $id = $entity->_get_Id();
                $model->update( $id, (array) $entity );
            }
        }
        return $this;
    }

    /**
     * @param Entity_Interface $entity
     * @param string           $name
     * @return \wsCore\DbAccess\Relation_Interface
     */
    public function relation( $entity, $name )
    {
        $model = $this->getModel( $entity->_get_Model() );
        $relation = $model->relation( $entity, $name );
        $entity->setRelation( $name, $relation );
        return $relation;
    }
}