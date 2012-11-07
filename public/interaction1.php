<?php
require_once( __DIR__ . '/../src/autoloader.php' );
use wsCore\Core;

class interact extends \wsCore\Web\Interaction
{

    /**
     * @param string $action
     * @param \dci\view $view
     * @return \dci\view
     */
    function insertData( $action, $view )
    {
        // get entity
        $entity = $this->restoreData( 'entity' );
        if( !$entity ) {
            $entity = $this->contextGet( 'entity' );
            $this->clearData();
            $this->registerData( 'entity', $entity );
        }
        elseif( $this->restoreData( 'complete' ) ) {
            goto done;
        }
        if( $this->actionFormAndLoad( $view, $entity, $action, 'form', 'load' ) ) return $view;

        // show confirm except for save.
        if( $action != 'save' ) {
            $view->setToken( $this->makeToken() );
            return $view->showConfirm( $entity );
        }
        // save entity.
        if( $action == 'save' && $this->verifyToken() ) {
            $role = $this->applyContext( $entity, 'active' );
            $role->insert();
        }
        // done
        done :
        return $view->showDone( $entity );
    }
    
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="./common/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="./common/css/bootstrap-responsive.css" />
    <link rel="stylesheet" type="text/css" href="./common/css/main.css" />
    <title>WScore Public Demo</title>
    <style type="text/css">
    </style>
</head>
<body>
<div class="container-narrow">
    <div class="masthead">
        <h3 class="muted"><a href="index.php" >WScore Public Demo</a></h3>
    </div>
    <hr>
    <h1>Interaction demo#1</h1>
    <p>Interaction with simple steps for inserting a data. The steps go through form -> confirm -> insert. </p>
    <form name="password" method="post" action="interaction1.php">
        <dl>
        </dl>
        <input type="submit" name="generate" class="btn btn-primary" value="interact data">
    </form>
    <footer class="footer">
        <hr>
        <p>WScore Developed by WorkSpot.JP<br />
            thanks, bootstrap. </p>
    </footer>
</div>
</body>
</html>