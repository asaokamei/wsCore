<?php
namespace friends\model;

class Contacts extends \WScore\DbAccess\Model
{
    public static $types = array( 
        array( 1, 'telephone' ), 
        array( 2, 'e-mails' ),
        array( 3, 'social' ),
    );
    /** @var string     name of database table */
    protected $table = 'demoContact';

    /** @var string     name of primary key */
    protected $id_name = 'contact_id';

    protected $definition = array(
        'contact_id' => array( 'contact id', 'number', ),
        'info'       => array( 'contact info', 'string', ),
        'type'       => array( 'type', 'string', ),
        'label'      => array( 'what', 'string', ),
        'created_at' => array( 'created at', 'string', 'created_at' ),
        'updated_at' => array( 'updated at', 'string', 'updated_at' ),
    );

    /** @var array      for validation of inputs */
    protected $validators = array(
        'contact_id' => array( 'number' ),
        'info'       => array( 'text', 'required' ),
        'type'       => array( 'text', '', ),
        'label'      => array( 'text', '' ),
    );

    /** @var array      for selector construction */
    protected $selectors = array(
        'contact_id' => array( 'Selector', 'text' ),
        'info'       => array( 'Selector', 'text', 'placeholder:contact info | class:span5' ),
        'type'       => array( 'Selector', 'select', ),
        'label'      => array( 'Selector', 'text', 'placeholder:type | default:home | class:span3' ),
    );

    protected $relations = array(
        'friend' => array(
            'relation_type' => 'HasOne',
            'source_column' => null, // use target_column.
            'target_model'  => 'Friends',
            'target_column' => null, // use target id name. 
        ),
    );

    public $recordClassName = 'friends\entity\contact';

    // +----------------------------------------------------------------------+
    /**
     * @param $em       \WScore\DbAccess\EntityManager
     * @param $query    \WScore\DbAccess\Query
     * @DimInjection Get      EntityManager
     * @DimInjection Fresh    Query
     */
    public function __construct( $em, $query )
    {
        parent::__construct( $em, $query );
        $this->selectors[ 'type' ][ 'items' ] = self::$types;
    }

}