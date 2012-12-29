<?php
namespace WScore\DataMapper;

interface Entity_Interface
{
    const _ENTITY_TYPE_GET_  = 'get';   // a new record for insert.
    const _ENTITY_TYPE_NEW_  = 'new';   // record from db for update.

    /**
     * @return null|string
     */
    public function _get_Model();

    /**
     * @return null|string
     */
    public function _get_ShortModel();

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
    public function _get_Identifier();

    /** @return null|string */
    public function _get_id();
    
    /**
     * @return null|string
     */
    public function _get_id_name();
    
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
     * @return bool
     */
    public function _is_valid();

    /**
     * @param null|string $name
     * @return mixed
     */
    public function _pop_error( $name=null );
    /**
     * @param $name
     * @return \WScore\DataMapper\Entity_Interface[]|Entity_Collection
     */
    public function relation( $name );

    /**
     * @param $name
     * @param $relation
     * @return Entity_Interface
     */
    public function setRelation( $name, $relation );
}
