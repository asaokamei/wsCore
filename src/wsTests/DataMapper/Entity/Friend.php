<?php
namespace wsTests\DataMapper\Entity;

class Friend extends \WScore\DataMapper\Entity_Abstract
{
    protected $_model = 'Friend';

    public $friend_id = null;

    public $friend_name = '';

    public $friend_bday = null;
}