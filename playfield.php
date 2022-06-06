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
            <input type="checkbox"><span id="answ1"></span><br>
            <input type="checkbox"><span id="answ2"></span><br>
            <input type="checkbox"><span id="answ3"></span><br>
            <input type="checkbox"><span id="answ4"></span><br>
        </div>
        <button>Fertig!</button>

    </body>
    <script>
        var ownRole = "<?php echo $_SESSION['session_role']; ?>";
        clearInterval();
        var global_beat = 0;
        var actQuestion = 0;

        setInterval(tickBeat,1000);
        
        if(ownRole == "host"){
            displayQuestion();
        }else{
            setTimeout(displayQuestion(),1000);
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
                },null)

                // Wirf die Frage an die Wand
                $("#question").text(questionList[actQuestion][1]);
                $("#answ1").text(questionList[actQuestion][3]);
                $("#answ2").text(questionList[actQuestion][5]);
                $("#answ3").text(questionList[actQuestion][7]);
                $("#answ4").text(questionList[actQuestion][9]);

                // ... der Code, wo die Frage für den Gast an die Wand geworfen wird,
                // steht bei tickBeat()
            }else{
                actQuestion = <?php 
                    $stmt = $dbh->query("select actQuestion from sessions where sessionname='" . $_SESSION['session_name'] . "'");
                    echo $stmt->fetchAll()[0][0];
                ?>;
                $("#question").text(questionList[actQuestion][1]);
                $("#answ1").text(questionList[actQuestion][3]);
                $("#answ2").text(questionList[actQuestion][5]);
                $("#answ3").text(questionList[actQuestion][7]);
                $("#answ4").text(questionList[actQuestion][9]);
            }

        }

        function tickBeat(){
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
        }   
    
        function changeTick(){
            $('#beat').text(global_beat)           
        }


    </script>
</html>