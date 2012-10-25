<?php
namespace wsCore\DbAccess;

class Relation_HasOne implements Relation_Interface
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
        $this->targetModel  = $relInfo[ 'target_model' ];
        $this->targetDao    = $this->source->getDao()->getInstance( $this->targetModel );
        $this->targetColumn = isset( $relInfo[ 'target_column' ] ) ?
            $relInfo[ 'target_column' ] : $this->targetDao->getIdName();
        $this->sourceColumn = isset( $relInfo[ 'source_column' ] ) ?
            $relInfo[ 'source_column' ] : $this->targetColumn;
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
        if( $this->linked )  return $this;
        if( !$this->source ) return $this;
        if( !$this->target ) return $this;
        // TODO: check if id is permanent or tentative.
        $value = $this->target->get( $this->targetColumn );
        $column = $this->sourceColumn;
        $this->source->$column = $value;
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
        $column = $this->sourceColumn;
        $this->source->$column = NULL;
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

    