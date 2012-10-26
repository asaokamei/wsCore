<?php
namespace wsTests\DbAccess;

class Mock_PdObjectDao {
    protected $data = array();
    protected $constructed = FALSE;
    protected $id_name = NULL;
    protected $id = NULL;
    public function __construct( $arg1 ) {
        $this->constructed = $arg1;
        $this->id_name = 'id';
    }
    public function __set( $name, $value ) {
        if( $name == $this->id_name ) {
            $this->id = $value;
        }
        else {
            $this->data[ $name ] = $value;
        }
    }
    public function getConstructed() {
        return $this->constructed;
    }
}

