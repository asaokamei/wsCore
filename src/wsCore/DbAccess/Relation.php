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
     * @return mixed
     * @throws \RuntimeException
     */
    static public function getRelation( $source, $relations, $name, $type=NULL )
    {
        if( empty( $relations ) ) {
            throw new \RuntimeException( "no relations. " );
        }
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
    
    /**
     * @param DataRecord   $source
     * @param string       $column
     * @param string       $targetModel
     * @param null|string  $targetColumn
     * @return Relation_HasOne
     */
    static public function HasOne( $source, $column, $targetModel, $targetColumn=NULL )
    {
        $relation = new Relation_HasOne( $source, $column, $targetModel, $targetColumn );
        static::$pool[] = $relation;
        return $relation;
    }

    /**
     * @param DataRecord  $source
     * @param string      $m2mModel
     * @return Relation_HasJoined
     */
    static public function Many2many( $source, $m2mModel )
    {
        /** @var $relation Relation_HasJoined */
        $relation = new $m2mModel( $source->query );
        $relation->setSource( $source );
        static::$pool[] = $relation;
        return $relation;
    }
}