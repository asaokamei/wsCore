<?php
namespace wsTests\DiContainer\DimpleMockDb;

class DbAccess {
    var $name ='dba at test1';
    public function dbType() {
        return $this->name;
    }
}
