<?php
namespace wsModule\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class ContextFormWizards extends Persist
{
    /** @var \wsModule\Alt\Web\Request */
    protected $request;

    /** @var \WScore\DataMapper\Role */
    protected $role;

    /** @var array */
    protected $forms;
    // +----------------------------------------------------------------------+
    //  object management
    // +----------------------------------------------------------------------+
    /**
     * @param \wsModule\Dci\Web\PersistInterface   $context
     * @DimInjection Get   \wsModule\Dci\Web\ContextFormAndLoad
     */
    public function __construct( $context )
    {
        $this->setContext( 'form', $context );
    }

    /**
     * a context to show form and load post data from the form.
     * returns $form name if $action is in this context,
     * otherwise returns false.
     *
     * @param Entity_Interface      $entity
     * @param string                $action
     * @param array                 $forms
     * @return bool|string
     */
    protected function main( $entity, $action, $forms=array() )
    {
        $prevForm = null;
        foreach( $forms as $form ) {
            if( $this->context( 'form' )->run( $entity, $action, $form, $prevForm ) ) {
                return $form;
            }
            $prevForm = $form;
        }
        return false;
    }
}