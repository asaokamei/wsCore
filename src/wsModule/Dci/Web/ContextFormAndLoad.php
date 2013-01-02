<?php
namespace wsModule\Alt\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class ContextFormAndLoad extends Context
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
     * a context to show form and load post data from the form.
     * returns $form name if $action is in this context,
     * otherwise returns false.
     *
     * @param Entity_Interface       $entity
     * @param string                 $action
     * @param string                 $form
     * @param string|null            $prevForm
     * @return bool|string
     */
    protected function main( $entity, $action, $form='form', $prevForm=null )
    {
        $role     = $this->role->applyLoadable( $entity );
        $isPost   = $this->request->isPost();
        // show form at least once. check for pin-point. 
        if ( !$this->checkPin( $form ) ) {
            // no validation result is necessary when showing the form.
            $role->resetValidation( true );
            return $form;
        }
        // requesting for a form. 
        if ( $action == $prevForm || ( $action == $form && !$isPost ) ) {
            // no validation result is necessary when showing the form.
            $role->resetValidation( true );
            return $form;
        }
        // load data if it is a post for a form. 
        if ( $action == $form && $isPost ) {
            $role->loadData( $form );
        }
        // validate data *always*. 
        if ( !$role->validate( $form ) ) {
            return $form;
        }
        // all pass. not in this context. 
        return false;
    }
}