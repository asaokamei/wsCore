<?php
namespace wsCore\DbAccess;

/**
 * opposite of Relation_HasOne. 
 */
class Relation_HasRefs implements Relation_Interface
{
    /** @var DataRecord */
    protected $source;
    protected $sourceColumn;

    /** @var DataRecord */
    protected $target;
    /** @var Dao */
    protected $targetDao;
    protected $targetModel;
    protected $targetColumn;

    protected $linked = false;
    /**
     * @param DataRecord   $source
     * @param array        $relInfo
     */
    public function __construct( $source, $relInfo )
    {
        $this->source = $source;
        $this->sourceColumn = isset( $relInfo[ 'source_column' ] ) ?
            $relInfo[ 'source_column' ] : $source->getIdName() ;
        $this->targetModel  = $relInfo[ 'target_model' ];
        $this->targetColumn = ( isset( $relInfo[ 'target_column' ] ) ) ?
            $relInfo[ 'target_column' ] : $this->sourceColumn ;
        $this->targetDao   = $this->source->getDao()->getInstance( $this->targetModel );
    }

    /**
     * @param DataRecord $target
     * @return Relation_HasRefs
     * @throws \RuntimeException
     */
    public function set( $target )
    {
        if( $target->getModel() != $this->targetModel ) {
            throw new \RuntimeException( "target model not match!" );
        }
        $this->target = $target;
        $this->linked = false;
        $this->link();
        return $this;
    }

    /**
     * @param bool $save
     * @return Relation_HasRefs|Relation_Interface
     */
    public function link( $save=false )
    {
        if( $this->linked )  return $this;
        if( !$this->source ) return $this;
        if( !$this->target ) return $this;
        // TODO: check if id is permanent or tentative.
        if( $this->sourceColumn == $this->source->getIdName() &&
            !$this->source->isIdPermanent() ) {
            return $this;
        }
        $value = $this->source->get( $this->sourceColumn );
        $this->target->set( $this->targetColumn, $value );
        $this->linked = true;
        if( $save ) {
            $this->target->save();
        }
        return $this;
    }

    /**
     * @param DataRecord $target
     * @return Relation_HasOne
     */
    public function del( $target=NULL ) {
        if( !is_null( $target ) ) {
            $target->set( $this->targetColumn, NULL );
        }
        return $this;
    }

    /**
     * @return array|DataRecord
     */
    public function get()
    {
        $value = $this->source->get( $this->sourceColumn );
        return $this->targetDao->query()->w( $this->targetColumn )->eq( $value )->select();
    }

    /**
     * @return void|DataRecord[]
     */
    public function getJoinRecord() {}

    /**
     * @return bool
     */
    public function isLinked() {
        return $this->linked;
    }
}