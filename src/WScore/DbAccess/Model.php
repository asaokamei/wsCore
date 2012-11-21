<?php
namespace WScore\DbAccess;

/**
 * base class for dao's for database tables.
 * a Table Data Gateway pattern.
 * 
 */
class Model
{
    /** @var EntityManager                   manager for model/entity        */
    protected $em;
    
    /** @var string                          name of database table          */
    protected $table;

    /** @var string                          name of primary key             */
    protected $id_name;

    /**
     * define property and data type. from this data,
     * properties, extraTypes and dataTypes are generated.
     * definition = array(
     *   column => [ name, data_type, extra_info ],
     * )
     *
     * @var array
     */
    protected $definition = array();
    
    /** @var array                           property names as key => name   */
    protected $properties = array();

    /**
     * extra information on property.
     *    extraTypes = array(
     *      type => column name,
     *    );
     * where types are:
     *   - created_at: adds timestamps at insert.
     *   - updated_at: adds timestamps at update.
     *   - primaryKey: specifies primary key(s).
     *
     * @var array
     */
    protected $extraTypes = array();
    
    /** 
     * store data types for each properties as in 
     * prepared statement's bindValue as key => type
     * 
     * for special case, 
     *    !created_at => key
     *    !updated_at => key
     * 
     * @var array   
     */
    protected $dataTypes  = array();

    /** @var array                           relations settings              */
    protected $relations  = array();
    
    /** @var array                           protected properties            */
    protected $protected  = array();

    /**
     * for selector construction. to use with WScore\Html\Selector,
     * $selectors = array(
     *  name => [ 'Selector', style, option text, [
     *      'items' => [ [ val1, str1 ], [ val2, str2 ], ... ],
     *      'filter' => function(){}
     *  ] ],
     * )
     *
     * @var array                                  */
    protected $selectors  = array();

    /** @var array                           for validation of inputs        */
    protected $validators = array();

    /** @var Query                                                           */
    protected $query;

    /** @var \WScore\DbAccess\Entity_Interface    return class from Pdo            */
    public $recordClassName = 'WScore\DbAccess\Entity_Generic';

    /** @var null|string    entity class name for quick methods (find/fetch). */
    protected $entityClass = null;
    // +----------------------------------------------------------------------+
    //  Managing Object and Instances. 
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\EntityManager $em
     * @param \WScore\DbAccess\Query         $query
     * @DimInjection   Get      EntityManager
     * @DimInjection   Fresh    Query
     */
    public function __construct( $em, $query )
    {
        $this->em    = $em;
        $this->query = $query;
        $this->query->setFetchMode( \PDO::FETCH_CLASS, $this->recordClassName, array( $this, 'get' ) );
        $this->prepare();
        $em->registerModel( $this );
    }

    /**
     * prepares restricted properties. 
     */
    public function prepare()
    {
        // create properties and dataTypes from definition.
        if( !empty( $this->definition ) ) {
            foreach( $this->definition as $key => $info ) {
                $this->properties[ $key ] = $info[0];
                $this->dataTypes[  $key ] = $info[1];
                if( isset( $info[2] ) ) {
                    $this->extraTypes[ $info[2] ][] = $key;
                }
            }
        }
        // set up primaryKey if id_name is set.
        if( isset( $this->id_name ) ) {
            $this->extraTypes[ 'primaryKey' ][] = $this->id_name;
        }
        // protect some properties in extraTypes.
        foreach( $this->extraTypes as $type => $list ) {
            if( in_array( $type, array( 'primaryKey', 'created_at', 'updated_at' ) ) ) {
                foreach( $list as $key ) {
                    array_push( $this->protected, $key );
                }
            }
        }
        // protect properties used for relation.
        if( !empty( $this->relations ) ) {
            foreach( $this->relations as $relInfo ) {
                if( $relInfo[ 'relation_type' ] == 'HasOne' ) {
                    $column = ( $relInfo[ 'source_column' ] ) ?: $this->id_name;
                    array_push( $this->protected, $column );
                }
            }
        }
    }

