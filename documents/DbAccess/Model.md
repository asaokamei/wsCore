Using Model
===========

Model class (aka Dao, Database Access Object) packs configurations and settings
for accessing database for a table, such as table name, primary key, etc.

This class, also contains configurations for
 - validation rules,
 - html form selectors,
for each properties.

Model for Table
---------------

Extend dao for each table to set up information about its table. 

    MyTable extends \WScore\DbAccess\Model
    {
        public function __construct( $dao=NULL ) {
            parent::__construct( $dao );
            $this->table = 'myTable';
            $this->id_name = 'data_id';
        }
    }
    $dao = Core::get( '\path\to\MyTable' );

Core module will automatically inject $dba object into $dao. 

###Model and DataRecords

the DataRecord object is used when retrieving data from dao. 

    $dao  = Core::get( '\path\to\MyTable' );
    $data = $dao->find( 10 ); // DataRecord object. 
    $data->get( 'name' );     // will get name data
    $data[ 'name' ];          // DataRecord implements ArrayAccess

Setting Model
-----------

###Set Up Property

set up its properties

    MyTable extends \WScore\DbAccess\Model
    {
        public function __construct() {
            parent::__construct();
            $this->table = 'myTable';
            $this->id_name = 'data_id';
            
            $this->properties = array(
                'data_id'  => 'Data ID',
                'name'     => 'User Name',
                'age'      => 'Age',
                'bdate'    => 'Birthday',
            );
        }
    }
    $dao = Core::get( '\path\to\MyTable' );
    echo $dao->popName( 'bdate' ); // shows 'Birthday'

###Set Up Html Selectors

set up selectors for each property. 
This will create HTML selector very easily. 

    MyTable extends \WScore\DbAccess\Model
    {
        public function __construct() {
            parent::__construct();
            $this->table = 'myTable';
            $this->id_name = 'data_id';
            
            $this->selectors = array(
                'name'     => [ 'selText', 30, 0, 'ON' ], // old style
                'age'      => [ 'int',   'class:selText | length:5 | max:3 | ime:OFF' ], // new style
                'bdate'    => [ 'date',  'class:selDate | starts:1942 | style:drop | default:1980-01-01' ],
            );
        }
    }
    $dao = Core::get( '\path\to\MyTable' );
    echo $dao->popHtml( 'Edit', 'bdate', $value, $error ); // shows selector for editing bdate. 
    // use DataRecord
    $data = $dao->find( 10 );
    $data->setHtmlType( 'Edit' );
    $data->popHtml( 'bdate' );                        // also shows selector for editing bdate. 

###Set Up Validators

set up validation rules for each property. 
it is also a nice idea to create a method for validation set ups. 

Please refer to Validator and DataIO class in \WScore\Validator\ folder for validation rules.

    MyTable extends \WScore\DbAccess\Model
    {
        public function __construct() {
            parent::__construct();
            $this->table = 'myTable';
            $this->id_name = 'data_id';
            
            $this->validators = array(
                'name'     => [ 'text' ],
                'age'      => [ 'int', 'min:0 | max:100' ],
                'bdate'    => [ 'date' ],
            );
        }
    }
    $dao = Core::get( '\path\to\MyTable' );
    $dio = Core::get( '\WScore\Validator\DataIO' ); // use DataIO for validation.
    echo $dao->validate( $dio, 'name' );
    echo $dao->validate( $dio, 'age'  );
    echo $dao->validate( $dio, 'bdate' );

###Set Up Restrictions

set up restriction to specify which properties can be saved to database. 

    MyTable extends \WScore\DbAccess\Model
    {
        public function __construct() {
            parent::__construct();
            $this->table = 'myTable';
            $this->id_name = 'data_id';
            
            $this->restrictions = array( 'name', 'bdate', );
        }
    }
    $dao = Core::get( '\path\to\MyTable' );
    $dao->insert( [ 'name' => 'Mike', 'bdate' => '1980-01-01', 'age'=>10 ] );
    // age is not be stored in the database.
    // maybe it is calculated from bdate in db...

the restricted properties maybe different from admin to end-users. 
