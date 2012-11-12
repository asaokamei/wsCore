<?php
namespace task;

class TaskController
{
    /**
     * @param \task\model\task $task
     */
    public function __construct( $task )
    {
        $this->task = $task;
    }
    public function index()
    {

    }
}