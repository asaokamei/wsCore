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
            'source_column'      => null,
            'target_column'      => null,
            'join_source_column' => null,
            'join_target_column' => null,
        );
        $relInfo = array_merge( $default, $relInfo );
        // set up about source data.
        $sourceModel            = $em->getModel( $source->_get_Model() );
        $this->source           = $source;
        $this->sourceColumn     = $relInfo[ 'source_column' ] ?: $sourceModel->getIdName();
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
        $results = array();
        foreach( $targets as $t ) {
            $this->loadJoint( $t );
            $results[ $t->_get_cenaId() ] = $t;
        }
        $this->source->setRelation( $this->relationName, $results );
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
        $targets = $this->source->relation( $this->relationName );
        $targets[ $target->_get_cenaId() ] = $target;
        $this->source->setRelation( $this->relationName, $targets );
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
        $targets = $this->source->relation( $this->relationName );
        if( !$targets ) return $this;
        // check if relation already exists.
        foreach( $targets as $target ) {
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
        $targets = $this->source->relation( $this->relationName );
        if( $target ) {
            $targetValue = $target[ $this->targetColumn ];
            if( isset( $this->joints[ $targetValue ] ) ) {
                $this->em->register( $this->joints[ $targetValue ] );
                $this->em->delete( $this->joints[ $targetValue ] );
                unset( $this->joints[ $targetValue ] );
            }
            if( isset( $targets[ $target->_get_cenaId() ] ) ) {
                unset( $targets[ $target->_get_cenaId() ] );
            }
        }
        else {
            foreach( $this->joints as $joint ) {
                $this->em->delete( $joint );
            }
            $targets = array();
            $this->joints = array();
        }
        $this->source->setRelation( $this->relationName, $targets );
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
        $targets = $this->source->relation( $this->relationName );
        return $targets;
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

    /**
     * @param \WScore\DbAccess\Entity_Interface[] $targets
     */
    public function replace( $targets )
    {
        if( !is_array( $targets ) ) $targets = array( $targets );
        $newTarget = array();
        foreach( $targets as $t ) {
            $newTarget[ $t->_get_cenaId() ] = $t;
        }
        if( $currTargets = $this->source->relation( $this->relationName ) ) {
            foreach( $currTargets as $t ) {
                if( !isset( $newTarget[ $t->_get_cenaId() ] ) ) {
                    $this->del( $t );
                }
            }
        }
        foreach( $newTarget as $cenaId => $t ) {
            if( !isset( $currTargets[ $cenaId ] ) ) {
                $this->set( $t );
            }
        }
    }
}
