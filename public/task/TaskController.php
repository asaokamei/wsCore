<?php
namespace task;

class TaskController
{
    /** @var \WScore\DbAccess\EntityManager */
    protected $em;

    /** @var \wsModule\Alt\Web\FrontMC */
    protected $front;

    /** @var \task\views\taskView */
    protected $view;

    /** @var \WScore\DbAccess\Role */
    protected $role;
    /**
     * @param \WScore\DbAccess\EntityManager $em
     * @param \task\views\taskView           $view
     * @param \WScore\DbAccess\Role          $role
     * @DimInjection get EntityManager
     * @DimInjection get \task\views\taskView
     * @DimInjection get \WScore\DbAccess\Role
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
     * @param \WScore\DbAccess\Entity_Interface $entity
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
    //  basic action methods.
    // +----------------------------------------------------------------------+
    /**
     * list of tasks.
     *
     * @return string
     */
    public function actIndex()
    {
        $model = $this->em->getModel( 'tasks' );
        $all   = $model->query()->order( 'task_status, task_date' )->select();
        $entities = array();
        foreach( $all as $row ) {
            $entities[] = $this->role->applySelectable( $row );
        }
        $this->view->showForm_list( $entities, 'list' );
        return $this->view;
    }

    /**
     * for adding a new task.
     * shows insert form, or insert task if post method.
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
        $role   = $this->role->applySelectable( $entity );
        $this->view->showForm_form( $role, 'insert task' );
        return $this->view;
    }

    /**
     * for modifying an existing task.
     * shows update form, or update task if post method.
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
        $role   = $this->role->applySelectable( $entity );
        $this->view->showForm_form( $role, 'update task' );
        return $this->view;
    }

    /**
     * for toggling status between active/done.
     *
     * @param array $args
     * @return views\taskView
     */
    public function actDone( $args )
    {
        $id = $args[ 'id' ];
        /** @var $entity \task\entity\task */
        $entity = $this->em->getEntity( 'tasks', $id );
        if( $entity->isDone() ) {
            $this->em->delete( $entity );
        }
        else {
            $entity->setDone();
        }
        $this->em->save();
        $taskUrl = $this->view->get( 'taskUrl' );
        header( "Location: $taskUrl" );
        exit;
    }
    // +----------------------------------------------------------------------+
    //  initialize database
    // +----------------------------------------------------------------------+
    /**
     * initialize the task database.
     *
     * @return string
     */
    public function actSetup() {
        if( $this->front->request->isPost() ) {
            $this->initDb( $this->front->request->getPost( 'initDb' ) );
        }
        $this->view->showSetup();
        return $this->view;
    }

    /**
     * @param string $initDb
     */
    public function initDb( $initDb )
    {
        if( $initDb !== 'yes' ) {
            $taskUrl = $this->view->get( 'taskUrl' );
            header( "Location: $taskUrl" );
            exit;
        }
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

    public function actPrintO( $args ) {
        $name = $args[ 'name' ];
        require_once( __DIR__ . '/../../vendor/print_o/src.php' );

        if( strtolower( $name ) == 'model' ) {
            print_o( $this->em->getModel( 'tasks' ) );
        }
        print_o( $this->$name );

    }
}