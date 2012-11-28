<?php
namespace WScore\Html;

/** 
 * @method \WScore\Html\Tags a()
 * @method \WScore\Html\Tags href()
 * @method \WScore\Html\Tags target()
 * @method \WScore\Html\Tags style()
 * @method \WScore\Html\Tags div()
 * @method \WScore\Html\Tags input()
 * @method \WScore\Html\Tags value()
 * @method \WScore\Html\Tags required()
 * @method \WScore\Html\Tags p()
 * @method \WScore\Html\Tags bold()
 * @method \WScore\Html\Tags i()
 * @method \WScore\Html\Tags em()
 * @method \WScore\Html\Tags option()
 * @method \WScore\Html\Tags checked
 * @method \WScore\Html\Tags optgroup
 * @method \WScore\Html\Tags label
 * @method \WScore\Html\Tags ul
 * @method \WScore\Html\Tags nl
 * @method \WScore\Html\Tags li
 * @method \WScore\Html\Tags table
 * @method \WScore\Html\Tags tr
 * @method \WScore\Html\Tags th
 * @method \WScore\Html\Tags td
 * @method \WScore\Html\Tags span
 * @method \WScore\Html\Tags dl
 * @method \WScore\Html\Tags dd
 * @method \WScore\Html\Tags dt
 * @method \WScore\Html\Tags h1
 * @method \WScore\Html\Tags h2
 * @method \WScore\Html\Tags h3
 * @method \WScore\Html\Tags h4
 * @method \WScore\Html\Tags form
 * @method \WScore\Html\Tags action
 * @method \WScore\Html\Tags method
 * @method \WScore\Html\Tags strong
 */
class Tags
{
    /** @var null                  name of tag, such as span */
    protected  $tagName    = null;
    
    /** @var array                 array of contents         */
    protected $contents   = array();
    
    /** @var array                 array of attributes       */
    public $attributes = array();
    
    /** @var bool                  for form element's name   */
    protected $multiple = false;

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
        'span', 'p', 'strong', 'i', 'sub', 'li', 'a', 'label',
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
    public function __invoke( $tagName=NULL, $contents=NULL ) {
        return $this->_( $tagName, $contents );
    }

    /**
     * construction of Tag object.
     *
     * @param string|null  $tagName
     * @param null|string $contents
     * @return \WScore\Html\Tags
     */
    public function __construct( $tagName=null, $contents=null )
    {
        $this->setTagName_( $tagName );
        $this->setContents_( $contents );
    }

    /**
     * @param string|null  $tagName
     * @param null|string $contents
     * @return \WScore\Html\Tags
     */
    public function _( $tagName=NULL, $contents=NULL )
    {
        $class = get_called_class();
        return new $class( $tagName, $contents );
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
            return $this->_( $name, $args );
        }
        else {
            $this->setAttribute_( $name, $args );
        }
        return $this;
    }

    /**
     * make string VERY safe for html.
     *
     * @param string $value
     * @return string
     */
    public static function safe_( $value ) {
        if( empty( $value ) ) return $value;
        return htmlentities( $value, ENT_QUOTES, static::$encoding );
    }

    /**
     * wrap value with closure. use this to avoid encoding attribute values.
     *
     * @param string $value
     * @return callable
     */
    public static function wrap_( $value ) {
        return function() use( $value ) { return $value; };
    }

    public function isSpanTag() {
        return in_array( $this->tagName, static::$tag_span );
    }

    public function isNoBodyTag() {
        return in_array( $this->tagName, static::$tag_no_body );
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
    protected function setTagName_( $tagName )
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
    protected function setContents_( $contents ) {
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
    protected function setAttribute_( $name, $value, $connector=null )
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
        if( $value === false ) return $this;     // ignore the property.
        $name = $this->normalize_( $name );
        if( $value === true  ) $value = $name;   // same as name (checked="checked")
        // set connector if it is not set.
        if( $connector === null ) {
            $connector = false;                  // default is to replace value.
            if( array_key_exists( $name, static::$attribute_connectors ) ) {
                $connector = static::$attribute_connectors[ $name ];
            }
        }
        // set attribute.
        if( !isset( $this->attributes[ $name ] ) // new attribute.
            || $connector === false ) {          // replace with new value.
            $this->attributes[ $name ] = $value;
        }
        else {                                   // attribute is appended.
            $this->attributes[ $name ] .= $connector . $value;
        }
        return $this;
    }

    /**
     * normalize tag and attribute name: lower case, and remove first _ if exists. 
     * 
     * @param string $name
     * @return string
     */
    protected function normalize_( $name ) {
        $name = strtolower( $name );
        if( $name[0]=='_') $name = substr( $name, 1 );
        $name = str_replace( '_', '-', $name );
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
     * @param string $class
     * @param string $connector    set FALSE to reset class.
     * @return Tags
     */
    public function _class( $class, $connector=' ' ) {
        return $this->setAttribute_( 'class', $class, $connector );
    }

    /**
     * set style. adds to the existing style.
     *
     * @param string $style
     * @param string $connector    set FALSE to reset style.
     * @return Tags
     */
    public function _style( $style, $connector='; ' ) {
        return $this->setAttribute_( 'style', $style, $connector );
    }

    /**
     * @param \Closure $func
     * @param string $attribute
     */
    public function walk( $func, $attribute=null )
    {
        if( !$attribute || $this->$attribute || isset( $this->attributes[ $attribute ] ) ) {
            $func( $this );
        }
        if( !empty( $this->contents ) ) {
            foreach( $this->contents as $content ) {
                if( $content instanceof self ) {
                    $content->walk( $func, $attribute );
                }
            }
        }
    }
    // +----------------------------------------------------------------------+
    //  convert Tags to a string.
    // +----------------------------------------------------------------------+
    /**
     * @param string $head
     * @return string
     */
    protected function toContents_( $head="" ) {
        $html = '';
        if( empty( $this->contents ) ) return $html;
        foreach( $this->contents as $content ) {
            if( !$this->isNoBodyTag() && !$this->isSpanTag() && $html && substr( $html, -1 ) != "\n" ) {
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
    protected function toAttribute_() {
        $attr = '';
        if( !empty( $this->attributes ) )
            foreach( $this->attributes as $name => $value ) {
                if( $value instanceof \Closure ) {
                    $value = $value(); // wrapped by closure. use it as is.
                }
                else {
                    $value = static::safe_( $value ); // make it very safe.
                }
                $attr .= " {$name}=\"{$value}\"";
            }
        return $attr;
    }

    /**
     * @param string $head
     * @return string
     */
    protected function toString_( $head='' )
    {
        $html = $head;
        if( static::isNoBodyTag() ) {
            // create tag without content, such as <tag attributes... />
            $html .= "<{$this->tagName}" . $this->toAttribute_() . ' />';
        }
        elseif( static::isSpanTag() || count( $this->contents ) == 1 ) {
            // short tag such as <tag>only one content</tag>
            $html .= "<{$this->tagName}" . $this->toAttribute_() . ">";
            $html .= $this->toContents_();
            $html .= "</{$this->tagName}>";
        }
        else { // create tag with contents inside.
            $html .= "<{$this->tagName}" . $this->toAttribute_() . ">";
            $html .= "\n";
            $html .= $this->toContents_( $head . '  ' );
            if( substr( $html, -1 ) != "\n" ) $html .= "\n";
            $html .= $head . "</{$this->tagName}>";
        }
        if( !static::isSpanTag() && !static::isNoBodyTag() ) {
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