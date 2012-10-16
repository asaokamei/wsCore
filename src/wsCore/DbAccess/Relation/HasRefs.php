<?php
namespace wsCore\DbAccess;

/**
 * opposite of Relation_HasOne. 
 */
class Relation_HasRefs
{
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
        if( $this->sourceColumn ) {
            $value = $this->source->get( $this->sourceColumn );
        }
        else {
            // TODO: check if id is permanent or tentative. 
            $value = $this->source->getId();
        }
        $target->set( $this->targetColumn, $value );
        $this->target = $target;
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
        $targetDao   = Dao::getInstance( $model, $this->source->getDao() );
        $targetColumn = ( !$this->targetColumn )? $targetDao->getIdName() : $this->targetColumn;
        $value = ( !$this->targetColumn )? $this->source->get( $this->sourceColumn ) : $this->source->getId();
        return $targetDao->query()->w( $targetColumn )->eq( $value )->select();
    }
}