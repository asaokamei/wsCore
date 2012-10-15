<?php
namespace wsTests\DbAccess;

class Dao_Friend extends \wsCore\DbAccess\Dao
{
    /** @var string     name of database table     */
    protected $table = 'Friend';

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
        'friend_name' => array( 'Selector', 'text', 'width:43' ),
        'friend_bday' => array( 'Selector', 'DateYMD' ),
    );

    protected $relations = array(
        'contact' => array(
            'relation_type' => 'HasRefs',
            'source_column' => null, // use id. 
            'target_model'  => 'Dao_Contact',
            'target_column' => null, // use id name of source. 
        ),
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
    public function setSelector( $name, $info ) {
        $this->selectors[ $name ] = $info;
    }
}
