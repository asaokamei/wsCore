<?php
namespace WScore\DataMapper;

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

    protected $linked = true;
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
        if( $this->ready() ) {
            $value   = $this->source[ $this->sourceColumn ];
            $targets = $this->em->fetch( $this->targetModelName, $value, $this->targetColumn );
            $targets->bind( $this->targetColumn, $value );
        }
        else {
            $targets = $this->em->emptyCollection();
        }
        $this->source->setRelation( $this->relationName, $targets );
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
        $targets->add( $target );
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
        if( !$targets->count() ) return $this; // nothing to link. 
        // check if the source ID is ready to use. 
        if( !$this->linked ) { // not linked yet. 
            if( !$this->ready() ) { // not ready to link (id not ready(. 
                return $this;
            }
            $value   = $this->source[ $this->sourceColumn ];
            $targets->bind( $this->targetColumn, $value );
            $this->linked = true;
        }
        if( $save ) { // TODO: check if this works or not.
            foreach( $targets as $entity ) {
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
     * @return \WScore\DataMapper\Relation_Interface
     */
    public function setOrder( $order ) {
        return $this;
    }

    /**
     * @param array $values
     * @return \WScore\DataMapper\Relation_Interface
     */
    public function setValues( $values ) {
        return $this;
    }

    /**
     * @return bool
     */
    private function ready() {
        if( $this->sourceColumn == $this->source->_get_id_name() &&
            !$this->source->isIdPermanent() ) {
            return false;
        }
        return true;
    }
}