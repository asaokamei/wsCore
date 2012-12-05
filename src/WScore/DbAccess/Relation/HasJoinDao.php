<?php
namespace WScore\DbAccess;

/**
 * represents many-to-many relationship using join-table.
 */
class Relation_HasJoinDao implements Relation_Interface
{
    /** @var string */
    protected $relationName = '';

    /** @var EntityManager */
    protected $em;

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
    protected $targetColumn;

    protected $order  = null;    // select order for get
    protected $values = array(); // extra values when set

    protected $linked = true;

    /**
     * @param EntityManager $em
     * @param Entity_Interface   $source
     * @param $relInfo
     * @return \WScore\DbAccess\Relation_HasJoinDao
     */
    public function __construct( $em, $source, $relInfo )
    {
        $this->relationName = $relInfo[ 'relation_name' ];
        $this->em     = $em;
        $default      = array(
            'sourceColumn'       => null,
            'target_column'      => null,
            'join_source_column' => null,
            'join_target_column' => null,
        );
        $relInfo = array_merge( $default, $relInfo );
        // set up about source data.
        $sourceModel            = $em->getModel( $source->_get_Model() );
        $this->source           = $source;
        $this->sourceColumn     = $relInfo[ 'sourceColumn' ] ?: $sourceModel->getIdName();
        // set up about target data.
        $this->targetModel      = $em->getModel( $relInfo[ 'target_model' ] );
        $this->targetColumn     = $relInfo[ 'target_column' ] ?: $this->targetModel->getIdName();
        // set up join table information.
        $this->joinModel        = $this->em->getModel( $relInfo[ 'join_model' ] );
        $this->joinSourceColumn = $relInfo[ 'join_source_column' ] ?: $sourceModel->getIdName();
        $this->joinTargetColumn = $relInfo[ 'join_target_column' ] ?: $this->targetModel->getIdName();
        // always load relations. 
        $this->load();
    }

    /**
     * load relations information. use it prior to get/del/add/etc.
     *
     * @return Relation_HasJoinDao
     */
    public function load()
    {
        // get joints (join records).
        $value  = $this->source[ $this->sourceColumn ];
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
            $this->loadJoint( $t );
            $this->target[ $t->_get_cenaId() ] = $t;
        }
        return $this;
    }

    /**
     * load joint data associated with the target entity into the entity. 
     * 
     * @param \WScore\DbAccess\Entity_Interface $target
     */
    public function loadJoint( $target )
    {
        $value = $target[ $this->targetColumn ];
        if( isset( $this->joints[ $value ] ) ) {
            foreach( $this->joints[ $value ] as $k => $v ) {
                if( !isset( $target->$k ) ) $target->$k = $v;
            }
        }
    }

    /**
     * create relation between the target and source entity. returns the joint entity. 
     * 
     * @param \WScore\DbAccess\Entity_Interface $target
     * @return \WScore\DbAccess\Entity_Interface
     */
    public function set( $target )
    {
        if( !$target ) return $this;
        $this->target[ $target->_get_cenaId() ] = $target;
        $this->linked = true;
        $this->link();
        return $this->getJoinRecord( $target );
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
        if( !$this->target ) return $this;
        // check if relation already exists.
        foreach( $this->target as $target ) {
            $this->linkTarget( $target );
        }
        $this->linked = true;
        return $this;
    }

    /**
     * create joint entity for the target and register it to em. hence linked. 
     * 
     * @param \WScore\DbAccess\Entity_Interface $target
     * @return \WScore\DbAccess\Entity_Interface
     */
    public function linkTarget( $target )
    {
        $targetValue = $target[ $this->targetColumn ];
        if( !isset( $this->joints[ $targetValue ] ) ) {
            $values = array(
                $this->joinSourceColumn => $this->source[ $this->sourceColumn ],
                $this->joinTargetColumn => $target[ $this->targetColumn ],
            );
            if( is_array( $this->values ) && !empty( $this->values ) ) {
                $values = array_merge( $this->values, $values );
            }
            $joint = $this->joinModel->getRecord( $values );
            $joint = $this->em->register( $joint );
            $this->joints[ $targetValue ] = $joint;
        }
        return $this->joints[ $targetValue ];
    }

    /**
     * deletes the relation between the source and target entities. 
     * 
     * @param null|\WScore\DbAccess\Entity_Interface $target
     * @return Relation_HasOne
     */
    public function del( $target=null )
    {
        if( !$target && !empty( $this->joints ) ) {
            foreach( $this->joints as $joint ) {
                $this->em->delete( $joint );
            }
            $this->joints = array();
            return $this;
        }
        $targetValue = $target[ $this->targetColumn ];
        if( isset( $this->target[ $target->_get_cenaId() ] ) ) {
            unset( $this->target[ $target->_get_cenaId() ] );
        }
        if( isset( $this->joints[ $targetValue ] ) ) {
            $this->em->delete( $this->joints[ $targetValue ] );
            unset( $this->joints[ $targetValue ] );
            return $this;
        }
        return $this;
    }

    /**
     * returns the joint entity for the target (without creating a new relation). 
     * returns false if no relation exists. 
     * 
     * @param DataRecord $target
     * @return bool|array|\WScore\DbAccess\Entity_Interface|\WScore\DbAccess\Entity_Interface[]
     */
    public function getJoinRecord( $target=null )
    {
        if( !$target ) return $this->joints;
        $targetValue = $target[ $this->targetColumn ];
        if( isset( $this->joints[ $targetValue ] ) ) return $this->joints[ $targetValue ];
        return false;
    }

    /**
     * returns the related entities (i.e. targets) for the source entity. 
     * 
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
