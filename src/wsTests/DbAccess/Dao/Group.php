<?php
namespace wsTests\DbAccess;

class Dao_Group extends \wsCore\DbAccess\Dao
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
        'friend_id' => array(
            'relation_type' => 'HasOne',
            'source_column' => null, // same as the relation name 
            'target_model'  => 'Dao_Friend',
            'target_column' => null, // use id. 
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
    public function insert( $data ) {
        return parent::insertValue( $data );
    }
}
