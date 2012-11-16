<?php
namespace WScore\DbAccess;

class Role_Abstract implements Role_Interface
{
    /** @var \WScore\DbAccess\EntityManager */
    protected $em;

    /** @var \WScore\DbAccess\Model */
    protected $model;

    /** @var \WScore\DbAccess\Entity_Interface */
    protected $entity;

    /** @var \WScore\Validator\DataIO */
    protected $dio;

    /** @var \WScore\Html\Selector */
    protected $selector;

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
}