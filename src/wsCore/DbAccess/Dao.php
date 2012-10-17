<?php
namespace wsCore\DbAccess;

/**
 * base class for dao's for database tables.
 * a Table Data Gateway pattern.
 * 
 */
class Dao
{
    /** @var string     name of database table     */
    protected $table;

    /** @var string     name of primary key        */
    protected $id_name;

    /** @var array      define property and data type  */
    protected $definition = array();
    
    /** @var array      property names as key => name  */
    protected $properties = array();
    
    /** 
     * store data types for each properties as in 
     * prepared statement's bindValue as key => type
     * 
     * for special case, 
     *    !created_at => key
     *    !updated_at => key
     * 
     * @var array      data type for prepared sql     
     */
    protected $dataTypes  = array();

    /** @var array     relations settings              */
    protected $relations  = array();
    
    /** @var array      protected properties           */
    protected $protected  = array();

    /** @var array      for selector construction      */
    protected $selectors  = array();

    /** @var array      for validation of inputs       */
    protected $validators = array();

    /** @var Query */
    protected $query;

    /** @var \wsCore\Html\Selector|\Closure */
    protected $selectorObj;

    /** @var \wsCore\DbAccess\DataRecord */
    protected $recordClassName = 'wsCore\DbAccess\DataRecord';

    /** @var array|Dao  */
    static $daoObjects = array();

    /** @var string       at where Dao classes exist  */
    static $daoDirectory = NULL;
    // +----------------------------------------------------------------------+
    //  Managing Object and Instances. 
    // +----------------------------------------------------------------------+
    /**
     * @param $query Query
     * @param $selector \wsCore\DiContainer\Dimplet
     * @DimInjection Fresh    Query
     * @DimInjection Get Raw  Selector
     */
    public function __construct( $query, $selector )
    {
        $this->query = $query;
        $this->query->setFetchMode( \PDO::FETCH_CLASS, $this->recordClassName, array( $this ) );
        $this->selectorObj= $selector;
        $this->prepare();
        // simple object pooling. 
        $class = $this->makeModelName( get_called_class() );
        static::$daoObjects[ $class ] = $this;
    }

    /**
     * @param string $dir
     */
    static public function setDaoDirectory( $dir ) {
        static::$daoDirectory = $dir;
    }

    /**
     * @param string $model
     * @throws \RuntimeException
     * @return Dao
     */
    public function getInstance( $model ) {
        if( isset( static::$daoObjects[ $model ] ) ) {
            return static::$daoObjects[ $model ];
        }
        throw new \RuntimeException( "instance of {$model} not set" );
    }
    /**
     * prepares restricted properties. 
     * TODO: improve {created|updated}_at implementation. 
     */
    public function prepare()
    {
        if( !empty( $this->definition ) ) {
            foreach( $this->definition as $key => $info ) {
                $this->properties[ $key ] = $info[0];
                $this->dataTypes[  $key ] = $info[1];
                if( isset( $info[2] ) ) {
                    $this->dataTypes[ '!' . $info[2] ] = $key;
                }
            }
        }
        array_push( $this->protected, $this->id_name );
        // TODO: add relations as well. 
    }
    /**
     * @return \wsCore\DbAccess\Query
     */
    public function query() {
        $this->query->setFetchMode( \PDO::FETCH_CLASS, $this->recordClassName, array( $this ) );
        return $this->query->table( $this->table, $this->id_name );
    }

    /**
     * @return DataRecord
     */
    public function getRecord() {
        /** @var $record \wsCore\DbAccess\DataRecord */
        $record = new $this->recordClassName();
        $record->setDao( $this );
        return $record;
    }

