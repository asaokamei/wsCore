Using Dao
=========

Dao, Database Access Object, is a famous design pattern to access database. 
This class packs all the information necessary to access tables in a database, 
including table name, primary key and its type, etc. 

#Creating Dao for Each Table

Extend dao for each table to set up information about its table. 

    MyTable extends \wsCore\DbAccess\Dao
    {
        public function __construct() {
            parent::__construct();
            $this->table = 'myTable';
            $this->id_name = 'data_id';
        }
    }
    $dao = Core::get( '\path\to\MyTable' );
    $data = $dao->dba()->findById( 10 );

Core module will automatically inject $dba object into $dao. 

##Set Up Property

set up its properties

    MyTable extends \wsCore\DbAccess\Dao
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
    echo $dao->propertyName( 'bdate' ); // shows 'Birthday'

##Set Up Selectors

set up selectors for each property. 
This will create HTML selector very easily. 

    MyTable extends \wsCore\DbAccess\Dao
    {
        public function __construct() {
            parent::__construct();
            $this->table = 'myTable';
            $this->id_name = 'data_id';
            
            $this->selectors = array(
                'name'     => [ 'selText', 30, 0, 'ON' ], // old style
                'age'      => [ 'class:selText | length:5 | max:3 | ime:OFF' ], // new style
                'bdate'    => [ 'class:selDate | starts:1942 | style:drop | default:1980-01-01' ],
            );
        }
    }
    $dao = Core::get( '\path\to\MyTable' );
    echo $dao->popHtml( 'Edit', 'bdate', $value, $error ); // shows selector for editing bdate. 

##Set Up Validators

set up validation rules for each property. 
it is also a nice idea to create a method for validation set ups. 

Please refer to Validator and DataIO class in \wsCore\Validator\ folder for validation rules. 

    MyTable extends \wsCore\DbAccess\Dao
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
    $dio = Core::get( '\wsCore\Validator\DataIO' ); // use DataIO for validation. 
    echo $dao->validate( $dio, 'name' );
    echo $dao->validate( $dio, 'age'  );
    echo $dao->validate( $dio, 'bdate' );

##Set Up Restrictions

set up restriction to specify which properties can be saved to database. 

    MyTable extends \wsCore\DbAccess\Dao
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
