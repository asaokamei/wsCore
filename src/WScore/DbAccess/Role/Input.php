<?php
namespace WScore\DbAccess;

class Role_Input extends Role_Abstract
{
    /** @var string                html, form, or ...? */
    private $html_type = 'html';
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\EntityManager    $em
     * @param \WScore\Validator\DataIO          $dio
     * @param \WScore\Html\Selector             $selector
     */
    public function __construct( $em, $dio, $selector )
    {
        $this->em = $em;
        $this->dio = $dio;
        $this->selector = $selector;
    }

    // +----------------------------------------------------------------------+
    //  get/set properties, and ArrayAccess
    // +----------------------------------------------------------------------+
    /**
     * @param null|string $name
     * @param array       $data
     * @return Role_Input
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
     * @param null|string $loadName
     * @return bool
     */
    public function validate( $loadName=null )
    {
        $this->dio->source( $this->entity );
        $list = $this->model->getPropertyList( $loadName );
        foreach( $list as $propName => $name ) {
            $validateInfo = $this->model->getValidateInfo( $propName );
            $type   = array_key_exists( 0, $validateInfo ) ? $validateInfo[0] : null ;
            $filter = array_key_exists( 1, $validateInfo ) ? $validateInfo[1] : '' ;
            $this->dio->push( $propName, $type, $filter );
        }
        $this->em->setEntityProperty( $this->entity, 'errors',  $this->dio->popError() );
        $this->em->setEntityProperty( $this->entity, 'isValid', $this->dio->isValid() );
        return $this->entity->_is_valid();
    }

    /**
     * @param bool $valid
     * @return Role_Input
     */
    public function resetValidation( $valid=false ) {
        $this->em->setEntityProperty( $this->entity, 'isValid', $valid );
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return $this->entity->_is_valid();
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
        return $this->entity->_pop_error( $name );
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
                $arg2     = $this->model->arrGet( $info, 2, null );
                $extra    = $this->model->arrGet( $info, 3, null );
                $arg3 = $this->model->arrGet( $info, 'items',  array() );
                $arg4 = $this->model->arrGet( $info, 'filter', null );
                if( is_array( $extra ) && !empty( $extra ) ) {
                    $arg3 = $this->model->arrGet( $extra, 'items',  array() );
                    $arg4 = $this->model->arrGet( $extra, 'filter', null );
                }
                $selector = $this->selector->getInstance( $info[1], $name, $arg2, $arg3, $arg4 );
            }
            else {
                $class = $info[0];
                $arg1     = $this->model->arrGet( $info[1], 0, null );
                $arg2     = $this->model->arrGet( $info[1], 1, null );
                $arg3     = $this->model->arrGet( $info[1], 2, null );
                $selector = new $class( $name, $arg1, $arg2, $arg3 );
            }
        }
        return $selector;
    }
    // +----------------------------------------------------------------------+
}