<?php
namespace Interaction;

class entity extends \WScore\DataMapper\Entity_Abstract
{
    protected $_model = 'Interaction\model';

    public $friend_id = null;

    public $friend_name = '';

    public $friend_gender = '';
    
    public $friend_bday = null;

    public $friend_memo = '';
}

