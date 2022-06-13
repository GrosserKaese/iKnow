<?php
    session_start();
    include "o_header.php";
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
        <button id="back">Abbrechen</button>
        <h1>Eingestellte Fragen</h1>
        <?php
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
                        
                        
                        if($row1['isFlagged'] == 1){
                            echo "<tr class='table-danger' style='cursor:pointer;'>";
                        }else{
                            echo "<tr>";
                        }
                        
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

        ?>
    </body>
    <script>
        $("#back").click(function(){
            window.location.assign("menu.php");
        });
    </script>
</html>