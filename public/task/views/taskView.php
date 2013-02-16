<?php
namespace task\views;

class taskView
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
     * @return taskView
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
        $this->set( 'title', 'My Tasks' );
        $contents = array();
        $table = $this->tableView( $entity, 'html' );
        $contents[] = $table;
        $this->set( 'content', $contents );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     * @param null|string $submitTitle
     */
    public function showForm_form( $entity, $submitTitle=null )
    {
        $submitTitle = $submitTitle ?: 'save task';
        $entity->setHtmlType( 'form' );
        $contents = array();
        $form = $this->tags->form(
            $this->tableForm( $entity, 'form' ),
            $this->view->bootstrapButton( 'submit', $submitTitle, 'primary' ),
            $this->view->bootstrapButton( 'reset', 'reset','' )
        )->method( 'post' )->action( '' );
        $contents[] = $form;
        $this->set( 'content', $contents );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     */
    public function showForm_confirm( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'confirm' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Confirmation of Inputs' );
        $this->set( 'action', 'save' );
        $this->set( 'button-primary', 'save the information' );
        $this->set( 'button-sub', 'back' );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     */
    public function showForm_done( $entity )
    {
        $entity->setHtmlType( 'html' );
        $this->set( 'currAction', 'done' );
        $this->set( 'entity', $entity );
        $this->set( 'title', 'Completed' );
        $this->set( 'action', 'done' );
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable $entity
     * @param string $type
     * @return \WScore\Html\Tags
     */
    public function tableForm( $entity, $type='html' )
    {
        /** @var $dl \WScore\Html\Tags */
        $entity->setHtmlType( $type );
        $dl = $this->tags->dl();
        $dl->_contain(
            $this->tags->dt( $entity->popName( 'task_memo' ) ),
            $this->tags->dd(
                $entity->popHtml( 'task_memo' ), '<br />',
                $this->tags->span( $entity->popError( 'task_memo' ) )->class_( 'formError' )
            ),
            $this->tags->dt( $entity->popName( 'task_date' ) ),
            $this->tags->dd(
                $entity->popHtml( 'task_date' ), '<br />',
                $this->tags->span( $entity->popError( 'task_date' ) )->class_( 'formError' )
            ),
            $this->tags->dt( $entity->popName( 'task_status' ) ),
            $this->tags->dd(
                $entity->popHtml( 'task_status' ), '<br />',
                $this->tags->span( $entity->popError( 'task_status' ) )->class_( 'formError' )
            )
        );
        return $dl;
    }

    /**
     * @param \WScore\DbAccess\Role_Selectable[] $entity
     * @param string $type
     * @return \WScore\Html\Tags
     */
    public function tableView( $entity, $type='html' )
    {
        $table = $this->tags->table()->class_( 'table' )->_contain(
            $this->tags->tr(
                $this->tags->th( 'task description' ),
                $this->tags->th( 'date' ),
                $this->tags->th( 'done' )
            )
        );
        $taskUrl = $this->view->get( 'taskUrl' );
        foreach( $entity as $row ) {
            $id = $row->getId();
            $row->setHtmlType( $type );

            /** @var $task \task\entity\task */
            $task = $row->retrieve();
            if( $task->isDone() ) {
                $memo   = $this->tags->a( $row->popHtml( 'task_memo' ) )->href( $taskUrl . 'task/'.$id )->style( 'color:#669999');
                $button = $this->tags->a( 'delete' )->href( $taskUrl . 'done/'.$id )->class_( 'btn btn-small btn' );
            }
            else {
                $memo   = $this->tags->a( $row->popHtml( 'task_memo' ) )->href( $taskUrl . 'task/'.$id )->style( 'font-weight:bold' );
                $button = $this->tags->a( 'done' )->href( $taskUrl . 'done/'.$id )->class_( 'btn btn-small btn-primary' );
            }
            $table->_contain(
                $tr = $this->tags->tr(
                    $this->tags->td( $memo ),
                    $this->tags->td( $row->popHtml( 'task_date' ) ),
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

