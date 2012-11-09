<?php
namespace Interaction;

class entity extends \wsCore\DbAccess\Entity_Abstract
{
    protected $_model = 'model';

    public $friend_id = null;

    public $friend_name = '';

    public $friend_gender = '';

    public $friend_bday = null;
}

