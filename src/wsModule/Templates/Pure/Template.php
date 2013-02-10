<?php
namespace wsModule\Templates\Pure;

/**
 * Template engine.
 * many codes copied from Symfony 2... bad practice...
 */
class Template 
{
    /** @var Template */
    protected $outerTemplate = null;
    
    protected $data = array();

    /**
     * @param mixed  $name
     * @param array  $parameters
     * @throws \RuntimeException
     * @return mixed
     */
    public function render( $name, $parameters = array() )
    {
        // attach the global variables
        if (false === $content = $this->evaluate( $name, $parameters ) ) {
            throw new \RuntimeException(sprintf('The template "%s" cannot be rendered.', $name ) );
        }

        if( isset( $this->outerTemplate ) ) {
            $this->outerTemplate;
        }

        return $content;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set( $name, $value ) {
        $this->data[ $name ] = $value;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return null|mixed
     */
    public function get( $name, $default=null ) {
        return array_key_exists( $name, $this->data ) ? $this->data[ $name ] : $default;
    }

    /**
     * @param string $name
     * @param array|mixed $default
     * @return mixed|null
     */
    public function getArr( $name, $default=array() ) {
        return $this->get( $name, $default );
    }

    /**
     * Evaluates a template.
     *
     * @param string  $template   The template to render
     * @param array   $parameters An array of parameters to pass to the template
     *
     * @throws \InvalidArgumentException
     * @return string|bool The evaluated template, or false if the engine is unable to render the template
     */
    protected function evaluate( $template, array $parameters = array())
    {
        $__template__ = $template;

        if (isset( $parameters[ '__template__' ] ) ) {
            throw new \InvalidArgumentException('Invalid parameter (__template__)');
        }

        extract($parameters, EXTR_SKIP);
        $view = $this;
        ob_start();
        require $__template__;

        return ob_get_clean();
    }
}