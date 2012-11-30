<?php
namespace friends\model;

class Group extends \WScore\DbAccess\Model
{
    /** @var string     name of database table     */
    protected $table = 'demoGroup';

    /** @var string     name of primary key        */
    protected $id_name = 'group_code';

    protected $definition = array(
        'group_code'   => array( 'group code',   'string', ),
        'group_name'   => array( 'name',         'string', ),
        'created_at'   => array( 'created at',   'string', 'created_at'),
        'updated_at'   => array( 'updated at',   'string', 'updated_at'),
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'group_code' => array( 'number', 'required | pattern:code | maxlength:60' ),
        'group_name' => array( 'text',   'required' ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'group_code'   => array( 'Selector', 'text', 'width:span3' ),
        'group_name'   => array( 'Selector', 'text', 'width:span6' ),
    );
    
    protected $relations = array(
        'friends' => array(
            'relation_type'      => 'HasJoinDao',
            'join_model'         => 'friends\model\Fr2gr', // same as the relation name
            'join_source_column' => 'group_code', // same as the relation name
            'join_target_column' => 'friend_id', // same as the relation name
            'source_column'      => 'group_code', // same as the relation name
            'target_model'       => 'friends\model\Friend',
            'target_column'      => 'friend_id', // use id.
        ),
    );
    public function insert( $data ) {
        return parent::insertValue( $data );
    }

    // +----------------------------------------------------------------------+
    public function getCreateSql()
    {
        $sql = "
        CREATE TABLE {$this->table} (
          group_code   char(64) PRIMARY KEY,
          name text NOT NULL DEFAULT '',
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
