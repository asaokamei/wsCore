<?php
namespace wsTests\DiContainer\DimpleMockBiz;

class Invoice implements \wsTests\DiContainer\DimpleMockDb\injectDbAccessInterface
{
    /** @var \Database\DbAccess */
    var $dba = NULL;

    /**
     * @DimInjection New \wsTests\DiContainer\DimpleMockDb\DbAccess
     * @param $dba
     */
    public function __construct( $dba ) {
        $this->dba = $dba;
    }
    function injectDbAccess( $dba ) {
        $this->dba = $dba;
    }
    function showDbType() {
        return $this->dba->dbType();
    }
}
