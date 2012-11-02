<?php
namespace wsCore\DbAccess;

class Entity_ActiveValue
{
    /** @var \wsCore\DbAccess\EntityManager */
    private $em;

    /** @var \wsCore\DbAccess\Dao */
    private $model;

    /** @var \wsCore\DbAccess\Entity_Interface */
    private $entity;

    /** @var array */
    private $errors = array();

    /** @var bool */
    private $is_valid = false;

    private $html_type = 'html';
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
    // +----------------------------------------------------------------------+
    //  get/set properties, and ArrayAccess
    // +----------------------------------------------------------------------+
    /**
     * @param array $data
     * @return Entity_ActiveRecord
     */
    public function loadData( $data )
    {
        // protect data.
        foreach( $data as $key => $value ) {
            $this->entity->$key = $value;
        }
        return $this;
    }

    /**
     * @param $name
     * @return \wsCore\DbAccess\Relation_Interface
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
    //  Validating data.
    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\Validator\DataIO $dio
     * @return bool
     */
    public function validate( $dio )
    {
        $dio->source( $this->entity );
        $this->model->validate( $dio );
        $this->is_valid = !$dio->popErrors( $this->errors );
        return $this->is_valid;
    }

    /**
     * @return DataRecord
     */
    public function resetValidation() {
        $this->is_valid = FALSE;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return $this->is_valid;
    }
    // +----------------------------------------------------------------------+
    //  getting Html Forms.
    // +----------------------------------------------------------------------+
    /**
     * setter/getter for html_type to show html elements.
     *
     * @param null|string $html_type
     * @return string
     */
    public function setHtmlType( $html_type=NULL ) {
        if( $html_type ) $this->html_type = $html_type;
        return $this->html_type;
    }
    /**
     * @param string $name
     * @param null   $html_type
     * @return mixed
     */
    public function popHtml( $name, $html_type=NULL ) {
        $html_type = ( $html_type ) ?: $this->html_type;
        return $this->model->popHtml( $html_type, $name, $this->entity->$name );
    }

    /**
     * @param $name
     * @return mixed
     */
    public function popError( $name ) {
        return $this->errors[ $name ];
    }

    /**
     * @param $name
     * @return string
     */
    public function popName( $name ) {
        return $this->model->propertyName( $name );
    }
    // +----------------------------------------------------------------------+
}