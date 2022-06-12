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
		break;
    }

	// Der Host muss vorher noch die Informationen an den Guest übersenden
	if($_SESSION['session_role'] == "host"){
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$dbh->beginTransaction();
		$dbh->exec("update sessions set subject='" . $subjectname . "',class='" . $classname . "' where sessionname=" . $_SESSION['session_name']);
		$dbh->commit();
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
		<p id="heartbeat"></p>
		<div id="questionOnDisplay"></div>

		<p id="waitForPlayer" hidden>Warten auf Mitspieler...</p>
		<p id="failMess" style="color:red;" hidden>Antworten stimmen nicht überein!</p>
		
		<button id="done">Fertig!</button><br>
		<button id="flagQuestion">Frage melden</button><br>
		<input type="text" id="flagText" hidden><br>
		<button id="cancel">Abbrechen</button>
    </body>
    <script>
		console.log("Session:<?php echo $_SESSION['session_name']; ?>");
		/* -------------------------------------------------------------------------------------------------
		*											GLOBALS
		* ------------------------------------------------------------------------------------------------- */ 
		var global_beat = 0;
		var ownRole = "<?php echo $_SESSION['session_role']; ?>";
		const gameMode = "<?php echo $_SESSION['game_mode']; ?>";
		var pingCounter = 0;
		var bReady = 0; // nur für den guest interessant
		var qCountdown = 60;
		const QTIMER = <?php 
							if($_SESSION['game_mode'] == "coop"){
								echo 60;
							}else if($_SESSION['game_mode'] == "versus"){
								echo 45;
							}
						?>;
		const PING = 100;
		var qCounter = 0;

		// Hier muss ein Timeout hin, weil der Host noch die Sessioninformationen an den Mitspieler übertragen muss
		setTimeout(nextQuestion(),1000);

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

			// Der Host gibt den Takt vor und legt ihn in heartbeat ab
			if(ownRole == "host"){
				$.post("o_general.php",{submit:"heartbeat",beat:pingCounter},function(){
					global_beat = pingCounter;

					if(gameMode == "coop"){
						qCountdown = 60 - global_beat%60;
					}else if(gameMode == "versus"){
						qCountdown = 45 - global_beat%45;
					}					

					$("#heartbeat").text(qCountdown);
				});

				// Der Host prüft, ob beide Spieler abgegeben haben
				$.post("o_general.php",{submit:"getReadyState",state:"control"},function(result){
					if(result == "equal"){
						if(gameMode == "versus"){
							$.post("o_general.php",{submit:"setReadyState",status:2},null);
							nextQuestion();
						}else if(gameMode == "coop"){
							$.post("o_general.php",{submit:"checkQuestions",qCounter:qCounter},function(result){
								if(result == "fail"){
									$.post("o_general.php",{submit:"rollbackQuestions",qCounter:qCounter},function(){
										$.post("o_general.php",{submit:"setReadyState",status:2},null);
										$("#failMess").prop("hidden",false);
									});
								}else if(result == "success"){
									$.post("o_general.php",{submit:"setReadyState",status:2},null);
									nextQuestion();
									$("#failMess").prop("hidden",true);
								}
							});
						}
					resetButtons();
					}
				});
			}

			// der guest holt sich den Takt von heartbeat
			if(ownRole == "guest"){
				$.post("o_general.php",{submit:"getBeat"},function(beat){
					global_beat = parseInt(beat);

					if(gameMode == "coop"){
						qCountdown = 60 - global_beat%60;
					}else if(gameMode == "versus"){
						qCountdown = 45 - global_beat%45;
					}

					$("#heartbeat").text(qCountdown);
				});

				// Prüft sich selbst, ob er abgegeben hat
				if(bReady == 1){
					setTimeout(function(){
						
						// Falls ja, prüft Gast, ob sein lokaler Ready-Status mit dem des Servers übereinstimmt
						$.post("o_general.php",{submit:"getOwnReadyState"},function(result){
							if(result == "zero"){

								// Falls nicht, heißt das, dass der Host etwas verändert hat,
								// d.h. die Frage wurde richtig oder falsch bewertet
								bReady = 0;
								resetButtons();
								if(gameMode == "coop"){
									// Gast muss prüfen, ob der Host schon die nächste Frage gestellt hat,
									// i.e. ob die Frage mit seinem aktuellen qCounter noch vorhanden ist
									$.post("o_general.php",{submit:"checkQuestionState",qCounter:qCounter},function(result){
										console.log("result=" + result);
										if(result == 1){
											$("#failMess").prop("hidden",true);
											nextQuestion();
										}else if(result == false){
											$("#failMess").prop("hidden",false);
										}
									});
								}else if(gameMode == "versus"){
									nextQuestion();
								}

							}
						});
					},100)
				}
			}

			if(qCountdown == 0){
				nextQuestion();
			}
		
			// prüfen, ob der Spieler die Frage meldet

			// falls ja, Input-Text anzeigen
		}


		// Hilfsfunktion zum Zurücksetzen der Buttons
		function resetButtons(){
			$("#done").prop("hidden",false);
			$("#flagQuestion").prop("hidden",false);
			$("#waitForPlayer").prop("hidden",true);
		}

		// Hilfsfunktion zum Löschen der Buttons
		function hideButtons(){
			$("#done").prop("hidden",true);
			$("#flagQuestion").prop("hidden",true);
			$("#waitForPlayer").prop("hidden",false);
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

		function resetTimer(){
			if(ownRole == "host"){
				pingCounter = 0;
			}
		}

		function displayQuestion(){
			if(ownRole == "host"){

			// Für den Host: neue Frage holen, der Server entscheidet selbst,
			// welche Frage drankommt
			console.log("<?php echo $subjectname; ?>" + " <?php echo $classname; ?> " + " <?php echo $_SESSION['session_name'] ?>");
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
			}else if(ownRole == "guest"){
					$.post("o_general.php",{submit:"getActQuestion",
										subject:"<?php echo $subjectname; ?>",
										class:"<?php echo $classname; ?>"},function(returnHTML){
					$("#questionOnDisplay").html(returnHTML);
					console.log("Frage angezeigt!");
				});
			}

		}

		function nextQuestion(){
			// Für beide: den qCounter hochziehen
			qCounter++;	

			resetTimer();
			console.log("Frage angefordert.");
			if(ownRole == "host"){
				displayQuestion();
			}else{
				setTimeout(displayQuestion,1000);
			}
			
		}

		// Bei Klicken des "Fertig"-Knopfes
		//
		$("#done").click(function(){
			var text = "Frage abgeben?";
			if(confirm(text) == true){
				// Buttons verstecken
				hideButtons();

				// Ready-Status setzen, falls ich Gast bin
				if(ownRole == "guest"){
					bReady = 1;
				}
				
				
				$.post("o_general.php",{submit:"setReadyState",status:1},null);
				$.post("o_general.php",{submit:"flagQuestionDone",
										a1:$("#a1").prop("checked"),
										a2:$("#a2").prop("checked"),
										a3:$("#a3").prop("checked"),
										a4:$("#a4").prop("checked"),
										qCounter:qCounter,
										subject:"<?php echo $subjectname; ?>",
										class:"<?php echo $classname; ?>"},null);
				}
		});

		// Bei Klicken des "Frage Melden" Buttons
		//
		$("#flagQuestion").click(function(){
			
		});

    </script>
</html>