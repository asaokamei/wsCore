<?php
namespace wsCore\DbAccess;

class DataRecord
{
    const TYPE_NEW = 'new-record';  // new record. not saved to database, yet
    const TYPE_GET = 'get-record';  // record from database. 
    const TYPE_IGNORE = 'ignored';  // non-operational record.

    /** @var mixed         value of id. probably an integer     */
    private $_id_         = NULL;
    
    /** @var array         stores property of the record        */
    private $_properties_ = array();
    private $_originals_  = array(); // stores original data from db  
    
    /** @var array         stores error messages from validator */
    private $_errors_     = array();

    /** @var string|null */
    private $_model_ = NULL;

    /** @var \wsCore\DbAccess\Dao                               */
    private $_dao_ = NULL;
    
    /** @var string         set type of record. default is get  */
    private $_type_ = NULL;
    
    /** @var string         html type to show                   */
    private $_html_type_ = 'NAME';
    // +----------------------------------------------------------------------+
    /**
     * @param array  $data
     * @param string $type
     */
    public function __construct( $data=array(), $type=self::TYPE_NEW )
    {
        if( $type == self::TYPE_NEW ) {
            $this->fresh( $data );
        }
        else {
            $this->load( $data );
        }
    }

    /**
     * @param \wsCore\DbAccess\Dao $dao
     */
    public function injectDao( $dao ) {
        $this->_dao_ = $dao;
    }

    /**
     * create fresh/new record with or without id.
     *
     * @param null|string|array|int $data
     * @throws \RuntimeException
     * @return DataRecord
     */
    public function fresh( $data=array() ) {
        if( !is_array( $data ) ) {
            throw new \RuntimeException( "data must be an array." );
        }
        $this->_properties_ = $this->_originals_ = $data;
        $this->resetId();
        $this->_type_ = self::TYPE_NEW;
        return $this;
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
        $this->_type_ = self::TYPE_GET;
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
     * get record from database. 
     * 
     * @param $id
     * @return DataRecord
     */
    public function fetch( $id ) {
        $stmt = $this->_dao_->find( $id );
        $this->_properties_ = $this->_originals_  = $stmt[0];
        $this->_id_ = $id;
        $this->_type_ = self::TYPE_GET;
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
        if( $dao ) {
            $this->_dao_ = $dao;
        }
        if( $this->_type_ == NULL && !empty( $this->_properties_ ) ) {
            $this->_originals_ = $this->_properties_;
            $this->_type_ = self::TYPE_GET;
            $this->resetId();
        }
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

    /**
     * @return null|string
     */
    public function getType() {
        return $this->_type_;
    }
    public function validate() {}
    public function validationOK() {}
    public function resetValidation() {}
    public function isValid() {}
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
    public function pop( $name=NULL ) {
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
}