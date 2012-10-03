<?php
namespace wsCore\Web;

class PageMcException extends \Exception {}

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

    /** @var \wsCore\Html\PageView           view object...  */
    protected $view = NULL;

    /** @var int                current error level */
    protected $errLevel = 0;

    /** @var string             messages.  */
    protected $messages   = array();

    /** @var array              titles/button of each action */
    protected $titles = array();

    /** @var Session */
    protected $session;
    // +-----------------------------------------------------------+
    /**
     * starts PageMC with object as Controller.
     *
     * @param object $object
     * @param Session $session
     */
    public function __construct( $object, $session=null )
    {
        $this->object = $object;
        $this->session = $session;
        if( !isset( $_SESSION[ static::TOKEN_ID ] ) ) $_SESSION[ static::TOKEN_ID ] = array();
    }

    // +-----------------------------------------------------------+
    /**
     * @param \wsCore\Html\PageView|array $view
     * @throws PageMcException
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
                throw new PageMcException( "invalid action: $action" );

            if( method_exists( $this->object, 'pre_action' ) ) {
                call_user_func( array( $this->object, 'pre_action' ), $this );
            }
            call_user_func( array( $this->object, $method ), $view );
            $this->setTitles( $action, '_curr' );
        }
        catch( PageMcException $e ) {
            $this->error( $e->getMessage() );
        }
    }
    // +-----------------------------------------------------------+
    //  managing actions
    // +-----------------------------------------------------------+
    public function nextAct( $act )
    {
        $this->view[ $this->act_name ] = $act;
        $this->setTitles( $act, $this->act_name );
    }

    public function setTitles( $act, $prefix )
    {
        if( !$act ) return;
        if( isset( $this->object->titles ) && is_array( $this->object->titles ) ) {
            if( array_key_exists( $act, $this->object->titles ) ) {
                $this->view[ $prefix . '_title' ] = $this->object->titles[ $act ][ 'title' ];
                $this->view[ $prefix . '_button' ] = $this->object->titles[ $act ][ 'button' ];
            }
        }
    }
    // +-----------------------------------------------------------+
    // token for Cross Site Resource Forage
    // +-----------------------------------------------------------+
    /**
     * push token into session data. max 20.
     */
    public function pushToken()
    {
        $this->session->pushToken();
    }

    /**
     * checks token from post against session data.
     *
     * @return bool
     */
    public function verifyToken()
    {
        return $this->session->verifyToken();
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

    function displayMessage( $opt=array() )
    {
        if( empty( $this->messages ) ) return '';
        $message = implode( '<br />', $this->messages );

        if( $this->errLevel >= static::ERR_ERROR ) {
            $tbl_color = '#CC3300';
            $tbl_msg   = 'エラーがありました';
        }
        else {
            $tbl_color = '#6699CC';
            $tbl_msg   = 'メッセージ';
        }
        if( is_array( $opt ) ) extract( $opt );
        if( !isset( $width ) ) $width = '100%';
        ?>
    <br>
    <table class="err_box" width="<?php echo $width; ?>"  border="0" align="center" cellpadding="2" cellspacing="2" bgcolor="<?php echo $tbl_color;?>">
        <tr>
            <th><?php echo "<span style='color=white;' ><strong>{$tbl_msg}</strong></span>\n"; ?></th>
        </tr>
        <tr>
            <td bgcolor="#FFFFFF"><?php echo $message;?></td>
        </tr>
    </table>
    <br>
    <?php
    }    // +-----------------------------------------------------------+
}

/**
$obj = new ExampleController();
$page = new PageMC( $obj );
 **/