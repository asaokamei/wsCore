<?php
namespace wsModule\Templates;

/**
 * Template engine using pure PHP code.
 */
class Template
{
    /** specify self rendering mode.  */
    const SELF = '*self*';

    /** @var string  */
    protected $templateFile;
    
    /** @var Template */
    protected $outerTemplate = null;
    
    /** @var array */
    protected $data = array();

    // +----------------------------------------------------------------------+
    /**
     */
    public function __construct()
    {
    }

    public function setTemplate( $name ) {
        $this->templateFile = $name;
    }
    // +----------------------------------------------------------------------+
    //  setting values. 
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set( $name, $value ) {
        $this->data[ $name ] = $value;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set( $name, $value ) {
        $this->set( $name, $value );
    }

    /**
     * mass assign data.
     * 
     * @param array $data
     */
    public function assign( $data ) {
        $this->data = array_merge( $this->data, $data );
    }

    /**
     * sets parent/outer template for the current template.
     * 
     * @param string $parentTemplate
     */
    public function parent( $parentTemplate ) {
        $this->outerTemplate = clone $this;
        $this->outerTemplate->setTemplate( $parentTemplate );
    }

    // +----------------------------------------------------------------------+
    //  getting values. 
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     * @param mixed  $default
     * @return null|mixed
     */
    public function get( $name, $default=null ) {
        list( $name, $filters ) = $this->parse( $name );
        if( !array_key_exists( $name, $this->data ) ) return $default;
        return $this->filter( $this->data[ $name ], $filters );
    }

    /**
     * @param $method
     * @param $args
     * @return mixed|null
     */
    public function __call( $method, $args ) {
        $name = array_shift( $args );
        list( $name, $filters ) = $this->parse( $name );
        if( !$value = $this->get( $name ) ) return $value;
        return $this->filter( $value, $filters, $method );
    }

    /**
     * @param $name
     * @return array
     */
    protected function parse( $name ) {
        $list = explode( '|', $name );
        $name = array_shift( $list );
        return array( $name, $list );
    }

    /**
     * @param        $value
     * @param        $filters
     * @param string $method
     * @return mixed
     */
    protected function filter( $value, $filters, $method='' ) 
    {
        // check if $method maybe a filter name in basic filters. 
        $defaultClass = __NAMESPACE__ . '\Filter_Basic';
        $classes = array( $defaultClass );
        if( $method && method_exists( $classes[0], $method ) ) {
            $value = $defaultClass::$method( $value );
        }
        // check for filter maybe in Filter_$method class. 
        if( empty( $filters ) ) return $value;
        if( $method ) $classes[] = __NAMESPACE__ . '\Filter_' . ucwords( $method );
        foreach( $filters as $f ) {
            foreach( $classes as $c ) {
                if( method_exists( $c, $f ) ) {
                    $value = $c::$f( $value );
                    break;
                }
            }
        }
        return $value;
    }
    
    /**
     * html safe get.
     * 
     * @param      $name
     * @param null $default
     * @return string
     */
    public function safe( $name, $default=null ) {
        $html = $this->get( $name, $default );
        return Filter_Basic::h( $html );
    }

    /**
     * html safe get. 
     * 
     * @param $name
     * @return string
     */
    public function __get( $name ) {
        return $this->safe( $name );
    }

    /**
     * @param string $name
     * @param array|mixed $default
     * @return mixed|null
     */
    public function arr( $name, $default=array() ) {
        return $this->get( $name, $default );
    }

    // +----------------------------------------------------------------------+
    //  rendering the template. 
    // +----------------------------------------------------------------------+
    /**
     * @param mixed  $template
     * @param array  $parameters
     * @throws \RuntimeException
     * @return mixed
     */
    public function render( $template, $parameters = array() )
    {
        // attach the global variables
        if( $this->templateFile == self::SELF ) {
            $content = ob_get_clean();
        }
        else {
            $content = $this->evaluate( $template, $parameters );
        }

        if( isset( $this->outerTemplate ) ) {
            $this->set( 'content', $content );
            $this->outerTemplate->assign( $this->data );
            $content = (string) $this->outerTemplate;
        }

        return $content;
    }

    /**
     * rendering output from own php file.
     */
    public function renderSelf() {
        ob_start();
        $this->templateFile = self::SELF;
    }

    /**
     * Evaluates a template.
     *
     * @param string  $template   The template to render
     * @param array   $parameters An array of parameters to pass to the template
     * @return string|bool The evaluated template, or false if the engine is unable to render the template
     */
    protected function evaluate( $template, array $parameters = array())
    {
        $parameters[ '__template__' ] = $template;
        $parameters[ '_v' ] = $this;
        $this->assign( $parameters );

        /** @var $__template__ string */
        extract( $parameters, EXTR_SKIP );
        ob_start();
        require $__template__;

        return ob_get_clean();
    }

    /**
     * @return mixed
     */
    public function __toString() {
        return $this->render( $this->templateFile, $this->data );
    }

    /**
     * render output if rendering self output.
     */
    public function __destruct()
    {
        if( $this->templateFile == self::SELF ) {
            echo $this->render( $this->templateFile );
        }
    }
    // +----------------------------------------------------------------------+
}