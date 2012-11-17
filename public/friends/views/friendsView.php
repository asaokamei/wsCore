<?php
namespace friends\views;

use \friends\model\Contacts;

class friendsView
{
    /** @var \wsModule\Alt\Html\View_Bootstrap */
    private $view;

    /** @var \WScore\Html\Tags */
    private $tags;

    /** @var \WScore\DbAccess\Role */
    private $role;

    /**
     * @param \wsModule\Alt\Html\View_Bootstrap $view
     * @param \WScore\Html\Form                 $tags
     * @param \WScore\DbAccess\Role             $role
     * @DimInjection Fresh \wsModule\Alt\Html\View_Bootstrap
     * @DimInjection Fresh \WScore\Html\Form
     * @DimInjection get \WScore\DbAccess\Role
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
     * @param \WScore\DbAccess\Role_Selectable[] $entity
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
     * @param \WScore\DbAccess\Role_Selectable $entity
     */
    public function showForm_brief( $entity )
    {
        $role        = $this->role->applySelectable( $entity );
        $id          = $role->getId();
        $this->set( 'title', $role->popHtml( 'name' ) );
        $tags        = $this->tags;
        $appUrl      = $this->get( 'appUrl' );
        $editUrl     = $appUrl . 'detail/' . $id;
        $contents    = array();
        $contents[ ] = $this->lists( $role, array( 'gender', 'birthday', 'star' ) );
        $contents[ ] = $tags->a( 'edit' )->href( $editUrl )->_class( 'btn' )->style( 'float:right' );
        $contents[ ] = $tags->div()->style( 'clear:both' );
        foreach( Contacts::$types as $type ) {
            $contents[ ] = $tags->h4( $type[1] );
            $contents[ ] = $tags->a( 'add new' )
                ->href( $appUrl.'contact/' . $id . '/type/' . $type[0] )
                ->_class( 'btn' )->style( 'float:right' );
            $contents[ ] = $tags->div()->style( 'clear:both' );
        }
        $this->set( 'content', $contents );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     */
    public function showForm_detail( $entity )
    {
        $entity = $this->role->applySelectable( $entity );
        $entity->setHtmlType( 'form' );
        $this->set( 'title', $entity->popHtml( 'name' ) );
        $contents    = array();
        $contents[ ] = $this->dl( $entity, array( 'gender', 'birthday', 'star', 'memo' ) );
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
        $contact_type = $contact->type;
        $friend  = $this->role->applySelectable( $friend );
        $contact = $this->role->applySelectable( $contact );
        $contact->setHtmlType( 'form' );
        $this->set( 'title', $friend->popHtml( 'name' ) );
        $contents    = array();
        $contents[ ] = $this->lists( $friend, array( 'gender', 'birthday', 'star' ) );
        $contents[ ] = $this->tags->div()->style( 'clear:both' );
        $contents[ ] = $this->tags->h4( 'add contact for: ' . $contact->popHtml( 'type', 'html' ) );
        
        $appUrl      = $this->get( 'appUrl' );
        $postUrl     = $appUrl . "contact/{$friend_id}/type/{$contact_type}";
        $form = $this->tags->form()->action( $postUrl )->method( 'post' );
        $form->contain_(
            $this->dl( $contact, array( 'info', 'type', 'label' ) ),
            $this->view->bootstrapButton( 'submit', 'add new contact', 'btn btn-primary' )
        );
        $contents[ ] = $form;
        $this->set( 'content', $contents );
    }
    // +----------------------------------------------------------------------+
    //  view tools
    // +----------------------------------------------------------------------+

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     * @param array                            $list
     * @return \WScore\Html\Tags
     */
    protected function lists( $entity, $list )
    {
        $tags = $this->tags;
        $div  = $tags->div();
        foreach ( $list as $name ) {
            $div->contain_( $tags->div( $entity->popHtml( $name ) )->style( 'float:left; margin-right: 1em;' ) );
        }
        return $div;
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     * @param array                            $list
     * @return \WScore\Html\Tags
     */
    protected function dl( $entity, $list )
    {
        $tags = $this->tags;
        $dl   = $tags->dl();
        foreach ( $list as $name ) {
            $dl->contain_( $this->dt( $entity, $name ) );
            $dl->contain_( $this->dd( $entity, $name ) );
        }
        return $dl;
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     * @param                                  $name
     * @return \WScore\Html\Tags
     */
    protected function dt( $entity, $name )
    {
        return $this->tags->dt( $entity->popName( $name ) );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     * @param                                  $name
     * @return \WScore\Html\Tags
     */
    protected function dd( $entity, $name )
    {
        return $this->tags->dd(
            $entity->popHtml( $name ), '<br />',
            $this->tags->span( $entity->popError( $name ) )->_class( 'formError' )
        );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable[] $entity
     * @param string                             $type
     * @return \WScore\Html\Tags
     */
    public function tableView( $entity, $type = 'html' )
    {
        $table  = $this->tags->table()->_class( 'table' )->contain_(
            $this->tags->tr(
                $this->tags->th( 'name' ),
                $this->tags->th( 'star' ),
                $this->tags->th( '' )
            )
        );
        $appUrl = $this->view->get( 'appUrl' );
        foreach ( $entity as $row ) {
            $row = $this->role->applySelectable( $row );
            $id  = $row->getId();
            $row->setHtmlType( $type );

            /** @var $task \task\entity\task */
            $memo   = $this->tags->a( $row->popHtml( 'name' ) )->href( $appUrl . $id )->style( 'font-weight:bold' );
            $star   = $row->popHtml( 'star' );
            $button = $this->tags->a( '>>' )->href( $appUrl . $id )->_class( 'btn btn-small btn' );
            $table->contain_(
                $tr = $this->tags->tr(
                    $this->tags->td( $memo ),
                    $this->tags->td( $star ),
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
        $form->contain_(
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