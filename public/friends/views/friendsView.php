<?php
namespace friends\views;

use \friends\model\Contacts;

class friendsView
{
    /** @var \wsModule\Alt\Html\View_Bootstrap */
    private $view;

    /** @var \WScore\Html\Tags */
    private $tags;

    /** @var \WScore\DataMapper\Role */
    private $role;

    /**
     * @param \wsModule\Alt\Html\View_Bootstrap $view
     * @param \WScore\Html\Form                 $tags
     * @param \WScore\DataMapper\Role             $role
     * @DimInjection Fresh \wsModule\Alt\Html\View_Bootstrap
     * @DimInjection Fresh \WScore\Html\Form
     * @DimInjection get \WScore\DataMapper\Role
     */
    public function __construct( $view, $tags, $role )
    {
        $this->view = $view;
        $this->tags = $tags;
        $this->role = $role;
    }

    /**
     * @return \wsModule\Alt\Html\View_Bootstrap
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * set state of the resource.
     *
     * @param $name
     * @param $value
     * @return friendsView
     */
    public function set( $name, $value )
    {
        $this->view->set( $name, $value );
        return $this;
    }

    /**
     * get state of the top resources.
     *
     * @param $name
     * @return mixed
     */
    public function get( $name )
    {
        return $this->view->get( $name );
    }

