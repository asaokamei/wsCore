<?php
namespace wsTests\DbAccess;

class QueryPdoMock
{
    var $config;
    var $sql, $prep, $type;
    var $stmt = 'stmt';
    function __construct() {
        $this->config = func_get_args();
    }
    function exec( $sql, $prep, $type ) {
        $this->sql = $sql;
        $this->prep = $prep;
        $this->type = $type;
    }
    function stmt() {
        return NULL;
    }
}
