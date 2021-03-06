<?php
namespace WScore\DataMapper;

class Role_Active extends Role_Abstract
{
    /** @var \WScore\DataMapper\EntityManager */
    protected $em;

    /** @var \WScore\DataMapper\Relation_Interface[] */
    protected $relations = array();
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @DimInjection Get \WScore\DataMapper\EntityManager
     */
    public function __construct( $em )
    {
        $this->em = $em;
    }

    // +----------------------------------------------------------------------+
    //  get/set properties, and ArrayAccess
    // +----------------------------------------------------------------------+
    /**
     * @param $name
     * @return Relation_Interface
     */
    public function relation( $name )
    {
        if( !isset( $this->relations[ $name ] ) ) {
            $relation = $this->em->relation( $this->entity, $name );
            $this->relations[ $name ] = $relation;
        }
        return $this->relations[ $name ];
    }
    // +----------------------------------------------------------------------+
    //  saving data to db using dao.
    // +----------------------------------------------------------------------+
    /**
     * @param bool $delete
     * @return Role_Active
     */
    public function delete( $delete=true )
    {
        $this->em->delete( $this->entity, !!$delete );
        return $this;
    }

    /**
     * @param bool $saveRelations
     * @return Role_Active
     */
    public function save( $saveRelations=false )
    {
        $this->em->saveEntity( $this->entity );
        return $this;
    }
    // +----------------------------------------------------------------------+
}
