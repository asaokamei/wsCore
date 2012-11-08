<?php
namespace wsCore\DbAccess;

class Context_RoleInput implements Context_Interface
{
    /** @var \wsCore\DbAccess\EntityManager */
    private $em;

    /** @var \wsCore\DbAccess\Dao */
    private $model;

    /** @var \wsCore\DbAccess\Entity_Interface */
    private $entity;

    /** @var \wsCore\Validator\DataIO */
    private $dio;
    
    /** @var array */
    private $errors = array();

    /** @var bool */
    private $is_valid = false;

    /** @var \wsCore\Html\Selector */
    private $selector;
    
    /** @var string                html, form, or ...? */
    private $html_type = 'html';
    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\DbAccess\EntityManager    $em
     * @param \wsCore\Validator\DataIO          $dio
     * @param \wsCore\Html\Selector             $selector
     */
    public function __construct( $em, $dio, $selector )
    {
        $this->em = $em;
        $this->dio = $dio;
        $this->selector = $selector;
    }

    /**
     * @param \wsCore\DbAccess\Entity_Interface    $entity
     */
    public function register( $entity ) 
    {
        $entity = $this->em->register( $entity );
        $this->entity = $entity;
        $this->model = $this->em->getModel( $entity->_get_Model() );
    }

    /**
     * @return Entity_Interface
     */
    public function retrieve() {
        return $this->entity;
    }
    // +----------------------------------------------------------------------+
    //  get/set properties, and ArrayAccess
    // +----------------------------------------------------------------------+
    /**
     * @param null|string $name
     * @param array       $data
     * @return Context_RoleInput
     */
    public function loadData( $name=null, $data=array() )
    {
        if( is_array(  $name ) ) $data = $name;
        if( empty( $data ) ) $data = $_POST;
        // populate the input data
        $data = $this->model->protect( $data );
        foreach( $data as $key => $value ) {
            if( substr( $key, 0, 1 ) == '_' ) continue; // ignore protected/private
            $this->entity->$key = $value;
        }
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  Validating data.
    // +----------------------------------------------------------------------+
    /**
     * @return bool
     */
    public function validate()
    {
        $this->dio->source( $this->entity );
        $this->model->validate( $this->dio );
        $this->errors = $this->dio->popError();
        $this->is_valid = $this->dio->isValid();
        return $this->is_valid;
    }

    /**
     * @param bool $valid
     * @return Context_RoleInput
     */
    public function resetValidation( $valid=false ) {
        $this->is_valid = $valid;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return $this->is_valid;
    }
    // +----------------------------------------------------------------------+
    //  getting Html Forms.
    // +----------------------------------------------------------------------+
    /**
     * setter/getter for html_type to show html elements.
     *
     * @param null|string $html_type
     * @return string
     */
    public function setHtmlType( $html_type=null ) {
        if( $html_type ) $this->html_type = $html_type;
        return $this->html_type;
    }
    /**
     * @param string $name
     * @param null   $html_type
     * @return mixed
     */
    public function popHtml( $name, $html_type=null ) {
        $html_type = ( $html_type ) ?: $this->html_type;
        $selector = $this->getSelInstance( $name );
        if( $selector ) {
            $html = $selector->popHtml( $html_type, $this->entity->$name );
        }
        else {
            $html = $this->selector->popHtml( 'html', $this->entity->$name );
        }
        return $html;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function popError( $name ) {
        return $this->errors[ $name ];
    }

    /**
     * @param $name
     * @return string
     */
    public function popName( $name ) {
        return $this->model->propertyName( $name );
    }

    /**
     * @param string $name
     * @return null|object
     */
    public function getSelInstance( $name )
    {
        static $selInstances = array();
        $modelName = $this->model->getModelName();
        if( isset( $selInstances[ $modelName ][ $name ] ) ) {
            return $selInstances[ $modelName ][ $name ];
        }
        return $selInstances[ $modelName ][ $name ] = $this->getSelector( $name );
    }

    /**
     * creates selector object based on selectors array.
     * $selector[ var_name ] = [
     *     className,
     *     styleName,
     *     [ arg2, arg3, arg4 ],
     *     function( &$val ){ doSomething( $val ); },
     *   ]
     *
     * TODO: simplify or move factory to Selector. 
     * 
     * @param string $name
     * @return null|object
     */
    public function getSelector( $name )
    {
        $selector = null;
        if( $info = $this->model->getSelectInfo( $name ) ) {
            if( $info[0] == 'Selector' ) {
                $arg2     = $this->model->arrGet( $info, 2, NULL );
                $arg3     = $this->model->arrGet( $info, 3, NULL );
                $selector = $this->selector->getInstance( $info[1], $name, $arg2, $arg3 );
            }
            else {
                $class = $info[0];
                $arg1     = $this->model->arrGet( $info[1], 0, NULL );
                $arg2     = $this->model->arrGet( $info[1], 1, NULL );
                $arg3     = $this->model->arrGet( $info[1], 2, NULL );
                $selector = new $class( $name, $arg1, $arg2, $arg3 );
            }
        }
        return $selector;
    }
    // +----------------------------------------------------------------------+
}