<?php
namespace wsModule\Alt\DbAccess;

/**
 *	Pagination class for SQL.
 *
 *	@copyright     Copyright 2010-2011, Asao Kamei
 *	@link          http://www.workspot.jp/cena/
 *	@license       GPLv2
 */
if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...

/* ============================================================================ */
function pn_disp_all( &$pn, $url=NULL )
{
    if( WORDY ) echo "<br>pn_disp_all( $pn, $url )<br>\n";
    if( WORDY > 3 ) wordy_table( $pn, 'pn' );
    if( !$url ) {
        $url = htmlspecialchars( $_SERVER['PHP_SELF'] );
    }
    echo pn_disp_link( $pn, $url, 'top',  '<<' );
    echo ' ';
    echo pn_disp_link( $pn, $url, 'prev', 'prev' );
    echo ' ';
    pn_disp_pages( $pn, $url );
    echo pn_disp_link( $pn, $url, 'next', 'next' );
    echo ' ';
    echo pn_disp_link( $pn, $url, 'last', '>>' );
}

/* ============================================================================ */
function pn_disp_pages( &$pn, $url, $word=NULL )
{
    if( !empty( $pn['page'] ) ) {
        foreach( $pn['page'] as $key=>$arg ) {
            if( empty( $arg ) ) {
                $link = "<strong>{$key}</strong>&nbsp;";
            }
            else {
                $link = "<a href='{$url}?{$arg}'>{$key}</a>&nbsp;";
            }
            echo $link;
        }
    }
}

/* ============================================================================ */
function pn_disp_link( &$pn, $url, $type, $word=NULL, $disp=TRUE )
{
    if( WORDY > 4 ) echo " --disp_pv_link( &$pn, $url, $type, $word )-- \n";
    if( !$word ) $word = ucfirst( strtolower( $type ) );

    $args = $pn[ $type ];
    if( empty( $args ) ) {
        if( $disp ) {
            $link = "{$word}";
        }
        else {
            $link = NULL;
        }
    }
    else {
        $link = "<a href='{$url}?{$args}'>{$word}</a>";
    }
    return $link;
}

/* ============================================================================ */
class Dba_Page
{
    const  MAX_LIST = 20;
    static $default_limit   = 10; // rows per page. overwrite Dba_Page::$limit=5. 

    var $limit    = NULL;
    var $sql      = NULL; // sql module 
    var $start      =  0; // offset count
    var $count      =  0; // total number of data -> should fetch everytime.

    var $options    = ''; // other form inputs to construct where condition.
    var $max_page   =  0; // maximum number of page used inside this class

