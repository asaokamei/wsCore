<?php
namespace friends\entity;

use \friends\model\Friends;

class friend extends \WScore\DataMapper\Entity_Abstract
{
    protected $_model = 'Friends';

    public $friend_id = null;
    
    public $name = '';
    
    public $star = 'B';
    
    public $gender = Friends::GENDER_NONE;

    public $memo = '';

    public $birthday = null;

    public $status = Friends::STATUS_ACTIVE;

    public $created_at;

    public $updated_at;

    /**
     * @return bool
     */
    public function isDone() {
        return $this->status == Friends::STATUS_DONE;
    }

    /**
     *
     */
    public function setDone() {
        $this->status = Friends::STATUS_DONE;
    }
}

