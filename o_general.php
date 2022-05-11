<?php
    session_start();
    include "o_header.php";

    //$_SESSION['user_role']        =       "admin","member"
    //$_SESSION['session_role']     =       "host" #braucht man das überhaupt??
    //$_SESSION['user_name']
    //$_SESSION['session_name']

    if(isset($_POST['submit']) && $_POST['submit'] == "Login"){
        $stmt = $dbh->query("select * from users");
        while($row = $stmt->fetch()){
            if($row['user'] == $_POST['user'] && $row['passw'] == hash("sha256",$_POST['passw'])){
                $_SESSION['user_name'] = $row['user'];
                if($row['role'] == "admin"){
                    $_SESSION['user_role'] = "admin";
                    header("Location: menu.php");
                }else if($row['role'] == "member"){
                    $_SESSION['user_role'] = "member";
                    header("Location: menu.php");                    
                }
            }
        }
    }else if(isset($_POST['submit']) && $_POST['submit'] == "Create session"){
        $sessionnumber = rand(1111,9999);
        $cnt = 1;
        $_SESSION['session_role'] = "host";
        $stmt = $dbh->query("select * from sessions");
        while(true){
            $stmt = $dbh->query("select * from sessions");
            while($row = $stmt->fetch()){
                if($row['sessionname'] == $sessionnumber){
                    $cnt = 0;
                    break;
                }
            }
            if($cnt == 1){
                break;
            }
            $sessionnumber = rand(1111,9999);
        }
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("insert into sessions (sessionname,hostname) values ('" . $sessionnumber . "','" . $_SESSION['user_name'] . "')");
        $dbh->commit();

        $_SESSION['session_name'] = $sessionnumber;

        header("Location: playfield.php#" . $sessionnumber);
    }else if(isset($_POST['submit']) && $_POST['submit'] == "Join Session"){

        $_SESSION['session_name'] = $_POST['joinsession'];
        header("Location: joinsession.php#" . $_SESSION['session_name']);

    }else if(isset($_POST['submit']) && $_POST['submit'] == "heartbeat"){
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $dbh->query("select * from sessions");
        while($row = $stmt->fetch()){
            if($row['sessionname'] == $_POST['sessionname']){
                $dbh->beginTransaction();
                $dbh->exec("update sessions set heartbeat = '" . $_POST['beat'] . "' where sessionname = '" . $_POST['sessionname'] . "'");
                $dbh->commit();
                break;
            }
        }
    }else if(isset($_POST['submit']) == true && $_POST['submit'] == "getBeat"){
        $stmt = $dbh->query("select * from sessions where sessionname='" . $_SESSION['session_name'] . "'");
        while($row = $stmt->fetch()){
            echo $row['heartbeat'];
        }
    }else if(isset($_POST['submit']) && $_POST['submit'] == "addQuestion"){
        // Apassen der Checkboxergebnisse, weil MySQL nur 0 und 1 annimmt
        $ch1 = 0;
        $ch2 = 0;
        $ch3 = 0;
        $ch4 = 0;
        $explan = "";
        
        if(isset($_POST['check1']) == true){
            $ch1 = 1;
        }
        if(isset($_POST['check2']) == true){
            $ch2 = 1;
        }
        if(isset($_POST['check3']) == true){
            $ch3 = 1;
        }
        if(isset($_POST['check4']) == true){
            $ch4 = 1;
        }      
        
        if(isset($_POST['explain']) == true){
            $explan = $_POST['explain'];
        }

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec( "insert into questions (subject,class,question,explanation,bAnsw1,bAnsw2,bAnsw3,bAnsw4,Answer1,Answer2,Answer3,Answer4) values ('" . $_POST['subject'] . "','" . $_POST['class'] . "','" . $_POST['question'] . "','" . $_POST['explain'] . "','" . $ch1 . "','" . $ch2 . "','" . $ch3 . "','" . $ch4 . "','" . $_POST['answ1'] . "','" . $_POST['answ2'] . "','" . $_POST['answ3'] . "','" . $_POST['answ4'] . "')");
        $dbh->commit();

        header('Location:addQuestions.php');
    }
?>