    /**
     * @return \wsCore\Html\Selector
     */
    public function selector() {
        /** @noinspection PhpUndefinedMethodInspection */
        if( $this->selectorObj instanceof \Closure ) {
            $selector = $this->selectorObj;
            $retSel   = $selector();
        }
        else {
            $retSel   = $this->selectorObj;
        }
        return $retSel;
    }
    // +----------------------------------------------------------------------+
    //  Basic DataBase Access.
    // +----------------------------------------------------------------------+
    /**
     * @param string $id
     * @return DataRecord
     */
    public function find( $id ) {
        $record = $this->query()
            ->id( $id )->limit(1)->select();
        $record = $record[0];
        /** @var $record DataRecord */
        $record->resetId();
        return $record;
    }

    /**
     * update data of primary key of $id.
     *
     * @param string $id
     * @param array $values
     * @return Dao
     */
    public function update( $id, $values )
    {
        $values = $this->protect( $values );
        if( isset( $values[ $this->id_name ] ) ) {
            unset( $values[ $this->id_name ] );
        }
        if( isset( $this->dataTypes[ '!updated_at' ] ) ) {
            $values[ $this->dataTypes[ '!updated_at' ] ] = date( 'Y-m-d H:i:s' );
        }
        $this->query()->id( $id )->update( $values );
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
        $values = $this->protect( $values );
        if( isset( $this->dataTypes[ '!updated_at' ] ) ) {
            $values[ $this->dataTypes[ '!updated_at' ] ] = date( 'Y-m-d H:i:s' );
        }
        if( isset( $this->dataTypes[ '!created_at' ] ) ) {
            $values[ $this->dataTypes[ '!created_at' ] ] = date( 'Y-m-d H:i:s' );
        }
        $this->query()->insert( $values );
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
        if( isset( $values[ $this->id_name ] ) ) {
            unset( $values[ $this->id_name ] );
        }
        $this->insertValue( $values );
        $id = $this->query->lastId();
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
     * @param string $type
     * @param string $var_name
     * @param mixed  $value
     * @return mixed
     */
    public function popHtml( $type, $var_name, $value=NULL )
    {
        $sel = $this->getSelInstance( $var_name );
        $val = $sel->popHtml( $type, $value );
        return $val;
    }
    /**
     * @param string $var_name
     * @return null|object
     */
    public function getSelInstance( $var_name )
    {
        static $selInstances = array();
        $self = get_called_class();
        if( isset( $selInstances[ $self ][ $var_name ] ) ) {
            return $selInstances[ $self ][ $var_name ];
        }
        return $selInstances[ $self ][ $var_name ] = $this->getSelector( $var_name );
    }

    /**
     * creates selector object based on selectors array.
     * $selector[ var_name ] = [
     *     className,
     *     styleName,
     *     [ arg2, arg3, arg4 ],
     *     function( &$val ){ doSomething( $val ); },
     *   ]
     *
     * @param string $var_name
     * @return null|object
     */
    public function getSelector( $var_name )
    {
        $sel = NULL;
        if( isset( $this->selectors[ $var_name ] ) ) {
            $info  = $this->selectors[ $var_name ];
            if( $info[0] == 'Selector' ) {
                $selector = $this->selector();
                $arg2     = $this->arrGet( $info, 2, NULL );
                $arg3     = $this->arrGet( $info, 3, NULL );
                $sel = $selector->getInstance( $info[1], $var_name, $arg2, $arg3 );
            }
            else {
                $class = $info[0];
                $arg1     = $this->arrGet( $info[1], 0, NULL );
                $arg2     = $this->arrGet( $info[1], 1, NULL );
                $arg3     = $this->arrGet( $info[1], 2, NULL );
                $sel = new $class( $var_name, $arg1, $arg2, $arg3 );
            }
        }
        return $sel;
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
     * protect values: only the keys in the property list.
     * 
     * @param array $values
     * @return array
     */
    public function protect( $values )
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
     * name of primary key. 
     * 
     * @return string
     */
    public function getIdName() {
        return $this->id_name;
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
        return get_called_class();
    }
    
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
        if( array_key_exists( $key, $arr ) ) {
            return $arr[ $key ];
        }
        return $default;
    }
    
    public function getRelationInfo() {
        return $this->relations;
    }
    // +----------------------------------------------------------------------+
}