    // +----------------------------------------------------------------------+
    //  about my friends. 
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\Role_Selectable[] $entity
     */
    public function showForm_list( $entity )
    {
        $this->set( 'title', 'My Friends' );
        $contents    = array();
        $table       = $this->tableView( $entity, 'html' );
        $contents[ ] = $table;
        $this->set( 'content', $contents );
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface   $entity
     * @param \WScore\DataMapper\Entity_Interface[] $contacts
     */
    public function showForm_info( $entity, $contacts )
    {
        $role        = $this->role->applySelectable( $entity );
        $id          = $role->getId();
        $this->set( 'title', $role->popHtml( 'name' ) );
        $tags        = $this->tags;
        $appUrl      = $this->get( 'appUrl' );
        $editUrl     = $appUrl . 'detail/' . $id;
        // about groups
        $groups = $entity->relation( 'groups' );
        $groupInfo = array();
        if( empty( $groups ) ) {
            $groupInfo[] = '-no group-';
        }
        else {
            foreach( $groups as $group ) {
                $groupInfo[] = $group[ 'group_code' ];
            }
        }
        $groupInfo = implode( ', ', $groupInfo );
        // -----------------------------
        // show brief info about my friend.
        $contents    = array();
        $contents[ ] = $tags->a( 'edit info' )->href( $editUrl )->class_( 'btn btn-primary' )->style( 'float:right' );
        $dl = $this->tags->dl()->class_( 'dl-horizontal' );
        $dl->_contain( $this->tags->dt( 'basic info' ) );
        $dl->_contain( $this->tags->dd( $this->lists( $role, array( 'gender', 'birthday', 'star' ) ) ) );
        $dl->_contain( $this->tags->div()->style( 'clear:both') );
        $dl->_contain( $this->tags->dt( 'groups' ) );
        $dl->_contain( $this->tags->dd( $groupInfo ) );
        $dl->_contain( $this->tags->div()->style( 'clear:both' ) );
        $contents[] = $dl;
        // -----------------------------
        // organize contacts based on types
        $roleContacts = array();
        if( !empty( $contacts ) ) 
        foreach( $contacts as $contact ) {
            $type = $contact->type;
            $roleContacts[ $type ][] = $this->role->applySelectable( $contact );
        }
        // -----------------------------
        // show contact for each type
        foreach( Contacts::$types as $type )
        {
            $contents[ ] = '<hr>';
            $contents[ ] = $tags->a( 'add new' )
                ->href( $appUrl.'contact/' . $id . '/type/' . $type[0] )
                ->class_( 'btn btn-mini btn-info' )->style( 'float:right' );
            $contents[ ] = $tags->h4( $type[1] );
            if( isset( $roleContacts[ $type[0] ] ) )
            {
                $contents[] = $dl = $tags->dl()->class_( 'dl-horizontal' );
                /** @var $role \WScore\DataMapper\Role_Selectable */
                foreach( $roleContacts[ $type[0] ] as $role )
                {
                    $link = $appUrl . 'contact/' . $id . '/' . $role->getId();
                    $dl->_contain(
                        $this->tags->dt( $role->popHtml( 'label' ) ),
                        $this->tags->dd( $this->tags->a( $role->popHtml( 'info' ) )->href( $link ) )
                    );
                }
            }
            $contents[ ] = $tags->div()->style( 'clear:both' );
        }
        $this->set( 'content', $contents );
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface $entity
     * @param string[] $groups
     */
    public function showForm_detail( $entity, $groups )
    {
        // get groups
        $myGroup = $entity->relation( 'groups' );
        $selectedGroup = array();
        if( !empty( $myGroup ) )
        foreach( $myGroup as $grp ) {
            $selectedGroup[] = $grp->group_code;
        }
        $select = $this->tags->select( 'groups', $groups, $selectedGroup, array( 'multiple'=>true ) );
        $selGroup = $this->tags->dl(
            $this->tags->dt( 'group list' ),
            $this->tags->dd( $select )
        );
        // form basic info
        $entity = $this->role->applySelectable( $entity );
        $entity->setHtmlType( 'form' );
        $this->set( 'title', $entity->popHtml( 'name', 'html' ) );
        $back = $this->view->get( 'appUrl' ) . $entity->getId();
        $form = $this->tags->form()->method( 'post' )->action( '' );
        $form->_contain(
            $selGroup,
            $this->dl( $entity, array( 'name', 'gender', 'birthday', 'star', 'memo' ) ),
            $this->view->bootstrapButton( 'submit', 'update info', 'btn btn-primary' ),
            $this->tags->a( 'back' )->href( $back )->class_( 'btn btn-small' )
        );
        $contents    = array();
        $contents[ ] = $form;
        $this->set( 'content', $contents );
    }

    // +----------------------------------------------------------------------+
    //  about contacts
    // +----------------------------------------------------------------------+

    /**
     * @param \friends\entity\friend $friend
     * @param \friends\entity\contact $contact
     */
    public function showContact_form( $friend, $contact )
    {
        $friend_id = $friend->friend_id;
        $back = $this->view->get( 'appUrl' ) . $friend_id;
        $contact_type = $contact->type;
        $friend  = $this->role->applySelectable( $friend );
        $contact = $this->role->applySelectable( $contact );
        $contact->setHtmlType( 'form' );
        $this->set( 'title', $friend->popHtml( 'name' ) );
        $contents    = array();
        $contents[ ] = $this->lists( $friend, array( 'gender', 'birthday', 'star' ) );
        $contents[ ] = $this->tags->div()->style( 'clear:both' );
        $contents[ ] = $this->tags->h4( 'contact info for: ', $contact->popHtml( 'type', 'html' ) );
        
        $form = $this->tags->form()->action('')->method( 'post' );
        $form->_contain(
            $this->dl( $contact, array( 'type', 'label', 'info', ) ),
            $this->view->bootstrapButton( 'submit', 'save contact', 'btn btn-primary' ),
            $this->tags->a( 'back' )->href( $back )->class_( 'btn btn-small' )
        );
        $contents[ ] = $form;
        $this->set( 'content', $contents );
    }
    // +----------------------------------------------------------------------+
    //  about Groups
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\Entity_Interface $groups
     * @param $action
     */
    public function showForm_group( $groups, $action )
    {
        $this->set( 'title', 'My Groups' );
        $dl = $this->tags->dl()->class_( 'dl-horizontal');
        $dl->_contain( $this->tags->dt( 'group code:' ) );
        $dl->_contain( $this->tags->dd( 'group\'s name/description' ) );
        foreach( $groups as $group )
        {
            $sel = $this->role->applySelectable( $group );
            $link = $this->get( 'appUrl' ) . 'group/' . $sel->popHtml( 'group_code' );
            $link = $this->tags->a( $this->tags->dd( $sel->popHtml( 'name' ) ) )->href( $link );
            $dl->_contain( $this->tags->dt( $sel->popHtml( 'group_code' ) ) );
            $dl->_contain( $link );
        }
        $form = $this->tags->form()->method('post');
        $form->_contain(
            $this->tags->input( 'text', 'group_code' )->class_( 'span2' )->placeholder( 'group code' ),
            $this->tags->input( 'text', 'name' )->class_( 'span5' )->placeholder( 'group\'s name... ' ),
            '<br>',
            $this->tags->input( 'submit', 'submit', 'new group', array( 'class' => 'btn btn-primary btn-small' ) )
        );
        $contents    = array( $dl,'<hr><h4>add new group</h4>', $form );
        $this->set( 'content', $contents );
    }
    public function showForm_groupView( $group )
    {
        $sel = $this->role->applySelectable( $group );
        $sel->setHtmlType( 'form' );
        $this->set( 'title', 'My Groups' );
        $dl = $this->tags->dl();
        $dl->_contain( $this->dt( $sel, 'group_code' ) );
        $dl->_contain( $this->tags->dd( $sel->popHtml( 'group_code', 'html' ) ) );
        $dl->_contain( $this->dt( $sel, 'name' ) );
        $dl->_contain( $this->dd( $sel, 'name' ) );
        $form = $this->tags->form()->method( 'post' );
        $form->_contain( $dl );
        $form->_contain( $this->view->bootstrapButton( 'submit', 'save group', 'primary' ) );
        $form->_contain( $this->tags->a( 'back' )->href( $this->get( 'appUrl' ) . 'group' )->class_( 'btn btn-small') );
        $contents = array( $form );
        $this->set( 'content', $contents );
    }
    // +----------------------------------------------------------------------+
    //  view tools
    // +----------------------------------------------------------------------+

    /**
     * @param \WScore\DataMapper\Role_Selectable $entity
     * @param array                            $list
     * @return \WScore\Html\Tags
     */
    protected function lists( $entity, $list )
    {
        $tags = $this->tags;
        $div  = $tags->div();
        foreach ( $list as $name ) {
            $div->_contain( $tags->div( $entity->popHtml( $name ) )->style( 'float:left; margin-right: 1em; min-width:3em; ' ) );
        }
        return $div;
    }

    /**
     * @param \WScore\DataMapper\Role_Selectable $entity
     * @param array                            $list
     * @return \WScore\Html\Tags
     */
    protected function dl( $entity, $list )
    {
        $tags = $this->tags;
        $dl   = $tags->dl();
        foreach ( $list as $name ) {
            $dl->_contain( $this->dt( $entity, $name ) );
            $dl->_contain( $this->dd( $entity, $name ) );
        }
        return $dl;
    }

    /**
     * @param \WScore\DataMapper\Role_Selectable $entity
     * @param                                  $name
     * @return \WScore\Html\Tags
     */
    protected function dt( $entity, $name )
    {
        return $this->tags->dt( $entity->popName( $name ) );
    }

    /**
     * @param \WScore\DataMapper\Role_Selectable $entity
     * @param                                  $name
     * @return \WScore\Html\Tags
     */
    protected function dd( $entity, $name )
    {
        return $this->tags->dd(
            $entity->popHtml( $name ),
            $this->tags->div()->style( 'clear:both' ),
            $this->tags->span( $entity->popError( $name ) )->class_( 'formError' )
        );
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface[] $entity
     * @param string                             $type
     * @return \WScore\Html\Tags
     */
    public function tableView( $entity, $type = 'html' )
    {
        $table  = $this->tags->table()->class_( 'table' )->_contain(
            $this->tags->tr(
                $this->tags->th( ' ' ),
                $this->tags->th( 'name' ),
                $this->tags->th( 'group' ),
                $this->tags->th( '' )
            )
        );
        $appUrl = $this->view->get( 'appUrl' );
        foreach ( $entity as $row )
        {
            $groups = $row->relation( 'groups' );
            $row = $this->role->applySelectable( $row );
            $id  = $row->getId();
            $row->setHtmlType( $type );

            /** @var $task \task\entity\task */
            $memo   = $this->tags->a( $row->popHtml( 'name' ) )->href( $appUrl . $id )->style( 'font-weight:bold' );
            $star   = $row->popHtml( 'star' );
            $groupInfo = array();
            if( empty( $groups ) ) {
                $groupInfo[] = '--';
            }
            else {
                foreach( $groups as $group ) {
                    $groupInfo[] = $group->group_code;
                }
            }
            $groupInfo = implode( ', ', $groupInfo );
            $button = $this->tags->a( '>>' )->href( $appUrl . $id )->class_( 'btn btn-small btn' );
            $table->_contain(
                $tr = $this->tags->tr(
                    $this->tags->td( $star ),
                    $this->tags->td( $memo ),
                    $this->tags->td( $groupInfo ),
                    $this->tags->td( $button )
                )
            );
        }
        return $table;
    }


    public function showSetup()
    {
        /** @var $form \WScore\Html\Tags */
        $this->set( 'title', 'Confirm Initializing Tasks' );
        $check = $this->tags->checkLabel( 'initDb', 'yes', 'check this box and click initialize button' );
        $form  = $this->tags->form()->method( 'post' )->action( '' );
        $form->_contain(
            $this->tags->p( 'really initialize database?' ),
            $this->tags->p( 'all the current tasks will be removed...' ),
            $check,
            '<br />',
            $this->view->bootstrapButton( 'submit', 'initialize', 'primary' )
        );
        $this->set( 'content', $form );
    }

    public function __toString()
    {
        $view = $this->view;
        ob_start();
        include( __DIR__ . '/template.php' );
        return ob_get_clean();
    }
}