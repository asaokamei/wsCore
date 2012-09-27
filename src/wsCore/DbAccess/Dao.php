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
    protected $restricts  = array();

    /** @var array      for selector construction      */
    protected $selectors  = array();

    /** @var array      for validation of inputs       */
    protected $validators = array();

    /** @var Query */
    protected $query;

    /** @var \wsCore\DiContainer\Dimplet */
    protected $container;

    /** @var \wsCore\DbAccess\DataRecord */
    protected $recordClassName = '\wsCore\DbAccess\DataRecord';

    // +----------------------------------------------------------------------+
    /**
     * @param $query Query
     * @param $container \wsCore\DiContainer\Dimplet
     * @DimInjection Fresh Query
     * @DimInjection Get   Container
     */
    public function __construct( $query, $container )
    {
        $this->query = $query;
        // TODO FIX: table is stored in Sql which is recreated all the time.
        // TODO: set data types for prepared statement.
        $this->query->setFetchMode( \PDO::FETCH_CLASS, $this->recordClassName, array( $this ) );
        $this->container= $container;
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
        $record->reconstruct( $this );
        return $record;
    }
    // +----------------------------------------------------------------------+
    /**
     * @param string $id
     * @return \PdoStatement
     */
    public function find( $id ) {
        return $this->query()
            ->where( $this->id_name, $id )
            ->limit(1)
            ->exec();
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
        $this->restrict( $values );
        return $this->query()
            ->where( $this->id_name, $id )
            ->update( $values )
        ;
    }

    /**
     * insert data into database. 
     *
     * @param array $values
     * @return string|bool             id of the inserted data or true if id not exist.
     */
    public function insertValue( &$values )
    {
        $this->restrict( $values );
        $this->query()
            ->insert( $values );
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
            ->where( $this->id_name, $id )
            ->limit(1)
            ->makeSQL( 'DELETE' )
            ->exec();
    }

    /**
     * @param array $values
     * @return string                 id of the inserted data
     */
    public function insertId( &$values )
    {
        if( isset( $values[ $this->id_name ] ) ) { unset(  $values[ $this->id_name ] ); }
        $this->insertValue( $values );
        $id = $this->query->lastId();
        if( isset( $values[ $this->id_name ] ) ) { $values[ $this->id_name ] = $id; }
        return $id;
    }

    /**
     * inserts a data. select insertId or insertValue to use.
     * default is to insertId.
     *
     * @param $values
     * @return string                 id of the inserted data
     */
    public function insert( &$values )
    {
        return $this->insertId( $values );
    }
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
     *     class => className,
     *     args  => [ arg2, arg3, arg4 ],
     *     call  => function( &$sel ){ $sel->do_something(); },
     *   ]
     * TODO: use container for construct and singleton!?
     *
     * @param string $var_name
     * @return null|object
     */
    public function getSelector( $var_name )
    {
        $sel = NULL;
        if( isset( $this->selectors[ $var_name ] ) ) {
            $info  = $this->selectors[ $var_name ];
            $args  = array( $var_name ) + $info[ 'args' ];
            $class = new \ReflectionClass( $info[ 'class' ] );
            $sel   = $class->newInstanceArgs( $args );
            if( isset( $info[ 'call' ] ) && is_callable( $info[ 'call' ] ) ) {
                $function = $info[ 'call' ];
                call_user_func( $function, $sel );
            }
        }
        return $sel;
    }

    /**
     * checks input data using pggCheck.
     * $validators[ $var_name ] = [
     *     type  => method_name,
     *     args  => [ arg2, arg3, arg4...],
     *   ]
     * @param $pgg
     * @param $var_name
     * @return mixed|null
     */
    public function checkPgg( $pgg, $var_name )
    {
        $return = NULL;
        if( isset( $this->validators[ $var_name ] ) ) {
            $info   = $this->validators[ $var_name ];
            $method = $info[ 'type' ];
            $args   = $info[ 'args' ];
            $return = call_user_func_array( array( $pgg, $method ), $args );
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
     */
    public function restrict( &$values )
    {
        if( !empty( $values ) )
        foreach( $values as $key => $val ) {
            if( !in_array( $key, $this->restricts ) ||
                !isset( $this->properties[ $key ] ) ) {
                unset( $values[ $key ] );
            }
        }
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