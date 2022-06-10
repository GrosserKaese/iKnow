<?php
    session_start();
    include "o_header.php";
    include "o_functions.php";

    //$_SESSION['user_role']        =       "admin","member"
    //$_SESSION['session_role']     =       "host","guest"
    //$_SESSION['user_name']        =       z.B. "Brettschneider_Horst1966"
    //$_SESSION['session_name']     =       z.B. "0815" oder "4711"
    //$_SESSION['game_mode']        =       coop oder versus

        // das Login-Skript
    if(isset($_POST['submit']) && $_POST['submit'] == "Login"){
        $stmt = $dbh->query("select * from users");
        // alle User durchsuchen
        while($row = $stmt->fetch()){
            // stimmen user-Name und Passwort-Hash überein?
            if($row['user'] == $_POST['user'] && $row['passw'] == hash("sha256",$_POST['passw'])){
                $_SESSION['user_name'] = $row['user'];
                // je nach Eintrag in der Datenbank wird entsprechend
                // die Rolle in die Session-ID 'user_role' geschrieben
                if($row['role'] == "admin"){
                    $_SESSION['user_role'] = "admin";
                    header("Location: menu.php");
                }else if($row['role'] == "member"){
                    $_SESSION['user_role'] = "member";
                    header("Location: menu.php");
                }
            }
        }

        // *******************************************************************************************
        // Session erstellen
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "Create session"){
        $sessionnumber = rand(1,9999);
        $cnt = 1;
        $_SESSION['session_role'] = "host";
        $stmt = $dbh->query("select * from sessions");
        // Schleife, um zu prüfen, ob die erstellte Session-ID schon vergeben ist
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
        $dbh->exec("insert into sessions (sessionname,hostname,userID,subject,class,modus) values ('" . $sessionnumber . "','" . 
                                                                                                        $_SESSION['user_name'] . "'," . 
                                                                                                        getUserIDfromName($_SESSION['user_name']) . ",'" .
                                                                                                        $_POST['subject'] . "','" .
                                                                                                        $_POST['class'] . "','" .
                                                                                                        $_POST['modus'] . "')");
        $dbh->commit();

        $_SESSION['session_name'] = $sessionnumber;

        // KRITISCH! Hier wird in die Session geschrieben, dass 
        // der User der Taktgeber ist. Wichtig für waitingroom.php und playfield.php
        $_SESSION['session_role'] = "host";

        // hier wird der Spielmodus festgehalten
        $_SESSION['game_mode'] = $_POST['modus'];

        header("Location: waitingroom.php");

        // *******************************************************************************************
        // Session beitreten
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "Join Session"){

        $_SESSION['session_name'] = $_POST['joinsession'];
        $_SESSION['session_role'] = "guest";

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("insert into sessions (sessionname,userID) values ('" . $_SESSION['session_name']. "'," . getUserIDfromName($_SESSION['user_name']) . ")");
        $dbh->commit();

        header("Location: waitingroom.php");

        // *******************************************************************************************
        // hier wird der Herzschlag für den Taktgeber generiert
        // *******************************************************************************************
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

        // *******************************************************************************************
        // hier wird der Herzschlag empfangen
        // *******************************************************************************************
    }else if(isset($_POST['submit']) == true && $_POST['submit'] == "getBeat"){
        $stmt = $dbh->query("select * from sessions where sessionname='" . $_SESSION['session_name'] . "'");
        while($row = $stmt->fetch()){
            echo $row['heartbeat'];
            break;
        }

        // *******************************************************************************************
        // hier wird eine Frage hinzugefügt
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "addQuestion"){
        // Anpassen der Checkboxergebnisse, weil MySQL nur 0 und 1 annimmt
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

        // *******************************************************************************************
        // hier wird für waitingroom.php der Bereitschaftsstatus des Gegenübers abgefragt
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "getReadyState"){
        $stmt = $dbh->query("select * from sessions where sessionname=" . $_SESSION['session_name']);
        // gehe zu der SessionID, die abgefragt wird und guck dir die Spieler an
        // wenn es nicht die eigene ID ist, muss es die vom Mitspieler sein
        while($row = $stmt->fetch()){

            if($row['userID'] != getUserIDfromName($_SESSION['user_name'])){
                if($_POST['state'] == "ready"){
                    echo $row['ready'];
                }else if($_POST['state'] == "control"){
                    echo $row['control'];
                }
            }
            /*
            readyState = $.get("o_general.php",{"submit":"getReadyState",
                                                "sessionID":"<?php echo $_SESSION['session_name']; ?>",
                                                "userName":"<?php echo $_SESSION['user_name'] ?>"},changeState());
            */
        }

        // *******************************************************************************************
        // hier wird der Bereit-Status auf den Server übertragen
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "changeReadyState"){
        $stmt = $dbh->query("select * from sessions where userID='" . getUserIDfromName($_POST['username']) . "'");
        while($row = $stmt->fetch()){
            if($row['sessionname'] == $_POST['sessionname']){
                if($_POST['value'] == "on"){
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $dbh->beginTransaction();
                    $dbh->exec( "update sessions set ready=1 where sessionname='" . $_POST['sessionname'] . "' and userID=" . getUserIDfromName($_POST['username']));
                    $dbh->commit();
                    break;
                }else{
                    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $dbh->beginTransaction();
                    $dbh->exec( "update sessions set ready=0 where sessionname='" . $_POST['sessionname']  . "' and userID=" . getUserIDfromName($_POST['username']));
                    $dbh->commit();
                    break;
                }
            }
        }

        // *******************************************************************************************
        // hier wird das eigentliche Spiel gestartet
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "Start"){
        header("Location:playfield.php");

        // *******************************************************************************************
        // Wenn im Wartezimmer auf Abbrechen gedrückt wird,
        // macht es der Host, wird die Session zerstört, und seine Credentials gelöscht,
        // macht es der Gast werden nur die Credentials gelöscht
        // *******************************************************************************************
    }else if(isset($_POST['cancel']) && $_POST['cancel'] == "Abbrechen"){
        if($_SESSION['session_role'] == "host"){
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->beginTransaction();
            $dbh->exec("delete from sessions where sessionname=" . $_SESSION['session_name']);
            $dbh->commit();

            $_SESSION['session_name'] = "";
            $_SESSION['session_role'] = "";

        }else if($_SESSION['session_role'] == "guest"){
            $_SESSION['session_name'] = "";
            $_SESSION['session_role'] = "";            
        }

        header("Location:menu.php");
        
        // *******************************************************************************************
        // hier wird die aktuelle Frage auf den Server gestellt
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "updateActQuestion"){
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("update sessions set actQuestion=" . $_POST['question'] . " where sessionname='" . $_SESSION['session_name'] . "'");
        $dbh->commit();

        // *******************************************************************************************
        // hier wird signalisiert, dass der jeweilige Spieler fertig ist mit der Frage
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "flagQuestionDone"){

        // Zusammenholen der geforderten Variablen
        $sessionID = $_SESSION['session_name'];
        $user = $_SESSION['user_name'];
        $questID = $_POST['question'];
        
        $bAnsw1 = 0;
        $bAnsw2 = 0;
        $bAnsw3 = 0;
        $bAnsw4 = 0;
        
        if($_POST['bansw1'] == "true"){
            $bAnsw1 = 1;
        }
        if($_POST['bansw2'] == "true"){
            $bAnsw2 = 1;
        }
        if($_POST['bansw3'] == "true"){
            $bAnsw3 = 1;
        }
        if($_POST['bansw4'] == "true"){
            $bAnsw4 = 1;
        }

        // Eintragen der geforderten Variablen
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("insert into doneQuestions (question,sessionname,user,bAnsw1,bAnsw2,bAnsw3,bAnsw4) values ('" . $questID . "','" . $sessionID . "','" . $user . "','" . $bAnsw1 . "','" . $bAnsw2 . "','" . $bAnsw3 . "','" . $bAnsw4 . "')");
        $dbh->commit();  
        
        // Ändern des Control-Status, dass der Spieler fertig ist
        //
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec( "update sessions set control=1 where sessionname='" . $_SESSION['session_name'] . "' and userID=" . getUserIDfromName($_SESSION['user_name']));
        $dbh->commit();

        // *******************************************************************************************
        // setzen des Readystatus (in der Datenbank bei Control)
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "setReadyState"){

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();

        if($_POST['status'] == 0){
            $dbh->exec( "update sessions set control=0 where sessionname='" . $_SESSION['session_name'] . "' and userID=" . getUserIDfromName($_SESSION['user_name']));
        }else if($_POST['status'] == 1){
            $dbh->exec( "update sessions set control=1 where sessionname='" . $_SESSION['session_name'] . "' and userID=" . getUserIDfromName($_SESSION['user_name']));
        }else if($_POST['status'] == 2){
            // das hier ist für das Zurücksetzen beider Controlstates für die nächste Frage
            $dbh->exec( "update sessions set control=0 where sessionname='" . $_SESSION['session_name']);
        }
        
        $dbh->commit();
        
        // *******************************************************************************************
        // Neue Frage schicken
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "getNewQuestion"){

        // Herausfinden, wieviele Fragen in dem gezeigten Modul vorkommen
        $stmt = $dbh->query("select count(ID) from questions where subject='" . $_POST['subject'] . "' and class='" . $_POST['class'] . "'");
        $numberOfQuestions = $stmt->fetchAll()[0][0];
        
        // Schleife über alle Fragen, die für das subject und die class infrage kommen.
        // Falls die Frage bereits in der doneQuestions unter derselben SessionID vorkommt, wird die nächste Frage genommen.
        while(true){

            // Notbremse, wenn die Anzahl der zur Verfügung stehenden Fragen mit der Zahl
            // der bereits beantworteten Fragen übereinstimmt
            $checkDoneQuestions = $dbh->query("select count(ID) from doneQuestions where sessionname=" . $_SESSION['session_name']);
            if($checkDoneQuestions->fetchAll()[0][0] == $numberOfQuestions){
                echo "forceQuit";
                break;
            }

            // Zahl auswürfeln
            $qNr = rand(0,$numberOfQuestions-1);

            // Query erstellen
            $stmt = $dbh->query("select * from questions where subject='" . $_POST['subject'] . "' and class='" . $_POST['class'] . "'");

            // Counter setzen
            $cnt = 0;

            // Equal-Trigger setzen
            $bIsEqual = 0;

            // Schleife beginnen
            while($row = $stmt->fetch()){
                if($cnt == $qNr && $row['bIsReviewed'] == 1 && $row['isFlagged'] == 0){
                    // Hier angekommen, ist er bei der richtigen zufälligen Frage angekommen.
                    // Nun die Frage in der doneQuestions suchen
                    $checkQuestion = $dbh->query("select ID from doneQuestions where sessionname=" . $_SESSION['session_name']);
                    while($row1 = $checkQuestion->fetch()){
                        if($row['ID'] == $row1['ID']){
                            $bIsEqual = 1;
                            break;
                        }
                    }

                    if($bIsEqual == 1){
                        break;
                    }
                }
                
                if($bIsEqual == 0){
                    // Wenn hier angekommen, dann ist die ausgewählte Frage noch nicht beantwortet worden.
                    // Somit kann das HTML für die Frage konstruiert werden.
                    $htmlString = "<p>" . $row['question'] . "</p>";
                    for($i = 1;$i < 5;$i++){
                        $htmlString = $htmlString . "<input type='checkbox' id='a" . $i . "'><span id='answ" . $i . "'>" . $row['Answer' . $i] . "</span><br>";
                    }
                    
                    echo $htmlString;
                    break;
    
    
                    /* 
                    <div id="questionOnDisplay">
    
                        <p id="question"></p>
                        
                        <input type="checkbox" id="a1"><span id="answ1"></span><br>
                        <input type="checkbox" id="a2"><span id="answ2"></span><br>
                        <input type="checkbox" id="a3"><span id="answ3"></span><br>
                        <input type="checkbox" id="a4"><span id="answ4"></span><br>
    
                    </div>
                    */
                    
                }

                $cnt++;
            }

        }
       
        // *******************************************************************************************
        // Die aktive Session wird zerstört und die Sessioncredentials zurückgesetzt
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "destroySession"){
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("delete from sessions where sessionname=" . $_SESSION['session_name']);
        $dbh->commit();

        $_SESSION['session_name'] = "";
        $_SESSION['session_role'] = "";
    }