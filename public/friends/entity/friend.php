<?php
namespace friends\entity;

class friend extends \WScore\DbAccess\Entity_Abstract
{
    const STATUS_ACTIVE = '1';
    const STATUS_DONE   = '9';
    
    const GENDER_MALE   = 'M';
    const GENDER_FEMALE = 'F';
    const GENDER_NONE   = 'N';

    protected $_model = 'Friends';

    public $friend_id = null;
    
    public $name = '';
    
    public $star = 'B';
    
    public $gender = self::GENDER_NONE;

    public $memo = '';

    public $birthday = null;

    public $status = self::STATUS_ACTIVE;

    public $created_at;

    public $updated_at;

    /**
     * @return bool
     */
    public function isDone() {
        return $this->status == self::STATUS_DONE;
    }

    /**
     *
     */
    public function setDone() {
        $this->status = self::STATUS_DONE;
    }
}

