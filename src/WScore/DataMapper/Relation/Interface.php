<?php
namespace WScore\DataMapper;

interface Relation_Interface
{
    /**
     * sets relation between the source and the target entity. 
     * 
     * @param Entity_Interface $target
     * @return Relation_Interface
     */
    public function set( $target );

    /**
     * deletes the relation between the source and the target entity. 
     * 
     * @param Entity_Interface $target
     * @return Relation_Interface
     */
    public function del( $target=null );

    /**
     * gets the related entities. 
     * 
     * @return Entity_Collection|Entity_Interface[]
     */
    public function get();

    /**
     * gets join record for many-to-many (cross table) joins. 
     * 
     * @return Entity_Interface[]
     */
    public function getJoinRecord();

    /**
     * links relationships (i.e. sets foreign keys to appropriate entity). 
     * if $save is set to true, tries to save the related entities for 
     * some types of relations. 
     * 
     * @param bool $save
     * @return Relation_Interface
     */
    public function link( $save=false );

    /**
     * checks if the relationships are all set (i.e. foreign keys are set). 
     * 
     * @return bool
     */
    public function isLinked();

    /**
     * for joined relation. 
     * 
     * @param string $order
     * @return \WScore\DataMapper\Relation_Interface
     */
    public function setOrder( $order );

    /**
     * for joined relation. 
     * 
     * @param array $values
     * @return \WScore\DataMapper\Relation_Interface
     */
    public function setValues( $values );

}