<?php
namespace wsCore\DbAccess;

/**
 * base class for dao's for database tables.
 * a Table Data Gateway pattern.
 */
class Dao
{
    /** @var string     name of database table     */
    protected $table;

    /** @var string     name of primary key        */
    protected $id_name;

    /** @var array      property names as key => name  */
    protected $properties = array();

    /** @var array      accessible properties          */
    protected $accessibles = array();
    
    /** @var array      restricted keys in properties  */
    protected $restricts  = array();

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
    }

    public function prepare()
    {
        if( empty( $this->properties ) ) return;
        foreach( $this->properties as $name => $val ) {
            if( $name == $this->id_name ) continue;
            // TODO: skip relations as well. 
            array_push( $this->accessibles, $name );
        }
    }
    /**
     * @return \wsCore\DbAccess\Query
     */
    public function query() {
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
        return ( $this->selectorObj instanceof \Closure ) ? $this->selectorObj() : $this->selectorObj;
    }
    // +----------------------------------------------------------------------+
    //  Basic DataBase Access.
    // +----------------------------------------------------------------------+
    /**
     * @param string $id
     * @return \PdoStatement
     */
    public function find( $id ) {
        return $this->query()
            ->where( $this->id_name, $id )->limit(1)->select()->fetchRow();
    }

    /**
     * update data of primary key of $id.
     *
     * @param string $id
     * @param array $values
     * @return \PdoStatement
     */
    public function update( $id, $values )
    {
        if( isset( $values[ $this->id_name ] ) ) unset(  $values[ $this->id_name ] );
        $values = $this->restrict( $values );
        return $this->query()->where( $this->id_name, $id )->update( $values );
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
        $this->query()->insert( $values );
        if( isset( $values[ $this->id_name ] ) ) {
            $id = $values[ $this->id_name ];
        }
        else {
            $id = TRUE;
        }
        return $id;
    }

    /**
     * @param string $id
     * @return \PdoStatement
     */
    public function delete( $id )
    {
        return $this->query()->clearWhere()
            ->where( $this->id_name, $id )->limit(1)->makeDelete()->exec();
    }

    /**
     * @param array $values
     * @return string                 id of the inserted data
     */
    public function insertId( $values )
    {
        if( isset( $values[ $this->id_name ] ) ) { unset(  $values[ $this->id_name ] ); }
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
        $val = $sel->show( $type, $value );
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
                $sel = $this->selector()->getInstance( $info[1], $var_name, $info[2], $info[3] );
            }
            else {
                $class = $info[0];
                $sel = new $class( $var_name, $info[2][0], $info[2][1], $info[2][2] );
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
     *     type  => dataType or methodName,
     *     args  => [ arg2, arg3, arg4...],
     *   ]
     * @param \wsCore\Validator\DataIo $dio
     * @param $var_name
     * @return mixed|null
     */
    public function validate( $dio, $var_name )
    {
        $return = NULL;
        if( isset( $this->validators[ $var_name ] ) ) {
            $info   = $this->validators[ $var_name ];
            $method = $info[ 'type' ];
            $args   = $info[ 'args' ];
            $return = $dio->validate( $var_name, $method, $args );
        }
        return $return;
    }

    /**
     * returns name of property, if set.
     *
     * @param $var_name
     * @return null
     */
    public function propertyName( $var_name )
    {
        return ( isset( $this->properties[ $var_name ] ) )?
            $this->properties[ $var_name ]: NULL;
    }

    /**
     * restrict values to only the defined keys.
     * uses $this->restricts or $this->properties
     *
     * @param array $values
     * @return array
     */
    public function restrict( $values )
    {
        if( empty( $values ) ) return $values;
        foreach( $values as $key => $val ) {
            if( !in_array( $key, $this->accessibles ) ||
                in_array( $key, $this->restricts ) ) {
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
     * name of the model: i.e. class name. 
     * @return string
     */
    public function getModelName() {
        return get_called_class();
    }
    // +----------------------------------------------------------------------+
}