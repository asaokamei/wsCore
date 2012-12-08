<?php
namespace wsTests\DbAccess;

class Dao_Friend extends \WScore\DataMapper\Model
{
    /** @var string     name of database table     */
    protected $table = 'friend';

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
            'source_column' => null, // use id name of source. 
            'target_model'  => 'wsTests\DbAccess\Dao_Contact',
            'target_column' => null, // use source column. 
        ),
        'group' => array(
            'relation_type' => 'HasJoined',
            'join_table'    => 'friend2group',
            'target_model'  => 'wsTests\DbAccess\Dao_Group',
            //'join_source_column' => null, // use id
            //'join_target_column' => null, // use id
            //'source_column' => null, // use id.
            //'target_column' => null, // use id.
        ),
        'network' => array(
            'relation_type' => 'HasJoinDao',
            'join_model'    => 'wsTests\DbAccess\Dao_Network',
            'join_source_column' => 'friend_id_from',
            'join_target_column' => 'friend_id_to',
            'target_model'  => 'wsTests\DbAccess\Dao_Friend',
        ),
    );
    
    // +----------------------------------------------------------------------+
    /**
     * @param $em       \WScore\DataMapper\EntityManager
     * @param $query    \WScore\DbAccess\Query
     * @param $selector \WScore\DiContainer\Dimplet
     * @DimInjection Get      EntityManager
     * @DimInjection Fresh    Query
     * @DimInjection Get Raw  Selector
     */
    public function __construct( $em, $query, $selector )
    {
        parent::__construct( $em, $query, $selector );
    }
    public function recordClassName() {
        return $this->recordClassName;
    }
    public function setSelector( $name, $info ) {
        $this->selectors[ $name ] = $info;
    }
}
