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
    </body>
    <script>
        var global_beat = 0;
        setInterval(tickBeat,1000);

        function tickBeat(){
            $("#beat").load("o_general.php",{
                "submit":"getBeat"
            },changeTick());
        }

        function changeTick(){
            $("#beat").text(global_beat)
        }
    </script>
</html>