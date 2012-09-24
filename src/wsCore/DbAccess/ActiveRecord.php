<?php
namespace wsCore\DbAccess;

class ActiveRecord extends DataRecord
{
    const TYPE_NEW = 'new-record';  // new record. not saved to database, yet
    const TYPE_GET = 'get-record';  // record from database.
    const TYPE_IGNORE = 'ignored';  // non-operational record.

    const EXEC_NONE = 'exec-none';
    const EXEC_SAVE = 'exec-save';
    const EXEC_DEL  = 'exec-delete';

    /** @var array         stores relations */
    protected $_relations_  = array();

    /** @var null          type of record: new, get, or ignore.  */
    protected $_type_ = NULL;

    /** @var string      set execution type                  */
    protected $_exec_ = self::EXEC_NONE;
    // +----------------------------------------------------------------------+
    /**
     */
    public function __construct()
    {
    }

    /**
     * saves record into database.
     *
     * @return Record
     */
    public function save() {
        if( $this->_exec_ == self::EXEC_SAVE ) {
            if( $this->type == self::TYPE_NEW ) {
                $id = $this->dao->insert( $this->properties );
                $this->load( $id );
            }
            elseif( $this->_exec_ == self::EXEC_SAVE && $this->type == self::TYPE_GET ) {
                $this->dao->update( $this->id, $this->properties );
            }
        }
        elseif( $this->_exec_ == self::EXEC_DEL && $this->type == self::TYPE_GET ) {
            $this->dao->delete( $this->id );
        }
        return $this;
    }

    // +----------------------------------------------------------------------+

    // +----------------------------------------------------------------------+
}