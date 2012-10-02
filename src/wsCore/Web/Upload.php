<?php

class UploadException extends Exception {}

class Upload
{
    const ERR_NO_UPLOAD = 'ERR901';
    const ERR_INVALID   = 'ERR902';
    const ERR_SIZE_ZERO = 'ERR903';
    const ERR_BAD_EXT   = 'ERR904';

    /** @var array        error messages */
    private $errorMessages = array();

    /** @var array        information about uploaded file */
    private $upload_info = array();

    /** @var bool         true if error happens */
    private $error      = FALSE;

    /** @var null         error code if error */
    private $error_code = NULL;

    private $throw_exception = TRUE;
    /**
     * @param null $userFile
     */
    public function __construct( $userFile=NULL )
    {
        $this->setErrorMessage();
        if( $userFile ) $this->uploadFile( $userFile );
    }

    /**
     * setup error messages.
     */
    public function setErrorMessage()
    {
        $this->errorMessages = array(
            UPLOAD_ERR_OK         =>  'ファイルをアップロードしました。 ',
            UPLOAD_ERR_INI_SIZE   =>  'ファイルサイズが上限を超えています。  ',
            UPLOAD_ERR_FORM_SIZE  =>  'ファイルサイズが上限値を超えています。  ',
            UPLOAD_ERR_PARTIAL    =>  'アップロードが完了しませんでした。  ',
            UPLOAD_ERR_NO_FILE    =>  'ファイルはアップロードされませんでした。  ',
            UPLOAD_ERR_NO_TMP_DIR =>  'テンポラリフォルダがありません。 ',
            UPLOAD_ERR_CANT_WRITE =>  'ディスクへの書き込みに失敗しました。 ',
            UPLOAD_ERR_EXTENSION  =>  'アップロードが拡張モジュールによって停止されました。  ',
            static::ERR_NO_UPLOAD =>  'ファイルがアップロードされていません。 ',
            static::ERR_INVALID   =>  'アップロードされたファイルではありません。 ',
            static::ERR_SIZE_ZERO =>  'ファイルサイズが空です（サイズ＝０）。 ',
            static::ERR_BAD_EXT   =>  'ファイルの拡張子が正しくありません。 ',
        );
    }

    /**
     * sets user file name for uploaded file.
     *
     * @param $userFile
     * @return bool
     */
    public function uploadFile( $userFile )
    {
        if( isset( $_FILES[ $userFile ] ) ) {
            $this->upload_info = $_FILES[ $userFile ];
        }
        else {
            $this->upload_info = array(
                'error' => static::ERR_NO_UPLOAD,
            );
            return $this->error;
        }
        if( $this->upload_info[ 'error' ] != UPLOAD_ERR_OK ) {
            $this->setError( $this->upload_info[ 'error' ] );
        }
        else
            if( !isset( $this->upload_info[ 'size' ] ) || $this->upload_info[ 'size' ] <= 0 ) {
                $this->setError( static::ERR_SIZE_ZERO );
            }
            else
                if( !is_uploaded_file( $this->upload_info[ 'tmp_name' ] ) ) {
                    $this->setError( static::ERR_INVALID );
                }
        return $this->error;
    }

    /**
     * @param $error_code
     * @throws UploadException
     * @return bool
     */
    public function setError( $error_code ) {
        $this->error_code = $error_code;
        if( $this->throw_exception ) {
            $message = " (error #$error_code)";
            if( isset( $this->errorMessages[ $error_code ] ) ) {
                $message = $this->errorMessages[ $error_code ] . $message;
            }
            throw new UploadException( $message );
        }
        $this->error      = TRUE;
        return $this->error;
    }

    /**
     * saves temporary file to the folder with original file name.
     *
     * @param $folder
     * @param bool $overwrite
     * @return bool
     */
    public function save( $folder, $overwrite=TRUE )
    {
        $orig_name = $this->upload_info[ 'name' ];
        return $this->saveAs( $folder . '/' . $orig_name, $overwrite );
    }

    /**
     * saves temporary file to a specific file name.
     *
     * @param $filename
     * @param bool $overwrite
     * @return bool
     */
    public function saveAs( $filename, $overwrite=TRUE )
    {
        if( $this->error ) return FALSE;
        $tmp_name = $this->upload_info[ 'tmp_name' ];
        $ok = move_uploaded_file( $tmp_name, $filename );
        return $ok;
    }

    /**
     * returns valid (bool) and err code
     *
     * @param $err_code
     * @return bool
     */
    public function isValid( &$err_code=NULL ) {
        $err_code = $this->error_code;
        if( isset( $this->errorMessages[ $err_code ] ) ) {
            $err_code = $this->errorMessages[ $err_code ] . " (error #$err_code)";
        }
        return !$this->error;
    }

    /**
     * checks file extension of the original file name.
     *
     * @param $extension
     * @return bool
     */
    public function verifyExt( $extension )
    {
        if( $this->error ) return FALSE;
        if( !is_array( $extension ) ) $extension = array( $extension );
        $error = TRUE;
        foreach( $extension as $ext ) {
            if( $ext == pathinfo( $this->upload_info[ 'name' ], PATHINFO_EXTENSION ) ) {
                $error = FALSE;
                break;
            }
        }
        if( $error ) {
            $this->setError( static::ERR_BAD_EXT );
        }
        return !$this->error;
    }
}