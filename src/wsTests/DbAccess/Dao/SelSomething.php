<?php
namespace wsTests\DbAccess;

class Dao_SelSomething extends \wsCore\Html\Selector
{
    var $a, $b, $c, $d;
    function __construct( $a, $b, $c, $d ) {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
        $this->d = $d;
    }
}