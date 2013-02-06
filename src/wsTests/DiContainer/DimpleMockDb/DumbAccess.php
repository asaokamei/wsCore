<?php
namespace wsTests\DiContainer\DimpleMockDb;

class DumbAccess {
    var $name ='dumb access';
    public function dbType() {
        return $this->name;
    }
}
