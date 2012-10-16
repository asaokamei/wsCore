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
}