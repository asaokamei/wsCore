<?php
namespace task;

class TaskController
{
    /** @var \wsCore\DbAccess\EntityManager */
    protected $em;

    /** @var \wsCore\Web\FrontMC */
    protected $front;

    /**
     * @param \wsCore\DbAccess\EntityManager $em
     * @DimInjection get EntityManager
     */
    public function __construct( $em )
    {
        $this->em = $em;
    }

    /**
     * @param \wsCore\Web\FrontMC $front
     */
    public function pre_action( $front ) {
        $this->front = $front;
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
        return ob_get_clean();
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