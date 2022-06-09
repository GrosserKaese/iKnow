<?php
/* 
*   Das Playfield wird je nach Spielmodus aufbereitet.
*   Der Host gibt die Taktrate vor, nach der der guest sich misst.
*/

    session_start();
    include "o_header.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <script src="jquery-3.6.0.js"></script>
        <title>Playfield</title>
    </head>
    <body>
		<p id="question"></p>
		
		<input type="checkbox" id="a1"><span id="answ1"></span><br>
		<input type="checkbox" id="a2"><span id="answ2"></span><br>
		<input type="checkbox" id="a3"><span id="answ3"></span><br>
		<input type="checkbox" id="a4"><span id="answ4"></span><br>

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
				$.post("o_general.php",{submit:"getReadyState"},function(readyState){
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

		function displayQuestion(){
			console.log("Frage angezeigt!");
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