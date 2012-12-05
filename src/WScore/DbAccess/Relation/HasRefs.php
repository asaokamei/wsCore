<?php
namespace WScore\DbAccess;

/**
 * opposite of Relation_HasOne. 
 */
class Relation_HasRefs implements Relation_Interface
{
    /** @var string */
    protected $relationName = '';

    /** @var EntityManager */
    protected $em;

    /** @var Entity_Interface */
    protected $source;
    protected $sourceColumn;

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
        $this->relationName = $relInfo[ 'relation_name' ];
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
        $this->load();
    }

    /**
     */
    public function load()
    {
        $value  = $this->source[ $this->sourceColumn ];
        $targets = $this->em->fetch( $this->targetModelName, $value, $this->targetColumn );
        $results = array();
        if( !empty( $targets ) )
        foreach( $targets as $t ) {
            $results[ $t->_get_cenaId() ] = $t;
        }
        $this->source->setRelation( $this->relationName, $results );
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
        $targets = $this->source->relation( $this->relationName );
        $targets[ $target->_get_cenaId() ] = $target;
        $this->source->setRelation( $this->relationName, $targets );
        $this->linked = true;
        $this->link();
        return $this;
    }

    /**
     * @param bool $save
     * @return Relation_Interface
     */
    public function link( $save=false )
    {
        $targets = $this->source->relation( $this->relationName );
        if( empty( $targets ) ) return $this;
        if( $this->sourceColumn == $this->em->getModel( $this->source->_get_Model() )->getIdName() &&
            !$this->source->isIdPermanent() ) {
            $this->linked = false;
            return $this;
        }
        $value  = $this->source[ $this->sourceColumn ];
        foreach( $targets as $entity ) {
            $entity[ $this->targetColumn ] = $value;
            if( $save ) { // TODO: check if this works or not.
                $this->em->saveEntity( $entity );
            }
        }
        return $this;
    }

    /**
     * @param Entity_Interface $target
     * @return Relation_HasOne
     */
    public function del( $target=null )
    {
        $targets = $this->source->relation( $this->relationName );
        if( $target ) {
            $target[ $this->targetColumn ] = null;
            if( isset( $targets[ $target->_get_cenaId() ] ) ) {
                unset( $targets[ $target->_get_cenaId() ] );
            }
        }
        else {
            foreach( $targets as $target ) {
                $target[ $this->targetColumn ] = null;
            }
            $targets = array();
        }
        $this->source->setRelation( $this->relationName, $targets );
        return $this;
    }

    /**
     * @return Entity_Interface[]
     */
    public function get()
    {
        $targets = $this->source->relation( $this->relationName );
        return $targets;
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