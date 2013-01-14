<?php
namespace friends;

class CenaFriendController
{
    /** @var \WScore\DataMapper\EntityManager */
    protected $em;

    /** @var \wsModule\Alt\Web\FrontMC */
    protected $front;

    /** @var \friends\views\cenaFriendsView */
    protected $view;

    /** @var \WScore\DataMapper\Role */
    protected $role;

    /** @var callable */
    protected $pager;
    
    /** @var \WScore\DataMapper\CenaManager */
    protected $cena;

    /**
     * @param \WScore\DataMapper\EntityManager   $em
     * @param \friends\views\cenaFriendsView     $view
     * @param \WScore\DataMapper\Role            $role
     * @param \Closure                           $pager
     * @param \WScore\DataMapper\CenaManager                           $cena
     * @DimInjection get EntityManager
     * @DimInjection get \friends\views\cenaFriendsView
     * @DimInjection get \WScore\DataMapper\Role
     * @DimInjection get Raw \wsModule\Alt\DbAccess\Paginate
     * @DimInjection GET \WScore\DataMapper\CenaManager
     */
    public function __construct( $em, $view, $role, $pager, $cena )
    {
        $this->em = $em;
        $this->view = $view;
        $this->role = $role;
        $this->pager = $pager;
        $this->cena  = $cena;
    }

    /**
     * @param \wsModule\Alt\Web\FrontMC $front
     */
    public function pre_action( $front ) {
        $this->front = $front;
        $this->view->set( 'baseUrl', $front->request->getBaseUrl() );
        $this->view->set( 'appUrl',  $front->request->getBaseUrl() . 'cenaFriends/' );
        class_exists( '\WScore\DbAccess\Relation' ); // just for debugger.
    }

    // +----------------------------------------------------------------------+
    //  basic action methods.
    // +----------------------------------------------------------------------+
    /**
     * load friends entities using pagination.
     *
     * @param \wsModule\Alt\DbAccess\Paginate $pager
     * @return \WScore\DataMapper\Entity_Interface[]
     */
    private function loadIndex( $pager )
    {
        $pager->per_page = 4;
        $pager->setOptions( $_GET );
        /** @var $model \friends\model\Friends */
        $model = $this->em->getModel( 'friends\model\Friends' );
        $entities   = $pager->setQuery( $model->query() )->select();
        return $entities;
    }
    /**
     * list of tasks.
     *
     * @return \task\views\taskView
     */
    public function getIndex()
    {
        $uri = $this->front->request->getRequestUri();
        $pager = $this->pager;
        $pager = $pager();
        $entities   = $this->loadIndex( $pager );
        $this->view->showForm_list( $entities, $pager, 'edit', $uri );
        return $this->view;
    }

    /**
     * shows edit form *and* saves friends data.
     * 
     * @return views\cenaFriendsView
     */
    public function postIndex()
    {
        $uri = $this->front->request->getRequestUri();
        $method = $this->front->request->getPost( 'method' );
        if( $method == 'save' ) 
        {
            $cena = $this->cena;
            $cena->useModel( 'friends\model\Friends' );
            $cena->serveEntities();
            $this->em->save();
            header( 'Location: ' . $uri );
            exit;
        }
        else 
        {
            $pager = $this->pager;
            $pager = $pager();
            $entities   = $this->loadIndex( $pager );
            /** @var $model \friends\model\Friends */
            $model = $this->em->getModel( 'friends\model\Friends' );
            $model->setupFormForListings();
            $this->view->showForm_list( $entities, $pager, 'save', $uri );
        }
        return $this->view;
    }

    // +----------------------------------------------------------------------+
    //
    // +----------------------------------------------------------------------+
    /**
     * @param array $parameter
     * @return views\cenaFriendsView
     */
    public function getInfo( $parameter )
    {
        $id = $parameter[ 'id' ];
        $friend   = $this->em->getEntity( 'friends\model\Friends', $id );
        $contacts = $this->em->relation( $friend, 'contacts' )->get();
        $this->em->relation( $friend, 'groups' );
        $this->view->showForm_info( $friend, $contacts );
        
        return $this->view;
    }

    /**
     * @param array $parameter
     * @return views\cenaFriendsView
     */
    public function postInfo( $parameter )
    {
        /** @var $model \friends\model\Friends */
        $model = $this->em->getModel( 'friends\model\Friends' );
        $model->setupFormForListings();
        $id = $parameter[ 'id' ];
        if( $this->front->request->getPost( '_method' ) == 'save' )
        {
            return $this->saveInfo( $id );
        }
        return $this->editInfo( $id );
    }

    /**
     * @param string $id
     * @return views\cenaFriendsView
     */
    protected function editInfo( $id )
    {
        $friend = $this->em->getEntity( 'friends\model\Friends', $id );
        $this->em->relation( $friend, 'contacts' );
        // add new contacts for each contact type. 
        foreach( \friends\model\Contacts::$types as $type ) {
            $contact = $this->em->newEntity( '\friends\model\Contacts' );
            $contact[ 'type' ] = $type[0];
            $this->em->relation( $friend, 'contacts' )->set( $contact );
        }
        foreach( $this->em->relation( $friend, 'contacts' )->get() as $contact ) {
            $this->em->relation( $contact, 'friend' )->set( $friend );
        }
        $groups = $this->em->fetch( 'friends\model\Group' );
        $this->em->relation( $friend, 'groups' );
        $this->view->showForm_detail( $friend, $groups );
        return $this->view;
    }

