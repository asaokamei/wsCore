<?php
namespace WScore\DbAccess;

class Role
{
    /** @var \WScore\DbAccess\EntityManager */
    public $em;

    /**
     * @param \WScore\DbAccess\EntityManager    $em
     * @DimInjection get  EntityManager
     */
    public function __construct( $em )
    {
        $this->em = $em;
    }

    /**
     * @param \WScore\DbAccess\Entity_Interface|\WScore\DbAccess\Role_Interface $entity
     * @param string                                                            $role
     * @throws \RuntimeException
     * @return \WScore\DbAccess\Role_Interface
     */
    public function applyRole( $entity, $role )
    {
        if( $entity instanceof \WScore\DbAccess\Role_Interface ) {
            $entity = $entity->retrieve();
        }
        if( !$entity instanceof \WScore\DbAccess\Entity_Interface ) {
            throw new \RuntimeException( 'Can apply role to: ' . get_class( $entity ) );
        }
        if( strpos( $role, '\\' ) !== false ) {
            $class = $role;
        }
        else {
            $class = '\WScore\DbAccess\Role_' . ucwords( $role );
        }
        $role = $this->em->container()->fresh( $class );
        /** @var $role Role_Interface */
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
    public function applyInputAndSelectable( $entity )
    {
        $role = $this->applyRole( $entity, 'input' );
        return $role;
    }

    /**
     * @param $entity
     * @return \WScore\DbAccess\Role_Loadable
     */
    public function applyLoadable( $entity ) {
        return $this->applyRole( $entity, 'Loadable' );
    }

    /**
     * @param $entity
     * @return \WScore\DbAccess\Role_Selectable
     */
    public function applySelectable( $entity ) {
        return $this->applyRole( $entity, 'Selectable' );
    }
}