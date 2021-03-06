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
    /** @var \WScore\DiContainer\Pimplet  */
    private $values = '\WScore\DiContainer\Pimplet';
    
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
     * @param Pimplet  $pimplet
     * @param Forge    $forge
     * @DimInjection Get \WScore\DiContainer\Pimplet
     * @DimInjection Get \WScore\DiContainer\Forge
     */
    public function __construct( $pimplet=null, $forge=null ) {
        $this->values = $pimplet ?: new $this->values;
        $this->forge = $forge ?: new $this->forge;
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
        $this->values->set( $id, $value );
        if( isset( $option ) ) $this->setOption( $id, $option );
    }

    /**
     * sets an option for an id.
     *
     * @param $id
     * @param $option
     */
    public function setOption( $id, $option ) {
        $this->options[ $id ] = Utils::normalizeOption( $option );
    }

    /**
     * check if id is set. 
     * 
     * @param $id
     * @return bool
     */
    public function exists( $id ) {
        return $this->values->exists( $id );
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
     * @return mixed
     */
    public function fresh( $id, $option=array() )
    {
        // if $id is not set at all, return $id itself.
        $found = $id;
        if( $this->values->exists( $id ) ) { // found it in the values.
            $found = $this->values->get( $id );
        }
        // check if $found is a closure, or a className to construct.
        if( $found instanceof \Closure ) {
            $found = $found( $this );
        }
        elseif( Utils::isClassName( $found ) ) {
            $option = Utils::normalizeOption( $option );
            if( isset( $this->options[$id] ) ) { // prepare options
                $option = Utils::mergeOption( $this->options[$id], $option );
            }
            $found = $this->forge->construct( $this, $found, $option );
        }
        // extend the found value if extend is set.
        if( array_key_exists( $id, $this->extends ) ) {
            $extender = $this->extends[ $id ];
            $found = $extender( $found, $this );
        }
        return $found;
    }

    /**
     * Returns a \Closure that stores the result of the given \Closure for
     * uniqueness in the scope of this instance of Pimple.
     *
     * @param \Closure $callable A \Closure to wrap for uniqueness
     * @return \Closure The wrapped \Closure
     */
    public function share( \Closure $callable )
    {
        return $this->values->share( $callable );
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
        return $this->values->protect( $value );
    }

    /**
     * Gets a \Closure returning an object for id.
     *
     * @param string $id   The unique identifier for the parameter or object
     * @param string $by   get or fresh. indicates how you obtain the id. 
     * @return mixed The   value of the parameter or the \Closure defining an object
     */
    public function raw( $id, $by='get' )
    {
        return $this->values->raw( $this, $id, $by );
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
            $this->extends[$id] = $this->values->makeExtend( $callable, $this->extends[ $id ] );
        }
        else {
            $this->extends[$id] = $callable;
        }
    }
}
