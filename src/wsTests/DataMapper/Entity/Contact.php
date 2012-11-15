<?php
namespace wsTests\DataMapper\Entity;

class Contact extends \WScore\DbAccess\Entity_Abstract
{
    protected $_model = 'Contact';

    public $contact_id = null;

    public $friend_id = null;

    public $contact_info = '';
}