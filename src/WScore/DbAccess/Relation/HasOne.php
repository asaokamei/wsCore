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
    /** @var Model */
    protected $targetModel;
    protected $targetColumn;

    protected $linked = false;

    /**
     * @param EntityManager $em
     * @param Entity_Interface   $source
     * @param array        $relInfo
     */
    public function __construct( $em, $source, $relInfo )
    {
        $this->em     = $em;
        $default      = array(
            'target_column' => null,
            'source_column' => null,
        );
        $relInfo      = array_merge( $default, $relInfo );
        $this->source = $source;
        // set up target/source
        $this->targetModel     = $this->em->getModel( $relInfo[ 'target_model' ] );
        $this->targetColumn    = $relInfo[ 'target_column' ] ? : $this->targetModel->getIdName();
        $this->sourceColumn    = $relInfo[ 'source_column' ] ? : $this->targetColumn;
    }

    /**
     * @param Entity_Interface $target
     * @throws \RuntimeException
     * @return \WScore\DbAccess\Relation_HasOne|\WScore\DbAccess\Relation_Interface
     */
    public function set( $target ) 
    {
        if( $target->_get_Model() != $this->targetModel->getModelName() ) {
            throw new \RuntimeException( "target model not match! " );
        }
        $this->target = $target;
        $this->linked = false;
        $this->link();
        return $this;
    }

    /**
     * @param bool $save
     * @return Relation_HasOne
     */
    public function link( $save=false )
    {
        if( $this->linked )  return $this;
        if( !$this->target ) return $this;
        // TODO: check if id is permanent or tentative.
        $column = $this->targetColumn;
        $value  = $this->target->$column;
        $column = $this->sourceColumn;
        $this->source->$column = $value;
        $this->linked = true;
        if( $save ) { // TODO: check if this works or not.
            $this->em->saveEntity( $this->source );
        }
        return $this;
    }

    /**
     * @param null $target
     * @return Relation_HasOne
     */
    public function del( $target=null ) {
        $column = $this->sourceColumn;
        $this->source->$column = null;
        return $this;
    }

    /**
     * @return Entity_Interface[]
     */
    public function get()
    {
        $column  = $this->sourceColumn;
        $value   = $this->source->$column;
        $records = $this->targetModel->fetch( $value, $this->targetColumn );
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

    