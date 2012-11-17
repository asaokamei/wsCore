<?php
namespace friends\entity;

class contact extends \WScore\DbAccess\Entity_Abstract
{
    protected $_model = 'Contacts';
    
    public $friend_id = null;
    
    public $info = '';
    
    public $type = null;
    
    public $label = '';
    
}
