<?php
namespace wsTests\DbAccess;

class Dao_Group extends \WScore\DataMapper\Model
{
    /** @var string     name of database table     */
    protected $table = 'myGroup';

    /** @var string     name of primary key        */
    protected $id_name = 'group_code';

    protected $definition = array(
        'group_code'     => array( 'group code', 'number', ),
        'group_name'   => array( 'name',         'string', ),
        'new_dt_group' => array( 'created at',   'string', 'created_at'),
        'mod_dt_group' => array( 'updated at',   'string', 'updated_at'),
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'group_code'   => array( 'number' ),
        'group_name' => array( 'text', 'required' ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'group_code'   => array( 'Selector', 'text' ),
        'group_name' => array( 'Selector', 'text', 'width:43' ),
    );
    
    protected $relations = array(
        'friend' => array(
            'relation_type' => 'HasJoined',
            'join_table'         => 'friend2group', // same as the relation name
            'join_source_column' => 'group_code', // same as the relation name
            'join_target_column' => 'friend_id', // same as the relation name
            'source_column' => 'group_code', // same as the relation name
            'target_model'  => 'wsTests\DbAccess\Dao_Friend',
            'target_column' => 'friend_id', // use id.
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
    public function insert( $data ) {
        return parent::insertValue( $data );
    }
}
