<?php
namespace wsCore\Html;

class Element
{
    /** @var null|string        overwrites name ex: name[1] */
    static $var_format = NULL;

    private $type = NULL;

    private $name = NULL;

    private $value = NULL;

    private $tags = NULL;
    /**
     * quick method to create input element.
     *
     * @param $type
     * @param $name
     * @param $value
     * @param array $attributes
     */
    public function input( $type, $name, $value, $attributes=array() ) {}

    /**
     * creates textArea element.
     *
     * @param $name
     * @param $value
     * @param array $attributes
     */
    public function textArea( $name, $value, $attributes=array() ) {}

    /**
     * @param $name
     * @param array $value
     * @param array $attributes
     */
    public function select( $name, $value, $attributes=array() ) {}

    /**
     * makes option list for Select box.
     *
     * @param $option
     * @param $checks
     */
    public function makeOptions( $option, $checks ) {}
    /**
     * set up ime mode in style.
     *
     * @param $ime
     */
    public function ime( $ime ) {}

    /**
     * set id attribute; id is generated from name.
     *
     * @param $id
     */
    public function setId( $id=NULL ) {}

    /**
     * set name attribute. name may be changed (name[1]) for checkbox etc.
     *
     * @param $name
     */
    public function setName( $name ) {}

    /**
     * returns Selector based on $type.
     * $types are:
     *   - NAME      : returns value (html encoded for safety).
     *   - EDIT, NEW : returns HTML form.
     *   - ASIS:     : returns value without encoded.
     *
     * @param string $type
     * @param string $value
     */
    public function show( $type='NAME', $value='' ) {}
    /**
     * returns $value.
     * for Select, Radio, and Checkbox, returns label name from $value.
     */
    public function makeName( $type ) {}

    public function makeHtml( $type ) {}
}