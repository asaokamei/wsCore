<?php
namespace task;

class TaskController
{
    /** @var \wsCore\DbAccess\EntityManager */
    protected $em;

    /** @var \wsCore\Web\FrontMC */
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
     * @param \wsCore\Web\FrontMC $front
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
        ob_start();
        var_dump( $all );
        $content = ob_get_clean();
        $this->view->set( 'content', $content );
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