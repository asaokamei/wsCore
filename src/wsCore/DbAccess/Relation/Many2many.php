<?php
namespace wsCore\DbAccess;

class Relation_Many2many extends Dao
{
    /** @var DataRecord */
    protected $source;
    protected $sourceModel;
    
    /** @var DataRecord */
    protected $target;
    protected $targetModel;

    protected $pivot = 'target';
    
    /**
     * @param Query $query
     */
    public function __construct( $query )
    {
        $this->query = $query;
        // make sure to set {source|target}{Model|Column}.
    }

    /**
     * @param DataRecord $source
     * @return \wsCore\DbAccess\Relation_Many2many
     */
    public function setSource( $source ) 
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param DataRecord $target
     * @return DataRecord
     */
    public function link( $target )
    {
        $this->target = $target;
        $this->pivot();
        // check if relation already exists. 
        $record = $this->exists( $target );
        // adding new relation. 
        if( empty( $record ) ) {
            
            $values = array(
                $this->source->getIdName() => $this->source->getId(),
                $this->target->getIdName() => $this->target->getId(),
            );
            $this->insert( $values );
            $record = $this->exists( $target );
        }
        return $record;
    }

    /**
     * @return bool|DataRecord
     */
    public function exists()
    {
        $sourceId = $this->source->getId();
        $targetId = $this->target->getId();
        $record = $this->query()
            ->w( $this->source->getIdName() )->eq( $sourceId )
            ->w( $this->target->getIdName() )->eq( $targetId )->select();
        if( empty( $record ) ) {
            return FALSE;
        }
        return $record[0];
    }

    /**
     * @throws \RuntimeException
     */
    public function pivot()
    {
        if( $this->source->getModel() != $this->sourceModel ) {
            $temp = $this->source;
            $this->target = $this->source;
            $this->target = $temp;
            $this->pivot  = 'source';
        }
        if( $this->source->getModel() != $this->sourceModel ) {
            throw new \RuntimeException( "Source/Target model mis match. " );
        }
        return $this;
    }

    /**
     * @return array|DataRecord
     */
    public function getLinkedRecord()
    {
        $target  = ( $this->pivot == 'target' ) ? $this->target : $this->source;
        $id_name = $target->getIdName();
        $table   = $target->getTable();
        $id      = $target->getId();
        return $this->query()->joinUsing( $table, $id_name )->w( $id_name )->eq( $id )->select();
    }
}
