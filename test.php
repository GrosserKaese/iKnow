<?php
/* 
*   Das Playfield wird je nach Spielmodus aufbereitet.
*   Der Host gibt die Taktrate vor, nach der der guest sich misst.
*
*
*/
    session_start();
    $servername = "localhost";
    $database  = "test";
    $username = "root";
    $password = "";

    $dbh = new PDO("mysql:host=$servername; dbname=$database",$username,$password);    
?>
<html>
    <head>
        <script src="jquery-3.6.0.js"></script>
        <title>Testlane</title>
    </head>
	<body>
	 	<p id="role"></p>
		<p id="ticker"></p>
		<button id="gimmehost">Host</button>
		<button id="gimmegast">Gast</button><br>
		<button id="done">Fertig</button>
	</body>
	<script>
		//
		// Globals
		//
		var ownRole = "";
		var pingCounter = 0;
		var bReady = 0;
		
		//
		// KRITISCHER MAIN TICKER! HEARTBEAT!
		// Triggert die Funktion tick()
		//
		const pingTime = 100;
		var ms = Date.now()+1000;
		setInterval(function(){
			if(Date.now()-ms > 0){
				tick();
				ms = Date.now()+1000;
			}
		},pingTime);

		function tick(){
			// pro Tick wird der Counter um eins erhöht
			pingCounter++;
			$("#ticker").text(pingCounter);

			// Frage holen
			// prüfe, ob du ready bist
			if(bReady == 1){
				// Falls ja, prüfe, ob der andere ready ist
				$.post("o_test.php",{submit:"getReadyState"},function(readyState){
					if(readyState == 1){
						// Falls ja, hole die nächste Frage
						bReady = 0;
						displayQuestion();
						setTimeout(function(){
							$.post("o_test.php",{submit:"setReadyState",state:2},null);
						},1000);
						
					}
				});
			}
		}

		function displayQuestion(){
			console.log("Frage angezeigt!");
		}

		//
		// Buttonfunktionen
		//
		$("#gimmehost").click(function(){
			$.post("o_test.php",{submit:"setUserRole",role:"host"},null)
			ownRole = "host";
			$("#role").text(ownRole);
		});
		$("#gimmegast").click(function(){
			$.post("o_test.php",{submit:"setUserRole",role:"gast"},null)
			ownRole = "gast";
			$("#role").text(ownRole);
		});
		$("#done").click(function(){
			bReady = 1;
			$.post("o_test.php",{submit:"setReadyState",state:1},null)
		});

	</script>
</html>