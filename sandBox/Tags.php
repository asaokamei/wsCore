<?php
/*

Example of Usage:

$tags()->tagName()->attr( value )->_contains( text, text2 )...
$tags()->tagName( text, text2 )->attr( value )...
$tags( tagName )->attr( value )->_contains( text, text2 )...
$tags( tagName, text, text2 )->attr( value )...

$tags( 'div' )->attribute( '...' )->_class( 'myClass' )->_id( 'myId' )
    ->_contain(
        $tags()->p( 'this is a paragraph, ' . $tags( 'b', 'with bold.' ) ),
        $tags()->div(
            $tags()->a( 'a link' )->href( 'pop.html' )
        ),
    );

 */

class Tags
{
    /** @var null                  name of tag, such as span */
    private $tagName    = NULL;
    
    /** @var array                 array of contents         */
    private $contents   = array();
    
    /** @var array                 array of attributes       */
    private $attributes = array();

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
    public static $tag_no_body = array(
        'br', 'img', 'input',
    );
    public static $tag_span = array(
        'span', 'p', 'strong', 'i', 'sub', ''
    );
    // +----------------------------------------------------------------------+
    //  constructions
    // +----------------------------------------------------------------------+
    /**
     * Start Tag object, with or without tag name.
     * 
     * @param null $tagName
     * @param null $contents
     * @return mixed
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
     * @param string|null  $contents
     */
    public function __construct( $tagName=NULL, $contents=NULL ) 
    {
        $this->_setTagName_( $tagName );
        $this->_setContents_( $contents );
    }

    /**
     * set tag name.
     * 
     * @param string $tagName
     * @return Tags
     */
    public function _setTagName_( $tagName )
    {
        if( empty( $tagName ) ) return $this;
        $tagName = $this->_normalize_( $tagName );
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
    public function _setContents_( $contents ) {
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
    public function _setAttribute_( $name, $value, $connector=FALSE ) 
    {
        if( is_array( $value ) ) {
            foreach( $value as $val ) {
                $this->_setAttribute_( $name, $val, $connector );
            }
            return $this;
        }
        if( empty( $value ) ) {
            return $this;
        }
        $name = $this->_normalize_( $name );
        if( !isset( $value ) ) {
            $value = $name;   // i.e. required, checked, etc. 
        }
        // set attribute. 
        if( !isset( $this->attributes[ $name ] ) ) {
            // new attribute. just set value to it. 
            $this->attributes[ $name ] = $value;
        }
        elseif( $connector === FALSE ) {
            // attribute is replaced with new value. 
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
    public function _normalize_( $name ) {
        $name = strtolower( $name );
        $name = ( $name[0]=='_') ? substr( $name, 1 ) : $name;
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
        return $this->_setContents_( $args );
    }

    /**
     * set class name. adds to the existing class. 
     * 
     * @param $class
     * @return Tags
     */
    public function _class( $class ) {
        return $this->_setAttribute_( 'class', $class, ' ' );
    }

    /**
     * set style. adds to the existing style.
     * 
     * @param $style
     * @return Tags
     */
    public function _style( $style ) {
        return $this->_setAttribute_( 'style', $style, '; ' );
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
            $this->_setTagName_( $name );
            $this->_setContents_( $args );
        }
        else {
            $this->_setAttribute_( $name, $args );
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
    public function _toContents_( $head="" ) {
        $html = '';
        if( !empty( $this->contents ) )
            foreach( $this->contents as $content ) {
                if( $html && substr( $html, -1 ) != "\n" ) {
                    $html .= "\n";
                }
                $html .= $head . (string) $content;
            }
        return $html;
    }

    /**
     * @return string
     */
    public function _toAttribute() {
        $attr = '';
        if( !empty( $this->attributes ) )
            foreach( $this->attributes as $name => $value ) {
                $attr .= " {$name}=\"{$value}\"";
            }
        return $attr;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $html = '';
        if( in_array( $this->tagName, static::$tag_no_body ) ) {
            // create short tag. 
            $html .= "<{$this->tagName}" . $this->_toAttribute() . ' />';
        }
        elseif( in_array( $this->tagName, static::$tag_span ) || count( $this->contents ) == 1 ) {
            // create inline tag. 
            $html .= "<{$this->tagName}" . $this->_toAttribute() . '>'
                . $this->_toContents_() . "</{$this->tagName}>\n";
        }
        else {
            // create tag. 
            $html .= "<{$this->tagName}" . $this->_toAttribute() . ">\n";
            $html .= $this->_toContents_() . "</{$this->tagName}>\n";
        }
        return $html;
    }
    // +----------------------------------------------------------------------+
}