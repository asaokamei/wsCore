<?php
namespace WScore\DbAccess;

class Role
{
    /** @var \WScore\DbAccess\EntityManager */
    private $em;

    /** @var \WScore\Validator\DataIO */
    private $dio;

    /** @var \WScore\Html\Selector */
    private $selector;

    /**
     * @param \WScore\DbAccess\EntityManager    $em
     * @param \WScore\Validator\DataIO          $dio
     * @param \WScore\Html\Selector             $selector
     * @DimInjection get  EntityManager
     * @DimInjection get  DataIO
     * @DimInjection get  Selector
     */
    public function __construct( $em, $dio, $selector )
    {
        $this->em = $em;
        $this->dio = $dio;
        $this->selector = $selector;
    }

    /**
     * @param string $modelName
     * @param string $id
     * @return \WScore\DbAccess\Entity_Interface
     */
    public function getEntity( $modelName, $id )
    {
        $entity = $this->em->getEntity( $modelName, $id );
        return $entity;
    }

    /**
     * @param string        $modelName
     * @param array|string  $data
     * @param null|string   $id
     * @return \WScore\DbAccess\Entity_Interface
     */
    public function newEntity( $modelName, $data=array(), $id=null )
    {
        $entity = $this->em->newEntity( $modelName, $data, $id );
        return $entity;
    }

    /**
     * @param \WScore\DbAccess\Entity_Interface|\WScore\DbAccess\Role_Interface $entity
     * @param string $role
     * @return mixed
     */
    public function applyRole( $entity, $role )
    {
        if( $entity instanceof \WScore\DbAccess\Role_Interface ) {
            $entity = $entity->retrieve();
        }
        if( strpos( $role, '\\' ) !== false ) {
            $class = $role;
        }
        else {
            $class = '\WScore\DbAccess\Role_' . ucwords( $role );
        }
        $em   = clone $this->em;
        $dio  = clone $this->dio;
        $sel  = clone $this->selector;
        /** @var $role Role_Interface */
        $role = new $class( $em, $dio, $sel );
        $role->register( $entity );
        return $role;
    }

    /**
     * @param \WScore\DbAccess\Entity_Interface $entity
     * @return \WScore\DbAccess\Role_Active
     */
    public function applyActive( $entity )
    {
        $role = $this->applyRole( $entity, 'active' );
        return $role;
    }

    /**
     * @param \WScore\DbAccess\Entity_Interface $entity
     * @return \WScore\DbAccess\Role_Input
     */
    public function applyLoadable( $entity )
    {
        $role = $this->applyRole( $entity, 'input' );
        return $role;
    }
}