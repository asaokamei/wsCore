<?php
use \WScore\Core;

require __DIR__ . "/../src/autoloader.php";

/*
 * pagination idea.
 */

#Idea #1

$query = Core::get( 'Query' );
/** @var $pager \wsModule\Alt\DbAccess\Paginate */
$pager = Core::get( '\wsModule\Alt\DbAccess\Paginate' );
$pager->setQuery( $query );

#Idea #2

class Pager extends \WScore\DbAccess\Query
{
    protected $pager;
    // +----------------------------------------------------------------------+
    //  Construction and Managing Dba Object.
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DbAccess\PdObject $pdoObj
     * @param \wsModule\Alt\DbAccess\Paginate  $pager
     * @DimInjection  Fresh   \WScore\DbAccess\PdObject
     * @DimInjection  Fresh   \wsModule\Alt\DbAccess\Paginate
     */
    public function __construct( $pdoObj=null, $pager=null ) {
        parent::__construct( $pdoObj );
        $this->pager = $pager;
    }

    /**
     * makes SQL statement. $types are:
     * INSERT, UPDATE, DELETE, COUNT, SELECT.
     * @param $type
     * @return \WScore\DbAccess\Query
     */
    public function makeSQL( $type ) {
        $this->pager->setTotal( $this->count() );
        $this->pager->setQuery( $this );
        return parent::makeSQL( $type );
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        // return $this->pager->postQuery( $this->fetchAll() );
        return parent::fetchAll();
    }
}
