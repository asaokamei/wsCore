<?php
namespace wsCore\DbAccess;

class Entity_Active
{
    const ACTION_NONE  = 'act-none';
    const ACTION_SAVE  = 'act-save';
    const ACTION_DEL   = 'act-del';

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
     * @param bool $force
     * @return \wsCore\DbAccess\Entity_Active
     */
    protected function setActionType( $actType, $force=false )
    {
        if( ( $actType == self::ACTION_NONE && $force ) ||
            ( $actType == self::ACTION_SAVE && $this->actType != self::ACTION_DEL ) ||
            ( $actType == self::ACTION_DEL ) )
        {
            $this->action = $actType;
        }
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  get/set properties, and ArrayAccess
    // +----------------------------------------------------------------------+
    /**
     * @param $name
     * @param $value
     */
    public function __set( $name, $value ) {
        $this->entity->$name = $value;
        $this->setActionType( self::ACTION_SAVE );
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        if( isset( $this->entity->$name ) ) return $this->entity->$name;
        return null;
    }

    // +----------------------------------------------------------------------+
    //  saving data to db using dao.
    // +----------------------------------------------------------------------+
    /**
     * @return Entity_Active
     */
    public function delete()
    {
        return $this->setActionType( self::ACTION_DEL );
    }

    /**
     * @param bool $saveRelations
     * @return \wsCore\DbAccess\Entity_Active
     */
    public function save( $saveRelations=FALSE )
    {
        $this->em->save();
        return $this;
    }
    // +----------------------------------------------------------------------+
}
