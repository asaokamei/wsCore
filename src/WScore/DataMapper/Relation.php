<?php
namespace WScore\DataMapper;

class Relation
{
    /** @var Relation_Interface[]      pools relation objects.             */
    private $relations = array();

    /**
     * @param EntityManager     $em
     * @param Entity_Interface  $source
     * @param array       $relations
     * @param string      $name
     * @param null|string $type
     * @throws \RuntimeException
     * @return Relation_Interface
     */
    public function getRelation( $em, $source, $relations, $name, $type=null )
    {
        if( empty( $relations ) ) {
            throw new \RuntimeException( "no relations. " );
        }
        $relation = null;
        foreach( $relations as $relName => $relInfo ) {
            if( $relName == $name ) 
            {
                if( $relation = $this->checkRelation( $source, $name ) ) {
                    return $relation;
                }
                $relInfo[ 'relation_name' ] = $name;
                $type = $relInfo[ 'relation_type' ];
                $class = '\WScore\DataMapper\Relation_' . ucwords( $type );
                if( !class_exists( $class ) ) {
                    throw new \RuntimeException( "no relation class for $class" );
                }
                $relation = new $class( $em, $source, $relInfo );
                $this->saveRelation( $source, $name, $relation );
            }
        }
        if( is_null( $relation ) ) {
            throw new \RuntimeException( "no relation information for '{$name}''. " );
        }
        return $relation;
    }

    /**
     * @param Entity_Interface $entity
     * @param string $name
     * @param Relation_Interface $relation
     */
    private function saveRelation( $entity, $name, $relation ) {
        $cenaId = $entity->_get_cenaId();
        $this->relations[ $cenaId ][ $name ] = $relation;
    }
    /**
     * @param Entity_Interface $entity
     * @param string $name
     * @return null|Relation_Interface
     */
    private function checkRelation( $entity, $name )
    {
        $cenaId = $entity->_get_cenaId();
        if( isset( $this->relations[ $cenaId ][ $name ] ) ) {
            return $this->relations[ $cenaId ][ $name ];
        }
        return null;
    }
    /**
     * TODO: implement late-linking relations.
     * to relate unsaved DataRecord, run this method after all the DataRecords
     * are saved to database, and thus, has the proper id value.
     */
    static public function link() {
    }
}