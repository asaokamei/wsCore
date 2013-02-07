<?php
namespace wsTests\DiContainer\DimpletMockProp;

class C extends B
{
    /**
     * @var \wsTests\DiContainer\DimpleMockDb\DbAccess
     * @DimInjection \wsTests\DiContainer\DimpleMockDb\DbAccess
     */
    private $invoice;

    /**
     * @return \wsTests\DiContainer\DimpleMockDb\DbAccess
     */
    public function getInvoice() {
        return $this->invoice;
    }
}