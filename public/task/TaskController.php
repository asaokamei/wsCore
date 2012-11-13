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

    /**
     * @param \wsCore\DbAccess\EntityManager $em
     * @param \task\views\taskView $view
     * @DimInjection get EntityManager
     * @DimInjection get \task\views\taskView
     */
    public function __construct( $em, $view )
    {
        $this->em = $em;
        $this->view = $view;
    }

    /**
     * @param \wsModule\Alt\Web\FrontMC $front
     */
    public function pre_action( $front ) {
        $this->front = $front;
        $this->view->set( 'baseUrl', $front->request->getBaseUrl() );
    }

    /**
     * @return string
     */
    public function actIndex()
    {
        $model = $this->em->getModel( 'tasks' );
        $all   = $model->query()->select();
        /** @var $role \wsCore\DbAccess\Role */
        $role = $this->em->container()->get( '\wsCore\DbAccess\Role' );
        $entities = array();
        foreach( $all as $row ) {
            $entities[] = $role->applyLoadable( $row );
        }
        $this->view->showForm_list( $entities, 'list' );
        return $this->view;
    }

    public function actTask( $args )
    {
        $id = $args[ 'id' ];
        $entity = $this->em->getEntity( 'tasks', $id );
        $role = $this->em->container()->get( '\wsCore\DbAccess\Role' );
        $entity = $role->applyLoadable( $entity );
        $this->view->showForm_form( $entity );
        return $this->view;
    }
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
        return 'setup';
    }
}