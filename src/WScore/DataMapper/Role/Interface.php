<?php
namespace WScore\DataMapper;

interface Role_Interface
{
    /**
     * @param \WScore\DataMapper\Entity_Interface    $entity
     */
    public function register( $entity );
    public function retrieve();
    public function getId();
    public function getIdName();
    public function isValid();
}