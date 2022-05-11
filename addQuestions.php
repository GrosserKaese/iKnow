<?php
    session_start();
    // nur zur Sicherheit, dass hier kein Unbefleckter herumeiert
    if($_SESSION['user_role'] != "admin"){
        $_SESSION['user_role'] == "";
        header('Location:index.php');
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <script src="jquery-3.6.0.js"></script>        
        <title>Fragen einstellen</title>
    </head>
    <body>
        <a href="menu.php">zurück</a>
        <form action="o_general.php" method="POST">
            <label>Fach</label>
            <select name="subject" id="subject">
                <option onclick="changeSubjectTo('CS')">Informatik B.Sc.</option>
                <option onclick="changeSubjectTo('WI')">Wirtschaftsinformatik B.A.</option>
                <option onclick="changeSubjectTo('HM')">Hotelmanagement M.A.</option>
            </select><br>            
            <label>Modul</label>
            <select name="class" id="class"></select><br>
            <label>Frage</label><br>
            <textarea name="question" id="question" rows="5" cols="45"></textarea><br><br>
            <label>Antworten (richtige Antwort/en markieren)</label><br>
            <input type="checkbox" id="check1" name="check1">1<input name="answ1" type="text"><br>
            <input type="checkbox" id="check2" name="check2">2<input name="answ2" type="text"><br>
            <input type="checkbox" id="check3" name="check3">3<input name="answ3" type="text"><br>
            <input type="checkbox" id="check4" name="check4">4<input name="answ4" type="text"><br><br>
            <label>Erklärung zur Frage (optional)</label><br>
            <textarea name="explain" id="explain" rows="5" cols="45"></textarea><br>
            <button type="submit" id="submit" name="submit" value="addQuestion">Frage einstellen</button>
        </form>
    </body>
    <script>
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
    </script>
</html>