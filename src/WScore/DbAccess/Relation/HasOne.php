<?php
namespace WScore\DbAccess;

class Relation_HasOne implements Relation_Interface
{
    /** @var EntityManager */
    protected $em;
    
    /** @var Entity_Interface */
    protected $source;
    protected $sourceColumn;

    /** @var Entity_Interface */
    protected $target;
    protected $targetModel;
    protected $targetColumn;

    protected $linked = FALSE;

    /**
     * @param EntityManager $em
     * @param Entity_Interface   $source
     * @param array        $relInfo
     */
    public function __construct( $em, $source, $relInfo )
    {
        $this->em     = $em;
        $this->source = $source;
        $this->targetModel  = $relInfo[ 'target_model' ];
        $this->targetColumn = isset( $relInfo[ 'target_column' ] ) ?
            $relInfo[ 'target_column' ] : $this->em->getModel( $this->targetModel )->getIdName();
        $this->sourceColumn = isset( $relInfo[ 'source_column' ] ) ?
            $relInfo[ 'source_column' ] : $this->targetColumn;
    }

    /**
     * @param Entity_Interface $target
     * @throws \RuntimeException
     * @return \WScore\DbAccess\Relation_HasOne|\WScore\DbAccess\Relation_Interface
     */
    public function set( $target ) 
    {
        if( $target->_get_Model() != $this->targetModel ) {
            throw new \RuntimeException( "target model not match! " );
        }
        $this->target = $target;
        $this->linked = FALSE;
        $this->link();
        return $this;
    }

    /**
     * @param bool $save
     * @return Relation_HasOne
     */
    public function link( $save=FALSE )
    {
        if( $this->linked )  return $this;
        if( !$this->source ) return $this;
        if( !$this->target ) return $this;
        // TODO: check if id is permanent or tentative.
        $column = $this->targetColumn;
        $value = $this->target->$column;
        $column = $this->sourceColumn;
        $this->source->$column = $value;
        $this->linked = TRUE;
        if( $save ) {
            die( "save in link not supported yet." );
            //$this->source->save();
        }
        return $this;
    }

    /**
     * @param null $target
     * @return Relation_HasOne
     */
    public function del( $target=NULL ) {
        $column = $this->sourceColumn;
        $this->source->$column = NULL;
        return $this;
    }

    /**
     * @return Entity_Interface[]
     */
    public function get()
    {
        $column = $this->sourceColumn;
        $value = $this->source->$column;
        $records = $this->em->fetchByModel( $this->targetModel, $value, $this->targetColumn );
        $this->target = $records[0]; // HasOne has only one record, as name indicates.
        return $records;
    }

    /**
     * @return void|Entity_Interface[]
     */
    public function getJoinRecord() {}

    /**
     * @return bool
     */
    public function isLinked() {
        return $this->linked;
    }

    /**
     * @param string $order
     * @return \WScore\DbAccess\Relation_Interface
     */
    public function setOrder( $order ) {
        return $this;
    }

    /**
     * @param array $values
     * @return \WScore\DbAccess\Relation_Interface
     */
    public function setValues( $values ) {
        return $this;
    }
}

    