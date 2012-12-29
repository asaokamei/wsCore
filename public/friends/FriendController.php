<?php
namespace friends;

class FriendController
{
    /** @var \WScore\DataMapper\EntityManager */
    protected $em;

    /** @var \wsModule\Alt\Web\FrontMC */
    protected $front;

    /** @var \friends\views\friendsView */
    protected $view;

    /** @var \WScore\DataMapper\Role */
    protected $role;
    /**
     * @param \WScore\DataMapper\EntityManager $em
     * @param \friends\views\friendsView     $view
     * @param \WScore\DataMapper\Role          $role
     * @DimInjection get EntityManager
     * @DimInjection get \friends\views\friendsView
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
        $this->view->set( 'appUrl',  $front->request->getBaseUrl() . 'myFriends/' );
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
    public function getIndex()
    {
        $model = $this->em->getModel( 'friends\model\Friends' );
        $entities   = $model->query()->select();
        foreach( $entities as $friend ) {
            $this->role->applyActive( $friend )->relation( 'groups' );
        }
        $this->view->showForm_list( $entities, 'list' );
        return $this->view;
    }

    /**
     * @param array $parameter
     * @return views\friendsView
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
     * shows modify form to update friends' information. 
     * 
     * @param array $parameter
     * @return views\friendsView
     */
    public function getDetail( $parameter )
    {
        $id = $parameter[ 'id' ];
        $friend = $this->em->getEntity( 'friends\model\Friends', $id );
        return $this->showDetail( $friend );
    }

    /**
     * updates Friend's detail information. 
     * 
     * @param $parameter
     * @return views\friendsView
     */
    public function postDetail( $parameter )
    {
        $id = $parameter[ 'id' ];
        $friend = $this->em->getEntity( 'friends\model\Friends', $id );
        // update groups
        // group entities without registering to em.
        $groups = $this->em->getModel( 'friends\model\Group' )->find( $_POST[ 'groups' ] );
        $this->em->relation( $friend, 'groups' )->replace( $groups );

        // update friends info
        $loadable = $this->role->applyLoadable( $friend );
        $loadable->loadData();
        if( $loadable->validate() )
        {
            $this->em->save();
            $jump = $this->view->get( 'appUrl' ) . $id;
            header( 'Location: ' . $jump );
            exit;
        }
        return $this->showDetail( $friend );
    }

    /**
     * shows Friend's form. 
     * 
     * @param $friend
     * @return views\friendsView
     */
    private function showDetail( $friend )
    {
        $groups = $this->em->fetch( 'friends\model\Group' );
        $groups = $groups->pack( array( 'group_code', 'name' ) );
        $this->em->relation( $friend, 'groups' );
        $this->view->showForm_detail( $friend, $groups );
        return $this->view;
    }
    // +----------------------------------------------------------------------+
    //  about contacts
    // +----------------------------------------------------------------------+
    /**
     * returns Friend and Contact entity. 
     * 
     * @param $parameter
     * @return array
     */
    private function loadFriendAndContact( $parameter )
    {
        $id   = $parameter[ 'id' ];
        $friend  = $this->em->getEntity( 'friends\model\Friends', $id );
        if( isset( $parameter[ 'cid' ] ) ) {
            $contact = $this->em->getEntity( 'friends\model\Contacts', $parameter[ 'cid' ] );
        }
        else {
            $contact = $this->em->newEntity( 'friends\model\Contacts' );
        }
        return array( $friend, $contact );
    }

    /**
     * shows modify form for a contact.
     * 
     * @param $parameter
     * @return views\friendsView
     */
    public function getContactMod( $parameter )
    {
        $id   = $parameter[ 'id' ];
        /** @var $contact \friends\entity\contact */
        list( $friend, $contact ) = $this->loadFriendAndContact( $parameter );
        $this->view->showContact_form( $friend, $contact );
        return $this->view;
    }

    /**
     * updates contact information. 
     * 
     * @param $parameter
     * @return views\friendsView
     */
    public function postContactMod( $parameter )
    {
        $id   = $parameter[ 'id' ];
        /** @var $contact \friends\entity\contact */
        list( $friend, $contact ) = $this->loadFriendAndContact( $parameter );

        $loadable = $this->role->applyLoadable( $contact );
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
        $this->view->showContact_form( $friend, $contact );
        return $this->view;
    }
    
    /**
     * shows contact form. 
     * 
     * @param $parameter
     * @return views\friendsView
     */
    public function getContactNew( $parameter )
    {
        list( $friend, $contact ) = $this->loadFriendAndContact( $parameter );
        $type = $parameter[ 'type' ];
        /** @var $friend  \friends\entity\friend */
        /** @var $contact \friends\entity\contact */
        $contact->type = $type;
        $this->view->showContact_form( $friend, $contact );
        return $this->view;
    }

    /**
     * create new contact for a friend. 
     * 
     * @param $parameter
     * @return views\friendsView
     */
    public function postContactNew( $parameter )
    {
        $id   = $parameter[ 'id' ];
        $type = $parameter[ 'type' ];
        /** @var $contact \friends\entity\contact */
        list( $friend, $contact ) = $this->loadFriendAndContact( $parameter );
        $contact->type = $type;
        
        $loadable = $this->role->applyLoadable( $contact );
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
        $this->view->showContact_form( $friend, $contact );
        return $this->view;
    }
    // +----------------------------------------------------------------------+
    //  about Groups
    // +----------------------------------------------------------------------+
    /**
     * shows list of groups. 
     * 
     * @return views\friendsView
     */
    public function getGroup()
    {
        $model = $this->em->getModel( 'friends\model\Group' );
        $entities   = $model->query()->select();
        $this->view->showForm_group( $entities, 'list' );
        return $this->view;
    }

    /**
     * creates a new group data.
     * 
     * @return views\friendsView
     */
    public function postGroup()
    {
        $group = $this->em->newEntity( 'friends\model\Group' );
        $loadable = $this->role->applyLoadable( $group );
        $loadable->loadData();
        if( $loadable->validate() )
        {
            $active = $this->role->applyActive( $group );
            $active->save();
            $jump = $this->view->get( 'appUrl' ) . 'group';
            header( 'Location: ' . $jump );
            exit;
        }
        return $this->getGroup();
    }
    /**
     * shows update form for a group. 
     * 
     * @param $parameter
     * @return views\friendsView
     */
    public function getGroupMod( $parameter )
    {
        $group = $this->em->getEntity( 'friends\model\Group', $parameter[ 'gCode' ] );
        $this->view->showForm_groupView( $group, 'list' );
        return $this->view;
    }

    /**
     * updates group information.
     * 
     * @param $parameter
     * @return views\friendsView
     */
    public function postGroupMod( $parameter )
    {
        $group = $this->em->getEntity( 'friends\model\Group', $parameter[ 'gCode' ] );

        $loadable = $this->role->applyLoadable( $group );
        $loadable->loadData();
        if( $loadable->validate() )
        {
            $active = $this->role->applyActive( $group );
            $active->save();
            $jump = $this->view->get( 'appUrl' ) . 'group';
            header( 'Location: ' . $jump );
            exit;
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