    /**
     * restrict keys in the property list.
     *
     * @param array $values
     * @return array
     */
    public function restrict( $values )
    {
        if( empty( $values ) ) return $values;
        foreach( $values as $key => $val ) {
            if( !array_key_exists( $key, $this->properties ) ) {
                unset( $values[ $key ] );
            }
        }
        return $values;
    }

    /**
     * protect data not to overwrite id or relation fields.
     *
     * @param $values
     * @param array $onlyTo
     * @return mixed
     */
    public function protect( $values, $onlyTo=array() )
    {
        if( empty( $values ) ) return $values;
        foreach( $values as $key => $val ) {
            if( in_array( $key, $this->protected ) ) {
                unset( $values[ $key ] );
            }
            elseif( !empty( $onlyTo ) && !in_array( $key, $onlyTo ) ) {
                unset( $values[ $key ] );
            }
        }
        return $values;
    }

    /**
     * @param null|string $class   entity class name.
     * @return \WScore\DbAccess\Query
     */
    public function query( $class=null ) {
        if( !$class ) $class = $this->recordClassName;
        $this->query->setFetchMode( \PDO::FETCH_CLASS, $class, array( $this, 'get' ) );
        return $this->query->table( $this->table, $this->id_name );
    }

    /**
     * set entity class for quick methods (find/fetch).
     * 
     * @param $class
     */
    public function setEntityClass( $class ) {
        $this->entityClass = $class;
    }
    /**
     * @param array $data
     * @return \WScore\DbAccess\Entity_Interface
     */
    public function getRecord( $data=array() ) 
    {
        /** @var $record \WScore\DbAccess\Entity_Interface */
        $class = ( $this->entityClass ) ?: $this->recordClassName;
        $record = new $class( $this, Entity_Interface::_ENTITY_TYPE_NEW_ );
        $this->entityClass = null;
        if( !empty( $data ) ) {
            foreach( $data as $key => $val ) {
                $record->$key = $val;
            }
        }
        return $record;
    }

    // +----------------------------------------------------------------------+
    //  Basic DataBase Access.
    // +----------------------------------------------------------------------+
    /**
     * @param string $id
     * @return Entity_Interface
     */
    public function find( $id ) {
        $record = $this->query( $this->entityClass )
            ->id( $id )->limit(1)->select();
        $this->entityClass = null;
        if( $record ) $record = $record[0];
        /** @var $record Entity_Interface */
        return $record;
    }

    /**
     * fetches entities from simple condition.
     * use $select to specify column name to get only the column you want.
     *
     * @param string|array $value
     * @param null         $column
     * @param bool         $select
     * @return array|\WScore\DbAccess\Entity_Interface[]
     */
    public function fetch( $value, $column=null, $select=false )
    {
        $query = $this->query( $this->entityClass );
        $this->entityClass = null;
        if( !$column ) $column = $this->getIdName();
        $query->w( $column );

        if( is_array( $value ) ) {
            $query->in( $value );
        } else {
            $query->eq( $value );
        }
        if( $select ) {
            if( $select === true ) {
                $query->column( $column );
                $select = $column;
            }
            else {
                $query->column( $select );
            }
        }
        $record = $query->select();
        if( $select ) {
            $result = array();
            if( !empty( $record ) )
                foreach( $record as $rec ) {
                    $result[] = $rec[ $select ];
                }
            return $result;
        }
        return $record;
    }
    /**
     * update data of primary key of $id.
     * TODO: another method to update entity without $id argument?
     *
     * @param string $id
     * @param Entity_Interface|array   $values
     * @return Model
     */
    public function update( $id, $values )
    {
        $values = $this->restrict( $values );
        unset( $values[ $this->id_name ] );
        if( isset( $this->extraTypes[ 'updated_at' ] ) ) {
            foreach( $this->extraTypes[ 'updated_at' ] as $column ) {
                $values[ $column ] = date( 'Y-m-d H:i:s' );
            }
        }
        $data = $this->entityToArray( $values );
        $this->query()->id( $id )->update( $data );
        return $this;
    }

