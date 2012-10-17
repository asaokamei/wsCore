<?php
namespace wsCore\DbAccess;

class Relation_HasOne
{
    /** @var DataRecord */
    protected $source;
    protected $sourceColumn;
    protected $target;
    protected $targetModel;
    protected $targetColumn;

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
     * @return \wsCore\DbAccess\Relation_HasOne
     */
    public function set( $target ) 
    {
        if( $target->getModel() != $this->targetModel ) {
            throw new \RuntimeException( "target model not match! " );
        }
        if( $this->targetColumn ) {
            $value = $target->get( $this->targetColumn );
        }
        else {
            // TODO: check if id is permanent or tentative. 
            $value = $target->getId();
        }
        $this->source->set( $this->sourceColumn, $value );
        $this->target = $target;
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
}

    