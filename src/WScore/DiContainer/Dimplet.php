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
        if( isset( $option ) ) $this->options[ $id ] = $option;
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
     * @throws \RuntimeException
     * @return mixed
     */
    public function fresh( $id, $option=array() )
    {
        if( isset( $this->options[$id] ) ) {
            $option = Utils::array_merge_recursive_distinct( $this->options[$id], $option );
        }
        if( $this->values->exists( $id ) ) 
        {
            $found = $this->values->get( $id );
            if( $found instanceof \Closure ) {
                $found = $found( $this );
            }
            elseif( Utils::isClassName( $found ) ) {
                $found = $this->construct( $found, $option );
            }
        }
        elseif( Utils::isClassName( $id ) ) {
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
        $injectList = Utils::array_merge_recursive_distinct( $injectList, $option );
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
        return $this->values->share( $callable );
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
        return $this->values->protect( $value );
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
        return $this->values->raw( $id, $by );
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
