<?php

namespace define {
    interface injectTestInterface {}
    class test {
        var $name ='test at test1';
        public function whoami() {
            echo $this->name;
        }
    }
}

namespace action {
    class more implements \define\injectTestInterface {
        var $test = NULL;
        function injectTest( $test ) {
            $this->test = $test;
        }
        function whoami() {
            echo $this->test->whoami();
        }
    }
    $more = new more();

    echo var_dump( inject( $more ) );

    function inject( $object )
    {
        $classes = array();
        if( $interfaces = class_implements( $object ) )
        foreach( $interfaces as $interface ) {
            if( preg_match( '/^(.*)Inject([_a-zA-Z0-9]+)Interface$/i', $interface, $matches ) )
            {
                var_dump( $matches );
                $className = $matches[1] . $matches[2];
                $injector  = "inject" . $matches[2];
                // now inject an object.
                $injObj = new $className;
                $object->$injector( $injObj );

                $result = array(
                    'namespace' => $matches[1],
                    'className' => $className,
                    'injector'  => $injector,
                );
                $classes[] = $result;
            }
        }
        return $classes;
    }

    $more->whoami();

}

