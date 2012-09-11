<?php
namespace wsTests\DbAccess;

class SqlMockDba
{
    var $config;
    var $sql, $prep;
    var $stmt = 'stmt';
    function __construct() {
        $this->config = func_get_args();
    }
    function execSQL( $sql, $prep ) {
        $this->sql = $sql;
        $this->prep = $prep;
    }
    function stmt() {
        return NULL;
    }
}
