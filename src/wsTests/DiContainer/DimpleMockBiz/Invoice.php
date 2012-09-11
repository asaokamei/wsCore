<?php
namespace wsTests\DiContainer\DimpleMockBiz;

class Invoice implements \wsTests\DiContainer\DimpleMockDb\injectDbAccessInterface
{
    /** @var \Database\DbAccess */
    var $dba = NULL;
    function injectDbAccess( $dba ) {
        $this->dba = $dba;
    }
    function showDbType() {
        return $this->dba->dbType();
    }
}
