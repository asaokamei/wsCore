<?php
namespace wsCore\DbAccess;

class Entity_Context
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