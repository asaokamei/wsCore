<?php
namespace task\views;

class taskView
{
    /** @var \wsModule\Alt\Html\View_Bootstrap */
    private $view;

    /** @var \wsCore\Html\Tags */
    private $tags;
    /**
     * @param \wsModule\Alt\Html\View_Bootstrap $view
     * @param \wsCore\Html\Tags $tags
     * @DimInjection Fresh \wsModule\Alt\Html\View_Bootstrap
     * @DimInjection Fresh \wsCore\Html\Tags
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
     * @param \wsCore\DbAccess\Role_Input $entity
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
     * @param \wsCore\DbAccess\Role_Input[] $entity
     */
    public function showForm_list( $entity )
    {
        $this->set( 'action', 'load' );
        $this->set( 'button-primary', 'confirm information' );
        $this->set( 'button-sub', 'reset' );
        $contents = array();
        $contents[] = $this->tags->h1( 'My Tasks' );
        $table = $this->tableView( $entity, 'html' );
        $contents[] = $table;
        $this->set( 'content', $contents );
    }

    /**
     * @param \wsCore\DbAccess\Role_Input $entity
     */
    public function showForm_form( $entity )
    {
        if( !$entity->isValid() ) {
            $this->set( 'alert-error', 'please submit the form again. ' );
        }
        $entity->setHtmlType( 'form' );
        $this->set( 'action', 'load' );
        $this->set( 'button-primary', 'confirm information' );
        $this->set( 'button-sub', 'reset' );
        $contents = array();
        $contents[] = $this->tags->h1( 'Details' );
        $table = $this->tableForm( $entity, 'form' );
        $contents[] = $table;
        $this->set( 'content', $contents );
    }

    /**
     * @param \wsCore\DbAccess\Role_Input $entity
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
     * @param \wsCore\DbAccess\Role_Input $entity
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
     * @param \wsCore\DbAccess\Role_Input $entity
     * @param string $type
     * @return \wsCore\Html\Tags
     */
    public function tableForm( $entity, $type='html' )
    {
        /** @var $dl \wsCore\Html\Tags */
        $entity->setHtmlType( $type );
        $dl = $this->tags->dl();
        $dl->contain_(
            $this->tags->dt( $entity->popName( 'task_memo' ) ),
            $this->tags->dd( $entity->popHtml( 'task_memo' ) ),
            $this->tags->dt( $entity->popName( 'task_date' ) ),
            $this->tags->dd( $entity->popHtml( 'task_date' ) ),
            $this->tags->dt( $entity->popName( 'task_status' ) ),
            $this->tags->dd( $entity->popHtml( 'task_status' ) )
        );
        return $dl;
    }

    /**
     * @param \wsCore\DbAccess\Role_Input[] $entity
     * @param string $type
     * @return \wsCore\Html\Tags
     */
    public function tableView( $entity, $type='html' )
    {
        $table = $this->tags->table()->_class( 'table' )->contain_(
            $this->tags->tr(
                $this->tags->th( '#' ),
                $this->tags->th( 'tasks' ),
                $this->tags->th( 'date' ),
                $this->tags->th( 'status' ),
                $this->tags->th( 'mod' )
            )
        );
        foreach( $entity as $row ) {
            $id = $row->getId();
            $row->setHtmlType( $type );
            $table->contain_(
                $this->tags->tr(
                    $this->tags->td( $row->popHtml( 'task_id' ) ),
                    $this->tags->td( $row->popHtml( 'task_memo' ) ),
                    $this->tags->td( $row->popHtml( 'task_date' ) ),
                    $this->tags->td( $row->popHtml( 'task_status' ) ),
                    $this->tags->td( $this->tags->a( 'modify' )->href( 'myTasks/task/'.$id )->_class( 'btn' ) )
                )
            );
        }
        return $table;
    }

    public function __toString()
    {
        $view = $this->view;
        ob_start();
        include( __DIR__ . '/template.php' );
        return ob_get_clean();
    }
}

