<?php
namespace wsTests;

require_once( __DIR__ . '/../autoloader.php' );

class mockPdo
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
\wsCore\Dba\Pdo::setPdoClass( '\wsTests\mockPdo' );
\wsCore\Dba\Pdo::set( 'mysql', 'db=mysql ' );
$pdo = \wsCore\Dba\Pdo::connect( 'mysql' );

var_dump( $pdo->config );
echo $pdo->exec;
echo 'hi';

