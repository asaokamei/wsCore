<?php
namespace Interaction;

class model extends \wsCore\DbAccess\Dao
{
    /** @var string     name of database table     */
    protected $table = 'noDbFriend';

    /** @var string     name of primary key        */
    protected $id_name = 'friend_id';

    protected $definition = array(
        'friend_id'     => array( 'friend code', 'number', ),
        'friend_name'   => array( 'name',        'string', ),
        'friend_bday'   => array( 'birthday',    'string', ),
        'new_dt_friend' => array( 'created at',  'string', 'created_at'),
        'mod_dt_friend' => array( 'updated at',  'string', 'updated_at'),
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'friend_id'   => array( 'number' ),
        'friend_name' => array( 'text', 'required' ),
        'friend_bday' => array( 'date', 'required' ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'friend_id'   => array( 'Selector', 'text' ),
        'friend_name' => array( 'Selector', 'text', 'placeholder:your friends name | class:span5' ),
        'friend_bday' => array( 'Selector', 'date' ),
    );

    public $recordClassName = 'Interaction\entity';

    // +----------------------------------------------------------------------+
    /**
     * @param $em       \wsCore\DbAccess\EntityManager
     * @param $query    \wsCore\DbAccess\Query
     * @param $selector \wsCore\DiContainer\Dimplet
     * @DimInjection Get      EntityManager
     * @DimInjection Fresh    Query
     * @DimInjection Get Raw  Selector
     */
    public function __construct( $em, $query, $selector )
    {
        parent::__construct( $em, $query, $selector );
    }

    /**
     * do nothing! fake insert method.
     * @return bool|string
     */
    public function insert() {
        return true;
    }
}