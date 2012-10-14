<?php
namespace wsTests\DbAccess;

class Dao_SetUp
{
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
    
}