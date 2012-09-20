<?php
/*
 * trait as DI
 */

class Service1 {}

class Service2 {}

class none
{
    protected $services = array();
    public function injectServices( $service ) {
        $service->injectThis( $this );
        array_push( $this->services, $service );
    }
    public function __call( $name, $args ) {
        foreach( $this->services as $service ) {
            if( method_exists( $service, $name ) ) {
                return call_user_func_array( array( $service, $name ), $args );
            }
        }
        throw new RuntimeException( "no such method: $name. " );
    }
}

$none = new none;
$none->injectServices( new Service1() );
$none->injectServices( new Service2() );