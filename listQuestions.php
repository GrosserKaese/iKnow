<?php
    session_start();
    include "o_header.php";
    include "o_functions.php";

    // nur zur Sicherheit, dass hier kein Unbefleckter herumeiert
    if($_SESSION['user_role'] <> "admin"){
        $_SESSION['user_role'] == "";
        header('Location:index.php');
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <script src="bootstrap/js/bootstrap.bundle.min.js"></script>        
        <script src="jquery-3.6.0.js"></script>        
        <title>Fragen ansehen</title>
    </head>
    <body>
        
        <?php
            echo "<div id='mainQuestions'>";
            echo "<button id='back'>Abbrechen</button>";
            echo "<h1>Eingestellte Fragen</h1>";
        
            // Schleife, die bei jedem neuen Vorkommen eines Fachs eine neue Tabelle erstellt
            $stmt = $dbh->query("select * from questions order by subject,class asc");
            $question_tracker = array();
            $class_tracker = array();
            $bHasSeen = false;
            $bHasPushed = false;
            while($row = $stmt->fetch()){
                for($i = 0;$i < count($question_tracker);$i++){
                    if($question_tracker[$i] == $row['subject']){
                        $bHasPushed = true;
                    }
                }

                if($bHasPushed <> true){
                    echo "<h2>" . $row['subject'] . "</h2>";
                    $stmt1 = $dbh->query("select * from questions where subject='" . $row['subject'] . "' order by subject,class asc");

                    while($row1 = $stmt1->fetch()){
                        
                        for($j=0;$j<count($class_tracker);$j++){
                            if($row1['class'] == $class_tracker[$j]){
                                $bHasSeen = true;
                            }
                        }
                        
                        if($bHasSeen == false){
  
                            echo "<table class=table>";
                            echo "<thead>";
                            echo "<th>Frage</th>";
                            echo "<th>Antwort 1</th>";
                            echo "<th>Antwort 2</th>";
                            echo "<th>Antwort 3</th>";
                            echo "<th>Antwort 4</th>";
                            echo "<th>Erklärung</th>";
                            echo "<th>reviewed?</th>";
                            echo "<th>geflagged?</th>";
                            echo "<th>Erklärung zu Flag</th>";
                            echo "</thead>";
                            echo "<tbody>";
                            array_push($class_tracker,$row1['class']);
                        }

                        if($bHasSeen == false){
                            echo "<h4>" . $row1['class'] . "</h4>";  
                        }
                        
                        echo "<tr style='cursor:pointer;' ";
                        if($row1['isFlagged'] == 1){
                            echo "class='table-danger' ";
                        }
                        echo "id='" . $row1['ID'] . "' onclick=edit('" . $row1['ID'] . "')>";
                        
                        echo "<td>" . $row1['question'] . "</td>";

                        if($row1['bAnsw1'] == "1"){
                            echo "<td class=table-success>" . $row1['Answer1'] . "</td>";
                        }else{
                            echo "<td>" . $row1['Answer1'] . "</td>";
                        }
                        
                        if($row1['bAnsw2'] == "1"){
                            echo "<td class=table-success>" . $row1['Answer2'] . "</td>";
                        }else{
                            echo "<td>" . $row1['Answer2'] . "</td>";
                        }

                        if($row1['bAnsw3'] == "1"){
                            echo "<td class=table-success>" . $row1['Answer3'] . "</td>";
                        }else{
                            echo "<td>" . $row1['Answer3'] . "</td>";
                        }

                        if($row1['bAnsw4'] == "1"){
                            echo "<td class=table-success>" . $row1['Answer4'] . "</td>";
                        }else{
                            echo "<td>" . $row1['Answer4'] . "</td>";
                        }

                        echo "<td>" . $row1['explanation'] . "</td>";
                        
                        echo "<td>";
                        if($row1['bIsReviewed'] == "0"){
                            echo "Nein";
                        }else{
                            echo "Ja";
                        }
                        echo "</td>";

                        if($row1['isFlagged'] == 1){
                            echo "<td>Ja</td>";
                        }else{
                            echo "<td>Nein</td>";
                        }

                        echo "<td>" . $row1['flaggedExplanation'] . "</td>";
                        echo "</tr>";
                        
                        $bHasSeen = false;  

                    }
                    echo "</tbody>";
                    echo "</table>";
  
                }
                
                $bHasPushed = false;
                array_push($question_tracker,$row['subject']);
            }

            echo "</div>";

        
            echo "<div id='editCont' hidden>";
            echo "<label>ID:</label><span id='contID'></span><br>";

            echo "<label>Fach</label><br>";
            echo "<select name='subject' id='subject'>";
            echo "    <option onclick=changeSubjectTo('CS')>Informatik B.Sc.</option>";
            echo "    <option onclick=changeSubjectTo('WI')>Wirtschaftsinformatik B.A.</option>";
            echo "    <option onclick=changeSubjectTo('HM')>Hotelmanagement M.A.</option>";
            echo "</select><br>     ";

            echo "<label>Modul</label><br>";
            echo "<select name='class' id='class'></select><br>";

            echo "<label>Frage</label><br>";
            echo "<textarea rows='5' cols='45' id='contQuest'></textarea><br>";

            echo "<label>Antwort 1:</label><input type='checkbox' id='check1'><br>";
            echo "<textarea rows='5' cols='45' id='contA1'></textarea><br>";

            echo "<label>Antwort 2:</label><input type='checkbox' id='check2'><br>";
            echo "<textarea rows='5' cols='45' id='contA2'></textarea><br>";

            echo "<label>Antwort 3:</label><input type='checkbox' id='check3'><br>";
            echo "<textarea rows='5' cols='45' id='contA3'></textarea><br>";

            echo "<label>Antwort 4:</label><input type='checkbox' id='check4'><br>";
            echo "<textarea rows='5' cols='45' id='contA4'></textarea><br>";

            echo "<label>Erklärung zur Frage:</label><br>";
            echo "<textarea rows='5' cols='45' id='explanation'></textarea><br>";

            echo "<label>Frage reviewed?</label><input type='checkbox' id='checkRev'><br>";
            echo "<label>Frage geflagged?</label><input type='checkbox' id='checkFlag'><br>";

            echo "<label>Erklärung zu Flag:</label><br>";
            echo "<textarea id='contFlagExpl' rows='5' cols='45'></textarea><br>";

            echo "<button id='editCancel'>Abbrechen</button>";
            echo "<button id='saveCont'>Speichern</button>";
            echo "<button id='deleteCont'>Löschen</button>";
            echo "</div>";
        ?>


    </body>
    <script>
        var global_questionID = "";

        changeSubjectTo("CS");

        function changeSubjectTo(x){
            if(x == "CS"){
                $("#class").html("<option>mathematische Logik</option><option>Machine Learning</option><option>Datenbanksysteme</option>");
            }else if(x == "WI"){
                $("#class").html("<option>Personalmanagement</option><option>wirtsch. Rechnungswesen</option><option>Wirtschaftsmathematik IV</option>");
            }else if(x == "HM"){
                $("#class").html("<option>Room Evaluation</option><option>Architektur I</option><option>Herbergsgeschichte</option>");
            }
        }  

        // Zusammenholen der Fragen in einem Array
        <?php
            echo "questionArray = [";

            // Herausfinden, wieviele Fragen es insgesamt gibt
            $stmt = $dbh->query("select count(ID) from questions");
            $noq = $stmt->fetchAll()[0][0];

            // Fragencounter initialisieren
            $qcnt = 0;

            // Fragen in ein Array kleiden
            $stmt = $dbh->query("select * from questions");
            while($row = $stmt->fetch()){
                echo "[";

                echo $row['ID'] . ",";                  // 0
                echo "'" . $row['subject'] . "',";      // 1
                echo "'" . $row['class'] . "',";        // 2
                echo "'" . $row['question'] . "',";     // 3
                echo $row['bAnsw1'] . ",";              // 4
                echo "'" . $row['Answer1'] . "',";      // 5
                echo $row['bAnsw2'] . ",";              // 6
                echo "'" . $row['Answer2'] . "',";      // 7
                echo $row['bAnsw3'] . ",";              // 8
                echo "'" . $row['Answer3'] . "',";      // 9
                echo $row['bAnsw4'] . ",";              // 10
                echo "'" . $row['Answer4'] . "',";      // 11
                echo "'" . $row['explanation'] . "',";  // 12
                echo $row['bIsReviewed'] . ",";         // 13
                echo $row['isFlagged'] . ",";           // 14

                if(isset($row['flaggedExplanation'])){  // 15
                    echo "'" . $row['flaggedExplanation'] . "',";
                }else{
                    echo "0";
                }
                
                $qcnt++;
                if($qcnt >= $noq){
                    echo "]";
                }else{
                    echo "],";
                }
            }

            echo "];";
        ?>

        $("#back").click(function(){
            window.location.assign("menu.php");
        });

        $("#editCancel").click(function(){
            $("#editCont").prop("hidden",true);
            $("#mainQuestions").prop("hidden",false);           
        });

        $("#saveCont").click(function(){
            $.post("o_general.php",{submit:"updateQuestion",
                                    ID:global_questionID,
                                    subject:$("#subject").val(),
                                    class:$("#class").val(),
                                    question:$("#contQuest").text(),
                                    check1:$("#contA1").text(),
                                    check2:$("#contA2").text(),
                                    check3:$("#contA3").text(),
                                    check4:$("#contA4").text(),
                                    answ1:$("#check1").prop("checked"),
                                    answ2:$("#check2").prop("checked"),
                                    answ3:$("#check3").prop("checked"),
                                    answ4:$("#check4").prop("checked"),
                                    explain:$("#explanation").text(),
                                    bIsFlagged:$("#checkFlag").prop("checked"),
                                    bIsReviewed:$("#checkRev").prop("checked"),
                                    flagExplain:$("#contFlagExpl").text()},function(){
                alert("Angaben gespeichert!");
                window.location.assign("listQuestions.php");
            });
        });

        // Ausgelöst durch Klick auf die Frage, übernimmt die ID der Frage
        function edit(x){
            global_questionID = x;

            // Angeklickte Frage suchen
            for(var i = 0;i < questionArray.length;i++){
                if(x == questionArray[i][0]){
                    $("#contID").text(questionArray[i][0]);
                    $("#contQuest").text(questionArray[i][3]);
                    if(questionArray[i][4] == 1){
                        $("#check1").prop("checked",true);
                    }
                    $("#contA1").text(questionArray[i][5]);
                    if(questionArray[i][6] == 1){
                        $("#check2").prop("checked",true);
                    }
                    $("#contA2").text(questionArray[i][7]);
                    if(questionArray[i][8] == 1){
                        $("#check3").prop("checked",true);
                    }
                    $("#contA3").text(questionArray[i][9]);
                    if(questionArray[i][10] == 1){
                        $("#check4").prop("checked",true);
                    }
                    $("#contA4").text(questionArray[i][11]);

                    $("#explanation").text(questionArray[i][12]);
                    
                    if(questionArray[i][13] == 1){
                        $("#checkRev").prop("checked",true);
                    }

                    if(questionArray[i][14] == 1){
                        $("#checkFlag").prop("checked",true);
                    }
                    if(questionArray[i][15] != "0"){
                        $("#contFlagExpl").text(questionArray[i][15]);
                    }
                    

                    break;
                }
            }

            

            // Fragen verstecken, Content anzeigen
            $("#editCont").prop("hidden",false);
            $("#mainQuestions").prop("hidden",true);
        }
    </script>
</html>