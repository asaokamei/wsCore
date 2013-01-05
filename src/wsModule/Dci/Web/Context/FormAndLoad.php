<?php
namespace wsModule\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class Context_FormAndLoad extends Persist
{
    /** @var \WScore\DataMapper\Role */
    protected $role;
    
    protected $actName = 'form';
    
    protected $prevForm = null;

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
     */
    public function setActName( $name )
    {
        $this->prevForm = $this->actName;
        $this->actName = $name;
    }

    /**
     * a context to show form and load post data from the form.
     * returns $form name if $action is in this context,
     * otherwise returns false.
     *
     * @param Entity_Interface       $entity
     * @param string                 $action
     * @param string                 $method
     * @return bool|string
     */
    protected function main( $entity, $action, $method='get' )
    {
        $role = $this->role->applyLoadable( $entity );
        if ( !$this->checkPin( $this->actName ) ) {
            // no validation result is necessary when showing the form.
            $role->resetValidation( true );
            return $this->actName;
        }
        if( strtolower( $method ) == 'post' ) 
        {
            // post method. load data if action is in this context. 
            if( !is_null( $this->prevForm ) && $action == $this->prevForm ) {
                $role->resetValidation( true );
                return $this->actName;
            }
            if( $action == $this->actName ) {
                // load data if it is a post for a form. 
                $role->loadData( $this->actName );
            }
        }
        else 
        {
            // get method. display forms if action is in this context. 
            if( $action == $this->actName ) {
                // no validation result is necessary when showing the form.
                $role->resetValidation( true );
                return $this->actName;
            }
        }
        // validate data *always*. 
        if ( !$role->validate( $this->actName ) ) {
            return $this->actName;
        }
        // all pass. not in this context. 
        return false;
    }
}