    /**
     * @param string $id
     * @return views\cenaFriendsView
     */
    protected function saveInfo( $id )
    {
        $cena = $this->cena;
        $cena->useModel( 'friends\model\Friends' );
        $cena->useModel( 'friends\model\Group' );
        $cena->useModel( 'friends\model\Contacts' );
        $cena->useSource( $_POST );
        $cena->cleanUpIfEmpty( 'Contacts', 'info' );
        $cena->serveEntities();
        $this->em->save();

        $jump = $this->view->get( 'appUrl' ) . $id;
        header( 'Location: ' . $jump );
        exit;
        // update groups
        // group entities without registering to em.
        //$groups = $this->em->getModel( 'friends\model\Group' )->find( $_POST[ 'groups' ] );
        //$this->em->relation( $friend, 'groups' )->replace( $groups );

        // update friends info
        if( $loadable->validate() )
        {
            $this->em->save();
            $jump = $this->view->get( 'appUrl' ) . $id;
            header( 'Location: ' . $jump );
            exit;
        }
        $groups = $this->em->fetch( 'friends\model\Group' );
        $this->em->relation( $friend, 'groups' );
        $this->view->showForm_detail( $friend, $groups );
        return $this->view;
    }
    // +----------------------------------------------------------------------+
    //  about Groups
    // +----------------------------------------------------------------------+
    public function getGroup( $parameter )
    {
        if( $this->front->request->isPost() )
        {
            $group = $this->em->newEntity( 'friends\model\Group' );
            $loadable = $this->role->applyCenaLoad( $group );
            $loadable->loadData();
            if( $loadable->validate() )
            {
                $active = $this->role->applyActive( $group );
                $active->save();
                $jump = $this->view->get( 'appUrl' ) . 'group';
                header( 'Location: ' . $jump );
                exit;
            }
        }
        // show list of groups.
        $model = $this->em->getModel( 'friends\model\Group' );
        $entities   = $model->query()->select();
        $this->view->showForm_group( $entities, 'list' );
        return $this->view;
    }
    public function getGroupMod( $parameter )
    {
        $group = $this->em->getEntity( 'friends\model\Group', $parameter[ 'gCode' ] );
        if( $this->front->request->isPost() )
        {
            $loadable = $this->role->applyCenaLoad( $group );
            $loadable->loadData();
            if( $loadable->validate() )
            {
                $active = $this->role->applyActive( $group );
                $active->save();
                $jump = $this->view->get( 'appUrl' ) . 'group';
                header( 'Location: ' . $jump );
                exit;
            }
        }
        $this->view->showForm_groupView( $group, 'list' );
        return $this->view;
    }
    // +----------------------------------------------------------------------+
    //  initialize database
    // +----------------------------------------------------------------------+
    /**
     * show view to initialize the Friends database.
     *
     * @return string
     */
    public function getSetup()
    {
        $this->view->showSetup();
        return $this->view;
    }

    /**
     * initialize the Friends database.
     */
    public function postSetup()
    {
        \WScore\Core::get( 'friends\model\Friends' );
        \WScore\Core::get( 'friends\model\Contacts' );
        $this->initDb( $this->front->request->getPost( 'initDb' ) );
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
        /** @var $model \friends\model\Friends */
        $model = $this->em->getModel( 'friends\model\Friends' );
        // clear the current tasks (drop the table)
        $sql = $model->getClearSql();
        $model->query()->execSQL( $sql );
        // create the new task table.
        $sql = $model->getCreateSql();
        $model->query()->execSQL( $sql );

        // using em. this works just fine.
        for( $i = 1; $i <= 15; $i++ ) {
            $task = $model->getSampleTasks($i);
            $this->em->newEntity( 'friends\model\Friends', $task );
        }
        $this->em->save();

        /** @var $model \friends\model\Contacts */
        $model = $this->em->getModel( 'friends\model\Contacts' );
        // clear the current tasks (drop the table)
        $sql = $model->getClearSql();
        $model->query()->execSQL( $sql );
        // create the new task table.
        $sql = $model->getCreateSql();
        $model->query()->execSQL( $sql );

        /** @var $model \friends\model\Group */
        $model = $this->em->getModel( 'friends\model\Group' );
        // clear the current tasks (drop the table)
        $sql = $model->getClearSql();
        $model->query()->execSQL( $sql );
        // create the new task table.
        $sql = $model->getCreateSql();
        $model->query()->execSQL( $sql );
        $groups = $model->getGroups();
        foreach( $groups as $data ) {
            $this->em->newEntity( 'friends\model\Group', $data );
        }
        $this->em->save();

        /** @var $model \friends\model\Fr2gr */
        $model = $this->em->getModel( 'friends\model\Fr2gr' );
        // clear the current tasks (drop the table)
        $sql = $model->getClearSql();
        $model->query()->execSQL( $sql );
        // create the new task table.
        $sql = $model->getCreateSql();
        $model->query()->execSQL( $sql );

        $friend_id = 1;
        while( !empty( $groups ) ) {
            foreach( $groups as $group ) {
                $data = array('friend_id' => $friend_id, 'group_code' => $group[ 'group_code' ] );
                $this->em->newEntity( 'friends\model\Fr2gr', $data );
            }
            array_shift( $groups );
            $friend_id ++;
        }
        $this->em->save();
        
        $taskUrl = $this->view->get( 'appUrl' );
        header( "Location: $taskUrl" );
        exit;
    }

}