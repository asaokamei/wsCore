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
        <p class="lead">Data Mapper and Cenatar technology for web applications.<br />
            Wishes wishes wishes. </p>
        <hr>
    </header>
    <div class="row-fluid marketing">
        <div class="span6">
            <h3>Basic Features</h3>
            <h4>&gt; <a href="password.php" >generate password</a></h4>
            <p>demo for building form elements. </p>
            <h4>&gt; <a href="myTasks" >task/todo application</a></h4>
            <p>demo for using data mapper, model, and entity. </p>
            <p class="memo">please give <code>./task/data</code> folder writable permission for web server to store sqlite file. </p>
            <h4>&gt; <a href="myFriends" >friends & contacts</a></h4>
            <p>demo for using relation, such as hasOne, isRef, isJoined, and isJoinDao relation types. </p>
            <p class="memo">click <a href="myFriends/setup">setup</a> to set up sqlite data file for this demo. </p>
        </div>
        <div class="span6">
            <h3>Experimental Demo</h3>
            <h4>&gt; interaction with forms</h4>
            <p>demo for data mapper and interactions. </p>
            <ul>
                <li><a href="interaction1.php" >demo #1: insert friend's data</a></li>
                <li><a href="interaction2.php" >demo #2: interaction like a wizard</a></li>
            </ul>
        </div>
        <div class="span12">
            <p>development still undergoing. more demo should come up, hopefully sometime soon...</p>
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