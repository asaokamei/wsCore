<?php
namespace wsCore\Html;

/** 
 * @method \wsCore\Html\Tags a()
 * @method \wsCore\Html\Tags href()
 * @method \wsCore\Html\Tags target()
 * @method \wsCore\Html\Tags style()
 * @method \wsCore\Html\Tags div()
 * @method \wsCore\Html\Tags input()
 * @method \wsCore\Html\Tags value()
 * @method \wsCore\Html\Tags required()
 * @method \wsCore\Html\Tags p()
 * @method \wsCore\Html\Tags bold()
 * @method \wsCore\Html\Tags i()
 * @method \wsCore\Html\Tags em()
 * @method \wsCore\Html\Tags option()
 * @method \wsCore\Html\Tags checked
 * @method \wsCore\Html\Tags optgroup
 * @method \wsCore\Html\Tags label
 * @method \wsCore\Html\Tags nl
 * @method \wsCore\Html\Tags li
 * @method \wsCore\Html\Tags table
 * @method \wsCore\Html\Tags tr
 * @method \wsCore\Html\Tags th
 * @method \wsCore\Html\Tags td
 * @method \wsCore\Html\Tags span
 * @method \wsCore\Html\Tags dl
 * @method \wsCore\Html\Tags dd
 * @method \wsCore\Html\Tags dt
 * @method \wsCore\Html\Tags h1
 * @method \wsCore\Html\Tags h2
 * @method \wsCore\Html\Tags h3
 * @method \wsCore\Html\Tags h4
 */
class Tags
{
    /** @var null                  name of tag, such as span */
    protected  $tagName    = NULL;
    
    /** @var array                 array of contents         */
    protected $contents   = array();
    
    /** @var array                 array of attributes       */
    public $attributes = array();
    
    /** @var bool                  for form element's name   */
    protected $multiple = FALSE;

    /** @var array                 normalize tag name  */
    public static $normalize_tag = array(
        'b'       => 'strong',
        'bold'    => 'strong',
        'italic'  => 'i',
        'image'   => 'img',
        'item'    => 'li',
        'order'   => 'ol',
        'number'  => 'nl',
    );
    /** @var array                  tags without contents */
    public static $tag_no_body = array(
        'br', 'img', 'input',
    );
    /** @var array                  in-line tags   */
    public static $tag_span = array(
        'span', 'p', 'strong', 'i', 'sub', 'li', '',
    );
    /** @var array                  how to connect attribute values */
    public static $attribute_connectors = array(
        'class' => ' ',
        'style' => '; ',
    );
    /** @var string                 encoding */
    public static $encoding = 'UTF-8';
    // +----------------------------------------------------------------------+
    //  constructions and static methods
    // +----------------------------------------------------------------------+
    /**
     * Start Tag object, with or without tag name.
     * 
     * @param null $tagName
     * @param null $contents
     * @return Tags
     */
    public function __invoke( $tagName=NULL, $contents=NULL ) 
    {
        $class = get_called_class();
        return new $class( $tagName, $contents );
    }

    /**
     * construction of Tag object.
     *
     * @param string|null  $tagName
     * @param null|string $contents
     * @return \wsCore\Html\Tags
     */
    public function __construct( $tagName=NULL, $contents=NULL ) 
    {
        $this->setTagName_( $tagName );
        $this->setContents_( $contents );
    }

    /**
     * @param string|null  $tagName
     * @param null|string $contents
     * @return \wsCore\Html\Tags
     */
    public function _( $tagName=NULL, $contents=NULL )
    {
        $class = get_called_class();
        return new $class( $tagName, $contents );
    }

    /**
     * make string VERY safe for html.
     *
     * @param $value
     * @return string
     */
    public static function safe_( $value ) {
        if( empty( $value ) ) return $value;
        return htmlentities( $value, ENT_QUOTES, static::$encoding );
    }

    /**
     * wrap value with closure. use this to avoid encoding attribute values.
     *
     * @param $value
     * @return callable
     */
    public static function wrap_( $value ) {
        return function() use( $value ) { return $value; };
    }
    // +----------------------------------------------------------------------+
    //  mostly internal functions
    // +----------------------------------------------------------------------+
    /**
     * set tag name.
     * 
     * @param string $tagName
     * @return Tags
     */
    public function setTagName_( $tagName )
    {
        if( empty( $tagName ) ) return $this;
        $tagName = $this->normalize_( $tagName );
        if( array_key_exists( $tagName, static::$normalize_tag ) ) {
            $tagName = static::$normalize_tag[ $tagName ];
        }
        $this->tagName = $tagName;
        return $this;
    }
    
    /**
     * set contents. 
     * 
     * @param string|array|Tags $contents
     * @return Tags
     */
    public function setContents_( $contents ) {
        if( empty( $contents ) ) return $this;
        if( is_array( $contents ) ) {
            $this->contents = array_merge( $this->contents, $contents );
        }
        else {
            $this->contents[] = $contents;
        }
        return $this;
    }

