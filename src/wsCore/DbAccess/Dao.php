<?php
namespace wsCore\DbAccess;

/**
 * base class for dao's for database tables.
 * a Table Data Gateway pattern.
 * 
 */
class Dao
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

    /** @var array                           for selector construction       */
    protected $selectors  = array();

    /** @var array                           for validation of inputs        */
    protected $validators = array();

    /** @var Query                                                           */
    protected $query;

    /** @var \wsCore\DbAccess\Entity_Interface    return class from Pdo            */
    public $recordClassName = 'wsCore\DbAccess\Entity_Generic';

    /** @var array|Dao                                                       */
    static $daoObjects = array();
    // +----------------------------------------------------------------------+
    //  Managing Object and Instances. 
    // +----------------------------------------------------------------------+
    /**
     * @param $em       EntityManager
     * @param $query    Query
     * @DimInjection Get      EntityManager
     * @DimInjection Fresh    Query
     */
    public function __construct( $em, $query )
    {
        $this->em    = $em;
        $this->query = $query;
        $this->query->setFetchMode( \PDO::FETCH_CLASS, $this->recordClassName, array( $this, 'get' ) );
        $this->prepare();
        // simple object pooling. 
        $class = $this->makeModelName( get_called_class() );
        static::$daoObjects[ $class ] = $this;
        $em->registerModel( $this );
    }

    /**
     * @param string $model
     * @throws \RuntimeException
     * @return \wsCore\DbAccess\Dao
     */
    public function getInstance( $model ) {
        if( isset( static::$daoObjects[ $model ] ) ) {
            return static::$daoObjects[ $model ];
        }
        throw new \RuntimeException( "instance of {$model} not set" );
    }
    
    /**
     * prepares restricted properties. 
     */
    public function prepare()
    {
        if( !empty( $this->definition ) ) {
            foreach( $this->definition as $key => $info ) {
                $this->properties[ $key ] = $info[0];
                $this->dataTypes[  $key ] = $info[1];
                if( isset( $info[2] ) ) {
                    $this->extraTypes[ $info[2] ][] = $key;
                }
            }
        }
        if( isset( $this->id_name ) ) {
            array_push( $this->protected, $this->id_name );
            $this->extraTypes[ 'primaryKey' ][] = $this->id_name;
        }
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
     * @return mixed
     */
    public function protect( $values )
    {
        if( empty( $values ) ) return $values;
        foreach( $values as $key => $val ) {
            if( array_key_exists( $key, $this->protected ) ) {
                unset( $values[ $key ] );
            }
        }
        return $values;
    }
    /**
     * @return \wsCore\DbAccess\Query
     */
    public function query() {
        $this->query->setFetchMode( \PDO::FETCH_CLASS, $this->recordClassName, array( $this, 'get' ) );
        return $this->query->table( $this->table, $this->id_name );
    }

    /**
     * @param array $data
     * @return \wsCore\DbAccess\Entity_Interface
     */
    public function getRecord( $data=array() ) 
    {
        /** @var $record \wsCore\DbAccess\Entity_Interface */
        $record = new $this->recordClassName( $this, 'new' );
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
        $record = $this->query()
            ->id( $id )->limit(1)->select();
        $record = $record[0];
        /** @var $record Entity_Interface */
        return $record;
    }

    /**
     * update data of primary key of $id.
     * TODO: another method to update entity without $id argument?
     *
     * @param string $id
     * @param array $values
     * @return Dao
     */
    public function update( $id, $values )
    {
        $values = $this->restrict( $values );
        $values = $this->unsetKey( $values, $this->id_name );
        if( isset( $this->extraTypes[ 'updated_at' ] ) ) {
            foreach( $this->extraTypes[ 'updated_at' ] as $column ) {
                $values = $this->setKey( $values, $column, date( 'Y-m-d H:i:s' ) );
            }
        }
        $data = $this->entityToArray( $values );
        $this->query()->id( $id )->update( $data );
        return $this;
    }

    /**
     * insert data into database. 
     *
     * @param array $values
     * @return string|bool             id of the inserted data or true if id not exist.
     */
    public function insertValue( $values )
    {
        $values = $this->restrict( $values );
        if( isset( $this->extraTypes[ 'updated_at' ] ) ) {
            foreach( $this->extraTypes[ 'updated_at' ] as $column ) {
                $this->setKey( $values, $column, date( 'Y-m-d H:i:s' ) );
            }
        }
        if( isset( $this->extraTypes[ 'created_at' ] ) ) {
            foreach( $this->extraTypes[ 'created_at' ] as $column ) {
                $this->setKey( $values, $column, date( 'Y-m-d H:i:s' ) );
            }
        }
        $data = $this->entityToArray( $values );
        $this->query()->insert( $data );
        $id = $this->arrGet( $values, $this->id_name, TRUE );
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
     * @param array $values
     * @return string                 id of the inserted data
     */
    public function insertId( $values )
    {
        $values = $this->unsetKey( $values, $this->id_name );
        $this->insertValue( $values );
        $id = $this->query->lastId();
        $this->setKey( $values, $this->id_name, $id );
        return $id;
    }

    /**
     * inserts a data. select insertId or insertValue to use.
     * default is to insertId.
     *
     * @param $values
     * @return string                 id of the inserted data
     */
    public function insert( $values )
    {
        return $this->insertId( $values );
    }
    // +----------------------------------------------------------------------+
    //  Managing Selector for Html/Form Output. 
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @return null|array
     */
    public function getSelectInfo( $name ) {
        return array_key_exists( $name, $this->selectors ) ? $this->selectors[ $name ] : NULL;
    }
    // +----------------------------------------------------------------------+
    //  Managing Validation and Properties. 
    // +----------------------------------------------------------------------+
    /**
     * checks input data using pggCheck.
     * $validators[ $var_name ] = [
     *     dataType or methodName,
     *     'filter rules',
     *   ]
     * @param \wsCore\Validator\DataIo $dio
     * @return mixed|null
     */
    public function validate( $dio )
    {
        if( empty( $this->validators ) ) return $this;
        foreach( $this->validators as $var_name => $info )
        {
            $type   = $this->arrGet( $info, 0, NULL );
            $filter = $this->arrGet( $info, 1, '' );
            $dio->push( $var_name, $type, $filter );
        }
        return $this;
    }

    /**
     * returns name of property, if set.
     *
     * @param $var_name
     * @return null
     */
    public function propertyName( $var_name )
    {
        return $this->arrGet( $this->properties, $var_name , NULL );
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
     * @param \wsCore\DbAccess\Entity_Interface $entity
     * @return null|string
     */
    public function getId( $entity ) {
        $idName = $this->id_name;
        $id = ( isset( $entity->$idName ) ) ? $entity->$idName: NULL;
        return $id;
    }
    /**
     * @param string $class
     * @return string
     */
    public function makeModelName( $class ) {
        if( strpos( $class, '\\' ) !== FALSE ) {
            $class = substr( $class, strrpos( $class, '\\' ) + 1 );
        }
        return $class;
    }
    /**
     * name of the model: i.e. class name. 
     * @return string
     */
    public function getModelName() {
        return $this->makeModelName( get_called_class() );
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
    public function arrGet( $arr, $key, $default=NULL ) {
        if( is_array( $arr ) && array_key_exists( $key, $arr ) ) {
            return $arr[ $key ];
        }
        elseif( is_object( $arr ) && isset( $arr->$key ) ) {
            return $arr->$key;
        }
        return $default;
    }
    
    public function unsetKey( $arr, $key ) {
        if( is_object( $arr ) ) {
            $arr->$key = null;
        }
        elseif( is_array( $arr ) ) {
            unset( $arr[ $key ] );
        }
        return $arr;
    }
    
    public function setKey( $arr, $key, $val ) {
        if( is_object( $arr ) ) {
            $arr->$key = $val;
        }
        elseif( is_array( $arr ) ) {
            $arr[ $key ] = $val;
        }
        return $arr;
    }

    public function entityToArray( $entity ) {
        if( !is_array( $entity ) ) {
            $data = get_object_vars( $entity );
        }
        else {
            $data = $entity;
        }
        return $data;
    }

    /**
     * @param \wsCore\DbAccess\Entity_Interface $source
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