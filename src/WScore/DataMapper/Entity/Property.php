<?php
namespace WScore\DataMapper;

class Entity_Property
{
    /** @var \ReflectionMethod[] */
    protected $refPropertySetter = array();

    /** @var array    array( entityClass => modelClass ) */
    protected $entityToModel = array();

    // +----------------------------------------------------------------------+
    //  construction. no dependencies!
    // +----------------------------------------------------------------------+
    public function __construct() {}

    /**
     * @param Entity_Interface $entity
     * @param string           $prop
     * @param string           $value
     */
    // +----------------------------------------------------------------------+
    //  setting hidden properties inside an entity.
    // +----------------------------------------------------------------------+
    public function set( $entity, $prop, $value )
    {
        $class = get_class( $entity );
        if( !isset( $this->refPropertySetter[ $class ] ) ) {
            $this->setup( $entity );
        }
        $ref = $this->refPropertySetter[ $class ];
        $ref->invoke( $entity, $prop, $value );
    }

    /**
     * @param Entity_Interface|string $entity
     */
    public function setup( $entity )
    {
        // get class name of entity if it is an object.
        $class = is_object( $entity ) ? get_class( $entity ) : $entity;
        // get that magic method to setup private properties.   
        if( !isset( $this->refPropertySetter[ $class ] ) ) {
            $reflections = new \ReflectionMethod( $class, '_set_protected_vars' );
            $reflections->setAccessible( true );
            $this->refPropertySetter[ $class ] = $reflections;
        }
    }

    /**
     * initialize entities hidden state properties: _type and _identifier.
     *
     * @param Entity_Interface|Entity_Interface[] $entity
     * @param string           $type
     * @param null|string           $identifier
     * @throws \RuntimeException
     */
    public function initialize( $entity, $type=null, $identifier=null )
    {
        if( !$entity ) return;
        if( is_array( $entity ) ) {
            foreach( $entity as $ent ) {
                $this->initialize( $ent, $type, $identifier );
            }
            return;
        }
        // force to set type.
        if( $type ) {
            $this->set( $entity, 'type', $type );
        }
        // type is not set.
        elseif( !$entity->_get_Type() ) {
            // assume it is a new entity.
            $this->set( $entity, 'type', Entity_Interface::_ENTITY_TYPE_NEW_ );
        }
        // force to set identifier.
        if( $identifier ) {
            $this->set( $entity, 'identifier', $identifier );
        }
        elseif( !$entity->_get_Identifier() ) {
            throw new \RuntimeException( 'identifier not set. ' );
        }
    }

    // +----------------------------------------------------------------------+
    //  check if it is an entity. 
    // +----------------------------------------------------------------------+
    /**
     * @param string|object $entity
     * @return bool
     */
    public function isEntity( $entity )
    {
        if( is_object( $entity ) && $entity instanceof Entity_Interface ) {
            return true;
        }
        $interfaces = class_implements( $entity );
        if( is_string( $entity ) && 
            is_array( $interfaces ) && 
            in_array( 'WScore\DataMapper\Entity_Interface', $interfaces ) ) {
            return true;
        }
        return false;
    }

    // +----------------------------------------------------------------------+
    //  get model name from an entity.
    // +----------------------------------------------------------------------+
    /**
     * gets model class name from entity class name or entity object.
     *
     * @param string $entity    entity class name.
     * @throws \RuntimeException
     * @return string
     */
    public function getModelName( $entity )
    {
        if( !$this->isEntity( $entity ) ) return $entity;
        if( is_object( $entity ) ) { // $entity is an *entity* object.
            return $entity->_get_Model();
        }
        if( isset( $this->entityToModel[ $entity ] ) ) { // found it in the table.
            return $this->entityToModel[ $entity ];
        }
        // get model name from entity class by getting $_model default value using reflection.
        $refClass = new \ReflectionClass( $entity );
        $propList = $refClass->getDefaultProperties();
        $this->entityToModel[ $entity ] = $propList[ '_model' ];
        return $this->entityToModel[ $entity ];
    }
}