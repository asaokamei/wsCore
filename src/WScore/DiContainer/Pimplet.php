<?php
namespace WScore\DiContainer;

class Pimplet
{
    private $values = array();

    /**
     * constructor. do nothing particular. 
     */
    public function __construct() {}
    
    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as \Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same a name as an existing parameter would break your container).
     *
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a \Closure to defined an object
     */
    public function set( $id, $value )
    {
        if( is_string( $id ) ) $this->values[$id] = $value;
    }

    /**
     * Gets a value for an id.
     * 
     * @param $id
     * @return null
     */
    public function get( $id )
    {
        if( $this->exists( $id ) ) return $this->values[ $id ];
        return null;
    }

    /**
     * check if id is set.
     *
     * @param $id
     * @return bool
     */
    public function exists( $id ) {
        return is_string( $id ) && array_key_exists( $id, $this->values );
    }

    /**
     * Protects a callable from being interpreted as a service. This is useful
     * when you want to store a callable or a class name as a parameter.
     *
     * @param  mixed $value  value to protect from being evaluated
     * @return mixed         The protected value
     */
    public function protect( $value )
    {
        if( $this->exists( $value ) ) {
            $value = $this->get( $value );
        }
        $this->set( $value, function () use ($value) {
            return $value;
        } );
    }

    /**
     * from Pimple!
     * Returns a \Closure that stores the result of the given \Closure for
     * uniqueness in the scope of this instance of Pimple.
     *
     * @param \Closure $callable A \Closure to wrap for uniqueness
     * @return \Closure The wrapped \Closure
     */
    public function share( \Closure $callable )
    {
        return function ($c) use ($callable) {
            static $object;
            if (null === $object) {
                $object = $callable($c);
            }
            return $object;
        };
    }

    /**
     * from Pimple!
     * Gets a \Closure returning an object for id.
     *
     * @param Dimplet $c
     * @param string $id   The unique identifier for the parameter or object
     * @param string $by   get or fresh. indicates how you obtain the id.
     * @return mixed The   value of the parameter or the \Closure defining an object
     */
    public function raw( $c, $id, $by='get' )
    {
        if( array_key_exists( $id, $this->values ) &&
            $this->values[ $id ] instanceof \Closure ) {
            return $this->values[ $id ];
        }
        return function() use( $c, $id, $by ) {
            /** @var $c Dimplet */
            return $c->$by( $id );
        };
    }

    /**
     * @param \Closure$callable1
     * @param \Closure $callable2
     * @return callable
     */
    public function makeExtend( $callable1, $callable2 )
    {
        return function( $obj ) use( $callable1, $callable2 ) {
            $obj = $callable2( $obj );
            return $callable1 ( $obj );
        };
    }
}