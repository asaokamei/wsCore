<?php
namespace friends;

class FriendController
{
    /** @var \WScore\DbAccess\EntityManager */
    protected $em;

    /** @var \wsModule\Alt\Web\FrontMC */
    protected $front;

    /** @var \friends\views\friendsView */
    protected $view;

    /** @var \WScore\DbAccess\Role */
    protected $role;
    /**
     * @param \WScore\DbAccess\EntityManager $em
     * @param \friends\views\friendsView     $view
     * @param \WScore\DbAccess\Role          $role
     * @DimInjection get EntityManager
     * @DimInjection get \friends\views\friendsView
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
        $this->view->set( 'appUrl',  $front->request->getBaseUrl() . 'myFriends/' );
        if( $front->parameter[ 'action' ] == 'setup' ) {
        }
        else {
            \WScore\Core::get( '\friends\model\Friends' );
        }
    }

    // +----------------------------------------------------------------------+
    //  basic action methods.
    // +----------------------------------------------------------------------+
    /**
     * list of tasks.
     *
     * @return \task\views\taskView
     */
    public function actIndex()
    {
        $model = $this->em->getModel( 'Friends' );
        $entities   = $model->query()->select();
        $this->view->showForm_list( $entities, 'list' );
        return $this->view;
    }

    /**
     * @param array $parameter
     * @return views\friendsView
     */
    public function actInfo( $parameter )
    {
        $id = $parameter[ 'id' ];
        $entity = $this->em->getEntity( 'Friends', $id );
        $this->view->showForm_brief( $entity );
        return $this->view;
    }

    /**
     * @param array $parameter
     * @return views\friendsView
     */
    public function actDetail( $parameter )
    {
        $id = $parameter[ 'id' ];
        $entity = $this->em->getEntity( 'Friends', $id );
        $this->view->showForm_detail( $entity );
        return $this->view;
    }
    // +----------------------------------------------------------------------+
    //  initialize database
    // +----------------------------------------------------------------------+
    /**
     * initialize the task database.
     *
     * @return string
     */
    public function actSetup() 
    {
        $folder = __DIR__ . '/data/';
        if( !file_exists( $folder ) ) {
            if( !@mkdir( $folder, 0777 ) ) {
                $this->view->set( 'alert-error', "
                cannot create folder: {$folder}. <br />\n
                please make the folder writable to the webserver.
                ex) mkdir -m 0777 data
                " );
            }
            $this->view->showSetup();
            return $this->view;
        }
        if( $this->front->request->isPost() ) {
            \WScore\Core::get( '\friends\model\Friends' );
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
            $taskUrl = $this->view->get( 'appUrl' );
            header( "Location: $taskUrl" );
            exit;
        }
        /** @var $model \task\model\tasks */
        $model = $this->em->getModel( 'Friends' );
        // clear the current tasks (drop the table)
        $sql = $model->getClearSql();
        $model->query()->execSQL( $sql );
        // create the new task table.
        $sql = $model->getCreateSql();
        $model->query()->execSQL( $sql );
        // using em. this works just fine.
        for( $i = 1; $i <= 15; $i++ ) {
            $task = $model->getSampleTasks($i);
            $this->em->newEntity( 'Friends', $task );
        }
        $this->em->save();
        $taskUrl = $this->view->get( 'appUrl' );
        header( "Location: $taskUrl" );
        exit;
    }

}