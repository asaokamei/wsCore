<?php
namespace wsCore\DbAccess;

class Relation_HasOne implements Relation_Interface
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
        $source_column = ( isset( $relInfo[ 'source_column' ] ) ) ?
            $relInfo[ 'source_column' ] : $relInfo[ 'relation_name' ];
        $this->sourceColumn = $source_column;
        $this->targetModel  = $relInfo[ 'target_model' ];
        $this->targetColumn = $relInfo[ 'target_column' ];
    }

    /**
     * @param \wsCore\DbAccess\DataRecord $target
     * @throws \RuntimeException
     * @return \wsCore\DbAccess\Relation_HasOne|\wsCore\DbAccess\Relation_Interface
     */
    public function set( $target ) 
    {
        if( $target->getModel() != $this->targetModel ) {
            throw new \RuntimeException( "target model not match! " );
        }
        $this->target = $target;
        $this->linked = false;
        $this->link();
        return $this;
    }

    /**
     * @param bool $save
     * @return Relation_HasOne
     */
    public function link( $save=false )
    {
        if( !$this->source ) return $this;
        if( !$this->target ) return $this;
        if( $this->targetColumn ) {
            $value = $this->target->get( $this->targetColumn );
        }
        else {
            // TODO: check if id is permanent or tentative.
            $value = $this->target->getId();
        }
        $this->source->set( $this->sourceColumn, $value );
        $this->linked = true;
        if( $save ) {
            die( "save in link not supported yet." );
            //$this->source->save();
        }
        return $this;
    }

    /**
     * @param null $target
     * @return Relation_HasOne
     */
    public function del( $target=NULL ) {
        $this->source->set( $this->sourceColumn, NULL );
        return $this;
    }

    /**
     * @return array|DataRecord
     */
    public function get()
    {
        /** @var $targetDao \wsCore\DbAccess\Dao */
        $model = $this->targetModel;
        $targetDao   = $this->source->getDao()->getInstance( $model );
        $value = $this->source->get( $this->sourceColumn );
        $targetColumn = ( !$this->targetColumn )? $targetDao->getIdName() : $this->targetColumn;
        return $targetDao->query()->w( $targetColumn )->eq( $value )->select();
    }

    /**
     * @return bool
     */
    public function isLinked() {
        return $this->linked;
    }
}

    