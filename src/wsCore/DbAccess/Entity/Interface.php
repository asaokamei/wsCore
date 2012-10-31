<?php
namespace wsCore\DbAccess;

interface Entity_Interface
{
    public function _get_Model();
    public function _get_Type();
    public function _get_Id();
    public function relation( $name );
    public function setRelation( $name, $relation );
    public function isIdPermanent();
}
