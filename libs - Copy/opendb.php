<?php
try {
    $dbh = new PDO('mysql:host='.$server.';dbname='.$db, $usrnm, $pwd);
    $dbh->exec("SET NAMES utf8;");
	$dbh->exec("SET CHARACTER_SET utf8;");
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>