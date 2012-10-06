<?php
namespace wsTests\DbAccess;

use \wsCore\Core;
require_once( __DIR__ . '/../../autoloader.php' );

class Dao_MySql_Test extends \PHPUnit_Framework_TestCase
{
    /** @var mixed */
    public $config;

    /** @var \wsCore\DbAccess\Query */
    public $query;
    
    /** @var Dao_Friend */
    public $friend;
    // +----------------------------------------------------------------------+
    function setUp()
    {
        $this->config = 'db=mysql dbname=test_wsCore username=admin password=admin';
        Core::clear();
        Core::go();
        Core::setPdo( $this->config );
        $this->query = Core::get( 'Query' );
        
        $this->setupFriend();
        
        $this->friend = Core::get( '\wsTests\DbAccess\Dao_Friend' );
    }

    /**
     * @param string $table
     */
    function setupFriend( $table='friend' ) 
    {
        $sql = "DROP TABLE IF EXISTS {$table}";
        $this->query->execSQL( $sql );
        $sql = "
            CREATE TABLE {$table} (
              friend_id    SERIAL, 
              friend_name  text, 
              friend_bday  date,
              created_at   datetime,
              updated_at   datetime,
              constraint friend_pkey PRIMARY KEY (
                friend_id
              )
            )
        ";
        $this->query->execSQL( $sql );
    }
    // +----------------------------------------------------------------------+
    function test_none() {}
    // +----------------------------------------------------------------------+
}