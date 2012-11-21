<?php
namespace WScore\DbAccess;

class Role_Active extends Role_Abstract
{
    /** @var \WScore\DbAccess\EntityManager */
    protected $em;
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\EntityManager    $em
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
        if( !$relation = $this->entity->relation( $name ) ) {
            $relation = $this->em->relation( $this->entity, $name );
            $this->entity->setRelation( $name, $relation );
        }
        return $relation;
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
