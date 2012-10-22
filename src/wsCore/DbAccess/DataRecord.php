<?php
namespace wsCore\DbAccess;

class DataRecord implements \ArrayAccess
{
    const ID_TYPE_GET  = 'get';
    const ID_TYPE_NEW  = 'new';
    const ACTION_NONE  = 'act-none';
    const ACTION_SAVE  = 'act-save';
    const ACTION_DEL   = 'act-del';

    /** @var string|null               id type: get, new, or null            */
    protected $srcType    = null;

    /** @var string                    action to do                          */
    protected $actType    = self::ACTION_NONE;

    /** @var mixed                     value of id. probably an integer      */
    protected $id         = NULL;
    
    /** @var string                    name of primary key                   */
    protected $id_name    = NULL;
    
    /** @var array                     stores property of the record         */
    protected $properties = array();

    /** @var bool                      validation result.                    */
    protected $is_valid   = FALSE;

    /** @var array                     stores error messages from validator  */
    protected $errors     = array();

    /** @var string|null */
    protected $model      = NULL;

    /** @var \wsCore\DbAccess\Dao      Data Access Object                    */
    protected $dao        = NULL;
    
    /** @var string                     html type to show                    */
    protected $html_type  = 'NAME';

    /** @var Relation_Interface[]       relation objects pool                */
    protected $relations  = array();

    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\DbAccess\Dao $dao
     * @param string|null $type
     * @DimInject  Get  Dao
     */
    public function __construct( $dao=NULL, $type=null )
    {
        $this->srcType = ($type) ?: self::ID_TYPE_GET;
        $this->actType = self::ACTION_NONE;
        $this->setDao( $dao );
        $this->resetId();
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
     * @return null|\wsCore\DbAccess\Dao
     */
    public function getDao() {
        return $this->dao;
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
     * @return null|string
     */
    public function getType() {
        return ( $this->srcType==self::ID_TYPE_GET ) ? self::ID_TYPE_GET : self::ID_TYPE_NEW;
    }

    /**
     * @return bool
     */
    public function isIdPermanent() {
        return $this->srcType == self::ID_TYPE_GET;
    }

    /**
     * @return mixed|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getIdName() {
        return $this->id_name;
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->dao->getTable();
    }

    /**
     * @param string $actType
     * @param bool $force
     * @return DataRecord
     */
    protected function setActionType( $actType, $force=false )
    {
        if( ( $actType == self::ACTION_NONE && $force ) ||
            ( $actType == self::ACTION_SAVE && $this->actType != self::ACTION_DEL ) ||
            ( $actType == self::ACTION_DEL ) )
        {
            $this->actType = $actType;
        }
        return $this;
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
     * @return array|bool
     */
    public function get( $name=NULL ) {
        if( $name ) {
            return ( array_key_exists( $name, $this->properties ) ) ? $this->properties[ $name ]: FALSE;
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
            $this->properties[ $name ] = $value;
        }
        $this->setActionType( self::ACTION_SAVE );
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
            $offset = $value;
            $value  = null;
        }
        $this->set( $offset, $value );
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
     * @return string
     */
    public function popName( $name ) {
        return $this->dao->propertyName( $name );
    }
    // +----------------------------------------------------------------------+
    //  Validating data.
    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\Validator\DataIO $dio
     * @return bool
     */
    public function validate( $dio )
    {
        $dio->source( $this->properties );
        $this->dao->validate( $dio );
        $this->is_valid = !$dio->popErrors( $this->errors );
        return $this->is_valid;
    }

    /**
     * @return DataRecord
     */
    public function resetValidation() {
        $this->is_valid = FALSE;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return $this->is_valid;
    }
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
        $this->srcType = self::ID_TYPE_GET;
        $this->id = $id;
        $this->properties[ $this->id_name ] = $id;
        return $this;
    }

    /**
     * @param Dao $dao
     * @return DataRecord
     */
    public function update( $dao=NULL ) 
    {
        if( is_null( $dao ) ) $dao = $this->dao;
        $dao->update( $this->getId(), $this->properties );
        return $this;
    }

    /**
     * @return DataRecord
     */
    public function delete()
    {
        return $this->setActionType( self::ACTION_DEL );
    }

    /**
     * @param Dao $dao
     * @return DataRecord
     */
    public function deleteRecord( $dao=NULL )
    {
        if( is_null( $dao ) ) $dao = $this->dao;
        $dao->delete( $this->getId() );
        return $this;
    }

    /**
     * @param bool $saveRelations
     * @return \wsCore\DbAccess\DataRecord
     */
    public function save( $saveRelations=FALSE )
    {
        if( $this->actType == self::ACTION_NONE ) {
            // do nothing.
        }
        elseif( $this->actType == self::ACTION_DEL ) {
            $this->deleteRecord();
        }
        elseif( $this->actType == self::ACTION_SAVE )
        {
            if( $this->srcType == self::ID_TYPE_GET ) { // i.e. ACTION_SAVE
                $this->update();
            }
            elseif( $this->srcType == self::ID_TYPE_NEW ) {
                $this->insert();
                if( $saveRelations && !empty( $this->relations ) ) {
                    foreach( $this->relations as $relation ) {
                        $relation->link();
                    }
                }
            }
        }
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  relations
    // +----------------------------------------------------------------------+
    /**
     * @param $name
     * @return Relation_Interface|Relation_HasJoinDao
     */
    public function relation( $name )
    {
        if( !isset( $this->relations[ $name ] ) ) {
            $this->relations[ $name ] = Relation::getRelation( $this, $this->dao->getRelationInfo(), $name );
        }
        return $this->relations[ $name ];
    }
    // +----------------------------------------------------------------------+
}