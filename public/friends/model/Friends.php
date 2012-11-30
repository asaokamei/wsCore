<?php
namespace friends\model;

class Friends extends \WScore\DbAccess\Model
{
    const STATUS_ACTIVE = '1';
    const STATUS_DONE   = '9';

    const GENDER_MALE   = 'M';
    const GENDER_FEMALE = 'F';
    const GENDER_NONE   = 'N';

    public static $genders = array(
        array( self::GENDER_NONE, 'not sure' ),
        array( self::GENDER_MALE, 'male' ),
        array( self::GENDER_FEMALE, 'female' )
    );

    public static $stars = array(
        array( 'A', '<i class="img-heart"></i> ' ),
        array( 'B', '<i class="img-star"></i> ' ),
        array( 'C', '<i class="img-user"></i> ' ),
    );

    public static $status = array(
        array( self::STATUS_ACTIVE, 'active' ),
        array( self::STATUS_DONE, 'done' )
    );

    /** @var string     name of database table */
    protected $table = 'demoFriend';

    /** @var string     name of primary key */
    protected $id_name = 'friend_id';

    public $recordClassName = 'friends\entity\friend';

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
            'friend_id'  => array( 'friend id', 'number', ),
            'name'       => array( 'friend\'s name', 'string', ),
            'star'       => array( 'stars', 'string', ),
            'gender'     => array( 'gender', 'string', ),
            'memo'       => array( 'about...', 'string', ),
            'birthday'   => array( 'birthday', 'string', ),
            'status'     => array( 'still friend?', 'string', ),
            'created_at' => array( 'created at', 'string', 'created_at' ),
            'updated_at' => array( 'updated at', 'string', 'updated_at' ),
        );

        /** @var array      for validation of inputs */
        $this->validators = array(
            'friend_id' => array( 'number', ),
            'name'      => array( 'text', 'required', ),
            'star'      => array( 'text', ),
            'gender'    => array( 'text', ),
            'memo'      => array( 'text', ),
            'birthday'  => array( 'date', ),
            'status'    => array( 'text', ),
        );

        /** @var array      for selector construction */
        $this->selectors = array(
            'friend_id' => array( 'Selector', 'text', ),
            'name'      => array( 'Selector', 'text', 'placeholder:your friends name | class:span5' ),
            'star'      => array( 'Selector', 'radio', 'items' => self::$stars ),
            'gender'    => array( 'Selector', 'radio', 'items' => self::$genders ),
            'memo'      => array( 'Selector', 'textarea', 'placeholder:your tasks here | class:span5 | rows:5', ),
            'birthday'  => array( 'Selector', 'date', ),
            'status'    => array( 'Selector', 'checkToggle', 'items' => self::$status ),
        );

        $this->relations = array(
            'contacts' => array(
                'relation_type' => 'HasRefs',
                'source_column' => null, // use id name of source. 
                'target_model'  => 'friends\model\Contacts',
                'target_column' => null, // use source column. 
            ),
            'groups' => array(
                'relation_type'      => 'HasJoinDao',
                'join_model'         => 'friends\model\Fr2gr', // same as the relation name
                'join_source_column' => 'friend_id', // same as the relation name
                'join_target_column' => 'group_code', // same as the relation name
                'source_column'      => 'friend_id', // same as the relation name
                'target_model'       => 'friends\model\Group',
                'target_column'      => 'group_code', // use id.
            ),
        );
        
        parent::__construct( $em, $query );

    }

    /**
     * @param null|string $name
     * @return array
     */
    public function getPropertyList( $name = null )
    {
        return $list = parent::protect( $this->properties );
    }

    public function getCreateSql()
    {
        $sql = "
        CREATE TABLE {$this->table} (
          friend_id   SERIAL PRIMARY KEY,
          name text NOT NULL DEFAULT '',
          star char(1),
          gender char(1),
          memo text,
          birthday date,
          status char(1) NOT NULL DEFAULT '1',
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

    public function getSampleTasks( $idx = 1 )
    {
        $vows = array( 'a', 'o', 'i', 'e' );
        $syll = array( 'k', 's', 't', 'm', 'g' );
        $cccc = mt_rand( 6, 9 );
        $name = '';
        for ( $i = 0; $i < $cccc; $i++ ) {
            $name .= $syll[ mt_rand( 0, 4 ) ];
            $name .= $vows[ mt_rand( 0, 3 ) ];
            if ( $i == 2 ) $name .= ' ';
        }
        $name    = ucwords( $name );
        $stars   = array( 'A', 'B', 'C' );
        $genders = array( 'N', 'M', 'F' );
        $task    = array(
            'name'     => $name,
            'star'     => $stars[ mt_rand( 0, 2 ) ],
            'gender'   => $genders[ mt_rand( 0, 2 ) ],
            'memo'     => 'memo #' . $idx,
            'birthday' => sprintf( '1980-11-%02d', $idx + 1 ),
            'status'   => 1,
        );
        return $task;
    }
}