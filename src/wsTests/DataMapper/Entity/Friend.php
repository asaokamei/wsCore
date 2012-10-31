<?php
namespace wsTests\DataMapper\Entity;

class Friend extends \wsCore\DbAccess\Entity_Base
{
    protected $_model = 'Friend';

    public $friend_id = null;

    public $friend_name = '';

    public $friend_bday = null;
}