<?php
namespace wsTests\DataMapper\Entity;

class Contact extends \wsCore\DbAccess\Entity_Base
{
    protected $_model = 'Contact';

    public $contact_id = null;

    public $friend_id = null;

    public $contact_info = '';
}