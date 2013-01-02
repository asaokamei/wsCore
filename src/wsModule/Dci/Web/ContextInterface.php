<?php
namespace wsModule\Alt\Dci\Web;

interface ContextInterface
{
    public function setContext( $name, $context );
    
    public function context( $name );
    
    public function run( $entity, $action=null, $form=null, $prevForm=null );
    
    public function restoreData( $name );
    
}