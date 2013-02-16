<?php
namespace friends\views;

use \friends\model\Contacts;

class cenaFriendsView
{
    /** @var \wsModule\Alt\Html\View_Bootstrap */
    private $view;

    /** @var \WScore\Html\Tags */
    private $tags;

    /** @var \WScore\DataMapper\Role */
    private $role;

    /** @var \wsModule\Alt\Html\Paginate */
    private $pager;

    /**
     * @param \wsModule\Alt\Html\View_Bootstrap $view
     * @param \WScore\Html\Form                 $tags
     * @param \WScore\DataMapper\Role           $role
     * @param \wsModule\Alt\Html\Paginate       $pager
     * @DimInjection Fresh \wsModule\Alt\Html\View_Bootstrap
     * @DimInjection Fresh \WScore\Html\Form
     * @DimInjection get \WScore\DataMapper\Role
     * @DimInjection get \wsModule\Alt\Html\Paginate
     */
    public function __construct( $view, $tags, $role, $pager )
    {
        $this->view = $view;
        $this->tags = $tags;
        $this->role = $role;
        $this->pager = $pager;
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
     * @param \WScore\DataMapper\Entity_Interface[] $entity
     * @param \wsModule\Alt\DbAccess\Paginate      $pager
     * @param string                               $button
     * @param string                               $uri
     */
    public function showForm_list( $entity, $pager, $button, $uri )
    {
        $appUrl   = $this->get( 'appUrl' );
        $pageUrls = $pager->setupUrls( $appUrl."?page=%d" );
        $this->set( 'title', 'My Friends' );
        $contents    = array();
        $htmlType    = $button=='save'?'form':'html';
        // pagination
        $this->pager->setUrls( $pageUrls );
        $form = $this->tags->form()->action( $uri )->method( 'post' );
        $form->_contain(
            $this->tableView( $entity, $htmlType ),
            $this->pager->bootstrap( $pageUrls ),
            $this->tags->input( 'submit', 'method', $button, array( 'class' => 'btn btn-primary') )
        );
        $contents[] = $form;
        $this->set( 'content', $contents );
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface   $entity
     * @param \WScore\DataMapper\Entity_Interface[] $contacts
     */
    public function showForm_info( $entity, $contacts )
    {
        $role        = $this->role->applyCenatar( $entity );
        $id          = $role->getId();
        $this->set( 'title', $role->popHtml( 'star' ) . $role->popHtml( 'name' ));
        $tags        = $this->tags;
        $appUrl      = $this->get( 'appUrl' );
        $editUrl     = $appUrl . $id;
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
        $form = $tags->form()->method( 'post' )->action( $editUrl );
        $form->_contain( $tags->input( 'submit', '_method', 'edit info', array( 'class'=>'btn btn-primary', 'style'=>'float:right' ) ) );
        $contents    = array();
        $contents[ ] = $form;
        $dl = $this->tags->dl()->class_( 'dl-horizontal' );
        $dl->_contain( $this->tags->dt( 'basic info' ) );
        $dl->_contain( $this->tags->dd( $this->lists( $role, array( 'gender', 'birthday' ) ) ) );
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
            $roleContacts[ $type ][] = $this->role->applyCenatar( $contact );
        }
        // -----------------------------
        // show contact for each type
        foreach( Contacts::$types as $type )
        {
            $contents[ ] = '<hr>';
            $contents[ ] = $tags->h4( $type[1] );
            if( isset( $roleContacts[ $type[0] ] ) )
            {
                $contents[] = $dl = $tags->dl()->class_( 'dl-horizontal' );
                /** @var $role \WScore\DataMapper\Role_Selectable */
                foreach( $roleContacts[ $type[0] ] as $role )
                {
                    $dl->_contain(
                        $this->tags->dt( $role->popHtml( 'label' ) ),
                        $this->tags->dd( $role->popHtml( 'info' ) )
                    );
                }
            }
            $contents[ ] = $tags->div()->style( 'clear:both' );
        }
        $this->set( 'content', $contents );
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface $entity
     * @param \WScore\DataMapper\Entity_Collection $groups
     */
    public function showForm_detail( $entity, $groups )
    {
        // -----------------------------
        // use Cenatar to generate forms.
        $contacts = $entity->relation( 'contacts' );
        $entity = $this->role->applyCenatar( $entity );
        $entity->setHtmlType( 'form' );
        // -----------------------------
        // get groups
        $select = $entity->popLinkSelect( 'groups', $groups, 'name' );
        $selGroup = $this->tags->dl(
            $this->tags->dt( 'group list' ),
            $this->tags->dd( $select )
        )->class_( 'dl-horizontal' );

        // -----------------------------
        // form basic info
        $this->set( 'title', $entity->popHtml( 'name', 'html' ) );
        $back = $this->view->get( 'appUrl' ) . $entity->getId();
        $form = $this->tags->form()->method( 'post' )->action( '' );
        $form->_contain(
            $this->dl( $entity, array( 'name', 'gender', 'birthday', 'star', 'memo' ) )->class_( 'dl-horizontal' ),
            $selGroup,
            $this->view->bootstrapButton( 'submit', 'update info', 'btn btn-primary' ),
            $this->tags->a( 'back' )->href( $back )->class_( 'btn btn-small' ),
            $this->tags->input( 'hidden', '_method', 'save' )
        );
        // -----------------------------
        // form contact info
        // organize contacts based on types
        $roleContacts = array();
        if( !empty( $contacts ) )
            foreach( $contacts as $contact ) {
                $type = $contact[ 'type' ];
                /** @var $role \WScore\DataMapper\Role_Cenatar */
                $role = $this->role->applyCenatar( $contact );
                $role->setHtmlType( 'form' );
                $roleContacts[ $type ][] = $role;
            }
        // show contact for each type
        foreach( Contacts::$types as $type )
        {
            $form->_contain( '<hr>',
                $this->tags->h4( $type[1] )
            );
            if( isset( $roleContacts[ $type[0] ] ) )
            {
                $dl = $this->tags->dl()->class_( 'dl-horizontal' );
                foreach( $roleContacts[ $type[0] ] as $role )
                {
                    $dl->_contain(
                        $this->tags->dt( $role->popHtml( 'type' )->_class( 'span1' ) ),
                        $this->tags->dd( 
                            $role->popHtml( 'label' )->_class( 'span1' ),
                            $role->popHtml( 'info'  )->_class( 'span3' ), 
                            $role->popLinkHidden( 'friend' )
                        )
                    );
                }
                $form->_contain( $dl );
            }
            $form->_contain( $this->tags->div()->style( 'clear:both' ) );
        }

        $form->_contain(
            $this->view->bootstrapButton( 'submit', 'update info', 'btn btn-primary' ),
            $this->tags->a( 'back' )->href( $back )->class_( 'btn btn-small' ),
            $this->tags->input( 'hidden', '_method', 'save' )
        );
        
        $contents    = array( $form );
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
            $sel = $this->role->applyCenatar( $group );
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
        $sel = $this->role->applyCenatar( $group );
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
                $this->tags->th( '' ),
                $this->tags->th( 'name' ),
                $this->tags->th( 'gender' ),
                $this->tags->th( 'birthday' ),
                $this->tags->th( '' )
            )
        );
        $appUrl = $this->view->get( 'appUrl' );
        foreach ( $entity as $row )
        {
            $row = $this->role->applyCenatar( $row );
            $id  = $row->getId();
            $row->setHtmlType( $type );

            /** @var $task \task\entity\task */
            if( $type == 'form' ) {
                $name   = $this->tags->span( $row->popHtml( 'name' ) )->style( 'font-weight:bold' );
            } else {
                $name   = $this->tags->a( $row->popHtml( 'name' ) )->href( $appUrl . $id )->style( 'font-weight:bold' );
            }
            $star   = $row->popHtml( 'star' );
            $button = $this->tags->a( '>>' )->href( $appUrl . $id )->class_( 'btn btn-small btn' );
            $table->_contain(
                $tr = $this->tags->tr(
                    $this->tags->td( $star ),
                    $this->tags->td( $name ),
                    $this->tags->td( $row->popHtml( 'gender' ) ),
                    $this->tags->td( $row->popHtml( 'birthday' ) ),
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
        include( __DIR__ . '/cenaTemplate.php' );
        return ob_get_clean();
    }
}