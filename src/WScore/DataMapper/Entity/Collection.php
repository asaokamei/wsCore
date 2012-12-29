<?php
namespace WScore\DataMapper;

class Entity_Collection implements \ArrayAccess, \Iterator, \Countable
{
    /** @var Entity_Interface[]  */
    public $_elements = array();
    
    /** @var array   $binds[ propertyName ] = $value */
    public $binds = array();

    // +----------------------------------------------------------------------+
    public function __construct()
    {
    }

    /**
     * @param Entity_Interface[] $entities
     * @return Entity_Collection
     */
    public function collection( $entities = array() )
    {
        $collection            = new static();
        foreach( $entities as $entity ) {
            $collection->_elements[ $entity->_get_cenaId() ] = $entity;
        }
        return $collection;
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface $entity
     */
    public function add( $entity )
    {
        $this->bindEntity( $entity );
        $cenaId = $entity->_get_cenaId();
        if( !$this->offsetExists( $cenaId ) ) {
            $this->offsetSet( $cenaId, $entity );
        }
    }

    /**
     * clears the collection. 
     */
    public function clear() {
        $this->_elements = array();
    }
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @param string $value
     */
    public function set( $name, $value )
    {
        if( empty( $this->_elements ) ) return;
        foreach( $this->_elements as $entity ) {
            $entity[ $name ] = $value;
        }
    }
    /**
     * specify bind condition. 
     * 
     * @param string $name
     * @param string $value
     */
    public function bind( $name, $value )
    {
        $this->binds[ $name ] = $value;
        foreach( $this->_elements as $entity ) {
            $this->bindEntity( $entity );
        }
    }

    /**
     * binds an entity. i.e. sets certain value for a property. 
     * 
     * @param \WScore\DataMapper\Entity_Interface $entity
     */
    public function bindEntity( $entity )
    {
        if( empty( $this->binds ) ) return;
        foreach( $this->binds as $prop => $val ) {
            $entity[ $prop ] = $val;
        }
    }
    
    /**
     * @param string     $model
     * @param array|string $values
     * @param string|null $column
     * @return \WScore\DataMapper\Entity_Collection
     */
    public function fetch( $model, $values, $column=null )
    {
        if( !is_array( $values ) ) $values = array( $values );
        $result = array();
        foreach( $this->_elements as $cenaId => $entity )
        {
            if( $model && $model !== $entity->_get_Model() ) continue;
            if( !$column ) {
                $prop = $entity->_get_id();
            }
            else {
                $prop = $entity[ $column ];
            }
            if( in_array( $prop, $values ) ) $result[] = $entity;
        }
        return $this->collection( $result );
    }
    
    /**
     * extracts values for selected column(s) and packs into an array.
     * 
     * @param string|array  $select
     * @return array
     */
    public function pack( $select )
    {
        $result = array();
        if( empty( $this->_elements ) ) return $result;
        foreach( $this->_elements as $rec ) {
            if( !is_array( $select ) ) {
                $result[] = $this->arrGet( $rec, $select );
            }
            else {
                $pack = array();
                foreach( $select as $item ) {
                    $pack[] = $this->arrGet( $rec, $item );
                }
                $result[] = $pack;
            }
        }
        $result = array_values( $result );
        return $result;
    }
    
    // +----------------------------------------------------------------------+
    /**
     * @param array $arr
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function arrGet( $arr, $key, $default=null ) {
        if( is_array( $arr ) && array_key_exists( $key, $arr ) ) {
            return $arr[ $key ];
        }
        elseif( is_object( $arr ) && isset( $arr->$key ) ) {
            return $arr->$key;
        }
        return $default;
    }

    // +----------------------------------------------------------------------+
    //  for ArrayAccess and Iterator. 
    // +----------------------------------------------------------------------+
    /**
     * @return mixed
     */
    public function getNext() {
        return next( $this->_elements );
    }

    /**
     * @return mixed
     */
    public function first() {
        return reset( $this->_elements );
    }
    
    /**
     * Return the current element
     *
     * @return mixed Can return any type.
     */
    public function current() {
        return current( $this->_elements );
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     */
    public function next() {
        next( $this->_elements );
    }

    /**
     * Return the key of the current element
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key() {
        return key( $this->_elements );
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean 
     */
    public function valid() {
        $key = key( $this->_elements );
        return ( $key !== null && $key !== false );
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void Any returned value is ignored.
     */
    public function rewind() {
        reset( $this->_elements );
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     * @return boolean true on success or false on failure.
     */
    public function offsetExists( $offset ) {
        return array_key_exists( $offset, $this->_elements );
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset ) {
        return $this->offsetExists( $offset ) ? $this->_elements[ $offset ]: null;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet( $offset, $value ) {
        $this->_elements[ $offset ] = $value;
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset( $offset ) {
        if ( $this->offsetExists( $offset ) ) unset( $this->_elements[ $offset ] );
    }

    /**
     * Count elements of an object
     *
     * @return int The custom count as an integer.
     */
    public function count() {
        return count( $this->_elements );
    }
}