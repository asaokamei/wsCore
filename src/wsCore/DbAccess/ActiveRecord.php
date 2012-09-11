<?php
namespace wsCore\DbAccess;

class ActiveRecord extends DataRecord
{
    const EXEC_NONE = 'exec-none';
    const EXEC_SAVE = 'exec-save';
    const EXEC_DEL  = 'exec-delete';

    /** @var array         stores relations */
    private $_relations_  = array();

    /** @var string      set execution type                  */
    private $_exec_ = self::EXEC_NONE;
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