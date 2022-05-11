<?php
    $servername = "localhost";
    $database  = "iknow";
    $username = "root";
    $password = "";

    $dbh = new PDO("mysql:host=$servername; dbname=$database",$username,$password);
?>