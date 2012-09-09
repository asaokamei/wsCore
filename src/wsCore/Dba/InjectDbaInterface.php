<?php
namespace wsCore\Dba;

interface InjectDbaInterface
{
    public function injectDba( $dba );
    public function obtainDba();
}