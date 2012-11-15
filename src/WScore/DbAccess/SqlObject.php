<?php
namespace WScore\DbAccess;

class SqlObject
{
    // public variables to represent sql statement.
    /** @var string           name of database table    */
    public $table;

    /** @var string           name of id (primary key)  */
    public $id_name = 'id';

    /** @var array            join for table            */
    public $join = array();

    /** @var string|array     columns to select in array or string     */
    public $columns = array();

    /** @var array            values for insert/update in array        */
    public $values = array();

    /** @var array            sql functions for insert/update          */
    public $functions = array();

    /** @var array            data to insert/update. from $values and $functions */
    public $rowData   = array();

    /** @var string */
    public $order;

    /** @var array|string */
    public $where;

    /** @var string */
    public $group;

    /** @var string */
    public $having;

    /** @var string */
    public $misc;

    /** @var bool|int */
    public $limit = FALSE;

    /** @var int */
    public $offset = 0;

    /** @var bool */
    public $distinct = FALSE;

    /** @var bool */
    public $forUpdate = FALSE;

    /** @var string */
    public $prepQuoteUseType = 'prepare';
    
    /** @var int */
    public $prepared_counter = 1;
        
    /** @var array    stores prepared values and holder name */
    public $prepared_values = array();

    /** @var array    stores data types of place holders     */
    public $prepared_types = array();

    /** @var array    stores data types of columns           */
    public $col_data_types = array();

    /** @var \Pdo */
    public $pdoObj = NULL;

    public function __construct( $pdoObj=NULL ) {
        $this->pdoObj = $pdoObj;
    }
    // +----------------------------------------------------------------------+
    //  building where clause. 
    // +----------------------------------------------------------------------+
    /**
     * set where statement with values properly prepared/quoted.
     *
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param null|string|bool   $type
     * @return SqlObject
     */
    public function where( $col, $val, $rel='=', $type=NULL ) {
        $this->prepOrQuote( $val, $type, $col );
        return $this->whereRaw( $col, $val, $rel );
    }

    /**
     * set where statement as is.
     *
     * @param        $col
     * @param        $val
     * @param string $rel
     * @return SqlObject
     */
    public function whereRaw( $col, $val, $rel='=' ) {
        $where = array( 'col' => $col, 'val'=> $val, 'rel' => $rel, 'op' => 'AND' );
        $this->where[] = $where;
        return $this;
    }

    /**
     * @param string $col
     */
    public function col( $col ) {
        $this->where[] = array( 'col' => $col, 'val'=> NULL, 'rel' => NULL, 'op' => 'AND' );
    }

    public function mod( $where, $type=NULL ) {
        $last = array_pop( $this->where );
        if( $last ) {
            if( isset( $where[ 'val' ] ) ) {
                $this->prepOrQuote( $where[ 'val' ], $type, $last[ 'col' ] );
            }
            $last = array_merge( $last, $where );
            array_push( $this->where, $last );
        }
    }
    /**
     * @param array $where
     */
    public function modRaw( $where ) {
        $last = array_pop( $this->where );
        if( $last ) {
            $last = array_merge( $last, $where );
            array_push( $this->where, $last );
        }
    }

    // +----------------------------------------------------------------------+
    //  preparing for Insert and Update statement. 
    // +----------------------------------------------------------------------+
    /**
     * prepares value for prepared statement. if value is NULL,
     * it will not be treated as prepared value, instead it is
     * set to SQL's NULL value.
     *
     * @return SqlObject
     */
    public function processValues()
    {
        if( !empty( $this->values ) )
            foreach( $this->values as $key => $val ) {
                if( $val === NULL ) {
                    $this->functions[ $key ] = 'NULL';
                    unset( $this->values[ $key ] );
                }
            }
        $values = $this->values;
        foreach( $values as $col => &$val ) {
            $this->prepOrQuote( $val, NULL, $col );
        }
        $this->rowData = array_merge( $this->functions, $values );
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  Quoting and Preparing Values for Prepared Statement.
    // +----------------------------------------------------------------------+
    /**
     * pre-process values with prepare or quote method.
     *
     * @param      $val
     * @param null $type    data type
     * @param null $col     column name. used to find data type
     * @return SqlObject
     */
    public function prepOrQuote( &$val, $type=NULL, $col=NULL )
    {
        $pqType = $this->prepQuoteUseType;
        $this->$pqType( $val, $type, $col );
        return $this;
    }

    /**
     * replaces value with place holder for prepared statement.
     * the value is kept in prepared_value array.
     *
     * if $type is specified, or column data type is set in col_data_types,
     * types for the place holder is kept in prepared_types array.
     *
     * @param string|array $val
     * @param null|int     $type    data type
     * @param null $col     column name. used to find data type
     * @return SqlObject
     */
    public function prepare( &$val, $type=NULL, $col=NULL )
    {
        if( is_array( $val ) ) {
            foreach( $val as &$v ) {
                $this->prepare( $v, $type, $col );
            }
        }
        else {
            $holder = ':db_prep_' . $this->prepared_counter++;
            $this->prepared_values[ $holder ] = $val;
            $val = $holder;
            if( $type ) {
                $this->prepared_types[ $holder ] = $type;
            }
            elseif( !$type && array_key_exists( $col, $this->col_data_types ) ) {
                $this->prepared_types[ $holder ] = $this->col_data_types[ $col ];
            }
        }
        return $this;
    }

    /**
     * Quote string using Pdo's quote (or just add-slashes if Pdo not present).
     *
     * @param string|array $val
     * @param null|int     $type    data type
     * @return SqlObject
     */
    public function quote( &$val, $type=NULL )
    {
        if( is_array( $val ) ) {
            foreach( $val as &$v ) {
                $this->quote( $v, $type );
            }
        }
        elseif( isset( $this->pdoObj ) ) {
            $val = $this->pdoObj->quote( $val );
        }
        else {
            $val = addslashes( $val );
        }
        return $this;
    }
    // +----------------------------------------------------------------------+
}