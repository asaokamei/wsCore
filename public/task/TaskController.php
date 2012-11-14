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
        if( $front->request->isPost() ) {
            $front->parameter[ 'action' ] .= '_post';
        }
    }

    // +----------------------------------------------------------------------+
    //  show list
    // +----------------------------------------------------------------------+
    /**
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
     * @param array $args
     * @return views\taskView
     */
    public function actNew( $args )
    {
        $entity = $this->em->newEntity( 'tasks' );
        $entity = $this->role->applyLoadable( $entity );
        $this->view->set( 'title', 'New Task' );
        $this->view->showForm_form( $entity );
        return $this->view;
    }

    /**
     * @param array $args
     * @return views\taskView
     */
    public function actNew_post( $args )
    {
        $entity = $this->em->newEntity( 'tasks' );
        $entity = $this->role->applyLoadable( $entity );
        $entity->loadData();
        if( $entity->validate() ) {
            $this->em->save();
            $taskUrl = $this->view->get( 'taskUrl' );
            header( "Location: $taskUrl" );
            exit;
        }
        $this->view->set( 'alert-error', 'insert failed...' );
        $this->view->showForm_form( $entity );
        return $this->view;
    }

    // +----------------------------------------------------------------------+
    //  update/post/modification
    // +----------------------------------------------------------------------+
    /**
     * @param array $args
     * @return views\taskView
     */
    public function actTask( $args )
    {
        $id = $args[ 'id' ];
        $entity = $this->em->getEntity( 'tasks', $id );
        $entity = $this->role->applyLoadable( $entity );
        $this->view->set( 'title', 'Details' );
        $this->view->showForm_form( $entity );
        return $this->view;
    }

    /**
     * @param array $args
     * @return views\taskView
     */
    public function actTask_post( $args )
    {
        $id = $args[ 'id' ];
        $entity = $this->em->getEntity( 'tasks', $id );
        $entity = $this->role->applyLoadable( $entity );
        $entity->loadData();
        if( $entity->validate() ) {
            $this->em->save();
            $taskUrl = $this->view->get( 'taskUrl' );
            header( "Location: $taskUrl" );
            exit;
        }
        $this->view->set( 'alert-error', 'update failed...' );
        $this->view->showForm_form( $entity );
        return $this->view;
    }

    // +----------------------------------------------------------------------+
    //  show details
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    public function actSetup()
    {
        /** @var $model \task\model\tasks */
        $model = $this->em->getModel( 'tasks' );
        $sql = $model->getClearSql();
        $model->query()->execSQL( $sql );
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