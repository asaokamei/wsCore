<?php
namespace wsCore\DbAccess;

/**
 * opposite of Relation_HasOne. 
 */
class Relation_HasRefs implements Relation_Interface
{
    /** @var EntityManager */
    protected $em;

    /** @var Entity_Interface */
    protected $source;
    protected $sourceColumn;

    /** @var Entity_Interface */
    protected $target;
    /** @var Dao */
    protected $targetDao;
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
        $this->source = $source;
        $this->sourceColumn = isset( $relInfo[ 'source_column' ] ) ?
            $relInfo[ 'source_column' ] : $this->em->getModel( $source->_get_Model() )->getIdName() ;
        $this->targetModel  = $relInfo[ 'target_model' ];
        $this->targetColumn = ( isset( $relInfo[ 'target_column' ] ) ) ?
            $relInfo[ 'target_column' ] : $this->sourceColumn ;
        $this->targetDao   = $this->em->getModel( $this->targetModel );
    }

    /**
     * @param Entity_Interface $target
     * @return Relation_HasRefs
     * @throws \RuntimeException
     */
    public function set( $target )
    {
        if( $target->_get_Model() != $this->targetModel ) {
            throw new \RuntimeException( "target model not match!" );
        }
        $this->target = $target;
        $this->linked = false;
        $this->link();
        return $this;
    }

    /**
     * @param bool $save
     * @return Relation_Interface
     */
    public function link( $save=false )
    {
        if( $this->linked )  return $this;
        if( !$this->source ) return $this;
        if( !$this->target ) return $this;
        if( $this->sourceColumn == $this->em->getModel( $this->source->_get_Model() )->getIdName() &&
            !$this->source->isIdPermanent() ) {
            return $this;
        }
        $column = $this->sourceColumn;
        $value = $this->source->$column;
        $column = $this->targetColumn;
        $this->target->$column = $value;
        $this->linked = true;
        if( $save ) {
            $this->em->save();
        }
        return $this;
    }

    /**
     * @param Entity_Interface $target
     * @return Relation_HasOne
     */
    public function del( $target=NULL ) {
        if( !is_null( $target ) ) {
            $column = $this->targetColumn;
            $target->$column = NULL;
        }
        return $this;
    }

    /**
     * @return Entity_Interface[]
     */
    public function get()
    {
        $column = $this->sourceColumn;
        $value = $this->source->$column;
        return $this->targetDao->query()->w( $this->targetColumn )->eq( $value )->select();
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
}