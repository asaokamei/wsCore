<?php
namespace wsCore\DbAccess;

interface Relation_Interface
{
    /**
     * @param DataRecord $target
     * @return Relation_Interface
     */
    public function set( $target );

    /**
     * @param DataRecord $target
     * @return Relation_Interface
     */
    public function del( $target=null );

    /**
     * @return DataRecord[]
     */
    public function get();

    /**
     * @return DataRecord[]
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