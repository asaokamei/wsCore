<?php
namespace wsCore\DbAccess;

/**
 * opposite of Relation_HasOne. 
 */
class Relation_HasRefs
{
    protected $source;
    protected $source_column;
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
        $this->source_column = $relInfo[ 'source_column' ];
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
        if( $this->source_column ) {
            $value = $this->source->get( $this->source_column );
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
    public function del( $target=null ) {
        if( !is_null( $target ) ) {
            $target->set( $this->targetColumn, null );
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
        // TODO: need getInstance in Dao. 
        $dao   = $model::getInstance();
        $value = $this->source->get( $this->source_column );
        return $dao->query()->w( $this->targetColumn )->eq( $value )->select();
    }
}