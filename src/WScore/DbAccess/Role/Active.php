<?php
namespace WScore\DbAccess;

class Role_Active implements Role_Interface
{
    const ACTION_NONE  = 'act-none';
    const ACTION_SAVE  = 'act-save';

    /** @var \WScore\DbAccess\EntityManager */
    private $em;

    /** @var \WScore\DbAccess\Model */
    private $model;

    /** @var \WScore\DbAccess\Entity_Interface */
    private $entity;

    /** @var string */
    private $action = self::ACTION_NONE;

    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\EntityManager    $em
     */
    public function __construct( $em )
    {
        $this->em = $em;
    }

    /**
     * @param \WScore\DbAccess\Entity_Interface    $entity
     */
    public function register( $entity )
    {
        $entity = $this->em->register( $entity );
        $this->entity = $entity;
        $this->model = $this->em->getModel( $entity->_get_Model() );
    }


    /**
     * @return Entity_Interface
     */
    public function retrieve() {
        return $this->entity;
    }
    /**
     * @param string $actType
     * @return Role_Active
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
     * @return Role_Active
     */
    public function delete( $delete=true )
    {
        $this->em->delete( $this->entity, !!$delete );
        $this->setActionType( self::ACTION_SAVE );
        return $this;
    }

    /**
     * @param bool $saveRelations
     * @return Role_Active
     */
    public function save( $saveRelations=false )
    {
        if( $this->action == self::ACTION_SAVE ) {
            $this->em->saveEntity( $this->entity );
        }
        return $this;
    }
    // +----------------------------------------------------------------------+
}
