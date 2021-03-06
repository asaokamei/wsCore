<?php
namespace Interaction;

require_once( __DIR__ . '/model.php' );
require_once( __DIR__ . '/entity.php' );
require_once( __DIR__ . '/view1.php' );
require_once( __DIR__ . '/view2.php' );
require_once( __DIR__ . '/interact.php' );

class selGender extends \WScore\Html\Selector
{
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Html\Form $form
     * @DimInjection Fresh \WScore\Html\Form
     */
    public function __construct( $form )
    {
        parent::__construct( $form );
        $this->style = 'radio';
        $this->item_data = array();
        $this->item_data[] = array( 'M', 'Male' );
        $this->item_data[] = array( 'F', 'Female' );
    }
}

