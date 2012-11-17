<?php
namespace friends\views;

class friendsView
{
    /** @var \wsModule\Alt\Html\View_Bootstrap */
    private $view;

    /** @var \WScore\Html\Tags */
    private $tags;
    /**
     * @param \wsModule\Alt\Html\View_Bootstrap $view
     * @param \WScore\Html\Form $tags
     * @DimInjection Fresh \wsModule\Alt\Html\View_Bootstrap
     * @DimInjection Fresh \WScore\Html\Form
     */
    public function __construct( $view, $tags ) {
        $this->view = $view;
        $this->tags = $tags;
    }

    /**
     * @return \wsModule\Alt\Html\View_Bootstrap
     */
    public function getView() {
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
    public function get( $name ) {
        return $this->view->get( $name );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     * @param string $form
     * @return void
     */
    public function showForm( $entity, $form )
    {
        $this->set( 'currAction', $form );
        $this->set( 'entity', $entity );
        $this->set( 'title', $form );
        $show = 'showForm_' . $form;
        $this->$show( $entity );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable[] $entity
     */
    public function showForm_list( $entity )
    {
        $this->set( 'action', 'load' );
        $this->set( 'title', 'My Friends' );
        $contents = array();
        $table = $this->tableView( $entity, 'html' );
        $contents[] = $table;
        $this->set( 'content', $contents );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable[] $entity
     * @param string $type
     * @return \WScore\Html\Tags
     */
    public function tableView( $entity, $type='html' )
    {
        $table = $this->tags->table()->_class( 'table' )->contain_(
            $this->tags->tr(
                $this->tags->th( 'name' ),
                $this->tags->th( 'star' ),
                $this->tags->th( '' )
            )
        );
        $appUrl = $this->view->get( 'appUrl' );
        foreach( $entity as $row ) 
        {
            $id = $row->getId();
            $row->setHtmlType( $type );

            /** @var $task \task\entity\task */
            $memo   = $this->tags->a( $row->popHtml( 'name' ) )->href( $appUrl . $id )->style( 'font-weight:bold');
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


    public function showSetup() {
        /** @var $form \WScore\Html\Tags */
        $this->set( 'title', 'Confirm Initializing Tasks' );
        $check = $this->tags->checkLabel( 'initDb', 'yes', 'check this box and click initialize button' );
        $form = $this->tags->form()->method( 'post' )->action( '' );
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
    }}