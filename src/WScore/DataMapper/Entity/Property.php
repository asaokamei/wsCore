<?php
namespace WScore\DataMapper;

class Entity_Property
{
    /** @var \ReflectionMethod[] */
    protected $reflections = array();

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
    private function setup( $entity )
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
}