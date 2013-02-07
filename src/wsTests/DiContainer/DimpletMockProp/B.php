<?php
namespace wsTests\DiContainer\DimpletMockProp;

class B extends A
{
    /**
     * @var \wsTests\DiContainer\DimpleMockDb\DbAccess
     * @DimInjection \wsTests\DiContainer\DimpleMockDb\DumbAccess
     */
    public $injected;

    /**
     * @return \wsTests\DiContainer\DimpleMockDb\DumbAccess
     */
    public function getInjected() {
        return $this->injected;
    }
}