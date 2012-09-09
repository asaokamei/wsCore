<?php
namespace wsCore\Dba;

abstract class InjectDbaAbstract implements InjectDbaInterface
{
    /** @var \wsCore\Dba\Dba */
    private $injectedDba;
    /** @var string */
    public  $classNameDba = '\wsCore\Dba\Dba';

    /**
     * injects Dba object for accessing database. 
     * if the injected $dba is a string, it is used to construct Dba object. 
     * 
     * @param $dba
     */
    public function injectDba( $dba ) 
    {
        if( is_object( $dba ) ) {
            $this->injectedDba = $dba;
        }
        elseif( is_string( $dba ) ) {
            $this->injectedDba = new $this->classNameDba( Rdb::connect( $dba ) );
        }
    }

    /**
     * @return \wsCore\Dba\Dba
     */
    public function obtainDba() {
        if( !isset( $this->injectedDba ) ) {
            $this->injectedDba = new $this->classNameDba;
        }
        return $this->injectedDba;
    }
}