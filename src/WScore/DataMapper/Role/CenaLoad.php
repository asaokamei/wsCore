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
     * ignore when undefined method is called. 
     */
    public function __call( $method, $args ) {
        return;
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

    /**
     * @param string $method
     * @param array  $data
     * @return Role_CenaLoad
     */
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

    /**
     * sets property. 
     * 
     * @param array $data
     */
    public function prop( $data )
    {
        parent::loadData( $data );
    }

    /**
     * sets relation. 
     * $data[ $name ] => [ targetEntity, targetEntity2, ... ]
     * 
     * @param array $data
     */
    public function links( $data )
    {
        foreach( $data as $name => $link ) {
            $entities = $this->cena->getCenaEntity( $link );
            $this->em->relation( $this->entity, $name )->set( $entities );
        }
    }

    /**
     * deletes an entity.
     */
    public function delete()
    {
        $this->em->delete( $this->entity );
    }
}