<?php
namespace wsTests\DbAccess;

class Dao_Network extends \WScore\DataMapper\Model
{
    /** @var string     name of database table     */
    protected $table = 'network';

    /** @var string     name of primary key        */
    protected $id_name = 'network_id';

    protected $definition = array(
        'network_id'     => array( 'network code', 'number', ),
        'friend_id_from' => array( 'my friend',    'number', ),
        'friend_id_to'   => array( 'friend to',    'number', ),
        'comment'        => array( 'comment',      'string', ),
        'status'         => array( 'friendship',   'number', ),
        'created_at'     => array( 'created at',   'string', 'created_at'),
        'updated_at'     => array( 'updated at',   'string', 'updated_at'),
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'network_id'     => array( 'int',  'required' ),
        'friend_id_from' => array( 'int',  'required' ),
        'friend_id_to'   => array( 'int',  'required' ),
        'comment'        => array( 'text', 'default:""',  ),
        'status'         => array( 'int',  'required', ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'comment'        => array( 'Selector', 'textarea', 'ime:on' ),
        'status'         => array( 'Selector', 'text',     'ime:off'),
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
