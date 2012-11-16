<?php
namespace friends\entity;

class friend extends \WScore\DbAccess\Entity_Abstract
{
    const STATUS_ACTIVE = '1';
    const STATUS_DONE   = '9';

    protected $_model = 'tasks';

    public $friend_id = null;

    public $friend_memo = '';

    public $friend_date = '';

    public $friend_status = self::STATUS_ACTIVE;

    public $created_at;

    public $updated_at;

    /**
     * @return bool
     */
    public function isDone() {
        return $this->friend_status == self::STATUS_DONE;
    }

    /**
     *
     */
    public function setDone() {
        $this->friend_status = self::STATUS_DONE;
    }
}

