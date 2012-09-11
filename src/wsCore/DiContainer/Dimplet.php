<?php
namespace wsCore\DiContainer;

/*
 * Simple Dependency Injection Manager inspired (?) by Pimple.
 * well, more like copy-and-pasted many codes from the Pimple.
 */

class Dimplet
{
    private $values = array();

    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same a name as an existing parameter would break your container).
     *
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to defined an object
     */
    public function set($id, $value)
    {
        $this->values[$id] = $value;
    }

    /**
     * gets id from container. id can be:
     *  - a pre-set id for a data or a factory closure.
     *  - a class name to construct.
     *
     * @param $id
     * @return mixed
     * @throws \RuntimeException
     */
    public function get( $id )
    {
        if( array_key_exists($id, $this->values) ) {
            $found = $this->values[$id];
            /** @var $found callable */
            $found = ( $found instanceof Closure ) ? $found( $this ) : $found;
        }
        elseif( class_exists( $id ) ) {
            // construct the class, and inject via interfaces.
            $found = new $id;
            $this->inject( $found );
        }
        else {
            throw new \RuntimeException(sprintf('Identifier "%s" is not defined.', $id));
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
        if( $interfaces = class_implements( $object ) )
            foreach( $interfaces as $interface ) {
                if( preg_match( '/^(.*)Inject([_a-zA-Z0-9]+)Interface$/i', $interface, $matches ) )
                {
                    $className = $matches[1] . $matches[2];
                    $injector  = "inject" . $matches[2];
                    // now inject an object.
                    $injObj = $this->get( $className );
                    $object->$injector( $injObj );
                }
            }
    }
    /**
     * from Pimple!
     * Returns a closure that stores the result of the given closure for
     * uniqueness in the scope of this instance of Pimple.
     *
     * @param Closure $callable A closure to wrap for uniqueness
     * @return Closure The wrapped closure
     */
    public function share( Closure $callable )
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
     * Protects a callable from being interpreted as a service.
     * This is useful when you want to store a callable as a parameter.
     *
     * @param Closure $callable A closure to protect from being evaluated
     * @return Closure The protected closure
     */
    public function protect(Closure $callable)
    {
        return function ($c) use ($callable) {
            return $callable;
        };
    }

    /**
     * from Pimple!
     * Gets a parameter or the closure defining an object.
     *
     * @param string $id The unique identifier for the parameter or object
     * @return mixed The value of the parameter or the closure defining an object
     */
    public function raw($id)
    {
        return array_key_exists($id, $this->values) ? $this->values[$id]: FALSE ;
    }

    /**
     * from Pimple!
     * Extends an object definition.
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string  $id       The unique identifier for the object
     * @param Closure $callable A closure to extend the original
     * @throws \RuntimeException
     * @return Closure The wrapped closure
     */
    public function extend($id, Closure $callable)
    {
        if (!array_key_exists($id, $this->values)) {
            throw new \RuntimeException( sprintf('Identifier "%s" is not defined.', $id ) );
        }

        $factory = $this->values[$id];

        if (!($factory instanceof Closure)) {
            throw new \RuntimeException( sprintf('Identifier "%s" does not contain an object definition.', $id ) );
        }

        return $this->values[$id] = function ($c) use ($callable, $factory) {
            return $callable($factory($c), $c);
        };
    }
}