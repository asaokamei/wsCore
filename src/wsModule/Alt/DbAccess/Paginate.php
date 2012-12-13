<?php
namespace wsModule\Alt\DbAccess;

/**
 * @property $limit
 * @property $offset
 * @property $total
 * @property $per_page
 * @property $num_links
 * @property $page_url
 * @property $curr_page
 * @property $top_page
 * @property $last_page
 * @property $next_page
 * @property $prev_page
 * @property $pages
 * @property $options
 */
class Paginate implements \ArrayAccess
{
    protected $data = array(
        'limit'     => null,
        'offset'    => 0,
        'total'     => 0,
        'per_page'  => 10,
        'num_links' => 5,
        'page_url'  => null,
        'curr_page' => 1,
        'top_page'  => null,
        'last_page' => null,
        'next_page' => null,
        'prev_page' => null,
        'pages'     => array(),
        'options'   => array(),
    );
    protected $url = array(
        'pages' => array(),
    );
    public $page_parameter = 'page';

    // +----------------------------------------------------------------------+
    //  constructor and setup pagination information.
    // +----------------------------------------------------------------------+
    /**
     *
     */
    public function __construct() {
    }

    /**
     * @param int $total
     * @return Paginate
     */
    public function setTotal( $total ) {
        $this->total = $total;
        return $this;
    }

    /**
     * @param int $page
     * @return Paginate
     */
    public function setCurrPage( $page ) {
        $this->curr_page = $page;
        return $this;
    }

    /**
     * @param $option
     * @return Paginate
     */
    public function setOptions( $option )
    {
        if( !$this->curr_page && isset( $option[ $this->page_parameter ] ) ) {
            $this->curr_page = $option[ $this->page_parameter ];
            unset( $option[ $this->page_parameter ] );
        }
        $this->options = $option;
        return $this;
    }
    // +----------------------------------------------------------------------+
    // +----------------------------------------------------------------------+
    /**
     * @return Paginate
     */
    public function setupPageOption() {
        $this->options[ $this->page_parameter ] = $this->curr_page;
        return $this;
    }
    /**
     * @param null|string $url
     * @return array
     */
    public function setupUrls( $url=null )
    {
        if( $url ) $this->page_url = $url;
        $properties = array( 'top_page', 'last_page', 'next_page', 'prev_page' );
        foreach( $properties as $prop ) {
            $this->url[ $prop ] = $this->makeUrl( $this->$prop );
        }
        foreach( $this->pages as $key => $page ) {
            $this->url[ 'pages' ][ $key ] = $this->makeUrl( $page );
        }
        return $this->url;
    }

    /**
     * @param string $name
     * @return string
     */
    public function url( $name ) {
        if( is_numeric( $name ) ) return $this->url[ 'pages' ][ $name ];
        return $this->url[ $name ];
    }

    /**
     * @param $page
     * @return string
     */
    public function makeUrl( $page )
    {
        if( !$page ) return '';
        $url = sprintf( $this->page_url, $page );
        if( !empty( $this->options ) ) {
            $url .= '?';
            foreach( $this->options as $key => $val ) {
                $key = htmlentities( $key, ENT_QUOTES );
                $val = urlencode( $val );
                $url .= strpos( $url, -1 ) == '?' ? '' : '&';
                $url .= "{$key}={$val}";
            }
        }
        return $url;
    }
    // +----------------------------------------------------------------------+
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\Query $query
     * @return \WScore\DbAccess\Query
     */
    public function setQuery( $query )
    {
        $this->calc();
        $query->offset( $this->getOffset() );
        $query->limit(  $this->getLimit() );
        return $query;
    }
    /**
     * @return int
     */
    public function getOffset() {
        return (integer) ( ( $this->curr_page - 1 ) * $this->per_page );
    }

    /**
     * @return int
     */
    public function getLimit() {
        return $this->per_page;
    }

    /**
     *
     */
    public function calc()
    {
        // check the current page.
        if( !$this->curr_page ) $this->curr_page = 1;
        // setting top page.
        $this->top_page = 1;
        $start = $this->curr_page - $this->num_links;
        if( $start <= 1 ) {
            $start = 1;
            $this->top_page = null;
        }
        // setting last page.
        if( !$this->total ) { // in case total is not set...
            $this->last_page = null;
            $last = $this->num_links + $this->curr_page; // show pages up to the num_links.
        }
        else {
            $this->last_page = (integer) ( ceil( $this->total / $this->per_page ) );
            $last = $this->curr_page + $this->num_links;
            if( $this->last_page <= $last ) {
                $last = $this->last_page;
                $this->last_page = null;
            }
        }
        // create pages
        for( $page = $start; $page <= $last; $page ++ ) {
            if( $page == $this->curr_page ) {
                $this->data[ 'pages' ][ $page ] = '';
            }
            else {
                $this->data[ 'pages' ][ $page ] = $page;
            }
        }
        if( $this->curr_page > 1 ) $this->prev_page = $this->curr_page - 1;
        if( $this->curr_page != $last ) $this->next_page = $this->curr_page + 1;
    }
    // +----------------------------------------------------------------------+
    //  magic methods and ArrayAccess methods.
    // +----------------------------------------------------------------------+
    /**
     * @param $offset
     * @return mixed
     */
    public function __get( $offset ) {
        return $this->offsetGet( $offset );
    }

    /**
     * @param $offset
     * @param $value
     */
    public function __set( $offset, $value ) {
        $this->offsetSet( $offset, $value );
    }
    /**
     * @param mixed $offset  An offset to check for.
     * @return boolean true on success or false on failure.
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists( $offset ) {
        return isset( $this->data[ $offset ] );
    }

    /**
     * @param mixed $offset  The offset to retrieve.
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset ) {
        return isset( $this->data[ $offset ] ) ? $this->data[ $offset ] : null;
    }

    /**
     * @param mixed $offset  The offset to assign the value to.
     * @param mixed $value   The value to set.
     * @return void
     */
    public function offsetSet( $offset, $value ) {
        $this->data[ $offset ] = $value;
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset( $offset ) {
        if ( isset( $this->data[ $offset ] ) ) unset( $this->data[ $offset ] );
    }
}
/*

example of generating pagination ul > li using Tags.
well, not tested yet.

$pages = new Paginate(); // and som settings.
$pages->setTotal( 123 );
$pages->setCurrPage( 5 );
$pages->calc();
$pages->setupUrls( '/path/to/%d' ); // %d replaces the page number.
$tags  = new \WScore\Html\Tags();

$ul = $tags->ul();
if( $pages->prev_page ) $ul->contain_( $tags->li( $tags->a( 'Prev' )->href( $pages->url( 'prev_page' ) ) ) );
if( $pages->top_page ) $ul->contain_( $tags->li( $tags->a( $pages->top_page ) )->href( $pages->url( 'top_page')) );
foreach( $pages->pages as $page => $value ) {
    if( $value ) {
        $ul->contain_( $tags->li( $tags->a( $page )->href( $pages->url( $page ) ) )->_class( 'active' ) );
    }
    else {
        $ul->contain_( $tags->li( $page )->_class( 'disabled' ) );
    }
}
if( $pages->last_page ) $ul->contain_( $tags->li( $tags->a( $pages->last_page )->href( $pages->url( 'last_page' ) ) ) );
if( $pages->next_page ) $ul->contain_( $tags->li( $tags->a( 'Next' )->href( $pages->url( 'next_page' ) ) ) );
echo $paginate = $tags->div( $ul )->_class( 'pagination' );


 */
