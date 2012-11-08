<?php
namespace wsCore\DbAccess;

interface Context_Interface
{
    /**
     * @param \wsCore\DbAccess\Entity_Interface    $entity
     */
    public function register( $entity );
    public function retrieve();
}