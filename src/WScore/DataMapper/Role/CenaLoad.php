<?php
namespace WScore\DataMapper;

class Role_CenaLoad extends Role_Loadable
{
    protected $cena;

    // +----------------------------------------------------------------------+
    //  constructor
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\CenaManager      $cena
     * @param \WScore\Validation\Validation       $dio
     * @param \WScore\Validation\Rules            $rule
     * @DimInjection Get \WScore\DataMapper\CenaManager
     * @DimInjection Get \WScore\Validation\Validation
     * @DimInjection Get \WScore\Validation\Rules
     */
    public function __construct( $cena, $dio, $rule )
    {
        $this->em = $cena->em();
        $this->dio = $dio;
        $this->cena = $cena;
        $this->rule = $rule;
    }

    /**
     * ignore when undefined method is called. 
     */
    public function __call( $method, $args ) {
        return;
    }

    // +----------------------------------------------------------------------+
    //  general methods. 
    // +----------------------------------------------------------------------+
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

    // +----------------------------------------------------------------------+
    //  methods that match with Cena's state/action name.  
    // +----------------------------------------------------------------------+
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
    public function link( $data )
    {
        foreach( $data as $name => $link ) {
            $entities = $this->cena->getCenaEntity( $link );
            $this->em->relation( $this->entity, $name )->set( $entities );
        }
    }

    /**
     * deletes an entity.
     */
    public function del()
    {
        $this->em->delete( $this->entity );
    }
    // +----------------------------------------------------------------------+
}