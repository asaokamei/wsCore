<?php
namespace wsModule\Dci\Web;

interface PersistInterface
{
    public function setContext( $name, $context );
    
    public function context( $name );
    
    public function run( $entity, $action=null, $form=null, $prevForm=null );
    
    public function restoreData( $name );
    
}