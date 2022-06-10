<?php
/* 
*   Das Playfield wird je nach Spielmodus aufbereitet.
*   Der Host gibt die Taktrate vor, nach der der guest sich misst.
*/

    session_start();
    include "o_header.php";
	include "o_functions.php";

    // Mach dir eine Liste mit den ID's der Fragen, die in Frage kommen
    //
    $classes = $dbh->query("select subject,class from sessions where sessionname=" . $_SESSION['session_name'] . " and userID='" . getUserIDfromName($_SESSION['user_name']) . "'");
    while($row = $classes->fetch()){
        $subjectname = $row['subject'];
        $classname = $row['class'];
    }
    
    // kurz herausfinden, wie viele Fragen es insgesamt sind
    $num1 = $dbh->query("select count(subject) from questions where subject='" . $subjectname . "' and class='" . $classname . "'");
    $numrows = $num1->fetchAll()[0][0];

    // braucht Javascript später
    echo "<script>var numberOfQuestions = " . $numrows . "</script>";
?>
<!DOCTYPE html>
<html>
    <head>
        <script src="jquery-3.6.0.js"></script>
        <title>Playfield</title>
    </head>
    <body>
		<div id="questionOnDisplay"></div>

		<p id="waitForPlayer" hidden>Warten auf Mitspieler...</p>
		
		<button id="done">Fertig!</button><br>
		<button id="flagQuestion">Frage melden</button><br>
		<input type="text" id="flagText" hidden><br>
		<button id="cancel">Abbrechen</button>
    </body>
    <script>
		/* -------------------------------------------------------------------------------------------------
		*											GLOBALS
		* ------------------------------------------------------------------------------------------------- */ 
		var global_beat = 0;
		var ownRole = "";
		var pingCounter = 0;
		var bReady = 0;
		const PING = 100;

		/* -------------------------------------------------------------------------------------------------
		*											TICKER
		* ------------------------------------------------------------------------------------------------- */
		var ms = Date.now() + 1000;
		setInterval(function(){
			if(Date.now() - ms > 0){
				pingCounter++;
				tick();
				ms = Date.now() + 1000;
			}
		},PING)

		/* -------------------------------------------------------------------------------------------------
		*										   FUNCTIONS
		* ------------------------------------------------------------------------------------------------- */ 
		function tick(){

			// prüfen ob der Spieler den Fertig-Knopf gedrückt hat
			if(bReady == 1){

				// falls ja, prüfen, ob der Gegenspieler den Knopf gedrückt hat
				$.post("o_general.php",{submit:"getReadyState",state:"control"},function(readyState){
					if(readyState == 1){

						// falls ja, neue Frage anzeigen und beide Ready-Statusse wieder zurücksetzen
						bReady = 0;
						displayQuestion();

						// Zeitverzögerung, um Server Zeit zu geben, den Status anzuerkennen,
						// danach zurücksetzen der Schalter und Buttons
						setTimeout(function(){
							$.post("o_general.php",{submit:"setReadyState",status:2},null);
							$("#done").prop("hidden",false);
							$("#flagQuestion").prop("hidden",false);
							$("#waitForPlayer").prop("hidden",true);
						},1000);
					}
				})
			}

			

			// prüfen, ob der Spieler die Frage meldet

			// falls ja, Input-Text anzeigen
		}

		// Hier wird das Spiel beendet und zur Ergebnisseite weitergeleitet.
		// Auch wird hier das Spielfeld aufgeräumt.
		//
		function quitGame(){
			console.log("Spiel wird beendet");

			// Session wird gelöscht
			$.post("o_general.php",{submit:"destroySession"},null);

			window.location.assign("results.php");
		}

		function displayQuestion(){
			console.log("Frage angezeigt!");

			// neue Frage holen, der Server entscheidet selbst,
			// welche Frage drankommt
			$.post("o_general.php",{submit:"getNewQuestion",
									subject:"<?php echo $subjectname; ?>",
									class:"<?php echo $classname; ?>",
									session:"<?php echo $_SESSION['session_name'] ?>"},function(returnHTML){
										if(returnHTML == "forceQuit"){

											// in diesem Fall gibt es keine Fragen mehr, die noch nicht beantwortet wurden.
											quitGame();
										}else{
											$("#questionOnDisplay").html(returnHTML);
										}
										
									});

		}

		// Bei Klicken des "Fertig"-Knopfes
		//
		$("#done").click(function(){
			// "Warten auf Spieler" einblenden
			$("#waitForPlayer").prop("hidden",false);

			// "Fertig" verschwinden lassen
			$("#done").prop("hidden",true);

			// "Frage melden" verschwinden lassen
			$("#flagQuestion").prop("hidden",true);

			// Ready-Status setzen
			bReady = 1;
			$.post("o_general.php",{submit:"setReadyState",status:1},null);
		});

		// Bei Klicken des "Frage Melden" Buttons
		//
		$("#flagQuestion").click(function(){
			
		});

    </script>
</html>