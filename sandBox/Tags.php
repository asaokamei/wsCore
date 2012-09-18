<?php

class Tags
{
    private static $_top;
    private $tags = NULL;
    private $inside = array();
    private $attributes = array();

    public function __construct( $tag=NULL, $inside=array(), $attributes=array() ) {
        $this->tags = $tag;
        $this->inside = is_array( $inside )? $inside: array( $inside );
        $this->attributes = is_array( $attributes )? $attributes: array( $attributes );
    }
    public static function start( $tag=NULL, $inside=NULL, $attributes=NULL ) {
        $class = get_called_class();
        static::$_top = new $class( $tag, $inside, $attributes );
        return static::$_top;
    }
    public function inner( $contents ) {}
    public function setClass( $class ) {}
    public function setName( $name ) {}
    public function setId( $id ) {}
    public function __call( $name, $args ) {

    }
}