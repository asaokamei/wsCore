<?php
namespace wsModule\Alt\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class InteractionSave3steps extends Context
{
    /** @var array|string */
    protected $forms = 'form';
    
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Web\Session       $session
     * @param \wsModule\Alt\Dci\Web\ContextInterface     $form
     * @param \wsModule\Alt\Dci\Web\ContextInterface   $confirm
     * @param \wsModule\Alt\Dci\Web\ContextInterface        $save
     * @DimInjection Get   Session
     * @DimInjection Get   \wsModule\Alt\Dci\Web\ContextFormAndLoad
     * @DimInjection Get   \wsModule\Alt\Dci\Web\ContextConfirmUnless
     * @DimInjection Get   \wsModule\Alt\Dci\Web\ContextSaveOnce
     */
    public function __construct( $session, $form, $confirm, $save )
    {
        parent::__construct( $session );
        $this->setContext( 'form',    $form );
        $this->setContext( 'confirm', $confirm );
        $this->setContext( 'save',    $save );
        $this->setFormSteps( 'form' );
    }

    /**
     * @param ContextInterface $context
     */
    public function setContextEntity( $context ) {
        $this->setContext( 'entity', $context );
    }

    /**
     * set steps of forms:
     * $forms = array( 'form1', 'formAge', ... )
     *
     * @param array $forms
     */
    public function setFormSteps( $forms )
    {
        $this->forms = $forms;
    }
    
    /**
     * inserts entity in 3 steps: form -> confirm -> save/done.
     *
     * @param Entity_Interface  $entity
     * @param string $action
     * @return bool|string
     */
    protected function main( $entity, $action )
    {
        // get entity from saved in the session.
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            // create new entity.
            $this->clearData();
            $entity = $this->context( 'entity' )->run( $entity );
            $this->registerData( 'entity', $entity );
        }
        // show the input form. also load and validates the input.
        if( $this->context( 'form' )->run( $entity, $action, $this->forms ) ) {
            return 'form';
        }
        // show the confirm view. save token as well.
        if( $this->context( 'confirm' )->run( $entity, $action, 'save' ) ) {
            if( $entity->_is_valid() ) {
                $token = $this->session->pushToken();
                $this->registerData( 'token', $token );
            }
            return 'confirm';
        }
        // save the entity.
        if( $this->session->verifyToken() && 
            $this->context( 'save' )->run( $entity, $action, 'save' ) ) {
            return 'save';
        }
        return 'done';
    }

}

    