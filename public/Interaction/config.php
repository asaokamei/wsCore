<?php
namespace Interaction;

require_once( __DIR__ . '/model.php' );
require_once( __DIR__ . '/entity.php' );
require_once( __DIR__ . '/View_Bootstrap.php' );
require_once( __DIR__ . '/view.php' );
require_once( __DIR__ . '/interaction.php' );

class selGender extends \wsCore\Html\Selector
{
    // +----------------------------------------------------------------------+
    /**
     * @param \wsCore\Html\Form $form
     * @DimInjection Fresh \wsCore\Html\Form
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

