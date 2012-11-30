<?php
namespace friends\model;

class Fr2gr extends \WScore\DbAccess\Model
{
    /** @var string     name of database table */
    protected $table = 'fr2gr';

    /** @var string     name of primary key */
    protected $id_name = 'fr2gr_id';

    protected $definition = array(
        'fr2gr_id'   => array( 'network code', 'number', ),
        'friend_id'  => array( 'my friend', 'number', ),
        'group_code' => array( 'friend to', 'number', ),
        'created_at' => array( 'created at', 'string', 'created_at' ),
        'updated_at' => array( 'updated at', 'string', 'updated_at' ),
    );

    /** @var array      for validation of inputs */
    protected $validators = array(
        'fr2gr_id'   => array( 'int', 'required' ),
        'friend_id'  => array( 'int', 'required' ),
        'group_code' => array( 'int', 'required' ),
    );

    /** @var array      for selector construction */
    protected $selectors = array();

    // +----------------------------------------------------------------------+
    public function getCreateSql()
    {
        $sql = "
        CREATE TABLE {$this->table} (
          fr2gr_id   SERIAL PRIMARY KEY,
          friend_id bigint NOT NULL,
          group_code char(64) NOT NULL,
          created_at datetime,
          updated_at datetime
        );
        ";
        return $sql;
    }

    public function getClearSql()
    {
        $sql = "DROP TABLE IF EXISTS {$this->table}";
        return $sql;
    }
}
