<?php
namespace wsCore\DbAccess;

class Relation_HasOne
{
    protected $source;
    protected $column;
    protected $targetModel;
    protected $targetColumn;

    /**
     * @param \wsCore\DbAccess\DataRecord   $source
     * @param string                        $column
     * @param string                        $targetModel
     * @param string|null                   $targetColumn
     */
    public function __construct( $source, $column, $targetModel, $targetColumn=NULL )
    {
        $this->source = $source;
        $this->column = $column;
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
            throw new \RuntimeException( "target model not match!" );
        }
        if( $this->targetColumn ) {
            $value = $target->get( $this->targetColumn );
        }
        else {
            // TODO: check if id is permanent or tentative. 
            $value = $target->getId();
        }
        $this->source->set( $this->column, $value );
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
        $value = $this->source->get( $this->column );
        return $dao->query()->w( $this->targetColumn )->eq( $value )->select();
    }
}

    