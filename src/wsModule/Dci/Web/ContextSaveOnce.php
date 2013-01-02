<?php
namespace wsModule\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class ContextFormAndLoad extends Persist
{
    /** @var \WScore\DataMapper\Role */
    protected $role;

    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Web\Session       $session
     * @param \WScore\DataMapper\Role   $role
     * @DimInjection Get   Session
     * @DimInjection Get   \WScore\DataMapper\Role
     */
    public function __construct( $session, $role )
    {
        parent::__construct( $session );
        $this->role = $role;
    }

    /**
     * saves the entity if $action is $form and token is verified. pin points the $form.
     * returns $form if $action is in this context (i.e. entity is saved), otherwise
     * returns false.
     *
     * @param Entity_Interface       $entity
     * @param string                 $action
     * @param string                 $form
     * @param string|null            $prevForm
     * @return bool|string
     */
    protected function main( $entity, $action, $form, $prevForm=null )
    {
        // it's new. and further check the token.
        if( $action == $form )
        {
            // check if already saved.
            if( $this->checkPin( $form ) ) return false;
            // OK, let's save the entity. 
            $this->role->applyActive( $entity )->save();
            $this->registerPin( $form );
            return $form;
        }
        return false;
    }
}