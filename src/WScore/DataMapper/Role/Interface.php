<?php
namespace WScore\DbAccess;

interface Role_Interface
{
    /**
     * @param \WScore\DbAccess\Entity_Interface    $entity
     */
    public function register( $entity );
    public function retrieve();
    public function getId();
    public function getIdName();
    public function isValid();
}