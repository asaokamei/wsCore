<?php
namespace wsModule\Templates;

class Filter
{
    /** @var array */
    protected $filters = array();

    /**
     * @param object $basic
     * @param object $date
     * @DimInjection \wsModule\Templates\Filter_Basic
     * @DimInjection \wsModule\Templates\Filter_Date
     */
    public function __construct( $basic, $date )
    {
        $this->filters[ 'basic' ] = $basic;
        $this->filters[ 'date'  ] = $date;
    }

    /**
     * @param string $name
     * @param object $filter
     */
    public function setFilter( $name, $filter ) {
        $this->filters[ $name ] = $filter;
    }

    /**
     * @param string   $value
     * @param string[] $filters
     * @param string   $method
     * @return mixed
     */
    public function apply( $value, $filters, $method='' )
    {
        // check if $method maybe a filter name in basic filters.
        if( $method && method_exists( $this->filters['basic'], $method ) ) {
            $value = $this->filters['basic']->$method( $value );
        }
        if( empty( $filters ) ) return $value;
        // setup which filter object to use.
        $objects = array( $this->filters['basic'] ); // always use basic filters.
        if( $method && isset( $this->filters[ $method ] ) ) {
            $objects[] = $this->filters[ $method ];  // use additional filters.
        }
        // apply filters
        foreach( $filters as $f ) {
            foreach( $objects as $obj ) {
                if( method_exists( $obj, $f ) ) {
                    $value = $obj->$f( $value );
                    break;
                }
            }
        }
        return $value;
    }

}