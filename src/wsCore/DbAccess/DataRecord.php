<?php
namespace wsCore\DbAccess;

class DataRecord
{
    /** @var mixed         value of id. probably an integer     */
    protected $_id_         = NULL;
    
    /** @var array         stores property of the record        */
    protected $_properties_ = array();
    protected $_originals_  = array(); // stores original data from db

    /** @var bool          validation result.                   */
    protected $_is_valid_ = FALSE;

    /** @var array         stores error messages from validator */
    protected $_errors_     = array();

    /** @var string|null */
    protected $_model_ = NULL;

    /** @var \wsCore\DbAccess\Dao                               */
    protected $_dao_ = NULL;
    
    /** @var string         html type to show                   */
    protected $_html_type_ = 'NAME';

    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\DbAccess\Dao $dao
     * @param array  $data
     * @DimInject  Get \wsCore\DbAccess\Dao
     */
    public function __construct( $dao=NULL, $data=array() )
    {
        $this->_dao_ = $dao;
        $this->load( $data );
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
        $this->_originals_ = $this->_properties_ = $data;
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
        if( isset( $this->_dao_ ) ) {
            if( isset( $this->_properties_[ $this->_dao_->getIdName() ] ) ) {
                $this->_id_ = $this->_properties_[ $this->_dao_->getIdName() ];
            }
        }
        return $this;
    }

    /**
     * re-populates id. use this after Pdo's fetch as class.
     *
     * @param null|\wsCore\DbAccess\Dao $dao
     * @return DataRecord
     */
    public function reconstruct( $dao=NULL ) 
    {
        $this->_dao_ = $dao;
        $this->_originals_ = $this->_properties_;
        $this->resetId();
        return $this;
    }

    /**
     * @return string
     */
    public function getModel() {
        return $this->_dao_->getModelName();
    }

    /**
     * @return mixed|null
     */
    public function getId() {
        return $this->_id_;
    }

    // +----------------------------------------------------------------------+
    //  get/set properties
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
            return ( isset( $this->_properties_[ $name ] ) ) ? $this->_properties_[ $name ]: FALSE;
        }
        return $this->_properties_;
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
            $this->_properties_ = array_merge( $this->_properties_, $name );
        }
        else {
            $this->_properties_[ $name ] = $value;
        }
        return $this;
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
        if( $html_type ) $this->_html_type_ = $html_type;
        return $this->_html_type_;
    }
    /**
     * @param string $name
     * @param null   $html_type
     * @return mixed
     */
    public function popHtml( $name, $html_type=NULL ) {
        $html_type = ( $html_type ) ?: $this->_html_type_;
        return $this->_dao_->popHtml( $html_type, $name, $this->_properties_[ $name ] );
    }

    /**
     * @param $name
     * @return mixed
     */
    public function popError( $name ) {
        return $this->_errors_[ $name ];
    }

    /**
     * @param $name
     */
    public function popName( $name ) {
        $this->_dao_->propertyName( $name );
    }
    // +----------------------------------------------------------------------+
    //  Validating data.
    // +----------------------------------------------------------------------+
    public function validate() {}
    public function validationOK() {}
    public function resetValidation() {}
    public function isValid() {}
    // +----------------------------------------------------------------------+
}