<?php
namespace WScore\DataMapper;

class Role_CenaLoad extends Role_Loadable
{
    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @param \WScore\Validator\DataIO          $dio
     * @DimInjection Get \WScore\DataMapper\EntityManager
     * @DimInjection Get \WScore\Validator\DataIO
     * @DimInjection Get \WScore\Html\Selector
     */
    public function __construct( $em, $dio )
    {
        $this->em = $em;
        $this->dio = $dio;
    }
    /**
     * @param null|string $name
     * @param array       $data
     * @return Role_Loadable
     */
    public function loadData( $name=null, $data=array() )
    {
        if( is_array(  $name ) ) $data = $name;
        if( empty( $data ) ) $data = $_POST;
        $data = $this->getData( $data );
        parent::loadData( $name, $data );
        return $this;
    }
    public function getData( $data ) 
    {
        // the data is not in Cena format. 
        // return the data as is. 
        if( !isset( $data[ 'Cena' ] ) ) return $data;
        // OK, got Cena formatted data. 
        $cenaId = $this->entity->_get_cenaId();
        $cena = explode( '.', $cenaId );
        $data = $data[ 'Cena' ];
        foreach( $cena as $item ) {
            if( !isset( $data[ $item ] ) ) return array();
            $data = $data[ $item ];
        }
        return $data;
    }
}