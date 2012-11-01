<?php
namespace wsCore\DbAccess;

interface Relation_Interface
{
    /**
     * @param Entity_Interface $target
     * @return Relation_Interface
     */
    public function set( $target );

    /**
     * @param Entity_Interface $target
     * @return Relation_Interface
     */
    public function del( $target=null );

    /**
     * @return Entity_Interface[]
     */
    public function get();

    /**
     * @return Entity_Interface[]
     */
    public function getJoinRecord();

    /**
     * @param bool $save
     * @return Relation_Interface
     */
    public function link( $save=false );

    /**
     * @return bool
     */
    public function isLinked();
}