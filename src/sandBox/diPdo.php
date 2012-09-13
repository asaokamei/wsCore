<?php

namespace
{
    // to inject \Pdo, a global, PHP's standard object.
    interface injectPdoInterface {}

    /*
     * must set Pdo in the Dimplet container, i.e.
     *   $container->set( 'Pdo', function($c) {
     *     return new \Pdo( 'mysql:dbname=test', $user, $pass );
     *   } );
     */
}

namespace business
{
    class Invoice implements \injectPdoInterface
    {
        /** @var \Pdo */
        var $dba = NULL;
        function injectPdo( $dba ) {
            $this->dba = $dba;
        }
        function showDbType() {
            echo $this->dba->name;
        }
    }
}

namespace main
{
    require_once( __DIR__ . '/../wsCore/DiContainer/Dimplet.php' );
    $container = new \wsCore\DiContainer\Dimplet();

    // set Pdo in the container.
    $container->set( 'Pdo', function($c) {
        $pdo = new \stdclass();
        $pdo->name = 'Pdo by stdClass';
        return $pdo;
    } );

    // build Invoice.
    $invoice = $container->get( '\business\Invoice' );
    /** @var $invoice \business\Invoice */
    $invoice->showDbType(); // should show 'Pdo by stdClass'
}

