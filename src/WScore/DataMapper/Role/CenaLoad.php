<?php
namespace WScore\DataMapper;

class Role_CenaLoad extends Role_Loadable
{
    protected $cena;

    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @param \WScore\Validator\DataIO            $dio
     * @param \WScore\DataMapper\CenaManager      $cena
     * @DimInjection Get EntityManager
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
        return $this;
    }
    public function loadLink( $method='set', $data=array() )
    {
        if( is_array(  $method ) ) $data = $method;
        if( empty( $data ) ) $data = $_POST;
        $data = $this->cena->getDataForCenaId( $data, $this->entity->_get_cenaId() );
        if( empty( $data[ 'link' ] ) ) return $this;
        foreach( $data[ 'link' ] as $name => $link ) {
            $entities = $this->cena->getCenaEntity( $link );
            $this->em->relation( $this->entity, $name )->$method( $entities );
        }
        return $this;
    }
}