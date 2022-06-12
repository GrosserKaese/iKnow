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
        <a href="menu.php">zurück</a>        
        <h1>Eingestellte Fragen</h1>
        <?php
            // Schleife, die bei jedem neuen Vorkommen eines Fachs eine neue Tabelle erstellt
            $stmt = $dbh->query("select * from questions order by subject asc");
            $subject_name = "";
            $subject_name2 = "";
            while($row = $stmt->fetch()){
                $subject_name = $row['subject'];
                if($subject_name != $subject_name2){
                    echo "<h2>" . $row['subject'] . "</h2>";
                    $subject_name2 = $row['subject'];

                    $class_name = "";
                    $class_name2 = "";

                    $stmt1 = $dbh->query("select * from questions where subject='" . $subject_name . "'");
                    while($row1 = $stmt1->fetch()){
                        $class_name = $row1['class'];
                        if($class_name != $class_name2){
                            echo "<h3>" . $row1['class'] . "</h3>";
                            $class_name2 = $row1['class'];

                            $stmt2 = $dbh->query("select * from questions where subject='" . $subject_name . "' and class='" . $class_name . "'");
                            echo "<table class=table>";
                            echo "<thead>";
                            echo "<th>Frage</th>";
                            echo "<th>Antwort 1</th>";
                            echo "<th>Antwort 2</th>";
                            echo "<th>Antwort 3</th>";
                            echo "<th>Antwort 4</th>";
                            echo "<th>Erklärung</th>";
                            echo "<th>reviewed?</th>";
                            echo "</thead>";
                            echo "<tbody>";
                            while($row2 = $stmt2->fetch()){
                                echo "<tr>";
                                echo "<td>" . $row2['question'] . "</td>";

                                if($row2['bAnsw1'] == "1"){
                                    echo "<td class=table-success>" . $row2['Answer1'] . "</td>";
                                }else{
                                    echo "<td>" . $row2['Answer1'] . "</td>";
                                }
                                
                                if($row2['bAnsw2'] == "1"){
                                    echo "<td class=table-success>" . $row2['Answer2'] . "</td>";
                                }else{
                                    echo "<td>" . $row2['Answer2'] . "</td>";
                                }

                                if($row2['bAnsw3'] == "1"){
                                    echo "<td class=table-success>" . $row2['Answer3'] . "</td>";
                                }else{
                                    echo "<td>" . $row2['Answer3'] . "</td>";
                                }

                                if($row2['bAnsw4'] == "1"){
                                    echo "<td class=table-success>" . $row2['Answer4'] . "</td>";
                                }else{
                                    echo "<td>" . $row2['Answer4'] . "</td>";
                                }

                                echo "<td>" . $row2['explanation'] . "</td>";
                                
                                echo "<td>";
                                if($row2['bIsReviewed'] == "0"){
                                    echo "Nein";
                                }else{
                                    echo "Ja";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                            echo "</tbody>";
                            echo "</table>";
                        }
                    }
                }
            }
        ?>
    </body>
    <script></script>
</html>