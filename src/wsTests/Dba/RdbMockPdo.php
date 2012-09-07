<?php
namespace wsTests\Dba;

require_once( __DIR__ . '/../autoloader.php' );

class RdbMockPdo
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
