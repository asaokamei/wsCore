<?php
namespace wsTests;

require_once( __DIR__ . '/../autoloader.php' );

\WScore\DbAccess\Rdb::setPdoClass( '\wsTests\mockPdo' );
\WScore\DbAccess\Rdb::set( 'mysql', 'db=mysql ' );
$pdo = \WScore\DbAccess\Rdb::connect( 'mysql' );

var_dump( $pdo->config );
echo $pdo->exec;
echo 'hi';

