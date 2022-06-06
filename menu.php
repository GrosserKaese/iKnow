<?php
    session_start();
    echo "Here is the menu and you are " . $_SESSION['user_role'] . ".";
    $_SESSION['session_name'] = "";
?>
<!DOCTYPE html>
<html>
    <head>
    <script src="jquery-3.6.0.js"></script>        
        <title>Menu</title>
    </head>
    <body>
        <form action="o_general.php" method="POST">
            <fieldset style="width:250px;">
                <legend>Topic</legend>
                <label>Fach wählen:</label>
                <select name="subject" id="subject">
                    <option onclick="changeSubjectTo('CS')">Informatik B.Sc.</option>
                    <option onclick="changeSubjectTo('WI')">Wirtschaftsinformatik B.A.</option>
                    <option onclick="changeSubjectTo('HM')">Hotelmanagement M.A.</option>
                </select><br>   
                <label>Modul wählen:</label>
                <select name="class" id="class"></select><br>
            </fieldset>
            <fieldset style="width:250px;">
                <legend>Spielmodus auswählen</legend>
                <input checked type="radio" name="modus" value="coop"><label>Miteinander</label><br>
                <input type="radio" name="modus" value="versus"><label>Gegeinander</label>
            </fieldset>
            <br>
            <br>
            <input type="submit" id="submit" name="submit" value="Create session">
        </form><br>
        <form action="o_general.php" method="POST">
            <input type="text" id="hostname" name="joinsession">
            <input type="submit" id="joinsession" name="submit" value="Join Session">
        </form>
        <h4 id="ueber" hidden>Frage einstellen</h4>
        <button onclick="switchSite('einstellen')">Frage einstellen</button>
        <?php 
            if($_SESSION['user_role'] == "admin"){
                echo "<h4>Fragen einsehen</h4>";
                echo "<button onclick=switchSite('einsehen')>Fragen einsehen</button>";
            }
        ?>
        <br><a href="logout.php">Logout</a>
    </body>
    <script>
        changeSubjectTo("CS");
        clearInterval();

        function changeSubjectTo(x){
            if(x == "CS"){
                $("#class").html("<option>mathematische Logik</option><option>Machine Learning</option><option>Datenbanksysteme</option>");
            }else if(x == "WI"){
                $("#class").html("<option>Personalmanagement</option><option>wirtsch. Rechnungswesen</option><option>Wirtschaftsmathematik IV</option>");
            }else if(x == "HM"){
                $("#class").html("<option>Room Evaluation</option><option>Architektur I</option><option>Herbergsgeschichte</option>");
            }
        }         
        function switchSite(x){
            if(x == "einstellen"){
                window.location = "addQuestions.php";
            }else if(x == "einsehen"){
                window.location = "listQuestions.php";
            }
        }
    </script>
</html>