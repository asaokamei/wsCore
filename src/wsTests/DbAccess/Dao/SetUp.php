<?php
namespace wsTests\DbAccess;

class Dao_SetUp
{
    // +----------------------------------------------------------------------+
    //  for Friend table
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @return string
     */
    static function clearFriend( $table='friend' ) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }
    
    /**
     * @param string $table
     * @return string
     */
    static function setupFriend( $table='friend' )
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
    static function clearContact( $table='contact' ) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return $sql;
    }
    
    /**
     * @param string $table
     * @return string
     */
    static function setupContact( $table='contact' )
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
}
    
