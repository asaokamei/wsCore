<?php
namespace wsCore\DbAccess;

class Context_Manager
{
    /** @var \wsCore\DbAccess\EntityManager */
    private $em;

    /** @var \wsCore\Validator\DataIO */
    private $dio;

    /** @var \wsCore\Html\Selector */
    private $selector;

    /**
     * @param \wsCore\DbAccess\EntityManager    $em
     * @param \wsCore\Validator\DataIO          $dio
     * @param \wsCore\Html\Selector             $selector
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
     * @return \wsCore\DbAccess\Entity_Interface
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
     * @return \wsCore\DbAccess\Entity_Interface
     */
    public function newEntity( $modelName, $data=array(), $id=NULL )
    {
        $entity = $this->em->newEntity( $modelName, $data, $id );
        return $entity;
    }

    /**
     * @param \wsCore\DbAccess\Entity_Interface $entity
     * @param string $role
     * @return mixed
     */
    public function applyRole( $entity, $role )
    {
        if( method_exists( $this, strtolower( $role ) . 'Role' ) ) {
            $method = strtolower( $role ) . 'Role';
            return $this->$method( $entity );
        }
        if( strpos( $role, '\\' ) !== FALSE ) {
            $class = $role;
        }
        else {
            $class = 'Entity_Role' . ucwords( $role );
        }
    }

    /**
     * @param \wsCore\DbAccess\Entity_Interface $entity
     * @return \wsCore\DbAccess\Entity_RoleActive
     */
    public function activeRole( $entity )
    {
        $em   = clone $this->em;
        $role = new Entity_RoleActive( $em );
        $role->register( $entity );
        return $role;
    }

    /**
     * @param \wsCore\DbAccess\Entity_Interface $entity
     * @return Entity_RoleInput
     */
    public function inputRole( $entity )
    {
        $em   = clone $this->em;
        $dio  = clone $this->dio;
        $sel  = clone $this->selector;
        $role = new Entity_RoleInput( $em, $dio, $sel );
        $role->register( $entity );
        return $role;
    }
}