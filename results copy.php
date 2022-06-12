<?php
    session_start();
    include "o_header.php";
    include "o_functions.php";

    $sessionname = 7149;
    $gamemode="coop";
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <script src="bootstrap/js/bootstrap.bundle.min.js"></script>        
        <script src="jquery-3.6.0.js"></script>        
        <title>Ergebnisse</title>
    </head>
    <body>
<?php
    // Die Spieler holen
    $stmt = $dbh->query("select * from donequestions where sessionname=" . $sessionname);
    $player1 = $stmt->fetch()[3];
    $player2 = $stmt->fetch()[3];

    for($j = 0;$j < 2;$j++){
        $player = $stmt->fetch()[3];

    }

    echo "<h3>Ergebnisse</h3>";
    echo "<table class='table'>";
    echo "<thead>";
    echo "<th>" . $player1 . "</th>";
    echo "<th>" . $player2 . "</th>";
    echo "</thead>";
    echo "<tbody>";        
    echo "<tr>";

    echo "<td>";
    echo "<table class='table'>";
    echo "<thead>";
    echo "<th>Nr</th>";
    echo "<th>Frage</th>";
    echo "<th>Antwort 1</th>";
    echo "<th>Antwort 2</th>";
    echo "<th>Antwort 3</th>";
    echo "<th>Antwort 4</th>";
    echo "</thead>";

    echo "<tbody>";
    $stmt = $dbh->query("select * from donequestions where sessionname=" . $sessionname . " and user='" . $player1 . "'");
    while($row = $stmt->fetch()){    
        echo "<tr>";
        // Die Fragen miteineander überprüfen
        // Wenn Coop gespielt wird, müssen sie zusätzlich übereinstimmen
        $bIsEqual = true;
        $bIsTrue = true;
        $tableString = "";
        if($gamemode == "coop"){
            $answArray = array();
            $correctArray = array();
            // Antworten beider Spieler miteinander vergleichen
            $answerString1 = $dbh->query("select * from donequestions where sessionname=" . $sessionname . " and qCounter=" . $row['qCounter']);
            
            while($row1 = $answerString1->fetch()){
                for($i = 1;$i < 5;$i++){
                    array_push($answArray,$row1['bAnsw' . $i]);
                }
            }
            for($i = 0;$i < 4;$i++){
                if($answArray[0+$i] <> $answArray[4+$i]){
                    $bIsEqual = false;
                }
            }
        
            // Antworten des Spieler mit den richtigen Antworten vergleichen
            $player1answers = $dbh->query("select * from donequestions where sessionname=" . $sessionname . " and qCounter=" . $row['qCounter'] . " and user='" . $player1 . "'");
            $correctAnswers = $dbh->query("select * from questions where ID=" . $row['question']);
            $answArray = array();
            // Antworten des Spielers sammeln
            while($row2 = $player1answers->fetch()){
                for($i = 1;$i < 5;$i++){
                    array_push($answArray,$row2['bAnsw' . $i]);
                }
            }
            // Korrekte Antworten sammeln
            while($row3 = $correctAnswers->fetch()){
                for($i = 1;$i < 5;$i++){
                    array_push($correctArray,$row3['bAnsw' . $i]);
                }
            }
            // Frage für die Tabelle holen
            $question = $dbh->query("select question from questions where ID=" . $row['question']);
            $tableString = $tableString . "<td>" . $question->fetch()[0] . "</td>";
            // Beide Werte Stück für Stück miteinander vergleichen
            for($i = 0;$i < 4;$i++){
                $answer = $dbh->query("select Answer" . $i+1 . " from questions where ID=" . $row['question']);
                if($answArray[$i] <> $correctArray[$i]){
                    $tableString = $tableString . "<td class='table-danger'>" . $answer->fetch()[0];
                    $bIsTrue = false;
                }else{
                    $tableString = $tableString . "<td class='table-success'>" . $answer->fetch()[0];
                }
                $tableString = $tableString . "</td>";
            }
            if($bIsEqual == true && $bIsTrue == true){
                echo "<td class='table-success'>R</td>";
            }else{
                echo "<td class='table-danger'>F</td>";
            }   
            echo $tableString;
            
            echo "</tr>";
        }


    }
        echo "</tbody>";
        echo "</table>";
        echo "</td>";

        echo "<td>"; 
        echo "<table class='table'>";
        echo "<thead>";
        echo "<th>Nr</th>";
        echo "<th>Frage</th>";
        echo "<th>Antwort 1</th>";
        echo "<th>Antwort 2</th>";
        echo "<th>Antwort 3</th>";
        echo "<th>Antwort 4</th>";
        echo "</thead>";      

        echo "<tbody>";
        $stmt = $dbh->query("select * from donequestions where sessionname=" . $sessionname . " and user='" . $player2 . "'");
        while($row = $stmt->fetch()){    
            echo "<tr>";
            echo "<td>" . $row['qCounter'] . "</td>";

            $question = $dbh->query("select question from questions where ID=" . $row['question']);
            echo "<td>" . $question->fetch()[0] . "</td>";

            for($i = 1;$i < 5;$i++){
                $answer = $dbh->query("select Answer" . $i . " from questions where ID=" . $row['question']);
                echo "<td>" . $answer->fetch()[0] . "</td>";
            }
            echo "</tr>";
        }        
        echo "</tbody>";        
        echo "</table>";        
        echo"</td>";

        echo "</tr>";
    echo "</tbody>";    
    echo "</table>";    
?>
    <p>
        <span>Legende:</span><br>
        <span>Antworten stimmen mit richtigen Antworten überein:       grün</span><br>
        <span>Antworten stimmen nicht mit richtigen Antworten überein: rot</span><br><br>
        <span>Punktezählung:</span><br>
        <span>Bei Coop:</span><br>
        <span>Wenn Fragen übereinstimmen: +1 Punkt</span><br>
        <span>Wenn Fragen nicht übereinstimmen: -1 Punkt, Frage wird als "Falsch" bewertet</span><br><br>
        <span>Bei allen Modi:</span><br>
        <span>Fragen richtig beantwortet: +1 Punkt</span><br>
        <span>Fragen falsch beantwortet:  -1 Punkt</span><br>
    </p>
    </body>
    <script>
        
    </script>
</html>