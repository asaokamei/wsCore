<?php
namespace WScore\DataMapper;

class Role_Loadable extends Role_Abstract
{
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @param \WScore\Validator\DataIO          $dio
     * @DimInjection Get \WScore\DataMapper\EntityManager
     * @DimInjection Get \WScore\Validator\DataIO
     * @DimInjection Get \WScore\Html\Selector
     */
    public function __construct( $em, $dio )
    {
        $this->em = $em;
        $this->dio = $dio;
    }

    // +----------------------------------------------------------------------+
    //  get/set properties, and ArrayAccess
    // +----------------------------------------------------------------------+
    /**
     * load id (primary key) from post/data.
     *
     * @param array $data
     * @return Role_Loadable
     */
    public function loadId( $data=array() )
    {
        if( empty( $data ) ) $data = $_POST;
        $id_name = $this->getIdName();
        $this->$id_name = isset( $data[ $id_name ] )? $data[ $id_name ] : null;
        return $this;
    }

    /**
     * loads all the data/post, except for protected keys.
     * also no key starting with '_' are considered as internal property
     * and thus are not loaded.
     *
     * @param null|string $name
     * @param array       $data
     * @return Role_Loadable
     */
    public function loadData( $name=null, $data=array() )
    {
        if( is_array(  $name ) ) $data = $name;
        if( empty( $data ) ) $data = $_POST;
        // populate the input data
        $data = $this->model->protect( $data );
        foreach( $data as $key => $value ) {
            if( substr( $key, 0, 1 ) == '_' ) continue; // ignore protected/private
            $this->entity->$key = $value;
        }
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  Validating data.
    // +----------------------------------------------------------------------+
    /**
     * validates the entity properties.
     *
     * @param null|string $loadName
     * @return bool
     */
    public function validate( $loadName=null )
    {
        $this->dio->source( $this->entity );
        // validates only for the property list defined in the model.
        // todo: is definition should be in entity itself?
        $list = $this->model->getPropertyList( $loadName );
        foreach( $list as $propName => $name ) {
            $validateInfo = $this->model->getValidateInfo( $propName );
            $type   = array_key_exists( 0, $validateInfo ) ? $validateInfo[0] : null ;
            $filter = array_key_exists( 1, $validateInfo ) ? $validateInfo[1] : '' ;
            $this->dio->push( $propName, $type, $filter );
        }
        $this->em->setEntityProperty( $this->entity, 'errors',  $this->dio->popError() );
        $this->em->setEntityProperty( $this->entity, 'isValid', $this->dio->isValid() );
        return $this->entity->_is_valid();
    }

    /**
     * resets the validation result (isValid in entity).
     *
     * @param bool $valid
     * @return Role_Loadable
     */
    public function resetValidation( $valid=false ) {
        $this->em->setEntityProperty( $this->entity, 'isValid', $valid );
        return $this;
    }
    // +----------------------------------------------------------------------+
}