<?php
namespace WScore\Html;

/**
 * todo: rename methods:
 * [ _class, _style ] -> [ class_, style_ ] , [ safe_, wrap_ ] -> [ _safe, _wrap ]
 * [ _contain -> _contain ], _ = _contain,
 *
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
    public $_attributes = array();
    
    /** @var bool                  for form element's name   */
    protected $multiple = false;

    /** @var array                 normalize tag name  */
    public static $_normalize_tag = array(
        'b'       => 'strong',
        'bold'    => 'strong',
        'italic'  => 'i',
        'image'   => 'img',
        'item'    => 'li',
        'order'   => 'ol',
        'number'  => 'nl',
    );
    /** @var array                  tags without contents */
    public static $_tag_no_body = array(
        'br', 'img', 'input',
    );
    /** @var array                  in-line tags   */
    public static $_tag_span = array(
        'span', 'p', 'strong', 'i', 'sub', 'li', 'a', 'label',
    );
    /** @var array                  how to connect attribute values */
    public static $_attribute_connectors = array(
        'class' => ' ',
        'style' => '; ',
    );
    /** @var string                 encoding */
    public static $_encoding = 'UTF-8';

    /** @var bool                   true for tags such as <img /> */
    public $_noBodyTag = false;
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
    public function __invoke( $tagName=null, $contents=null ) {
        return $this->_new( $tagName, $contents );
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
        $this->_setTagName( $tagName );
        $this->_setContents( $contents );
    }

    /**
     * @param string|null  $tagName
     * @param null|string $contents
     * @return \WScore\Html\Tags
     */
    public function _new( $tagName=null, $contents=null )
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
            return $this->_new( $name, $args );
        }
        else {
            $this->_setAttribute( $name, $args );
        }
        return $this;
    }

    public function __get( $name ) {
        if( is_null( $this->tagName ) ) {
            return $this->_new( $name );
        }
        $this->_setAttribute( $name, true );
        return $this;
    }
    /**
     * make string VERY safe for html.
     *
     * @param string $value
     * @return string
     */
    public static function _safe( $value ) {
        if( empty( $value ) ) return $value;
        return htmlentities( $value, ENT_QUOTES, static::$_encoding );
    }

    /**
     * wrap value with closure. use this to avoid encoding attribute values.
     *
     * @param string $value
     * @return callable
     */
    public static function _wrap( $value ) {
        return function() use( $value ) { return $value; };
    }

    public function _isSpanTag() {
        return in_array( $this->tagName, static::$_tag_span );
    }

    public function _isNoBodyTag() {
        return $this->_noBodyTag;
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
    protected function _setTagName( $tagName )
    {
        if( empty( $tagName ) ) return $this;
        $tagName = $this->_normalize( $tagName );
        if( array_key_exists( $tagName, static::$_normalize_tag ) ) {
            $tagName = static::$_normalize_tag[ $tagName ];
        }
        $this->tagName = $tagName;
        if( in_array( $this->tagName, static::$_tag_no_body ) ) {
            $this->_noBodyTag = true;
        }
        return $this;
    }
    
    /**
     * set contents. 
     * 
     * @param string|array|Tags $contents
     * @return Tags
     */
    protected function _setContents( $contents ) {
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
    protected function _setAttribute( $name, $value, $connector=null )
    {
        if( is_array( $value ) && !empty( $value ) ) {
            foreach( $value as $val ) {
                $this->_setAttribute( $name, $val, $connector );
            }
            return $this;
        }
        elseif( is_array( $value ) ) {
            $value = '';
        }
        if( $value === false ) return $this;     // ignore the property.
        $name = $this->_normalize( $name );
        if( $value === true  ) $value = $name;   // same as name (checked="checked")
        // set connector if it is not set.
        if( $connector === null ) {
            $connector = false;                  // default is to replace value.
            if( array_key_exists( $name, static::$_attribute_connectors ) ) {
                $connector = static::$_attribute_connectors[ $name ];
            }
        }
        // set attribute.
        if( !isset( $this->_attributes[ $name ] ) // new attribute.
            || $connector === false ) {          // replace with new value.
            $this->_attributes[ $name ] = $value;
        }
        else {                                   // attribute is appended.
            $this->_attributes[ $name ] .= $connector . $value;
        }
        return $this;
    }

    /**
     * normalize tag and attribute name: lower case, and remove first _ if exists. 
     * 
     * @param string $name
     * @return string
     */
    protected function _normalize( $name ) {
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
    public function _contain()
    {
        /** @var $args array */
        $args = func_get_args();
        return $this->_setContents( $args );
    }

    /**
     * set class name. adds to the existing class.
     *
     * @param string $class
     * @param string $connector    set FALSE to reset class.
     * @return Tags
     */
    public function class_( $class, $connector=' ' ) {
        return $this->_setAttribute( 'class', $class, $connector );
    }

    /**
     * set style. adds to the existing style.
     *
     * @param string $style
     * @param string $connector    set FALSE to reset style.
     * @return Tags
     */
    public function style_( $style, $connector='; ' ) {
        return $this->_setAttribute( 'style', $style, $connector );
    }

    /**
     * @param \Closure $func
     * @param string $attribute
     */
    public function _walk( $func, $attribute=null )
    {
        if( !$attribute || isset( $this->$attribute ) || isset( $this->_attributes[ $attribute ] ) ) {
            $func( $this );
        }
        if( !empty( $this->contents ) ) {
            foreach( $this->contents as $content ) {
                if( $content instanceof self ) {
                    $content->_walk( $func, $attribute );
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
    protected function _toContents( $head="" ) {
        $html = '';
        if( empty( $this->contents ) ) return $html;
        foreach( $this->contents as $content ) {
            if( !$this->_isNoBodyTag() && !$this->_isSpanTag() && $html && substr( $html, -1 ) != "\n" ) {
                $html .= "\n";
            }
            if( is_object( $content ) && method_exists( $content, '_toString' ) ) {
                $html .= $content->_toString( $head );
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
    protected function _toAttribute() {
        $attr = '';
        if( !empty( $this->_attributes ) )
            foreach( $this->_attributes as $name => $value ) {
                if( $value instanceof \Closure ) {
                    $value = $value(); // wrapped by closure. use it as is.
                }
                else {
                    $value = static::_safe( $value ); // make it very safe.
                }
                $attr .= " {$name}=\"{$value}\"";
            }
        return $attr;
    }

    /**
     * @param string $head
     * @return string
     */
    protected function _toString( $head='' )
    {
        $html = $head;
        if( $this->_isNoBodyTag() ) {
            // create tag without content, such as <tag attributes... />
            $html .= "<{$this->tagName}" . $this->_toAttribute() . ' />';
        }
        elseif( $this->_isSpanTag() || count( $this->contents ) == 1 ) {
            // short tag such as <tag>only one content</tag>
            $html .= "<{$this->tagName}" . $this->_toAttribute() . ">";
            $html .= $this->_toContents();
            $html .= "</{$this->tagName}>";
        }
        else { // create tag with contents inside.
            $html .= "<{$this->tagName}" . $this->_toAttribute() . ">";
            $html .= "\n";
            $html .= $this->_toContents( $head . '  ' );
            if( substr( $html, -1 ) != "\n" ) $html .= "\n";
            $html .= $head . "</{$this->tagName}>";
        }
        if( !$this->_isSpanTag() && !$this->_isNoBodyTag() ) {
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
        return $this->_toString();
    }
    // +----------------------------------------------------------------------+
}