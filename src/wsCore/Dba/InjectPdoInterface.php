<?php
namespace wsCore\Dba;

interface InjectPdoInterface
{
    public function injectPdo( $pdo );
    public function obtainPdo();
}