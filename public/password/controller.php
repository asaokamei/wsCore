<?php
namespace password;

class controller
{
    /** @var \wsCore\Web\PageMC */
    protected $page = null;

    protected $input = array();

    /** @var \wsCore\Html\Form */
    protected $form;

    /** @var $dio \wsCore\Validator\DataIO */
    protected $dio;

    /**
     * @param \wsCore\Html\Form $form
     * @param \wsCore\Validator\DataIO $dio
     * @DimInjection fresh \wsCore\Html\Form
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
     * @param \wsCore\Html\PageView $view
     */
    public function makeForm( $view )
    {
        $item_count = array(
            array(  '5', ' 5 passwords' ),
            array( '10', '10 passwords' ),
            array( '15', '15 passwords' ),
        );
        if( !$this->input[ 'symbol' ] ) $this->input[ 'symbol' ] = false;

        $view->set( 'length', $this->form->input( 'text', 'length', $this->input[ 'length' ] ) );
        $view->set( 'symbol', $this->form->input( 'checkbox', 'symbol', 'checked' )->checked( $this->input[ 'symbol' ] ) );
        $view->set( 'count', $this->form->select( 'count', $item_count, array( $this->input['count']) ) );
    }

    /**
     * @param \wsCore\Html\PageView $view
     */

    public function act_index( $view )
    {
        $view->set( 'input', $this->input );
        $this->makeForm( $view );
    }
    /**
     * @param \wsCore\Html\PageView $view
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
        $view->set( 'passwords', $this->generate_password( $input ) );
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
            $passwords[] = \wsCore\Utilities\Tools::password( $input[ 'length' ], $input[ 'symbol' ] );
        }
        return $passwords;
    }
}