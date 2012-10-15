<?php
namespace wsCore\DbAccess;

class Relation_HasOne
{
    /** @var DataRecord */
    protected $source;
    protected $source_column;
    protected $target;
    protected $targetModel;
    protected $targetColumn;

    /**
     * @param DataRecord $source
     * @param string                        $source_column
     * @param string                        $targetModel
     * @param string|null                   $targetColumn
     */
    public function __construct( $source, $source_column, $targetModel, $targetColumn=NULL )
    {
        $this->source = $source;
        $this->source_column = $source_column;
        $this->targetModel  = $targetModel;
        $this->targetColumn = $targetColumn;
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
        $this->source->set( $this->source_column, $value );
        $this->target = $target;
        return $this;
    }
    public function del( $target ) {

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

    