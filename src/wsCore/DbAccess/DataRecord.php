<?php
namespace wsCore\DbAccess;

class DataRecord implements \ArrayAccess
{
    /** @var mixed         value of id. probably an integer     */
    protected $id         = NULL;
    
    /** @var string        name of primary key                  */
    protected $id_name    = NULL;
    
    /** @var array         stores property of the record        */
    protected $properties = array();
    protected $originals  = array(); // stores original data from db

    /** @var bool          validation result.                   */
    protected $is_valid   = FALSE;

    /** @var array         stores error messages from validator */
    protected $errors     = array();

    /** @var string|null */
    protected $model      = NULL;

    /** @var \wsCore\DbAccess\Dao                               */
    protected $dao        = NULL;
    
    /** @var string         html type to show                   */
    protected $html_type  = 'NAME';

    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\DbAccess\Dao $dao
     * @DimInject  Get  Dao
     */
    public function __construct( $dao=NULL )
    {
        $this->setDao( $dao );
    }

    /**
     * @param $dao  Dao
     */
    public function setDao( $dao ) {
        if( $dao ) {
            $this->dao     = $dao;
            $this->id_name = $dao->getIdName();
            $this->model   = $dao->getModelName();
        }
    }
    
    /**
     * creates record from data for an existing data.
     * @param array $data
     * @return \wsCore\DbAccess\DataRecord
     * @throws \RuntimeException
     */
    public function load( $data )
    {
        if( !is_array( $data ) ) {
            throw new \RuntimeException( "data must be an array." );
        }
        $this->originals = $this->properties = $data;
        $this->resetId();
        return $this;
    }

    /**
     * sets id value from the property if dao is set (i.e. know which is id data).
     *
     * @return DataRecord
     */
    public function resetId()
    {
        if( isset( $this->id_name ) && array_key_exists( $this->id_name, $this->properties ) ) {
            $this->id = $this->properties[ $this->id_name ];
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @return mixed|null
     */
    public function getId() {
        return $this->id;
    }

    // +----------------------------------------------------------------------+
    //  get/set properties, and ArrayAccess
    // +----------------------------------------------------------------------+
    /**
     * store properties. for Pdo's fetch as class method. 
     * 
     * @param $name
     * @param $value
     */
    public function __set( $name, $value ) {
        $this->set( $name, $value );
    }

    /**
     * returns it's property. 
     * 
     * @param null|string $name
     * @return mixed
     */
    public function get( $name=NULL ) {
        if( $name ) {
            return ( isset( $this->properties[ $name ] ) ) ? $this->properties[ $name ]: FALSE;
        }
        return $this->properties;
    }

    /**
     * sets property. or set properties by an array( $name => $value ). 
     * 
     * @param string|array $name
     * @param null|mixed   $value
     * @return DataRecord
     */
    public function set( $name, $value=NULL ) {
        if( is_array( $name ) ) {
            $this->properties = array_merge( $this->properties, $name );
        }
        else {
            if( $name !== $this->id_name ) {
                $this->properties[ $name ] = $value;
            }
        }
        return $this;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists( $offset ) {
        return array_key_exists( $offset, $this->properties );
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet( $offset ) {
        return ( array_key_exists( $offset, $this->properties ) )? $this->properties[$offset]:NULL;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        if( is_null( $offset ) ) {
            $this->properties = $value;
        }
        else {
            $this->properties[ $offset ] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset( $offset ) {
        unset( $this->properties[ $offset ] );
    }
    // +----------------------------------------------------------------------+
    //  getting Html Forms.
    // +----------------------------------------------------------------------+
    /**
     * setter/getter for html_type to show html elements. 
     * 
     * @param null|string $html_type
     * @return string
     */
    public function setHtmlType( $html_type=NULL ) {
        if( $html_type ) $this->html_type = $html_type;
        return $this->html_type;
    }
    /**
     * @param string $name
     * @param null   $html_type
     * @return mixed
     */
    public function popHtml( $name, $html_type=NULL ) {
        $html_type = ( $html_type ) ?: $this->html_type;
        return $this->dao->popHtml( $html_type, $name, $this->properties[ $name ] );
    }

    /**
     * @param $name
     * @return mixed
     */
    public function popError( $name ) {
        return $this->errors[ $name ];
    }

    /**
     * @param $name
     */
    public function popName( $name ) {
        $this->dao->propertyName( $name );
    }
    // +----------------------------------------------------------------------+
    //  Validating data.
    // +----------------------------------------------------------------------+
    public function validate() {}
    public function validationOK() {}
    public function resetValidation() {}
    public function isValid() {}
    // +----------------------------------------------------------------------+
    //  saving data to db using dao.
    // +----------------------------------------------------------------------+
    /**
     * @param Dao $dao
     * @return DataRecord
     */
    public function insert( $dao=NULL ) 
    {
        if( is_null( $dao ) ) $dao = $this->dao;
        $id = $dao->insert( $this->properties );
        $this->id = $id;
        return $this;
    }

    /**
     * @param Dao $dao
     * @return DataRecord
     */
    public function update( $dao=NULL ) 
    {
        if( is_null( $dao ) ) $dao = $this->dao;
        $dao->update( $this->id, $this->properties );
        return $this;
    }

    /**
     * @param Dao $dao
     * @return DataRecord
     */
    public function delete( $dao=NULL ) 
    {
        if( is_null( $dao ) ) $dao = $this->dao;
        $dao->delete( $this->id );
        return $this;
    }
    // +----------------------------------------------------------------------+
}