<?php
namespace WScore\DiContainer;

/*
 * Simple Dependency Injection Manager.
 * an extension of Pimple.
 * - create an object if class name is given as id. 
 * - auto inject dependencies based on interface. 
 * - id will be chained, unless it is protected.
 */

class Dimplet
{
    /** @var array|\Closure[]      */
    private $values = array();

    /** @var array      */
    private $objects = array();
    
    /** @var \Closure[]      */
    private $extends = array();

    /** @var \WScore\DiContainer\DimConstructor */
    private $dimConstructor = '\WScore\DiContainer\DimConstructor';

    private $dimCache = '\WScore\DiContainer\DimCache';
    // +----------------------------------------------------------------------+
    /**
     * @param DimConstructor $dimConst
     * @DimInjection Get \WScore\DiContainer\DimConstructor
     */
    public function __construct( $dimConst=null ) {
        $this->dimConstructor = $dimConst ?: new $this->dimConstructor;
        $this->dimCache = new $this->dimCache;
    }

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

    /**
     * check if id is set. 
     * 
     * @param $id
     * @return bool
     */
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
        $this->objects[ $id ] = $this->fresh( $id );
        return $this->objects[ $id ];
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
        if( array_key_exists($id, $this->values) ) 
        {
            $found = $this->values[$id];
            if( $found instanceof \Closure ) {
                $found = $found( $this );
            }
            elseif( $this->isClassName( $found ) ) {
                $found = $this->construct( $found );
            }
        }
        elseif( $this->isClassName( $id ) ) {
            $found = $this->construct( $id );
        }
        else {
            throw new \RuntimeException(sprintf('Identifier "%s" is not defined.', $id));
        }
        if( array_key_exists( $id, $this->extends ) ) {
            $extender = $this->extends[ $id ];
            $found = $extender( $found, $this );
        }
        return $found;
    }

    /**
     * test if a string maybe a class name, which contains backslash and a-zA-Z0-9.
     * @param mixed $name
     * @return bool
     */
    private function isClassName( $name ) {
        return is_string( $name ) && preg_match( "/^[_a-zA-Z0-9\\\\]*$/", $name ) && class_exists( $name );
    }

    /**
     * DI by constructor. uses annotation @DimInjection
     *
     * @param $className
     * @return object
     */
    private function construct( $className )
    {
        // todo: maybe storing object before running constructor...

        if( $object = $this->dimCache->fetch( $className ) ) {
            return $object;
        }
        $refClass   = new \ReflectionClass( $className );
        $injectList = $this->dimConstructor->getList( $refClass );
        $args = array();
        foreach( $injectList as $injectInfo ) {
            $args[] = $this->forgeObject( $injectInfo );
        }
        $object = $refClass->newInstanceArgs( $args );
        $this->dimCache->store( $className, $object );
        return $object;
    }

    /**
     * @param array $injectInfo
     * @return mixed|null
     */
    private function forgeObject( $injectInfo )
    {
        extract( $injectInfo ); // gets $by, $ob, and $id.
        /** @var $by string   type of object fresh/get   */
        /** @var $ob string   type of construct obj/raw  */
        /** @var $id string   look for id to generate    */
        $object = null;
        if( $by && $ob && $id ) {
            if( $ob == 'raw' ) {
                $object = $this->raw( $id, $by );
            }
            else {
                $object = $this->$by( $id );
            }
        }
        return $object;
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
     * Gets a \Closure returning an object for id.
     *
     * @param string $id   The unique identifier for the parameter or object
     * @param string $by   get or fresh. indicates how you obtain the id. 
     * @return mixed The   value of the parameter or the \Closure defining an object
     */
    public function raw( $id, $by='get' )
    {
        if( array_key_exists( $id, $this->values ) && 
            $this->values[ $id ] instanceof \Closure ) {
            return $this->values[ $id ];
        }
        $c = $this;
        return function() use( $c, $id, $by ) {
            /** @var $c Dimplet */
            return $c->$by( $id );
        };
    }

    /**
     * Extends generated objects.
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     * 
     * $extend = function( $obj, $c ) {
     *   // do whatever with $obj
     *   return $obj;
     * }
     *
     * @param string   $id       The unique identifier for the object
     * @param \Closure $callable A \Closure to extend the original
     */
    public function extend( $id, \Closure $callable )
    {
        /** @var $factory \Closure */
        if( isset( $this->extends[ $id ] ) ) {
            $callable2 = $this->extends[ $id ];
            $this->extends[$id] = function( $obj ) use( $callable, $callable2 ) {
                $obj = $callable2( $obj );
                return $callable ( $obj );
            };
        }
        else {
            $this->extends[$id] = $callable;
        }
    }
}

class DimCache
{
    private $useApc = false;
    private $cached = array();
    private $header = 'DimCache:';
    public function __construct()
    {
        if( function_exists( 'apc_store' ) ) {
            $this->useApc = true;
        }
        $this->useApc = false;
    }
    public function store( $className, $value )
    {
        $className = $this->header . str_replace( '\\', '-', $className );
        if( $this->useApc ) {
            try {
                apc_store( $className, $value );
            } catch( \Exception $e ) {
            }
        } else {
            $this->cached[ $className ] = $value;
        }
    }
    public function fetch( $className )
    {
        $className = $this->header . str_replace( '\\', '-', $className );
        if( $this->useApc ) {
            try {
                $fetched = apc_fetch( $className );
            } catch( \Exception $e ) {
                echo $className;
                echo $e->getMessage();
                exit;
            }
        } else {
            $fetched = array_key_exists( $className, $this->cached ) ? $this->cached[ $className]: false;
        }
        return $fetched;
    }
}