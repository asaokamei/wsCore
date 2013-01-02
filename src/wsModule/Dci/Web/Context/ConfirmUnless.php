<?php
namespace wsModule\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class Context_ConfirmUnless extends Persist
{
    /** @var \WScore\DataMapper\Role */
    protected $role;

    /** @var string */
    protected $goSave = 'save';
    
    /** @var string */
    protected $actName = 'confirm';
    
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Web\Session         $session
     * @param \WScore\DataMapper\Role     $role
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
     * @param string $unless
     * @return void
     */
    public function setActName( $name, $unless='save' )
    {
        $this->actName   = $name;
        $this->goSave    = $unless;
    }

    /**
     * for mostly showing confirm view. validates, again, and pushes token.
     * returns true if $action is in this context, otherwise returns false.
     *
     * @param Entity_Interface       $entity
     * @param string                 $action
     * @return bool|string
     */
    protected function main( $entity, $action )
    {
        $role = $this->role->applyLoadable( $entity );
        // validate data *always*. 
        if ( !$role->validate() ) {
            return $this->actName;
        }
        if( $action == $this->goSave ) {
            return false;
        }
        return $this->actName;
    }
}