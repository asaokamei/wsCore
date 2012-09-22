<?php

class Csv
{
    /** @var string          PHP's internal encoding  */
    public static $toEncoding = 'UTF-8';

    /** @var null|string     source CSV file's encoding. assume Japanese windows */
    protected $fromEncoding = 'SJIS-win';

    /** @var null            file name of CSV.  */
    protected $filename = NULL;

    /** @var null            file pointer from fopen.  */
    protected $filePointer = NULL;

    /** @var array           header data.  */
    protected $header = array();

    /** @var bool            associate with header column if true.  */
    protected $useHeader = FALSE;

    /**
     * set
     * @param null $filename
     * @param string $from
     */
    public function __construct( $filename=NULL, $from=NULL )
    {
        $this->filename = $filename;
        if( isset( $from ) ) $this->fromEncoding = $from;
    }

    /**
     * opens file. specify encoding of the CSV file.
     *
     * @param string|null $filename
     * @param string|null $from
     * @throws RuntimeException
     * @return resource
     */
    public function open( $filename=NULL, $from=NULL )
    {
        $filename = ( $filename ) ?: $this->filename;
        $from     = ( $from )     ?: $this->fromEncoding;
        if( !$filename ) throw new RuntimeException( "CSV file does not exist: ".$filename );
        if( $from == static::$toEncoding ) {
            $fp = fopen( $filename, 'r+' );
        }
        else {
            $fp = $this->fOpenAsUtf8( $filename, $from );
        }
        $this->filePointer = $fp;
        return $fp;
    }

    /**
     * gets CSV data from file in UTF-8.
     *
     * @param int $size
     * @throws RuntimeException
     * @return array
     */
    public function getCsv( $size=10280 )
    {
        if( !$this->filePointer ) throw new RuntimeException( "Invalid CSV pointer: " );
        $data = fgetcsv( $this->filePointer, $size );
        if( $this->useHeader ) {
            $result = array();
            foreach( $data as $col => $val ) {
                $result[ $this->header[$col] ] = $val;
            }
            $data = $result;
        }
        return $data;
    }

    /**
     * gets CSV data as header.
     *
     * @param int $size
     * @param bool $useHeader
     * @return array
     */
    public function getHeader( $size=10280, $useHeader=FALSE )
    {
        $header = $this->getCsv( $size );
        $this->useHeader = $useHeader;
        if( $this->useHeader ) {
            $this->header = $header;
        }
        return $header;
    }

    /**
     * open file as utf-8 encoding.
     *
     * @param $file_name
     * @param string $from
     * @return resource
     */
    public static function fOpenAsUtf8( $file_name, $from='SJIS-win' )
    {
        $data = file_get_contents( $file_name );
        $data = mb_convert_encoding( $data, static::$toEncoding, $from );
        $fp   = fopen( 'php://memory', 'r+' );
        fwrite( $fp, $data );
        rewind( $fp ); //
        return $fp;
    }

}