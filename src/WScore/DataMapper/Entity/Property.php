<?php
namespace WScore\DataMapper;

class Entity_Property
{
    /** @var \ReflectionMethod[] */
    protected $reflections = array();

    /** @var array    array( entityClass => modelClass ) */
    protected $entityToModel = array();

    // +----------------------------------------------------------------------+
    public function __construct() {}

    /**
     * @param Entity_Interface $entity
     * @param string           $prop
     * @param string           $value
     */
    public function set( $entity, $prop, $value )
    {
        $class = get_class( $entity );
        if( !isset( $this->reflections[ $class ] ) ) {
            $this->setup( $entity );
        }
        $ref = $this->reflections[ $class ];
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
        if( !isset( $this->reflections[ $class ] ) ) {
            $reflections = new \ReflectionMethod( $class, '_set_protected_vars' );
            $reflections->setAccessible( true );
            $this->reflections[ $class ] = $reflections;
        }
    }


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
        if( is_string( $entity ) && is_array( $interfaces ) && in_array( 'WScore\DataMapper\Entity_Interface', $interfaces ) ) {
            return true;
        }
        return false;
    }

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