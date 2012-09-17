<?php
namespace wsCore\DiContainer;

/*
 * Simple Dependency Injection Manager.
 * an extension of Pimple.
 * - create an object if class name is given as id. 
 * - auto inject dependencies based on interface. 
 * - id will be chained, unless it is protected. 
 */

class Dimplet
{
    /** @var array      */
    private $values = array();

    /** @var array      */
    private $objects = array();
    
    /** @var array      */
    private $extends = array();
    // +----------------------------------------------------------------------+
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
    public function set($id, $value)
    {
        $this->values[$id] = $value;
    }
    
    public function exists( $id ) {
        return array_key_exists( $id, $this->values );
    }

    /**
     * gets id from container. id can be:
     *  - a pre-set id for a data or a factory \Closure.
     *  - a class name to construct.
     * 
     * @param $id
     * @return mixed
     */
    public function get( $id )
    {
        if( array_key_exists( $id, $this->objects ) ) {
            return $this->objects[ $id ];
        }
        $found = $this->fresh( $id );
        $this->objects[ $id ] = $found;
        return $found;
    }
    /**
     * gets _fresh_ id from container. returns freshly constructed objects 
     * unless it is wrapped by share method. 
     *
     * @param $id
     * @return mixed
     * @throws \RuntimeException
     */
    public function fresh( $id )
    {
        if( array_key_exists($id, $this->values) ) {
            $found = $this->values[$id];
            if( $found instanceof \Closure ) {
                $found = $found( $this );
            }
            elseif( $this->exists( $found ) ) {
                $found = $this->get( $found );
            }
            elseif( class_exists( $found ) ) {
                $found = $this->get( $found );
            }
        }
        elseif( class_exists( $id ) ) {
            // construct the class, and inject via interfaces.
            $found = new $id;
            $this->inject( $found );
        }
        else {
            throw new \RuntimeException(sprintf('Identifier "%s" is not defined.', $id));
        }
        if( array_key_exists( $id, $this->extends ) ) {
            $extender = $this->extends[ $id ];
            $extender( $found, $this );
        }
        return $found;
    }

    /**
     * injects object using interfaces.
     *
     * @param $object
     */
    public function inject( $object )
    {
        if( !$interfaces = class_implements( $object ) ) return;
        foreach( $interfaces as $interface ) {
            if( !preg_match( '/^(.*)Inject([_a-zA-Z0-9]+)Interface$/i', $interface, $matches ) ) {
                continue;
            }
            $className = $matches[1] . $matches[2];
            $injector  = "inject" . $matches[2];
            // now inject an object.
            $injObj = $this->get( $className );
            $object->$injector( $injObj );
        }
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

            if (NULL === $object) {
                $object = $callable($c);
            }

            return $object;
        };
    }
    /**
     * from Pimple!
     * Protects a callable from being interpreted as a service. This is useful
     * when you want to store a callable or a class name as a parameter.
     *
     * @param  mixed $value  value to protect from being evaluated
     * @return mixed         The protected value
     */
    public function protect( $value )
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * from Pimple!
     * Gets a parameter or the \Closure defining an object.
     *
     * @param string $id The unique identifier for the parameter or object
     * @return mixed The value of the parameter or the \Closure defining an object
     */
    public function raw($id)
    {
        return array_key_exists($id, $this->values) ? $this->values[$id]: FALSE ;
    }

    /**
     * Extends generated objects.
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string   $id       The unique identifier for the object
     * @param \Closure $callable A \Closure to extend the original
     */
    public function extend($id, \Closure $callable)
    {
        /** @var $factory \Closure */
        $this->extends[$id] = $callable;
    }
}