<?php
namespace friends\model;

class Friends extends \WScore\DbAccess\Model
{
    /** @var string     name of database table     */
    protected $table = 'demoFriend';

    /** @var string     name of primary key        */
    protected $id_name = 'friend_id';

    protected $definition = array(
        'friend_id'     => array( 'task id',     'number', ),
        'friend_memo'   => array( 'what to do?', 'string', ),
        'friend_date'   => array( 'by when?',    'string', ),
        'friend_status' => array( 'done?',       'string', ),
        'created_at'  => array( 'created at',  'string', 'created_at'),
        'updated_at'  => array( 'updated at',  'string', 'updated_at'),
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'friend_id'     => array( 'number' ),
        'friend_memo'   => array( 'text', 'required' ),
        'friend_date'   => array( 'date', '', ),
        'friend_status' => array( 'text', '' ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'friend_id'     => array( 'Selector', 'text' ),
        'friend_memo'   => array( 'Selector', 'textarea', 'placeholder:your tasks here | class:span5 | rows:5' ),
        'friend_date'   => array( 'Selector', 'date', ),
        'friend_status' => array( 'Selector', 'checkToggle', '', array(
            'items' => array( array( 1, 'active' ), array( 9, 'done' ) )
        ) ),
    );

    public $recordClassName = 'friends\entity\friend';

    // +----------------------------------------------------------------------+
    /**
     * @param $em       \WScore\DbAccess\EntityManager
     * @param $query    \WScore\DbAccess\Query
     * @DimInjection Get      EntityManager
     * @DimInjection Fresh    Query
     */
    public function __construct( $em, $query )
    {
        parent::__construct( $em, $query );
    }

    /**
     * @param null|string $name
     * @return array
     */
    public function getPropertyList( $name=null ) {
        return $list = parent::protect( $this->properties );
    }

    public function getCreateSql() {
        $sql = "
        CREATE TABLE {$this->table} (
          friend_id   INTEGER PRIMARY KEY AUTOINCREMENT,
          friend_memo text NOT NULL DEFAULT '',
          friend_date date,
          friend_status char(1) NOT NULL DEFAULT '1',
          created_at text,
          updated_at text
        );
        ";
        return $sql;
    }
    public function getClearSql() {
        $sql = "DROP TABLE IF EXISTS {$this->table}";
        return $sql;
    }
    public function getSampleTasks( $idx=1 ) {
        $memo = array(
            1 => 'set done this task',
            2 => 'modify this task',
            3 => 'add a new task',
            4 => 'try validation? set all blank and update/insert a task. ',
            5 => 'delete all finished tasks and setup the task list',
        );
        $task = array(
            'friend_memo' => 'friend #' . $idx . ' ' . $memo[ $idx ],
            'friend_status' => 1,
            'friend_date' => sprintf( '2012-11-%02d', $idx + 1 ),
        );
        return $task;
    }
}