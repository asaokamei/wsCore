<?php
namespace wsModule\Alt\Web;

class Response
{
    protected $content;
    protected $content_type = 'text/html';
    protected $status_code = 200;
    protected $http_headers = array();
    /** @var string ResponseHelper */
    protected $helper = '\wsModule\Alt\Web\ResponseHelper';

    public function send()
    {
        /** @var $helper ResponseHelper */
        $helper = $this->helper;
        $helper::emitStatus( $this->status_code );

        $mime = $helper::findMimeType( $this->content_type );
        header( 'Content-type: ' . $mime );

        foreach( $this->http_headers as $name => $value ) {
            header( $name . ': ' . $value );
        }
        echo $this->content;
    }

    public function setContent( $content ) {
        $this->content = $content;
    }

    public function setContentType( $type ) {
        $this->content_type = $type;
    }

    public function setStatusCode( $status_code ) {
        $this->status_code = $status_code;
    }

    public function setHttpHeader( $name, $value ) {
        $this->http_headers[ $name ] = $value;
    }
}

class ResponseHelper
{
    public static $protocol = 'HTTP/1.1';
    /**
     * from https://github.com/fuel/core/blob/1.1/develop/classes/response.php
     * @var  array  An array of status codes and messages
     */
    public static $statuses = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Un-processable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded'
    );

    // +-------------------------------------------------------------+
    static function getStatusText( $code )
    {
        $statusText = "";
        if( isset( self::$statuses[ $code ] ) ) {
            $statusText = self::$statuses[ $code ];
        }
        return $statusText;
    }

    // +-------------------------------------------------------------+
    static function emitStatus( $status )
    {
        if( !empty( $_SERVER[ 'FCGI_SERVER_VERSION' ] ) ) {
            $header = 'Status: ' . $status . ' ' . self::getStatusText( $status );
        } else {
            $header = self::$protocol . ' ' . $status . ' ' . self::getStatusText( $status );
        }
        header( $header );
    }

    // +-------------------------------------------------------------+
    /**
     * find mime type from content_type/file extensions.
     * @static
     * @param $content_type
     * @return string
     */
    static function findMimeType( $content_type )
    {
        switch( strtolower( $content_type ) ) {
            case 'text':
            case 'txt':
                $mime = 'text/plain';
                break;
            case 'css':
                $mime = 'text/css';
                break;
            case 'js':
            case 'javascript':
                $mime = 'text/javascript';
                break;
            case 'jpg':
            case 'jpeg':
                $mime = 'image/jpeg';
                break;
            case 'gif':
                $mime = 'image/gif';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            case 'pdf':
                $mime = 'application/pdf';
                break;
            case 'ico':
                $mime = 'image/x-icon';
                break;
            default:
                $mime = 'text/html';
                break;
        }
        return $mime;
    }
    // +-------------------------------------------------------------+
}