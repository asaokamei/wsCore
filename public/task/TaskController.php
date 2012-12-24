<?php
namespace task;

class TaskController
{
    /** @var \WScore\DataMapper\EntityManager */
    protected $em;

    /** @var \wsModule\Alt\Web\FrontMC */
    protected $front;

    /** @var \task\views\taskView */
    protected $view;

    /** @var \WScore\DataMapper\Role */
    protected $role;
    /**
     * @param \WScore\DataMapper\EntityManager $em
     * @param \task\views\taskView           $view
     * @param \WScore\DataMapper\Role          $role
     * @DimInjection get EntityManager
     * @DimInjection get \task\views\taskView
     * @DimInjection get \WScore\DataMapper\Role
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
     * @param \WScore\DataMapper\Entity_Interface $entity
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
    public function getIndex()
    {
        $model = $this->em->getModel( 'task\entity\task' );
        $all   = $model->query()->order( 'task_status, task_date' )->select();
        $entities = array();
        foreach( $all as $row ) {
            $entities[] = $this->role->applySelectable( $row );
        }
        $this->view->showForm_list( $entities, 'list' );
        return $this->view;
    }

    /**
     * shows insert form for adding a new task.
     *
     * @param array $args
     * @return views\taskView
     */
    public function getNew( $args )
    {
        $entity = $this->em->newEntity( 'task\entity\task' );
        $role   = $this->role->applySelectable( $entity );
        $this->view->showForm_form( $role, 'insert task' );
        return $this->view;
    }

    /**
     * adds new task. 
     * 
     * @param $args
     * @return views\taskView
     */
    public function postNew( $args )
    {
        $entity = $this->em->newEntity( 'task\entity\task' );
        $this->contextLoadAndSave( $entity );
        $this->view->set( 'alert-error', 'insert failed...' );
        $role   = $this->role->applySelectable( $entity );
        $this->view->showForm_form( $role, 'insert task' );
        return $this->view;
    }

    /**
     * shows update form or modifying an existing task.
     *
     * @param array $args
     * @return views\taskView
     */
    public function getTask( $args )
    {
        $id = $args[ 'id' ];
        $entity = $this->em->getEntity( 'task\entity\task', $id );
        $role   = $this->role->applySelectable( $entity );
        $this->view->showForm_form( $role, 'update task' );
        return $this->view;
    }

    /**
     * updates a task. 
     * 
     * @param $args
     * @return views\taskView
     */
    public function postTask( $args )
    {
        $id = $args[ 'id' ];
        $entity = $this->em->getEntity( 'task\entity\task', $id );
        $this->contextLoadAndSave( $entity );
        $this->view->set( 'alert-error', 'update failed...' );
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
    public function getDone( $args )
    {
        $id = $args[ 'id' ];
        /** @var $entity \task\entity\task */
        $entity = $this->em->getEntity( 'task\entity\task', $id );
        if( $entity ) {
            if( $entity->isDone() ) {
                $this->em->delete( $entity );
            }
            else {
                $entity->setDone();
            }
            $this->em->save();
        }
        $taskUrl = $this->view->get( 'taskUrl' );
        header( "Location: $taskUrl" );
        exit;
    }
    // +----------------------------------------------------------------------+
    //  initialize database
    // +----------------------------------------------------------------------+
    /**
     * show a view to initialize the task database.
     *
     * @return string
     */
    public function getSetup() {
        $this->view->showSetup();
        return $this->view;
    }

    /**
     * initialize the task database. 
     */
    public function postSetup() {
        $this->initDb( $this->front->request->getPost( 'initDb' ) );
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
        $model = $this->em->getModel( 'task\entity\task' );
        // clear the current tasks (drop the table)
        $sql = $model->getClearSql();
        $model->query()->execSQL( $sql );
        // create the new task table.
        $sql = $model->getCreateSql();
        $model->query()->execSQL( $sql );
        // using em. this works just fine.
        for( $i = 1; $i <= 5; $i++ ) {
            $task = $model->getSampleTasks($i);
            $this->em->newEntity( 'task\entity\task', $task );
        }
        $this->em->save();
        $taskUrl = $this->view->get( 'taskUrl' );
        header( "Location: $taskUrl" );
        exit;
    }

    public function getPrintO( $args ) {
        $name = $args[ 'name' ];
        require_once( __DIR__ . '/../../vendor/print_o/src.php' );

        if( strtolower( $name ) == 'model' ) {
            print_o( $this->em->getModel( 'tasks' ) );
        }
        print_o( $this->$name );

    }
}