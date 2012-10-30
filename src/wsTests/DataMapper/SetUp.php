<?php
namespace wsTests\DataMapper;

class SetUp
{
    // +----------------------------------------------------------------------+
    //  for Friend table
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return string
     */
    static function clearFriend( $table='mapFriend' ) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }
    
    /**
     * @param string $table
     * @return string
     */
    static function setupFriend( $table='mapFriend' )
    {
        $sql = "
            CREATE TABLE {$table} (
              friend_id    SERIAL, 
              friend_name  text, 
              friend_bday  date,
              new_dt_friend   datetime,
              mod_dt_friend   datetime,
              constraint friend_pkey PRIMARY KEY (
                friend_id
              )
            )
        ";
        return $sql;
    }

    /**
     * @param int $idx
     * @return array
     */
    static function makeFriend( $idx=0 )
    {
        $values = array(
            'friend_name' => 'my friend',
            'friend_bday' => '1980-01-23',
        );
        if( $idx > 0 ) {
            $values[ 'friend_name' ] .= '#' . $idx;
            $values[ 'friend_bday' ]  = date( 'Y-m-d', mktime( 0,0,0, 1, 23+$idx, 1980 ) );
        }
        return $values;
    }

    // +----------------------------------------------------------------------+
    //  for Contact table
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return string
     */
    static function clearContact( $table='mapContact' ) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }
    
    /**
     * @param string $table
     * @return string
     */
    static function setupContact( $table='mapContact' )
    {
        $sql = "
            CREATE TABLE {$table} (
              contact_id       SERIAL, 
              friend_id        int,
              contact_info     text, 
              new_dt_contact   datetime,
              mod_dt_contact   datetime,
              constraint contact_id PRIMARY KEY (
                contact_id
              )
            )
        ";
        return $sql;
    }
    
    /**
     * @param int $idx
     * @return array
     */
    static function makeContact( $idx=0 )
    {
        $values = array(
            'contact_info' => 'my contact',
        );
        if( $idx > 0 ) {
            $values[ 'contact_info' ] .= '#' . $idx;
        }
        return $values;
    }

    // +----------------------------------------------------------------------+
    //  for Group table
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return string
     */
    static function clearGroup( $table='mapGroup' ) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }

    /**
     * @param string $table
     * @return string
     */
    static function setupGroup( $table='mapGroup' )
    {
        $sql = "
            CREATE TABLE {$table} (
              group_code     varchar(64) NOT NULL,
              group_name     text NOT NULL,
              new_dt_group   datetime default NULL,
              mod_dt_group   datetime default NULL,
              constraint group_code PRIMARY KEY (
                group_code
              )
            )
        ";
        return $sql;
    }

    /**
     * @param int $idx
     * @return array
     */
    static function makeGroup( $idx=0 )
    {
        $values = array(
            'group_code' => 'GroupCode',
            'group_name' => 'My Group Name',
        );
        if( $idx > 0 ) {
            $values[ 'group_code' ] .= '_' . $idx;
            $values[ 'group_name' ] .= '#' . $idx;
        }
        return $values;
    }
    // +----------------------------------------------------------------------+
    //  for Friend-to-Group join table
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return string
     */
    static function clearFriend2Group( $table='mapFr2Gr' ) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }

    /**
     * @param string $table
     * @return string
     */
    static function setupFriend2Group( $table='mapFr2Gr' )
    {
        $sql = "
            CREATE TABLE {$table} (
              group_code     varchar(64) NOT NULL,
              friend_id      int NOT NULL,
              created_date   date DEFAULT NULL,
              constraint friend2group_id PRIMARY KEY (
                group_code, friend_id
              )
            )
        ";
        return $sql;
    }

    // +----------------------------------------------------------------------+
    //  for network (friend-to-friend) join table
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return string
     */
    static function clearNetwork( $table='mapNetwork' ) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }

    /**
     * @param string $table
     * @return string
     */
    static function setupNetwork( $table='mapNetwork' )
    {
        $sql = "
            CREATE TABLE {$table} (
              network_id      SERIAL,
              friend_id_from  int NOT NULL,
              friend_id_to    int NOT NULL,
              comment         text,
              status          int,
              created_at      datetime,
              updated_at      datetime,
              constraint network_id PRIMARY KEY (
                network_id
              )
            )
        ";
        return $sql;
    }

    // +----------------------------------------------------------------------+
}
    
