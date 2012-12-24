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

    protected $pager;

    /**
     * @param \WScore\DataMapper\EntityManager   $em
     * @param \friends\views\cenaFriendsView     $view
     * @param \WScore\DataMapper\Role            $role
     * @param \Closure                           $pager
     * @DimInjection get EntityManager
     * @DimInjection get \friends\views\cenaFriendsView
     * @DimInjection get \WScore\DataMapper\Role
     * @DimInjection get Raw \wsModule\Alt\DbAccess\Paginate
     */
    public function __construct( $em, $view, $role, $pager )
    {
        $this->em = $em;
        $this->view = $view;
        $this->role = $role;
        $this->pager = $pager;
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
     * list of tasks.
     *
     * @return \task\views\taskView
     */
    public function actIndex()
    {
        $uri = $this->front->request->getRequestUri();
        $pager = $this->pager;
        $pager = $pager();
        /** @var $pager \wsModule\Alt\DbAccess\Paginate */
        $pager->per_page = 4;
        $pager->setOptions( $_GET );
        $model = $this->em->getModel( 'friends\model\Friends' );
        $entities   = $pager->setQuery( $model->query() )->select();
        if( $this->front->request->isPost() )
        {
            $method = $this->front->request->getPost( 'method' );
            if( $method == 'save' ) {
                header( 'Location: ' . $uri );
                exit;
            }
            else {
                $model->selectors[ 'name'     ][ 2 ] .= 'class:span4';
                $model->selectors[ 'star'     ][ 1 ]  = 'select';
                $model->selectors[ 'star'     ][ 2 ] .= 'class:span1';
                $model->selectors[ 'star'     ][ 'items' ] = \friends\model\Friends::$stars_in_select;
                $model->selectors[ 'gender'   ][ 1 ]  = 'select';
                $model->selectors[ 'gender'   ][ 2 ] .= 'class:span2';
                $model->selectors[ 'birthday' ][ 2 ] .= 'class:span2';
                $this->view->showForm_list( $entities, $pager, 'save', $uri );
            }
        }
        else {
            $this->view->showForm_list( $entities, $pager, 'edit', $uri );
        }
        return $this->view;
    }

    /**
     * @param array $parameter
     * @return views\cenaFriendsView
     */
    public function actInfo( $parameter )
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
    public function actDetail( $parameter )
    {
        $id = $parameter[ 'id' ];
        $friend = $this->em->getEntity( 'friends\model\Friends', $id );
        if( $this->front->request->isPost() )
        {
            // update groups
            // group entities without registering to em.
            $groups = $this->em->getModel( 'friends\model\Group' )->find( $_POST[ 'groups' ] );
            $this->em->relation( $friend, 'groups' )->replace( $groups );

            // update friends info
            $loadable = $this->role->applyCenaLoad( $friend );
            $loadable->loadData();
            if( $loadable->validate() )
            {
                $this->em->save();
                $jump = $this->view->get( 'appUrl' ) . $id;
                header( 'Location: ' . $jump );
                exit;
            }
        }
        $groups = $this->em->getModel( 'friends\model\Group' )->query()->select();
        $groups = $this->em->packToArray( $groups, array( 'group_code', 'name' ) );
        $this->em->relation( $friend, 'groups' );
        $this->view->showForm_detail( $friend, $groups );
        return $this->view;
    }
    // +----------------------------------------------------------------------+
    //  about contacts
    // +----------------------------------------------------------------------+
    public function actContactMod( $parameter )
    {
        $id   = $parameter[ 'id' ];
        $cid  = $parameter[ 'cid' ];
        $friend  = $this->em->getEntity( 'friends\model\Friends', $id );
        $contact = $this->em->getEntity( 'friends\model\Contacts', $cid );
        if( $this->front->request->isPost() )
        {
            $loadable = $this->role->applyCenaLoad( $contact );
            $loadable->loadData();
            if( $loadable->validate() )
            {
                $active = $this->role->applyActive( $contact );
                $active->relation( 'friend' )->set( $friend );
                $active->save();
                $jump = $this->view->get( 'appUrl' ) . $id;
                header( 'Location: ' . $jump );
                exit;
            }
        }
        $this->view->showContact_form( $friend, $contact );
        return $this->view;
    }
    /**
     * @param $parameter
     * @return views\cenaFriendsView
     */
    public function actContactNew( $parameter )
    {
        $id   = $parameter[ 'id' ];
        $type = $parameter[ 'type' ];
        /** @var $friend  \friends\entity\friend */
        /** @var $contact \friends\entity\contact */
        $friend  = $this->em->getEntity( 'friends\model\Friends', $id );
        $contact = $this->em->newEntity( 'friends\model\Contacts' );
        $contact->type = $type;
        /** @var $contact \friends\entity\contact */
        if( $this->front->request->isPost() ) 
        {
            $loadable = $this->role->applyCenaLoad( $contact );
            $loadable->loadData();
            if( $loadable->validate() ) 
            {
                $active = $this->role->applyActive( $loadable );
                $active->relation( 'friend' )->set( $friend );
                $active->save();
                $jump = $this->view->get( 'appUrl' ) . $id;
                header( 'Location: ' . $jump );
                exit;
            }
        }
        $this->view->showContact_form( $friend, $contact );
        return $this->view;
    }
    // +----------------------------------------------------------------------+
    //  about Groups
    // +----------------------------------------------------------------------+
    public function actGroup( $parameter )
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
    public function actGroupMod( $parameter )
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
            \WScore\Core::get( 'friends\model\Friends' );
            \WScore\Core::get( 'friends\model\Contacts' );
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