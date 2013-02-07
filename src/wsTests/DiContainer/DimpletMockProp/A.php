<?php
namespace wsTests\DiContainer\DimpletMockProp;

class A
{
    /**
     * @var \wsTests\DiContainer\DimpleMockDb\DbAccess
     * @DimInjection \wsTests\DiContainer\DimpleMockDb\DbAccess
     */
    public $injected;

    /**
     * @var \wsTests\DiContainer\DimpleMockBiz\Invoice
     * @DimInjection \wsTests\DiContainer\DimpleMockBiz\Invoice
     */
    private $invoice;

    /**
     * @return \wsTests\DiContainer\DimpleMockDb\DbAccess
     */
    public function getInjected() {
        return $this->injected;
    }

    /**
     * @return \wsTests\DiContainer\DimpleMockBiz\Invoice
     */
    public function getInvoice() {
        return $this->invoice;
    }
}