<?php
namespace wsTests\DbAccess;

class Dao_Friend extends \wsCore\DbAccess\Dao
{
    /** @var string     name of database table     */
    protected $table = 'Friend';

    /** @var string     name of primary key        */
    protected $id_name = 'friend_id';

    /** @var array      property names as key => name  */
    protected $properties = array(
        'friend_id'   => 'friend code',
        'friend_name' => 'name',
        'friend_bday' => 'birthday',
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'friend_id'   => array( 'number' ),
        'friend_name' => array( 'text' ),
        'friend_bday' => array( 'date' ),
    );


    /** @var array      for selector construction      */
    protected $selectors  = array(
        'friend_id'   => array( 'Selector', 'text' ),
        'friend_name' => array( 'Selector', 'text', 'width:43' ),
        'friend_bday' => array( 'Selector', 'DateYMD' ),
    );
    
    // +----------------------------------------------------------------------+
    /**
     * @param $query \wsCore\DbAccess\Query
     * @param $selector \wsCore\DiContainer\Dimplet
     * @DimInjection Fresh    Query
     * @DimInjection Get Raw  Selector
     */
    public function __construct( $query, $selector )
    {
        parent::__construct( $query, $selector );
    }
    public function recordClassName() {
        return $this->recordClassName;
    }
}
