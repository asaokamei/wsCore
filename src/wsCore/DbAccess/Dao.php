<?php
namespace wsCore\DbAccess;

/**
 * base class for dao's for database tables.
 * a Table Data Gateway pattern.
 */
class Dao implements InjectDbaInterface
{
    /** @var string     name of database table     */
    private $table;
    /** @var string     name of primary key        */
    private $id_name;

    /** @var array      property names as key => name  */
    private $properties = array();
    /** @var array      list of accessible keys        */
    private $restricts  = array();
    /** @var array      for selector construction      */
    private $selectors  = array();
    /** @var array      for validation of inputs       */
    private $validators = array();

    /** @var \wsCore\DbAccess\Dba */
    private $dba;
    /** @var string */
    public  $classNameDba = '\wsCore\DbAccess\DbAccess';

    // +----------------------------------------------------------------------+
    /**
     * 
     */
    public function __construct()
    {
    }

    /**
     * injects DbAccess object for accessing database.
     * if the injected $dba is a string, it is used to construct DbAccess object.
     *
     * @param $dba
     */
    public function injectDba( $dba )
    {
        if( is_object( $dba ) ) {
            $this->dba = $dba;
        }
        elseif( is_string( $dba ) ) {
            $this->dba = new $this->classNameDba( $dba );
        }
    }

    /**
     * @return \wsCore\DbAccess\Dba
     */
    public function dba() {
        if( !isset( $this->dba ) ) {
            $this->dba = new $this->classNameDba;
        }
        return $this->dba;
    }
    // +----------------------------------------------------------------------+
    /**
     * @param string $id
     * @return \PdoStatement
     */
    public function find( $id ) {
        return $this->dba()->sql()
            ->table( $this->table )
            ->where( $this->id_name, $id )
            ->limit(1)
            ->exec();
    }

    /**
     * @param string $id
     * @param array $values
     * @return \PdoStatement
     */
    public function update( $id, $values )
    {
        if( isset( $values[ $this->id_name ] ) ) unset(  $values[ $this->id_name ] );
        return $this->dba()->sql()->clearWhere()
            ->table( $this->table )
            ->where( $this->id_name, $id )
            ->update( $values )
        ;
    }

    /**
     * @param string $values
     * @return \PdoStatement
     */
    public function insert( $values )
    {
        return $this->dba()->sql()
            ->table( $this->table )
            ->insert( $values );
    }

    /**
     * @param string $id
     * @return \PdoStatement
     */
    public function removeDataFromTable( $id )
    {
        return $this->dba()->sql()->clearWhere()
            ->table( $this->table )
            ->where( $this->id_name, $id )
            ->limit(1)
            ->makeSQL( 'DELETE' )
            ->exec();
    }

    /**
     * @param string $values
     * @return string                 id of the inserted data
     */
    public function insertId( $values )
    {
        if( isset( $values[ $this->id_name ] ) ) unset(  $values[ $this->id_name ] );
        $this->insert( $values );
        return $this->dba()->lastId();
    }
    // +----------------------------------------------------------------------+
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
    // +----------------------------------------------------------------------+
}