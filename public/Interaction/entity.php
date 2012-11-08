<?php
namespace Interaction;

class entity extends \wsCore\DbAccess\Entity_Abstract
{
    protected $_model = 'model';

    public $friend_id = null;

    public $friend_name = '';

    public $friend_bday = null;

    public $entityName = null;

    public $role = null;

    public $_actions = array();

    public function loadData( $load ) {
        $this->_actions[] = $load;
    }
    public function verify( $load ) {
        $this->_actions[] = $load;
        return !\wsCore\Utilities\Tools::getKey( $_REQUEST, 'error' );
    }
}

