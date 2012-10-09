<?php
namespace wsCore\DbAccess;

class Relation
{
    static $pool = array();

    /**
     * @param DataRecord   $source
     * @param strimg       $column
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
     * @return Relation_Many2many
     */
    static public function Many2many( $source, $m2mModel )
    {
        /** @var $relation Relation_Many2many */
        $relation = new $m2mModel( $source->query );
        $relation->setSource( $source );
        static::$pool[] = $relation;
        return $relation;
    }
}