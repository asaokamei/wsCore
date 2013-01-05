<?php
namespace wsModule\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class Context_SaveOnce extends Persist
{
    /** @var \WScore\DataMapper\Role */
    protected $role;

    /** @var string */
    protected $actName = 'confirm';

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
     * @param string $name
     * @return void
     */
    public function setActName( $name )
    {
        $this->actName   = $name;
    }

    /**
     * saves the entity if $action is $form and token is verified. pin points the $form.
     * returns $form if $action is in this context (i.e. entity is saved), otherwise
     * returns false.
     *
     * @param Entity_Interface       $entity
     * @param string                 $action
     * @return bool|string
     */
    protected function main( $entity, $action )
    {
        // it's new. and further check the token.
        if( $action == $this->actName )
        {
            // check if already saved.
            if( $this->checkPin( $this->actName ) ) return false;
            // OK, let's save the entity. 
            $this->role->applyActive( $entity )->save();
            $this->registerPin( $this->actName );
            return $this->actName;
        }
        return false;
    }
}