<?php
namespace wsModule\Templates\Pure;

class SelfTemplate extends Template
{
    // +----------------------------------------------------------------------+
    /**
     * @param string $name
     */
    public function __construct( $name=null )
    {
        $this->templateFile = $name;
        ob_start();
    }

    public function __destruct()
    {
        $content = ob_get_clean();

        if( isset( $this->outerTemplate ) ) {
            $this->set( 'content', $content );
            $this->outerTemplate->assign( $this->data );
            $content = (string) $this->outerTemplate;
        }

        echo $content;
    }
}