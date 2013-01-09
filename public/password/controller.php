<?php
namespace password;

class controller
{
    /** @var \WScore\Web\PageMC */
    protected $page = null;

    protected $input = array();

    /** @var \WScore\Html\Form */
    protected $form;

    /** @var $dio \WScore\Validator\DataIO */
    protected $dio;

    /**
     * @param \WScore\Html\Form $form
     * @param \WScore\Validator\DataIO $dio
     * @DimInjection fresh \WScore\Html\Form
     * @DimInjection fresh DataIO
     */
    public function __construct( $form, $dio ) {
        $this->form = $form;
        $this->dio  = $dio;
    }

    public function pre_action( $page )
    {
        $this->page = $page;
        $this->input = array(
            'length' => '12',
            'symbol' => '',
            'count'  => '5',
        );
    }

    /**
     * @param \WScore\Html\PageView $view
     */
    public function makeForm( $view )
    {
        $item_count = array(
            array(  '5', ' 5 passwords' ),
            array( '10', '10 passwords' ),
            array( '15', '15 passwords' ),
        );
        if( !$this->input[ 'symbol' ] ) $this->input[ 'symbol' ] = false;

        $view->set( 'length',
            $this->form->input( 'range', 'length', $this->input[ 'length' ] )
                ->min(5)->max(24)->_class( 'span4' )
        );
        $view->set( 'symbol',
            $this->form->input( 'checkbox', 'symbol', 'checked' )
                ->checked( $this->input[ 'symbol' ] )
        );
        $view->set( 'count',
            $this->form->select( 'count', $item_count, array( $this->input['count'] ) )
                ->_class( 'span2' )
        );
    }

    /**
     * @param \WScore\Html\PageView $view
     */

    public function act_index( $view )
    {
        $view->set( 'input', $this->input );
        $this->makeForm( $view );
    }
    /**
     * @param \WScore\Html\PageView $view
     */
    public function act_generate( $view )
    {
        $this->dio->source( $_POST );
        $this->dio->push( 'length', 'number' );
        $this->dio->push( 'symbol', 'text' );
        $this->dio->push( 'count', 'number' );

        $input = $this->dio->popSafe();
        if( $input[ 'length' ] < 5 ) $input[ 'length' ] = $this->input[ 'length' ];
        $this->input = array_merge( $this->input, $input );
        $this->makeForm( $view );
        $passwords = $this->generate_password( $input );
        $view->set( 'passwords', $passwords );

        $md5 = array();
        foreach( $passwords as $pass ) {
            $md5[] = array(
                'crypt' => crypt( $pass, \WScore\Utilities\Tools::password(2) ),
                'md5'   => md5( $pass )
            );
        }
        $view->set( 'md5', $md5 );
    }

    /**
     * @param array $input
     * @return array
     */
    function generate_password( $input )
    {
        $count = ( $input[ 'count' ] ) ?: 5;
        $passwords = array();
        for( $i = 0; $i < $count; $i ++ ) {
            $passwords[] = \WScore\Utilities\Tools::password( $input[ 'length' ], $input[ 'symbol' ] );
        }
        return $passwords;
    }
}