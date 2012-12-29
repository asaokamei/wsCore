<?php
namespace WScore\DataMapper;

class Role_CenaLoad extends Role_Loadable
{
    protected $cena;

    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @param \WScore\Validator\DataIO            $dio
     * @param \WScore\DataMapper\CenaManager      $cena
     * @DimInjection Get \WScore\DataMapper\EntityManager
     * @DimInjection Get \WScore\Validator\DataIO
     * @DimInjection Get \WScore\DataMapper\CenaManager
     */
    public function __construct( $em, $dio, $cena )
    {
        $this->em = $em;
        $this->dio = $dio;
        $this->cena = $cena;
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
        $data = $this->cena->getDataForCenaId( $data, $this->entity->_get_cenaId() );
        parent::loadData( $name, $data[ 'prop' ] );
        // TODO: implement relation.
        return $this;
    }
}