<?php
namespace wsTests;

require_once( __DIR__ . '/../autoloader.php' );

\wsCore\DbAccess\Rdb::setPdoClass( '\wsTests\mockPdo' );
\wsCore\DbAccess\Rdb::set( 'mysql', 'db=mysql ' );
$pdo = \wsCore\DbAccess\Rdb::connect( 'mysql' );

var_dump( $pdo->config );
echo $pdo->exec;
echo 'hi';

