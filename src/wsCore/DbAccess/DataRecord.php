<?php
namespace wsCore\DbAccess;

class DataRecord implements InjectDaoInterface
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
    
    /** @var \wsCore\DbAccess\Dao                               */
    private $_dao_ = NULL;
    
    /** @var string         set type of record. default is get  */
    private $_type_ = NULL;
    
    /** @var string         html type to show                   */
    private $_html_type_ = 'NAME';
    // +----------------------------------------------------------------------+
    /**
     */
    public function __construct() 
    {
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
     * @return Record
     */
    public function fresh( $data=NULL ) {
        $id = NULL;
        if( is_array( $data ) ) {
            $this->_properties_ = $this->_originals_ = $data;
            if( isset( $this->_properties_[ $this->_dao_->getIdName() ] ) ) {
                $id = $this->_properties_[ $this->_dao_->getIdName() ];
            }
        }
        else {
            $id = $data;
        }
        $this->_id_ = $id;
        $this->_type_ = self::TYPE_NEW;
        return $this;
    }

    /**
     * get record from database. 
     * 
     * @param $id
     * @return Record
     */
    public function load( $id ) {
        $stmt = $this->_dao_->find( $id );
        $this->_properties_ = $this->_originals_  = $stmt[0];
        $this->_id_ = $id;
        $this->_type_ = self::TYPE_GET;
        return $this;
    }

    /**
     * re-populates id. use this after Pdo's fetch as class.
     * 
     * @return Record
     */
    public function reconstruct() {
        if( $this->_type_ == NULL && !empty( $this->_properties_ ) ) {
            $this->_id_ = $this->_properties_[ $this->_dao_->getIdName() ];
            $this->_originals_ = $this->_properties_;
            $this->_type_ = self::TYPE_GET;
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
        $this->_properties_[ $name ] = $value;
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
     * @return Record
     */
    public function set( $name, $value=NULL ) {
        if( is_array( $name ) ) {
            $this->_properties_ = array_merge( $this->_properties_, $name );
        }
        else {
            $this->_properties_[ $name ] = $value;
        }
        $this->_exec_ = self::EXEC_SAVE;
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