    // +--------------------------------------------------------------- +
    function __construct( $sql, $opt=array() )
    {
        if( WORDY ) echo "<b>created instance of paginate</b>...<br>\n";
        $this->sql = $sql;
        if( have_value( $opt, 'options' ) ) $this->setOptions( $opt[ 'options' ] );
        $this->getParameters( $opt );
    }
    // +--------------------------------------------------------------- +
    function setPage()
    {
        $this->sql
            ->limit( $this->limit )
            ->offset( $this->start );
        return $this;
    }
    // +--------------------------------------------------------------- +
    function fetchPage( &$data )
    {
        $this->setPage();
        $this->sql
            ->execSelect()
            ->fetchAll( $data );
        return $this;
    }
    // +--------------------------------------------------------------- +
    function fetchPN( &$pn ) {
        $pn = $this->_getPrevNext();
        return $this;
    }
    // +--------------------------------------------------------------- +
    function setOptions( $options ) {
        // to set options used to construct sql where condition.
        if( WORDY ) echo "<br>setOptions( $options )<br>\n";
        if( WORDY > 3 ) print_r( $options );
        $this->options = $options;
        return $this;
    }
    // +--------------------------------------------------------------- +
    function getParameters( $data=array() )
    {
        if( is_array( $data ) ) $data = $data + $_REQUEST;
        $this->start = have_value( $data, 'start' ) ? $data[ 'start' ] : 0;
        $this->limit = have_value( $data, 'limit' ) ? $data[ 'limit' ] : self::$default_limit;
        // no count supported. count everytime.
        //$this->count = is_numeric( $data[ 'count' ] ) ? $data[ 'count' ] : $this->count;
        $this->_setMaxPage();
        if( WORDY ) {
            echo "got parameters: " .
                "start={$this->start}, limit={$this->limit}, count={$this->count}, " .
                "max={$this->max_page}<br> ";
        }
        return $this;
    }
    // +--------------------------------------------------------------- +
    function _setMaxPage()
    {
        if( $this->count == 0 ) {
            $this->_setCount();
        }
        if( $this->count == 0 || $this->limit == 0 ) {
            $this->max_page = 0;
        }
        else {
            $this->max_page = ceil( $this->count/$this->limit );
        }
        if( WORDY ) echo "_setMaxPage = {$this->max_page}<br>\n";
        return $this;
    }
    // +--------------------------------------------------------------- +
    function _setCount()
    {
        $this->sql->fetchCount( $this->count );
        if( WORDY ) echo "_setCount = {$this->count}<br>\n";
        return $this;
    }
    // +--------------------------------------------------------------- +
    function _getPrevNext()
    {
        // total found
        $pg_info['start']  = $this->start;
        $pg_info['limit']  = $this->limit;
        $pg_info['count']  = $this->count;

        // make NEXT button
        $start = $this->start + $this->limit;
        $pg_info[ "next" ] = $this->_getURL( $start );

        // make PREV button
        $start = $this->start - $this->limit;
        $pg_info[ "prev" ] = $this->_getURL( $start );

        // make TOP button
        if( $this->max_page > 1 ) {
            $pg_info[ "top" ] = $this->_getURL( 0 );
        }
        else
            $pg_info[ 'top' ] = '';

        // make LAST button
        if( $this->start != ( $this->max_page - 1 ) * $this->limit ) {
            $pg_info[ "last" ] = $this->_getURL( ( $this->max_page - 1 ) * $this->limit );
        }
        else
            $pg_info[ 'last' ] = '';

        // make PAGEs 
        if( $this->max_page < self::MAX_LIST * 2 ) {
            $pg_add = $this->getPrevNext1();
        }
        else {
            $pg_add =$this->getPrevNext2();
        }
        $pg_info = array_merge( $pg_info, $pg_add );

        return $pg_info;
    }
    // +--------------------------------------------------------------- +
    function getPrevNext2()
    {
        if( WORDY ) echo "<b>getPrevNext2()</b>...<br>\n";
        $pg_info    = array();
        $repeat     = self::MAX_LIST * 2;
        $curr_page  = floor( $this->start / $this->limit ) + 1; // page starts from 1...
        $max_offset = $this->max_page - $repeat + 1;

        // getting the first page
        if( $curr_page > self::MAX_LIST ) {
            $page_start = $curr_page - self::MAX_LIST;
        }
        else {
            $page_start = 1;
        }
        if( $page_start > $max_offset ) $page_start = $max_offset;

        // make PAGE numbers
        for( $i = 0; $i < $repeat; $i++ ) {
            $page  = $page_start + $i;
            $start = $i * $this->limit;
            $pg_info['page'][ $page ] = $this->_getURL( $start );
        }


        if( WORDY > 4 ) wordy_table( $pg_info, 'getPrevNext2' );
        return $pg_info;
    }
    // +--------------------------------------------------------------- +
    function getPrevNext1()
    {
        if( WORDY ) echo "<b>getPrevNext1()</b>...<br>\n";
        $pg_info = array();

        // make PAGE numbers
        for( $i = 0; $i < $this->max_page; $i++ ) {
            $page_id = $i + 1;
            $start   = $i * $this->limit;
            $pg_info['page'][ $page_id ] = $this->_getURL( $start );
        }
        if( WORDY > 4 ) wordy_table( $pg_info, 'getPrevNext1'. is_array( $pg_info ) );
        return $pg_info;
    }
    // +--------------------------------------------------------------- +
    function _getURL( $start )
    {
        if( WORDY > 4 ) echo "_makeURL( $start )...<br>\n";
        $url   = '';
        if( $start > $this->count ) {
            // no url
        }
        else
            if( $start < 0 ) {
                // what's wrong ? no url
            }
            else
                if( $start == $this->start ) {
                    // current page. no url
                }
                else {
                    $url = "start={$start}&limit={$this->limit}";
                    // $url .= "&count={$this->count}"; // no adding count.
                    if( $this->options ) $url .= "&{$this->options}";
                }
        return $url;
    }
    // +--------------------------------------------------------------- +
}