    /**
     * insert data into database. 
     *
     * @param Entity_Interface|array   $values
     * @return string|bool             id of the inserted data or true if id not exist.
     */
    public function insertValue( $values )
    {
        $values = $this->restrict( $values );
        if( isset( $this->extraTypes[ 'updated_at' ] ) ) {
            foreach( $this->extraTypes[ 'updated_at' ] as $column ) {
                $values[ $column ] = date( 'Y-m-d H:i:s' );
            }
        }
        if( isset( $this->extraTypes[ 'created_at' ] ) ) {
            foreach( $this->extraTypes[ 'created_at' ] as $column ) {
                $values[ $column ] = date( 'Y-m-d H:i:s' );
            }
        }
        $data = $this->entityToArray( $values );
        $this->query()->insert( $data );
        $id = $this->arrGet( $values, $this->id_name, true );
        return $id;
    }

    /**
     * @param string $id
     * @return \PdoStatement
     */
    public function delete( $id )
    {
        return $this->query()->clearWhere()
            ->id( $id )->limit(1)->makeDelete()->exec();
    }

    /**
     * @param Entity_Interface|array   $values
     * @return string                 id of the inserted data
     */
    public function insertId( $values )
    {
        unset( $values[ $this->id_name ] );
        $this->insertValue( $values );
        $id = $this->query->lastId();
        $values[ $this->id_name ] = $id;
        return $id;
    }

    /**
     * inserts a data. select insertId or insertValue to use.
     * default is to insertId.
     *
     * @param Entity_Interface|array  $values
     * @return string                 id of the inserted data
     */
    public function insert( $values )
    {
        return $this->insertId( $values );
    }
    // +----------------------------------------------------------------------+
    //  Managing information about selector, validator, and property list.
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @return null|array
     */
    public function getSelectInfo( $name ) {
        return $this->arrGet( $this->selectors, $name );
    }

    /**
     * @param string $name
     * @return null|array
     */
    public function getValidateInfo( $name ) {
        return $this->arrGet( $this->validators, $name );
    }

    /**
     * @param null|string $name
     * @return array
     */
    public function getPropertyList( $name=null ) {
        $list = $this->protect( $this->properties );
        return $list;
    }
    // +----------------------------------------------------------------------+
    //  Managing Validation and Properties. 
    // +----------------------------------------------------------------------+
    /**
     * returns name of property, if set.
     *
     * @param $var_name
     * @return null
     */
    public function propertyName( $var_name )
    {
        return $this->arrGet( $this->properties, $var_name , null );
    }

    /**
     * name of primary key. 
     * 
     * @return string
     */
    public function getIdName() {
        return $this->id_name;
    }

    /**
     * @param \WScore\DbAccess\Entity_Interface $entity
     * @return null|string
     */
    public function getId( $entity ) {
        $idName = $this->id_name;
        $id = ( isset( $entity->$idName ) ) ? $entity->$idName: null;
        return $id;
    }

    /**
     * name of the model: i.e. class name. 
     * @return string
     */
    public function getModelName() {
        return get_called_class();
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->table;
    }
    
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

    /**
     * @param array|Entity_Interface $entity
     * @return array
     */
    public function entityToArray( $entity ) {
        if( is_object( $entity ) ) {
            return get_object_vars( $entity );
        }
        return $entity;
    }

    /**
     * @param \WScore\DbAccess\Entity_Interface $source
     * @param string $name
     * @return Relation_Interface
     */
    public function relation( $source, $name )
    {
        if( !$relation = $source->relation( $name ) ) {
            $relation = Relation::getRelation( $this->em, $source, $this->relations, $name );
            $source->setRelation( $name, $relation );
        }
        return $relation;
    }
    // +----------------------------------------------------------------------+
}