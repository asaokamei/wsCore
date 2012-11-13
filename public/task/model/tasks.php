<?php
namespace task\model;

class tasks extends \wsCore\DbAccess\Dao
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
        'task_date'   => array( 'date', 'required | pattern:[FM]', ),
        'task_status' => array( 'text', 'required' ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'task_id'     => array( 'Selector', 'text' ),
        'task_memo'   => array( 'Selector', 'text', 'placeholder:your tasks here | class:span5' ),
        'task_date'   => array( 'Selector', 'date', ),
        'task_status' => array( 'Selector', 'text' ),
    );

    public $recordClassName = 'task\entity\task';

    // +----------------------------------------------------------------------+
    /**
     * @param $em       \wsCore\DbAccess\EntityManager
     * @param $query    \wsCore\DbAccess\Query
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
        $task = array(
            'task_memo' => 'task #' . $idx,
            'task_status' => '1',
            'task_date' => sprintf( '2012-11-%02d', $idx + 1 ),
        );
        return $task;
    }
}