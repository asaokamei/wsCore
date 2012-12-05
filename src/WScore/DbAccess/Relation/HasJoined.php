<?php
namespace WScore\DbAccess;

/**
 * represents many-to-many relationship using join-table without Model.
 * WARNING: this class is not fully maintained.
 */
class Relation_HasJoined implements Relation_Interface
{
    /** @var string */
    protected $relationName = '';

    /** @var EntityManager */
    protected $em;

    /** @var string */
    protected $joinTable;
    protected $joinSourceColumn;
    protected $joinTargetColumn;

    /** @var \WScore\DbAccess\Query */
    protected $query;

    /** @var Entity_Interface */
    protected $source;
    protected $sourceColumn;
    
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
     * @return \WScore\DbAccess\Relation_HasJoined
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
        // set up join table information.
        $this->source    = $source;
        $sourceModel     = $em->getModel( $source->_get_Model() );
        $this->query     = clone $sourceModel->query();
        $this->joinTable = $relInfo[ 'join_table' ];
        // set up about source data.
        $this->joinSourceColumn = $relInfo[ 'join_source_column' ] ? : $sourceModel->getIdName();
        $this->sourceColumn     = $relInfo[ 'source_column' ] ? : $sourceModel->getIdName();
        // set up about target data.
        $this->targetModelName  = $relInfo[ 'target_model' ];
        $this->targetModel      = $em->getModel( $this->targetModelName );
        $this->joinTargetColumn = $relInfo[ 'join_target_column' ] ? : $this->targetModel->getIdName();
        $this->targetColumn     = $relInfo[ 'target_column' ] ? : $this->targetModel->getIdName();
    }
    /**
     * load relations information. use it prior to get/del/add/etc.
     *
     * @return Relation_HasJoinDao
     */
    public function load()
    {
        $targets = $this->get();
        $this->source->setRelation( $this->relationName, $targets );
    }

    /**
     * @param Entity_Interface $target
     * @return Relation_Interface
     */
    public function set( $target )
    {
        if( !$target ) return $this;
        $targets = $this->source->relation( $this->relationName );
        $targets[] = $target;
        $this->source->setRelation( $this->relationName, $targets );
        $this->linked = false;
        $this->link();
        return $this;
    }

    /**
     * @param array $values
     * @return \WScore\DbAccess\Relation_Interface
     */
    public function setValues( $values )
    {
        $this->values = $values;
        return $this;
    }

    public function link( $save=false )
    {
        if( $this->linked )  return $this;
        if( !$this->source ) return $this;
        $targets = $this->source->relation( $this->relationName );
        if( !$targets ) return $this;
        // check if relation already exists.
        foreach( $targets as $target )
        {
            $record = $this->getJoinRecord( $target );
            if( empty( $record ) ) {
                $values = array(
                    $this->joinSourceColumn => $this->source[ $this->sourceColumn ],
                    $this->joinTargetColumn => $target[       $this->targetColumn ],
                );
                if( is_array( $this->values ) && !empty( $this->values ) ) {
                    $values = array_merge( $this->values, $values );
                }
                $this->query->table( $this->joinTable )->insert( $values );
            }
        }
        $this->linked = true;
        return $this;
    }

    /**
     * @param null|Entity_Interface $target
     * @return Relation_HasOne
     */
    public function del( $target=null ) 
    {
        $sourceValue = $this->source[ $this->sourceColumn ];
        $query = $this->query->table( $this->joinTable );
        $query->w( $this->joinSourceColumn )->eq( $sourceValue );
        if( $target ) {
            $targetValue = $target[ $this->targetColumn ];
            $query->w( $this->joinTargetColumn )->eq( $targetValue );
        }
        $query->makeDelete()->exec();
        $this->load();
        return $this;
    }

    /**
     * @param Entity_Interface $target
     * @return bool|array
     */
    public function getJoinRecord( $target=null )
    {
        $sourceValue = $this->source[ $this->sourceColumn ];
        $query = $this->query->table( $this->joinTable );
        $query->w( $this->joinSourceColumn )->eq( $sourceValue );
        if( $target ) {
            $targetValue = $target[ $this->targetColumn ];
            $query->w( $this->joinTargetColumn )->eq( $targetValue );
        }
        $record = $query->select();
        return $record;
    }

    /**
     * @return Entity_Interface[]
     */
    public function get()
    {
        $table  = $this->targetModel->getTable();
        $order  = isset( $this->order ) ? $this->order : $this->joinSourceColumn;
        $column = $this->sourceColumn;
        $record = $this->targetModel->query()
            ->joinOn(
                $this->joinTable,
                "{$table}.{$this->targetColumn}={$this->joinTable}.{$this->joinTargetColumn}"
            )
            ->w( $this->joinSourceColumn )->eq( $this->source->$column )
            ->order( $order )
            ->select();
        return $record;
    }


    /**
     * @param string $order
     * @return \WScore\DbAccess\Relation_Interface
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
