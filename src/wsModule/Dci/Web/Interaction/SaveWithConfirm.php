<?php
namespace wsModule\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class Interaction_SaveWithConfirm extends Persist
{
    /** @var array|string */
    protected $forms = 'form';
    
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Web\Session       $session
     * @DimInjection Get   Session
     */
    public function __construct( $session )
    {
        parent::__construct( $session );
    }

    /**
     * @param PersistInterface $context
     */
    public function setContextEntity( $context ) {
        $this->setContext( 'entity', $context );
    }

    /**
     * @param PersistInterface $context
     */
    public function setContextForm( $context ) {
        $this->setContext( 'form', $context );
    }

    /**
     * @param PersistInterface $context
     */
    public function setContextConfirm( $context ) {
        $this->setContext( 'confirm', $context );
    }

    /**
     * @param PersistInterface $context
     */
    public function setContextSave( $context ) {
        $this->setContext( 'save', $context );
    }

    /**
     * inserts entity in 3 steps: form -> confirm -> save/done.
     *
     * @param Entity_Interface  $entity
     * @param string            $action
     * @param string            $method
     * @return bool|string
     */
    protected function main( $entity, $action, $method='get' )
    {
        // get entity from saved in the session.
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            // create new entity.
            $entity = $this->context( 'entity' )->run( $entity );
            $this->registerData( 'entity', $entity );
        }
        // show the input form. also load and validates the input.
        if( $name = $this->context( 'form' )->run( $entity, $action, $method ) ) {
            return $name;
        }
        // show the confirm view. save token as well.
        if( $name = $this->context( 'confirm' )->run( $entity, $action ) ) {
            if( $entity->_is_valid() ) {
                $token = $this->session->pushToken();
                $this->registerData( '_token', $token );
            }
            return $name;
        }
        // save the entity.
        if( $this->session->verifyToken() &&
            $name = $this->context( 'save' )->run( $entity, $action ) ) {
            return $name;
        }
        return 'done';
    }

}

    