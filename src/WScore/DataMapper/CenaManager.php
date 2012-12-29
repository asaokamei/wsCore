<?php
namespace WScore\DataMapper;

class CenaManager
{
    public $cena = 'Cena';
    
    public $connector = '.';
    
    /** @var \WScore\DataMapper\EntityManager */
    protected $em;
    
    /** @var \WScore\DataMapper\Role */
    protected $role;
    
    /** @var array     $models[ $modelName ] = $model */
    protected $models = array();
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @param \WScore\DataMapper\Role            $role
     * @DimInjection GET EntityManager
     * @DimInjection GET \WScore\DataMapper\Role
     */
    public function __construct( $em, $role )
    {
        $this->em = $em;
        $this->role = $role;
    }

    /**
     * @param string      $modelName
     * @param string|null $model
     */
    public function useModel( $modelName, $model=null ) 
    {
        if( is_null( $model ) ) {
            $model     = $modelName;
            $modelName = substr( $model, strrpos( $model, '\\' )+1 );
        }
        $this->models[ $modelName ] = $model;
    }
    // +----------------------------------------------------------------------+
    //  managing entities with cena
    // +----------------------------------------------------------------------+
    /**
     * @param array $data
     * @return Entity_Interface[]
     */
    public function serveEntities( $data=array() )
    {
        if( empty( $data ) ) $data = $_POST;
        $data = $data[ $this->cena ];
        $list = array();
        if( empty( $data ) ) return $list;
        foreach( $data as $model => $types ) {
            foreach( $types as $type => $ids ) {
                foreach( $ids as $id => $info )
                {
                    // now create entities... 
                    $entity = $this->getEntity( $model, $type, $id );
                    $role   = $this->role->applyCenaLoad( $entity );
                    $role->loadData( $info[ 'prop' ] );
                    // TODO: implement relation. 
                    $list[] = $entity;
                }
            }
        }
        return $list;
    }

    /**
     * @param string $model
     * @param string $type
     * @param string $id
     * @return Entity_Interface
     */
    public function getEntity( $model, $type, $id ) 
    {
        if( isset( $this->models[ $model ] ) ) $model = $this->models[ $model ];
        if( $type == 'new' ) {
            return $this->em->newEntity( $model, $id );
        }
        return $this->em->getEntity( $model, $id );
    }
    // +----------------------------------------------------------------------+
    //  utility methods. 
    // +----------------------------------------------------------------------+
    /**
     * returns cena-formatted name for form elements.
     *
     * @param string  $cenaId
     * @param string  $type
     * @param null    $name
     * @return string
     */
    public function getFormName( $cenaId, $type='prop', $name=null )
    {
        $cena = explode( $this->connector, $cenaId );
        $formName = $this->cena . '[' . implode( '][', $cena ) . "][{$type}]";
        if( $name ) $formName .= "[{$name}]";
        return $formName;
    }


    /**
     * @param array  $data
     * @param string $cenaId
     * @return array
     */
    public function getDataForCenaId( $data, $cenaId=null )
    {
        // the data is not in Cena format. 
        // return the data as is. 
        if( !isset( $data[ $this->cena ] ) ) return $data;
        // OK, got Cena formatted data. 
        $data = $data[ $this->cena ];
        if( !$cenaId ) return $data;
        
        // get data for a specific cenaID. 
        $cena = explode( '.', $cenaId );
        foreach( $cena as $item ) {
            if( !isset( $data[ $item ] ) ) return array();
            $data = $data[ $item ];
        }
        return $data;
    }
    // +----------------------------------------------------------------------+
}