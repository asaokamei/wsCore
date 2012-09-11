<?php
namespace wsCore\DbAccess;

class Record implements InjectDaoInterface
{
    const TYPE_NEW = 'new-record';  // new record. not saved to database, yet
    const TYPE_GET = 'get-record';  // record from database. 
    const TYPE_IGNORE = 'ignored';  // non-operational record. 
    
    /** @var mixed         value of id. probably an integer     */
    private $id         = NULL;
    
    /** @var array         stores property of the record        */
    private $properties = array();
    private $originals  = array(); // stores original data from db  
    
    /** @var array         stores error messages from validator */
    private $errors     = array();
    
    /** @var array         stores relations */
    private $relations  = array();
    
    /** @var \wsCore\DbAccess\Dao     */
    private $dao = NULL;
    
    /** @var string      set type of record. default is get  */
    private $type = NULL;

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
        $this->dao = $dao;
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
            $this->properties = $this->originals = $data;
            if( isset( $this->properties[ $this->dao->getIdName() ] ) ) {
                $id = $this->properties[ $this->dao->getIdName() ];
            }
        }
        else {
            $id = $data;
        }
        $this->id = $id;
        $this->type = self::TYPE_NEW;
        return $this;
    }

    /**
     * get record from database. 
     * 
     * @param $id
     * @return Record
     */
    public function load( $id ) {
        $stmt = $this->dao->find( $id );
        $this->properties = $this->originals  = $stmt[0];
        $this->id = $id;
        $this->type = self::TYPE_GET;
        return $this;
    }

    /**
     * saves record into database. 
     * 
     * @return Record
     */
    public function save() {
        if( $this->type == self::TYPE_NEW ) {
            $id = $this->dao->insert( $this->properties );
            $this->load( $id );
        }
        elseif( $this->type == self::TYPE_GET ) {
            $this->dao->update( $this->id, $this->properties );
        }
        return $this;
    }

    /**
     * re-populates id. use this after Pdo's fetch as class.
     * 
     * @return Record
     */
    public function reconstruct() {
        if( $this->type == NULL && !empty( $this->properties ) ) {
            $this->id = $this->properties[ $this->dao->getIdName() ];
            $this->originals = $this->properties;
            $this->type = self::TYPE_GET;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getModelName() {
        return $this->dao->getModelName();
    }
    // +----------------------------------------------------------------------+
    /**
     * store properties. for Pdo's fetch as class method. 
     * 
     * @param $name
     * @param $value
     */
    public function __set( $name, $value ) {
        $this->properties[ $name ] = $value;
    }

    public function pop( $name ) {
        return ( isset( $this->properties[ $name ] ) ) ? $this->properties[ $name ]: FALSE;
    }
    /**
     * @param      $name
     * @param null $type
     * @return mixed
     */
    public function popHtml( $name, $type=NULL ) {
        return $this->dao->popHtml( $type, $name, $this->properties[ $name ] );
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
}