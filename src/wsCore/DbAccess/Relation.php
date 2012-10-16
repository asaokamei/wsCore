<?php
namespace wsCore\DbAccess;

class Relation
{
    static $pool = array();

    /**
     * @param DataRecord  $source
     * @param array       $relations
     * @param string      $name
     * @param null|string $type
     * @return Relation_Interface
     * @throws \RuntimeException
     */
    static public function getRelation( $source, $relations, $name, $type=NULL )
    {
        if( empty( $relations ) ) {
            throw new \RuntimeException( "no relations. " );
        }
        $relation = null;
        foreach( $relations as $relName => $relInfo ) {
            if( $relName == $name ) {
                $relInfo[ 'relation_name' ] = $name;
                $type = $relInfo[ 'relation_type' ];
                $class = '\wsCore\DbAccess\Relation_' . ucwords( $type );
                $relation = new $class( $source, $relInfo );
            }
        }
        return $relation;
    }
}