<?php
namespace wsCore\DbAccess;

/**
 * represents many-to-many relationship using join-table.
 */
class Relation_HasJoined extends Dao
{
    /** @var string|Dao */
    protected $joinModel;
    protected $query;

    /** @var DataRecord */
    protected $source;
    protected $sourceColumn;
    
    /** @var DataRecord */
    protected $target;
    protected $targetModel;
    protected $targetColumn;

    protected $pivot = 'target';

    /**
     * @param DataRecord  $source
     * @param array       $relInfo
     * @return \wsCore\DbAccess\Relation_HasJoined
     */
    public function __construct( $source, $relInfo )
    {
        try {
            $this->joinModel = $source->getDao()->getInstance( [ 'join_model' ] );
            $this->query = $this->joinModel->query();
        }
        catch( \Exception $e ) {
            $this->joinModel = $relInfo[ 'join_model' ];
            $this->query = clone $source->getDao()->query();
            $this->query->table( $this->joinModel );
        }
        $this->source = $source;
        $source_column = ( isset( $relInfo[ 'source_column' ] ) ) ?
            $relInfo[ 'source_column' ] : $source->getIdName();
        $this->sourceColumn = $source_column;
        $this->targetModel  = $relInfo[ 'target_model' ];
        $this->targetColumn = $relInfo[ 'target_column' ];
    }

    /**
     * @param DataRecord $target
     * @return DataRecord
     */
    public function set( $target )
    {
        $this->target = $target;
        $this->targetColumn = ( $this->targetColumn ) ?: $target->getIdName();
        // check if relation already exists. 
        $record = $this->getJoinRecord( $target );
        // adding new relation. 
        if( empty( $record ) ) {
            
            $values = array(
                $this->sourceColumn => $this->source->getId(),
                $this->targetColumn => $this->target->getId(),
            );
            $this->query->insert( $values );
            $record = $this->getJoinRecord( $target );
        }
        return $record;
    }

    /**
     * @param null|DataRecord $target
     * @return Relation_HasOne
     */
    public function del( $target=null ) {
        
    }

    /**
     * @param DataRecord $target
     * @return bool|DataRecord[]
     */
    public function getJoinRecord( $target=null )
    {
        $sourceId = $this->source->getId();
        $this->query()->w( $this->sourceColumn )->eq( $sourceId );
        if( $target ) {
            $targetId = $target->getId();
            $this->query()->w( $this->targetColumn )->eq( $targetId );
        }
        $record = $this->query()->select();
        return $record[0];
    }

    /**
     * @return array|DataRecord
     */
    public function get()
    {
        $targetDao = $this->source->getDao()->getInstance( $this->targetModel );
        $targetTable = $targetDao->table;
        $targetIdName = $targetDao->getIdName();
        return $this->query()->joinUsing( $targetTable, $targetIdName )
            ->w( $this->sourceColumn )->eq( $this->source->getId() )
            ->select();
    }
}
