<?php
namespace wsModule\Alt\Html;

class Paginate
{
    /** @var array */
    private $url;

    /** @var \WScore\Html\Form */
    protected $tags;

    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\Html\Form                 $tags
     * @DimInjection Get \WScore\Html\Form
     */
    public function __construct( $tags )
    {
        $this->tags = $tags;
    }

    /**
     * @param array $url
     */
    public function setUrls( $url ) {
        $this->url = $url;
        $this->checkTopAndLast();
    }
    /**
     */
    public function checkTopAndLast()
    {
        return;
        /* // probably I need really complicated logic to do this...
        if( !$this->url[ 'top_page' ] && !$this->url[ 'last_page' ] ) {
            // none of top and last page are present. i.e. delete them.
            unset( $this->url[ 'top_page' ] );
            unset( $this->url[ 'last_page' ] );
        }
         */
    }
    // +----------------------------------------------------------------------+
    //  for Bootstrap pagination.
    // +----------------------------------------------------------------------+
    /**
     * @return \WScore\Html\Tags
     */
    function bootstrap()
    {
        $pageDiv = $this->tags->div( $ul = $this->tags->ul() )->class_( 'pagination' );
        if( $li = $this->getListBootstrap( 'top_page',  'top' ) ) $ul->_contain( $li );
        if( $li = $this->getListBootstrap( 'prev_page', '«'   ) ) $ul->_contain( $li );
        foreach( $this->url['pages'] as $page => $url ) {
            if( !$url ) {
                $ul->_contain( $this->tags->li( $this->tags->a( $page )->href( '#' ) )->class_( 'disabled' ) );
            } else {
                $ul->_contain( $this->tags->li( $this->tags->a( $page )->href( $url ) ) );
            }
        }
        if( $li = $this->getListBootstrap( 'next_page', '»'    ) ) $ul->_contain( $li );
        if( $li = $this->getListBootstrap( 'last_page', 'last' ) ) $ul->_contain( $li );
        return $pageDiv;
    }

    /**
     * @param $name
     * @param $label
     * @return \WScore\Html\Tags
     */
    function getListBootstrap( $name, $label )
    {
        if( !isset( $this->url[ $name ] ) ) return '';
        if( $this->url[ $name ] ) {
            return $this->tags->li( $this->tags->a( $label )->href( $this->url[ $name ] ) );
        }
        return $this->tags->li( $this->tags->a( $label )->href( '#' ) )->class_( 'disabled' );
    }
    // +----------------------------------------------------------------------+
}