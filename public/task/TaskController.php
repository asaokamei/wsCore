<?php
namespace task;

class TaskController
{
    /** @var \wsCore\DbAccess\EntityManager */
    protected $em;

    /** @var \wsModule\Alt\Web\FrontMC */
    protected $front;

    /** @var \task\views\taskView */
    protected $view;

    /** @var \wsCore\DbAccess\Role */
    protected $role;
    /**
     * @param \wsCore\DbAccess\EntityManager $em
     * @param \task\views\taskView           $view
     * @param \wsCore\DbAccess\Role          $role
     * @DimInjection get EntityManager
     * @DimInjection get \task\views\taskView
     * @DimInjection get \wsCore\DbAccess\Role
     */
    public function __construct( $em, $view, $role )
    {
        $this->em = $em;
        $this->view = $view;
        $this->role = $role;
    }

    /**
     * @param \wsModule\Alt\Web\FrontMC $front
     */
    public function pre_action( $front ) {
        $this->front = $front;
        $this->view->set( 'baseUrl', $front->request->getBaseUrl() );
        $this->view->set( 'taskUrl', $front->request->getBaseUrl() . 'myTasks/' );
    }

    /**
     * a generic method to load post data into entity,
     * validates the entity values, saves to database,
     * and jump back to the task list if successful.
     * returns false if save fails.
     *
     * @param \wsCore\DbAccess\Entity_Interface $entity
     * @return views\taskView
     */
    public function contextLoadAndSave( $entity )
    {
        $role   = $this->role->applyLoadable( $entity );
        $role->loadData();
        if( $role->validate() ) {
            $this->em->save();
            $taskUrl = $this->view->get( 'taskUrl' );
            header( "Location: $taskUrl" );
            exit;
        }
        return false;
    }
    // +----------------------------------------------------------------------+
    //  show list
    // +----------------------------------------------------------------------+
    /**
     * list of tasks.
     *
     * @return string
     */
    public function actIndex()
    {
        $model = $this->em->getModel( 'tasks' );
        $all   = $model->query()->select();
        $entities = array();
        foreach( $all as $row ) {
            $entities[] = $this->role->applyLoadable( $row );
        }
        $this->view->showForm_list( $entities, 'list' );
        return $this->view;
    }

    // +----------------------------------------------------------------------+
    //  insert/put/addition
    // +----------------------------------------------------------------------+
    /**
     * shows the form for inserting task, or insert task if post method.
     *
     * @param array $args
     * @return views\taskView
     */
    public function actNew( $args )
    {
        $entity = $this->em->newEntity( 'tasks' );
        if( $this->front->request->isPost() ) {
            $this->contextLoadAndSave( $entity );
            $this->view->set( 'alert-error', 'insert failed...' );
        }
        $role   = $this->role->applyLoadable( $entity );
        $this->view->showForm_form( $role, 'insert task' );
        return $this->view;
    }

    // +----------------------------------------------------------------------+
    //  update/post/modification
    // +----------------------------------------------------------------------+
    /**
     * shows the form for update task, or update task if post method.
     *
     * @param array $args
     * @return views\taskView
     */
    public function actTask( $args )
    {
        $id = $args[ 'id' ];
        $entity = $this->em->getEntity( 'tasks', $id );
        if( $this->front->request->isPost() ) {
            $this->contextLoadAndSave( $entity );
            $this->view->set( 'alert-error', 'update failed...' );
        }
        $role   = $this->role->applyLoadable( $entity );
        $this->view->showForm_form( $role, 'update task' );
        return $this->view;
    }

    // +----------------------------------------------------------------------+
    //  show details
    // +----------------------------------------------------------------------+
    /**
     * initialize the task database.
     *
     * @return string
     */
    public function actSetup()
    {
        /** @var $model \task\model\tasks */
        $model = $this->em->getModel( 'tasks' );
        // clear the current tasks (drop the table)
        $sql = $model->getClearSql();
        $model->query()->execSQL( $sql );
        // create the new task table.
        $sql = $model->getCreateSql();
        $model->query()->execSQL( $sql );
        // using em. this works just fine.
        for( $i = 1; $i <= 5; $i++ ) {
            $task = $model->getSampleTasks($i);
            $this->em->newEntity( 'tasks', $task );
        }
        $this->em->save();
        $taskUrl = $this->view->get( 'taskUrl' );
        header( "Location: $taskUrl" );
        exit;
    }
}