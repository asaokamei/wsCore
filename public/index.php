<?php



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

            /* Main marketing message and sign up button */
        .jumbotron {
            margin: 60px 0;
            text-align: center;
        }
        .jumbotron h1 {
            font-size: 60px;
            line-height: 1;
        }
        .jumbotron .btn {
            font-size: 21px;
            padding: 14px 24px;
        }
    </style>
</head>
<body>
<div class="container-narrow">
    <div class="masthead">
        <h3 class="muted">WScore Public Demo</h3>
    </div>
    <hr>
    <header class="jumbotron">
        <h1>WScore framework</h1>
        <p class="lead">Data Mapper and DCI inspired web application framework.<br />
            Wishes wishes wishes. </p>
        <hr>
    </header>
    <div class="row-fluid marketing">
        <div class="span6">
            <h4>building forms</h4>
            <p>demo for building form elements and using page based controller. </p>
            <ul>
                <li><a href="password.php" >generate password</a></li>
            </ul>
            <h4>task/todo application</h4>
            <p>simple demo application using data mapper and simple mvc. </p>
            <ul><li><a href="myTasks" >task/todo</a></li></ul>
            <p>make sure current <code>./task/data</code> folder is writable for web server to store sqlite file. </p>
            <h4>interaction with forms</h4>
            <p>data mapper demo for using models and entities, then interaction. </p>
            <ul>
                <li><a href="interaction1.php" >demo #1: insert friend's data</a></li>
                <li><a href="interaction2.php" >demo #2: interaction like a wizard</a></li>
            </ul>
        </div>
        <div class="span6">
            <h4>more demo</h4>
            more demo should come up, maybe soon...
        </div>
    </div>
    <footer class="footer">
        <hr>
        <p>WScore Developed by WorkSpot.JP<br />
        thanks, bootstrap. </p>
    </footer>
</div>
</body>
</html>