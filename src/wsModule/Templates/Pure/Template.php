<?php
namespace wsModule\Templates\Pure;

/**
 * Template engine.
 * many codes copied from Symfony 2... bad practice...
 */
class Template
{
    /** @var string  */
    protected $templateFile;
    
    /** @var Template */
    protected $outerTemplate = null;
    
    /** @var array */
    protected $data = array();

    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     */
    public function __construct( $name )
    {
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
        $this->outerTemplate = new static( $parentTemplate );
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
        return array_key_exists( $name, $this->data ) ? $this->data[ $name ] : $default;
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
        return $html = htmlspecialchars( $html, ENT_QUOTES, 'UTF-8' );
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
        $content = $this->evaluate( $template, $parameters );

        if( isset( $this->outerTemplate ) ) {
            $this->set( 'content', $content );
            $this->outerTemplate->assign( $this->data );
            $content = (string) $this->outerTemplate;
        }

        return $content;
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
        if( isset( $parameters[ '__template__' ] ) ) unset( $parameters[ '__template__' ] );
        $__template__ = $template;

        extract($parameters, EXTR_SKIP);
        $view = $this;
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
    // +----------------------------------------------------------------------+
}