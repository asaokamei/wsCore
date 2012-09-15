<?php

/*

Example of simple automatic dependency injection using only interface. 

this code is equivalent to:

$service = new \MyService\Service;
$invoice = new \Application\Invoice;
$invoice->injectService( $service );
$invoice->doIt();

*/

namespace MyService {
    interface injectServiceInterface {}
    class Service {
        public function doIt() {
            return 'done that.';
        }
    }
}

namespace Application {
    class Invoice implements \MyService\injectServiceInterface
    {
        /** @var \MyService\Service */
        var $service = NULL;
        function injectService( $service ) {
            $this->service = $service;
        }
        function doIt() {
            return $this->service->doIt();
        }
    }
}

namespace main
{
    $invoice = new \Application\Invoice();
    inject( $invoice );
    echo $invoice->doIt(); // shows 'done that.'

    function inject( $object )
    {
        if( !$interfaces = class_implements( $object ) ) return;
        foreach( $interfaces as $interface )  // get a list of interfaces.
        {
            if( preg_match( '/^(.*)Inject([_a-zA-Z0-9]+)Interface$/i',
                $interface, $matches ) )
            {
                $className = $matches[1] . $matches[2]; // class to inject. 
                $injector  = "inject" . $matches[2];    // method to inject. 
                $injObj = new $className;               // create an object. 
                $object->$injector( $injObj );          // and inject it!
            }
        }
    }
}