<?php
namespace wsTests;

require_once( __DIR__ . '/../autoloader.php' );

\wsCore\Dba\Rdb::setPdoClass( '\wsTests\mockPdo' );
\wsCore\Dba\Rdb::set( 'mysql', 'db=mysql ' );
$pdo = \wsCore\Dba\Rdb::connect( 'mysql' );

var_dump( $pdo->config );
echo $pdo->exec;
echo 'hi';

