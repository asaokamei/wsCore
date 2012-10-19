<?php
namespace wsCore\DbAccess;

/**
 * represents many-to-many relationship using join-table.
 */
class Relation_HasJoined implements Relation_Interface
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

    protected $linked = false;

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
        // get query from source's dao's query!
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
     * @return Relation_Interface
     */
    public function set( $target )
    {
        $this->target = $target;
        if( !$target ) return $this;
        $this->target = $target;
        $this->linked = false;
        $this->link();
        return $this;
    }

    public function link( $save=false )
    {
        if( $this->linked )  return $this;
        if( !$this->source ) return $this;
        if( !$this->target ) return $this;
        // set up.
        if( !$this->targetColumn     ) $this->targetColumn     = $this->target->getIdName();
        if( !$this->joinTargetColumn ) $this->joinTargetColumn = $this->targetColumn;
        // check if relation already exists.
        $record = $this->getJoinRecord( $this->target );
        // adding new relation.
        // TODO: check if id is permanent or tentative.
        if( empty( $record ) ) {
            $values = array(
                $this->sourceColumn => $this->source->get( $this->sourceColumn ),
                $this->targetColumn => $this->target->get( $this->targetColumn ),
            );
            $this->query->insert( $values );
        }
        $this->linked = true;
        return $this;
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
        if( !$target ) $target = $this->target;
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
        $record = $targetDao->query()
            ->joinOn(
                $this->joinTable,
                "{$this->targetModel}.{$this->targetColumn}={$this->joinTable}.{$this->joinTargetColumn}"
            )
            ->w( $this->sourceColumn )->eq( $this->source->get( $this->joinSourceColumn ) )
            ->select();
        return $record;
    }

    /**
     * @return bool
     */
    public function isLinked() {
        return $this->linked;
    }
}
