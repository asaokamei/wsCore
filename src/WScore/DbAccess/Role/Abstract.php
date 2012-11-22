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
        // maybe NOT to register in the roles. 
        // not sure if this is the right decision... 
        // $entity = $this->em->register( $entity );
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
     * @return null|string
     */
    public function getId() {
        return $this->model->getId( $this->entity );
    }

    /**
     * @return string
     */
    public function getIdName() {
        return $this->model->getIdName();
    }

    /**
     * returns if validated is successful or not.
     *
     * @return bool
     */
    public function isValid() {
        return $this->entity->_is_valid();
    }
}