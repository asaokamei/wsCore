<?php
namespace friends\model;

class Contacts extends \WScore\DbAccess\Model
{
    const TYPE_TELEPHONE  = '1';
    const TYPE_EMAIL      = '2';
    const TYPE_SOCIAL     = '3';
    
    public static $types = array( 
        array( self::TYPE_TELEPHONE, 'telephone' ), 
        array( self::TYPE_EMAIL,     'e-mails' ),
        array( self::TYPE_SOCIAL,    'social' ),
    );

    public static $labels = array( 'home', 'office' );

    /** @var string     name of database table */
    protected $table = 'demoContact';

    /** @var string     name of primary key */
    protected $id_name = 'contact_id';

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
        $this->definition = array(
            'contact_id' => array( 'contact id', 'number', ),
            'friend_id'  => array( 'link to friend', 'number' ),
            'info'       => array( 'contact info', 'string', ),
            'type'       => array( 'type', 'string', ),
            'label'      => array( 'what', 'string', ),
            'created_at' => array( 'created at', 'string', 'created_at' ),
            'updated_at' => array( 'updated at', 'string', 'updated_at' ),
        );

        /** @var array      for validation of inputs */
        $this->validators = array(
            'contact_id' => array( 'number' ),
            'friend_id'  => array( 'number' ),
            'info'       => array( 'text', 'required' ),
            'type'       => array( 'text', '', ),
            'label'      => array( 'text', '' ),
        );

        /** @var array      for selector construction */
        $this->selectors = array(
            'contact_id' => array( 'Selector', 'text' ),
            'info'       => array( 'Selector', 'text', 'placeholder:contact info | class:span5' ),
            'type'       => array( 'Selector', 'select', 'items' => self::$types ),
            'label'      => array( 'Selector', 'text', 'placeholder:type | default:home | class:span3', 'items' => self::$labels ),
        );

        $this->relations = array(
            'friend' => array(
                'relation_type' => 'HasOne',
                'source_column' => null, // use target_column.
                'target_model'  => 'friends\model\Friends',
                'target_column' => null, // use target id name. 
            ),
        );

        parent::__construct( $em, $query );
    }

    public function getCreateSql()
    {
        $sql = "
        CREATE TABLE {$this->table} (
          contact_id   SERIAL PRIMARY KEY,
          friend_id int,
          info text NOT NULL DEFAULT '',
          type char(1),
          label text,
          created_at datetime,
          updated_at datetime
        );
        ";
        return $sql;
    }

    public function getClearSql()
    {
        $sql = "DROP TABLE IF EXISTS {$this->table}";
        return $sql;
    }

}