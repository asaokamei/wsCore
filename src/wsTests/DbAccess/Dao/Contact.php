<?php
namespace wsTests\DbAccess;

class Dao_Contact extends \wsCore\DbAccess\Dao
{
    /** @var string     name of database table     */
    protected $table = 'contact';

    /** @var string     name of primary key        */
    protected $id_name = 'contact_id';

    protected $definition = array(
        'contact_id'     => array( 'contact code', 'number', ),
        'friend_id'      => array( 'friend link',  'number', ),
        'contact_info'   => array( 'name',         'string', ),
        'new_dt_contact' => array( 'created at',   'string', 'created_at'),
        'mod_dt_contact' => array( 'updated at',   'string', 'updated_at'),
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'contact_id'   => array( 'number' ),
        'friend_id'    => array( 'number', 'required' ),
        'contact_info' => array( 'text', 'required' ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'contact_id'   => array( 'Selector', 'text' ),
        'friend_id'    => array( 'Selector', 'text', 'width:10' ),
        'contact_info' => array( 'Selector', 'text', 'width:43' ),
    );
    
    protected $relations = array(
        'friend_id' => array(
            'relation_type' => 'HasOne',
            'source_column' => null, // use target_column.
            'target_model'  => 'Dao_Friend',
            'target_column' => null, // use target id name. 
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
