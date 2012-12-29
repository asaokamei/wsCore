<?php
namespace WScore\DataMapper;

class Relation_HasOne implements Relation_Interface
{
    /** @var string */
    protected $relationName = '';

    /** @var EntityManager */
    protected $em;
    
    /** @var Entity_Interface */
    protected $source;
    protected $sourceColumn;

    /** @var string */
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
        $this->em     = $em;
        $default      = array(
            'target_column' => null,
            'source_column' => null,
        );
        $relInfo      = array_merge( $default, $relInfo );
        $this->source = $source;
        // set up target/source
        $this->targetModelName = $relInfo[ 'target_model' ];
        $this->targetColumn    = $relInfo[ 'target_column' ] ? : $this->em->getIdName( $this->targetModelName );
        $this->sourceColumn    = $relInfo[ 'source_column' ] ? : $this->targetColumn;
        // always load relation data. 
        $this->load();
    }

    /**
     */
    public function load()
    {
        $value   = $this->source[ $this->sourceColumn ];
        if( $value ) {
            $target = $this->em->fetch( $this->targetModelName, $value, $this->targetColumn );
        }
        else {
            $target = $this->em->emptyCollection();
        }
        $this->source->setRelation( $this->relationName, $target );
    }

    /**
     * @param Entity_Interface $target
     * @throws \RuntimeException
     * @return \WScore\DataMapper\Relation_HasOne|\WScore\DataMapper\Relation_Interface
     */
    public function set( $target ) 
    {
        if( $target->_get_Model() != $this->targetModelName ) {
            throw new \RuntimeException( "target model not match! " );
        }
        $collection = $this->source->relation( $this->relationName );
        $collection->clear();
        $collection->add( $target );
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
        if( !$collection = $this->source->relation( $this->relationName ) ) return $this;
        // TODO: check if id is permanent or tentative.
        if( !$target  = $collection->first() ) return $this;
        
        $value   = $target[ $this->targetColumn ];
        $this->source[ $this->sourceColumn ] = $value;
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
        $this->source[ $this->sourceColumn ] = null;
        return $this;
    }

    /**
     * @return Entity_Interface[]
     */
    public function get()
    {
        return $this->source->relation( $this->relationName );
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
}

    