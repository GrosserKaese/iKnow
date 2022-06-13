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

        $stmt = $dbh->query("select modus from sessions where sessionname=" . $_SESSION['session_name']);
        $_SESSION['game_mode'] = $stmt->fetchAll()[0][0];

        header("Location: waitingroom.php");

        // *******************************************************************************************
        // hier wird der Herzschlag für den Taktgeber generiert
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "heartbeat"){
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $dbh->query("select * from sessions");
        while($row = $stmt->fetch()){
            if($row['sessionname'] == $_SESSION['session_name']){
                $dbh->beginTransaction();
                $dbh->exec("update sessions set heartbeat = '" . $_POST['beat'] . "' where sessionname = '" . $_SESSION['session_name'] . "'");
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
        // Host prüft die Stati der beiden Control-Werte bei Sessions
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "getReadyState"){
        if($_POST['state'] == "control"){
            $sessArray = array();

            $stmt = $dbh->query("select control from sessions where sessionname=" . $_SESSION['session_name']);
            // gehe zu der SessionID, die abgefragt wird und guck dir die Spieler an
            // wenn es nicht die eigene ID ist, muss es die vom Mitspieler sein
            while($row = $stmt->fetch()){
                array_push($sessArray,$row['control']);
            }
    
            if($sessArray[0] == 1 && $sessArray[1] == 1){
                echo "equal";
            }else{
                echo "unequal";
            }
        }else if($_POST['state'] == "ready"){
            $stmt = $dbh->query("select * from sessions where sessionname=" . $_SESSION['session_name']);
            while($row = $stmt->fetch()){
                if($row['userID'] <> getUserIDfromName($_SESSION['user_name'])){
                    echo $row['ready'];
                    break;
                }
            }
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
        if($_SESSION['session_role'] == "host"){
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->beginTransaction();
            $dbh->exec("update sessions set bGameStarted=1 where sessionname=" . $_SESSION['session_name'] . " and userID='" . getUserIDfromName($_SESSION['user_name']) . "'");
            $dbh->commit();

            header("Location:playfield.php");
        }else if($_SESSION['session_role'] == "guest"){
            $stmt = $dbh->query("select bGameStarted from sessions where sessionname = " . $_SESSION['session_name'] . " and userID <> '" . getUserIDfromName($_SESSION['user_name']) . "'");
            if($stmt->fetchAll()[0][0] == 1){
                echo "start";
            }
        }

        

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
        // hier wird die Frage vorerst auf donequestions geschubst
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "flagQuestionDone"){
        
        // Zusammenholen der Variablen
        $stmt = $dbh->query("select actQuestion from sessions where sessionname=" . $_SESSION['session_name']);
        $actNumber = $stmt->fetchAll()[0][0];

        $a1 = 0;
        $a2 = 0;
        $a3 = 0;
        $a4 = 0;

        if($_POST['a1'] == "true"){
            $a1 = 1;
        }
        if($_POST['a2'] == "true"){
            $a2 = 1;
        }
        if($_POST['a3'] == "true"){
            $a3 = 1;
        }
        if($_POST['a4'] == "true"){
            $a4 = 1;
        }

        $stmt = $dbh->query("select ID from questions where subject='" . $_POST['subject'] . "' and class='" . $_POST['class'] . "'");
        $cnt = 0;
        while($row = $stmt->fetch()){
            if($cnt == $actNumber){
                $qID = $row['ID'];
            }
            $cnt++;
        }

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("insert into donequestions (question,sessionname,user,bAnsw1,bAnsw2,bAnsw3,bAnsw4,qCounter) values (" . $qID . "," . $_SESSION['session_name'] . ",'" . $_SESSION['user_name'] . "'," . $a1 . "," . $a2 . "," . $a3 . "," . $a4 . "," . $_POST['qCounter'] . ")");
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
            $dbh->exec( "update sessions set control=0 where sessionname='" . $_SESSION['session_name'] . "'");
        }
        
        $dbh->commit();
        
        // *******************************************************************************************
        // Neue Frage schicken
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "getNewQuestion"){
        
        // Wenn noch nicht geschehen, dann initialisieren des Fragencounters
        if(!isset($_SESSION['session_counter'])){
            $sessCnt = 0;
            $_SESSION['session_counter'] = $sessCnt;
        }else{
            $sessCnt = $_SESSION['session_counter'];
        }

        // Herausfinden, wieviele Fragen in dem gezeigten Modul vorkommen
        // und in $numberOfQuestions speichern
        $stmt = $dbh->query("select count(ID) from questions where subject='" . 
                            $_POST['subject'] . "' and class='" . $_POST['class'] . 
                            "' and bIsReviewed <> 0 and isFlagged <> 1");
        while($row = $stmt->fetch()){
            $numberOfQuestions = $row['count(ID)'];
            break;
        }

        // Schalter, falls die maximale Fragenzahl erreicht ist
        // oder alle Fragen aufgebraucht sind
        $sessCnt++;
        $_SESSION['session_counter'] = $sessCnt;

        if($sessCnt-1 >= $numberOfQuestions || $sessCnt-1 > 10){
            $sessCnt = null;
            $_SESSION['session_counter'] = null;

            // Zurücksetzen des "Ready"-Status, um zu signalisieren,
            // dass auf Results weitergeleitet wird
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->beginTransaction();            
            $dbh->exec("update sessions set ready=0 where sessionname=" . $_SESSION['session_name'] . " and userID=" . getUserIDfromName($_SESSION['user_name']));
            $dbh->commit();  

            //header("Location:results.php");
            echo "forceQuit";
            return;
        }

        // eine Frage suchen
        $overflow = false;
        $bHasBeenPicked = true;
        while($bHasBeenPicked == true){
            $randomQuestNumber = rand(0,$numberOfQuestions-1);
            $bHasBeenPicked = false;

            // suche die zufällig gewählte Nummer in der Liste der validen Fragen
            $qCnt = 0;
            $stmt = $dbh->query("select * from questions where subject='" . $_POST['subject'] . "' and class='" . $_POST['class'] . "'"); // ID
            $stmt1 = $dbh->query("select * from donequestions where sessionname=" . $_SESSION['session_name']); // question
            while($row = $stmt->fetch()){
                if($randomQuestNumber == $qCnt){
                    if($row['bIsReviewed'] <> 0 && $row['isFlagged'] <> 1){
                        while($row1 = $stmt1->fetch()){
                            if($row['ID'] == $row1['question']){
                                $bHasBeenPicked = true;
                                break;
                            }
                        }
                    }else{
                        break;
                    }
                    if($bHasBeenPicked == true){
                        break;
                    }else{
                        // aktuelle Frage aktualisieren
                        // in actQuestion wird die hier errechnete laufende Nummer eingetragen, nicht die ID!
                        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $dbh->beginTransaction();        
                        $dbh->exec("update sessions set actQuestion=" . $qCnt . " where sessionname='" . $_SESSION['session_name'] . "'");
                        $dbh->commit();
                        
                        echo "<p>" . $row['question'] . "</p>";
                        for($i = 1;$i < 5;$i++){
                            echo "<input type='checkbox' id='a" . $i ."'>";
                            echo "<span id='answ" . $i . "'>" . $row['Answer' . $i] . "</span><br>";
                        }

                        break;                        
                    }
                }

                $qCnt++;
            }
        }

        // *******************************************************************************************
        // Abholen der momentanen Frage
        // *******************************************************************************************        
    }else if(isset($_POST['submit']) && $_POST['submit'] == "getActQuestion"){
        $stmt = $dbh->query("select actQuestion from sessions where sessionname='" . $_SESSION['session_name'] . "'");
        $actQrank = $stmt->fetchAll()[0][0];

        $qCnt = 0;
        $stmt = $dbh->query("select * from questions where subject='" . $_POST['subject'] . "' and class='" . $_POST['class'] . "'");
        while($row = $stmt->fetch()){
            if($actQrank == $qCnt){
                if($row['bIsReviewed'] <> 0 && $row['isFlagged'] <> 1){                    
                    echo "<p>" . $row['question'] . "</p>";
                    for($i = 1;$i < 5;$i++){
                        echo "<input type='checkbox' id='a" . $i ."'>";
                        echo "<span id='answ" . $i . "'>" . $row['Answer' . $i] . "</span><br>";
                    }                    
                    break;
                }
            }
            $qCnt++;
        }
        // *******************************************************************************************
        // Die aktive Session wird zerstört und die Sessioncredentials zurückgesetzt sowie die Fragen gelöscht
        // *******************************************************************************************
    }else if(isset($_POST['submit']) && $_POST['submit'] == "destroySession"){
        // Fragencounter neutralisieren
        $_SESSION['session_counter'] = null;

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("delete from sessions where sessionname=" . $_SESSION['session_name']);
        $dbh->exec("delete from donequestions where sessionname=" . $_SESSION['session_name']);
        $dbh->commit();

        $_SESSION['session_name'] = "";
        $_SESSION['session_role'] = "";

        // *******************************************************************************************
        // für den Coop-Modus: prüft, ob die Antworten übereinstimmen
        // *******************************************************************************************        
    }else if(isset($_POST['submit']) && $_POST['submit'] == "checkQuestions"){
        $answArray = array();
        $stmt = $dbh->query("select * from donequestions where sessionname=" . $_SESSION['session_name'] ." and qCounter=" . $_POST['qCounter']);
        while($row = $stmt->fetch()){
            for($i = 1;$i < 5;$i++){
                array_push($answArray,$row['bAnsw' . $i]);
            }
        }

        $bHasFailed = 0;

        for($i = 0;$i < 4;$i++){
            if($answArray[0+$i] <> $answArray[4+$i]){
                $bHasFailed = 1;
                break;
            }
        }

        if($bHasFailed == 1){
            echo "fail";
        }else if($bHasFailed == 0){
            echo "success";
        }
        // *******************************************************************************************
        // Löschen der vorher gemachten Fragen
        // *******************************************************************************************          
    }else if(isset($_POST['submit']) && $_POST['submit'] == "rollbackQuestions"){
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("delete from donequestions where sessionname=" . $_SESSION['session_name'] . " and qCounter=" . $_POST['qCounter']);
        $dbh->commit();  
        // *******************************************************************************************
        // Gast fragt seinen Status auf dem Server ab
        // *******************************************************************************************         
    }else if(isset($_POST['submit']) && $_POST['submit'] == "getOwnReadyState"){
        $stmt = $dbh->query("select control from sessions where sessionname=" . $_SESSION['session_name'] . " and userID=" . getUserIDfromName($_SESSION['user_name']));
        if($stmt->fetchAll()[0][0] == 0){
            echo "zero";
        }else{
            echo "one";
        }
        // *******************************************************************************************
        // Gast prüft, ob Host schon die nächste Frage gestellt hat, indem er den Zähler der zuletzt
        // beantworteten Frage mit seinem lokalen Zähler vergleicht
        // *******************************************************************************************          
    }else if(isset($_POST['submit']) && $_POST['submit'] == "checkQuestionState"){
        $bFoundQuestion = false;
        $stmt = $dbh->query("select * from donequestions where sessionname=" . $_SESSION['session_name']);
        while($row = $stmt->fetch()){
            if($row['qCounter'] == $_POST['qCounter']){
                $bFoundQuestion = true;
                break;
            }
        }
 
        echo $bFoundQuestion;
        // *******************************************************************************************
        // Prüfung, ob die eigene Session noch läuft
        // *******************************************************************************************           
    }else if(isset($_POST['submit']) && $_POST['submit'] == "checkSessionState"){
        $status = "false";

        $stmt = $dbh->query("select * from sessions");
        while($row = $stmt->fetch()){
            if($row['sessionname'] == $_SESSION['session_name']){
                $status = "true";
                break;
            }
        }
        echo $status;

        // *******************************************************************************************
        // Löschen der Fragen in der Session (bei Neustart Spiel)
        // *******************************************************************************************          
    }else if(isset($_POST['submit']) && $_POST['submit'] == "deleteQuestions"){

        // Fragencounter neutralisieren
        $_SESSION['session_counter'] = null;

        // GameStart auf Null setzen
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("update sessions set bGameStarted=0 where sessionname=" . $_SESSION['session_name']);
        $dbh->commit();    

        $stmt = $dbh->query("select * from donequestions");
        while($row = $stmt->fetch()){
            if($row['sessionname'] == $_SESSION['session_name']){
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dbh->beginTransaction();
                $dbh->exec("delete from donequestions where sessionname=" . $_SESSION['session_name']);
                $dbh->commit();                  
            }
        }
        // *******************************************************************************************
        // Guest fragt den Ready-Status des Hosts ab, um zu wissen, ob schon auf results umgeleitet wird
        // *******************************************************************************************          
    }else if(isset($_POST['submit']) && $_POST['submit'] == "checkHostReadyState"){
        $stmt = $dbh->query("select * from sessions where sessionname=" . $_SESSION['session_name'] . " and userID<>'" . getUserIDfromName($_SESSION['user_name']) . "'");
        while($row = $stmt->fetch()){
            if($row['ready'] == 0){
                echo "true";
                break;
            }else{
                echo "false";
            }
        }
    }else if(isset($_POST['submit']) && $_POST['submit'] == "flagQuestion"){
        
        // aktuelle Frage holen
        $stmt = $dbh->query("select * from sessions where sessionname=" . $_SESSION['session_name']);
        while($row = $stmt->fetch()){
            $subject = $row['subject'];
            $class = $row['class'];
            $questNumber = $row['actQuestion']; // ...man möge mich beim nächsten Mal daran erinnern, die ID als zentrales Element zu nutzen.
            break;
        }

        // Frage in der Questiondatenbank suchen
        $stmt = $dbh->query("select * from questions where subject='" . $subject . "' and class='" . $class . "'");
        $cnt = 0;
        while($row = $stmt->fetch()){
            if($questNumber == $cnt){
                if($row['isFlagged'] <> 1 && $row['bIsReviewed'] <> 0){
                    $finalID = $row['ID'];
                }
            }
            $cnt++;
        }

        // Frage flaggen
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->beginTransaction();
        $dbh->exec("update questions set isFlagged=1,flaggedExplanation='" . $_POST['explanation'] . "' where ID=" . $finalID);
        $dbh->commit();  
        
    }