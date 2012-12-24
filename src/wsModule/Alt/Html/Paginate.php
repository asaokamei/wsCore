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
        $pageDiv = $this->tags->div( $ul = $this->tags->ul() )->_class( 'pagination' );
        if( $li = $this->getListBootstrap( 'top_page',  'top' ) ) $ul->contain_( $li );
        if( $li = $this->getListBootstrap( 'prev_page', '«'   ) ) $ul->contain_( $li );
        foreach( $this->url['pages'] as $page => $url ) {
            if( !$url ) {
                $ul->contain_( $this->tags->li( $this->tags->a( $page )->href( '#' ) )->_class( 'disabled' ) );
            } else {
                $ul->contain_( $this->tags->li( $this->tags->a( $page )->href( $url ) ) );
            }
        }
        if( $li = $this->getListBootstrap( 'next_page', '»'    ) ) $ul->contain_( $li );
        if( $li = $this->getListBootstrap( 'last_page', 'last' ) ) $ul->contain_( $li );
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
        return $this->tags->li( $this->tags->a( $label )->href( '#' ) )->_class( 'disabled' );
    }
    // +----------------------------------------------------------------------+
}