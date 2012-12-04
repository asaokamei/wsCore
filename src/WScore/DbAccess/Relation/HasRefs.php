<?php
namespace WScore\DbAccess;

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

    /** @var Entity_Interface[] */
    protected $targets = array();
    /** @var Model */
    protected $targetModel;
    protected $targetModelName;
    protected $targetColumn;

    protected $linked = false;
    /**
     * @param EntityManager $em
     * @param Entity_Interface   $source
     * @param array        $relInfo
     */
    public function __construct( $em, $source, $relInfo )
    {
        $this->em = $em;
        $default  = array(
            'target_column' => null,
            'source_column' => null,
        );
        $relInfo  = array_merge( $default, $relInfo );

        $this->source          = $source;
        $this->sourceColumn    = $relInfo[ 'source_column' ] ? : $this->em->getModel( $source->_get_Model() )->getIdName();
        $this->targetModelName = $relInfo[ 'target_model' ];
        $this->targetColumn    = $relInfo[ 'target_column' ] ? : $this->sourceColumn;
        // get relation data always. 
        //$this->load();
    }

    /**
     */
    public function load()
    {
        $value  = $this->source[ $this->sourceColumn ];
        $this->targets = $this->em->fetch( $this->targetModelName, $value, $this->targetColumn );
    }

    /**
     * @param Entity_Interface $target
     * @return Relation_HasRefs
     * @throws \RuntimeException
     */
    public function set( $target )
    {
        if( $target->_get_Model() != $this->targetModelName ) {
            throw new \RuntimeException( "target model not match!" );
        }
        $this->targets[] = $target;
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
        if( empty( $this->targets ) ) return $this;
        if( $this->sourceColumn == $this->em->getModel( $this->source->_get_Model() )->getIdName() &&
            !$this->source->isIdPermanent() ) {
            return $this;
        }
        $value  = $this->source[ $this->sourceColumn ];
        foreach( $this->targets as &$entity ) {
            $entity[ $this->targetColumn ] = $value;
            if( $save ) { // TODO: check if this works or not.
                $this->em->saveEntity( $entity );
            }
        }
        $this->linked = true;
        return $this;
    }

    /**
     * @param Entity_Interface $target
     * @return Relation_HasOne
     */
    public function del( $target=null ) {
        if( !is_null( $target ) ) {
            $target[ $this->targetColumn ] = null;
        }
        return $this;
    }

    /**
     * @return Entity_Interface[]
     */
    public function get()
    {
        $column = $this->sourceColumn;
        $value  = $this->source->$column;
        $this->targets = $this->em->fetch( $this->targetModelName, $value, $this->targetColumn );
        return $this->targets;
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