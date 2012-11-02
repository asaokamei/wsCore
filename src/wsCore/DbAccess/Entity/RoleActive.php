<?php
namespace wsCore\DbAccess;

class Entity_RoleActive
{
    const ACTION_NONE  = 'act-none';
    const ACTION_SAVE  = 'act-save';

    /** @var \wsCore\DbAccess\EntityManager */
    private $em;

    /** @var \wsCore\DbAccess\Dao */
    private $model;

    /** @var \wsCore\DbAccess\Entity_Interface */
    private $entity;

    /** @var string */
    private $action = self::ACTION_NONE;

    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\DbAccess\EntityManager    $em
     * @param \wsCore\DbAccess\Entity_Interface $entity
     */
    public function __construct( $em, $entity )
    {
        $this->em = $em;
        $this->em->register( $entity );
        $this->entity = $entity;
        $this->model = $em->getModel( $entity->_get_Model() );
    }

    /**
     * @param string $actType
     * @return Entity_RoleActive
     */
    protected function setActionType( $actType ) {
        $this->action = $actType;
        return $this;
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
            $relation = $this->model->relation( $this->entity, $name );
            $this->entity->setRelation( $name, $relation );
        }
        return $relation;
    }
    // +----------------------------------------------------------------------+
    //  saving data to db using dao.
    // +----------------------------------------------------------------------+
    /**
     * @param bool $delete
     * @return Entity_RoleActive
     */
    public function delete( $delete=TRUE )
    {
        $this->em->delete( $this->entity, !!$delete );
        $this->setActionType( self::ACTION_SAVE );
        return $this;
    }

    /**
     * @param bool $saveRelations
     * @return Entity_RoleActive
     */
    public function save( $saveRelations=FALSE )
    {
        if( $this->action == self::ACTION_SAVE ) {
            $this->em->saveEntity( $this->entity );
        }
        return $this;
    }
    // +----------------------------------------------------------------------+
}
