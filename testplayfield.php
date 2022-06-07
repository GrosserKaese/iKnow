<?php 
	session_start();
	include "o_header.php";
?>

<!DOCTYPE html>
<html>
	<head>
		<title>playfield</title>
	</head>
	<body>
<?php
	// Hilfsfunktion
	function wait($sec){
		$wait_time = microtime(false) + $sec;
		while($wait_time > microtime(false)){

		}
	}

	// Den Status beider Spieler holen. Wenn einer von beiden noch nicht bereit ist,
	// dann so lange wiederholen, bis beide Spieler ready sind
	//
	$out = 0;
	while($out == 0){
		echo "<script>console.log('Entry');</script>";
		$cnt = 0;
		$stmt = $dbh->query("select ready from sessions where sessionname='" . $_SESSION['session_name'] . "'");
		while($row = $stmt->fetch()){
			if($cnt == 0){
				$host = $row['ready'];
				$cnt++;
			}else{
				$guest = $row['ready'];
				$cnt = 0;
				break;
			}
		}

		if($guest == 1 && $host == 1){
			echo "<p>Found both!</p>";
			$out = 1;
		}else{
			echo "<p>Waiting for player....</p>";
			wait(2);			
		}
	}

?>
	</body>
</html>