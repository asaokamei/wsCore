<?php
namespace wsCore\Dba;

abstract class InjectPdoAbstract implements InjectPdoInterfaceInterface
{
    /** @var \Pdo */
    private $injectedPdo;
    /** @var string */
    public  $classNamePdoFactory = '\wsCore\Dba\Rdb';

    /**
     * inject $pdo object. if $pdo is a string, it is used to factory Pdo using
     * PdoFactory (\wsCore\Dba\Rdb). 
     * 
     * @param $pdo
     */
    public function injectDba( $pdo )
    {
        if( is_object( $pdo ) ) {
            $this->injectedPdo = $pdo;
        }
        elseif( is_string( $pdo ) ) {
            /** @var $PdoFactory \wsCore\Dba\Rdb */
            $PdoFactory = $this->classNamePdoFactory;
            $this->injectedPdo = $PdoFactory::connect( $pdo );
        }
    }

    /**
     * 
     * @return \Pdo
     */
    public function obtainPdo() {
        if( !isset( $this->injectedPdo ) ) {
            /** @var $PdoFactory \wsCore\Dba\Rdb */
            $PdoFactory = $this->classNamePdoFactory;
            $this->injectedPdo = $PdoFactory::connect();
        }
        return $this->injectedPdo;
    }
}