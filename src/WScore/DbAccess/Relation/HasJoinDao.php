<?php
namespace WScore\DbAccess;

/**
 * represents many-to-many relationship using join-table.
 */
class Relation_HasJoinDao implements Relation_Interface
{
    /** @var EntityManager */
    protected $em;

    /** @var string */
    protected $joinModelName;
    /** @var string */
    protected $joinTable;

    /** @var \WScore\DbAccess\Model */
    protected $joinModel;
    
    /** @var \WScore\DbAccess\Entity_Interface[] */
    protected $joints;
    protected $joinSourceColumn;
    protected $joinTargetColumn;

    /** @var \WScore\DbAccess\Entity_Interface */
    protected $source;
    protected $sourceColumn;
    
    /** @var \WScore\DbAccess\Entity_Interface */
    protected $target;

    /** @var \WScore\DbAccess\Model */
    protected $targetModel;
    protected $targetModelName;
    protected $targetColumn;

    protected $order  = null;    // select order for get
    protected $values = array(); // extra values when set

    protected $linked = false;

    /**
     * @param EntityManager $em
     * @param Entity_Interface   $source
     * @param $relInfo
     * @return \WScore\DbAccess\Relation_HasJoinDao
     */
    public function __construct( $em, $source, $relInfo )
    {
        $this->em     = $em;
        // set up join table information.
        $this->source           = $source;
        $this->joinModelName    = $relInfo[ 'join_model' ];
        $this->joinModel        = $this->em->getModel( $this->joinModelName );
        $this->joinTable        = $this->joinModel->getTable();
        // set up about source data.
        $sourceModel = $em->getModel( $source->_get_Model() );
        $this->joinSourceColumn = isset( $relInfo[ 'join_source_column' ] ) ?
            $relInfo[ 'join_source_column' ] : $sourceModel->getIdName();
        $this->sourceColumn = isset( $relInfo[ 'sourceColumn' ] ) ?
            $relInfo[ 'sourceColumn' ] : $sourceModel->getIdName();
        // set up about target data.
        $this->targetModelName      = $relInfo[ 'target_model' ];
        $this->targetModel = $em->getModel( $this->targetModelName );
        $this->joinTargetColumn = isset( $relInfo[ 'join_target_column' ] ) ?
            $relInfo[ 'join_target_column' ] : $this->targetModel->getIdName();
        $this->targetColumn     = isset( $relInfo[ 'target_column' ] ) ?
            $relInfo[ 'target_column' ] : $this->targetModel->getIdName();
    }

    /**
     * load relations information. use it prior to get/del/add/etc.
     *
     * @return Relation_HasJoinDao
     */
    public function load()
    {
        // get joints (join records).
        $value  = $this->source[ $this->joinSourceColumn ];
        $joints = $this->joinModel->fetch( $value, $this->joinSourceColumn );
        if( empty( $joints ) ) return $this;
        // set joints based on joinTargetColumn value.
        $column = $this->joinTargetColumn;
        foreach( $joints as $j ) {
            $this->joints[ $j->$column ] = $j;
        }
        // get target entities. save it as: $this->targets[ cena_id ] = $entity.
        $lists   = $this->em->packToArray( $joints, $this->joinTargetColumn );
        $targets = $this->targetModel->fetch( $lists, $this->targetColumn );
        foreach( $targets as $t ) {
            $this->target[ $t->_get_cenaId() ] = $t;
        }
        return $this;
    }

    /**
     * @param DataRecord $target
     * @return Relation_Interface|Relation_HasJoinDao
     */
    public function set( $target )
    {
        $this->target = $target;
        if( !$target ) return $this;
        $this->linked = false;
        $this->link();
        return $this;
    }

    /**
     * @param array $values
     * @return \WScore\DbAccess\Relation_HasJoinDao
     */
    public function setValues( $values )
    {
        $this->values = $values;
        return $this;
    }
    /**
     * @param bool $save
     * @return Relation_HasJoinDao|Relation_Interface
     */
    public function link( $save=false )
    {
        if( $this->linked )  return $this;
        if( !$this->source ) return $this;
        if( !$this->target ) return $this;
        // check if relation already exists.
        $record = $this->getJoinRecord( $this->target );
        // adding new relation.
        // TODO: check if id is permanent or tentative.
        if( empty( $record ) ) {
            $sourceColumn = $this->sourceColumn;
            $targetColumn = $this->targetColumn;
            $values = array(
                $this->joinSourceColumn => $this->source->$sourceColumn,
                $this->joinTargetColumn => $this->target->$targetColumn,
            );
            if( is_array( $this->values ) && !empty( $this->values ) ) {
                $values = array_merge( $this->values, $values );
            }
            $this->joinModel->insert( $values );
        }
        $this->linked = true;
        return $this;
    }

    /**
     * @param null|DataRecord $target
     * @return Relation_HasOne
     */
    public function del( $target=null )
    {
        $sourceColumn = $this->sourceColumn;
        $sourceValue = $this->source->$sourceColumn;
        $query = $this->joinModel->query();
        $query->w( $this->joinSourceColumn )->eq( $sourceValue );
        if( !$target ) $target = $this->target;
        if( $target ) {
            $targetColumn = $this->targetColumn;
            $targetValue = $target->$targetColumn;
            $query->w( $this->joinTargetColumn )->eq( $targetValue );
        }
        $query->makeDelete()->exec();
        return $this;
    }

    /**
     * @param DataRecord $target
     * @return bool|array|DataRecord[]
     */
    public function getJoinRecord( $target=null )
    {
        $sourceColumn = $this->sourceColumn;
        $sourceValue = $this->source->$sourceColumn;
        $query = $this->joinModel->query();
        $query->w( $this->joinSourceColumn )->eq( $sourceValue );
        if( !$target ) $target = $this->target;
        if( $target ) {
            $targetColumn = $this->targetColumn;
            $targetValue = $target->$targetColumn;
            $query->w( $this->joinTargetColumn )->eq( $targetValue );
        }
        $record = $query->select();
        return $record;
    }

    /**
     * @return DataRecord[]
     */
    public function get()
    {
        if( !$this->target ) $this->load();
        return $this->target;
    }

    /**
     * @param string $order
     * @return \WScore\DbAccess\Relation_HasJoinDao
     */
    public function setOrder( $order )
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLinked() {
        return $this->linked;
    }
}
