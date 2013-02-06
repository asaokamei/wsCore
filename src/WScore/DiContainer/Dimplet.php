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
    /** @var string[]|mixed[]|\Closure[]      */
    private $values = array();
    
    /** @var array */
    private $options = array();

    /** @var array      */
    private $objects = array();
    
    /** @var \Closure[]      */
    private $extends = array();

    /** @var \WScore\DiContainer\Forge */
    private $forge = '\WScore\DiContainer\Forge';

    /** @var \WScore\DiContainer\Dimplet */
    private static $self = null;
    // +----------------------------------------------------------------------+
    /**
     * @param Forge $dimConst
     * @DimInjection Get \WScore\DiContainer\Forge
     */
    public function __construct( $dimConst=null ) {
        $this->forge = $dimConst ?: new $this->forge;
    }

    public static function getInstance( $dimConst=null )
    {
        if( ! self::$self ) {
            self::$self = new static( $dimConst );
        }
        return self::$self;
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
     * @param array  $option  set dependencies. 
     */
    public function set( $id, $value, $option=null )
    {
        $this->values[$id] = $value;
        if( isset( $option ) ) $this->options[ $id ] = $option;
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
     * @param       $id
     * @param array $option
     * @return mixed
     */
    public function get( $id, $option=array() )
    {
        if( array_key_exists( $id, $this->objects ) ) {
            return $this->objects[ $id ];
        }
        $this->objects[ $id ] = $this->fresh( $id, $option );
        return $this->objects[ $id ];
    }

    /**
     * gets _fresh_ id from container. returns freshly constructed objects
     * unless it is wrapped by share method.
     *
     * @param       $id
     * @param array $option
     * @throws \RuntimeException
     * @return mixed
     */
    public function fresh( $id, $option=array() )
    {
        if( isset( $this->options[$id] ) ) {
            $option = $this->array_merge_recursive_distinct( $this->options[$id], $option );
        }
        if( array_key_exists($id, $this->values) ) 
        {
            $found = $this->values[$id];
            if( $found instanceof \Closure ) {
                $found = $found( $this );
            }
            elseif( $this->isClassName( $found ) ) {
                $found = $this->construct( $found, $option );
            }
        }
        elseif( $this->isClassName( $id ) ) {
            $found = $this->construct( $id, $option );
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
     * DI by constructor. uses annotation
     * @DimInjection
     *
     * @param string     $className
     * @param array|null $option
     * @return object
     */
    public function construct( $className, $option=array() )
    {
        $injectList = $this->forge->listDi( $className );
        $injectList = $this->array_merge_recursive_distinct( $injectList, $option );
        foreach( $injectList['construct'] as $key => $injectInfo ) {
            $injectList['construct'][$key] = $this->getObject( $injectInfo );
        }
        $object = $this->forge->forge( $className, $injectList );
        return $object;
    }

    /**
     * @param array $injectInfo
     * @return mixed|null
     */
    private function getObject( $injectInfo )
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

    /**
     * FROM:
     * http://www.php.net/manual/ja/function.array-merge-recursive.php#92195
     * 
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    private function array_merge_recursive_distinct ( array &$array1, array &$array2 )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
            }
            else
            {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }
}
