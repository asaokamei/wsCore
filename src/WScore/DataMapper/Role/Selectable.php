<?php
namespace WScore\DataMapper;

class Role_Selectable extends Role_Abstract
{
    /** @var string                html, form, or ...? */
    private $html_type = 'html';
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @param \WScore\Html\Selector             $selector
     * @DimInjection Get \WScore\DataMapper\EntityManager
     * @DimInjection Get \WScore\Html\Selector
     */
    public function __construct( $em, $selector )
    {
        $this->em       = $em;
        $this->selector = $selector;
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
     * pops value of the $name (property name).
     * returns html-safe value if html_type is 'html',
     * returns html form element if html_type is 'form'.
     *
     * @param string $name
     * @param null   $html_type
     * @return mixed
     */
    public function popHtml( $name, $html_type=null )
    {
        $html_type = ( $html_type ) ?: $this->html_type;
        $selector = $this->getSelInstance( $name );
        if( $selector ) {
            $html = $selector->popHtml( $html_type, $this->entity->$name );
        }
        else {
            $html = $this->selector->popHtml( 'html', $this->entity->$name );
        }
        if( $html instanceof \WScore\Html\Form ) $html->walkSetId();
        return $html;
    }

    /**
     * returns error message if any.
     *
     * @param $name
     * @return mixed
     */
    public function popError( $name ) {
        return $this->entity->_pop_error( $name );
    }

    /**
     * returns name of the property (readable for human).
     *
     * @param $name
     * @return string
     */
    public function popName( $name ) {
        return $this->model->propertyName( $name );
    }

    /**
     * returns form element object for property name.
     * the object is pooled and will be reused for model/propName basis.
     *
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
     * see the structure of array in Model::$selectors section.
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