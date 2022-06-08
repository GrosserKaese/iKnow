<?php
    session_start();
    include "o_header.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <script src="jquery-3.6.0.js"></script>
        <title>Wartezimmer</title>
    </head>
    <body>
        <p>Warteraum</p>
        <p>Dies ist Session: <?php echo $_SESSION['session_name']; ?></p>
        <p>Du bist: <?php echo $_SESSION['session_role']; ?></p>
        <p>Dein Username: <?php echo $_SESSION['user_name']; ?></p>
        <p>ReadyState:<span id="readyState">0</span></p>
        <p>Dein Mitspieler ist <span id="readyHTML" style="color:red;"><i><b>nicht bereit</b></i></span>.</p>
        <form action="o_general.php" method="POST">
            <input hidden type="submit" id="iSubmit" name="submit" value="Start">
        </form><br>

        <input type="submit" id="readyButton" value="Bereit!" onclick=getReady("on")>
        <input hidden type="submit" id="stopButton" value="Stop!" onclick=getReady("off")>
        

        <form action="o_general.php" method="POST">
            <input type="submit" name="cancel" value="Abbrechen">
        </form>

    </body>
    <script>
        var readyState = 0;
        var ownReadyState = 0;
        setInterval(tickBeat,1000);
        function tickBeat(){

            readyState = $.get("o_general.php",{"submit":"getReadyState",
                                                "sessionID":"<?php echo $_SESSION['session_name']; ?>",
                                                "userName":"<?php echo $_SESSION['user_name'] ?>"},changeState());
        }

        // Lässt den Bereit-Button erscheinen oder verschwinden
        // teilt dies über das Skript dem Server mit

        if(ownReadyState == 1){
            $("#readyButton").prop('hidden',true);
                $("#stopButton").prop('hidden',false);

                $.post("o_general.php",{
                    submit: "changeReadyState",
                    sessionname: "<?php echo $_SESSION['session_name'] ?>",
                    username: "<?php echo $_SESSION['user_name']; ?>",
                    value: "on"
                },null);
        }else{
            $("#readyButton").prop('hidden',false);
                $("#stopButton").prop('hidden',true);

                $.post("o_general.php",{
                    submit: "changeReadyState",
                    sessionname: "<?php echo $_SESSION['session_name'] ?>",
                    username: "<?php echo $_SESSION['user_name']; ?>",
                    value: "off"
                },null);                 
        }

        function getReady(x){
            if(x == "on"){
                ownReadyState = 1;
                $("#readyButton").prop('hidden',true);
                $("#stopButton").prop('hidden',false);

                $.post("o_general.php",{
                    submit: "changeReadyState",
                    sessionname: "<?php echo $_SESSION['session_name'] ?>",
                    username: "<?php echo $_SESSION['user_name']; ?>",
                    value: "on"
                },null);
            }else{
                ownReadyState = 0;
                $("#readyButton").prop('hidden',false);
                $("#stopButton").prop('hidden',true);

                $.post("o_general.php",{
                    submit: "changeReadyState",
                    sessionname: "<?php echo $_SESSION['session_name'] ?>",
                    username: "<?php echo $_SESSION['user_name']; ?>",
                    value: "off"
                },null);                              
            }

        }

        function changeState(){
            console.log(readyState.responseText , " " , ownReadyState);

            if((readyState.responseText == "0" || readyState.responseText == "")){
                $("#readyHTML").prop('style').color = "red";
                $("#readyHTML").text("nicht bereit");
            }else if (readyState.responseText == "1"){
                $("#readyHTML").prop('style').color = "green";
                $("#readyHTML").text("bereit!");
                if(ownReadyState == 1){
                    $("#iSubmit").prop('hidden',false);
                }else{
                    $("#iSubmit").prop('hidden',true);
                }

            }
            $("#readyState").text(readyState.responseText);
        } 
            
    </script>
</html>