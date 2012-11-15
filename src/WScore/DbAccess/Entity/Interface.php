<?php
namespace WScore\DbAccess;

interface Entity_Interface
{

    /**
     * @return null|string
     */
    public function _get_Model();

    /**
     * @return null|string
     */
    public function _get_ModelClass();

    /**
     * @return null|string
     */
    public function _get_Type();

    /**
     * @return bool
     */
    public function isIdPermanent();

    /**
     * @return null|string
     */
    public function _get_Id();

    /**
     * @throws \RuntimeException
     * @return string
     */
    public function _get_cenaId();

    /**
     * @return bool
     */
    public function toDelete();

    /**
     * @param $name
     * @return \WScore\DbAccess\Relation_Interface
     */
    public function relation( $name );

    /**
     * @param $name
     * @param $relation
     * @return Entity_Interface
     */
    public function setRelation( $name, $relation );
}
