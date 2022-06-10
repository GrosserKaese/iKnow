<?php
    // ein paar Hilfsfunktionen
    //
    function getUserIDfromName($userName){
        include "o_header.php";
        $stmt1 = $dbh->query("select * from users where user='" . $userName . "'");
        while($row = $stmt1->fetch()){
            return $row['ID'];
        }
    }
?>