<?php
namespace WScore\DataMapper;

class Relation
{
    /** @var Relation_Interface[]      pools relation objects.             */
    static $pool = array();

    /**
     * @param EntityManager     $em
     * @param Entity_Interface  $source
     * @param array       $relations
     * @param string      $name
     * @param null|string $type
     * @throws \RuntimeException
     * @return Relation_Interface
     */
    static public function getRelation( $em, $source, $relations, $name, $type=NULL )
    {
        if( empty( $relations ) ) {
            throw new \RuntimeException( "no relations. " );
        }
        $relation = NULL;
        foreach( $relations as $relName => $relInfo ) {
            if( $relName == $name ) {
                $relInfo[ 'relation_name' ] = $name;
                $type = $relInfo[ 'relation_type' ];
                $class = '\WScore\DataMapper\Relation_' . ucwords( $type );
                $relation = new $class( $em, $source, $relInfo );
            }
        }
        if( is_null( $relation ) ) {
            throw new \RuntimeException( "no relation information for '{$name}''. " );
        }
        return $relation;
    }

    /**
     * TODO: implement late-linking relations.
     * to relate unsaved DataRecord, run this method after all the DataRecords
     * are saved to database, and thus, has the proper id value.
     */
    static public function link() {
    }
}