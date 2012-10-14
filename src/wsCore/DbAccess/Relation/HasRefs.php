<?php
namespace wsCore\DbAccess;

/**
 * opposite of Relation_HasOne. 
 */
class Relation_HasRefs
{
    protected $source;
    protected $column;
    protected $targetModel;
    protected $targetColumn;

    /**
     * @param DataRecord                    $source
     * @param                               $targetColumn
     * @param string                        $targetModel
     * @param string                        $column
     */
    public function __construct( $source, $targetColumn, $targetModel, $column=NULL )
    {
        $this->source = $source;
        $this->column = $column;
        $this->targetModel  = $targetModel;
        $this->targetColumn = $targetColumn;
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
        if( $this->column ) {
            $value = $this->source->get( $this->column );
        }
        else {
            // TODO: check if id is permanent or tentative. 
            $value = $this->source->getId();
        }
        $target->set( $this->targetColumn, $value );
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