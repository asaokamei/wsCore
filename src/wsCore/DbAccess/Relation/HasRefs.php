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
        $this->sourceColumn = $relInfo[ 'source_column' ];
        $this->targetModel  = $relInfo[ 'target_model' ];
        $this->targetColumn = ( is_null( $relInfo[ 'target_column' ] ) ) ? 
            $source->getIdName() : $relInfo[ 'target_column' ];
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
     * @return Relation_HasRefs
     */
    public function link( $save=false )
    {
        if( !$this->source ) return $this;
        if( !$this->target ) return $this;
        if( $this->sourceColumn ) {
            $value = $this->source->get( $this->sourceColumn );
        }
        else {
            // TODO: check if id is permanent or tentative.
            $value = $this->source->getId();
        }
        $this->target->set( $this->targetColumn, $value );
        $this->linked = true;
        if( $save ) {
            die( "save in link not supported yet." );
            //$this->source->save();
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
        /** @var $dao \wsCore\DbAccess\Dao */
        $model = $this->targetModel;
        $targetDao   = $this->source->getDao()->getInstance( $model );
        $targetColumn = ( !$this->targetColumn )? $targetDao->getIdName() : $this->targetColumn;
        $value = ( !$this->targetColumn )? $this->source->get( $this->sourceColumn ) : $this->source->getId();
        return $targetDao->query()->w( $targetColumn )->eq( $value )->select();
    }

    /**
     * @return bool
     */
    public function isLinked() {
        return $this->linked;
    }
}