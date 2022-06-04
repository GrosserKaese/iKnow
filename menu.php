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
        clearInterval();
        function switchSite(x){
            if(x == "einstellen"){
                window.location = "addQuestions.php";
            }else if(x == "einsehen"){
                window.location = "listQuestions.php";
            }
        }
    </script>
</html>