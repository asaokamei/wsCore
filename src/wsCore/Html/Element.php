<?php
namespace wsCore\Html;
/*

$element->form(
    $element->input( text, user_name, taro-san ),
    $element->radio( gender, [ m=>male, f=>female ], m ),
    $element->select( ages, [10=>teen, 20=>twenties, 30=>over], 20 )
)->action( 'do.php' );

 */
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
    public function input( $type, $name, $value=NULL, $attributes=array() ) {}

    /**
     * creates textArea element.
     *
     * @param $name
     * @param $value
     * @param array $attributes
     */
    public function textArea( $name, $value=NULL, $attributes=array() ) {}

    /**
     * @param $name
     * @param $items
     * @param array $value
     * @param array $attributes
     */
    public function select( $name, $items, $value=NULL, $attributes=array() ) {}

    /**
     * makes option list for Select box.
     *
     * @param $option
     * @param $checks
     */
    public function makeOptions( $option, $checks ) {}

    public function radio( $name, $items, $value=NULL, $attributes=array() ) {}

    public function check( $name, $items, $value=NULL, $attributes=array() ) {}

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
    public function makeName() {}

    /**
     * returns html element.
     */
    public function makeHtml() {}
}