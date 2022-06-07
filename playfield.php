<?php
    session_start();
    include "o_header.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <script src="jquery-3.6.0.js"></script>
        <title>Playfield Session <?php echo $_SESSION['session_name']; ?></title>
    </head>
    <body>
        <p>Session Number: <?php echo $_SESSION['session_name'] ?></p>
        <p id="beat"></p>
        <?php
            // Mach dir eine Liste mit den ID's der Fragen, die in Frage kommen
            //
            $classes = $dbh->query("select subject,class from sessions where sessionname=" . $_SESSION['session_name'] . " and hostname='admin'");
            while($row = $classes->fetch()){
                $subjectname = $row['subject'];
                $classname = $row['class'];
            }
            
            // kurz herausfinden, wie viele Fragen es insgesamt sind
            $num1 = $dbh->query("select count(subject) from questions where subject='" . $subjectname . "' and class='" . $classname . "'");
            $numrows = $num1->fetchAll()[0][0];

            // braucht Javascript später
            echo "<script>var numberOfQuestions = " . $numrows . "</script>";

            $stmt = $dbh->query("select * from questions where subject='" . $subjectname . "' and class='" . $classname . "'");

            // Erstellen einer Liste den infrage kommenden Fragen
            echo "<script>";
            echo "var questionList = [";
            $cnt = 0;
            while($row = $stmt->fetch()){
                $cnt++;
                if($row['bIsReviewed'] == 0 && $row['isFlagged'] == 0){
                    echo "[";
                    echo $row['ID'] . ",";                  // 0
                    echo "'" . $row['question'] . "',";     // 1 
                    echo $row['bAnsw1'] . ",";              // 2
                    echo "'" . $row['Answer1'] . "',";      // 3 
                    echo $row['bAnsw2'] . ",";              // 4
                    echo "'" . $row['Answer2'] . "',";      // 5
                    echo $row['bAnsw3'] . ",";              // 6
                    echo "'" . $row['Answer3'] . "',";      // 7
                    echo $row['bAnsw4'] . ",";              // 8
                    echo "'" . $row['Answer4'] . "',";      // 9
                    echo "'" . $row['explanation'] . "'";   // 10                                       
                    if($cnt < $numrows){
                        echo "],";
                    }else{
                        echo "]";
                    }
                }

            }
            echo "];</script>";

        ?>
        <div>
            <p id="question"></p>
        </div>
        <div id="answers">
            <input id="bAnsw1" type="checkbox"><span id="answ1"></span><br>
            <input id="bAnsw2" type="checkbox"><span id="answ2"></span><br>
            <input id="bAnsw3" type="checkbox"><span id="answ3"></span><br>
            <input id="bAnsw4" type="checkbox"><span id="answ4"></span><br>
        </div>
        <p id="waitmessage" hidden>Warten auf Mitspieler....</p>

        <?php 
            // der Button stellt den Flag "control" in sessions auf "1" und signalisiert,
            // dass der jeweilige Spieler fertig ist.
            // Je nach Spielmodus wird dann anders verfahren
        ?>
        <button onclick=questionDone()>Fertig!</button>

    </body>
    <script>
        var ownRole = "<?php echo $_SESSION['session_role']; ?>";
        clearInterval();
        var global_beat = 0;
        var actQuestion = 0;
        var readyControl = "0";

        // diese Liste enthält alle schon abgehandelten Fragen
        var finishedQuestions = [];

        // dieser "Ticker" zeigt an, dass man selbst fertig ist
        var global_wait_ticker = 0;

        setInterval(tickBeat,1000);
        
        // Ist der Gast angemeldet, muss eine künstliche Verzögerung
        // eingebaut werden, damit der Server Zeit hat, sich zu sortieren
        //
        if(ownRole == "host"){
            displayQuestion();
        }else{
            setTimeout(displayQuestion(),1000);
        }

        
        // hier wird signalisiert, dass der Spieler fertig ist;
        // übergeben wird auch die ID der Frage des Fragenkatalogs,
        // um sie richtig zuordnen zu können
        //
        function questionDone(){

            $.post("o_general.php",{
                    submit:"flagQuestionDone",
                    question:actQuestion,
                    bansw1:$("#bAnsw1").prop("checked"),
                    bansw2:$("#bAnsw2").prop("checked"),
                    bansw3:$("#bAnsw3").prop("checked"),
                    bansw4:$("#bAnsw4").prop("checked")
                },null);

                // Löschen des Textes
                $("#question").text("");

                $("#answ1").text("");
                $("#answ2").text("");
                $("#answ3").text("");
                $("#answ4").text("");

                document.getElementById("bAnsw1").checked = false;
                document.getElementById("bAnsw2").checked = false;
                document.getElementById("bAnsw3").checked = false;
                document.getElementById("bAnsw4").checked = false;

                // Frage auf die Liste der fertigen Fragen schieben
                finishedQuestions.push(actQuestion);
                global_wait_ticker = 1;

                // Warten auf Signal von Mitspieler oder auf Spielende
                document.getElementById("waitmessage").hidden = false;
        }

        // Hier wird die nächste Frage getriggert,
        // unabhängig davon, ob die Spieler abgegeben haben oder
        // die Zeit abgelaufen ist
        //
        function nextQuestion(){

        }

        // displayQuestion wird beim Start der Session und bei jeder neuen Anforderung einer Frage getriggert
        function displayQuestion(){

            if(ownRole == "host"){
                // Wähle zufällig eine Frage aus der Liste der zur Verfügung stehenden Fragen (questionList)
                actQuestion = Math.floor(Math.random() * numberOfQuestions);

                // Schicke die Frage an den Server, damit der Mitspieler sie sehen kann
                $.post("o_general.php",{
                    submit:"updateActQuestion",
                    question:actQuestion
                },null);

                // Wirf die Frage an die Wand
                $("#question").text(questionList[actQuestion][1]);
                $("#answ1").text(questionList[actQuestion][3]);
                $("#answ2").text(questionList[actQuestion][5]);
                $("#answ3").text(questionList[actQuestion][7]);
                $("#answ4").text(questionList[actQuestion][9]);

            }else{
                // TODO: Nicht Inline, sondern als Funktion
                actQuestion = <?php 
                    $stmt = $dbh->query("select actQuestion from sessions where sessionname='" . $_SESSION['session_name'] . "' and hostname <>'" . $_SESSION['user_name'] . "'");
                    while($row = $stmt->fetch()){
                        echo $row['actQuestion'];
                        break;                        
                    }
                ?>;
                $("#question").text(questionList[actQuestion][1]);
                $("#answ1").text(questionList[actQuestion][3]);
                $("#answ2").text(questionList[actQuestion][5]);
                $("#answ3").text(questionList[actQuestion][7]);
                $("#answ4").text(questionList[actQuestion][9]);
            }

        }

        function tickBeat(){

            // Hier wird je nach Host oder Gast der Heartbeat an den Server übermittelt
            // oder von diesem abgefragt
            //
            if(ownRole == "host"){
                global_beat = global_beat + 1;
                document.getElementById('beat').innerText = global_beat;
            
                $.post('o_general.php',
                {
                    submit: 'heartbeat',
                    sessionname: "<?php echo $_SESSION['session_name']; ?>",
                    beat: global_beat
                },null);
            }else{
                $('#beat').load('o_general.php',{
                'submit':'getBeat'
                },changeTick());
            }

            // wenn der Ticker anzeigt, dass der Spieler seine Frage abgegeben hat,
            // wird der Server angepingt und gefragt, ob der zweite Spieler fertig ist
            //
            if(global_wait_ticker == 1){
                readyControl = $.post("o_general.php",{"submit":"getControlState"},checkIfDone());
            }
         
        }   
    
        function checkIfDone(){
            // sollte der zweite Spieler fertig sein, wird zur nächsten Frage geschaltet
            // TODO: Delay für displayQuestion() einbauen
            // TODO: die Zufallsfragengenerierung auslagern
            if(readyControl.responseText == "1"){
                console.log("Hab ich!");
                global_wait_ticker = 0;
                $.post("o_general.php",{submit:"setControlState"},null);
                displayQuestion();
            }                
        }

        function changeTick(){
            $('#beat').text(global_beat)           
        }


    </script>
</html>