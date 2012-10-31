<?php
namespace wsTests\DataMapper\Mock;

class EntityManager extends \wsCore\DbAccess\EntityManager
{
    public function returnModels() {
        return $this->models;
    }
    public function returnNewId() {
        return $this->newId;
    }
    public function returnReflections() {
        return $this->reflections;
    }
    public function returnEntities() {
        return $this->entities;
    }
}