<?php
namespace task\entity;

class task extends \WScore\DbAccess\Entity_Abstract
{
    const STATUS_ACTIVE = '1';
    const STATUS_DONE   = '9';

    protected $_model = 'tasks';

    public $task_id = null;

    public $task_memo = '';

    public $task_date = '';

    public $task_status = self::STATUS_ACTIVE;

    public $new_dt_task;

    public $mod_dt_task;

    /**
     * @return bool
     */
    public function isDone() {
        return $this->task_status == self::STATUS_DONE;
    }

    /**
     *
     */
    public function setDone() {
        $this->task_status = self::STATUS_DONE;
    }
}

