<?php
namespace wsModule\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class ContextConfirmUnless extends Persist
{
    /** @var \wsModule\Alt\Web\Request */
    protected $request;

    /** @var \WScore\DataMapper\Role */
    protected $role;

    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Web\Session         $session
     * @param \wsModule\Alt\Web\Request   $request
     * @param \WScore\DataMapper\Role     $role
     * @DimInjection Get   Session
     * @DimInjection Get   \wsModule\Alt\Web\Request
     * @DimInjection Get   \WScore\DataMapper\Role
     */
    public function __construct( $session, $request, $role )
    {
        parent::__construct( $session );
        $this->request = $request;
        $this->role = $role;
    }

    /**
     * for mostly showing confirm view. validates, again, and pushes token.
     * returns true if $action is in this context, otherwise returns false.
     *
     * @param Entity_Interface       $entity
     * @param string                 $action
     * @param string                 $form
     * @param string|null            $default
     * @return bool|string
     */
    protected function main( $entity, $action, $form, $default='confirm' )
    {
        $role = $this->role->applyLoadable( $entity );
        // validate data *always*. 
        if ( !$role->validate() ) {
            return $default;
        }
        if( $action == $form ) {
            return false;
        }
        return $default;
    }
}