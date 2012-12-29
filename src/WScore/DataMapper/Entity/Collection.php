<?php
namespace WScore\DataMapper;

class Entity_Collection
{
    protected $entities = array();
    
    public function __construct( $entities=array() ) {
        $this->entities = $entities;
    }
    public function collection( $entities=array() ) {
        $this->entities = $entities;
    }
}