    /**
     * set attribute. if connector is not set, attribute is replaced. 
     * 
     * @param string       $name
     * @param string|array $value
     * @param bool|string  $connector
     * @return Tags
     */
    public function setAttribute_( $name, $value, $connector=NULL )
    {
        if( is_array( $value ) && !empty( $value ) ) {
            foreach( $value as $val ) {
                $this->setAttribute_( $name, $val, $connector );
            }
            return $this;
        }
        elseif( is_array( $value ) ) {
            $value = '';
        }
        if( $value === false ) return $this; // ignore the property.
        if( $value === true  ) $value = $name; // copy the attribute name (checked="checked")
        $name = $this->normalize_( $name );
        // set connector if it is not set.
        if( $connector === NULL ) {
            $connector = FALSE; // default is to replace value.
            if( array_key_exists( $name, static::$attribute_connectors ) ) {
                $connector = static::$attribute_connectors[ $name ];
            }
        }
        // set attribute.
        if( !isset( $this->attributes[ $name ] ) // new attribute.
            || $connector === FALSE ) {          // replace with new value.
            $this->attributes[ $name ] = $value;
        }
        else {
            // attribute is appended. 
            $this->attributes[ $name ] .= $connector . $value;
        }
        return $this;
    }

    /**
     * normalize tag and attribute name: lower case, and remove first _ if exists. 
     * 
     * @param $name
     * @return string
     */
    public function normalize_( $name ) {
        $name = strtolower( $name );
        if( $name[0]=='_') $name = substr( $name, 1 );
        return $name;
    }
    // +----------------------------------------------------------------------+
    //  methods for setting tags, attributes, and contents.
    // +----------------------------------------------------------------------+
    /**
     * set contents.
     *
     * @internal param array|string|Tags $contents
     * @return Tags
     */
    public function contain_()
    {
        /** @var $args array */
        $args = func_get_args();
        return $this->setContents_( $args );
    }

    /**
     * set class name. adds to the existing class.
     *
     * @param $class
     * @param string $connector    set FALSE to reset class.
     * @return Tags
     */
    public function _class( $class, $connector=' ' ) {
        return $this->setAttribute_( 'class', $class, $connector );
    }

    /**
     * set style. adds to the existing style.
     *
     * @param $style
     * @param string $connector    set FALSE to reset style.
     * @return Tags
     */
    public function _style( $style, $connector='; ' ) {
        return $this->setAttribute_( 'style', $style, $connector );
    }

    /**
     * set attribute, or tagName if tagName is not set.
     * 
     * @param string $name
     * @param array  $args
     * @return Tags
     */
    public function __call( $name, $args ) 
    {
        // attribute or tag if not set.
        if( is_null( $this->tagName ) ) { // set it as a tag name
            return new static( $name, $args );
        }
        else {
            $this->setAttribute_( $name, $args );
        }
        return $this;
    }
    // +----------------------------------------------------------------------+
    //  convert Tags to a string.
    // +----------------------------------------------------------------------+
    /**
     * @param string $head
     * @return string
     */
    public function toContents_( $head="" ) {
        $html = '';
        if( empty( $this->contents ) ) return $html;
        foreach( $this->contents as $content ) {
            if( $html && substr( $html, -1 ) != "\n" ) {
                $html .= "\n";
            }
            if( is_object( $content ) && method_exists( $content, 'toString_' ) ) {
                $html .= $content->toString_( $head );
            }
            else {
                $html .= $head . (string) $content;
            }
        }
        return $html;
    }

    /**
     * @return string
     */
    public function toAttribute_() {
        $attr = '';
        if( !empty( $this->attributes ) )
            foreach( $this->attributes as $name => $value ) {
                if( $value instanceof \Closure ) {
                    $value = $value(); // wrapped by closure. use it as is.
                }
                else {
                    $value = static::safe_( $value ); // make it very safe.
                }
                // add [] for names for form elements such as checkbox. 
                if( $name == 'name' && $this->multiple ) {
                    $value .= '[]';
                }
                $attr .= " {$name}=\"{$value}\"";
            }
        return $attr;
    }

    /**
     * @param string $head
     * @return string
     */
    public function toString_( $head='' )
    {
        $html = $head;
        if( in_array( $this->tagName, static::$tag_no_body ) ) {
            // create tag without content, such as <tag attritutes... />
            $html .= "<{$this->tagName}" . $this->toAttribute_() . ' />';
        }
        elseif( count( $this->contents ) == 1 ) {
            // short tag such as <tag>only one content</tag>
            $html .= "<{$this->tagName}" . $this->toAttribute_() . ">";
            $html .= $this->toContents_();
            $html .= "</{$this->tagName}>";
        }
        else { // create tag with contents inside.
            $html .= "<{$this->tagName}" . $this->toAttribute_() . ">";
            $html .= "\n";
            $html .= $this->toContents_( $head . '  ' );
            $html .= $head . "</{$this->tagName}>";
        }
        if( !in_array( $this->tagName, static::$tag_span ) ) {
            // add new-line, except for in-line tags.
            $html .= "\n";
        }
        return $html;
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString_();
    }
    // +----------------------------------------------------------------------+
}