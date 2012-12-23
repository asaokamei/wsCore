<?php
namespace WScore\DataMapper;

class Role
{
    /** @var \WScore\DataMapper\EntityManager */
    public $em;

    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @DimInjection get  EntityManager
     */
    public function __construct( $em )
    {
        $this->em = $em;
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface|\WScore\DataMapper\Role_Interface $entity
     * @param string                                                            $role
     * @throws \RuntimeException
     * @return \WScore\DataMapper\Role_Interface
     */
    public function applyRole( $entity, $role )
    {
        if( $entity instanceof \WScore\DataMapper\Role_Interface ) {
            $entity = $entity->retrieve();
        }
        if( !$entity instanceof \WScore\DataMapper\Entity_Interface ) {
            throw new \RuntimeException( 'Can apply role to: ' . get_class( $entity ) );
        }
        if( strpos( $role, '\\' ) !== false ) {
            $class = $role;
        }
        else {
            $class = '\WScore\DataMapper\Role_' . ucwords( $role );
        }
        $role = $this->em->container()->fresh( $class );
        /** @var $role Role_Interface */
        $role->register( $entity );
        return $role;
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface $entity
     * @return \WScore\DataMapper\Role_Active
     */
    public function applyActive( $entity )
    {
        $role = $this->applyRole( $entity, 'active' );
        return $role;
    }

    /**
     * @param \WScore\DataMapper\Entity_Interface $entity
     * @return \WScore\DataMapper\Role_Input
     */
    public function applyInputAndSelectable( $entity )
    {
        $role = $this->applyRole( $entity, 'input' );
        return $role;
    }

    /**
     * @param $entity
     * @return \WScore\DataMapper\Role_Loadable
     */
    public function applyLoadable( $entity ) {
        return $this->applyRole( $entity, 'Loadable' );
    }

    /**
     * @param $entity
     * @return \WScore\DataMapper\Role_Selectable
     */
    public function applySelectable( $entity ) {
        return $this->applyRole( $entity, 'Selectable' );
    }

    /**
     * @param $entity
     * @return \WScore\DataMapper\Role_Cenatar
     */
    public function applyCenatar( $entity ) {
        return $this->applyRole( $entity, 'Cenatar' );
    }
}