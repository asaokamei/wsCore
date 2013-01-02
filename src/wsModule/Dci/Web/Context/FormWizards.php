<?php
namespace wsModule\Dci\Web;

use \WScore\DataMapper\Entity_Interface;

class Context_FormWizards extends Persist
{
    /** @var \wsModule\Alt\Web\Request */
    protected $request;

    /** @var \WScore\DataMapper\Role */
    protected $role;

    /** @var array */
    protected $actName;
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
     * @param array|string $name
     */
    public function setActName( $name )
    {
        if( !is_array( $name ) ) $name = array( $name );
        $this->actName = $name;
    }

    /**
     * a context to show form and load post data from the form.
     * returns $form name if $action is in this context,
     * otherwise returns false.
     *
     * @param Entity_Interface      $entity
     * @param string                $action
     * @return bool|string
     */
    protected function main( $entity, $action )
    {
        $prevForm = null;
        foreach( $this->actName as $form ) {
            /** @var $context \wsModule\Dci\Web\Context_FormAndLoad */
            $context = $this->context( 'form' );
            $context->setActName( $form );
            if( $name = $context->run( $entity, $action ) ) {
                return $name;
            }
        }
        return false;
    }
}