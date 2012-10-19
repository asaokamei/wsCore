<?php
namespace wsCore\DbAccess;

/**
 * represents many-to-many relationship using join-table.
 */
class Relation_HasJoinDao implements Relation_Interface
{
    /** @var string */
    protected $joinModel;
    /** @var string */
    protected $joinTable;

    /** @var \wsCore\DbAccess\Dao */
    protected $joinDao;
    protected $joinSourceColumn;
    protected $joinTargetColumn;

    /** @var DataRecord */
    protected $source;
    protected $sourceColumn;
    
    /** @var DataRecord */
    protected $target;

    /** @var \wsCore\DbAccess\Dao */
    protected $targetDao;
    protected $targetModel;
    protected $targetColumn;

    protected $linked = false;

    /**
     * @param DataRecord  $source
     * @param array       $relInfo
     * @return \wsCore\DbAccess\Relation_HasJoinDao
     */
    public function __construct( $source, $relInfo )
    {
        // set up join table information.
        $this->source           = $source;
        $this->query            = clone $source->getDao()->query();
        $this->joinModel        = $relInfo[ 'join_model' ];
        $this->joinDao          = $this->source->getDao()->getInstance( $this->joinModel );
        $this->joinTable        = $this->joinDao->getTable();
        // set up about source data.
        $this->joinSourceColumn = isset( $relInfo[ 'join_source_column' ] ) ?
            $relInfo[ 'join_source_column' ] : $source->getIdName();
        $this->sourceColumn = isset( $relInfo[ 'sourceColumn' ] ) ?
            $relInfo[ 'sourceColumn' ] : $source->getIdName();
        // set up about target data.
        $this->targetModel      = $relInfo[ 'target_model' ];
        $this->targetDao = $this->source->getDao()->getInstance( $this->targetModel );
        $this->joinTargetColumn = isset( $relInfo[ 'join_target_column' ] ) ?
            $relInfo[ 'join_target_column' ] : $this->targetDao->getIdName();
        $this->targetColumn     = isset( $relInfo[ 'target_column' ] ) ?
            $relInfo[ 'target_column' ] : $this->targetDao->getIdName();
    }

    /**
     * @param DataRecord $target
     * @return Relation_Interface|Relation_HasJoinDao
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
        // check if relation already exists.
        $record = $this->getJoinRecord( $this->target );
        // adding new relation.
        // TODO: check if id is permanent or tentative.
        if( empty( $record ) ) {
            $values = array(
                $this->joinSourceColumn => $this->source->get( $this->sourceColumn ),
                $this->joinTargetColumn => $this->target->get( $this->targetColumn ),
            );
            $this->joinDao->query()->table( $this->joinTable )->insert( $values );
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
     * @return bool|array|DataRecord[]
     */
    public function getJoinRecord( $target=null )
    {
        $sourceValue = $this->source->get( $this->sourceColumn );
        $query = $this->joinDao->query();
        $query->w( $this->joinSourceColumn )->eq( $sourceValue );
        if( !$target ) $target = $this->target;
        if( $target ) {
            $targetValue = $target->get( $this->targetColumn );
            $query->w( $this->joinTargetColumn )->eq( $targetValue );
        }
        $record = $query->select();
        return $record;
    }

    /**
     * @return DataRecord[]
     */
    public function get()
    {
        $table  = $this->targetDao->getTable();
        $record = $this->targetDao->query()
            ->joinOn(
                $this->joinTable,
                "{$table}.{$this->targetColumn}={$this->joinTable}.{$this->joinTargetColumn}"
            )
            ->w( $this->joinSourceColumn )->eq( $this->source->get( $this->sourceColumn ) )
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
