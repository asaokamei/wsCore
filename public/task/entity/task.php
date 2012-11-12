<?php
namespace task\entity;

class task extends \wsCore\DbAccess\Entity_Abstract
{
    protected $_model = 'tasks';

    public $task_id = null;

    public $task_memo = '';

    public $task_date = '';

    public $task_status = '';

    public $new_dt_task;

    public $mod_dt_task;
}

