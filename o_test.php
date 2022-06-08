<?php
    session_start();
    $servername = "localhost";
    $database  = "test";
    $username = "root";
    $password = "";

    $dbh = new PDO("mysql:host=$servername; dbname=$database",$username,$password);  

if(isset($_POST['submit']) && $_POST['submit'] == "getReadyState"){
    $stmt = $dbh->query("select * from ttable");
    while($row = $stmt->fetch()){
        if($row['role'] <> $_SESSION['session_role']){
            echo $row['ready'];
            break;
        }
    }
}else if(isset($_POST['submit']) && $_POST['submit'] == "setReadyState"){
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->beginTransaction();

    if($_POST['state'] == 0){
        $dbh->exec( "update ttable set ready=0 where role='" . $_SESSION['session_role'] . "'");
    }else if($_POST['state'] == 1){
        $dbh->exec( "update ttable set ready=1 where role='" . $_SESSION['session_role'] . "'");
    }else if($_POST['state'] == 2){
        $dbh->exec( "update ttable set ready=0");
    }
        
    $dbh->commit();    
}else if(isset($_POST['submit']) && $_POST['submit'] == "setUserRole"){
    $_SESSION['session_role'] = $_POST['role'];
}
?>