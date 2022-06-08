<?php
/* 
*   Das Playfield wird je nach Spielmodus aufbereitet.
*   Der Host gibt die Taktrate vor, nach der der guest sich misst.
*
*
*/
    session_start();
    include "o_header.php";
?>
<html>
    <head>
        <script src="jquery-3.6.0.js"></script>
        <title>Playfield Session <?php echo $_SESSION['session_name']; ?></title>
    </head>
	<body>

	</body>
	<script>
		// KRITISCHER MAIN TICKER! HEARTBEAT!
		const pingTime = 100;
		var ms = Date.now()+1000;
		setInterval(function(){
			if(Date.now()-ms > 0){
				tick();
				ms = Date.now()+1000;
			}
		},pingTime);

		function tick(){
			
		}

	</script>
</html>