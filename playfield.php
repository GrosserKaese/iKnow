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
        clearInterval();
        var global_beat = 0;
        setInterval(tickBeat,1000);

        function tickBeat(){
            global_beat = global_beat + 1;
            document.getElementById("beat").innerText = global_beat;

            $.post("o_general.php",
            {
                submit: "heartbeat",
                sessionname: "<?php echo $_SESSION['session_name'] ?>",
                beat: global_beat
            },null);
        }
    </script>
</html>