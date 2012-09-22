<?php

class AppException extends Exception {}

class ExampleController
{
    public $titles = array(
        'index' => array(
            'title' => '入力フォーム',
            'button' => '入力フォームの表示',
        ),
        'check' => array(
            'title' => '確認画面',
            'button' => '入力内容の確認',
        ),
        'post' => array(
            'title' => '登録完了画面',
            'button' => '入力内容を登録',
        ),
    );
    /**
     * @actionTitle   入力フォーム
     * @submitAction  入力フォームの表示
     * @param $view
     */
    public function act_index( $view ) {}

    /**
     * @actionTitle   確認画面
     * @submitAction  入力内容の確認
     * @param $view
     */
    public function act_check( $view ) {}

    /**
     * @actionTitle   登録完了画面
     * @submitAction  入力内容を登録
     * @param $view
     */
    public function act_save( $view ) {}
}

class PageMC
{
    const ERR_NONE       =     0;
    const ERR_NOTICE     =    10;
    const ERR_WARNING    =    50;
    const ERR_ERROR      =   100;
    const ERR_FATAL      =   200;

    const TOKEN_ID = '_token_id_by_PageMC';

    /** @var object             object for Pager.  */
    protected $object = NULL;

    /** @var string             name of action in $_REQUEST */
    protected $act_name = '_act';

    /** @var string             default method name is act_index  */
    protected $default  = 'index';

    /** @var PageView           view object...  */
    protected $view = NULL;

    /** @var int                current error level */
    protected $errLevel = 0;

    /** @var string             messages.  */
    protected $messages   = array();

    /** @var array              titles/button of each action */
    protected $titles = array();

    /** @var array              list of annotations used in PageMC */
    protected $annotation = array( 'submitAction', 'actionTitle' );
    // +-----------------------------------------------------------+
    /**
     * starts PageMC with object as Controller.
     *
     * @param object $object
     */
    public function __construct( $object )
    {
        $this->object = $object;
        if( !isset( $_SESSION[ static::TOKEN_ID ] ) ) $_SESSION[ static::TOKEN_ID ] = array();
        $this->reflect( $object );
    }

    /**
     * get method annotations for PageMC.
     *
     * @param $object
     */
    public function reflect( $object )
    {
        $refObject = new ReflectionClass( $object );
        $refMethods = $refObject->getMethods();
        foreach( $refMethods as $rMethod )
        {
            $name = $rMethod->getName();
            if( substr( $name, 0, 4 ) !== 'act_' ) continue;
            $name = substr( $name, 4 );
            $docs = $rMethod->getDocComment();
            if( !preg_match_all( "/(@.*)$/mU", $docs, $matches ) ) continue;
            foreach( $matches[1] as $comment )
            {
                if( !preg_match( '/@([-_a-zA-Z0-9]+)[ \t]+(.*)$/', $comment, $comMatch ) ) continue;
                if( in_array( $comMatch[1], $this->annotation ) ) {
                    $this->titles[ $name ][ $comMatch[1] ] = $comMatch[2];
                }
            }
        }
        var_dump( $this->titles );
    }

    // +-----------------------------------------------------------+
    /**
     * @param PageView $view
     * @throws AppException
     */
    public function run( $view )
    {
        $this->view = $view;
        $action = ( isset( $_REQUEST[ $this->act_name ] ) && !empty( $_REQUEST[ $this->act_name ] ) )
            ? $_REQUEST[ $this->act_name ]: 'index';
        $method = 'act_' . $action;
        try
        {
            $this->view->set( 'currAction', $action );
            if( !method_exists( $this->object, $method ) )
                throw new AppException( "invalid action: $action" );

            if( method_exists( $this->object, 'pre_action' ) ) {
                call_user_func( array( $this->object, 'pre_action' ), $this );
            }
            call_user_func( array( $this->object, $method ), $view );
        }
        catch( AppException $e ) {
            $this->error( $e->getMessage() );
        }
    }
    public function nextAct( $act ) {
        $this->view->set( $this->act_name, $act );
    }
    // +-----------------------------------------------------------+
    // token for Cross Site Resource Forage
    // +-----------------------------------------------------------+
    /**
     * push token into session data. max 20.
     */
    public function pushToken()
    {
        $token = md5( rand() ); // need better token!
        if( !isset( $_SESSION[ static::TOKEN_ID ][ 'token' ] ) ) {
            $_SESSION[ static::TOKEN_ID ][ 'token' ] = array();
        }
        $token_data  = $_SESSION[ static::TOKEN_ID ][ 'token' ];
        array_push( $token_data, $token );
        if( count( $token_data ) > 20 ) {
            array_shift( $token_data );
        }
        $this->view->set( static::TOKEN_ID, $token );
    }

    /**
     * checks token from post against session data.
     *
     * @return bool
     */
    public function verifyToken()
    {
        $token_post = $_POST[ static::TOKEN_ID ];
        $token_data  = $_SESSION[ static::TOKEN_ID ][ 'token' ];
        if( $token_post && in_array( $token_post, $token_data ) ) {
            $key = array_search( $token_post, $token_data );
            unset( $token_data[ $key ] );
            return TRUE;
        }
        $this->error( 'access not allowed with current token.' );
        return FALSE;
    }

    // +-----------------------------------------------------------+
    // messages and errors
    // +-----------------------------------------------------------+
    /**
     * @param $message
     */
    public function message( $message ) {
        $this->messages[] = $message;
    }

    /**
     * @param string $message
     */
    public function error( $message ) {
        $this->messages[] = $message;
        $this->errLevel = static::ERR_ERROR;
    }

    public function isError( &$message=array() ) {
        $message = $this->messages;
        return $this->errLevel >= static::ERR_ERROR;
    }
    // +-----------------------------------------------------------+
}

$obj = new ExampleController();
$page = new PageMC( $obj );