<?php
namespace wsCore\DbAccess;

interface Relation_Interface
{
    public function set( $target );
    public function del( $target );
    public function get();
}