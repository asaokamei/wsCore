<?php
namespace wsCore\Dba;

/**
 * base class for various dao's for each table.
 * a table data gateway pattern.
 */
class Dao
{
    private $table;
    private $id_name;

    private $properties = array();
    private $selectors  = array();

    private $dba;
    // +----------------------------------------------------------------------+
    public function __construct( $dba=NULL )
    {
        $this->dba = ( is_object( $dba ) )?:
            ( $dba )? new Dba( Pdo::connect( $dba ) ) : new Dba();
    }
    // +----------------------------------------------------------------------+
    /**
     * @param string $id
     * @return \PdoStatement
     */
    public function find( $id ) {
        return $this->dba->sql()
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
        return $this->dba->sql()->clearWhere()
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
        return $this->dba->sql()
            ->table( $this->table )
            ->insert( $values );
    }

    /**
     * @param string $id
     * @return \PdoStatement
     */
    public function removeDataFromTable( $id )
    {
        return $this->dba->sql()->clearWhere()
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
        return $this->dba->lastId();
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
        if( isset( $this->selectors[ $var_name ] ) ) {
            $info  = $this->selectors[ $var_name ];
            $args  = array( $var_name ) + $info[ 'args' ];
            $class = new ReflectionClass( $info[ 'class' ] );
            $sel   = $class->newInstanceArgs( $args );
            if( isset( $info[ 'call' ] ) && is_callable( $info[ 'call' ] ) ) {
                $function = $info[ 'call' ];
                call_user_func( $function, $sel );
            }
        }
        return NULL;
    }
    // +----------------------------------------------------------------------+
}