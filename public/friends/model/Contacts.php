<?php
namespace friends\model;

class Contacts extends \WScore\DbAccess\Model
{
    /** @var string     name of database table     */
    protected $table = 'demoContact';

    /** @var string     name of primary key        */
    protected $id_name = 'contact_id';

    protected $definition = array(
        'contact_id'     => array( 'contact id',     'number', ),
        'contact_memo'   => array( 'what to do?', 'string', ),
        'contact_date'   => array( 'by when?',    'string', ),
        'contact_status' => array( 'done?',       'string', ),
        'created_at'  => array( 'created at',  'string', 'created_at'),
        'updated_at'  => array( 'updated at',  'string', 'updated_at'),
    );

    /** @var array      for validation of inputs       */
    protected $validators = array(
        'contact_id'     => array( 'number' ),
        'contact_memo'   => array( 'text', 'required' ),
        'contact_date'   => array( 'date', '', ),
        'contact_status' => array( 'text', '' ),
    );

    /** @var array      for selector construction      */
    protected $selectors  = array(
        'contact_id'     => array( 'Selector', 'text' ),
        'contact_memo'   => array( 'Selector', 'textarea', 'placeholder:your tasks here | class:span5 | rows:5' ),
        'contact_date'   => array( 'Selector', 'date', ),
        'contact_status' => array( 'Selector', 'checkToggle', '', array(
            'items' => array( array( 1, 'active' ), array( 9, 'done' ) )
        ) ),
    );

    public $recordClassName = 'friends\entity\contact';

}