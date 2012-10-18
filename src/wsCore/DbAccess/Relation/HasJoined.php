<?php
namespace wsCore\DbAccess;

/**
 * represents many-to-many relationship using join-table.
 */
class Relation_HasJoined
{
    /** @var string */
    protected $joinTable;
    protected $joinSourceColumn;
    protected $joinTargetColumn;
    /** @var \wsCore\DbAccess\Query */
    protected $query;

    /** @var DataRecord */
    protected $source;
    protected $sourceColumn;
    
    /** @var DataRecord */
    protected $target;
    protected $targetModel;
    protected $targetColumn;

    /**
     * @param DataRecord  $source
     * @param array       $relInfo
     * @return \wsCore\DbAccess\Relation_HasJoined
     */
    public function __construct( $source, $relInfo )
    {
        // set up join table information.
        $this->joinTable        = $relInfo[ 'join_table' ];
        $this->joinSourceColumn = $relInfo[ 'join_source_column' ];
        $this->joinTargetColumn = $relInfo[ 'join_target_column' ];
        $this->query            = clone $source->getDao()->query();
        $this->query->table( $this->joinTable );
        // set up source data.
        $this->source           = $source;
        $this->sourceColumn     = ( isset( $relInfo[ 'source_column' ] ) ) ?
            $relInfo[ 'source_column' ] : $source->getIdName();
        if( !$this->joinSourceColumn ) $this->joinSourceColumn = $this->sourceColumn;
        // set up target data.
        $this->targetModel  = $relInfo[ 'target_model' ];
        $this->targetColumn = $relInfo[ 'target_column' ];
    }

    /**
     * @param DataRecord $target
     * @return array
     */
    public function set( $target )
    {
        // set up.
        $this->target = $target;
        if( !$this->targetColumn     ) $this->targetColumn     = $target->getIdName();
        if( !$this->joinTargetColumn ) $this->joinTargetColumn = $this->targetColumn;
        // check if relation already exists.
        $record = $this->getJoinRecord( $target );
        // adding new relation. 
        if( empty( $record ) ) {
            $values = array(
                $this->sourceColumn => $this->source->get( $this->sourceColumn ),
                $this->targetColumn => $this->target->get( $this->targetColumn ),
            );
            $this->query->insert( $values );
            $record = $this->getJoinRecord( $target );
        }
        return $record[0];
    }

    /**
     * @param null|DataRecord $target
     * @return Relation_HasOne
     */
    public function del( $target=null ) {
        
    }

    /**
     * @param DataRecord $target
     * @return bool|array
     */
    public function getJoinRecord( $target=null )
    {
        $sourceId = $this->source->getId();
        $this->query->w( $this->sourceColumn )->eq( $sourceId );
        if( $target ) {
            $targetId = $target->getId();
            $this->query->w( $this->targetColumn )->eq( $targetId );
        }
        $record = $this->query->select();
        return $record;
    }

    /**
     * @return DataRecord[]
     */
    public function get()
    {
        $targetDao = $this->source->getDao()->getInstance( $this->targetModel );
        $targetDao->query()
            ->joinOn(
                $this->joinTable,
                "{$this->targetModel}.{$this->targetColumn}={$this->joinTable}.{$this->joinTargetColumn}"
            )
            ->w( $this->sourceColumn )->eq( $this->source->get( $this->joinSourceColumn ) )
            ->select();
    }
}
