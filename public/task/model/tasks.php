<?php
namespace task\model;

use \task\entity\task;

class tasks extends \WScore\DataMapper\Model
{
    /** @var string     name of database table     */
    protected $table = 'demoTask';

    /** @var string     name of primary key        */
    protected $id_name = 'task_id';

    protected $definition = array(
        'task_id'     => array( 'task id',     'number', ),
        'task_memo'   => array( 'what to do?', 'string', ),
        'task_date'   => array( 'by when?',    'string', ),
        'task_status' => array( 'done?',       'string', ),
        'new_dt_task' => array( 'created at',  'string', 'created_at'),
        'mod_dt_task' => array( 'updated at',  'string', 'updated_at'),
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'task_id'     => array( 'number' ),
        'task_memo'   => array( 'text', 'required' ),
        'task_date'   => array( 'date', '', ),
        'task_status' => array( 'text', '' ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'task_id'     => array( 'Selector', 'text' ),
        'task_memo'   => array( 'Selector', 'textarea', 'placeholder:your tasks here | class:span5 | rows:5' ),
        'task_date'   => array( 'Selector', 'date', ),
        'task_status' => array( 'Selector', 'checkToggle', '', array(
            'items' => array( array( task::STATUS_ACTIVE, 'active' ), array( task::STATUS_DONE, 'done' ) )
        ) ),
    );

    public $recordClassName = 'task\entity\task';

    // +----------------------------------------------------------------------+
    /**
     * @param $em       \WScore\DataMapper\EntityManager
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
          task_id   INTEGER PRIMARY KEY AUTOINCREMENT,
          task_memo text NOT NULL DEFAULT '',
          task_date date,
          task_status char(1) NOT NULL DEFAULT '1',
          new_dt_task text,
          mod_dt_task text
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
            'task_memo' => 'task #' . $idx . ' ' . $memo[ $idx ],
            'task_status' => task::STATUS_ACTIVE,
            'task_date' => sprintf( '2012-11-%02d', $idx + 1 ),
        );
        return $task;
    }
}