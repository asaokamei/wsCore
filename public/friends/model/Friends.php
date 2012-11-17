<?php
namespace friends\model;

use \friends\entity\friend;

class Friends extends \WScore\DbAccess\Model
{
    public static $genders = array(
        array( friend::GENDER_NONE, 'not sure' ),
        array( friend::GENDER_MALE, 'male' ),
        array( friend::GENDER_FEMALE, 'female' )
    );

    public static $stars = array(
        array( 'A', '**' ),
        array( 'B', '*' ),
        array( 'C', '-' ),
    );

    public static $status = array(
        array( friend::STATUS_ACTIVE, 'active' ),
        array( friend::STATUS_DONE, 'done' )
    );

    /** @var string     name of database table */
    protected $table = 'demoFriend';

    /** @var string     name of primary key */
    protected $id_name = 'friend_id';

    protected $definition = array(
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
    protected $validators = array(
        'friend_id' => array( 'number', ),
        'name'      => array( 'text', 'required', ),
        'star'      => array( 'text', ),
        'gender'    => array( 'text', ),
        'memo'      => array( 'text', ),
        'birthday'  => array( 'date', ),
        'status'    => array( 'text', ),
    );

    /** @var array      for selector construction */
    protected $selectors = array(
        'friend_id' => array( 'Selector', 'text', ),
        'name'      => array( 'Selector', 'text', 'placeholder:your friends name | class:span5' ),
        'star'      => array( 'Selector', 'radio', ),
        'gender'    => array( 'Selector', 'radio', ),
        'memo'      => array( 'Selector', 'textarea', 'placeholder:your tasks here | class:span5 | rows:5', ),
        'birthday'  => array( 'Selector', 'date', ),
        'status'    => array( 'Selector', 'checkToggle', ),
    );

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
        parent::__construct( $em, $query );
        $this->selectors[ 'gender' ][ 'items' ] = self::$genders;
        $this->selectors[ 'star'   ][ 'items' ]   = self::$stars;
        $this->selectors[ 'status' ][ 'items' ] = self::$status;
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
          friend_id   INTEGER PRIMARY KEY AUTOINCREMENT,
          name text NOT NULL DEFAULT '',
          star char(1),
          gender char(1),
          memo text,
          birthday date,
          status char(1) NOT NULL DEFAULT '1',
          created_at text,
          updated_at text
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