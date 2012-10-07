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

    /** @var array      restricted keys in properties  */
    protected $restricted = array();

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

    /**
     * prepares restricted properties. 
     */
    public function prepare()
    {
        array_push( $this->restricted, $this->id_name );
        // TODO: add relations as well. 
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
        $values = $this->restrict( $values );
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
            ->id( $id )->limit(1)->makeDelete()->exec();
    }

    /**
     * @param array $values
     * @return string                 id of the inserted data
     */
    public function insertId( $values )
    {
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
                $selector = $this->selector();
                $arg2     = ( isset( $info[2] ) ) ? $info[2] : null;
                $arg3     = ( isset( $info[3] ) ) ? $info[3] : null;
                $sel = $selector->getInstance( $info[1], $var_name, $arg2, $arg3 );
            }
            else {
                $class = $info[0];
                $arg1     = ( isset( $info[1][0] ) ) ? $info[1][0] : null;
                $arg2     = ( isset( $info[1][1] ) ) ? $info[1][1] : null;
                $arg3     = ( isset( $info[1][2] ) ) ? $info[1][2] : null;
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
            $type   = $info[0];
            $filter = ( isset( $info[1] ) ) ? $info[1] : '';
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
            if( !array_key_exists( $key, $this->properties ) ||
                in_array( $key, $this->restricted ) ) {
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