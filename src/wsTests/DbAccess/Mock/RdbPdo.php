<?php
namespace wsTests\DbAccess;

class Mock_RdbPdo
{
    var $config;
    var $exec;
    function __construct() {
        $this->config = func_get_args();
    }
    function exec( $exec ) {
        $this->exec = $exec;
    }
}
