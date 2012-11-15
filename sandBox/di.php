<?php

namespace Database {
    interface injectDbAccessInterface {}
    class DbAccess {
        var $name ='dba at test1';
        public function dbType() {
            return $this->name;
        }
    }
}

namespace action {
    class Invoice implements \Database\injectDbAccessInterface
    {
        /** @var \Database\DbAccess */
        var $dba = NULL;
        function injectDbAccess( $dba ) {
            $this->dba = $dba;
        }
        function showDbType() {
            echo $this->dba->dbType();
        }
    }
}

namespace main
{
    require_once( '../src/WScore/DiContainer/Dimplet.php' );
    $container = new \wsCore\DiContainer\Dimplet();
    $more = $container->get( '\action\Invoice' );

    /** @var $more \action\Invoice */
    $more->showDbType();

}

